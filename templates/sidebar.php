<?php
/**
 * templates/sidebar.php
 * VERSÃO: V52.0 (Sistema de Menu Retrátil - socialbr.lol)
 * PAPEL: Container da barra lateral com gatilho de expansão/recolhimento.
 * AJUSTE: Adição de controle físico para alternar estados do menu.
 */
?>
<aside class="site-sidebar" id="sidebar-principal">
    
    <div class="sidebar-header-controle">
        <button type="button" id="botao-controle-sidebar" class="btn-sidebar-toggle" title="Recolher/Expandir Menu">
            <i class="fas fa-chevron-left" id="icone-sidebar"></i>
        </button>
    </div>

    <?php include 'menu_links.php'; ?>

</aside>