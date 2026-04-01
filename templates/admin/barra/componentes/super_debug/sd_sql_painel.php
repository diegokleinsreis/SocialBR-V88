<?php
/**
 * FICHEIRO: super_debug/sd_sql_painel.php
 * PAPEL: Interface HUD Unificada para Performance e Auditoria SQL.
 * VERSÃO: 1.5 (SQL Hub Unificado - Tabbed Edition)
 * RESPONSABILIDADE: Renderizar a interface de abas para visualização de queries em tempo real e log persistente.
 */

// 1. ACESSO AO RASTREADOR (Sessão Atual)
// O $conn já é um LoggedMySQLi vindo do database.php
if (!class_exists('SQLPerfTracker')) {
    return; // Silencia se o motor não estiver carregado
}

// Recupera a instância ativa do rastreador (Singleton)
$tracker = SQLPerfTracker::init($conn);
$queriesHistory = $tracker->getHistory();
$totalQueries = $tracker->getCount();

// 2. LEITURA DA AUDITORIA (Arquivo Físico)
// Buscamos o log persistente para permitir a depuração de chamadas AJAX/Fetch
$logPath = __DIR__ . '/../../../../../config/sql_audit.log';
$auditLines = [];

if (file_exists($logPath)) {
    // Técnica de leitura otimizada: Carrega apenas as últimas 50 entradas do log
    $fileData = file($logPath);
    if ($fileData) {
        // Inverte a ordem para que a consulta mais recente apareça no topo da lista
        $auditLines = array_reverse(array_slice($fileData, -50));
    }
}

// 3. VERIFICAÇÃO DE SAÚDE (Sessão Atual)
$hasPerformanceIssue = false;
foreach ($queriesHistory as $q) {
    if (isset($q['no_index']) && $q['no_index']) {
        $hasPerformanceIssue = true;
        break;
    }
}

// Definição da classe visual do botão de gatilho (Alerta Neon se houver problemas)
$btnClass = $hasPerformanceIssue ? 'btn-sql-alert' : 'btn-sql-normal';
?>

