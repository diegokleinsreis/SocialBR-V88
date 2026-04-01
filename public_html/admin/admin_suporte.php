<?php
/**
 * admin/admin_suporte.php
 * ORQUESTRADOR DE ATENDIMENTO (V1.7)
 * PAPEL: Gerir o layout fixo "App-Like" para o suporte administrativo.
 * AJUSTE: Remoção do cabeçalho de página para ganho de espaço vertical.
 * VERSÃO: 1.7 - socialbr.lol
 */

// 1. DEFINIÇÕES DE ACESSO E SEGURANÇA
if (!defined('ACESSO_ROTEADOR')) {
    die("Acesso direto não permitido.");
}

// 2. CARREGAMENTO DO CÉREBRO
require_once __DIR__ . '/../../src/SuporteLogic.php';

// 3. CAPTURA DE ESTADO
$chamado_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$page_title = $chamado_id > 0 ? "Atendimento #$chamado_id" : "Centro de Chamados";

// 4. INCLUSÃO DO HEADER ADMINISTRATIVO
include __DIR__ . '/templates/admin_header.php';
?>

<div class="admin-main-wrapper" style="display: flex; height: calc(100vh - 60px); width: 100%; overflow: hidden;">
    
    <?php include __DIR__ . '/templates/admin_sidebar.php'; ?>

    <main class="admin-content" style="flex: 1; display: flex; flex-direction: column; height: 100%; padding: 15px; overflow: hidden; background: #f4f7f6;">
        
        <div class="admin-card-atomo" style="flex: 1; background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); display: flex; flex-direction: column; overflow: hidden; border: 1px solid #e0e0e0; min-height: 0;">
            <?php
                if ($chamado_id > 0) {
                    // Interface de Chat
                    include __DIR__ . '/suporte/atendimento_chat.php';
                } else {
                    // Interface de Listagem
                    include __DIR__ . '/suporte/tabela_tickets.php';
                }
            ?>
        </div>
    </main>
</div>

<style>
/* Reset estrutural para congelar o scroll do body */
body { overflow: hidden !important; height: 100vh; margin: 0; }

.admin-main-wrapper *, 
.admin-main-wrapper *:before, 
.admin-main-wrapper *:after {
    box-sizing: border-box;
}

/* Ajustes para Mobile */
@media (max-width: 768px) {
    .admin-main-wrapper { height: auto !important; overflow: visible !important; display: block !important; }
    body { overflow-y: auto !important; height: auto !important; }
    .admin-content { height: auto !important; padding: 10px !important; }
    .admin-card-atomo { min-height: 600px; border-radius: 0; }
}
</style>

<div class="lightbox-overlay is-hidden" id="lightbox-overlay">
    <div class="lightbox-modal" id="lightbox-modal">
        <button class="lightbox-close-btn" id="lightbox-close-btn" title="Fechar">&times;</button>
        
        <a href="#" class="lightbox-download-btn is-hidden" id="lightbox-download-btn" download title="Baixar">
            <i class="fas fa-download"></i>
        </a>

        <div class="lightbox-content">
            <div class="lightbox-image-column">
                <div class="lightbox-image-wrapper">
                    <div class="spinner"></div>
                </div>
            </div>
            <div class="lightbox-details-column"></div>
        </div>
    </div>
</div>

<script src="<?php echo $config['base_path']; ?>admin/assets/js/suporte_admin.js"></script>

</body>
</html>