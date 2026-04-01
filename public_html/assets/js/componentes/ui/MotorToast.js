/**
 * assets/js/componentes/ui/MotorToast.js
 * Gerenciador Central de Notificações em Tempo Real (Toasts)
 * VERSÃO: 4.3 - Sincronização em Tempo Real com o Header (socialbr.lol)
 * PAPEL: Buscar novos alertas e comandar a atualização global da interface.
 */

const MotorToast = {
    lastId: 0,
    queue: [],
    ignoredBroadcasts: [], // Evita que alertas fechados reapareçam por delay do servidor
    isProcessing: false,
    interval: null,
    config: {
        pollingTime: 2000, // REDUZIDO PARA 2s: Mais agilidade na entrega
        displayTime: 6000, // Tempo de tela para notificações comuns
        basePath: window.location.origin + '/' // Fallback de segurança
    },

    /**
     * Inicializa o motor
     */
    init(basePath) {
        if (basePath) {
            this.config.basePath = basePath;
        } else if (typeof BASE_PATH !== 'undefined') {
            this.config.basePath = BASE_PATH;
        }
        
        if (!document.getElementById('container-notificacoes-toast')) {
            const container = document.createElement('div');
            container.id = 'container-notificacoes-toast';
            document.body.appendChild(container);
        }

        this.startPolling();
        console.log("🚀 MotorToast v4.3: Central de Alertas Ativada em " + this.config.basePath);
    },

    /**
     * Inicia a busca constante por novidades
     */
    startPolling() {
        this.checkNewToasts();
        this.interval = setInterval(() => this.checkNewToasts(), this.config.pollingTime);
    },

    /**
     * Consulta a API obter_toasts.php
     */
    async checkNewToasts() {
        try {
            const response = await fetch(`${this.config.basePath}api/notificacoes/obter_toasts.php?last_id=${this.lastId}`);
            
            if (response.status === 403) {
                clearInterval(this.interval);
                return;
            }

            const result = await response.json();

            if (result.status === 'success' && result.count > 0) {
                
                // --- [GATILHO DE SINCRONIZAÇÃO GLOBAL - v4.3] ---
                // Se chegaram novos toasts, mandamos o header atualizar o sino e o chat na hora
                if (typeof window.fetchNotifications === 'function') { 
                    window.fetchNotifications(); 
                }
                if (typeof window.fetchChatUnreadCount === 'function') { 
                    window.fetchChatUnreadCount(); 
                }

                result.data.forEach(toast => {
                    // --- FILTRO 1: SILENCIAMENTO LOCAL ---
                    if (toast.tipo === 'broadcast' && this.ignoredBroadcasts.includes(toast.id)) {
                        return; 
                    }

                    // --- FILTRO 2: ANTI-DUPLICIDADE ---
                    const isAlreadyInQueue = this.queue.some(item => item.id === toast.id && item.tipo === toast.tipo);
                    const isAlreadyOnScreen = document.querySelector(`.toast-card[data-id="${toast.id}"][data-tipo="${toast.tipo}"]`);

                    if (!isAlreadyInQueue && !isAlreadyOnScreen) {
                        this.queue.push(toast);
                    }
                    
                    if (toast.id > this.lastId && toast.tipo !== 'broadcast') {
                        this.lastId = toast.id;
                    }
                });
                this.processQueue();
            }
        } catch (error) {
            console.debug("MotorToast: Aguardando sinal...");
        }
    },

    /**
     * Processa a fila de mensagens
     */
    processQueue() {
        if (this.isProcessing || this.queue.length === 0) return;

        this.isProcessing = true;
        const toastData = this.queue.shift();
        this.renderToast(toastData);
    },

    /**
     * Cria o elemento HTML e gerencia a exibição baseada em prioridade, CTAs e Ícones
     */
    renderToast(data) {
        const container = document.getElementById('container-notificacoes-toast');
        if (!container) return;
        
        const card = document.createElement('a');
        card.href = data.link || '#';
        card.className = 'toast-card';
        
        // Atributos de identificação para o Filtro Anti-Duplicidade
        card.setAttribute('data-id', data.id);
        card.setAttribute('data-tipo', data.tipo);

        // --- 1. CONFIGURAÇÃO DE ESTÉTICA ---
        if (data.tipo === 'broadcast' && data.cor_preset) {
            card.classList.add(`toast-${data.cor_preset}`);
            if (data.cor_preset === 'gold' || data.cor_preset === 'emergency') {
                card.classList.add('toast-admin-alert');
            }
        }

        // --- 2. CONFIGURAÇÃO STICKY ---
        if (data.is_sticky) {
            card.classList.add('toast-is-sticky');
        }

        // --- 3. LÓGICA DE ÍCONE VS IMAGEM ---
        let mediaContent = '';
        if (data.tipo === 'broadcast') {
            const iconeDesejado = data.icone || data.icone_custom;
            let finalIconClass = 'fa-bullhorn';

            if (iconeDesejado) {
                finalIconClass = iconeDesejado;
            } else if (data.cor_preset === 'emergency') {
                finalIconClass = 'fa-exclamation-triangle';
            }

            mediaContent = `<div class="toast-icon-container"><i class="fas ${finalIconClass}"></i></div>`;
        } else {
            const fotoUrl = data.foto || (this.config.basePath + 'assets/images/default-avatar.png');
            mediaContent = `<img src="${fotoUrl}" class="toast-avatar" alt="Avatar">`;
        }

        // --- 4. LÓGICA DE BOTÃO CALL TO ACTION ---
        let ctaHtml = '';
        if (data.cta_texto) {
            ctaHtml = `
                <div class="toast-cta-container">
                    <div class="toast-btn">${data.cta_texto}</div>
                </div>
            `;
            if (data.cta_link) {
                card.href = data.cta_link;
            }
        }

        // --- 5. CONSTRUÇÃO DO HTML ---
        card.innerHTML = `
            <div class="toast-close-btn" title="Fechar">
                <i class="fas fa-times"></i>
            </div>
            ${mediaContent}
            <div class="toast-content">
                ${data.titulo ? `<strong>${data.titulo}</strong>` : ''}
                <p>${data.mensagem}</p>
                ${ctaHtml}
            </div>
            <div class="toast-progress"></div>
        `;

        container.appendChild(card);

        // --- 6. LÓGICA DO BOTÃO DE FECHAR ---
        const closeBtn = card.querySelector('.toast-close-btn');
        closeBtn.addEventListener('click', (e) => {
            e.preventDefault(); 
            e.stopPropagation(); 

            if (data.tipo === 'broadcast') {
                this.ignoredBroadcasts.push(data.id);
                this.marcarComoLido(data.id);
            }
            
            this.removeToast(card);
        });

        // --- 7. AGENDAMENTO DE REMOÇÃO ---
        if (!data.is_sticky) {
            const autoRemove = setTimeout(() => {
                this.removeToast(card);
            }, this.config.displayTime);
            card.dataset.timer = autoRemove;
        }
    },

    /**
     * Remove o toast com animação OLED e libera a fila
     */
    removeToast(card) {
        if (card.dataset.timer) clearTimeout(card.dataset.timer);
        
        card.classList.add('toast-fade-out');
        card.addEventListener('transitionend', () => {
            card.remove();
            this.isProcessing = false;
            this.processQueue(); 
        }, { once: true });
    },

    /**
     * Sincroniza a leitura com o servidor
     */
    async marcarComoLido(avisoId) {
        if (avisoId === 0) return; // Ignora prévias
        try {
            const formData = new FormData();
            formData.append('aviso_id', avisoId);
            await fetch(`${this.config.basePath}api/notificacoes/marcar_aviso_lido.php`, {
                method: 'POST',
                body: formData
            });
        } catch (error) {
            console.error("MotorToast: Erro ao silenciar aviso", error);
        }
    }
};

/**
 * AUTO-INICIALIZAÇÃO
 */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        const path = (typeof BASE_PATH !== 'undefined') ? BASE_PATH : '/';
        MotorToast.init(path);
    });
} else {
    const path = (typeof BASE_PATH !== 'undefined') ? BASE_PATH : '/';
    MotorToast.init(path);
}