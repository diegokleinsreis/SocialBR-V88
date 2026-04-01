/**
 * SETTINGS MEDIA HUB JS (V65.1)
 * Lida com o auto-upload de mídia e a remoção de capa via AJAX.
 * Desenvolvido para garantir uma experiência fluida (estilo App).
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ==========================================================
    // 1. LÓGICA DE AUTO-UPLOAD (Capa e Avatar)
    // ==========================================================
    
    // Selecionamos os inputs de ficheiro definidos no template modular
    const mediaInputs = document.querySelectorAll('#hub-cover-input, #hub-avatar-input');
    
    mediaInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Verifica se um ficheiro foi realmente selecionado
            if (this.files && this.files[0]) {
                
                // Exibe feedback visual imediato usando a função global do sistema
                if (typeof showToast === 'function') {
                    showToast('A processar imagem...', 'success');
                }

                // Envia o formulário específico onde o input está inserido
                // Isso permite que o avatar e a capa usem APIs diferentes de forma independente
                const parentForm = this.closest('form');
                if (parentForm) {
                    parentForm.submit();
                }
            }
        });
    });

    // ==========================================================
    // 2. LÓGICA DE REMOÇÃO DE CAPA VIA AJAX
    // ==========================================================
    
    const btnRemoverCapa = document.getElementById('btn-remover-capa');
    
    if (btnRemoverCapa) {
        btnRemoverCapa.addEventListener('click', async function() {
            
            // Confirmação simples de segurança para evitar cliques acidentais
            if (!confirm('Tem certeza que deseja remover sua foto de capa?')) {
                return;
            }

            try {
                // Utilizamos a apiFetch global para garantir o envio do CSRF Token
                // A API remover_capa.php será criada no próximo passo
                const response = await apiFetch('usuarios/remover_capa.php', 'POST', new FormData());
                
                if (response.success) {
                    // Feedback de sucesso
                    if (typeof showToast === 'function') {
                        showToast('Capa removida com sucesso!');
                    }

                    // ATUALIZAÇÃO VISUAL EM TEMPO REAL (Sem recarregar a página)
                    const coverPreview = document.querySelector('.settings-cover-preview');
                    if (coverPreview) {
                        // Define a imagem para o padrão do sistema
                        const defaultCoverUrl = BASE_PATH + 'assets/images/default-cover.jpg';
                        coverPreview.style.backgroundImage = `url('${defaultCoverUrl}')`;
                    }

                    // Remove o botão de exclusão e a camada de sombra, já que não há mais foto personalizada
                    btnRemoverCapa.remove();
                    const topOverlay = document.querySelector('.settings-cover-overlay-top');
                    if (topOverlay) topOverlay.style.opacity = '0';

                } else {
                    // Trata erro retornado pela API
                    if (typeof showToast === 'function') {
                        showToast(response.erro || 'Erro ao remover a capa.', 'error');
                    }
                }
            } catch (error) {
                console.error('Erro na requisição AJAX:', error);
                if (typeof showToast === 'function') {
                    showToast('Erro de conexão com o servidor.', 'error');
                }
            }
        });
    }
});