/**
 * assets/js/notificacoes.js
 * VERSÃO: 16.4 (Global Sync Support - socialbr.lol)
 * PAPEL: Motor de busca e renderização de notificações com suporte a gatilhos externos.
 * AJUSTE: Funções de busca agora são globais (window) para sincronia com MotorToast.
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. CONFIGURAÇÃO DE AMBIENTE
    const BASE_PATH = document.body.getAttribute('data-base-path') || window.base_path || '/';
    
    // 2. SELETORES DE PRECISÃO
    const notificationTrigger = document.getElementById('notification-trigger');
    const notificationPanel = document.getElementById('notifications-panel-unificado');
    const notificationBadge = document.getElementById('header-notification-badge');
    const notificationList = document.getElementById('header-notifications-list');
    const chatBadgeSidebar = document.getElementById('menu-chat-badge');

    /**
     * [GLOBAL] Busca notificações gerais via API
     */
    window.fetchNotifications = function() {
        const url = BASE_PATH + 'api/notificacoes/buscar_notificacoes.php';
        
        fetch(url) 
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateNotificationUI(data.nao_lidas);
                    renderNotificationList(data.notificacoes);
                }
            })
            .catch(err => console.warn('Falha ao sincronizar notificações:', err));
    };

    /**
     * [GLOBAL] Monitor de Chat (Sidebar)
     */
    window.fetchChatUnreadCount = function() {
        if (!chatBadgeSidebar) return;
        fetch(BASE_PATH + 'api/chat/contar_nao_lidas.php')
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (data.total > 0) {
                        chatBadgeSidebar.style.display = 'flex';
                        chatBadgeSidebar.innerText = data.total > 9 ? '9+' : data.total;
                    } else {
                        chatBadgeSidebar.style.display = 'none';
                    }
                }
            });
    };

    // 3. INICIALIZAÇÃO E POLLING
    window.fetchNotifications();
    window.fetchChatUnreadCount();

    setInterval(() => {
        window.fetchNotifications();
        window.fetchChatUnreadCount();
    }, 60000);

    // --- 4. INTERAÇÃO DO SINO ---
    if (notificationTrigger && notificationPanel) {
        notificationTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const isActive = notificationPanel.classList.toggle('active');
            notificationTrigger.classList.toggle('active', isActive);
        });
    }

    document.addEventListener('click', function(event) {
        if (notificationPanel && !notificationPanel.contains(event.target) && !notificationTrigger.contains(event.target)) {
            notificationPanel.classList.remove('active');
            notificationTrigger.classList.remove('active');
        }
    });

    /**
     * Atualiza o Interruptor do Ponto Vermelho
     */
    function updateNotificationUI(count) {
        if (!notificationBadge) return;
        if (count > 0) {
            notificationBadge.style.display = 'block';
            notificationBadge.classList.add('pulse');
        } else {
            notificationBadge.style.display = 'none';
            notificationBadge.classList.remove('pulse');
        }
    }

    /**
     * Renderiza o HTML das notificações no Dropdown
     */
    function renderNotificationList(notifications) {
        if (!notificationList) return;

        if (!notifications || notifications.length === 0) {
            notificationList.innerHTML = `
                <div class="no-notifications-message">
                    <i class="fas fa-bell-slash"></i>
                    <p>Você não tem novas notificações.</p>
                </div>`;
            return;
        }

        let html = '';
        notifications.forEach(notif => {
            const isUnread = (notif.lida == 0) ? 'unread' : '';
            const total = parseInt(notif.total_agrupado || 1);
            const idRef = notif.id_referencia || 0;
            const nomeGrupo = notif.grupo_nome ? escapeHtml(notif.grupo_nome) : 'um grupo';
            
            let msgAction = 'interagiu com você.';
            let link = BASE_PATH + 'historico_notificacoes';
            let avatar = notif.remetente_foto 
                ? (notif.remetente_foto.startsWith('http') ? notif.remetente_foto : BASE_PATH + notif.remetente_foto)
                : BASE_PATH + 'assets/images/default-avatar.png';

            switch (notif.tipo) {
                case 'mensagem':
                    msgAction = (total > 1) ? `enviou <strong>${total}</strong> novas mensagens.` : 'enviou uma nova mensagem.';
                    link = BASE_PATH + 'chat?id=' + idRef;
                    break;
                case 'curtida': case 'curtida_post':
                    msgAction = 'curtiu a sua publicação.';
                    link = BASE_PATH + 'postagem/' + idRef;
                    break;
                case 'curtida_comentario':
                    msgAction = 'curtiu o seu comentário.';
                    link = BASE_PATH + 'postagem/' + idRef;
                    break;
                case 'comentario': case 'comentario_post':
                    msgAction = 'comentou na sua publicação.';
                    link = BASE_PATH + 'postagem/' + idRef;
                    break;
                case 'compartilhar': case 'compartilhamento_post':
                    msgAction = 'compartilhou a sua publicação.';
                    link = BASE_PATH + 'postagem/' + idRef;
                    break;
                case 'pedido_amizade':
                    msgAction = 'enviou um pedido de amizade.';
                    link = BASE_PATH + 'perfil/' + idRef;
                    break;
                case 'aceite_amizade': case 'amizade_aceita':
                    msgAction = 'aceitou o seu pedido de amizade.';
                    link = BASE_PATH + 'perfil/' + idRef;
                    break;
                case 'convite_grupo':
                    msgAction = `convidou você para o grupo <strong>${nomeGrupo}</strong>.`;
                    link = BASE_PATH + 'grupos/ver/' + idRef;
                    break;
                case 'solicitacao_grupo':
                    msgAction = `quer entrar no seu grupo <strong>${nomeGrupo}</strong>.`;
                    link = BASE_PATH + 'grupos/ver/' + idRef;
                    break;
                case 'aceite_solicitacao_grupo':
                    msgAction = `aceitou sua entrada no grupo <strong>${nomeGrupo}</strong>.`;
                    link = BASE_PATH + 'grupos/ver/' + idRef;
                    break;
                case 'aceite_convite_grupo':
                    msgAction = `aceitou seu convite para o grupo <strong>${nomeGrupo}</strong>.`;
                    link = BASE_PATH + 'grupos/ver/' + idRef;
                    break;
                case 'promocao_moderador':
                    msgAction = `te promoveu a moderador no grupo <strong>${nomeGrupo}</strong>.`;
                    link = BASE_PATH + 'grupos/ver/' + idRef;
                    break;
                case 'rebaixamento_membro':
                    msgAction = `alterou seu cargo para membro no grupo <strong>${nomeGrupo}</strong>.`;
                    link = BASE_PATH + 'grupos/ver/' + idRef;
                    break;
                case 'transferencia_dono':
                    msgAction = `transferiu a posse do grupo <strong>${nomeGrupo}</strong> para você.`;
                    link = BASE_PATH + 'grupos/ver/' + idRef;
                    break;
                case 'expulsao_grupo':
                    msgAction = `removeu você do grupo <strong>${nomeGrupo}</strong>.`;
                    link = BASE_PATH + 'grupos';
                    break;
                case 'convite_chat_grupo':
                    msgAction = `convidou você para o chat em grupo <strong>${nomeGrupo}</strong>.`;
                    link = BASE_PATH + 'chat?id=' + idRef;
                    break;
                case 'voto_enquete':
                    msgAction = 'votou na sua enquete.';
                    link = BASE_PATH + 'postagem/' + idRef;
                    break;
                case 'interesse_mkt':
                    msgAction = 'demonstrou interesse num item seu no Marketplace.';
                    link = BASE_PATH + 'marketplace/item/' + idRef;
                    break;
                case 'broadcast':
                    msgAction = `<strong>${escapeHtml(notif.remetente_nome)}</strong>: ${escapeHtml(notif.grupo_nome)}`;
                    link = '#';
                    avatar = BASE_PATH + 'assets/images/favicon.png';
                    break;
            }

            html += `
                <a href="${link}" class="notification-item ${isUnread}" data-id="${notif.id}">
                    <div class="notification-avatar">
                        <img src="${avatar}" alt="User">
                    </div>
                    <div class="notification-text">
                        <p><span class="user-name">${escapeHtml(notif.remetente_nome || 'Sistema')}</span> ${msgAction}</p>
                        <span class="notification-time">${formatDate(notif.data_criacao)}</span>
                    </div>
                </a>`;
        });

        notificationList.innerHTML = html;

        notificationList.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                if (this.classList.contains('unread')) {
                    markAsRead(id);
                }
            });
        });
    }

    function markAsRead(id) {
        const formData = new FormData();
        formData.append('id', id);
        fetch(BASE_PATH + 'api/notificacoes/marcar_uma_como_lida.php', { method: 'POST', body: formData });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDate(dateString) {
        if(!dateString) return '';
        const date = new Date(dateString.replace(/-/g, "/"));
        return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute:'2-digit' });
    }
});