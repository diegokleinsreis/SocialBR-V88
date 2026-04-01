<?php
/**
 * templates/post/grade_video.php
 * Componente: Renderização de Vídeo Individual (Smart Grid V3.1).
 * VERSÃO: V3.1 (Sincronia Total com Orquestrador e CSS V4.0)
 * SINCRONIZADO: Com assets/css/components/_feed_midia.css.
 */

// Variáveis recebidas do orquestrador (grade_midia.php): 
// $media_src (URL do vídeo)
// $index (Posição na grade: 0, 1 ou 2)
// $post_id (ID para referência de clique)
?>

<div class="media-item-container post-video-container" 
     data-postid="<?php echo $post_id; ?>" 
     data-media-index="<?php echo $index; ?>">
    
    <video class="lazy-video" 
           controls 
           preload="none" 
           poster="<?php echo $config['base_path']; ?>assets/images/video-placeholder.png">
        
        <source src="<?php echo $media_src; ?>" type="video/mp4">
        
        <p>Seu navegador não suporta a reprodução deste vídeo.</p>
    </video>

</div>

<script>
    /**
     * LÓGICA DE AUTO-PAUSE (UX PRO)
     * Implementação atômica: garante que apenas um vídeo toque por vez no feed.
     */
    (function() {
        const videos = document.querySelectorAll('video');
        videos.forEach(vid => {
            vid.addEventListener('play', function() {
                videos.forEach(otherVid => {
                    if (otherVid !== vid) {
                        otherVid.pause();
                    }
                });
            });
        });
    })();
</script>