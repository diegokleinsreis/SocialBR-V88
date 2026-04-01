/**
 * assets/js/chat_lightbox.js
 * Componente: Motor Especialista de Visualização de Fotos do Chat.
 * PAPEL: Abrir fotos do chat e suporte em ecrã total com suporte a download.
 * AJUSTE: Fix de visibilidade para Desktop (Remoção forçada de is-hidden).
 * VERSÃO: V1.6 (Support & Admin SPA Sync - socialbr.lol)
 */

window.chatLightbox = {
    overlay: null,
    modal: null,
    wrapper: null,
    downloadBtn: null,
    closeBtn: null,
    estaInicializado: false,

    /**
     * Faz o mapeamento dos elementos do DOM e configura eventos.
     */
    init: function() {
        this.overlay = document.getElementById('lightbox-overlay');
        this.modal = document.getElementById('lightbox-modal');
        this.wrapper = document.querySelector('.lightbox-image-wrapper');
        this.downloadBtn = document.getElementById('lightbox-download-btn');
        this.closeBtn = document.getElementById('lightbox-close-btn');

        // Se a estrutura não existir (ex: páginas sem footer), interrompe.
        if (!this.overlay || !this.modal) {
            return;
        }

        if (this.estaInicializado) return;

        // --- 1. DELEGAÇÃO DE EVENTOS (Vigia cliques em imagens de chat/suporte) ---
        document.addEventListener('click', (e) => {
            // Seletores que cobrem Chat, Suporte Usuário e Suporte Admin
            const imgTarget = e.target.closest('.chat-msg-media img, .msg-foto img, .msg-media-photo img, .suporte-msg-foto');
            
            if (imgTarget) {
                // Impede o navegador de abrir a imagem numa nova guia (Comportamento Desktop)
                e.preventDefault();
                e.stopPropagation();
                
                // Captura a URL da imagem (seja o alvo a tag <img> ou o container)
                const src = imgTarget.tagName === 'IMG' ? imgTarget.src : imgTarget.querySelector('img').src;
                
                console.log("📸 chatLightbox: Interceptando clique e expandindo imagem.");
                this.open(src);
            }
        });

        // 2. Listener para o botão fechar
        if (this.closeBtn) {
            this.closeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.close();
            });
        }

        // 3. Fechar ao clicar no overlay (fundo escuro)
        this.overlay.addEventListener('click', (e) => {
            if (e.target === this.overlay) {
                this.close();
            }
        });

        // 4. Fechar com a tecla ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.overlay.classList.contains('is-visible')) {
                this.close();
            }
        });

        this.estaInicializado = true;
        console.log("💎 Motor chatLightbox V1.6 (Desktop Fix) inicializado.");
    },

    /**
     * Abre o Lightbox em "Modo Chat" (Design Escuro OLED)
     */
    open: function(src) {
        if (!this.estaInicializado) this.init();

        // --- 1. CONFIGURAÇÃO DE ESTADO ---
        this.modal.classList.add('is-chat-mode');
        this.overlay.classList.add('is-chat-mode');
        
        // FIX DESKTOP: Remove a classe que esconde o elemento por padrão
        this.overlay.classList.remove('is-hidden');
        
        // Limpa conteúdo anterior e mostra carregamento
        this.wrapper.innerHTML = '<div class="spinner"></div>';
        
        // --- 2. GESTÃO DE DOWNLOAD ---
        if (this.downloadBtn) {
            this.downloadBtn.href = src;
            this.downloadBtn.setAttribute('download', 'socialbr_atendimento_' + Date.now() + '.jpg');
            this.downloadBtn.classList.remove('is-hidden');
        }

        // --- 3. CARREGAMENTO DA IMAGEM ---
        const img = new Image();
        img.onload = () => {
            this.wrapper.innerHTML = ''; 
            img.classList.add('lightbox-media-item'); 
            this.wrapper.appendChild(img);
        };
        img.onerror = () => {
            this.wrapper.innerHTML = '<p style="color:#fff; padding:20px;">Erro ao carregar imagem.</p>';
        };
        img.src = src;

        // --- 4. ATIVAÇÃO VISUAL ---
        this.overlay.style.display = 'flex'; 
        this.overlay.style.zIndex = '10000'; 

        // Pequeno delay para permitir a transição suave de opacidade
        setTimeout(() => {
            this.overlay.classList.add('is-visible');
        }, 10);

        // Bloqueia o scroll do fundo (Site ou Admin)
        document.body.style.overflow = 'hidden';
    },

    /**
     * Fecha o Lightbox e limpa o lixo de memória
     */
    close: function() {
        if (!this.overlay) return;

        this.overlay.classList.remove('is-visible');
        
        // Aguarda a transição de saída (0.3s) antes de esconder totalmente
        setTimeout(() => {
            if (!this.overlay.classList.contains('is-visible')) {
                this.overlay.style.display = 'none';
                this.overlay.style.zIndex = '';
                
                // Limpa classes de estado
                this.modal.classList.remove('is-chat-mode');
                this.overlay.classList.remove('is-chat-mode');
                this.overlay.classList.add('is-hidden'); // Restaura estado oculto
                
                if (this.downloadBtn) {
                    this.downloadBtn.classList.add('is-hidden');
                }

                this.wrapper.innerHTML = '';
                document.body.style.overflow = ''; // Restaura scroll
            }
        }, 300);
    }
};

// Inicialização automática ao carregar o DOM
document.addEventListener('DOMContentLoaded', () => {
    window.chatLightbox.init();
});