/**
 * post_text_expander.js
 *
 * Lógica para o "ver mais..." em posts de texto longo E comentários longos.
 * Expande e encolhe o conteúdo.
 */
document.addEventListener('DOMContentLoaded', function() {

    // Função auxiliar para processar a expansão do texto
    function toggleText(contentContainer) {
        const idParts = contentContainer.id.split('-');
        if (idParts.length < 4) return;
        
        const type = idParts[0]; // "post" ou "comment"
        const contentId = idParts[3]; // ID

        // Generaliza para funcionar com posts, comentários, ou o lightbox
        const wrapper = contentContainer.closest('.post-card, .comment-item-wrapper, .comment-preview-item, .lightbox-details-column');
        if (!wrapper) return;

        const shortTextDiv = wrapper.querySelector(`#${type}-content-short-${contentId}`);
        const fullTextDiv = wrapper.querySelector(`#${type}-content-full-${contentId}`);

        if (shortTextDiv && fullTextDiv) {
            shortTextDiv.classList.toggle('is-hidden');
            fullTextDiv.classList.toggle('is-hidden');
        }
    }

    // Adiciona um "ouvinte" de cliques ao corpo da página (event delegation)
    document.body.addEventListener('click', function(event) {

        const target = event.target;

        // -------------------------------------------------------------
        // --- LÓGICA PARA ABRIR (Expandir) ---
        // 1. Clicar no Link "ver mais..." (comportamento original)
        const seeMoreLink = target.closest('.see-more-link');
        if (seeMoreLink) {
            event.preventDefault(); // Impede que o link '#' suba a página
            
            // O link "ver mais" tem o data-content-id no seu próprio elemento, mas 
            // precisamos do ID do elemento pai que estamos a expandir.
            const contentId = seeMoreLink.dataset.contentId;
            const type = seeMoreLink.dataset.type;

            const wrapper = seeMoreLink.closest('.post-card, .comment-item-wrapper, .comment-preview-item, .lightbox-details-column');
            if (!wrapper) return;

            const shortTextDiv = wrapper.querySelector(`#${type}-content-short-${contentId}`);
            
            // Se encontrarmos o container curto, executamos a expansão
            if (shortTextDiv) {
                toggleText(shortTextDiv);
            }
            return; 
        }

        // 2. Clicar em QUALQUER PARTE do texto curto (Novo requisito)
        // Usamos o .post-content-short (ou .comment-content-short)
        const shortTextDivClicked = target.closest('.post-content-short, .comment-content-short');
        if (shortTextDivClicked) {
             event.preventDefault(); 
             // Se o clique foi no texto curto, executamos a expansão
             toggleText(shortTextDivClicked);
             return;
        }

        
        // -------------------------------------------------------------
        // --- LÓGICA PARA FECHAR (Encolher) ---
        // Clicar em QUALQUER PARTE do texto expandido
        const fullTextDivClicked = target.closest('.post-content-full, .comment-content-full');
        if (fullTextDivClicked) {
             event.preventDefault(); 
             // Se o clique foi no texto completo, executamos o fecho
             toggleText(fullTextDivClicked);
             return;
        }
    });
});