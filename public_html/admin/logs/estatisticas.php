<?php
/**
 * admin/logs/estatisticas.php
 * PAPEL: Exibir cartões de resumo da auditoria.
 * LOCALIZAÇÃO: Deve estar dentro da pasta 'admin/logs/'
 * VERSÃO: 1.0 (Responsivo - socialbr.lol)
 */

// A variável $stats_hoje já foi carregada no admin_logs.php
?>
<style>
    .logs-stats-container {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .stat-card-mini {
        background: #fff;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        gap: 15px;
        border-left: 4px solid var(--admin-primary);
    }

    .stat-icon-circle {
        width: 40px;
        height: 40px;
        background: #f0f4f8;
        color: var(--admin-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .stat-details h4 {
        margin: 0;
        font-size: 0.75rem;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-details .stat-value {
        font-size: 1.25rem;
        font-weight: 800;
        color: #333;
        display: block;
        margin-top: 2px;
    }

    .stat-details .stat-sub {
        font-size: 0.7rem;
        color: #999;
    }

    /* Ajuste para Mobile */
    @media (max-width: 1100px) {
        .logs-stats-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 480px) {
        .logs-stats-container {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="logs-stats-container">

    <div class="stat-card-mini">
        <div class="stat-icon-circle">
            <i class="fas fa-bolt"></i>
        </div>
        <div class="stat-details">
            <h4>Ações Hoje</h4>
            <span class="stat-value"><?php echo number_format($stats_hoje['total_hoje']); ?></span>
            <span class="stat-sub">Registadas nas últimas 24h</span>
        </div>
    </div>

    <div class="stat-card-mini" style="border-left-color: #28a745;">
        <div class="stat-icon-circle" style="color: #28a745;">
            <i class="fas fa-user-shield"></i>
        </div>
        <div class="stat-details">
            <h4>Top Admin</h4>
            <span class="stat-value">
                <?php 
                    echo $stats_hoje['top_admin'] 
                        ? htmlspecialchars($stats_hoje['top_admin']['nome']) 
                        : '---'; 
                ?>
            </span>
            <span class="stat-sub">
                <?php 
                    echo $stats_hoje['top_admin'] 
                        ? $stats_hoje['top_admin']['total'] . ' ações realizadas' 
                        : 'Nenhuma ação registada'; 
                ?>
            </span>
        </div>
    </div>

    <div class="stat-card-mini" style="border-left-color: #17a2b8;">
        <div class="stat-icon-circle" style="color: #17a2b8;">
            <i class="fas fa-database"></i>
        </div>
        <div class="stat-details">
            <h4>Retenção</h4>
            <span class="stat-value">90 Dias</span>
            <span class="stat-sub">Logs antigos são limpos</span>
        </div>
    </div>

</div>