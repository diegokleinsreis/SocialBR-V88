<?php
// 1. LÓGICA DE LOGIN E DETERMINAÇÃO DE PERFIL
$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$id_usuario_logado = $is_logged_in ? (int)$_SESSION['user_id'] : 0;

$id_do_perfil_a_exibir = $_GET['id'] ?? 0;
if (empty($id_do_perfil_a_exibir) && $is_logged_in) {
    $id_do_perfil_a_exibir = $id_usuario_logado;
}
$id_do_perfil_a_exibir = (int)$id_do_perfil_a_exibir;

if ($id_do_perfil_a_exibir <= 0) {
    if (!$is_logged_in) {
        header("Location: " . $config['base_path'] . "login");
        exit();
    }
    header("Location: " . $config['base_path'] . "pagina-nao-encontrada");
    exit();
}

$active_page = $_GET['tab'] ?? 'posts';

if (!$is_logged_in) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
}

// 3. INCLUI OS "CÉREBROS" (LÓGICA)
require_once __DIR__ . '/../src/UserLogic.php';
require_once __DIR__ . '/../src/PostLogic.php';

// --- VERIFICAÇÃO DE BLOQUEIO ---
$eu_bloqueie = false;
$fui_bloqueado = false;

if ($is_logged_in && $id_do_perfil_a_exibir != $id_usuario_logado) {
    $stmtBlock = $conn->prepare("SELECT id FROM Bloqueios WHERE bloqueador_id = ? AND bloqueado_id = ?");
    $stmtBlock->bind_param("ii", $id_usuario_logado, $id_do_perfil_a_exibir);
    $stmtBlock->execute();
    if ($stmtBlock->get_result()->num_rows > 0) {
        $eu_bloqueie = true;
    }
    $stmtBlock->close();

    if (!$eu_bloqueie) {
        $stmtBlockMe = $conn->prepare("SELECT id FROM Bloqueios WHERE bloqueador_id = ? AND bloqueado_id = ?");
        $stmtBlockMe->bind_param("ii", $id_do_perfil_a_exibir, $id_usuario_logado);
        $stmtBlockMe->execute();
        if ($stmtBlockMe->get_result()->num_rows > 0) {
            $fui_bloqueado = true;
        }
        $stmtBlockMe->close();
    }
}

if ($fui_bloqueado) {
    header("Location: " . $config['base_path'] . "pagina-nao-encontrada");
    exit();
}

// 4. BUSCA DADOS DO CABEÇALHO DO PERFIL
$headerData = UserLogic::getProfileHeaderData($conn, $id_do_perfil_a_exibir, $id_usuario_logado);

if ($headerData === null) {
    header("Location: " . $config['base_path'] . "pagina-nao-encontrada");
    exit();
}

$perfil_data = $headerData['perfil_data'];
$amizade_details = $headerData['amizade_details'];
$pode_ver_conteudo = $headerData['pode_ver_conteudo'];

if ($eu_bloqueie) {
    $pode_ver_conteudo = false;
    $status_amizade = 'bloqueado';
} else {
    $status_amizade = $amizade_details['status_amizade'];
}

$amizade_id = $amizade_details['amizade_id'];
$id_remetente_pedido = $amizade_details['id_remetente_pedido'];
$sao_amigos = $amizade_details['sao_amigos'];

// 6. DEFINE O TÍTULO DA PÁGINA
$page_title = htmlspecialchars($perfil_data['nome'] . ' ' . $perfil_data['sobrenome']);

// 7. INICIALIZA VARIÁVEIS DE CONTEÚDO
$posts_para_exibir = [];
$lista_amigos = [];
$galeria_midia = []; // Nova variável
$pode_ver_lista_amigos = false;

