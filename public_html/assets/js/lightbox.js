/**
 * assets/js/lightbox.js
 * VERSÃO V122.0 - FIX DE SUBMISSÃO E PATHS
 * Correção: Força o uso da URL absoluta do formulário para evitar erros de rota
 * causados pela estrutura de pastas do servidor.
 */

document.addEventListener('DOMContentLoaded', function() {

    // --- 1. SELETORES GLOBAIS ---
    const overlay = document.getElementById('lightbox-overlay');
    const modal = document.getElementById('lightbox-modal');
    const closeBtn = document.getElementById('lightbox-close-btn');
    const imageWrapper = document.querySelector('.lightbox-image-wrapper');
    const detailsColumn = document.querySelector('.lightbox-details-column');

    // Se o HTML do lightbox não existir, para a execução.
    if (!overlay || !modal) return;

    // --- 2. ESTADO DA GALERIA ---
    let currentPostId = null;
    let currentMediaIndex = 0;
    let allMediaForPost = [];

    // --- 3. FUNÇÕES AUXILIARES DE SEGURANÇA ---
    
    function escapeHTML(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/[&<>"']/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }

    function nl2br(str) {
        if (typeof str === 'undefined' || str === null) return '';
        return String(str).replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br />$2');
    }

    // --- 4. FUNÇÕES DE ABRIR E FECHAR ---

    function openModal() {
        if (overlay) {
            overlay.classList.remove('is-hidden');
            overlay.classList.add('is-visible');
            document.body.style.overflow = 'hidden'; // Bloqueia o scroll do site
        }
    }

    function closeModal() {
        if (overlay) {
            overlay.classList.remove('is-visible');
            overlay.classList.add('is-hidden');
            document.body.style.overflow = ''; // Libera o scroll
            
            // Pausa vídeos
            if (imageWrapper) {
                const video = imageWrapper.querySelector('video');
                if (video) {
                    video.pause();
                    video.src = "";
                    video.load();
                }
                imageWrapper.innerHTML = '<div class="spinner"></div>';
            }
        }
    }

    // --- 5. CARREGAMENTO DE DADOS (API) ---

    function loadPostDetails(postId, initialMediaIndex = 0) {
        currentPostId = postId;
        currentMediaIndex = parseInt(initialMediaIndex) || 0;

        // Feedback visual
        if (imageWrapper) imageWrapper.innerHTML = '<div class="spinner"></div>';
        if (detailsColumn) detailsColumn.innerHTML = '<div style="padding:20px; text-align:center; color:#ccc;">Carregando...</div>';
        
        openModal();

        const url = `${BASE_PATH}api/postagens/obter_detalhes_post.php?id=${postId}`;
        
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Erro HTTP: ' + response.status);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    allMediaForPost = [];
                    // Normaliza mídias
                    if (data.post.midias && Array.isArray(data.post.midias) && data.post.midias.length > 0) {
                        allMediaForPost = data.post.midias;
                    } else if (data.post.url_media || data.post.url_midia) {
                        allMediaForPost = [{
                            url_midia: data.post.url_media || data.post.url_midia,
                            tipo_midia: data.post.tipo_media || data.post.tipo_midia || 'imagem'
                        }];
                    }

                    updateMainMedia();
                    renderSideColumnFull(data.post, data.comentarios);

                } else {
                    console.error('API Error:', data.error);
                    alert('Erro ao carregar publicação.');
                    closeModal();
                }
            })
            .catch(err => {
                console.error('Lightbox Fetch Error:', err);
                closeModal();
            });
    }

    // --- 6. RENDERIZAÇÃO DA MÍDIA ---

    function updateMainMedia() {
        if (!imageWrapper) return;
        imageWrapper.innerHTML = ''; 

        if (!allMediaForPost || allMediaForPost.length === 0) {
            imageWrapper.innerHTML = '<p style="color:#fff;">Mídia não disponível.</p>';
            return;
        }

        if (currentMediaIndex < 0) currentMediaIndex = 0;
        if (currentMediaIndex >= allMediaForPost.length) currentMediaIndex = allMediaForPost.length - 1;

        const media = allMediaForPost[currentMediaIndex];
        const rawUrl = media.url_midia || media.url_media;

        if (!rawUrl) {
            imageWrapper.innerHTML = '<p style="color:red;">Erro: URL inválida.</p>';
            return;
        }

        const fullUrl = rawUrl.startsWith('http') ? rawUrl : BASE_PATH + rawUrl;
        const tipo = media.tipo_midia || media.tipo_media || '';
        const isVideo = (tipo === 'video') || (fullUrl.match(/\.(mp4|webm|mov|ogg)$/i));

        if (isVideo) {
            const videoEl = document.createElement('video');
            videoEl.className = 'lightbox-media-item';
            videoEl.controls = true;
            videoEl.autoplay = true; 
            videoEl.playsInline = true;
            videoEl.style.maxWidth = '100%';
            videoEl.style.maxHeight = '100%';
            videoEl.style.display = 'block';
            
            const sourceEl = document.createElement('source');
            sourceEl.src = fullUrl;
            sourceEl.type = 'video/mp4';
            
            videoEl.appendChild(sourceEl);
            imageWrapper.appendChild(videoEl);
        } else {
            const imgEl = document.createElement('img');
            imgEl.className = 'lightbox-media-item';
            imgEl.src = fullUrl;
            imgEl.style.maxWidth = '100%';
            imgEl.style.maxHeight = '100%';
            imgEl.style.objectFit = 'contain';
            imgEl.style.display = 'block';
            imageWrapper.appendChild(imgEl);
        }

        // Navegação
        if (allMediaForPost.length > 1) {
            if (currentMediaIndex > 0) {
                const prevBtn = document.createElement('button');
                prevBtn.className = 'lightbox-nav-btn prev';
                prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
                prevBtn.onclick = function(e) { e.stopPropagation(); currentMediaIndex--; updateMainMedia(); };
                imageWrapper.appendChild(prevBtn);
            }
            if (currentMediaIndex < allMediaForPost.length - 1) {
                const nextBtn = document.createElement('button');
                nextBtn.className = 'lightbox-nav-btn next';
                nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
                nextBtn.onclick = function(e) { e.stopPropagation(); currentMediaIndex++; updateMainMedia(); };
                imageWrapper.appendChild(nextBtn);
            }
        }
    }

    // --- 7. RENDERIZAÇÃO LATERAL ---

    function renderSideColumnFull(post, comentarios) {
        if (!detailsColumn) return;

        const avatarUrl = post.autor_foto_perfil ? (BASE_PATH + post.autor_foto_perfil) : (BASE_PATH + 'assets/images/default-avatar.png');
        const perfilLink = BASE_PATH + 'perfil/' + post.autor_id;
        const nomeCompleto = escapeHTML(post.autor_nome) + ' ' + escapeHTML(post.autor_sobrenome);
        const dataFormatada = new Date(post.data_postagem).toLocaleDateString('pt-BR');
        
        // 1. Header
        let html = `
            <div class="lightbox-details-header">
                <div class="post-header">
                    <div class="lh-avatar"><img src="${avatarUrl}" alt="Avatar"></div>
                    <div class="lh-info">
                        <a href="${perfilLink}" class="lh-name">${nomeCompleto}</a>
                        <span class="lh-date">${dataFormatada}</span>
                    </div>
                </div>
                <div class="post-content" style="margin-top:10px;">
                    <p class="lightbox-caption">${nl2br(escapeHTML(post.conteudo_texto))}</p>
                </div>
            </div>`;

        // 2. Body
        html += `<div class="lightbox-details-body"><div class="full-comment-list">`;
        if (comentarios && comentarios.length > 0) {
            const lista = Array.isArray(comentarios) ? comentarios : Object.values(comentarios);
            lista.forEach(c => {
                const cAvatar = c.autor_foto_perfil ? (BASE_PATH + c.autor_foto_perfil) : (BASE_PATH + 'assets/images/default-avatar.png');
                const cNome = escapeHTML(c.autor_nome || c.nome || 'Usuário');
                html += `
                    <div class="comment-item" style="margin-bottom:10px;">
                        <div class="comment-avatar-small" style="float:left; margin-right:8px;">
                            <img src="${cAvatar}" style="width:32px; height:32px; border-radius:50%;">
                        </div>
                        <div class="comment-bubble" style="background:#f0f2f5; padding:8px 12px; border-radius:15px; display:inline-block;">
                            <strong>${cNome}</strong>
                            <p style="margin:0;">${nl2br(escapeHTML(c.conteudo_texto))}</p>
                        </div>
                    </div>`;
            });
        } else {
            html += `<p class="no-comments-message">Seja o primeiro a comentar.</p>`;
        }
        html += `</div></div>`;

        // 3. Footer (CSRF Token Injection)
        const csrf = (typeof CSRF_TOKEN !== 'undefined') ? CSRF_TOKEN : '';
        const userCurtiu = post.usuario_curtiu ? 'active' : '';

        // A URL da action aqui deve ser ABSOLUTA graças ao BASE_PATH
        const actionUrl = `${BASE_PATH}api/postagens/criar_comentario.php`;

        html += `
            <div class="lightbox-details-footer">
                <div class="post-stats" style="display:flex; justify-content:space-between; color:#65676b; font-size:0.9em; padding-bottom:5px; margin-bottom:5px; border-bottom:1px solid #efefef;">
                    <span class="like-count"><i class="fas fa-thumbs-up"></i> ${post.total_curtidas || 0} curtidas</span>
                    <span class="comment-count">${post.total_comentarios || 0} comentários</span>
                </div>

                <div class="post-actions" style="display:flex; justify-content:space-around; padding:5px 0;">
                    <button class="action-btn like-btn ${userCurtiu}" data-postid="${post.id}" style="background:none; border:none; cursor:pointer; color:#65676b; font-weight:600;"><i class="far fa-thumbs-up"></i> Curtir</button>
                    <button class="action-btn focus-comment-btn" style="background:none; border:none; cursor:pointer; color:#65676b; font-weight:600;"><i class="far fa-comment"></i> Comentar</button>
                    <button class="action-btn btn-compartilhar" data-postid="${post.id}" style="background:none; border:none; cursor:pointer; color:#65676b; font-weight:600;"><i class="fas fa-share"></i> Compartilhar</button>
                </div>

                <div class="add-comment-form-container" style="margin-top:10px;">
                    <form class="lightbox-comment-form" action="${actionUrl}" method="POST" style="display:flex; gap:5px;">
                        <input type="hidden" name="id_postagem" value="${post.id}">
                        <input type="hidden" name="csrf_token" value="${csrf}">
                        <input type="text" name="conteudo_texto" class="comment-input" placeholder="Escreva um comentário..." required autocomplete="off" style="flex-grow:1; border-radius:20px; border:1px solid #ddd; padding:8px 12px; background:#f0f2f5;">
                        <button type="submit" style="border:none; background:none; color:#1877f2; cursor:pointer;"><i class="fas fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>
        `;

        detailsColumn.innerHTML = html;
        
        // Foco no input
        const focusBtn = detailsColumn.querySelector('.focus-comment-btn');
        if(focusBtn) {
            focusBtn.addEventListener('click', function() {
                const input = detailsColumn.querySelector('input[name="conteudo_texto"]');
                if(input) input.focus();
            });
        }
    }

    // --- 8. EVENT LISTENER GLOBAL (CORREÇÃO DE SUBMISSÃO) ---
    
    document.addEventListener('submit', function(e) {
        // Verifica se o formulário submetido é o nosso do lightbox
        if (e.target && e.target.classList.contains('lightbox-comment-form')) {
            e.preventDefault(); // Impede recarregamento da página
            
            const form = e.target;
            const input = form.querySelector('input[name="conteudo_texto"]');
            const originalPlaceholder = input.placeholder;
            
            // 1. Feedback visual imediato
            input.disabled = true;
            input.placeholder = "Enviando...";

            const formData = new FormData(form);
            const targetUrl = form.action; // Pega a URL exata do atributo action

            console.log("Tentando enviar para:", targetUrl); // Debug no console

            // 2. Fetch direto (Sem apiFetch para evitar conflitos de path)
            fetch(targetUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Se o servidor responder com erro (ex: 500 ou 404), lança erro
                if (!response.ok) {
                    return response.text().then(text => { throw new Error('Erro HTTP: ' + response.status + ' - ' + text); });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Adiciona comentário na tela visualmente
                    if (detailsColumn) {
                        const list = detailsColumn.querySelector('.full-comment-list');
                        const noMsg = list.querySelector('.no-comments-message');
                        if(noMsg) noMsg.remove();

                        const cAvatar = data.autor_foto ? data.autor_foto : (BASE_PATH + 'assets/images/default-avatar.png');
                        const cNome = data.autor_nome || 'Você';
                        const cTexto = nl2br(escapeHTML(formData.get('conteudo_texto')));

                        const newHtml = `
                            <div class="comment-item" style="margin-bottom:10px;">
                                <div class="comment-avatar-small" style="float:left; margin-right:8px;">
                                    <img src="${cAvatar}" style="width:32px; height:32px; border-radius:50%;">
                                </div>
                                <div class="comment-bubble" style="background:#f0f2f5; padding:8px 12px; border-radius:15px; display:inline-block;">
                                    <strong>${cNome}</strong>
                                    <p style="margin:0;">${cTexto}</p>
                                </div>
                            </div>`;
                        
                        list.insertAdjacentHTML('beforeend', newHtml);
                        list.scrollTop = list.scrollHeight;
                    }
                    form.reset();
                } else {
                    console.error("Erro Lógico:", data);
                    alert('Erro ao comentar: ' + (data.error || 'Tente novamente.'));
                }
            })
            .catch(err => {
                console.error('Erro Fatal de Envio:', err);
                alert('Erro de conexão ao enviar comentário. Verifique o console.');
            })
            .finally(() => {
                // Restaura o input
                input.disabled = false;
                input.placeholder = originalPlaceholder;
                input.focus();
            });
        }
    });

    // --- 9. OUTROS EVENTOS GLOBAIS ---

    document.addEventListener('click', function(e) {
        const trigger = e.target.closest('.post-image-clickable');
        if (trigger) {
            if (e.target.tagName === 'VIDEO') return; 
            e.preventDefault();
            const pid = trigger.dataset.postid;
            const idx = trigger.dataset.mediaIndex;
            loadPostDetails(pid, idx);
        }
    });

    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (overlay) overlay.addEventListener('click', (e) => { if (e.target === overlay || e.target === imageWrapper) closeModal(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

});