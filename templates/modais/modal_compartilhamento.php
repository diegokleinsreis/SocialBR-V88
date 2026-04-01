<?php
/**
 * templates/modais/modal_compartilhamento.php
 * VERSÃO: V1.0 - COMPONENTE ATÓMICO DE COMPARTILHAMENTO
 * PAPEL: Estrutura HTML do modal para compartilhamento de publicações.
 * VERSÃO: V1.0 ( socialbr.lol )
 */
?>

<div class="report-modal-overlay is-hidden" id="share-modal-overlay"></div>

<div class="report-modal is-hidden" id="share-modal" role="dialog" aria-labelledby="share-modal-title" aria-modal="true">
    <form id="share-modal-form">
        <div class="report-modal-header">
            <h3 id="share-modal-title">Compartilhar publicação</h3>
            <button type="button" class="close-modal-btn" id="close-share-modal" aria-label="Fechar">&times;</button>
        </div>

        <div class="report-modal-body">
            <textarea name="conteudo_texto" 
                      class="share-modal-textarea" 
                      placeholder="Escreva algo sobre isto... (opcional)"></textarea>
            
            <div id="share-modal-preview-content"></div>
        </div>

        <div class="report-modal-footer">
            <input type="hidden" name="post_id" id="share-modal-post-id" value="">
            
            <button type="submit" class="primary-btn-small" id="share-modal-submit-btn">
                Publicar
            </button>
        </div>
    </form>
</div>