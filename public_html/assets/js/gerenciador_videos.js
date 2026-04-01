/**
 * assets/js/gerenciador_videos.js
 * VERSÃO EQUILIBRADA (Visual + Performance)
 * Objetivo: Carrega a CAPA do vídeo ao aparecer na tela, mas não o vídeo todo.
 * Pausa automaticamente se sair da tela.
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Configuração do Observador (Vigia)
    const options = {
        root: null, // Janela do navegador
        rootMargin: '50px', // Começa a carregar um pouco antes de entrar na tela (suavidade)
        threshold: 0.25 // Dispara quando 25% do vídeo estiver visível
    };

    /**
     * Função executada sempre que um vídeo entra ou sai da tela
     */
    const callback = (entries, observer) => {
        entries.forEach(entry => {
            const video = entry.target;

            if (entry.isIntersecting) {
                // --- VÍDEO ENTROU NA TELA ---
                // Se o vídeo estava "dormindo" (preload="none"), mudamos para "metadata".
                // ISSO FAZ A CAPA APARECER (baixa o primeiro frame e a duração).
                if (video.getAttribute('preload') === 'none') {
                    video.setAttribute('preload', 'metadata');
                }
            } else {
                // --- VÍDEO SAIU DA TELA ---
                // Se estiver a tocar, pausamos para não incomodar.
                if (!video.paused) {
                    video.pause(); 
                }
            }
        });
    };

    // Cria o observador
    const observer = new IntersectionObserver(callback, options);

    // Função global para registrar vídeos
    window.registrarVideos = function() {
        const videos = document.querySelectorAll('video.lazy-video');
        videos.forEach(video => {
            observer.observe(video);
        });
    };

    // Executa a primeira vez
    window.registrarVideos();

    // Integração com Scroll Infinito (para novos vídeos que chegam)
    const feedContainer = document.getElementById('feed-posts-container');
    if (feedContainer) {
        const mutationObserver = new MutationObserver(() => {
            window.registrarVideos();
        });
        mutationObserver.observe(feedContainer, { childList: true, subtree: true });
    }
});