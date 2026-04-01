/**
 * assets/js/comentarios.js
 * VERSÃO V9.0: OLED Unification & SweetAlert2 Premium
 * PAPEL: Gerir interações (Feed e Modal) com captura global de identidade pendente.
 * VERSÃO: V9.0 ( socialbr.lol )
 */

document.addEventListener('DOMContentLoaded', function() {

    // --- 1. SELETORES GLOBAIS E DO MODAL ---
    const modal = document.getElementById('comment-interaction-modal');
    const modalBody = document.getElementById('modal-full-comments-list');
    const modalForm = document.getElementById('modal-comment-form');
    const modalInput = document.getElementById('modal-comment-input');
    const modalPostIdInput = document.getElementById('modal-post-id');
    const modalParentIdInput = document.getElementById('modal-parent-id');
    const replyBadge = document.getElementById('replying-to-info');
    const replyName = document.getElementById('replying-to-name');
    const closeBtn = document.getElementById('close-comment-modal');

    // --- 2. FUNÇÕES DE CARREGAMENTO (MODAL) ---

    async function abrirModalComentarios(postId) {
        if (!modal || !modalBody) return;

        cancelarResposta();
        if (modalPostIdInput) modalPostIdInput.value = postId;
        modalBody.innerHTML = '<div class="modal-loading-placeholder"><i class="fas fa-spinner fa-spin"></i> A carregar interações...</div>';
        
        modal.classList.remove('is-hidden');
        document.body.style.overflow = 'hidden'; 

        try {
            const data = await apiFetch(`comentarios/listar_comentarios_modal.php?post_id=${postId}`, 'GET');

            if (data.success) {
                const likeCount = document.getElementById('modal-like-count');
                const shareCount = document.getElementById('modal-share-count');
                
                if (likeCount) likeCount.textContent = data.stats.curtidas;
                if (shareCount) shareCount.textContent = data.stats.compartilhamentos;

                if (data.comentarios && data.comentarios.length > 0) {
                    modalBody.innerHTML = renderizarArvoreComentarios(data.comentarios);
                } else {
                    modalBody.innerHTML = '<p style="text-align:center; color:#65676b; padding:20px;">Ainda não há comentários. Seja o primeiro!</p>';
                }
            } else {
                modalBody.innerHTML = `<p style="color:red; text-align:center;">${data.error}</p>`;
            }
        } catch (error) {
            modalBody.innerHTML = '<p style="color:red; text-align:center;">Erro ao carregar comentários.</p>';
        }
    }

    function renderizarArvoreComentarios(comentarios, nivel = 0) {
        let html = '';
        comentarios.forEach(c => {
            const avatar = c.foto_perfil_url ? BASE_PATH + c.foto_perfil_url : BASE_PATH + 'assets/images/default-avatar.png';
            const hasReplies = c.respostas && c.respostas.length > 0;
            const dataHumana = c.data_comentario;

            const loggedUserId = window.LOGGED_USER_ID || 0;
            const isAuthor = (loggedUserId > 0 && c.autor_id == loggedUserId);
            const isLoggedIn = (loggedUserId > 0);

            html += `
                <div class="comment-item-container" id="comment-wrapper-${c.id}" style="margin-bottom: 18px;">
                    <div class="comment-item" id="comment-${c.id}" style="display: flex; gap: 10px; position: relative;">
                        <div class="comment-author-avatar">
                            <img src="${avatar}" alt="${c.nome}" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                        </div>
                        <div class="comment-content-main" style="flex-grow: 1; min-width: 0;">
                            <div class="comment-bubble-row" style="display: flex; align-items: flex-start; gap: 8px; width: 100%;">
                                <div class="comment-bubble comment-view-mode" style="background: #f0f2f5; padding: 8px 12px; border-radius: 18px; flex-grow: 0; max-width: 85%;">
                                    <a href="${BASE_PATH}perfil/${c.autor_id}" class="comment-author-name" style="font-weight: 700; font-size: 0.85rem; color: #050505; text-decoration: none;">${c.nome} ${c.sobrenome}</a>
                                    <p class="comment-text" style="margin: 2px 0 0 0; font-size: 0.95rem; line-height: 1.3; color: #050505;">${c.conteudo_texto}</p>
                                </div>
                                ${isLoggedIn ? `
                                <div class="comment-options-container" style="align-self: center;">
                                    <button class="comment-options-btn" style="background:none; border:none; color:#65676b; cursor:pointer; padding: 8px; border-radius: 50%; transition: background 0.2s;">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                    <div class="comment-options-menu is-hidden" style="position: absolute; right: 0; background: #fff; border: 1px solid #dddfe2; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 100; padding: 5px; width: 160px;">
                                        ${isAuthor ? `
                                            <a href="#" class="comment-edit-btn" data-comment-id="${c.id}" style="display:block; padding:8px 12px; color:#050505; text-decoration:none; font-size:0.9rem;"><i class="fas fa-edit"></i> Editar</a>
                                            <a href="#" class="comment-delete-btn" data-comment-id="${c.id}" style="display:block; padding:8px 12px; color:#dc3545; text-decoration:none; font-size:0.9rem;"><i class="fas fa-trash"></i> Eliminar</a>
                                        ` : `
                                            <a href="#" class="post-report-btn" data-content-type="comentario" data-content-id="${c.id}" style="display:block; padding:8px 12px; color:#e67e22; text-decoration:none; font-size:0.9rem;">
                                                <i class="fas fa-flag"></i> Denunciar Comentário
                                            </a>
                                        `}
                                    </div>
                                </div>` : ''}
                            </div>
                            <div class="comment-edit-form is-hidden" style="margin: 8px 0; width: 90%;">
                                <textarea class="comment-edit-textarea" style="width:100%; border-radius:12px; border:1px solid #dddfe2; padding:10px; font-family: inherit; resize: none;">${c.conteudo_texto.replace(/<br\s*\/?>/mg, "\n")}</textarea>
                                <div style="display:flex; gap:8px; margin-top:5px; justify-content: flex-start;">
                                    <button class="comment-edit-save" data-comment-id="${c.id}" style="font-size:0.8rem; background:#0C2D54; color:white; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; font-weight: 600;">Guardar</button>
                                    <button class="comment-edit-cancel" data-comment-id="${c.id}" style="font-size:0.8rem; background:#e4e6eb; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; font-weight: 600;">Cancelar</button>
                                </div>
                            </div>
                            <div class="comment-actions" style="margin-top: 4px; padding-left: 12px; font-size: 0.8rem; display: flex; align-items: center; gap: 12px; color: #65676b;">
                                <a href="#" class="comment-like-btn ${c.usuario_curtiu ? 'active' : ''}" data-comment-id="${c.id}" style="text-decoration: none; color: inherit; font-weight: 700;">Curtir</a>
                                <a href="#" class="modal-reply-trigger" data-comment-id="${c.id}" data-author="${c.nome}" style="text-decoration: none; color: inherit; font-weight: 700;">Responder</a>
                                <span>${dataHumana}</span>
                                ${c.total_curtidas > 0 ? `<span class="comment-like-count" data-comment-id="${c.id}" style="background:#fff; padding:2px 6px; border-radius:10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 4px; color: #0C2D54;"><i class="fas fa-thumbs-up" style="font-size: 0.7rem;"></i> ${c.total_curtidas}</span>` : ''}
                            </div>
                        </div>
                    </div>
                    ${hasReplies ? `
                        <div class="comment-replies" style="margin-left: 42px; border-left: 2px solid #f0f2f5; padding-left: 12px; margin-top: 8px;">
                            ${renderizarArvoreComentarios(c.respostas, nivel + 1)}
                        </div>
                    ` : ''}
                </div>
            `;
        });
        return html;
    }

    // --- 3. LÓGICA DE RESPOSTA ---
    function ativarResposta(commentId, authorName) {
        if (modalParentIdInput) modalParentIdInput.value = commentId;
        if (replyName) replyName.textContent = authorName;
        if (replyBadge) replyBadge.classList.remove('is-hidden');
        if (modalInput) {
            modalInput.focus();
            modalInput.placeholder = "Escreva uma resposta...";
        }
    }

    function cancelarResposta() {
        if (modalParentIdInput) modalParentIdInput.value = '';
        if (replyBadge) replyBadge.classList.add('is-hidden');
        if (modalInput) modalInput.placeholder = "Escreva um comentário...";
    }

    /**
     * [NOVO] MOTOR DE ENVIO UNIFICADO (AJAX + SWEETALERT2)
     * Processa tanto o formulário do Modal quanto o do Feed.
     */
    async function processarEnvioComentario(formElement) {
        const btn = formElement.querySelector('button[type="submit"]');
        const originalIcon = btn.innerHTML;
        const inputField = formElement.querySelector('input[type="text"], textarea');

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        const formData = new FormData(formElement);
        // Obtém o endpoint do atributo 'action' do formulário
        const endpoint = formElement.getAttribute('action') || 'api/comentarios/criar_comentario_ajax.php';

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();

            if (data.success) {
                if (inputField) inputField.value = '';
                cancelarResposta();
                
                // Se estiver no modal, recarrega a lista. Se estiver no feed, redireciona ou atualiza.
                if (formElement.id === 'modal-comment-form') {
                    abrirModalComentarios(modalPostIdInput.value);
                } else if (data.redirect) {
                    window.location.href = data.redirect;
                }
                
                if (window.showToast) showToast('Interação enviada!');
            } else {
                // CAPTURA PREMIUM: Identidade Pendente
                if (data.error === 'verificacao_pendente') {
                    Swal.fire({
                        title: '🛡️ Quase lá!',
                        text: data.message || 'Confirme o seu e-mail para poder postar comentários e interagir na rede.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#0C2D54',
                        cancelButtonColor: '#606770',
                        confirmButtonText: 'Verificar E-mail Agora',
                        cancelButtonText: 'Agora não'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = BASE_PATH + 'configurar_perfil?tab=conta';
                        }
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Ops!', text: data.message || 'Não foi possível enviar o comentário.', confirmButtonColor: '#0C2D54' });
                }
            }
        } catch (error) {
            console.error("Erro no motor de comentários:", error);
            Swal.fire({ icon: 'error', title: 'Erro de Rede', text: 'Não foi possível conectar ao servidor.', confirmButtonColor: '#d33' });
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalIcon;
        }
    }

    // --- 4. LISTENERS DE SUBMISSÃO ---
    
    // Listener para o Modal
    if (modalForm) {
        modalForm.addEventListener('submit', (e) => {
            e.preventDefault();
            processarEnvioComentario(modalForm);
        });
    }

    // Listener Global para o Feed (Delegação de Eventos para .ajax-comment-form)
    document.body.addEventListener('submit', function(e) {
        if (e.target.classList.contains('ajax-comment-form')) {
            e.preventDefault();
            processarEnvioComentario(e.target);
        }
    });

    // --- 5. DELEGAÇÃO DE EVENTOS CENTRALIZADA ---
    document.body.addEventListener('click', async function(e) {
        const target = e.target;

        // Abrir Modal
        if (target.closest('.open-modal-comments') || target.closest('.btn-comentar-trigger')) {
            e.preventDefault();
            const container = target.closest('[data-postid]');
            if (container) abrirModalComentarios(container.dataset.postid);
            return;
        }

        // Fechar Modal
        if (target === closeBtn || target.closest('#close-comment-modal') || (modal && target === modal)) {
            if (modal) {
                modal.classList.add('is-hidden');
                document.body.style.overflow = '';
            }
            return;
        }

        // Trigger de Resposta
        if (target.classList.contains('modal-reply-trigger')) {
            e.preventDefault();
            ativarResposta(target.dataset.commentId, target.dataset.author);
            return;
        }

        // Editar Comentário (Abrir form)
        const editBtn = target.closest('.comment-edit-btn');
        if (editBtn) {
            e.preventDefault();
            const wrap = document.getElementById(`comment-wrapper-${editBtn.dataset.commentId}`);
            if (wrap) {
                wrap.querySelector('.comment-view-mode').classList.add('is-hidden');
                wrap.querySelector('.comment-edit-form').classList.remove('is-hidden');
                wrap.querySelector('.comment-edit-textarea').focus();
            }
            return;
        }

        // Cancelar Edição
        const cancelEdit = target.closest('.comment-edit-cancel');
        if (cancelEdit) {
            const wrap = document.getElementById(`comment-wrapper-${cancelEdit.dataset.commentId}`);
            wrap.querySelector('.comment-view-mode').classList.remove('is-hidden');
            wrap.querySelector('.comment-edit-form').classList.add('is-hidden');
            return;
        }

        // Salvar Edição
        const saveEdit = target.closest('.comment-edit-save');
        if (saveEdit) {
            e.preventDefault();
            const commentId = saveEdit.dataset.commentId;
            const wrap = document.getElementById(`comment-wrapper-${commentId}`);
            const newText = wrap.querySelector('.comment-edit-textarea').value;
            
            const fd = new FormData();
            fd.append('comment_id', commentId);
            fd.append('new_text', newText);

            try {
                const data = await apiFetch('comentarios/editar_comentario.php', 'POST', fd);
                if (data.success) {
                    wrap.querySelector('.comment-view-mode .comment-text').innerHTML = data.new_text_html;
                    wrap.querySelector('.comment-view-mode').classList.remove('is-hidden');
                    wrap.querySelector('.comment-edit-form').classList.add('is-hidden');
                    if (window.showToast) showToast('Atualizado!');
                }
            } catch (err) { console.error(err); }
            return;
        }

        // Excluir Comentário
        const deleteBtn = target.closest('.comment-delete-btn');
        if (deleteBtn) {
            e.preventDefault();
            const confirmado = await MotorDeAlertas.confirmar(
                'Eliminar Comentário', 
                'Tens a certeza que desejas eliminar esta interação?', 
                'Sim, eliminar', 
                '#dc3545'
            );
            
            if (confirmado) {
                const fd = new FormData();
                fd.append('comment_id', deleteBtn.dataset.commentId);
                try {
                    const data = await apiFetch('comentarios/excluir_comentario.php', 'POST', fd);
                    if (data.success) {
                        const wrap = document.getElementById(`comment-wrapper-${deleteBtn.dataset.commentId}`);
                        if (wrap) wrap.innerHTML = '<p style="font-style:italic;color:#65676b;padding:10px;font-size:0.85rem;">Comentário eliminado.</p>';
                    }
                } catch (err) { console.error(err); }
            }
            return;
        }
    });

});