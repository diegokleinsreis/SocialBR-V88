/**
 * assets/js/chat_acoes.js
 * Componente: Gestor de Ações, Segurança e Moderação.
 * PAPEL: Controlar Dropdowns, Bloqueios, Início de Conversas e Expansão do Painel.
 * VERSÃO: V70.4 - Sincronização CSRF Total & Fix Saída de Grupo (socialbr.lol)
 */

const chatAcoes = {
    dropdownAtivo: null,

    /**
     * Inicializa os listeners globais usando Delegação de Eventos.
     */
    init: function() {
        console.log("🛡️ Gestor de Ações V70.4 iniciado com Blindagem Total...");

        // 1. Fecha dropdowns ao clicar fora
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown-wrapper')) {
                this.fecharTodosDropdowns();
            }
        });

        // 2. Delegação para o gatilho do menu de opções (Dropdown Principal)
        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('.chat-options-trigger');
            if (trigger) {
                e.preventDefault();
                this.toggleDropdown(trigger);
            }
        });

        // 3. Delegação para Troca de Abas da Galeria
        document.addEventListener('click', (e) => {
            const tabBtn = e.target.closest('.media-tab-btn');
            if (tabBtn) {
                this.switchGalleryTab(tabBtn);
            }
        });
    },

    /**
     * Lógica para Iniciar Conversa Privada com Trava de Segurança.
     */
    iniciarConversaPrivada: async function(usuarioId) {
        this.fecharTodosDropdowns();
        
        try {
            const response = await fetch(`${CHAT_CONFIG.baseUrl}api/chat/iniciar_conversa.php?usuario_id=${usuarioId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();

            if (data.success) {
                window.location.href = data.redirect;
            } else {
                if (data.error === 'verificacao_pendente') {
                    Swal.fire({
                        title: '🛡️ Identidade Necessária',
                        text: data.message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#0C2D54',
                        confirmButtonText: 'Confirmar E-mail Agora',
                        cancelButtonText: 'Talvez mais tarde'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `${CHAT_CONFIG.baseUrl}configurar_perfil?tab=conta`;
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ops!',
                        text: data.message || 'Não foi possível iniciar esta conversa.',
                        confirmButtonColor: '#0C2D54'
                    });
                }
            }
        } catch (e) {
            console.error("Erro crítico ao iniciar conversa:", e);
            Swal.fire({
                icon: 'error',
                title: 'Erro de Rede',
                text: 'Não foi possível conectar ao servidor de chat.',
                confirmButtonColor: '#d33'
            });
        }
    },

    /**
     * Lógica de Troca de Abas via Delegação.
     */
    switchGalleryTab: function(btn) {
        const wrapper = btn.closest('.chat-media-gallery-wrapper');
        if (!wrapper) return;

        wrapper.querySelectorAll('.media-tab-btn').forEach(b => b.classList.remove('active'));
        wrapper.querySelectorAll('.media-pane').forEach(p => p.classList.remove('active'));

        btn.classList.add('active');

        const targetId = btn.getAttribute('data-target');
        const targetPane = wrapper.querySelector(`#${targetId}`);
        if (targetPane) {
            targetPane.classList.add('active');
        }
    },

    /**
     * Controla a visibilidade e animação do Painel Direito (Centro de Comando).
     */
    toggleRightSidebar: function(show = true) {
        const sidebar = document.getElementById('chat-right-sidebar');
        if (!sidebar) return;

        if (show) {
            sidebar.classList.remove('is-hidden');
            setTimeout(() => sidebar.classList.add('is-active'), 10);
        } else {
            sidebar.classList.remove('is-active');
            setTimeout(() => {
                sidebar.classList.add('is-hidden');
                sidebar.innerHTML = ''; 
            }, 300);
        }
    },

    /**
     * Controla a exibição do menu de opções da conversa.
     */
    toggleDropdown: function(btn) {
        const menu = btn.nextElementSibling;
        if (!menu) return;

        const estaAberto = !menu.classList.contains('is-hidden');
        this.fecharTodosDropdowns();

        if (!estaAberto) {
            menu.classList.remove('is-hidden');
            this.dropdownAtivo = menu;
        }
    },

    /**
     * Fecha todos os menus suspensos ativos.
     */
    fecharTodosDropdowns: function() {
        document.querySelectorAll('.chat-dropdown-menu').forEach(m => m.classList.add('is-hidden'));
        this.dropdownAtivo = null;
    },

    /**
     * Abre as Informações do Contato no Centro de Comando 1x1.
     */
    openContactInfo: async function(usuarioId, conversaId) {
        this.fecharTodosDropdowns();
        this.toggleRightSidebar(true);
        const sidebar = document.getElementById('chat-right-sidebar');
        
        sidebar.innerHTML = `
            <div style="text-align:center; padding:60px; color:#0C2D54;">
                <i class="fas fa-circle-notch fa-spin fa-2x"></i> 
                <p style="margin-top:15px; font-weight:600;">A carregar centro de comando 1x1...</p>
            </div>
        `;

        try {
            const response = await fetch(`${CHAT_CONFIG.baseUrl}chat?ajax_info_contato=1&usuario_id=${usuarioId}&conversa_id=${conversaId}`);
            const html = await response.text();
            sidebar.innerHTML = html;
        } catch (e) {
            sidebar.innerHTML = '<p style="padding:40px; color:red; text-align:center;">Erro ao carregar informações do contato.</p>';
        }
    },

    /**
     * Abre as Informações do Grupo em Painel Amplo.
     */
    openGroupInfo: async function(conversaId) {
        this.fecharTodosDropdowns();
        this.toggleRightSidebar(true);
        const sidebar = document.getElementById('chat-right-sidebar');
        
        sidebar.innerHTML = `
            <div style="text-align:center; padding:60px; color:#0C2D54;">
                <i class="fas fa-circle-notch fa-spin fa-2x"></i> 
                <p style="margin-top:15px; font-weight:600;">A carregar perfil da comunidade...</p>
            </div>
        `;

        try {
            const response = await fetch(`${CHAT_CONFIG.baseUrl}chat?ajax_info_grupo=1&conversa_id=${conversaId}`);
            const html = await response.text();
            sidebar.innerHTML = html;
        } catch (e) {
            sidebar.innerHTML = '<p style="padding:40px; color:red; text-align:center;">Erro ao carregar informações do grupo.</p>';
        }
    },

    /**
     * Abre o Painel de Gestão de Membros (Poder do Admin).
     */
    openMemberManagement: async function(conversaId) {
        this.fecharTodosDropdowns();
        this.toggleRightSidebar(true);
        const sidebar = document.getElementById('chat-right-sidebar');

        sidebar.innerHTML = `
            <div style="text-align:center; padding:60px; color:#0C2D54;">
                <i class="fas fa-users-cog fa-spin fa-2x"></i>
                <p style="margin-top:15px; font-weight:600;">Acedendo ao centro de comando...</p>
            </div>
        `;

        try {
            const response = await fetch(`${CHAT_CONFIG.baseUrl}chat?ajax_painel_gestao=1&conversa_id=${conversaId}`);
            const html = await response.text();
            sidebar.innerHTML = html;
        } catch (e) {
            sidebar.innerHTML = '<p style="padding:40px; color:red; text-align:center;">Erro ao carregar painel de gestão.</p>';
        }
    },

    /**
     * Abre a Galeria de Mídias Compartilhadas via AJAX.
     */
    openMediaHub: async function(conversaId, tipo = 'privada') {
        this.fecharTodosDropdowns();
        this.toggleRightSidebar(true);
        const sidebar = document.getElementById('chat-right-sidebar');

        sidebar.innerHTML = `
            <div style="text-align:center; padding:60px; color:#0C2D54;">
                <i class="fas fa-images fa-spin fa-2x"></i>
                <p style="margin-top:15px; font-weight:600;">A organizar galeria multimédia...</p>
            </div>
        `;

        try {
            const response = await fetch(`${CHAT_CONFIG.baseUrl}chat?ajax_ver_midia=1&id=${conversaId}&tipo=${tipo}`);
            const html = await response.text();
            sidebar.innerHTML = html;
        } catch (e) {
            console.error("Erro ao carregar galeria:", e);
            sidebar.innerHTML = '<p style="padding:40px; color:red; text-align:center;">Não foi possível carregar a galeria de mídias.</p>';
        }
    },

    /**
     * Player de Vídeo em Modal para Galeria.
     */
    playGalleryVideo: function(url) {
        const modal = document.createElement('div');
        modal.className = 'video-fullscreen-modal';
        modal.style.background = 'rgba(0,0,0,0.9)';
        modal.innerHTML = `
            <div class="modal-close" style="position:absolute; top:20px; right:30px; color:#fff; font-size:40px; cursor:pointer;">&times;</div>
            <video src="${url}" controls autoplay style="max-width:90%; max-height:80vh; border-radius:8px; box-shadow:0 0 30px rgba(0,0,0,0.5);"></video>
        `;
        
        modal.querySelector('.modal-close').onclick = () => modal.remove();
        modal.onclick = (e) => { if(e.target === modal) modal.remove(); };
        
        document.body.appendChild(modal);
    },

    /**
     * Executa ações de moderação ou saída usando SweetAlert2.
     * [FIX V70.4] Sincronização CSRF Total para saída de grupo.
     */
    gerenciarMembro: async function(conversaId, usuarioId, acao) {
        this.fecharTodosDropdowns();

        // --- CAPTURA DE TOKEN RESILIENTE ---
        const token = window.csrf_token || document.querySelector('input[name="csrf_token"]')?.value || CHAT_CONFIG.token || '';

        const configAlerta = {
            'remover': { titulo: "Expulsar Membro", texto: "Deseja realmente expulsar este membro da comunidade?", icone: "warning", cor: "#dc3545" },
            'sair': { titulo: "Sair do Grupo", texto: "Tem certeza que deseja abandonar este grupo?", icone: "question", cor: "#0C2D54" },
            'promover': { titulo: "Promover Membro", texto: "Deseja tornar este membro um administrador?", icone: "info", cor: "#0C2D54" }
        };

        const config = configAlerta[acao] || { titulo: "Confirmar Ação", texto: "Deseja executar este comando?", icone: "question", cor: "#0C2D54" };

        const result = await Swal.fire({
            title: config.titulo,
            text: config.texto,
            icon: config.icone,
            showCancelButton: true,
            confirmButtonColor: config.cor,
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Sim, confirmar',
            cancelButtonText: 'Cancelar'
        });

        if (!result.isConfirmed) return;

        try {
            const response = await fetch(`${CHAT_CONFIG.baseUrl}api/chat/gerenciar_participante.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    conversa_id: conversaId, 
                    usuario_id: usuarioId,
                    acao: acao,
                    csrf_token: token // ENVIANDO O NOME CORRETO PARA A API
                })
            });
            
            const res = await response.json();

            if (res.sucesso) {
                if (acao === 'sair') {
                    // Redirecionamento imediato após sair com sucesso
                    window.location.href = `${CHAT_CONFIG.baseUrl}chat`; 
                } else {
                    Swal.fire({ icon: 'success', title: 'Sucesso', text: res.mensagem || "Comando executado com êxito.", confirmButtonColor: '#0C2D54' });
                    this.openMemberManagement(conversaId);
                    if (typeof chatMotor !== 'undefined') chatMotor.atualizarSidebar();
                }
            } else {
                Swal.fire({ icon: 'error', title: 'Ação Negada', text: res.erro || "Falha ao processar comando.", confirmButtonColor: '#0C2D54' });
            }
        } catch (e) {
            console.error("Erro na gestão de membro:", e);
            Swal.fire({ icon: 'error', title: 'Erro de Conexão', text: "O servidor não respondeu. Tente recarregar a página.", confirmButtonColor: '#d33' });
        }
    },

    /**
     * Alterna o estado de Fixação da conversa.
     */
    togglePin: async function(conversaId) {
        this.fecharTodosDropdowns();
        if (!conversaId) return;
        const token = window.csrf_token || CHAT_CONFIG.token;
        try {
            const response = await fetch(`${CHAT_CONFIG.baseUrl}api/chat/fixar_silenciar.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ conversa_id: conversaId, acao: 'fixar', csrf_token: token })
            });
            const res = await response.json();
            if (res.sucesso) {
                if (typeof chatMotor !== 'undefined') chatMotor.atualizarSidebar();
            }
        } catch (e) { console.error("Erro ao fixar:", e); }
    },

    /**
     * Alterna o silenciamento de notificações.
     */
    toggleMute: async function(conversaId) {
        this.fecharTodosDropdowns();
        if (!conversaId) return;
        const token = window.csrf_token || CHAT_CONFIG.token;
        try {
            const response = await fetch(`${CHAT_CONFIG.baseUrl}api/chat/fixar_silenciar.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ conversa_id: conversaId, acao: 'silenciar', csrf_token: token })
            });
            const res = await response.json();
            if (res.sucesso) {
                if (typeof chatMotor !== 'undefined') chatMotor.atualizarSidebar();
            }
        } catch (e) { console.error("Erro ao silenciar:", e); }
    },

    /**
     * Bloqueio de utilizador com SweetAlert2.
     */
    toggleBlock: async function(usuarioId) {
        this.fecharTodosDropdowns();
        const token = window.csrf_token || CHAT_CONFIG.token;

        const result = await Swal.fire({
            title: 'Bloquear Utilizador',
            text: "Tem certeza que deseja bloquear este utilizador? A amizade será desfeita.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Sim, bloquear',
            cancelButtonText: 'Cancelar'
        });

        if (!result.isConfirmed) return;

        try {
            const formData = new FormData();
            formData.append('id_usuario_bloqueado', usuarioId);
            formData.append('csrf_token', token);

            const response = await fetch(`${CHAT_CONFIG.baseUrl}api/usuarios/bloquear_usuario.php`, { 
                method: 'POST', 
                body: formData 
            });
            
            const res = await response.json();
            
            if (res.success) {
                Swal.fire({ 
                    icon: 'success', 
                    title: 'Bloqueado', 
                    text: res.message || "Utilizador bloqueado com sucesso.", 
                    confirmButtonColor: '#0C2D54' 
                })
                .then(() => { 
                    window.location.href = `${CHAT_CONFIG.baseUrl}chat`; 
                });
            } else {
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Erro no Bloqueio', 
                    text: res.error || "Não foi possível completar a ação.", 
                    confirmButtonColor: '#0C2D54' 
                });
            }
        } catch (e) { 
            console.error("Falha no Bloqueio:", e); 
        }
    },

    /**
     * Desbloqueio de utilizador com SweetAlert2.
     */
    toggleUnblock: async function(usuarioId) {
        this.fecharTodosDropdowns();
        const token = window.csrf_token || CHAT_CONFIG.token;

        const result = await Swal.fire({
            title: 'Desbloquear Utilizador?',
            text: "Deseja permitir novamente interações com este utilizador?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Sim, desbloquear',
            cancelButtonText: 'Cancelar'
        });

        if (!result.isConfirmed) return;

        try {
            const formData = new FormData();
            formData.append('id_usuario_desbloqueado', usuarioId);
            formData.append('csrf_token', token);

            const response = await fetch(`${CHAT_CONFIG.baseUrl}api/usuarios/desbloquear_usuario.php`, { 
                method: 'POST', 
                body: formData 
            });
            
            const res = await response.json();
            
            if (res.success) {
                Swal.fire({ 
                    icon: 'success', 
                    title: 'Desbloqueado', 
                    text: "Utilizador desbloqueado com sucesso!", 
                    confirmButtonColor: '#0C2D54' 
                })
                .then(() => { 
                    location.reload(); 
                });
            } else {
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Erro no Desbloqueio', 
                    text: res.error || "Não foi possível completar a ação.", 
                    confirmButtonColor: '#0C2D54' 
                });
            }
        } catch (e) { 
            console.error("Falha no Desbloqueio:", e); 
        }
    }
};

// Inicialização Global
document.addEventListener('DOMContentLoaded', () => chatAcoes.init());