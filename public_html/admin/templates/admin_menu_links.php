<?php
/**
 * admin/templates/admin_menu_links.php
 * PAPEL: Links de navegação do painel administrativo.
 * ATUALIZAÇÃO: V3.2 (Integração do Monitor de Erros Sentinela - socialbr.lol)
 */

// 1. CARREGAMENTO DO CÉREBRO (Para o Badge de Notificação)
if (!class_exists('SuporteLogic')) {
    $path_logic = __DIR__ . '/../../../src/SuporteLogic.php';
    if (file_exists($path_logic)) {
        require_once $path_logic;
    }
}

// 2. BUSCA ESTATÍSTICAS PARA O BADGE (Apenas se a conexão existir)
$abertos_count = 0;
if (class_exists('SuporteLogic') && isset($conn)) {
    $stats_admin = SuporteLogic::getStatsAdmin($conn);
    $abertos_count = (int)($stats_admin['abertos'] ?? 0);
}

// 3. BUSCA ERROS PENDENTES PARA O BADGE (Sentinela)
$erros_count = 0;
if (isset($pdo)) {
    try {
        $stmt_erros = $pdo->query("SELECT COUNT(*) FROM Logs_Erros_Sistema WHERE status = 'pendente'");
        $erros_count = (int)$stmt_erros->fetchColumn();
    } catch (Exception $e) {
        $erros_count = 0; // Blindagem contra erro de tabela inexistente
    }
}

