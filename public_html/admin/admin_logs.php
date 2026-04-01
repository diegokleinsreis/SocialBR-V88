<?php
/**
 * admin/admin_logs.php
 * PAPEL: Orquestrador do Módulo de Auditoria (Logs).
 * VERSÃO: 1.1 (Com suporte a Modal de Detalhes - socialbr.lol)
 */

// 1. SEGURANÇA E DEPENDÊNCIAS
require_once 'admin_auth.php'; // Já carrega o LogsLogic e a conexão

// 2. PARÂMETROS DE FILTRO E PAGINAÇÃO
$pagina_atual = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$limite = 20;
$offset = ($pagina_atual - 1) * $limite;
$filtro_tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';

// 3. BUSCA DE DADOS VIA LOGSLOGIC
$lista_logs = LogsLogic::listarLogs($conn, $limite, $offset, $filtro_tipo);
$total_logs = LogsLogic::contarTotal($conn, $filtro_tipo);
$total_paginas = ceil($total_logs / $limite);
$stats_hoje = LogsLogic::getStatsHoje($conn);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Auditoria de Sistema - Painel Admin</title>
    
    <link rel="stylesheet" href="assets/css/admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root { --admin-primary: #0C2D54; }
        
        .admin-main-content { 
            padding: 15px !important; 
            max-width: 100vw; 
            overflow-x: hidden; 
        }

        /* Layout em Grid Responsivo */
        .logs-dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 280px;
            gap: 20px;
        }

        /* Wrapper da Tabela (Proteção Mobile) */
        .table-responsive-wrapper {
            width: 100%;
            overflow-x: auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            -webkit-overflow-scrolling: touch;
            margin-bottom: 20px;
        }

        /* Responsividade */
        @media (max-width: 1100px) {
            .logs-dashboard-grid { grid-template-columns: 1fr; }
            .logs-sidebar { order: -1; } 
        }

        @media (max-width: 600px) {
            .admin-main-content { padding: 8px !important; }
            .admin-card h1 { font-size: 1.2rem; }
        }

        /* Paginação */
        .admin-pagination { 
            display: flex; gap: 5px; justify-content: center; margin: 20px 0; flex-wrap: wrap; 
        }
        .admin-pagination a { 
            padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px;
            text-decoration: none; color: var(--admin-primary); background: #fff; font-size: 0.85rem;
        }
        .admin-pagination a.active { background: var(--admin-primary); color: #fff; border-color: var(--admin-primary); }

        /* Estilos específicos para o Modal de Logs */
        #logConteudo .log-detail-item { margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #f0f0f0; }
        #logConteudo .log-detail-label { font-size: 0.75rem; color: #888; text-transform: uppercase; font-weight: bold; display: block; }
        #logConteudo .log-detail-value { font-size: 0.95rem; color: #333; display: block; margin-top: 4px; }
        #logConteudo .log-full-text { background: #f9f9f9; padding: 10px; border-radius: 5px; border: 1px inset #eee; font-family: monospace; white-space: pre-wrap; }
    </style>
</head>
<body>

    <?php include 'templates/admin_header.php'; ?>
    <?php include 'templates/admin_mobile_nav.php'; ?>

    <main class="admin-main-content">
        <a href="dashboard" class="admin-back-button"><i class="fas fa-arrow-left"></i> Voltar</a>
        
        <div class="admin-card" style="margin-bottom: 20px;">
            <h1><i class="fas fa-history"></i> Logs de Auditoria</h1>
            <p>Histórico completo de ações realizadas por administradores.</p>
        </div>

        <div class="logs-dashboard-grid">
            
            <div class="logs-main-column">
                
                <?php // include 'logs/busca.php'; ?>

                <div class="table-responsive-wrapper">
                    <?php include 'logs/tabela.php'; ?>
                </div>

                <?php if ($total_paginas > 1): ?>
                <div class="admin-pagination">
                    <?php for($i=1; $i<=$total_paginas; $i++): ?>
                        <?php if($i == 1 || $i == $total_paginas || ($i >= $pagina_atual - 2 && $i <= $pagina_atual + 2)): ?>
                            <a href="?sub_route=logs&p=<?php echo $i; ?>&tipo=<?php echo urlencode($filtro_tipo); ?>" 
                               class="<?php echo $pagina_atual == $i ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>

            <aside class="logs-sidebar">
                <?php include 'logs/estatisticas.php'; ?>
            </aside>

        </div>
    </main>

    <div id="logDetalhesModal" class="admin-modal" style="display: none;">
        <div class="admin-modal-content">
            <div class="admin-modal-header">
                <h3><i class="fas fa-info-circle"></i> Detalhes da Auditoria</h3>
                <span class="admin-modal-close" onclick="document.getElementById('logDetalhesModal').style.display='none'">&times;</span>
            </div>
            <div id="logConteudo" class="admin-modal-body">
                <p>Carregando dados...</p>
            </div>
        </div>
    </div>

    </body>
</html>