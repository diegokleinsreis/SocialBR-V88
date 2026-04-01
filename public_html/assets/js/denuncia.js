/**
 * assets/js/denuncia.js
 * PAPEL: Módulo para gerir o modal de denúncia de posts, comentários e perfis.
 * VERSÃO: V60.5 - FIX: Integração com SweetAlert2 para Alertas de Luxo
 * socialbr.lol
 */

/**
 * Função para ABRIR o modal de denúncia. 
 * É chamada globalmente pelo main.js ao detetar um clique em .post-report-btn
 */
function openReportModal(reportBtn) {
    const modal = document.getElementById('report-modal');
    const overlay = document.getElementById('report-modal-overlay');
    
    if (!modal || !overlay) {
        console.error('Erro: Estrutura do modal de denúncia não encontrada.');
        return;
    }

    const title = modal.querySelector('.report-modal-title');
    const contentReportList = document.getElementById('content-report-options');
    const userReportList = document.getElementById('user-report-options');
    const detailsContainer = document.getElementById('report-details-container');
    const modalFooter = document.getElementById('report-modal-footer');
    const descriptionTextarea = document.getElementById('report-description');
    const motiveInput = document.getElementById('selected-report-motive');

    // --- RESET TOTAL AO ABRIR (Garante limpeza de denúncias anteriores) ---
    if (descriptionTextarea) descriptionTextarea.value = '';
    if (motiveInput) motiveInput.value = '';
    if (detailsContainer) detailsContainer.style.display = 'none';
    if (modalFooter) modalFooter.style.display = 'none';
    
    // Limpa a classe de seleção visual dos motivos
    modal.querySelectorAll('.report-option').forEach(opt => {
        opt.style.backgroundColor = '';
        opt.style.color = '';
    });

    // Captura os dados do alvo da denúncia
    const contentType = reportBtn.dataset.contentType || 'post';
    const contentId = reportBtn.dataset.contentId || reportBtn.dataset.postid;

    if (!contentId) {
        console.warn('Aviso: Tentativa de denúncia sem ID de conteúdo.');
        return;
    }

    modal.dataset.contentType = contentType;
    modal.dataset.contentId = contentId;

    // Configuração Visual baseada no tipo de conteúdo
    if (contentType === 'usuario') {
        if (title) title.textContent = 'Por que você está denunciando esse perfil?';
        if (userReportList) userReportList.style.display = 'block';
        if (contentReportList) contentReportList.style.display = 'none';
    } else { 
        if (title) title.textContent = 'Por que você está denunciando isso?';
        if (userReportList) userReportList.style.display = 'none';
        if (contentReportList) contentReportList.style.display = 'block';
    }

    // --- CORREÇÃO V60.4/V60.5: Remove is-hidden e adiciona is-visible ---
    overlay.classList.remove('is-hidden');
    modal.classList.remove('is-hidden');
    overlay.classList.add('is-visible');
    modal.classList.add('is-visible');
}

/**
 * Função para FECHAR o modal de denúncia
 */
function closeReportModal() {
    const modal = document.getElementById('report-modal');
    const overlay = document.getElementById('report-modal-overlay');
    
    if (modal) {
        modal.classList.remove('is-visible');
        modal.classList.add('is-hidden');
    }
    if (overlay) {
        overlay.classList.remove('is-visible');
        overlay.classList.add('is-hidden');
    }
}

/**
 * INICIALIZAÇÃO DOS EVENTOS
 */
document.addEventListener('DOMContentLoaded', function() {
    const reportModal = document.getElementById('report-modal');
    if (!reportModal) return;

    const reportModalOverlay = document.getElementById('report-modal-overlay');
    const closeReportModalBtn = document.getElementById('close-report-modal');
    const submitBtn = document.getElementById('submit-report-btn');
    const detailsContainer = document.getElementById('report-details-container');
    const modalFooter = document.getElementById('report-modal-footer');
    const motiveInput = document.getElementById('selected-report-motive');

    if (closeReportModalBtn) closeReportModalBtn.addEventListener('click', closeReportModal);
    if (reportModalOverlay) reportModalOverlay.addEventListener('click', closeReportModal);

    // --- PASSO 1: SELEÇÃO DE MOTIVO ---
    reportModal.querySelectorAll('.report-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Feedback Visual: Destaca o motivo selecionado
            reportModal.querySelectorAll('.report-option').forEach(opt => {
                opt.style.backgroundColor = '';
                opt.style.color = '';
            });
            this.style.backgroundColor = '#f0f2f5';
            this.style.color = '#0C2D54';

            // Armazena a categoria escolhida no input oculto
            const motivo = this.dataset.motivo;
            motiveInput.value = motivo;

            // Revela a área de descrição e o botão de envio
            if (detailsContainer) detailsContainer.style.display = 'block';
            if (modalFooter) modalFooter.style.display = 'flex';
            
            // Foca no textarea para facilitar a escrita
            const textarea = document.getElementById('report-description');
            if (textarea) textarea.focus();
        });
    });

    // --- PASSO 2: ENVIO FINAL VIA AJAX ---
    if (submitBtn) {
        submitBtn.addEventListener('click', async function() {
            const contentType = reportModal.dataset.contentType;
            const contentId = reportModal.dataset.contentId;
            const motivo = motiveInput.value;
            const descricao = document.getElementById('report-description').value;

            if (!motivo) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    text: 'Por favor, selecione um motivo primeiro.',
                    confirmButtonColor: '#0C2D54'
                });
                return;
            }

            // Estado de carregamento do botão
            const originalBtnText = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> A enviar...';

            const formData = new FormData();
            formData.append('content_type', contentType);
            formData.append('content_id', contentId);
            formData.append('motivo', motivo);
            formData.append('descricao', descricao);

            try {
                // Utiliza a apiFetch global (main.js) que já trata o CSRF Token
                const data = await apiFetch('denuncias/criar_denuncia.php', 'POST', formData);
                
                if (data.success) {
                    // Alerta de Sucesso Premium
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: data.message || 'Denúncia registada com sucesso.',
                        confirmButtonColor: '#0C2D54'
                    });

                    // Toast secundário para redundância visual
                    if (window.showToast) showToast(data.message || 'Denúncia registada com sucesso.');
                    
                    closeReportModal();
                } else {
                    throw new Error(data.error || 'Erro ao processar denúncia.');
                }
            } catch (error) {
                console.error('Falha técnica na denúncia:', error);
                
                // Alerta de Erro Premium
                Swal.fire({
                    icon: 'error',
                    title: 'Falha na Operação',
                    text: error.message,
                    confirmButtonColor: '#0C2D54'
                });

                if (window.showToast) showToast(error.message, 'error');
            } finally {
                // Restaura o botão após o processamento
                this.disabled = false;
                this.innerHTML = originalBtnText;
            }
        });
    }
});