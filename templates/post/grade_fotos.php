<?php
/**
 * templates/post/grade_fotos.php
 * Componente: Renderização de Imagem Individual (Smart Grid V3.1).
 * VERSÃO: V3.1 (Sincronia Total com Orquestrador e CSS V4.0)
 * SINCRONIZADO: Com assets/css/components/_feed_midia.css.
 */

// Variáveis recebidas do orquestrador (grade_midia.php): 
// $media_src (URL da imagem)
// $index (Posição 0, 1 ou 2)
// $total_midias (Quantidade total de fotos/vídeos no post)
// $post_id (ID para o Lightbox)
?>

<div class="media-item-container post-image-clickable" 
     data-postid="<?php echo $post_id; ?>" 
     data-media-index="<?php echo $index; ?>">
    
    <img src="<?php echo $media_src; ?>" 
         alt="Foto da postagem" 
         loading="lazy">

    <?php 
    /**
     * LÓGICA DE OVERLAY (+X fotos)
     * Se houver mais de 3 mídias, o terceiro item (index 2) 
     * exibe a contagem restante.
     */
    if ($total_midias > 3 && $index === 2): 
    ?>
        <div class="media-more-overlay">
            +<?php echo ($total_midias - 3); ?>
        </div>
    <?php endif; ?>
    
</div>