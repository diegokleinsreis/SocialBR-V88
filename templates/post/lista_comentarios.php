<?php
/**
 * templates/post/lista_comentarios.php
 * Componente: Prévia dos Últimos Comentários no Feed.
 * VERSÃO: V6.2 (Integração com CensuraLogic - socialbr.lol)
 * Responsabilidade: Exibir os comentários recentes e permitir abertura do modal completo.
 */

// Se não houver comentários para exibir, o componente não renderiza nada
// O array 'ultimos_comentarios' é alimentado pelo PostLogic via orquestrador
if (empty($post['ultimos_comentarios'])) {
    return;
}
?>

<style>
    /* Estilos específicos para a lista de prévia de comentários */
    .comments-preview-section {
        padding: 12px 15px;
        border-top: 1px solid #f0f2f5;
        margin-bottom: 5px;
    }

    .comment-preview-item {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 8px;
    }

    .comment-bubble {
        background: #f0f2f5;
        padding: 8px 12px;
        border-radius: 18px;
        font-size: 0.85rem;
        max-width: 85%;
        position: relative;
    }

    .comment-author-name {
        color: #050505;
        display: block;
        font-weight: 700;
        margin-bottom: 2px;
        text-decoration: none;
    }

    .comment-author-name:hover {
        text-decoration: underline;
    }

    .comment-text-content {
        color: #050505;
        line-height: 1.3;
        word-break: break-word;
    }

    .view-all-comments {
        color: #65676b;
        font-size: 0.85rem;
        text-decoration: none;
        font-weight: 600;
        margin-top: 5px;
        display: inline-block;
        transition: color 0.2s;
    }

    .view-all-comments:hover {
        text-decoration: underline;
    }
</style>

<div class="comments-preview-section">
    
    <div class="comments-preview-list" style="display: flex; flex-direction: column;">
        <?php foreach ($post['ultimos_comentarios'] as $c): 
            // Define o avatar do autor do comentário (Regra 5 Debug)
            $c_avatar = !empty($c['autor_foto']) 
                ? $config['base_path'] . htmlspecialchars($c['autor_foto']) 
                : $config['base_path'] . 'assets/images/default-avatar.png';
            
            $c_perfil_link = $config['base_path'] . 'perfil/' . ($c['id_usuario'] ?? '');

            /**
             * APLICAÇÃO DA MÁSCARA SOCIAL NOS COMENTÁRIOS
             * O objeto $censura vem do post_template.php pai.
             */
            $comentario_original = $c['conteudo_texto'] ?? '';
            $comentario_filtrado = isset($censura) ? $censura->aplicarMascaraSocial($comentario_original) : $comentario_original;
        ?>
            <div class="comment-preview-item">
                <a href="<?php echo $c_perfil_link; ?>">
                    <img src="<?php echo $c_avatar; ?>" 
                         alt="Avatar" 
                         style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 1px solid #dddfe2;"
                         onerror="this.src='<?php echo $config['base_path']; ?>assets/images/default-avatar.png'">
                </a>
                
                <div class="comment-bubble">
                    <a href="<?php echo $c_perfil_link; ?>" class="comment-author-name">
                        <?php echo htmlspecialchars($c['autor_nome']); ?>
                    </a>
                    <span class="comment-text-content">
                        <?php echo htmlspecialchars($comentario_filtrado); ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <a href="javascript:void(0)" 
       class="view-all-comments open-modal-comments" 
       data-postid="<?php echo $post_id; ?>" 
       title="Ver todos os comentários desta publicação">
         Ver mais comentários
    </a>

</div>