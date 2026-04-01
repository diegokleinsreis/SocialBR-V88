<?php
/**
 * templates/modais/modal_lightbox.php
 * VERSÃO: V1.0 - COMPONENTE ATÓMICO DE VISUALIZAÇÃO (LIGHTBOX)
 * PAPEL: Estrutura HTML do visualizador global de imagens e mídias.
 * VERSÃO: V1.0 ( socialbr.lol )
 */
?>

<div class="lightbox-overlay is-hidden" id="lightbox-overlay">
    <div class="lightbox-modal" id="lightbox-modal">
        <button class="lightbox-close-btn" id="lightbox-close-btn" title="Fechar">&times;</button>
        
        <a href="#" class="lightbox-download-btn is-hidden" id="lightbox-download-btn" download title="Baixar Imagem">
            <i class="fas fa-download"></i>
        </a>

        <div class="lightbox-content">
            <div class="lightbox-image-column">
                <div class="lightbox-image-wrapper">
                    <div class="spinner"></div>
                </div>
            </div>
            
            <div class="lightbox-details-column">
                </div>
        </div>
    </div>
</div>