// 8. BUSCA DADOS DA ABA ATIVA
if ($is_logged_in && $pode_ver_conteudo && !$eu_bloqueie) {
    switch ($active_page) {
        case 'sobre':
            break;

        case 'amigos':
            $friendsData = UserLogic::getFriendsPageData($conn, $id_do_perfil_a_exibir, $id_usuario_logado, $perfil_data, $sao_amigos);
            $pode_ver_lista_amigos = $friendsData['pode_ver_lista_amigos'];
            $lista_amigos = $friendsData['lista_amigos'];
            break;

        case 'galeria':
            // --- [NOVO] BUSCA A GALERIA ---
            $galeria_midia = PostLogic::getGalleryMedia($conn, $id_do_perfil_a_exibir);
            break;

        case 'salvos':
            if ($id_do_perfil_a_exibir == $id_usuario_logado) {
                $posts_para_exibir = PostLogic::getSavedPosts($conn, $id_usuario_logado);
            }
            break;

        case 'posts':
        default:
            $posts_para_exibir = PostLogic::getPostsForProfile($conn, $id_usuario_logado, $id_do_perfil_a_exibir);
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <?php include '../templates/head_common.php'; ?>
    <style>
        .friends-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; }
        .friend-card { background: var(--cor-fundo-card); border-radius: 8px; box-shadow: var(--sombra-padrao); text-align: center; padding: 15px; }
        .friend-card a { text-decoration: none; color: var(--cor-texto-primaria); }
        .friend-avatar { width: 80px; height: 80px; border-radius: 50%; margin: 0 auto 10px; object-fit: cover; }
        .friend-name { font-weight: 600; font-size: 0.9em; margin: 0; }
        .friend-username { font-size: 0.8em; color: var(--cor-texto-secundaria); margin: 2px 0 0 0; }
    </style>
</head>
<body>
    
    <?php if ($is_logged_in): ?>
        
        <?php include '../templates/header.php'; ?>
        <?php include '../templates/mobile_nav.php'; ?>
        <div class="main-content-area">
            <?php include '../templates/sidebar.php'; ?>
            <main class="profile-main-content">
                
                <?php include '../templates/profile_header_template.php'; ?>

                <?php if ($eu_bloqueie): ?>
                    <div class="post-card private-profile-card" style="border: 1px solid #d32f2f;">
                        <i class="fas fa-ban" style="color: #d32f2f;"></i>
                        <h3 style="color: #d32f2f;">Você bloqueou este usuário</h3>
                        <p>Você não pode ver as publicações ou informações de <?php echo htmlspecialchars($perfil_data['nome']); ?> enquanto ele estiver bloqueado.</p>
                        <br>
                        <button class="primary-btn bloquear-usuario-btn" data-usuario-id="<?php echo $id_do_perfil_a_exibir; ?>" data-acao="desbloquear" style="background-color: #606770;">Desbloquear Usuário</button>
                    </div>
                <?php else: ?>

                    <nav class="profile-nav">
                        <a href="<?php echo $config['base_path']; ?>perfil/<?php echo $id_do_perfil_a_exibir; ?>" 
                           class="<?php echo ($active_page === 'posts') ? 'active' : ''; ?>">Posts</a>
                        
                        <a href="<?php echo $config['base_path']; ?>perfil/<?php echo $id_do_perfil_a_exibir; ?>?tab=sobre" 
                           class="<?php echo ($active_page === 'sobre') ? 'active' : ''; ?>">Sobre</a>
                        
                        <a href="<?php echo $config['base_path']; ?>perfil/<?php echo $id_do_perfil_a_exibir; ?>?tab=amigos" 
                           class="<?php echo ($active_page === 'amigos') ? 'active' : ''; ?>">Amigos</a>
                        
                        <a href="<?php echo $config['base_path']; ?>perfil/<?php echo $id_do_perfil_a_exibir; ?>?tab=galeria"
                           class="<?php echo ($active_page === 'galeria') ? 'active' : ''; ?>">Galeria</a>

                        <?php if ($id_do_perfil_a_exibir == $id_usuario_logado): ?>
                            <a href="<?php echo $config['base_path']; ?>perfil/<?php echo $id_do_perfil_a_exibir; ?>?tab=salvos"
                               class="<?php echo ($active_page === 'salvos') ? 'active' : ''; ?>">Salvos</a>
                        <?php endif; ?>
                    </nav>

                    <?php if ($perfil_data['status'] === 'suspenso'): ?>
                        <div class="post-card">
                            <h2 style="text-align: center; color: #8a1717;">Esta conta está suspensa.</h2>
                        </div>
                    <?php elseif (!$pode_ver_conteudo): ?>
                        <div class="post-card private-profile-card">
                            <i class="fas fa-lock"></i>
                            <h3>Este perfil é privado</h3>
                            <p>Adicione <?php echo htmlspecialchars($perfil_data['nome']); ?> como amigo para ver as suas publicações e informações.</p>
                        </div>
                    <?php else: ?>
                        
                        <?php switch ($active_page):
                            case 'sobre': ?>
                                <?php if ($id_do_perfil_a_exibir == $id_usuario_logado): ?>
                                    <div class="profile-actions-card" style="margin-bottom: 20px; display: flex; justify-content: flex-end;">
                                        <a href="<?php echo $config['base_path']; ?>configurar_perfil" class="primary-btn-small" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($perfil_data['biografia'])): ?>
                                <div class="profile-details-card">
                                    <h3><i class="fas fa-info-circle"></i> Biografia</h3>
                                    <p class="profile-bio" style="font-size: 1em; padding: 5px;"><?php echo nl2br(htmlspecialchars($perfil_data['biografia'])); ?></p>
                                </div>
                                <?php endif; ?>

                                <div class="profile-details-card">
                                    <h3>Informações de <?php echo htmlspecialchars($perfil_data['nome']); ?></h3>
                                    <div class="info-item"><i class="fas fa-user"></i><label>Nome Completo</label><span><?php echo htmlspecialchars($perfil_data['nome'] . ' ' . $perfil_data['sobrenome']); ?></span></div>
                                    <div class="info-item"><i class="fas fa-at"></i><label>Nome de Usuário</label><span>@<?php echo htmlspecialchars($perfil_data['nome_de_usuario']); ?></span></div>
                                    <div class="info-item"><i class="fas fa-envelope"></i><label>E-mail</label><span><?php echo htmlspecialchars($perfil_data['email']); ?></span></div>
                                    <?php if (!empty($perfil_data['nome_bairro'])): ?>
                                    <div class="info-item"><i class="fas fa-map-marker-alt"></i><label>Localização</label><span><?php echo htmlspecialchars($perfil_data['nome_bairro'] . ', ' . $perfil_data['nome_cidade'] . ' - ' . $perfil_data['sigla_estado']); ?></span></div>
                                    <?php endif; ?>
                                    <div class="info-item"><i class="fas fa-birthday-cake"></i><label>Data de Nascimento</label><span><?php echo date("d/m/Y", strtotime($perfil_data['data_nascimento'])); ?></span></div>
                                    <div class="info-item"><i class="fas fa-calendar-alt"></i><label>Membro desde</label><span><?php echo date("d/m/Y", strtotime($perfil_data['data_cadastro'])); ?></span></div>
                                </div>
                                <?php break; ?>

                            <?php case 'amigos': ?>
                                <div class="page-section-header">
                                    <h1>Amigos de <?php echo htmlspecialchars($perfil_data['nome']); ?></h1>
                                </div>
                                <?php if ($pode_ver_lista_amigos): ?>
                                    <?php if (!empty($lista_amigos)): ?>
                                        <div class="friends-grid">
                                            <?php foreach ($lista_amigos as $amigo): ?>
                                                <div class="friend-card">
                                                    <a href="<?php echo $config['base_path']; ?>perfil/<?php echo $amigo['id']; ?>">
                                                        <?php
                                                        $avatar_amigo_src = $amigo['foto_perfil_url']
                                                            ? $config['base_path'] . htmlspecialchars($amigo['foto_perfil_url'])
                                                            : $config['base_path'] . 'assets/images/default-avatar.png.png';
                                                        ?>
                                                        <img src="<?php echo $avatar_amigo_src; ?>" alt="Foto de <?php echo htmlspecialchars($amigo['nome']); ?>" class="friend-avatar">
                                                        <p class="friend-name"><?php echo htmlspecialchars($amigo['nome'] . ' ' . $amigo['sobrenome']); ?></p>
                                                        <p class="friend-username">@<?php echo htmlspecialchars($amigo['nome_de_usuario']); ?></p>
                                                    </a>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="post-card">
                                            <p><?php echo htmlspecialchars($perfil_data['nome']); ?> ainda não tem amigos.</p>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="post-card private-profile-card">
                                        <i class="fas fa-user-friends"></i>
                                        <h3>Lista de amigos privada</h3>
                                        <p>Apenas amigos de <?php echo htmlspecialchars($perfil_data['nome']); ?> podem ver a sua lista de amigos.</p>
                                    </div>
                                <?php endif; ?>
                                <?php break; ?>

                            <?php 
                            // --- [NOVO] ABA GALERIA ---
                            case 'galeria': 
                            ?>
                                <div class="page-section-header">
                                    <h1>Galeria de Fotos</h1>
                                </div>
                                <div class="post-card">
                                    <?php if (!empty($galeria_midia)): ?>
                                        <div class="gallery-grid">
                                            <?php foreach ($galeria_midia as $midia): ?>
                                                <?php 
                                                    $midia_url = $config['base_path'] . htmlspecialchars($midia['url_midia']); 
                                                    // Usamos a classe do Lightbox para abrir ao clicar
                                                ?>
                                                <div class="gallery-item post-image-clickable" data-postid="<?php echo $midia['id_postagem']; ?>">
                                                    <?php if ($midia['tipo_midia'] === 'imagem'): ?>
                                                        <img src="<?php echo $midia_url; ?>" alt="Foto da galeria">
                                                    <?php else: ?>
                                                        <video src="<?php echo $midia_url; ?>"></video>
                                                        <div class="video-overlay"><i class="fas fa-play"></i></div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p style="text-align: center; color: #606770; padding: 20px;">Ainda não há fotos nesta galeria.</p>
                                    <?php endif; ?>
                                </div>
                                <?php break; ?>

                            <?php case 'salvos': ?>
                                <div class="page-section-header">
                                    <h1>Meus Itens Salvos</h1>
                                    <p>Aqui estão todas as publicações que você salvou para ver mais tarde.</p>
                                </div>
                                <?php if (!empty($posts_para_exibir)): ?>
                                    <?php foreach ($posts_para_exibir as $post): ?>
                                        <div class="post-card" id="post-<?php echo $post['id']; ?>">
                                            <?php $user_id = $id_usuario_logado; include '../templates/post_template.php'; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="post-card"><p>Você ainda não salvou nenhuma postagem...</p></div>
                                <?php endif; ?>
                                <?php break; ?>

                            <?php case 'posts':
                            default: ?>
                                <?php if ($id_do_perfil_a_exibir == $id_usuario_logado): ?>
                                    <?php include '../templates/form_postagem.php'; ?>
                                <?php endif; ?>
                                
                                <?php if (!empty($posts_para_exibir)): ?>
                                    <?php foreach ($posts_para_exibir as $post): ?>
                                        <div class="post-card" id="post-<?php echo $post['id']; ?>">
                                            <?php $user_id = $id_usuario_logado; include '../templates/post_template.php'; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="post-card"><p>Este utilizador ainda não publicou nada.</p></div>
                                <?php endif; ?>
                                <?php break; ?>
                                
                        <?php endswitch; ?>
                    <?php endif; ?>
                <?php endif; // Fim do if eu_bloqueie ?>

            </main>
        </div>
        <div class="report-modal-overlay is-hidden" id="report-modal-overlay"></div>
        <?php include '../templates/footer.php'; ?>

    <?php else: ?>

        <?php // --- VISUALIZAÇÃO PÚBLICA --- ?>
        <div class="public-view-header">
            <a href="<?php echo $config['base_path']; ?>" class="logo"> <i class="fas fa-home"></i> 
                <span class="logo-text"><?php echo htmlspecialchars($config['site_nome']); ?></span>
            </a>
            <div class="public-header-actions">
                <a href="<?php echo $config['base_path']; ?>login" class="login-link-public">Entrar</a> 
                <?php if (isset($config['permite_cadastro']) && $config['permite_cadastro'] == '1'): ?>
                    <a href="<?php echo $config['base_path']; ?>cadastro" class="register-btn-public">Criar nova conta</a> 
                <?php endif; ?>
            </div>
        </div>

        <div class="main-content-public">
            <div class="profile-preview-card">
                <div class="profile-avatar-large">
                    <?php if (!empty($perfil_data['foto_perfil_url'])): ?>
                        <img src="<?php echo $config['base_path'] . htmlspecialchars($perfil_data['foto_perfil_url']); ?>" alt="Foto de Perfil">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                <div class="profile-header-info">
                    <h1><?php echo htmlspecialchars($perfil_data['nome'] . ' ' . $perfil_data['sobrenome']); ?></h1>
                    <p>@<?php echo htmlspecialchars($perfil_data['nome_de_usuario']); ?></p>
                </div>
            </div>

            <div class="public-prompt-card">
                <h2>Entre ou cadastre-se para ver o perfil completo</h2>
                <p>Conecte-se com os seus amigos, familiares e outras pessoas que você talvez conheça.</p>
                <div class="public-prompt-actions">
                    <a href="<?php echo $config['base_path']; ?>login" class="primary-btn">Entrar</a> 
                    <?php if (isset($config['permite_cadastro']) && $config['permite_cadastro'] == '1'): ?>
                        <span>ou</span>
                        <a href="<?php echo $config['base_path']; ?>cadastro" class="secondary-btn">Criar nova conta</a> 
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
    <?php endif; ?>
</body>
</html>