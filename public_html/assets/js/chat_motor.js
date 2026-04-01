/**
 * assets/js/chat_motor.js
 * Componente: Motor de Sincronização e Roteamento.
 * PAPEL: Gerenciar o ciclo de vida da conexão, troca de salas e Gatilhos de Envio.
 * VERSÃO: V67.8 - Fix CSRF & Padronização de Segurança (socialbr.lol)
 */

const chatMotor = {
    intervaloSinc: null,
    tempoPolling: 3000, 
    conversaAtivaId: null,
    isProcessando: false,
    timerBusca: null, 
    chatType: 'privada',

    /**
     * Inicializa o motor ao carregar a página de chat.
     */
    init: function() {
        console.log("🚀 Motor do Chat V67.8 (CSRF Fixed) iniciado...");
        
        // 1. Captura o ID da conversa pela URL (Deep Linking)
        const urlParams = window.location.pathname.split('/');
        const idDaUrl = urlParams[urlParams.length - 1];

        if (!isNaN(idDaUrl) && idDaUrl > 0) {
            this.conversaAtivaId = parseInt(idDaUrl);
            this.iniciarSincronizacao();
        }

        // 2. Listeners para Busca Híbrida
        const buscaInput = document.getElementById('chat-search-input');
        if (buscaInput) {
            buscaInput.addEventListener('input', (e) => {
                const termo = e.target.value;
                this.filtrarConversasLocal(termo);
                
                clearTimeout(this.timerBusca);
                this.timerBusca = setTimeout(() => {
                    this.buscarAmigosGlobal(termo);
                }, 500);
            });
        }

        /**
         * 3. DELEGAÇÃO DE EVENTOS
         */
        document.body.addEventListener('click', (e) => {
            const btnNovoChat = e.target.closest('.btn-nova-conversa') || e.target.closest('#btn-open-new-chat');
            if (btnNovoChat) {
                e.preventDefault();
                this.abrirIniciadorChat();
            }

            if (e.target.closest('#sb-close-initiator') || (e.target.id === 'sb-chat-initiator-overlay')) {
                const overlay = document.getElementById('sb-chat-initiator-overlay');
                if (overlay) overlay.remove();
            }

            const typeCard = e.target.closest('.sb-type-card');
            if (typeCard) {
                this.chatType = typeCard.dataset.type;
                this.avancarParaSelecao();
            }

            if (e.target.id === 'sb-btn-back') {
                this.voltarParaFase1();
            }

            if (e.target.id === 'sb-btn-create-group') {
                this.finalizarCriacaoGrupo();
            }
            
            const friendItem = e.target.closest('.sb-friend-item');
            if (friendItem) {
                const checkbox = friendItem.querySelector('.sb-group-check');
                if (e.target.classList.contains('sb-group-check')) {
                    friendItem.style.background = e.target.checked ? '#f0f7ff' : 'transparent';
                    return; 
                }
                const friendId = friendItem.dataset.id;
                this.iniciadorAcao(friendId, friendItem);
            }
        });

        // 4. Listeners para Inputs Dinâmicos
        document.body.addEventListener('input', (e) => {
            if (e.target.id === 'sb-friend-search-privado' || e.target.id === 'sb-friend-search-grupo') {
                this.filtrarAmigosIniciador(e.target.value);
            }

            if (e.target && e.target.id === 'chat-message-input') {
                e.target.style.height = 'auto'; 
                e.target.style.height = e.target.scrollHeight + 'px'; 
            }
        });
    },

    /**
     * GESTÃO DO MODAL INICIADOR
     */
    abrirIniciadorChat: async function() {
        try {
            const response = await fetch(`${BASE_PATH}chat?ajax_iniciador=1`);
            const html = await response.text();
            const oldModal = document.getElementById('sb-chat-initiator-overlay');
            if(oldModal) oldModal.remove();
            document.body.insertAdjacentHTML('beforeend', html);
        } catch (e) {
            console.error("❌ Erro ao carregar modal iniciador:", e);
        }
    },

    avancarParaSelecao: function() {
        const step1 = document.getElementById('sb-step-1');
        const step2 = document.getElementById('sb-step-2');
        const title = document.getElementById('sb-modal-title');
        const footer = document.getElementById('sb-modal-footer');
        const btnCreate = document.getElementById('sb-btn-create-group');
        const privadoContainer = document.getElementById('sb-privado-ui-container');
        const grupoContainer = document.getElementById('sb-grupo-ui-container');

        if (!step1 || !step2) return;

        step1.classList.add('is-hidden');
        step2.style.display = 'block';
        footer.classList.remove('is-hidden');
        
        if (this.chatType === 'grupo') {
            title.innerText = "Criar Novo Grupo";
            btnCreate.classList.remove('is-hidden');
            if(privadoContainer) privadoContainer.classList.add('is-hidden');
            if(grupoContainer) grupoContainer.classList.remove('is-hidden');
        } else {
            title.innerText = "Selecionar Amigo";
            btnCreate.classList.add('is-hidden');
            if(grupoContainer) grupoContainer.classList.add('is-hidden');
            if(privadoContainer) privadoContainer.classList.remove('is-hidden');
        }

        this.carregarAmigosIniciador();
    },

    voltarParaFase1: function() {
        const step1 = document.getElementById('sb-step-1');
        const step2 = document.getElementById('sb-step-2');
        const title = document.getElementById('sb-modal-title');
        const footer = document.getElementById('sb-modal-footer');

        if(step1) step1.classList.remove('is-hidden');
        if(step2) step2.style.display = 'none';
        if(footer) footer.classList.add('is-hidden');
        title.innerText = "Nova Conversa";
    },

    carregarAmigosIniciador: async function() {
        const containerId = (this.chatType === 'grupo') ? 'sb-friends-container-grupo' : 'sb-friends-container-privado';
        const container = document.getElementById(containerId);
        if (!container) return;

        try {
            const response = await fetch(`${BASE_PATH}api/chat/buscar_amigos_iniciador.php?iniciador=1&group=${this.chatType === 'grupo' ? 1 : 0}`);
            const data = await response.json();

            if (data.success && data.amigos.length > 0) {
                container.innerHTML = data.amigos.map(amigo => `
                    <div class="sb-friend-item" data-id="${amigo.id}">
                        <div class="sb-friend-item-left">
                            <img src="${amigo.avatar}" class="sb-friend-avatar">
                            <div class="sb-friend-info">
                                <span class="sb-friend-name">${amigo.nome_completo}</span>
                            </div>
                        </div>
                        ${this.chatType === 'grupo' ? '<input type="checkbox" class="sb-group-check">' : ''}
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p style="text-align:center; padding:20px; color:#65676b;">Nenhum amigo disponível.</p>';
            }
        } catch (e) {
            container.innerHTML = '<p style="text-align:center; padding:20px; color:red;">Erro ao carregar amigos.</p>';
        }
    },

    iniciadorAcao: function(friendId, element) {
        if (this.chatType === 'privada') {
            if (typeof chatAcoes !== 'undefined' && chatAcoes.iniciarConversaPrivada) {
                chatAcoes.iniciarConversaPrivada(friendId);
            } else {
                location.href = `${BASE_PATH}api/chat/iniciar_conversa.php?usuario_id=${friendId}`;
            }
        } else {
            const cb = element.querySelector('.sb-group-check');
            if (cb) {
                cb.checked = !cb.checked;
                element.style.background = cb.checked ? '#f0f7ff' : 'transparent';
            }
        }
    },

    filtrarAmigosIniciador: function(termo) {
        const termoLimpo = termo.toLowerCase();
        document.querySelectorAll('.sb-friend-item').forEach(item => {
            const name = item.querySelector('.sb-friend-name').innerText.toLowerCase();
            item.style.display = name.includes(termoLimpo) ? 'flex' : 'none';
        });
    },

    /**
     * [FIX V67.8] Criação de Grupo com Sincronia de Token CSRF.
     */
    finalizarCriacaoGrupo: async function() {
        const groupNameInput = document.getElementById('sb-group-name-input');
        const groupName = groupNameInput ? groupNameInput.value.trim() : '';

        if (!groupName) {
            Swal.fire({ icon: 'warning', title: 'Campo Obrigatório', text: "Por favor, digite um nome para o grupo.", confirmButtonColor: '#0C2D54' });
            return;
        }

        const selected = Array.from(document.querySelectorAll('.sb-friend-item .sb-group-check:checked'))
                              .map(cb => cb.closest('.sb-friend-item').dataset.id);

        if (selected.length < 1) {
            Swal.fire({ icon: 'warning', title: 'Sem Membros', text: "Selecione pelo menos um amigo para o grupo.", confirmButtonColor: '#0C2D54' });
            return;
        }

        const formData = new FormData();
        formData.append('titulo', groupName);
        formData.append('participantes', JSON.stringify(selected));
        
        // --- INJEÇÃO DO TOKEN PADRONIZADO ---
        const token = window.csrf_token || document.querySelector('input[name="csrf_token"]')?.value || '';
        formData.append('csrf_token', token);

        try {
            const response = await fetch(`${BASE_PATH}api/chat/criar_grupo.php`, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                location.href = `${BASE_PATH}chat/${data.conversa_id}`;
            } else {
                if (data.error === 'verificacao_pendente') {
                    Swal.fire({
                        title: '🛡️ Identidade Necessária',
                        text: data.message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#0C2D54',
                        cancelButtonColor: '#606770',
                        confirmButtonText: 'Confirmar E-mail Agora',
                        cancelButtonText: 'Talvez mais tarde'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `${BASE_PATH}configurar_perfil?tab=conta`;
                        }
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Ops!', text: data.message || data.error || "Erro ao criar grupo.", confirmButtonColor: '#0C2D54' });
                }
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Erro de Sistema', text: "Não foi possível processar a criação do grupo.", confirmButtonColor: '#d33' });
        }
    },

    /**
     * CICLO DE SINCRONIZAÇÃO E MENSAGENS
     */
    iniciarSincronizacao: function() {
        if (this.intervaloSinc) clearInterval(this.intervaloSinc);
        this.buscarNovasMensagens();
        this.intervaloSinc = setInterval(() => this.buscarNovasMensagens(), this.tempoPolling);
    },

    buscarNovasMensagens: async function() {
        if (!this.conversaAtivaId || this.isProcessando) return;
        this.isProcessando = true;

        try {
            const response = await fetch(`${BASE_PATH}api/chat/obter_mensagens.php?conversa_id=${this.conversaAtivaId}`);
            const dados = await response.json();

            if (dados.sucesso) {
                if (typeof chatVisual !== 'undefined') chatVisual.renderizarMensagens(dados.mensagens);
                if (dados.novas > 0 || (dados.mensagens && dados.mensagens.some(m => m.lida == 1))) {
                    this.atualizarSidebar();
                    if (dados.novas > 0) {
                        this.marcarComoLida(this.conversaAtivaId);
                        if (window.chatLightbox) window.chatLightbox.init();
                    }
                }
            }
        } catch (error) {
            console.error("❌ Erro na sincronização:", error);
        } finally {
            this.isProcessando = false;
        }
    },

    atualizarSidebar: async function() {
        const sidebarContainer = document.getElementById('chat-sidebar-container');
        if (!sidebarContainer) return;

        try {
            const response = await fetch(`${BASE_PATH}chat?ajax_sidebar=1`);
            const html = await response.text();
            if (html.trim() !== "") {
                sidebarContainer.innerHTML = html;
                const buscaInput = document.getElementById('chat-search-input');
                if (buscaInput && buscaInput.value) this.filtrarConversasLocal(buscaInput.value);
            }
        } catch (e) {
            console.warn("Erro ao atualizar sidebar.");
        }
    },

    trocarConversa: async function(id) {
        if (id === this.conversaAtivaId) return;
        this.conversaAtivaId = id;
        window.history.pushState({ id: id }, '', `${BASE_PATH}chat/${id}`);

        const sidebar = document.getElementById('chat-sidebar-container');
        const activeWindow = document.getElementById('chat-active-window');

        if (window.innerWidth <= 768 && sidebar) {
            sidebar.classList.add('mobile-hidden');
            activeWindow.classList.remove('mobile-hidden');
        }

        try {
            activeWindow.innerHTML = '<div class="chat-loader-full"><i class="fas fa-circle-notch fa-spin"></i></div>';
            const response = await fetch(`${BASE_PATH}chat/${id}?ajax=1`);
            const html = await response.text();
            activeWindow.innerHTML = html;

            if (typeof chatVisual !== 'undefined') {
                chatVisual.init(); 
                chatVisual.limparJanela();
            }
            
            if (window.chatLightbox) window.chatLightbox.init();

            this.iniciarSincronizacao();
            this.atualizarSidebar(); 
        } catch (e) {
            activeWindow.innerHTML = '<div class="chat-error-msg">Erro ao carregar conversa.</div>';
        }
    },

    voltarParaLista: function() {
        const sidebar = document.getElementById('chat-sidebar-container');
        const activeWindow = document.getElementById('chat-active-window');
        if (activeWindow) activeWindow.classList.add('mobile-hidden');
        if (sidebar) sidebar.classList.remove('mobile-hidden');
        this.conversaAtivaId = null;
        if (this.intervaloSinc) clearInterval(this.intervaloSinc);
        window.history.pushState({}, '', `${BASE_PATH}chat`);
        this.atualizarSidebar();
    },

    /**
     * Envio de Mensagem Orquestrado com Blindagem CSRF.
     */
    enviarMensagem: async function() {
        const form = document.getElementById('chat-send-form');
        if (!form || this.isProcessando) return;

        const input = document.getElementById('chat-message-input');
        const msgTexto = input ? input.value.trim() : '';
        
        const temMidia = (typeof chatMidia !== 'undefined' && chatMidia.arquivoSelecionado !== null);

        if (msgTexto.length === 0 && !temMidia) {
            console.warn("Tentativa de envio vazia cancelada.");
            return;
        }

        this.isProcessando = true;
        const formData = new FormData(form);
        
        // --- GARANTIA CSRF NO ENVIO ---
        const token = window.csrf_token || document.querySelector('input[name="csrf_token"]')?.value || '';
        if(!formData.has('csrf_token')) formData.append('csrf_token', token);

        try {
            let result;
            if (temMidia) {
                result = await chatMidia.uploadComProgresso(formData);
            } else {
                const response = await fetch(`${BASE_PATH}api/chat/enviar_mensagem.php`, {
                    method: 'POST',
                    body: formData
                });
                result = await response.json();
            }

            if (result.sucesso) {
                if (input) {
                    input.value = '';
                    input.style.height = 'auto'; 
                }
                
                if (typeof chatMidia !== 'undefined' && chatMidia.cancelUpload) {
                    chatMidia.cancelUpload();
                }
                
                this.buscarNovasMensagens();
                this.atualizarSidebar(); 
            } else {
                Swal.fire({ icon: 'error', title: 'Falha no Envio', text: result.erro || "Ocorreu um erro ao enviar a mensagem.", confirmButtonColor: '#0C2D54' });
            }
        } catch (e) {
            console.error("Erro fatal no motor de despacho:", e);
        } finally {
            this.isProcessando = false;
        }
    },

    /**
     * Marcar como lida com Sincronia de Segurança.
     */
    marcarComoLida: async function(conversaId) {
        try {
            const token = window.csrf_token || document.querySelector('input[name="csrf_token"]')?.value || '';
            await fetch(`${BASE_PATH}api/chat/marcar_lida.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ conversa_id: conversaId, csrf_token: token })
            });
            if (typeof window.fetchNotifications === 'function') window.fetchNotifications();
            if (typeof window.fetchChatUnreadCount === 'function') window.fetchChatUnreadCount();
            this.atualizarSidebar();
        } catch (e) {
            console.warn("Falha ao marcar como lida.");
        }
    },

    filtrarConversasLocal: function(termo) {
        const conversas = document.querySelectorAll('#chat-local-list .chat-item');
        const termoLimpo = termo.toLowerCase();
        conversas.forEach(item => {
            const nomeElement = item.querySelector('.chat-item-name');
            if (nomeElement) {
                const nome = nomeElement.innerText.toLowerCase();
                item.style.display = nome.includes(termoLimpo) ? 'flex' : 'none';
            }
        });
    },

    buscarAmigosGlobal: async function(termo) {
        const containerGlobal = document.getElementById('chat-global-search-results');
        if (!termo || termo.length < 1) {
            if (containerGlobal) containerGlobal.classList.add('is-hidden');
            return;
        }
        try {
            const response = await fetch(`${BASE_PATH}api/chat/buscar_amigos.php?termo=${encodeURIComponent(termo)}`);
            const dados = await response.json();
            if (dados.sucesso && dados.resultados.length > 0) {
                this.renderizarResultadosGlobais(dados.resultados);
                if (containerGlobal) containerGlobal.classList.remove('is-hidden');
            } else {
                if (containerGlobal) containerGlobal.classList.add('is-hidden');
            }
        } catch (e) {
            console.error("Erro na busca global:", e);
        }
    },

    renderizarResultadosGlobais: function(amigos) {
        const listaResultados = document.getElementById('global-results-list');
        if (!listaResultados) return;
        listaResultados.innerHTML = ''; 
        amigos.forEach(amigo => {
            const acaoClique = amigo.conversa_id 
                ? `chatMotor.trocarConversa(${amigo.conversa_id})` 
                : `chatAcoes.iniciarConversaPrivada(${amigo.id})`; 

            const itemHTML = `
                <div class="chat-item chat-item-new-friend" onclick="${acaoClique}">
                    <div class="chat-item-avatar"><img src="${amigo.avatar}" alt="${amigo.nome}"></div>
                    <div class="chat-item-info">
                        <div class="chat-item-header">
                            <span class="chat-item-name">${amigo.nome}</span>
                        </div>
                        <div class="chat-item-footer">
                            <span class="chat-item-preview">@${amigo.username}</span>
                        </div>
                    </div>
                    <div class="chat-item-action-icon">
                        <i class="fas ${amigo.conversa_id ? 'fa-chevron-right' : 'fa-comment-medical'}"></i>
                    </div>
                </div>
            `;
            listaResultados.insertAdjacentHTML('beforeend', itemHTML);
        });
    }
};

document.addEventListener('DOMContentLoaded', () => chatMotor.init());