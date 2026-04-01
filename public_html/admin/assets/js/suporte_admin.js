/**
 * admin/assets/js/suporte_admin.js
 * PAPEL: Gestão de comportamento do Módulo Suporte Admin.
 * FUNCIONALIDADES: Troca de status, envio de respostas, auto-scroll, gaveta técnica, Refresh AJAX e Ctrl+V.
 * VERSÃO: 1.3 - socialbr.lol
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. SELETORES DE ELEMENTOS
    const adminChatBox     = document.getElementById('admin-chat-box');
    const statusSelect     = document.getElementById('admin-status-select');
    const respostaForm     = document.getElementById('admin-form-resposta');
    const fileInput        = document.getElementById('foto_suporte_admin');
    const filePreviewLabel = document.getElementById('admin-file-preview');
    const btnEnviar        = document.getElementById('btn-admin-enviar');
    const msgTextArea      = document.getElementById('admin_resposta_mensagem');

    // Seletores de Miniatura (Novo na V1.3)
    const thumbContainer   = document.getElementById('thumb-container-admin');
    const imgPreview       = document.getElementById('img-preview-admin');

    // Seletores da Gaveta Técnica
    const btnToggleDiag    = document.getElementById('btn-toggle-diagnostico');
    const gavetaDiag       = document.getElementById('gaveta-diagnostico');
    const iconChevron      = document.getElementById('icon-chevron');

    // Seletores do Refresh Silencioso
    const btnRefresh       = document.getElementById('btn-refresh-chat');
    const iconRefresh      = document.getElementById('icon-refresh');

    // Recupera o Base Path e ID do Chamado
    const basePath = window.location.origin + '/'; 
    const chamadoId = respostaForm ? respostaForm.querySelector('[name="chamado_id"]').value : null;

    /**
     * FUNÇÃO: Auto-Scroll do Chat
     */
    function scrollToBottom() {
        if (adminChatBox) {
            adminChatBox.scrollTop = adminChatBox.scrollHeight;
        }
    }

    /**
     * FUNÇÃO: Exibir Miniatura (Thumbnail)
     */
    function handleFilePreview(file) {
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imgPreview.src = e.target.result;
                if (thumbContainer) thumbContainer.style.display = 'flex';
                if (filePreviewLabel) filePreviewLabel.textContent = '✓ Selecionada';
            }
            reader.readAsDataURL(file);
        }
    }

    /**
     * FUNÇÃO: Carregar Mensagens (Sincronia Silenciosa)
     */
    function refreshChatMessages() {
        if (!chamadoId) return;

        if (iconRefresh) iconRefresh.classList.add('fa-spin');

        fetch(`${basePath}api/suporte/acao_chamado.php?acao=get_mensagens&chamado_id=${chamadoId}`)
            .then(r => r.json())
            .then(data => {
                if (data.success && adminChatBox) {
                    adminChatBox.innerHTML = data.html;
                    scrollToBottom();
                }
            })
            .catch(err => console.error('Erro ao sincronizar chat:', err))
            .finally(() => {
                setTimeout(() => {
                    if (iconRefresh) iconRefresh.classList.remove('fa-spin');
                }, 500);
            });
    }

    // Executa scroll inicial
    scrollToBottom();

    /**
     * 2. EVENTO: Botão Refresh Manual
     */
    if (btnRefresh) {
        btnRefresh.addEventListener('click', refreshChatMessages);
    }

    /**
     * 3. GESTÃO DA GAVETA TÉCNICA (Accordion)
     */
    if (btnToggleDiag && gavetaDiag) {
        btnToggleDiag.addEventListener('click', function() {
            const isVisible = gavetaDiag.style.display === 'block';
            gavetaDiag.style.display = isVisible ? 'none' : 'block';
            if (iconChevron) {
                iconChevron.style.transform = isVisible ? 'rotate(0deg)' : 'rotate(180deg)';
                iconChevron.style.transition = 'transform 0.3s ease';
            }
            this.style.background = isVisible ? '#f0f2f5' : '#eef3ff';
        });
    }

    /**
     * 4. GESTÃO DE STATUS
     */
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            const novoStatus = this.value;
            if (!chamadoId) return;

            this.style.opacity = '0.5';

            fetch(`${basePath}api/suporte/acao_chamado.php?acao=mudar_status`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `chamado_id=${chamadoId}&status=${novoStatus}`
            })
            .then(r => r.json())
            .then(data => {
                this.style.opacity = '1';
                if (!data.success) alert('Erro: ' + data.message);
            });
        });
    }

    /**
     * 5. LISTENER: Seleção Manual de Ficheiro
     */
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                handleFilePreview(this.files[0]);
            }
        });
    }

    /**
     * 6. LISTENER: Colar Imagem (Ctrl+V) no Admin
     */
    if (msgTextArea) {
        msgTextArea.addEventListener('paste', function(e) {
            const items = (e.clipboardData || e.originalEvent.clipboardData).items;
            for (let index in items) {
                const item = items[index];
                if (item.kind === 'file' && item.type.startsWith('image/')) {
                    const blob = item.getAsFile();
                    const file = new File([blob], "admin_pasted_" + Date.now() + ".png", { type: blob.type });
                    
                    // Injeta o ficheiro no input real para o FormData processar
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    if (fileInput) fileInput.files = dataTransfer.files;

                    handleFilePreview(file);
                }
            }
        });
    }

    /**
     * 7. ENVIO DE RESPOSTA (Sincronizado e Silencioso)
     */
    if (respostaForm) {
        respostaForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const originalBtnContent = btnEnviar.innerHTML;
            btnEnviar.disabled = true;
            btnEnviar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> A enviar...';

            const formData = new FormData(this);

            fetch(`${basePath}api/suporte/acao_chamado.php?acao=responder`, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    respostaForm.reset();
                    // Oculta a miniatura após o envio
                    if (thumbContainer) thumbContainer.style.display = 'none';
                    if (filePreviewLabel) filePreviewLabel.style.display = 'none';
                    
                    refreshChatMessages();
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(err => {
                console.error('Erro:', err);
                alert('Erro crítico de comunicação.');
            })
            .finally(() => {
                btnEnviar.disabled = false;
                btnEnviar.innerHTML = originalBtnContent;
            });
        });
    }
});