<?php
/**
 * templates/form_postagem.php
 * Template modular para criação de posts.
 * VERSÃO V91.0 - Integração Dinâmica com Módulo de Grupos.
 */

$user_id_form = $_SESSION['user_id'] ?? 0;
$user_foto_form = $config['base_path'] . 'assets/images/default-avatar.png'; 
$privacidade_padrao = 'publico'; 

if ($user_id_form > 0 && isset($conn)) {
    $sql_form_user = "SELECT foto_perfil_url, privacidade_posts_padrao FROM Usuarios WHERE id = ?";
    $stmt_form_user = $conn->prepare($sql_form_user);
    $stmt_form_user->bind_param("i", $user_id_form);
    $stmt_form_user->execute();
    $result_form_user = $stmt_form_user->get_result();
    
    if ($data_form_user = $result_form_user->fetch_assoc()) {
        if (!empty($data_form_user['foto_perfil_url'])) {
            $user_foto_form = $config['base_path'] . htmlspecialchars($data_form_user['foto_perfil_url']);
        }
        $privacidade_padrao = $data_form_user['privacidade_posts_padrao'];
    }
    $stmt_form_user->close();
}

$selected_public = ($privacidade_padrao == 'publico') ? 'selected' : '';
$selected_amigos = ($privacidade_padrao == 'amigos') ? 'selected' : '';

// NOVO: Detecta se estamos dentro de um grupo para vincular a postagem
$contexto_grupo = (isset($id_grupo) && (int)$id_grupo > 0) ? (int)$id_grupo : 0;
?>

<div class="create-post-card">
    <form action="<?php echo $config['base_path']; ?>api/postagens/criar_post.php" method="POST" enctype="multipart/form-data" id="create-post-form">
        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
        
        <input type="hidden" name="id_grupo" value="<?php echo $contexto_grupo; ?>">

        <div class="create-post-header">
            <div class="user-avatar-small">
                <a href="<?php echo $config['base_path']; ?>perfil/<?php echo $user_id_form; ?>">
                    <img src="<?php echo $user_foto_form; ?>" alt="Avatar">
                </a>
            </div>

            <div class="privacy-selector">
                <?php if ($contexto_grupo > 0): ?>
                    <span class="group-post-badge" style="font-size: 0.75rem; color: #65676b; font-weight: 600;">
                        <i class="fas fa-users"></i> No Grupo
                    </span>
                    <input type="hidden" name="privacidade" value="publico">
                <?php else: ?>
                    <select name="privacidade" class="privacy-selector-button">
                        <option value="publico" <?php echo $selected_public; ?>>Público</option>
                        <option value="amigos" <?php echo $selected_amigos; ?>>Amigos</option>
                    </select>
                <?php endif; ?>
            </div>
        </div>

        <textarea name="conteudo_texto" id="post-text-area" placeholder="<?php echo ($contexto_grupo > 0) ? 'O que quer partilhar com o grupo?' : 'No que você está pensando?'; ?>"></textarea>
        
        <div id="media-preview-container" class="media-preview-area is-hidden"></div>

        <div id="link-preview-container" class="link-preview-card is-hidden">
            <button type="button" id="remove-link-preview" class="close-btn-preview" title="Remover Link">&times;</button>
            <div class="link-preview-image">
                <img id="lp-img" src="" alt="">
            </div>
            <div class="link-preview-info">
                <h4 id="lp-title"></h4>
                <p id="lp-desc"></p>
                <small id="lp-domain"></small>
            </div>
            <input type="hidden" name="link_url" id="input-lp-url">
            <input type="hidden" name="link_title" id="input-lp-title">
            <input type="hidden" name="link_image" id="input-lp-image">
            <input type="hidden" name="link_description" id="input-lp-desc">
        </div>

        <div id="poll-setup-container" class="poll-setup-area is-hidden">
            <div class="poll-header">
                <h3><i class="fas fa-poll-h"></i> Criar Enquete</h3>
                <button type="button" id="remove-poll" class="close-btn-preview">&times;</button>
            </div>
            <div class="poll-question">
                <input type="text" name="poll_question" id="poll-question-input" placeholder="Faça uma pergunta...">
            </div>
            <div id="poll-options-list" class="poll-options-list">
                <div class="poll-option-input"><input type="text" name="poll_options[]" placeholder="Opção 1"></div>
                <div class="poll-option-input"><input type="text" name="poll_options[]" placeholder="Opção 2"></div>
            </div>
            <button type="button" id="add-poll-option" class="add-option-btn"><i class="fas fa-plus"></i> Adicionar Opção</button>
        </div>

        <div class="create-post-actions">
            <div class="post-tools-group">
                <div class="media-input-wrapper">
                    <input type="file" name="post_media[]" id="post_media" class="input-file" accept="image/*,video/mp4" multiple>
                    <label for="post_media" class="tool-btn" title="Adicionar Mídia"><i class="fas fa-images"></i></label>
                </div>
                
                <button type="button" id="btn-trigger-poll" class="tool-btn" title="Criar Enquete"><i class="fas fa-poll"></i></button>
            </div>

            <button type="submit" class="primary-btn-small">Publicar</button>
        </div>
    </form>
</div>