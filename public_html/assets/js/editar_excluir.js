/**
 * js/editar_excluir.js
 * PAPEL: Gerenciar a edição e exclusão de postagens via Modal Global.
 * VERSÃO: V60.15 - Refatoração para Modal Minimalista de Luxo - socialbr.lol
 */
document.addEventListener('DOMContentLoaded', function() {

    // ======================================================
    // 1. LÓGICA PARA EXCLUIR POSTS (ASSÍNCRONA)
    // ======================================================
    document.body.addEventListener('click', async function(event) {
        const deleteBtn = event.target.closest('.post-delete-btn');
        
        if (deleteBtn) {
            event.preventDefault();
            
            // Integração com o MotorDeAlertas para confirmação de luxo
            const confirmado = await MotorDeAlertas.confirmar(
                'Excluir Postagem', 
                'Tem certeza que deseja excluir esta postagem? Esta ação não pode ser desfeita.',
                'Sim, excluir',
                '#e74c3c'
            );

            if (confirmado) {
                const postId = deleteBtn.dataset.postid;
                const formData = new FormData();
                formData.append('post_id', postId);

                // Uso do apiFetch para garantir segurança CSRF
                apiFetch('postagens/excluir_post.php', 'POST', formData)
                .then(data => {
                    if (data.success) {
                        const postElement = document.getElementById('post-' + postId) || deleteBtn.closest('.post-card');
                        if (postElement) {
                            postElement.style.transition = 'opacity 0.5s';
                            postElement.style.opacity = '0';
                            setTimeout(() => postElement.remove(), 500);
                        }
                        if(window.showToast) window.showToast('Postagem excluída com sucesso.');
                    } else {
                        MotorDeAlertas.erro('Erro na Exclusão', data.error || 'Ocorreu um erro.');
                    }
                })
                .catch(error => {
                    console.error('Erro ao excluir:', error);
                    MotorDeAlertas.erro('Erro Técnico', 'Não foi possível completar a ação.');
                });
            }
        }
    });

    // ======================================================
    // 2. LÓGICA PARA ABRIR O MODAL DE EDIÇÃO
    // ======================================================
    document.body.addEventListener('click', function(event) {
        const editBtn = event.target.closest('.post-edit-btn');
        
        if (editBtn) {
            event.preventDefault();
            const postId = editBtn.dataset.postid;
            const postCard = editBtn.closest('.post-card');
            
            // Localiza o texto atual dentro da estrutura limpa do post_template
            const postContentP = postCard?.querySelector('.post-content p');
            const textoAtual = postContentP ? postContentP.innerText : '';

            // Referências do Novo Modal
            const editModal = document.getElementById('post-edit-interaction-modal');
            const editOverlay = document.getElementById('post-edit-interaction-modal-overlay');
            const modalTextarea = document.getElementById('modal-edit-post-textarea');
            const modalInputId = document.getElementById('modal-edit-post-id');

            if (editModal && modalTextarea) {
                // Injeta os dados no modal global
                modalTextarea.value = textoAtual;
                modalInputId.value = postId;

                // Exibe o modal com a animação CSS
                editOverlay.classList.add('is-visible');
                editModal.classList.add('is-visible');

                // Foco automático no final do texto para UX Premium
                setTimeout(() => {
                    modalTextarea.focus();
                    modalTextarea.setSelectionRange(textoAtual.length, textoAtual.length);
                }, 300);
            }
        }
    });

    // ======================================================
    // 3. SALVAR EDIÇÃO VIA MODAL
    // ======================================================
    const btnSaveEdit = document.getElementById('btn-save-post-edit');
    if (btnSaveEdit) {
        btnSaveEdit.addEventListener('click', function() {
            const modalTextarea = document.getElementById('modal-edit-post-textarea');
            const modalInputId = document.getElementById('modal-edit-post-id');
            
            const postId = modalInputId.value;
            const novoTexto = modalTextarea.value;

            if (!postId) return;

            // Feedback visual no botão do modal
            const originalText = btnSaveEdit.innerText;
            btnSaveEdit.innerText = 'Guardando...';
            btnSaveEdit.disabled = true;

            const formData = new FormData();
            formData.append('post_id', postId);
            formData.append('new_text', novoTexto);

            apiFetch('postagens/editar_post.php', 'POST', formData)
            .then(data => {
                if (data.success) {
                    // Atualiza o texto diretamente no feed original
                    const postCardOriginal = document.getElementById('post-' + postId);
                    const postContentP = postCardOriginal?.querySelector('.post-content p');
                    
                    if (postContentP) {
                        postContentP.innerHTML = data.new_text_html;
                    }

                    // Fecha o modal suavemente
                    const editModal = document.getElementById('post-edit-interaction-modal');
                    const editOverlay = document.getElementById('post-edit-interaction-modal-overlay');
                    if (editModal) {
                        editModal.classList.remove('is-visible');
                        editOverlay.classList.remove('is-visible');
                    }

                    if(window.showToast) window.showToast('Postagem atualizada com sucesso!');
                } else {
                    MotorDeAlertas.erro('Erro na Edição', data.error || 'Ocorreu um erro.');
                }
            })
            .catch(error => {
                console.error('Erro ao editar:', error);
                MotorDeAlertas.erro('Erro de Conexão', 'Não foi possível salvar as alterações.');
            })
            .finally(() => {
                btnSaveEdit.innerText = originalText;
                btnSaveEdit.disabled = false;
            });
        });
    }

});