<div class="admin-sql-tracker-wrapper">
    <button type="button" 
            onclick="toggleSQLHub()" 
            class="btn-sql-debug <?php echo $btnClass; ?>" 
            title="SQL Hub: <?php echo $totalQueries; ?> queries na sessão atual.">
        <i class="fas fa-database"></i>
        <span class="sql-badge"><?php echo $totalQueries; ?></span>
    </button>

    <div class="sql-hub-panel" id="sql-hub-root" style="display: none !important;">
        
        <div class="sql-nav-tabs">
            <button type="button" class="sql-tab-btn active" id="btn-tab-live" onclick="switchSQLTab('live')">
                <i class="fas fa-bolt"></i> Sessão Atual
            </button>
            <button type="button" class="sql-tab-btn" id="btn-tab-audit" onclick="switchSQLTab('audit')">
                <i class="fas fa-history"></i> Auditoria AJAX
            </button>
            <button type="button" onclick="toggleSQLHub()" class="btn-close-panel" style="margin-left: auto; background: none; border: none; color: #888; cursor: pointer; padding: 10px;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div id="sql-tab-live" class="sql-tab-content active">
            <div class="sql-summary">
                <span>Total: <strong><?php echo $totalQueries; ?></strong> queries capturadas</span>
                <?php if ($hasPerformanceIssue): ?>
                    <span class="perf-warning"><i class="fas fa-exclamation-circle"></i> Alertas de Índice!</span>
                <?php endif; ?>
            </div>

            <div class="sql-list-scroll">
                <?php if ($totalQueries === 0): ?>
                    <div class="hub-row-empty" style="padding: 20px; text-align: center; color: #666; font-size: 11px;">
                        <i class="fas fa-info-circle"></i> Nenhuma consulta executada nesta requisição.
                    </div>
                <?php else: ?>
                    <table class="sql-debug-table">
                        <thead>
                            <tr>
                                <th style="width: 70px;">Tempo</th>
                                <th>Query / Contexto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($queriesHistory as $data): ?>
                                <tr class="<?php echo ($data['no_index'] || $data['error']) ? 'row-danger' : ''; ?>">
                                    <td class="sql-time">
                                        <?php echo $data['time']; ?> <small>ms</small>
                                    </td>
                                    <td class="sql-code">
                                        <code><?php echo htmlspecialchars(substr($data['sql'], 0, 160)) . (strlen($data['sql']) > 160 ? '...' : ''); ?></code>
                                        
                                        <div style="font-size: 8px; color: #777; margin-top: 4px; display: flex; align-items: center; gap: 5px;">
                                            <i class="fas fa-code-branch"></i> Origem: <span style="color: #aaa;"><?php echo $data['caller'] ?? 'Sistema'; ?></span>
                                        </div>

                                        <?php if ($data['no_index']): ?>
                                            <div class="sql-alert-tag">
                                                <i class="fas fa-running"></i> FULL TABLE SCAN (Otimização Necessária)
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($data['error']): ?>
                                            <div class="sql-error-tag">
                                                <i class="fas fa-bug"></i> Erro: <?php echo htmlspecialchars($data['error']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <div id="sql-tab-audit" class="sql-tab-content">
            <div class="sql-log-toolbar">
                <span style="font-size: 9px; color: #888;">Monitorização AJAX & Histórico Recente</span>
                <button type="button" class="btn-clear-log" onclick="clearSQLAuditLog()">
                    <i class="fas fa-eraser"></i> Limpar Log
                </button>
            </div>

            <div class="sql-list-scroll">
                <?php if (empty($auditLines)): ?>
                    <div class="hub-row-empty" style="padding: 20px; text-align: center; color: #666; font-size: 11px;">
                        <i class="fas fa-folder-open"></i> O arquivo <code>sql_audit.log</code> está vazio.
                    </div>
                <?php else: ?>
                    <table class="sql-debug-table">
                        <thead>
                            <tr>
                                <th style="width: 85px;">ID / Status</th>
                                <th>Análise de Tráfego</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($auditLines as $line): 
                                if (trim($line) === '') continue;
                                
                                // Detecção de criticidade para o destaque Neon (Dica de Ouro)
                                $isCritical = (str_contains($line, '[ALERTA-INDICE]') || str_contains($line, '[ERRO-SQL]'));
                            ?>
                                <tr class="<?php echo $isCritical ? 'log-critical' : ''; ?>">
                                    <td style="border-right: 1px solid rgba(255,255,255,0.05);">
                                        <div class="status-tag" style="font-size: 7px; margin-bottom: 2px;">
                                            <?php 
                                                preg_match('/\[(OK|ALERTA-INDICE|ERRO-SQL)\]/', $line, $matches);
                                                echo $matches[1] ?? 'LOG';
                                            ?>
                                        </div>
                                        <div style="font-size: 8px; font-family: monospace; color: var(--sd-accent);">
                                            #<?php 
                                                preg_match('/\[([a-f0-9]{8})\]/', $line, $matches);
                                                echo $matches[1] ?? '??????';
                                            ?>
                                        </div>
                                    </td>
                                    <td class="sql-code">
                                        <div style="font-size: 8px; color: #999; margin-bottom: 4px;">
                                            <?php 
                                                // Extração inteligente da URL e Duração via Regex
                                                preg_match('/\] (\/[^ ]*) ([0-9\.]+)ms/', $line, $matches);
                                                echo "<strong>REQ:</strong> " . ($matches[1] ?? 'N/A') . " | <strong>DURAÇÃO:</strong> " . ($matches[2] ?? '0') . "ms";
                                            ?>
                                        </div>
                                        <code style="font-size: 9px;"><?php 
                                            $parts = explode('| Query: ', $line);
                                            echo htmlspecialchars(substr($parts[1] ?? 'Não capturada', 0, 150)) . '...'; 
                                        ?></code>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <div class="sql-footer-note" style="padding: 10px; border-top: 1px solid rgba(255,255,255,0.05);">
            <i class="fas fa-info-circle"></i> A auditoria permite rastrear consultas em APIs e chamadas assíncronas feitas via <code>Fetch/AJAX</code>.
        </div>
    </div>
</div>