<?php
/**
 * templates/menu_links.php
 * Componente: Links de Navegação Principal Dinâmicos.
 * VERSÃO: V88.6 (Header Migration Cleanup & Admin Bypass - socialbr.lol)
 * PAPEL: Renderizar o menu lateral limpo (Sem notificações) com escudo de clique.
 * MUDANÇA: Bloco A removido (Notificações migradas para o Header).
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Dependências de Dados
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/RotasLogic.php';

$motorRotas = new RotasLogic($pdo);
$itensMenu = $motorRotas->obterLinksMenu(); 

// 2. Dados do Usuário e Estado do Sistema
$user_id_sidebar = $_SESSION['user_id'] ?? 0;
$user_role_sidebar = $_SESSION['user_role'] ?? 'visitante';
$user_nome_sidebar = 'Visitante';
$avatar_url_sidebar = $config['base_path'] . 'assets/images/default-avatar.png';
$agora = time();

if ($user_id_sidebar > 0) {
    $sql_u = "SELECT nome, sobrenome, foto_perfil_url FROM Usuarios WHERE id = ?";
    $stmt_u = $pdo->prepare($sql_u);
    $stmt_u->execute([$user_id_sidebar]);
    if ($u = $stmt_u->fetch()) {
        $user_nome_sidebar = $u['nome'] . ' ' . $u['sobrenome'];
        if (!empty($u['foto_perfil_url'])) {
            $avatar_url_sidebar = $config['base_path'] . htmlspecialchars($u['foto_perfil_url']);
        }
    }
}
?>

<nav class="sidebar-nav">
    <ul>
        <?php foreach ($itensMenu as $item): ?>
            
            <?php 
            /**
             * 3. Lógica de Estado e Bypass de Admin
             */
            $esta_bloqueado = $item['is_bloqueado'] ?? false;
            
            // Garantia de Arquiteto: Admin nunca é bloqueado
            if ($user_role_sidebar === 'admin') {
                $esta_bloqueado = false;
            }

            $classe_estado = $esta_bloqueado ? 'nav-item-disabled' : '';
            $data_liberacao = !empty($item['liberacao_em']) ? strtotime($item['liberacao_em']) : null;
            $attr_liberacao = $data_liberacao ? 'data-liberacao="' . $item['liberacao_em'] . '"' : '';
            
            // CLICK SHIELD: Link morto para usuários se o módulo estiver bloqueado
            $link_final = $esta_bloqueado ? "javascript:void(0)" : $config['base_path'] . $item['slug'];

            // Texto de ajuda (Tooltip)
            $tooltip = $item['label'];
            if ($esta_bloqueado) {
                $tooltip = (isset($item['manutencao_modulo']) && $item['manutencao_modulo'] == 1) 
                           ? "Módulo em Manutenção" 
                           : "Disponível em breve!";
            }

            /**
             * DIVISORES VISUAIS (HR)
             */
            if ($item['slug'] === 'grupos' || $item['slug'] === 'suporte') echo '<hr>'; 
            ?>

            <?php 
            /**
             * BLOCO B: ITENS COM SUBMENUS (Configurações / Administrativo)
             */
            if (!empty($item['submenus']) || $item['slug'] === 'configurar_perfil'): ?>
                <li class="nav-dropdown-toggle <?php echo $classe_estado; ?>">
                    <a href="javascript:void(0)" class="nav-link-dropdown <?php echo ($item['slug'] === 'configurar_perfil') ? 'config-dropdown-toggle' : ''; ?>" 
                       title="<?php echo $tooltip; ?>" <?php echo $attr_liberacao; ?>>
                        <span class="nav-icon"><i class="<?php echo $item['icone']; ?>"></i></span>
                        <span class="nav-text">
                            <span class="label-texto"><?php echo ($item['slug'] === 'configurar_perfil') ? 'Configurações' : $item['label']; ?></span>
                            
                            <?php if($esta_bloqueado): ?>
                                <i class="fas fa-lock lock-indicator"></i>
                            <?php elseif($data_liberacao && $data_liberacao > $agora): ?>
                                <span class="countdown-label">Carregando...</span>
                            <?php endif; ?>
                        </span>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </a>
                </li>
                
                <ul class="nav-submenu <?php echo ($item['slug'] === 'configurar_perfil') ? 'config-submenu' : $item['slug'] . '-submenu'; ?> is-hidden">
                    
                    <?php if ($item['slug'] === 'configurar_perfil'): ?>
                        <li>
                            <a href="<?php echo $config['base_path']; ?>configurar_perfil">
                                <span class="nav-icon"><i class="fas fa-level-up-alt fa-rotate-90" style="font-size: 0.7rem; opacity: 0.6;"></i></span>
                                <span class="nav-text">Configurações Gerais</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php foreach ($item['submenus'] as $sub): 
                        $sub_bloqueado = $sub['is_bloqueado'] ?? false;
                        if ($user_role_sidebar === 'admin') $sub_bloqueado = false;
                        
                        $sub_link_final = $sub_bloqueado ? "javascript:void(0)" : $config['base_path'] . $sub['slug'];
                        $sub_classe = $sub_bloqueado ? 'nav-item-disabled' : '';
                    ?>
                        <li class="<?php echo $sub_classe; ?>">
                            <a href="<?php echo $sub_link_final; ?>">
                                <span class="nav-icon"><i class="fas fa-level-up-alt fa-rotate-90" style="font-size: 0.7rem; opacity: 0.6;"></i></span>
                                <span class="nav-text">
                                    <?php echo $sub['label']; ?>
                                    <?php if($sub_bloqueado): ?> <i class="fas fa-lock lock-indicator"></i> <?php endif; ?>
                                </span>
                            </a>
                        </li>
                    <?php endforeach; ?>

                    <?php if ($item['slug'] === 'configurar_perfil'): ?>
                        <li>
                            <a href="#" class="theme-toggle-link theme-toggle-btn-menu">
                                <span class="nav-icon"><i class="fas fa-level-up-alt fa-rotate-90" style="font-size: 0.7rem; opacity: 0.6;"></i></span>
                                <span class="nav-text">
                                    <span class="theme-text-light">Modo Escuro</span>
                                    <span class="theme-text-dark">Modo Claro</span>
                                </span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

            <?php else: ?>
                <?php /** BLOCO C: ITENS SIMPLES **/ ?>
                <li class="<?php echo $classe_estado; ?>">
                    <a href="<?php echo $link_final; ?>" 
                       <?php echo ($item['slug'] === 'admin') ? 'target="_blank"' : ''; ?> 
                       title="<?php echo $tooltip; ?>"
                       <?php echo $attr_liberacao; ?>>
                        
                        <span class="nav-icon <?php echo ($item['slug'] === 'perfil') ? 'nav-avatar' : ''; ?>">
                            <?php if ($item['slug'] === 'perfil'): ?>
                                <img src="<?php echo $avatar_url_sidebar; ?>" alt="Avatar">
                            <?php else: ?>
                                <i class="<?php echo $item['icone']; ?>"></i>
                                <?php if ($item['slug'] === 'chat'): ?>
                                    <span class="chat-count" id="menu-chat-badge" style="display: none;">0</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </span>
                        <span class="nav-text">
                            <span class="label-texto"><?php echo ($item['slug'] === 'perfil') ? htmlspecialchars($user_nome_sidebar) : $item['label']; ?></span>
                            
                            <?php if($esta_bloqueado): ?>
                                <i class="fas fa-lock lock-indicator"></i>
                            <?php elseif($data_liberacao && $data_liberacao > $agora): ?>
                                <span class="countdown-label">Carregando...</span>
                            <?php endif; ?>
                        </span>
                    </a>
                </li>
            <?php endif; ?>

        <?php endforeach; ?>

        <li>
            <a href="<?php echo $config['base_path']; ?>api/usuarios/logout.php" title="Sair da Conta">
                <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
                <span class="nav-text">Sair</span>
            </a>
        </li>
    </ul>
</nav>