// Recupera a sub_rota atual para marcar o menu como ativo
$current_page = $sub_route ?? 'dashboard';
?>
<nav class="sidebar-nav">
    <ul>
        <li class="<?php echo $current_page == 'dashboard' ? 'active' : ''; ?>">
            <a href="<?php echo $config['base_path']; ?>admin/dashboard">
                <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>
        
        <li class="<?php echo $current_page == 'configuracoes' ? 'active' : ''; ?>">
            <a href="<?php echo $config['base_path']; ?>admin/configuracoes">
                <span class="nav-icon"><i class="fas fa-cogs"></i></span>
                <span class="nav-text">Configurações Gerais</span>
            </a>
        </li>

        <li class="<?php echo $current_page == 'menus-rotas' ? 'active' : ''; ?>">
            <a href="<?php echo $config['base_path']; ?>admin/menus-rotas">
                <span class="nav-icon"><i class="fas fa-route"></i></span>
                <span class="nav-text">Menus e Rotas</span>
            </a>
        </li>
        
        <li class="<?php echo $current_page == 'estatisticas' ? 'active' : ''; ?>">
            <a href="<?php echo $config['base_path']; ?>admin/estatisticas">
                <span class="nav-icon"><i class="fas fa-chart-line"></i></span>
                <span class="nav-text">Estatísticas</span>
            </a>
        </li>

        <li class="<?php echo $current_page == 'logs' ? 'active' : ''; ?>">
            <a href="<?php echo $config['base_path']; ?>admin/logs">
                <span class="nav-icon"><i class="fas fa-history"></i></span>
                <span class="nav-text">Logs de Auditoria</span>
            </a>
        </li>

        <li class="<?php echo $current_page == 'erros-sistema' ? 'active' : ''; ?>">
            <a href="<?php echo $config['base_path']; ?>admin/erros-sistema">
                <span class="nav-icon"><i class="fas fa-exclamation-triangle"></i></span>
                <span class="nav-text">Monitor de Erros</span>
                <?php if ($erros_count > 0): ?>
                    <span style="background: #e74c3c; color: #fff; padding: 2px 7px; border-radius: 10px; font-size: 0.7rem; font-weight: bold; margin-left: auto; box-shadow: 0 0 5px rgba(231, 76, 60, 0.4);">
                        <?php echo $erros_count; ?>
                    </span>
                <?php endif; ?>
            </a>
        </li>

        <li class="<?php echo $current_page == 'anotacoes' ? 'active' : ''; ?>">
            <a href="<?php echo $config['base_path']; ?>admin/anotacoes">
                <span class="nav-icon"><i class="fas fa-sticky-note"></i></span>
                <span class="nav-text">Anotações</span>
            </a>
        </li>
        
        <hr>

        <li class="<?php echo $current_page == 'denuncias' ? 'active' : ''; ?>">
            <a href="<?php echo $config['base_path']; ?>admin/denuncias">
                <span class="nav-icon"><i class="fas fa-flag"></i></span>
                <span class="nav-text">Denúncias</span>
            </a>
        </li>

        <li class="<?php echo $current_page == 'suporte' ? 'active' : ''; ?>">
            <a href="<?php echo $config['base_path']; ?>admin/suporte">
                <span class="nav-icon"><i class="fas fa-headset"></i></span>
                <span class="nav-text">Atendimento Suporte</span>
                <?php if ($abertos_count > 0): ?>
                    <span style="background: #e74c3c; color: #fff; padding: 2px 7px; border-radius: 10px; font-size: 0.7rem; font-weight: bold; margin-left: auto; box-shadow: 0 0 5px rgba(231, 76, 60, 0.4);">
                        <?php echo $abertos_count; ?>
                    </span>
                <?php endif; ?>
            </a>
        </li>
        
        <li class="<?php echo $current_page == 'usuarios' ? 'active' : ''; ?>">
            <a href="<?php echo $config['base_path']; ?>admin/usuarios">
                <span class="nav-icon"><i class="fas fa-users-cog"></i></span>
                <span class="nav-text">Gerenciar Usuários</span>
            </a>
        </li>

        <li class="<?php echo $current_page == 'grupos' ? 'active' : ''; ?>">
            <a href="<?php echo $config['base_path']; ?>admin/grupos">
                <span class="nav-icon"><i class="fas fa-users"></i></span>
                <span class="nav-text">Gerenciar Grupos</span>
            </a>
        </li>

        <li class="<?php echo $current_page == 'marketplace' ? 'active' : ''; ?>">
            <a href="<?php echo $config['base_path']; ?>admin/marketplace">
                <span class="nav-icon"><i class="fas fa-store"></i></span>
                <span class="nav-text">Marketplace</span>
            </a>
        </li>

        <li class="<?php echo $current_page == 'postagens' ? 'active' : ''; ?>">
            <a href="<?php echo $config['base_path']; ?>admin/postagens">
                <span class="nav-icon"><i class="fas fa-file-alt"></i></span>
                <span class="nav-text">Gerenciar Postagens</span>
            </a>
        </li>

        <li class="<?php echo $current_page == 'comentarios' ? 'active' : ''; ?>">
            <a href="<?php echo $config['base_path']; ?>admin/comentarios">
                <span class="nav-icon"><i class="fas fa-comments"></i></span>
                <span class="nav-text">Gerenciar Comentários</span>
            </a>
        </li>

        <li class="<?php echo $current_page == 'admin_toasts' ? 'active' : ''; ?>">
            <a href="<?php echo $config['base_path']; ?>admin/admin_toasts">
                <span class="nav-icon"><i class="fas fa-comment-dots"></i></span>
                <span class="nav-text">Gestão de Toasts</span>
            </a>
        </li>

        <li class="<?php echo $current_page == 'chat' ? 'active' : ''; ?>">
            <a href="<?php echo $config['base_path']; ?>admin/chat">
                <span class="nav-icon"><i class="fas fa-comments"></i></span>
                <span class="nav-text">Gestão de Chat</span>
            </a>
        </li>
        
        <li class="<?php echo $current_page == 'links' ? 'active' : ''; ?>">
            <a href="<?php echo $config['base_path']; ?>admin/links">
                <span class="nav-icon"><i class="fas fa-link"></i></span>
                <span class="nav-text">Rastreamento de Links</span>
            </a>
        </li>

        <li class="<?php echo $current_page == 'busca' ? 'active' : ''; ?>">
            <a href="<?php echo $config['base_path']; ?>admin/busca">
                <span class="nav-icon"><i class="fas fa-search"></i></span>
                <span class="nav-text">Inteligência de Busca</span>
            </a>
        </li>

        <li class="<?php echo $current_page == 'Palavras_Proibidas' ? 'active' : ''; ?>">
            <a href="<?php echo $config['base_path']; ?>admin/Palavras_Proibidas">
                <span class="nav-icon"><i class="fas fa-user-slash"></i></span>
                <span class="nav-text">Lista Negra (Censura)</span>
            </a>
        </li>

        <li class="<?php echo $current_page == 'sinonimos' ? 'active' : ''; ?>">
            <a href="<?php echo $config['base_path']; ?>admin/busca_sinonimos">
                <span class="nav-icon"><i class="fas fa-exchange-alt"></i></span>
                <span class="nav-text">Gerenciar Sinônimos</span>
            </a>
        </li>
        
        <li class="nav-separator"></li>
        
        <li>
            <a href="<?php echo $config['base_path']; ?>feed" target="_blank">
                <span class="nav-icon"><i class="fas fa-external-link-alt"></i></span>
                <span class="nav-text">Ver Site</span>
            </a>
        </li>
        
        <li>
            <a href="<?php echo $config['base_path']; ?>api/usuarios/logout.php" class="text-danger">
                <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
                <span class="nav-text">Sair</span>
            </a>
        </li>
    </ul>
</nav>