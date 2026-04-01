<?php
/**
 * admin/logs/tabela.php
 * PAPEL: Visualização dos registos de auditoria com gatilho para modal.
 * LOCALIZAÇÃO: Deve estar dentro da pasta 'admin/logs/'
 * VERSÃO: 1.1 (Com suporte a detalhes completos - socialbr.lol)
 */
?>
<style>
    /* 1. Estilos da Tabela */
    .logs-table { 
        width: 100%; 
        border-collapse: collapse; 
        table-layout: auto;
    }
    
    .logs-table th { 
        background: #f8f9fa; 
        text-align: left; 
        padding: 10px 5px; 
        font-size: 0.7rem; 
        color: #666; 
        text-transform: uppercase;
        border-bottom: 2px solid #eee; 
    }
    
    .logs-table td { 
        padding: 8px 4px; 
        border-bottom: 1px solid #eee; 
        font-size: 0.8rem; 
        vertical-align: middle;
    }

    /* 2. Estilo do Administrador */
    .admin-info-cell { display: flex; align-items: center; gap: 6px; }
    .admin-img-xs { 
        width: 24px; 
        height: 24px; 
        border-radius: 50%; 
        object-fit: cover; 
        flex-shrink: 0;
        background: #eee;
    }
    .admin-name { font-weight: 700; color: #333; line-height: 1; }

    /* 3. Badges de Ação */
    .badge-log {
        padding: 2px 5px;
        border-radius: 4px;
        font-size: 0.65rem;
        font-weight: 700;
        display: inline-block;
        white-space: nowrap;
    }
    /* Cores por tipo de ação */
    .log-grupo { background: #e7f3ff; color: #1877f2; }
    .log-usuario { background: #e6ffed; color: #28a745; }
    .log-post { background: #fff3cd; color: #856404; }
    .log-default { background: #f0f2f5; color: #65676b; }

    /* 4. Botão de Ver Detalhes */
    .view-log-btn {
        background: none;
        border: none;
        color: #1877f2;
        cursor: pointer;
        font-size: 1rem;
        transition: transform 0.2s;
        padding: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .view-log-btn:hover {
        transform: scale(1.2);
        color: #0d6efd;
    }

    /* 5. Responsividade de Esmagamento */
    @media (max-width: 768px) {
        .col-id, .col-detalhes { display: none; }
    }

    @media (max-width: 480px) {
        .col-obj { display: none; }
        .logs-table td { font-size: 0.75rem; padding: 6px 2px; }
        .admin-name { max-width: 60px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    }
</style>

<table class="logs-table">
    <thead>
        <tr>
            <th class="col-id" style="width: 40px;">ID</th>
            <th style="width: 100px;">ADMIN</th>
            <th>AÇÃO</th>
            <th class="col-obj">OBJETO</th>
            <th class="col-detalhes">DETALHES</th>
            <th style="width: 80px;">DATA</th>
            <th style="width: 40px; text-align: center;">VER</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($lista_logs)): ?>
            <tr>
                <td colspan="7" style="text-align:center; padding:30px; color:#888;">Nenhum registo de atividade.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($lista_logs as $log): ?>
            <tr>
                <td class="col-id" style="color:#999;">#<?php echo $log['id']; ?></td>
                <td>
                    <div class="admin-info-cell">
                        <img src="<?php echo $config['base_path'] . ($log['foto_perfil_url'] ?: 'assets/img/default_avatar.png'); ?>" class="admin-img-xs">
                        <span class="admin-name"><?php echo htmlspecialchars(explode(' ', $log['admin_nome'])[0]); ?></span>
                    </div>
                </td>
                <td>
                    <?php 
                        $acao_label = str_replace('_', ' ', $log['acao']);
                        $classe_tipo = 'log-' . ($log['tipo_objeto'] ?? 'default');
                    ?>
                    <span class="badge-log <?php echo $classe_tipo; ?>">
                        <?php echo strtoupper($acao_label); ?>
                    </span>
                </td>
                <td class="col-obj">
                    <span style="opacity: 0.7; font-size: 0.7rem;"><?php echo strtoupper($log['tipo_objeto'] ?? 'SISTEMA'); ?></span>
                    <strong>#<?php echo $log['id_objeto']; ?></strong>
                </td>
                <td class="col-detalhes">
                    <div style="max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #666;" title="<?php echo htmlspecialchars($log['detalhes']); ?>">
                        <?php echo htmlspecialchars($log['detalhes']); ?>
                    </div>
                </td>
                <td style="white-space: nowrap; font-size: 0.7rem; color: #999;">
                    <i class="far fa-clock"></i> <?php echo date('d/m H:i', strtotime($log['data_log'])); ?>
                </td>
                <td style="text-align: center;">
                    <button class="view-log-btn" data-log-id="<?php echo $log['id']; ?>" title="Ver detalhes completos">
                        <i class="fas fa-search-plus"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>