<?php
/**
 * templates/post/barra_estatisticas.php
 * Componente: Exibição de Contadores Sociais (Likes, Comentários, Shares).
 * VERSÃO: V6.1 (Sincronização de Seletores JS - socialbr.lol)
 * Responsabilidade: Mostrar métricas de engajamento e garantir atualização em tempo real.
 */

// As variáveis $post_id, $total_curtidas, $total_comentarios, $total_compartilhamentos são providas pelo orquestrador.
?>

<style>
    .post-stats-container {
        padding: 10px 15px;
        border-bottom: 1px solid #f0f2f5;
        background-color: transparent;
    }

    .post-stats-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #65676b;
        font-size: 0.9rem;
    }

    .post-stats-left, .post-stats-right {
        display: flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
        transition: text-decoration 0.2s;
    }

    .post-stats-left:hover, .post-stats-right:hover {
        text-decoration: underline;
    }

    .like-icon-circle {
        background: linear-gradient(180deg, #18AFFF 0%, #0062E0 100%);
        color: #fff;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
    }
</style>

<div class="post-stats-container">
    <div class="post-stats-flex">
        
        <div class="post-stats-left open-modal-likes" 
             data-postid="<?php echo $post_id; ?>"
             title="Ver quem curtiu">
            <span class="like-icon-circle">
                <i class="fas fa-thumbs-up"></i>
            </span>
            <span class="like-count">
                <?php echo $total_curtidas; ?>
            </span>
        </div>

        <div class="post-stats-right open-modal-comments" 
             data-postid="<?php echo $post_id; ?>"
             title="Ver comentários e compartilhamentos">
            
            <span class="comment-count"> 
                <?php echo $total_comentarios; ?> <?php echo ($total_comentarios == 1) ? 'comentário' : 'comentários'; ?>
            </span>
            
            <span class="stat-divider" style="margin: 0 4px;">·</span>
            
            <span class="share-count"> 
                <?php echo $total_compartilhamentos; ?> <?php echo ($total_compartilhamentos == 1) ? 'compartilhamento' : 'compartilhamentos'; ?>
            </span>
            
        </div>

    </div>
</div>