<?php
/**
 * templates/post/campo_comentario.php
 * Componente: Campo de Input para Comentário Rápido.
 * VERSÃO: V6.2 (Cor Oficial & Sincronia Modal V9.1 - socialbr.lol)
 * Responsabilidade: Permitir a inserção imediata de comentários via AJAX e disparar abertura do modal.
 */

// Recupera o token CSRF definido no header.php para validar a requisição
$csrf_token_atual = $_SESSION['csrf_token'] ?? '';
?>

<style>
    /* Estilos específicos para a área de inserção de comentários */
    .add-comment-wrapper {
        padding: 10px 15px;
        background: #fafafa;
        border-radius: 0 0 12px 12px;
        border-top: 1px solid #f0f2f5;
    }

    .ajax-comment-form {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .comment-input-field {
        flex-grow: 1;
        border-radius: 20px;
        border: 1px solid #ccd0d5;
        padding: 8px 15px;
        outline: none;
        background: #fff;
        font-size: 0.9rem;
        transition: border-color 0.2s;
    }

    .comment-input-field:focus {
        border-color: #0C2D54; /* Cor Oficial SocialBR */
    }

    .comment-submit-btn {
        background: none;
        border: none;
        color: #0C2D54; /* Cor Oficial SocialBR */
        cursor: pointer;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s, color 0.2s;
        padding: 0;
    }

    .comment-submit-btn:hover {
        transform: scale(1.1);
        color: #08203c; /* Tom mais profundo para hover */
    }

    .comment-submit-btn:disabled {
        color: #bec3c9;
        cursor: not-allowed;
    }
</style>

<div class="add-comment-wrapper">
    <form action="<?php echo $config['base_path']; ?>api/comentarios/criar_comentario.php" 
          method="POST" 
          class="ajax-comment-form"
          data-post-id="<?php echo $post_id; ?>">
        
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token_atual; ?>">
        
        <input type="hidden" name="id_postagem" value="<?php echo $post_id; ?>">
        
        <input type="text" 
               name="conteudo_texto" 
               class="comment-input-field" 
               placeholder="Escreva um comentário..." 
               autocomplete="off"
               required>
        
        <button type="submit" class="comment-submit-btn" title="Enviar Comentário">
            <i class="fas fa-paper-plane"></i>
        </button>
    </form>
</div>
