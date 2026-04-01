<?php
/**
 * views/marketplace/componentes/barra_ferramentas.php
 * Componente: Barra de Ações Superior (Versão Limpa)
 * CSS centralizado em: assets/css/components/_marketplace.css
 */

// Proteção de acesso direto
if (!isset($id_usuario_logado)) {
    exit('Acesso restrito.');
}
?>

<div class="mkt-toolbar">
    <div class="toolbar-content">
        
        <div class="mkt-brand">
            <h2>
                <i class="fas fa-store" style="color: var(--mkt-primary);"></i> 
                Marketplace
            </h2>
        </div>

        <div class="mkt-action-buttons">
            
            <a href="<?php echo $config['base_path']; ?>marketplace/meus-anuncios" 
               class="btn-mkt-secondary" 
               title="Gerenciar meus anúncios">
                <i class="fas fa-box-open"></i> 
                <span class="d-none-mobile">Meus Itens</span>
            </a>

            <a href="<?php echo $config['base_path']; ?>marketplace/vender" 
               class="btn-mkt-primary" 
               title="Criar novo anúncio">
                <i class="fas fa-plus-circle"></i> 
                <span>Vender Algo</span>
            </a>

        </div>
    </div>
</div>