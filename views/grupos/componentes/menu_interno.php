<?php
/**
 * views/grupos/componentes/menu_interno.php
 * Componente: Menu de Navegação do Grupo.
 * PAPEL: Alternar entre Feed, Membros, Solicitações e Configurações.
 * VERSÃO: 1.0 (Permissões Dinâmicas - SOOC)
 */

// 1. PREPARAÇÃO DE LINKS (Vindo do orquestrador ver.php)
$base_url_grupo = $config['base_path'] . 'grupos/ver/' . $id_grupo;

// Helper para classe ativa
function is_active($current, $tab) {
    return ($current === $tab) ? 'active' : '';
}
?>

<style>
    /* Estilos do Menu de Navegação Interno do Grupo */
    .group-nav {
        border-top: 1px solid #e4e6eb;
        margin-top: 15px;
        display: flex;
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
    }

    .group-nav::-webkit-scrollbar {
        display: none;
    }

    .group-nav-item {
        padding: 15px 20px;
        text-decoration: none !important;
        color: #65676b;
        font-weight: 700;
        font-size: 0.95rem;
        border-bottom: 3px solid transparent;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .group-nav-item:hover {
        background-color: #f2f2f2;
        color: #050505;
    }

    .group-nav-item.active {
        color: #1877f2;
        border-bottom-color: #1877f2;
    }

    /* Badge para sinalizar pedidos pendentes (Dono) */
    .nav-badge-count {
        background-color: #fa3e3e;
        color: #fff;
        font-size: 0.7rem;
        padding: 2px 6px;
        border-radius: 10px;
        margin-left: 4px;
    }

    @media (max-width: 600px) {
        .group-nav-item { padding: 12px 15px; font-size: 0.85rem; }
    }
</style>

<nav class="group-nav">
    
    <a href="<?php echo $base_url_grupo; ?>" class="group-nav-item <?php echo is_active($active_tab, 'feed'); ?>">
        <i class="fas fa-stream"></i> Feed
    </a>

    <?php if ($is_membro || $is_admin): ?>
        <a href="<?php echo $base_url_grupo; ?>?tab=membros" class="group-nav-item <?php echo is_active($active_tab, 'membros'); ?>">
            <i class="fas fa-users"></i> Participantes
        </a>

        <a href="<?php echo $base_url_grupo; ?>?tab=config" class="group-nav-item <?php echo is_active($active_tab, 'config'); ?>">
            <i class="fas fa-cog"></i> Configurações
        </a>
    <?php endif; ?>

    <?php if ($is_dono || $is_admin): ?>
        <a href="<?php echo $base_url_grupo; ?>?tab=solicitacoes" class="group-nav-item <?php echo is_active($active_tab, 'solicitacoes'); ?>">
            <i class="fas fa-user-clock"></i> Solicitações 
            <?php 
                // Exemplo de como colocar um alerta visual se houver pedidos
                // <span class="nav-badge-count">3</span> 
            ?>
        </a>
    <?php endif; ?>

</nav>