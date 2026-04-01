<?php
/**
 * admin/menus/rastreador_cliques.php
 * Componente: Monitor de Tentativas de Acesso e Erros de Rota.
 * VERSÃO: 1.3 (Cleanup Integration - socialbr.lol)
 */

// Busca as últimas 10 tentativas de acesso negado com detalhes de quem e de onde
$sql_cliques = "SELECT l.*, u.nome, u.sobrenome 
                FROM Logs_Acessos_Negados l
                LEFT JOIN Usuarios u ON l.usuario_id = u.id 
                ORDER BY l.data_tentativa DESC LIMIT 10";

try {
    $stmt_cliques = $pdo->query($sql_cliques);
    $cliques_mortos = $stmt_cliques->fetchAll();
} catch (Exception $e) {
    $cliques_mortos = []; 
}
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h5 class="mb-0" style="font-weight: 600; font-size: 1rem; color: #0C2D54;">
                <i class="fas fa-mouse-pointer me-2 text-warning"></i>Rastreador de Cliques Mortos
            </h5>
            
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="d-flex gap-2 flex-wrap" style="font-size: 0.75rem;">
                    <span class="badge bg-dark" title="A URL digitada não existe no banco de dados">404: Não Encontrado</span>
                    <span class="badge bg-danger" title="O utilizador tentou entrar onde não tem permissão">403: Proibido</span>
                    <span class="badge bg-warning text-dark" title="O módulo está com o interruptor de manutenção ligado">503: Manutenção</span>
                </div>

                <?php if (!empty($cliques_mortos)): ?>
                    <button id="btnLimparLogs" class="btn btn-sm btn-outline-danger fw-bold px-3 shadow-sm" style="font-size: 0.7rem; border-width: 2px;">
                        <i class="fas fa-trash-alt me-1"></i> LIMPAR HISTÓRICO
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="text-muted" style="font-size: 0.8rem;">
                    <tr>
                        <th>URL/Slug Tentado</th>
                        <th class="text-center">Erro</th>
                        <th>Utilizador</th>
                        <th class="d-none d-lg-table-cell">Endereço IP</th>
                        <th class="d-none d-xl-table-cell">Dispositivo</th>
                        <th class="text-end">Data/Hora</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.85rem;">
                    <?php if (!empty($cliques_mortos)): ?>
                        <?php foreach ($cliques_mortos as $clique): ?>
                            <tr>
                                <td>
                                    <code class="text-danger" title="<?php echo htmlspecialchars($clique['slug_tentado']); ?>">
                                        /<?php echo htmlspecialchars(substr($clique['slug_tentado'], 0, 30)) . (strlen($clique['slug_tentado']) > 30 ? '...' : ''); ?>
                                    </code>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    $cor_erro = 'bg-secondary';
                                    if ($clique['erro_codigo'] == 404) $cor_erro = 'bg-dark';
                                    if ($clique['erro_codigo'] == 403) $cor_erro = 'bg-danger';
                                    if ($clique['erro_codigo'] == 503) $cor_erro = 'bg-warning text-dark';
                                    ?>
                                    <span class="badge <?php echo $cor_erro; ?>">
                                        <?php echo $clique['erro_codigo']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $clique['usuario_id'] ? htmlspecialchars($clique['nome'] . ' ' . $clique['sobrenome']) : '<span class="text-muted">Visitante</span>'; ?>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <small class="text-muted font-monospace"><?php echo $clique['ip_endereco'] ?? '0.0.0.0'; ?></small>
                                </td>
                                <td class="d-none d-xl-table-cell">
                                    <small class="text-muted" title="<?php echo htmlspecialchars($clique['user_agent']); ?>">
                                        <i class="fas <?php echo (strpos(strtolower($clique['user_agent']), 'mobile') !== false) ? 'fa-mobile-alt' : 'fa-desktop'; ?> me-1"></i>
                                        <?php echo substr($clique['user_agent'], 0, 20); ?>...
                                    </small>
                                </td>
                                <td class="text-end text-muted">
                                    <?php echo date('d/m H:i', strtotime($clique['data_tentativa'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-check-circle text-success d-block mb-2 fa-2x"></i>
                                Nenhum "clique morto" detetado recentemente.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-0 text-center pb-3">
        <small class="text-muted">
            <i class="fas fa-shield-alt me-1 text-primary"></i> 
            Este monitor de inteligência rastreia falhas de navegação e tentativas de acesso não autorizadas em tempo real, permitindo a correção proativa de links e o monitoramento da integridade de segurança do ecossistema.
        </small>
    </div>
</div>