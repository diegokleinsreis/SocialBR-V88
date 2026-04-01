<?php
// 1. VERIFICA SE O UTILIZADOR ESTÁ LOGADO
// O index.php já carrega as configurações, mas garantimos aqui
if (!isset($_SESSION['user_id'])) {
    if (isset($config['base_path'])) {
        header("Location: " . $config['base_path'] . "login");
    } else {
        header("Location: /login");
    }
    exit();
}
$user_id = $_SESSION['user_id'];

// 2. OBTÉM O ID DA POSTAGEM
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($post_id <= 0) {
    header("Location: " . ($config['base_path'] ?? '/') . "pagina-nao-encontrada");
    exit();
}

// 3. LÓGICA DE DADOS
// Tenta carregar a lógica. Se não existir, faz um fallback ou mostra erro.
if (file_exists(__DIR__ . '/../src/PostLogic.php')) {
    require_once __DIR__ . '/../src/PostLogic.php';
    $data = PostLogic::getSinglePostWithComments($conn, $post_id, $user_id);
} else {
    die("Erro Crítico: Arquivo PostLogic.php não encontrado.");
}

// 4. VERIFICA SE O POST EXISTE
if ($data === null || empty($data['post'])) {
    header("Location: " . ($config['base_path'] ?? '/') . "pagina-nao-encontrada");
    exit();
}

// 5. PREPARA OS DADOS
$post = $data['post'];
$comentarios = $data['comentarios'] ?? [];
$respostas = $data['respostas'] ?? [];

// Configuração da página
$page_title = "Postagem de " . htmlspecialchars($post['nome'] ?? 'Usuário') . " - " . ($config['site_nome'] ?? 'Social BR');
$comment_limite_global = 150;

