<?php
/**
 * templates/modais/modal_denuncia.php
 * VERSÃO: V1.0 - COMPONENTE ATÓMICO DE DENÚNCIA
 * PAPEL: Estrutura HTML do modal de denúncias com suporte a categorias e descrição detalhada.
 * VERSÃO: V1.0 ( socialbr.lol )
 */
?>

<div class="report-modal-overlay is-hidden" id="report-modal-overlay"></div>

<div class="report-modal is-hidden" id="report-modal" role="dialog" aria-labelledby="report-modal-title" aria-modal="true">
    
    <div class="report-modal-header">
        <h3 class="report-modal-title" id="report-modal-title">Denunciar Conteúdo</h3>
        <button class="close-modal-btn" id="close-report-modal" aria-label="Fechar">&times;</button>
    </div>

    <div class="report-modal-body">
        <p class="report-modal-info" id="report-instruction">Sua denúncia é anônima. Por favor, selecione um motivo:</p>
        
        <ul class="report-options-list" id="content-report-options" style="display: none;">
            <li><a href="#" class="report-option" data-motivo="Bullying, assédio ou abuso">Bullying, assédio ou abuso</a></li>
            <li><a href="#" class="report-option" data-motivo="Conteúdo violento ou perturbador">Conteúdo violento ou perturbador</a></li>
            <li><a href="#" class="report-option" data-motivo="Golpe, fraude ou informação falsa">Golpe, fraude ou informação falsa</a></li>
            <li><a href="#" class="report-option" data-motivo="Spam">Spam</a></li>
            <li><a href="#" class="report-option" data-motivo="Discurso de ódio">Discurso de ódio</a></li>
            <li><a href="#" class="report-option" data-motivo="Outro motivo">Outro motivo</a></li>
        </ul>

        <ul class="report-options-list" id="user-report-options" style="display: none;">
            <li><a href="#" class="report-option" data-motivo="Perfil Falso">Perfil Falso</a></li>
            <li><a href="#" class="report-option" data-motivo="Nome impróprio">Nome impróprio</a></li>
            <li><a href="#" class="report-option" data-motivo="Publicando conteúdo inadequado">Publicando conteúdo inadequado</a></li>
            <li><a href="#" class="report-option" data-motivo="Outra coisa">Outra coisa</a></li>
        </ul>

        <div id="report-details-container" style="display: none; margin-top: 15px; border-top: 1px solid #f0f2f5; padding-top: 15px;">
            <label for="report-description" style="display: block; font-size: 0.9rem; color: #050505; font-weight: 700; margin-bottom: 8px;">
                Informações Adicionais (Opcional)
            </label>
            <textarea id="report-description" 
                      class="modal-edit-textarea" 
                      placeholder="Pode descrever melhor o que aconteceu? Isso ajuda nossa equipa de moderação." 
                      style="min-height: 100px; padding: 12px; font-size: 0.9rem;"></textarea>
            
            <input type="hidden" id="selected-report-motive" value="">
        </div>
    </div>

    <div class="report-modal-footer" id="report-modal-footer" style="display: none; padding: 15px 20px; border-top: 1px solid #dddfe2; background: #fff; justify-content: flex-end;">
        <button type="button" id="submit-report-btn" class="btn-modal-save-edit">
            Enviar Denúncia
        </button>
    </div>

</div>