/**
 * public_html/assets/js/compartilhar.js
 * * Lógica para o Módulo de Compartilhamento (V54).
 * MODIFICADO (V55-Final-v3): Bloqueia o scroll do body quando o modal está aberto.
 */
document.addEventListener('DOMContentLoaded', () => {

    // --- 1. Seletores dos Elementos do Modal ---
    const shareModal = document.getElementById('share-modal');
    const shareOverlay = document.getElementById('share-modal-overlay');
    const closeShareBtn = document.getElementById('close-share-modal');
    const shareForm = document.getElementById('share-modal-form');
    const sharePostIdInput = document.getElementById('share-modal-post-id');
    const previewContainer = document.getElementById('share-modal-preview-content');
    const shareTextarea = document.querySelector('.share-modal-textarea');
    const submitButton = document.getElementById('share-modal-submit-btn');

    if (!shareModal || !shareOverlay || !closeShareBtn || !shareForm || !previewContainer) {
        console.warn('Elementos do modal de compartilhamento não encontrados. A funcionalidade estará desativada.');
        return;
    }

    // --- 2. Funções de Abrir e Fechar ---

    /**
     * Abre o modal de compartilhamento e carrega o preview.
     * @param {string} postId - O ID do post a ser compartilhado.
     * @param {HTMLElement} shareButton - O botão que foi clicado.
     */
    function openShareModal(postId, shareButton) {
        sharePostIdInput.value = postId;

        // --- INÍCIO DA LÓGICA DE PREVIEW CORRIGIDA (Mudanças 1 e 2) ---
        const originalPostCard = shareButton.closest('.post-card');
        if (!originalPostCard) {
            console.error("Não foi possível encontrar o .post-card pai.");
            return;
        }

        let headerToClone, contentToClone, mediaToClone;
        
        // 1. Verifica se o post clicado é um compartilhamento
        const sharedWrapper = originalPostCard.querySelector('.shared-post-wrapper');
        
        if (sharedWrapper) {
            // É um compartilhamento. Clona os elementos DE DENTRO dele.
            headerToClone = sharedWrapper.querySelector('.post-header');
            contentToClone = sharedWrapper.querySelector('.post-content');
            mediaToClone = sharedWrapper.querySelector('.post-media-container');
        } else {
            // É um post original. Clona os elementos do topo.
            const postView = originalPostCard.querySelector('.post-view-mode');
            headerToClone = originalPostCard.querySelector('.post-header');
            if (postView) {
                contentToClone = postView.querySelector('.post-content');
                mediaToClone = postView.querySelector('.post-media-container');
            }
        }

        // 2. Validação (contentToClone é o único opcional, pois um post pode não ter texto)
        if (!headerToClone) {
            console.error("Não foi possível encontrar o .post-header para clonar.");
            return;
        }
        
        // 3. Clona os elementos
        const headerClone = headerToClone.cloneNode(true);

        // 4. Remove o menu de 3-pontos (Bug 2)
        const optionsMenu = headerClone.querySelector('.post-options');
        if (optionsMenu) {
            optionsMenu.remove();
        }

        // 5. Limpa o container e insere os clones
        previewContainer.innerHTML = '';
        const previewWrapper = document.createElement('div');
        previewWrapper.className = 'shared-post-wrapper';
        
        previewWrapper.appendChild(headerClone);
        
        if (contentToClone) {
            previewWrapper.appendChild(contentToClone.cloneNode(true));
        }
        if (mediaToClone) {
            previewWrapper.appendChild(mediaToClone.cloneNode(true));
        }
        
        previewContainer.appendChild(previewWrapper);
        
        // --- FIM DA LÓGICA DE PREVIEW ---

        // Mostra o modal e o overlay
        shareOverlay.classList.remove('is-hidden');
        shareModal.classList.remove('is-hidden');
        
        // --- BLOQUEIA O SCROLL ---
        document.body.style.overflow = 'hidden';
        
        setTimeout(() => {
            shareOverlay.classList.add('is-visible');
            shareModal.classList.add('is-visible');
            shareTextarea.focus();
        }, 10);
    }

    /**
     * Fecha o modal de compartilhamento.
     */
    function closeShareModal() {
        shareOverlay.classList.remove('is-visible');
        shareModal.classList.remove('is-visible');
        
        // --- LIBERA O SCROLL ---
        document.body.style.overflow = '';
        
        setTimeout(() => {
            shareOverlay.classList.add('is-hidden');
            shareModal.classList.add('is-hidden');
            
            previewContainer.innerHTML = '';
            shareTextarea.value = '';
            sharePostIdInput.value = '';
        }, 300); // 300ms (duração da transição)
    }

    // --- 3. Listeners de Eventos ---

    // Listener (delegado) para ABRIR o modal
    document.body.addEventListener('click', function(event) {
        const shareButton = event.target.closest('.btn-compartilhar');
        if (!shareButton) return;
        
        event.preventDefault();
        const postId = shareButton.dataset.postid;
        if (!postId) {
            console.error('Botão de compartilhar sem data-postid!');
            return;
        }
        
        openShareModal(postId, shareButton);
    });

    // Listeners diretos para FECHAR o modal
    closeShareBtn.addEventListener('click', (event) => {
        event.preventDefault();
        closeShareModal();
    });

    shareOverlay.addEventListener('click', () => {
        closeShareModal();
    });

    // --- LÓGICA DE ENVIO DO FORMULÁRIO (Sem recarregar a página) ---
    shareForm.addEventListener('submit', async function(event) {
        event.preventDefault();

        const originalButtonText = submitButton.innerHTML;
        submitButton.innerHTML = 'Publicando...';
        submitButton.disabled = true;

        const formData = new FormData(shareForm);
        const submittedPostId = formData.get('post_id');

        try {
            const response = await apiFetch('postagens/compartilhar_post.php', 'POST', formData);

            if (response.success) {
                closeShareModal(); // Esta função agora também libera o scroll
                showToast(response.message || 'Postagem compartilhada com sucesso!');
                
                const triggeringButton = document.querySelector(`.btn-compartilhar[data-postid="${submittedPostId}"]`);
                if (triggeringButton) {
                    triggeringButton.classList.toggle('active', response.compartilhado);
                    const postCard = triggeringButton.closest('.post-card');
                    const shareCountElement = postCard.querySelector('.share-count');
                    
                    if (shareCountElement) {
                        const novoTotal = response.novo_total;
                        let plural = (novoTotal != 1) ? 's' : '';
                        shareCountElement.textContent = ` · ${novoTotal} compartilhamento${plural}`;
                    }
                }
                
            } else {
                throw new Error(response.erro || 'Erro desconhecido ao compartilhar.');
            }

        } catch (error) {
            console.error('Erro ao compartilhar post:', error);
            showToast(error.message, 'error');
        } finally {
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
        }
    });

});