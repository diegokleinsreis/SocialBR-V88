<?php
/**
 * templates/mobile_nav.php
 * Painel de Navegação Lateral para Dispositivos Móveis.
 * VERSÃO: V52.0 (Sincronia com Chat e Roteamento - socialbr.lol)
 */

// --- CORREÇÃO DE ARQUITETURA ---
// Garantimos que $config (com $config['base_path']) esteja disponível.
if (!isset($config)) {
    // O index.php já carrega, mas isto é uma garantia.
    // Caminho sobe 2 níveis: /templates -> / (raiz V51.1)
    require_once __DIR__ . '/../config/database.php';
}
// ---------------------------------
?>

<div class="overlay" id="overlay"></div>

<aside class="mobile-nav-panel" id="mobile-nav-panel">
    <button class="close-btn" id="close-mobile-menu" aria-label="Fechar menu">&times;</button>
    
    <?php 
    // Inclui os links do menu principal (que agora contém o Botão de Chat V52.0)
    include 'menu_links.php'; 
    ?>

    <div id="notifications-panel-mobile" class="notifications-panel is-hidden">
        <div class="notifications-header">
            <h3>Notificações</h3>
        </div>
        
        <div class="notifications-list">
            </div>

        <div class="notifications-footer" style="padding: 15px; border-top: 1px solid #f0f2f5; background: #fff; position: sticky; bottom: 0; z-index: 10;">
            <a href="<?php echo $config['base_path']; ?>historico_notificacoes" 
               class="primary-btn" 
               style="display: block; text-align: center; width: 100%; text-decoration: none;">
                Ver todas as notificações
            </a>
        </div>
    </div>
</aside>