// --- HELPER PARA EVITAR ERROS DE ARRAY KEY ---
// Esta função tenta encontrar o valor em várias chaves possíveis
function get_safe($array, $keys, $default = '') {
    foreach ($keys as $key) {
        if (!empty($array[$key])) return $array[$key];
    }
    return $default;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <?php include '../templates/head_common.php'; ?>
</head>
<body>

    <?php include '../templates/header.php'; ?>
    <?php include '../templates/mobile_nav.php'; ?>

    <div class="main-content-area">
        <?php include '../templates/sidebar.php'; ?>

        <main class="feed-container">

            <div class="post-card" id="post-<?php echo $post['id']; ?>">
                <?php 
                // Define a flag para o template mostrar o texto completo
                $is_pagina_postagem = true; 
                
                // Normaliza dados do autor do post para o template não falhar
                // Se o PostLogic retornar chaves diferentes, ajustamos aqui
                if (!isset($post['autor_id'])) $post['autor_id'] = $post['id_usuario'] ?? 0;
                
                include '../templates/post_template.php'; 
                ?>
            </div>

            <div class="post-card comment-list-card">
                <div class="full-comment-list">
                    <?php if (!empty($comentarios)): ?>
                        <?php foreach ($comentarios as $comment_id => $comment): ?>
                            <?php
                            // --- PROTEÇÃO CONTRA ERROS (CORREÇÃO) ---
                            // Define variáveis seguras testando múltiplas chaves
                            $c_autor_id = get_safe($comment, ['autor_id', 'id_usuario'], 0);
                            $c_nome = get_safe($comment, ['autor_nome', 'nome'], 'Usuário');
                            $c_sobrenome = get_safe($comment, ['autor_sobrenome', 'sobrenome'], '');
                            $c_foto = get_safe($comment, ['autor_foto_perfil', 'foto_perfil_url'], '');
                            
                            $c_curtiu = get_safe($comment, ['usuario_curtiu_comentario'], 0);
                            $c_total_curtidas = get_safe($comment, ['total_curtidas_comentario'], 0);
                            ?>

                            <div class="comment-item-wrapper" id="comment-wrapper-<?php echo $comment_id; ?>">
                                <div class="comment-view-mode">
                                    <div class="comment-item">
                                        <div class="comment-author-avatar">
                                            <a href="<?php echo $config['base_path']; ?>perfil/<?php echo $c_autor_id; ?>">
                                                <?php if (!empty($c_foto)): ?>
                                                    <img src="<?php echo $config['base_path'] . htmlspecialchars($c_foto); ?>" alt="Foto">
                                                <?php else: ?>
                                                    <i class="fas fa-user"></i>
                                                <?php endif; ?>
                                            </a>
                                        </div>
                                        <div class="comment-content">
                                            <div class="comment-bubble">
                                                <a href="<?php echo $config['base_path']; ?>perfil/<?php echo $c_autor_id; ?>" class="comment-author-name">
                                                    <?php echo htmlspecialchars($c_nome . ' ' . $c_sobrenome); ?>
                                                </a>
                                                
                                                <?php
                                                // Lógica "Ver Mais"
                                                $comment_texto_completo = $comment['conteudo_texto'] ?? '';
                                                
                                                if (mb_strlen($comment_texto_completo) > $comment_limite_global) {
                                                    $comment_texto_curto = mb_strimwidth($comment_texto_completo, 0, $comment_limite_global, ""); 
                                                    echo '<div class="comment-content-short" id="comment-content-short-' . $comment_id . '">';
                                                    echo '  <p class="comment-text">' . htmlspecialchars($comment_texto_curto) . '... <a href="#" class="see-more-link" data-content-id="' . $comment_id . '" data-type="comment">ver mais...</a></p>';
                                                    echo '</div>';
                                                    echo '<div class="comment-content-full is-hidden" id="comment-content-full-' . $comment_id . '">';
                                                    echo '  <p class="comment-text">' . nl2br(htmlspecialchars($comment_texto_completo)) . '</p>';
                                                    echo '</div>';
                                                } else {
                                                    echo '<p class="comment-text">' . nl2br(htmlspecialchars($comment_texto_completo)) . '</p>';
                                                }
                                                ?>
                                            </div>
                                            <div class="comment-actions">
                                                <span class="comment-timestamp"><?php echo date("d/m H:i", strtotime($comment['data_comentario'])); ?></span>
                                                <a href="#" class="comment-like-btn <?php echo ($c_curtiu > 0) ? 'active' : ''; ?>" data-comment-id="<?php echo $comment_id; ?>">Curtir</a>
                                                <a href="#" class="reply-link" data-comment-id="<?php echo $comment_id; ?>">Responder</a>
                                                <span class="comment-like-count" <?php if($c_total_curtidas == 0) echo 'style="display:none;"'; ?> data-comment-id="<?php echo $comment_id; ?>"><i class="fas fa-thumbs-up"></i> <?php echo $c_total_curtidas; ?></span>
                                            </div>
                                        </div>
                                        <div class="comment-options">
                                            <button class="comment-options-btn"><i class="fas fa-ellipsis-h"></i></button>
                                            <div class="comment-options-menu is-hidden">
                                                <?php if ($c_autor_id == $user_id): ?>
                                                    <a href="#" class="comment-edit-btn" data-comment-id="<?php echo $comment_id; ?>">Editar</a>
                                                    <a href="#" class="comment-delete-btn" data-comment-id="<?php echo $comment_id; ?>">Excluir</a>
                                                <?php else: ?>
                                                    <a href="#" class="report-btn" data-content-type="comentario" data-content-id="<?php echo $comment_id; ?>"><i class="fas fa-flag"></i> Denunciar</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if (isset($respostas[$comment_id])): ?>
                                        <div class="comment-replies">
                                            <?php foreach ($respostas[$comment_id] as $reply): ?>
                                                <?php
                                                // --- PROTEÇÃO PARA RESPOSTAS TAMBÉM ---
                                                $r_autor_id = get_safe($reply, ['autor_id', 'id_usuario'], 0);
                                                $r_nome = get_safe($reply, ['autor_nome', 'nome'], 'Usuário');
                                                $r_sobrenome = get_safe($reply, ['autor_sobrenome', 'sobrenome'], '');
                                                $r_foto = get_safe($reply, ['autor_foto_perfil', 'foto_perfil_url'], '');
                                                $r_curtiu = get_safe($reply, ['usuario_curtiu_comentario'], 0);
                                                $r_total = get_safe($reply, ['total_curtidas_comentario'], 0);
                                                ?>
                                                <div id="comment-wrapper-<?php echo $reply['id']; ?>">
                                                    <div class="comment-view-mode">
                                                        <div class="comment-item is-reply">
                                                            <div class="comment-author-avatar">
                                                                <a href="<?php echo $config['base_path']; ?>perfil/<?php echo $r_autor_id; ?>">
                                                                    <?php if (!empty($r_foto)): ?>
                                                                        <img src="<?php echo $config['base_path'] . htmlspecialchars($r_foto); ?>" alt="Foto">
                                                                    <?php else: ?>
                                                                        <i class="fas fa-user"></i>
                                                                    <?php endif; ?>
                                                                </a>
                                                            </div>
                                                            <div class="comment-content">
                                                                <div class="comment-bubble">
                                                                    <a href="<?php echo $config['base_path']; ?>perfil/<?php echo $r_autor_id; ?>" class="comment-author-name">
                                                                        <?php echo htmlspecialchars($r_nome . ' ' . $r_sobrenome); ?>
                                                                    </a>
                                                                    <?php
                                                                        // Texto da Resposta
                                                                        $reply_txt = $reply['conteudo_texto'] ?? '';
                                                                        if (mb_strlen($reply_txt) > $comment_limite_global) {
                                                                            $r_short = mb_strimwidth($reply_txt, 0, $comment_limite_global, "");
                                                                            echo '<div class="comment-content-short" id="comment-content-short-' . $reply['id'] . '"><p class="comment-text">' . htmlspecialchars($r_short) . '... <a href="#" class="see-more-link" data-content-id="' . $reply['id'] . '" data-type="comment">ver mais...</a></p></div>';
                                                                            echo '<div class="comment-content-full is-hidden" id="comment-content-full-' . $reply['id'] . '"><p class="comment-text">' . nl2br(htmlspecialchars($reply_txt)) . '</p></div>';
                                                                        } else {
                                                                            echo '<p class="comment-text">' . nl2br(htmlspecialchars($reply_txt)) . '</p>';
                                                                        }
                                                                    ?>
                                                                </div>
                                                                <div class="comment-actions">
                                                                    <span class="comment-timestamp"><?php echo date("d/m H:i", strtotime($reply['data_comentario'])); ?></span>
                                                                    <a href="#" class="comment-like-btn <?php echo ($r_curtiu > 0) ? 'active' : ''; ?>" data-comment-id="<?php echo $reply['id']; ?>">Curtir</a>
                                                                    <a href="#" class="reply-link" data-comment-id="<?php echo $comment_id; ?>">Responder</a>
                                                                    <span class="comment-like-count" <?php if($r_total == 0) echo 'style="display:none;"'; ?> data-comment-id="<?php echo $reply['id']; ?>"><i class="fas fa-thumbs-up"></i> <?php echo $r_total; ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="comment-options">
                                                                <button class="comment-options-btn"><i class="fas fa-ellipsis-h"></i></button>
                                                                <div class="comment-options-menu is-hidden">
                                                                    <?php if ($r_autor_id == $user_id): ?>
                                                                        <a href="#" class="comment-edit-btn" data-comment-id="<?php echo $reply['id']; ?>">Editar</a>
                                                                        <a href="#" class="comment-delete-btn" data-comment-id="<?php echo $reply['id']; ?>">Excluir</a>
                                                                    <?php else: ?>
                                                                        <a href="#" class="report-btn" data-content-type="comentario" data-content-id="<?php echo $reply['id']; ?>"><i class="fas fa-flag"></i> Denunciar</a>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="comment-edit-form is-hidden" id="edit-form-<?php echo $reply['id']; ?>">
                                                        <textarea class="comment-edit-textarea"><?php echo htmlspecialchars($reply['conteudo_texto']); ?></textarea>
                                                        <div class="comment-edit-actions">
                                                            <button class="comment-edit-cancel" data-comment-id="<?php echo $reply['id']; ?>">Cancelar</button>
                                                            <button class="comment-edit-save" data-comment-id="<?php echo $reply['id']; ?>">Salvar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="comment-edit-form is-hidden" id="edit-form-<?php echo $comment_id; ?>">
                                    <textarea class="comment-edit-textarea"><?php echo htmlspecialchars($comment['conteudo_texto']); ?></textarea>
                                    <div class="comment-edit-actions">
                                        <button class="comment-edit-cancel" data-comment-id="<?php echo $comment_id; ?>">Cancelar</button>
                                        <button class="comment-edit-save" data-comment-id="<?php echo $comment_id; ?>">Salvar</button>
                                    </div>
                                </div>
                                
                                <div class="reply-form-container is-hidden" id="reply-form-<?php echo $comment_id; ?>">
                                    <form action="<?php echo $config['base_path']; ?>api/postagens/criar_comentario.php" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                        <input type="hidden" name="id_postagem" value="<?php echo $post['id']; ?>">
                                        <input type="hidden" name="id_comentario_pai" value="<?php echo $comment_id; ?>">
                                        
                                        <div style="display: flex; gap: 8px; width: 100%;">
                                            <input type="text" name="conteudo_texto" class="comment-input" placeholder="Escreva sua resposta..." required style="flex-grow: 1; border-radius: 20px;">
                                            <button type="submit" class="comment-submit-btn" style="background:none; border:none; color:#1877f2; cursor:pointer;"><i class="fas fa-paper-plane"></i></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="padding: 20px; text-align: center; color: #777;">Nenhum comentário ainda. Seja o primeiro a comentar!</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <?php include '../templates/footer.php'; ?>
</body>
</html>