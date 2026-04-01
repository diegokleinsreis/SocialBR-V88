/**
 * ARQUIVO: assets/js/sentinela_global.js
 * VERSÃO: 1.3 (Reversion: Estabilização de Rede - socialbr.lol)
 * PAPEL: Capturar erros de JS e Promises não tratadas (Sem Radar de Rede).
 */

(function() {
    // 1. --- [CONFIGURAÇÃO DE AMBIENTE] ---
    const base_path = window.base_path || '/';
    const endpoint  = `${base_path}api/admin/registrar_erro_js.php`;

    /**
     * Função mestre para enviar o rastro ao servidor via Fetch API.
     */
    function transmitirAoSentinela(dados) {
        // Blindagem contra loops: Não reporta erros se a mensagem envolver o próprio endpoint
        if (!dados.mensagem || 
            dados.mensagem.includes('registrar_erro_js.php') || 
            (dados.arquivo && dados.arquivo.includes('registrar_erro_js.php'))) {
            return;
        }

        const payload = {
            tipo: dados.tipo || 'JavaScript Error',
            mensagem: dados.mensagem,
            arquivo: dados.arquivo || 'N/A',
            linha: dados.linha || 0,
            stack: dados.stack || null,
            url_atual: window.location.href
        };

        // Envio assíncrono silencioso
        fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
            keepalive: true 
        }).catch(err => {
            console.warn("Sentinela Offline");
        });
    }

    // 2. --- [INTERCEPTOR DE ERROS GLOBAIS (window.onerror)] ---
    window.onerror = function(mensagem, fonte, linha, coluna, erro) {
        transmitirAoSentinela({
            tipo: 'JS Runtime Error',
            mensagem: mensagem,
            arquivo: fonte,
            linha: linha,
            stack: erro ? erro.stack : `Coluna: ${coluna}`
        });
        
        // Retornar false permite que o erro ainda apareça no console
        return false; 
    };

    // 3. --- [INTERCEPTOR DE PROMISES (window.onunhandledrejection)] ---
    window.onunhandledrejection = function(event) {
        const erro = event.reason;
        transmitirAoSentinela({
            tipo: 'JS Promise Rejection',
            mensagem: erro && erro.message ? erro.message : 'Promise rejeitada sem mensagem',
            arquivo: 'Async/Fetch Context',
            linha: 0,
            stack: erro && erro.stack ? erro.stack : JSON.stringify(erro)
        });
    };

    console.log("🛡️ Sentinela JS v1.3: Monitor de Código ativo (Radar de Rede removido).");
})();