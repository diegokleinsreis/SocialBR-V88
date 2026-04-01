<?php
/**
 * views/marketplace/componentes/menu_lateral.php
 * Componente: Menu Lateral Social (Desktop)
 * Integra o menu principal da rede social dentro do layout do Marketplace.
 */

// Proteção básica para garantir que o arquivo seja chamado pelo maestro
if (!isset($id_usuario_logado)) {
    exit('Acesso restrito.');
}

/**
 * DICA DE OURO: PERSISTÊNCIA DE ESTADO ATIVO
 * Aqui garantimos que o CSS entenda que estamos na seção Marketplace
 * mesmo navegando por sub-páginas de detalhes ou criação.
 */
?>

<div class="mkt-sidebar-wrapper">
    <?php 
        // Definimos uma variável de controle para o menu_links.php saber que deve destacar o Marketplace
        $aba_ativa_global = 'marketplace'; 
        
        // Inclui o sidebar original que contém o menu_links.php
        include __DIR__ . '/../../../templates/sidebar.php'; 
    ?>
</div>

<style>
/* Ajustes para que o menu lateral social caiba perfeitamente 
   dentro da coluna do Marketplace sem quebrar o layout premium.
*/
.mkt-sidebar-wrapper .site-sidebar {
    width: 100% !important; /* Força o menu a ocupar toda a largura da coluna lateral */
    position: static !important; /* Remove fixação para respeitar o grid do Marketplace */
    box-shadow: var(--mkt-shadow);
    border-radius: var(--mkt-radius);
    overflow: hidden;
    background: #fff;
}

/* Garante que os links tenham o estilo do site mas respeitem o Dark Mode do Marketplace */
.mkt-sidebar-wrapper .nav-link-dropdown, 
.mkt-sidebar-wrapper .nav-text {
    font-size: 0.95rem;
    font-weight: 600;
}

/* Destaque para o item Marketplace no menu lateral */
.mkt-sidebar-wrapper .sidebar-nav ul li a[href*="marketplace"] {
    background-color: #e7f3ff;
    color: var(--mkt-primary);
    border-radius: 8px;
}

.mkt-sidebar-wrapper .sidebar-nav ul li a[href*="marketplace"] i {
    color: var(--mkt-primary);
}
</style>