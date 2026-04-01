/**
 * assets/js/barra_carregamento.js
 * PAPEL: Controle inteligente da barra de progresso (NProgress).
 * VERSÃO: 1.1 (Fix: Race Condition & Auto-Finish - socialbr.lol)
 */

(function() {
    // 1. Configura e inicia a barra com segurança (Fix: Cannot read properties of null)
    if (typeof NProgress !== 'undefined') {
        NProgress.configure({ 
            showSpinner: false, // Garante que a bolinha giratória NÃO apareça
            trickleSpeed: 200,
            minimum: 0.08 
        });

        /**
         * AJUSTE DE SEGURANÇA:
         * Só inicia se o body existir. Se o script carregar antes do body (comum no Mobile/Brave),
         * ele aguarda o DOMContentLoaded para não gerar erro de 'null'.
         */
        if (document.body) {
            NProgress.start();
        } else {
            window.addEventListener('DOMContentLoaded', function() {
                NProgress.start();
            });
        }
    }

    /**
     * 2. Finalização no Evento 'load'
     * O evento 'load' é disparado quando TUDO terminou (DOM, Imagens, Scripts).
     * Como o erro de 'null' foi corrigido acima, este bloco agora será executado
     * corretamente, fazendo a barra sumir.
     */
    window.addEventListener('load', function() {
        if (typeof NProgress !== 'undefined') {
            NProgress.done();
        }
    });

    /**
     * 3. REDE DE SEGURANÇA (Anti-Barra Infinita)
     * Se uma imagem externa demorar muito e travar o evento 'load', 
     * forçamos a barra a sumir após 4 segundos para não poluir o visual.
     */
    setTimeout(function() {
        if (typeof NProgress !== 'undefined') {
            NProgress.done();
        }
    }, 4000);

})();