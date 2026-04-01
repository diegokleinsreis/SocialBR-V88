/**
 * assets/js/infinite_scroll.js (V103.3)
 * MOTOR DE PAGINAÇÃO: Carregamento automático de posts via AJAX.
 * TECNOLOGIA: Intersection Observer API.
 * FIX: Prevenção de disparos em loop e otimização de gatilho (socialbr.lol).
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // --- 1. CONFIGURAÇÕES E ESTADO ---
    const postsContainer = document.getElementById('feed-posts-container');
    const sentinel = document.getElementById('infinite-scroll-sentinel');
    const loader = document.getElementById('feed-loader');
    const noMoreMsg = document.getElementById('no-more-posts-msg');

    if (!postsContainer || !sentinel) return;

    let currentPage = 1; // Começamos na 1 (carregada pelo feed.php)
    let isLoading = false;
    let hasMore = true;

    // --- 2. FUNÇÃO DE CARREGAMENTO ---
    /**
     * Busca o próximo bloco de posts na API e injeta no feed.
     */
    async function loadMorePosts() {
        // Bloqueio de segurança: não carrega se já estiver em curso ou se o fim foi atingido
        if (isLoading || !hasMore) return;

        isLoading = true;
        if (loader) loader.style.display = 'block'; // Mostra o spinner

        const nextPage = currentPage + 1;
        
        // Garante o uso da URL base correta
        const basePath = (typeof BASE_PATH !== 'undefined') ? BASE_PATH : '/';
        const apiUrl = `${basePath}api/postagens/obter_posts_paginados.php?page=${nextPage}`;

        try {
            const response = await fetch(apiUrl);
            if (!response.ok) throw new Error('Erro na rede ao buscar posts.');

            const data = await response.json();

            if (data.success) {
                if (data.html && data.html.trim() !== "") {
                    // Injeta o HTML renderizado pelo PHP no final do container
                    postsContainer.insertAdjacentHTML('beforeend', data.html);
                    currentPage = nextPage;
                    
                    // Notifica outros módulos para reinicializar (Lightbox, Enquetes, etc)
                    document.dispatchEvent(new CustomEvent('postsLoaded'));
                }

                // Atualiza o estado com base na resposta da API
                hasMore = data.has_more;
                
                if (!hasMore) {
                    if (noMoreMsg) noMoreMsg.style.display = 'block';
                    if (loader) loader.style.display = 'none';
                    // Desliga o vigia: o sentinel não precisa mais ser observado
                    observer.unobserve(sentinel);
                }
            } else {
                console.error('API Error:', data.error);
                hasMore = false; // Interrompe em caso de erro lógico na API
            }

        } catch (error) {
            console.error('Erro ao carregar posts:', error);
            hasMore = false; // Previne tentativas infinitas em caso de queda de servidor
        } finally {
            isLoading = false;
            // Se ainda houver conteúdo, esconde o loader até o próximo scroll
            if (hasMore && loader) loader.style.display = 'none';
        }
    }

    // --- 3. O OBSERVADOR (VIGIA) ---
    /**
     * rootMargin: Ajustado para 100px (Menos sensível para evitar loops iniciais).
     * threshold: 0.1 (Exige que pelo menos 10% do sentinel entre na tela).
     */
    const observerOptions = {
        root: null, // Viewport do navegador
        rootMargin: '100px', 
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            // Dispara apenas se o sentinel entrar no radar e o sistema estiver livre
            if (entry.isIntersecting && !isLoading && hasMore) {
                loadMorePosts();
            }
        });
    }, observerOptions);

    // Inicia a monitorização do fundo da página
    observer.observe(sentinel);

    // --- 4. TRATAMENTO DE INTERATIVIDADE PÓS-CARGA ---
    /**
     * Nota: A maioria das interações (Like, Comentar) usa Event Delegation
     * no body, portanto, funcionará automaticamente para os novos posts.
     */
});