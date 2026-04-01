/**
 * assets/js/form_postagem.js
 * VERSÃO V91.0 - Blindagem de Segurança & Verificação de E-mail (socialbr.lol)
 * PAPEL: Gestão de posts, enquetes, links e bloqueio de mídia para não-verificados.
 */

document.addEventListener('DOMContentLoaded', function() {
    
    const form = document.getElementById('create-post-form');
    const textArea = document.getElementById('post-text-area');
    const pollContainer = document.getElementById('poll-setup-container');
    const linkContainer = document.getElementById('link-preview-container');
    
    const btnTriggerPoll = document.getElementById('btn-trigger-poll');
    const btnAddOption = document.getElementById('add-poll-option');
    const btnRemovePoll = document.getElementById('remove-poll');
    const btnRemoveLink = document.getElementById('remove-link-preview');

    if (!form) return;

    // --- 1. LÓGICA DE ENQUETES ---
    if (btnTriggerPoll) {
        btnTriggerPoll.addEventListener('click', () => {
            pollContainer.classList.remove('is-hidden');
            linkContainer.classList.add('is-hidden'); 
        });
    }

    if (btnRemovePoll) {
        btnRemovePoll.addEventListener('click', () => {
            pollContainer.classList.add('is-hidden');
            document.getElementById('poll-question-input').value = '';
            const options = document.querySelectorAll('#poll-options-list .poll-option-input');
            options.forEach((opt, index) => {
                if (index > 1) opt.remove(); else opt.querySelector('input').value = '';
            });
        });
    }

    if (btnAddOption) {
        btnAddOption.addEventListener('click', () => {
            const optionsList = document.getElementById('poll-options-list');
            const currentOptions = optionsList.querySelectorAll('.poll-option-input').length;
            if (currentOptions < 5) {
                const div = document.createElement('div');
                div.className = 'poll-option-input';
                div.innerHTML = `<input type="text" name="poll_options[]" placeholder="Opção ${currentOptions + 1}"><button type="button" class="remove-opt" onclick="this.parentNode.remove()">×</button>`;
                optionsList.appendChild(div);
            }
        });
    }

    // --- 2. DETEÇÃO AUTOMÁTICA DE LINKS ---
    let lastUrl = "";
    if (textArea) {
        textArea.addEventListener('input', function() {
            const urlRegex = /(https?:\/\/[^\s]+)/g;
            const found = this.value.match(urlRegex);

            // Só dispara se encontrar link e se não estivermos a configurar uma enquete
            if (found && found[0] !== lastUrl && pollContainer.classList.contains('is-hidden')) {
                lastUrl = found[0];
                fetchLinkMetadata(lastUrl);
            }
        });
    }

    async function fetchLinkMetadata(url) {
        try {
            const response = await fetch(`${BASE_PATH}api/utils/link_scraper.php?url=${encodeURIComponent(url)}`);
            const data = await response.json();

            if (data.success) {
                linkContainer.classList.remove('is-hidden');
                document.getElementById('lp-title').innerText = data.title;
                document.getElementById('lp-desc').innerText = data.description;
                document.getElementById('lp-img').src = data.image || `${BASE_PATH}assets/images/link-placeholder.png`;
                document.getElementById('lp-domain').innerText = new URL(url).hostname;

                document.getElementById('input-lp-url').value = url;
                document.getElementById('input-lp-title').value = data.title;
                document.getElementById('input-lp-image').value = data.image;
                document.getElementById('input-lp-desc').value = data.description;
            }
        } catch (error) {
            console.error('Erro no link scraper:', error);
        }
    }

    if (btnRemoveLink) {
        btnRemoveLink.addEventListener('click', () => {
            linkContainer.classList.add('is-hidden');
            lastUrl = "";
            document.querySelectorAll('input[id^="input-lp-"]').forEach(input => input.value = '');
        });
    }

    // --- 3. ENVIO AJAX COM SWEETALERT2 & TRAVA DE SEGURANÇA ---
    form.addEventListener('submit', function(event) {
        event.preventDefault(); 
        const submitButton = form.querySelector('button[type="submit"]');
        const originalBtnHTML = submitButton.innerHTML;
        
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publicando...';
        
        const formData = new FormData(form);
        if (typeof CSRF_TOKEN !== 'undefined') formData.append('csrf_token', CSRF_TOKEN);

        fetch(form.action, { method: 'POST', body: formData })
        .then(r => {
            if (!r.ok) throw new Error('Erro na resposta do servidor.');
            return r.json();
        })
        .then(data => {
            if (data.success) {
                // Alerta de Sucesso Premium
                Swal.fire({
                    icon: 'success',
                    title: 'Publicado!',
                    text: data.toast_message || 'A sua publicação foi enviada com sucesso.',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                }).then(() => {
                    location.reload();
                });
            } else {
                // [NOVO] TRATAMENTO PARA VERIFICAÇÃO DE E-MAIL PENDENTE
                if (data.error === 'verificacao_pendente') {
                    Swal.fire({
                        title: '🛡️ Quase lá!',
                        text: data.message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#0C2D54', // Azul Oficial
                        cancelButtonColor: '#606770',
                        confirmButtonText: 'Verificar E-mail Agora',
                        cancelButtonText: 'Agora não'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Redireciona para a aba de conta nas configurações
                            const path = (typeof BASE_PATH !== 'undefined') ? BASE_PATH : '/';
                            window.location.href = path + 'configurar_perfil?tab=conta';
                        }
                    });
                } else {
                    // Alerta de Erro Comum
                    Swal.fire({
                        icon: 'error',
                        title: 'Ops!',
                        text: data.error || 'Ocorreu um erro ao processar a sua publicação.',
                        confirmButtonColor: '#0C2D54'
                    });
                }
                
                submitButton.disabled = false;
                submitButton.innerHTML = originalBtnHTML;
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Erro de Ligação',
                text: 'Não foi possível conectar ao servidor. Verifique a sua internet.',
                confirmButtonColor: '#d33'
            });
            submitButton.disabled = false;
            submitButton.innerHTML = originalBtnHTML;
        });
    });
});