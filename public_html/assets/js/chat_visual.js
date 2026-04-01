/**
 * assets/js/chat_visual.js
 * Componente: Gestão de Interface e Renderização.
 * PAPEL: Transformar JSON em HTML, gerir Scroll e Identidade de Grupos.
 * VERSÃO: V68.7 (Upgrade Visual: Empty State Premium - socialbr.lol)
 */

const chatVisual = {
    containerMensagens: null,
    scrollArea: null,
    chatAnchor: null,
    ultimaMensagemId: 0,
    chatType: 'privada', 
    corOficial: "#0C2D54",

    /**
     * Mapeia os elementos do DOM necessários.
     */
    init: function() {
        console.log("🎨 Motor Visual V68.7 (OLED UI) iniciado...");
        this.containerMensagens = document.getElementById('messages-container');
        this.scrollArea = document.getElementById('chat-messages-scroll');
        this.chatAnchor = document.getElementById('chat-anchor');

        const win = document.querySelector('.chat-window-container');
        this.chatType = win ? win.dataset.tipo : 'privada';
    },

    /**
     * Renderiza o array de mensagens na tela.
     */
    renderizarMensagens: function(mensagens) {
        if (!this.containerMensagens || !mensagens) return;

        const loader = document.getElementById('messages-loader');
        if (loader) loader.classList.add('is-hidden');

        // --- [NOVO] DESIGN DO ESTADO VAZIO (EMPTY STATE) ---
        if (mensagens.length === 0) {
            if (this.containerMensagens.innerHTML === '') {
                const prompt = (this.chatType === 'grupo') 
                    ? 'Envie uma mensagem para o grupo!' 
                    : 'Inicie uma conversa agora!';

                const icon = (this.chatType === 'grupo') ? 'fa-users' : 'fa-comment-dots';

                this.containerMensagens.innerHTML = `
                    <div class="chat-empty-state-wrapper">
                        <div class="chat-empty-state-card">
                            <div class="empty-state-icon" style="color: ${this.corOficial};">
                                <i class="fas ${icon}"></i>
                            </div>
                            <h4 style="color: ${this.corOficial};">Nada por aqui ainda</h4>
                            <p>${prompt}</p>
                            <div class="empty-state-badge">Criptografia de Ponta a Ponta</div>
                        </div>
                    </div>`;
            }
            return; 
        }

        const emptyState = this.containerMensagens.querySelector('.chat-empty-state-wrapper');
        if (emptyState) emptyState.remove();

        let novasMensagensEncontradas = false;

        mensagens.forEach(msg => {
            const existingMsg = document.getElementById(`msg-${msg.id}`);
            
            if (existingMsg) {
                // Atualização de recibo de leitura para mensagens próprias
                if (msg.remetente_id == (window.CHAT_CONFIG ? window.CHAT_CONFIG.myId : 0)) {
                    const icon = existingMsg.querySelector('.fa-check-double');
                    if (icon && msg.lida == 1) {
                        icon.style.color = '#34b7f1';
                    }
                }
                return;
            }

            const htmlMsg = this.criarBalaoMensagem(msg);
            this.containerMensagens.insertAdjacentHTML('beforeend', htmlMsg);
            
            novasMensagensEncontradas = true;
            this.ultimaMensagemId = msg.id;
        });

        if (novasMensagensEncontradas) {
            this.rolarParaBaixo();
        }
    },

    /**
     * [UPGRADE V68.6] CONSTRUTOR DE BALÕES HÍBRIDOS
     */
    criarBalaoMensagem: function(msg) {
        const myId = window.CHAT_CONFIG ? window.CHAT_CONFIG.myId : 0;
        const isMinha = (parseInt(msg.remetente_id) === parseInt(myId));
        
        const classeLado = isMinha ? 'msg-me' : 'msg-them';
        let htmlAvatarRemetente = '';
        let htmlNomeRemetente = '';
        
        let htmlMedia = '';
        let htmlTexto = '';

        // Identidade em Grupos
        if (this.chatType === 'grupo' && !isMinha) {
            const avatarUrl = msg.remetente_avatar 
                ? `${BASE_PATH}${msg.remetente_avatar}` 
                : `${BASE_PATH}assets/images/default-avatar.png`;
                
            htmlAvatarRemetente = `<img src="${avatarUrl}" class="msg-sender-avatar">`;
            htmlNomeRemetente = `<span class="msg-sender-name">${msg.remetente_nome}</span>`;
        }

        // 1. Processamento de Mídia
        if (msg.midia_url) {
            const fullUrl = `${BASE_PATH}${msg.midia_url}`;
            const hasText = (msg.mensagem && msg.mensagem.trim() !== '');
            const mediaClass = hasText ? 'msg-media-with-caption' : 'msg-media-only';

            switch(msg.tipo_midia) {
                case 'foto':
                    htmlMedia = `<div class="msg-media-photo ${mediaClass}"><img src="${fullUrl}" onclick="chatLightbox.open(this.src)" loading="lazy"></div>`;
                    break;
                case 'video':
                    htmlMedia = `<div class="msg-media-video ${mediaClass}"><video controls src="${fullUrl}"></video></div>`;
                    break;
                case 'audio':
                    htmlMedia = `
                        <div class="msg-media-audio ${mediaClass}">
                            <audio controls preload="metadata" src="${fullUrl}"></audio>
                        </div>`;
                    break;
            }
        }

        // 2. Processamento de Texto
        if (msg.mensagem && msg.mensagem.trim() !== '') {
            htmlTexto = `<div class="msg-text">${this.formatarTexto(msg.mensagem)}</div>`;
        }

        const corVisto = msg.lida == 1 ? '#34b7f1' : 'rgba(255, 255, 255, 0.4)';

        return `
            <div class="message-wrapper ${classeLado}" id="msg-${msg.id}">
                ${htmlAvatarRemetente}
                <div class="message-bubble">
                    ${htmlNomeRemetente}
                    <div class="msg-content-wrapper">
                        ${htmlMedia}
                        ${htmlTexto}
                    </div>
                    <span class="msg-time">
                        ${this.formatarHora(msg.criado_em)}
                        ${isMinha ? `<i class="fas fa-check-double" style="color: ${corVisto};"></i>` : ''}
                    </span>
                </div>
            </div>
        `.replace(/>\s+</g, '><').trim(); 
    },

    limparJanela: function() {
        if (this.containerMensagens) {
            this.containerMensagens.innerHTML = '';
            this.ultimaMensagemId = 0;
            const loader = document.getElementById('messages-loader');
            if (loader) loader.classList.remove('is-hidden');
            const win = document.querySelector('.chat-window-container');
            this.chatType = win ? win.dataset.tipo : 'privada';
        }
    },

    rolarParaBaixo: function(suave = true) {
        if (this.chatAnchor) {
            this.chatAnchor.scrollIntoView({ behavior: suave ? 'smooth' : 'auto', block: 'end' });
        } else if (this.scrollArea) {
            this.scrollArea.scrollTop = this.scrollArea.scrollHeight;
        }
    },

    formatarTexto: function(texto) {
        if (!texto) return '';
        
        let formatado = texto.trim()
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");

        formatado = formatado.replace(/\n/g, '<br>');

        const urlRegex = /(https?:\/\/[^\s]+)/g;
        return formatado.replace(urlRegex, (url) => {
            return `<a href="${url}" target="_blank" class="chat-link">${url}</a>`;
        });
    },

    formatarHora: function(dataString) {
        if (!dataString) return '--:--';
        try {
            const data = new Date(dataString.replace(/-/g, "/")); 
            return data.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        } catch (e) { return '--:--'; }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('chat-master-card') || document.querySelector('.chat-window-container')) {
        chatVisual.init();
    }
});