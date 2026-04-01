<?php
/**
 * templates/modais/modal_editar_postagem.php
 * Componente: Modal de Edição de Postagem (Global).
 * PAPEL: Interface centralizada para edição de conteúdo de texto.
 * VERSÃO: V60.12 - Design Minimalista ( socialbr.lol )
 */
?>

<div id="post-edit-interaction-modal-overlay" class="post-edit-modal-closer"></div>

<div id="post-edit-interaction-modal" role="dialog" aria-labelledby="edit-modal-title" aria-modal="true">
    
    <div class="report-modal-header" style="border-bottom: none; padding: 20px 25px 10px 25px;">
        <h3 id="edit-modal-title" style="font-size: 1.1rem; font-weight: 800; color: #0C2D54;">
            Editar publicação
        </h3>
        <button type="button" class="close-modal-btn post-edit-modal-closer" aria-label="Fechar">&times;</button>
    </div>

    <div class="report-modal-body" style="padding: 10px 25px;">
        <textarea id="modal-edit-post-textarea" 
                  class="modal-edit-textarea" 
                  placeholder="O que você está pensando agora?"
                  spellcheck="true"></textarea>
        
        <input type="hidden" id="modal-edit-post-id" value="">
    </div>

    <div class="report-modal-footer" style="padding: 15px 25px 25px 25px; border-top: none; background: transparent;">
        <button type="button" class="btn-modal-cancel-edit post-edit-modal-closer">
            Cancelar
        </button>
        
        <button type="button" id="btn-save-post-edit" class="btn-modal-save-edit">
            Guardar alterações
        </button>
    </div>

</div>

<script>
    /**
     * LÓGICA DE INTERFACE DO MODAL
     * Funções básicas de abertura e fecho para garantir fluidez.
     */
    document.addEventListener('DOMContentLoaded', function() {
        const overlay = document.getElementById('post-edit-interaction-modal-overlay');
        const modal = document.getElementById('post-edit-interaction-modal');
        const closers = document.querySelectorAll('.post-edit-modal-closer');

        // Função de Fechamento
        function closeEditModal() {
            if (overlay) overlay.classList.remove('is-visible');
            if (modal) modal.classList.remove('is-visible');
            // Limpa o textarea para a próxima abertura
            const textarea = document.getElementById('modal-edit-post-textarea');
            if (textarea) textarea.value = '';
        }

        // Atribui evento aos botões de fecho e ao overlay
        closers.forEach(btn => {
            btn.addEventListener('click', closeEditModal);
        });

        // Fecha ao pressionar a tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal && modal.classList.contains('is-visible')) {
                closeEditModal();
            }
        });
    });
</script>