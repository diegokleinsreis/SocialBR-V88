<?php
/**
 * views/perfil/menu_abas.php
 * Componente: Menu de Navegação do Perfil.
 * PAPEL: Alternar entre as abas de conteúdo dinâmico.
 * VERSÃO: V1.1 (Estilos encapsulados em tag STYLE)
 */

// Variáveis recebidas do orquestrador (perfil.php):
// $config['base_path']
// $id_do_perfil_a_exibir
// $id_usuario_logado
// $active_page
?>

<style>
    /* Estilos do Menu de Navegação (Baseados no _profile.css original) */
    .profile-nav {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        margin-bottom: 20px;
        padding: 0 10px;
        display: flex;
        overflow-x: auto; /* Permite scroll horizontal no mobile */
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none; /* Esconde scrollbar no Firefox */
    }

    .profile-nav::-webkit-scrollbar {
        display: none; /* Esconde scrollbar no Chrome/Safari */
    }

    .profile-nav a {
        padding: 15px 20px;
        text-decoration: none;
        color: #65676b;
        font-weight: bold;
        border-bottom: 3px solid transparent;
        transition: all 0.2s ease;
        font-size: 0.95em;
    }

    .profile-nav a:hover {
        background-color: #f0f2f5;
        color: #050505;
    }

    /* Estado da Aba Ativa conforme o design original */
    .profile-nav a.active {
        color: #0c2d54;
        border-bottom-color: #0c2d54;
    }

    /* Ajuste de padding para o container de abas */
    .profile-dynamic-content {
        margin-top: 10px;
    }
</style>

<nav class="profile-nav">
    <a href="<?php echo $config['base_path']; ?>perfil/<?php echo $id_do_perfil_a_exibir; ?>" 
       class="<?php echo ($active_page === 'posts') ? 'active' : ''; ?>">
        Posts
    </a>
    
    <a href="<?php echo $config['base_path']; ?>perfil/<?php echo $id_do_perfil_a_exibir; ?>?tab=sobre" 
       class="<?php echo ($active_page === 'sobre') ? 'active' : ''; ?>">
        Sobre
    </a>
    
    <a href="<?php echo $config['base_path']; ?>perfil/<?php echo $id_do_perfil_a_exibir; ?>?tab=amigos" 
       class="<?php echo ($active_page === 'amigos') ? 'active' : ''; ?>">
        Amigos
    </a>
    
    <a href="<?php echo $config['base_path']; ?>perfil/<?php echo $id_do_perfil_a_exibir; ?>?tab=galeria"
       class="<?php echo ($active_page === 'galeria') ? 'active' : ''; ?>">
        Galeria
    </a>

</nav>