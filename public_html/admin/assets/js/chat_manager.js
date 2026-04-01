/**
 * public_html/admin/assets/js/chat_manager.js
 * PAPEL: Motor de Gestão e Moderação do Chat Admin.
 * VERSÃO: 1.5 (Fix: Busca por ID Curto em Vínculos - socialbr.lol)
 */

const ChatManager = {
    // ADMIN_BASE deve ser definida globalmente no admin_chat.php antes deste script
    basePath: typeof ADMIN_BASE !== 'undefined' ? ADMIN_BASE : '/admin/',
    debounceTimer: null,

    /**
     * AUXILIAR: Renderiza o HTML de mídia de forma compacta
     */
    renderMedia(tipo, url) {
        if (!url) return '';
        
        // Ajusta o caminho: sai de /admin/ e entra em /assets/uploads/
        const fullUrl = this.basePath + '../' + url;

        if (tipo === 'foto') {
            return `<img src="${fullUrl}" style="max-width:150px; max-height:150px; border-radius:8px; cursor:pointer; margin: 5px 0; display:block; border: 1px solid #ddd;" onclick="window.open('${fullUrl}', '_blank')" title="Clique para ampliar">`;
        } else if (tipo === 'video') {
            return `<video src="${fullUrl}" controls style="max-width:200px; border-radius:8px; margin: 5px 0; display:block;"></video>`;
        } else if (tipo === 'audio') {
            return `<audio src="${fullUrl}" controls style="max-width:200px; height:35px; margin: 5px 0; display:block;"></audio>`;
        }
        return '';
    },

    /**
     * GHOST MODE (MODO ESPECTADOR)
     * Pode ser aberto via tabela de grupos, Auditoria ou Inspeção de Vínculos.
     */
    async openGhostMode(conversaId, titulo) {
        document.getElementById('ghost-title').innerText = "Auditando: " + titulo;
        document.getElementById('modalGhost').style.display = 'block';
        const body = document.getElementById('chat-history-body');
        body.innerHTML = '<p style="text-align:center; padding: 20px;">Escaneando registros ocultos...</p>';

        try {
            const response = await fetch(`${this.basePath}api/admin/chat/obter_logs_espectador.php?id=${conversaId}`);
            const data = await response.json();
            
            body.innerHTML = '';
            if(data.sucesso && data.mensagens.length > 0) {
                data.mensagens.forEach(m => {
                    const bubble = document.createElement('div');
                    bubble.className = 'bubble admin-view';
                    
                    let content = `<span class="sender">${m.remetente}</span>`;
                    
                    if (m.tipo_midia !== 'texto') {
                        content += this.renderMedia(m.tipo_midia, m.midia_url);
                    }
                    
                    if (m.texto) {
                        content += `<div style="margin-top:2px;">${m.texto}</div>`;
                    }
                    
                    content += `<small style="font-size:0.6em; color:#999; display:block; margin-top:4px;">${m.data}</small>`;
                    
                    bubble.innerHTML = content;
                    body.appendChild(bubble);
                });
                body.scrollTop = body.scrollHeight;
            } else {
                body.innerHTML = '<p style="text-align:center; padding: 20px;">Nenhuma mensagem nesta conversa.</p>';
            }
        } catch (e) { 
            body.innerHTML = '<p style="color:red; text-align:center; padding: 20px;">Erro ao carregar logs.</p>'; 
        }
    },

    closeGhostMode() {
        document.getElementById('modalGhost').style.display = 'none';
    },

    /**
     * GESTÃO DE MEMBROS E HIERARQUIA
     */
    async manageMembers(conversaId, titulo) {
        document.getElementById('members-title').innerText = "Gerir: " + titulo;
        document.getElementById('modalMembers').style.display = 'block';
        const body = document.getElementById('member-list-body');
        body.innerHTML = '<p style="text-align:center; padding: 20px;">Obtendo lista de participantes...</p>';

        try {
            const response = await fetch(`${this.basePath}api/admin/chat/obter_membros_grupo.php?id=${conversaId}`);
            const data = await response.json();
            
            body.innerHTML = '';
            if(data.sucesso && data.membros.length > 0) {
                data.membros.forEach(u => {
                    const avatarUrl = u.foto_perfil_url 
                        ? this.basePath + '../' + u.foto_perfil_url 
                        : this.basePath + '../assets/images/default-avatar.png';

                    const item = document.createElement('div');
                    item.className = 'member-item';
                    item.innerHTML = `
                        <div class="member-info">
                            <img src="${avatarUrl}" class="member-avatar" onerror="this.src='${this.basePath}../assets/images/default-avatar.png'">
                            <div>
                                <span class="member-name">${u.nome} ${u.sobrenome}</span>
                                ${u.eh_dono ? '<span style="color:#DAA520; font-size:0.7em; display:block;">Proprietário</span>' : ''}
                            </div>
                        </div>
                        <div style="display:flex; gap:5px;">
                            ${!u.eh_dono ? `
                                <button onclick="ChatManager.transferCrown(${conversaId}, ${u.id})" class="filter-btn" style="background:#DAA520; padding:4px 8px; font-size:0.75em;" title="Passar Coroa">
                                    <i class="fas fa-crown"></i>
                                </button>
                                <button onclick="ChatManager.kickMember(${conversaId}, ${u.id})" class="filter-btn" style="background:#dc3545; padding:4px 8px; font-size:0.75em;" title="Expulsar">
                                    <i class="fas fa-user-minus"></i>
                                </button>
                            ` : '<span style="font-size:0.8em; color:#999; padding:5px;">Líder</span>'}
                        </div>
                    `;
                    body.appendChild(item);
                });
            } else { body.innerHTML = '<p style="text-align:center; padding: 20px;">Erro na busca de membros.</p>'; }
        } catch (e) { body.innerHTML = '<p style="color:red; text-align:center; padding: 20px;">Erro ao carregar lista.</p>'; }
    },

    closeMembersMode() {
        document.getElementById('modalMembers').style.display = 'none';
    },

    /**
     * AÇÕES DE MODERAÇÃO ATIVA
     */
    async kickMember(conversaId, usuarioId) {
        if(!confirm("Expulsar este utilizador do grupo?")) return;
        try {
            const res = await fetch(`${this.basePath}api/admin/chat/remover_membro.php`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ conversa_id: conversaId, usuario_id: usuarioId })
            });
            const data = await res.json();
            if(data.sucesso) {
                alert("Membro removido.");
                this.manageMembers(conversaId, document.getElementById('members-title').innerText.replace("Gerir: ", ""));
            } else { alert("Erro: " + data.erro); }
        } catch (e) { alert("Falha na API de membros."); }
    },

    async transferCrown(conversaId, usuarioId) {
        if(!confirm("Deseja transferir a propriedade deste grupo para este utilizador?")) return;
        try {
            const res = await fetch(`${this.basePath}api/admin/chat/transferir_coroa.php`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ conversa_id: conversaId, usuario_id: usuarioId })
            });
            const data = await res.json();
            if(data.sucesso) {
                alert("Coroa transferida!");
                location.reload();
            } else { alert("Erro: " + data.erro); }
        } catch (e) { alert("Falha na API de hierarquia."); }
    },

    async toggleGroupStatus(id, currentStatus) {
        const acao = currentStatus === 'ativa' ? 'Bloquear' : 'Ativar';
        if(!confirm(`Deseja realmente ${acao} este grupo?`)) return;
        try {
            const res = await fetch(`${this.basePath}api/admin/chat/bloquear_conversa.php`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id: id, status: currentStatus })
            });
            const data = await res.json();
            if(data.sucesso) location.reload();
        } catch (e) { alert("Falha na API de moderação."); }
    },

    async deleteMessage(msgId) {
        if(!confirm("Apagar esta mensagem permanentemente?")) return;
        try {
            const res = await fetch(`${this.basePath}api/admin/chat/excluir_mensagem.php`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id: msgId })
            });
            const data = await res.json();
            if(data.sucesso) this.auditMessages(); 
        } catch (e) { alert("Erro ao excluir mensagem."); }
    },

    /**
     * AUDITORIA GLOBAL COM CONTEXTO
     */
    auditMessages() {
        clearTimeout(this.debounceTimer);
        const termo = document.getElementById('audit-search').value;
        if(termo.length < 3) {
            document.getElementById('audit-results').innerHTML = '<p style="text-align:center; color:#999; padding: 20px;">Digite ao menos 3 caracteres.</p>';
            return;
        }
        this.debounceTimer = setTimeout(async () => {
            const results = document.getElementById('audit-results');
            results.innerHTML = '<p style="text-align:center; padding: 20px;">Auditando...</p>';
            try {
                const res = await fetch(`${this.basePath}api/admin/chat/buscar_auditoria.php?termo=${encodeURIComponent(termo)}`);
                const data = await res.json();
                results.innerHTML = '';
                if(data.sucesso && data.resultados.length > 0) {
                    data.resultados.forEach(r => {
                        const mediaHtml = (r.tipo_midia !== 'texto') ? this.renderMedia(r.tipo_midia, r.midia_url) : '';
                        
                        const typeLabel = r.conversa_tipo === 'privada' ? 
                            '<span style="background:#e3f2fd; color:#1976d2; padding:2px 6px; border-radius:4px; font-size:0.7em; font-weight:800; margin-left:8px;">CHAT PRIVADO</span>' : 
                            '<span style="background:#fff3e0; color:#ef6c00; padding:2px 6px; border-radius:4px; font-size:0.7em; font-weight:800; margin-left:8px;">GRUPO</span>';

                        results.innerHTML += `
                            <div style="background:#fff; padding:10px; border-radius:8px; margin-bottom:8px; border:1px solid #eee; border-left: 3px solid #DAA520; position:relative;">
                                <div style="font-size:0.7em; font-weight:800; color:#0C2D54;">
                                    DE: ${r.remetente} | EM: ${r.data} ${typeLabel}
                                </div>
                                <div style="margin-top: 5px;">${mediaHtml}</div>
                                <div style="font-size:0.85em; margin-top: 5px; padding-right: 60px;">${r.mensagem}</div>
                                
                                <div style="position:absolute; right:10px; top:10px; display:flex; gap:10px;">
                                    <button onclick="ChatManager.openGhostMode(${r.conversa_id}, 'Auditoria Contextual')" title="Ver Contexto" style="border:none; background:none; color:#1976d2; cursor:pointer;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="ChatManager.deleteMessage(${r.id})" title="Apagar" style="border:none; background:none; color:#dc3545; cursor:pointer;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                } else { results.innerHTML = '<p style="text-align:center; color:#999; padding: 20px;">Nenhum rastro encontrado.</p>'; }
            } catch (e) { results.innerHTML = '<p style="color:red; text-align:center; padding: 20px;">Erro na auditoria.</p>'; }
        }, 500);
    },

    /**
     * INSPEÇÃO DE VÍNCULOS (Masterplan v9.4)
     * CORREÇÃO: Permite busca por ID numérico com apenas 1 caractere.
     */
    inspectUser() {
        clearTimeout(this.debounceTimer);
        const termo = document.getElementById('inspect-user-search').value.trim();
        const results = document.getElementById('inspect-results');

        // Lógica Inteligente: Se for número (ID), aceita 1 dígito. Se for texto, exige 3.
        const isNumeric = /^\d+$/.test(termo);
        const minLength = isNumeric ? 1 : 3;

        if(termo.length < minLength) {
            results.innerHTML = '';
            return;
        }

        this.debounceTimer = setTimeout(async () => {
            results.innerHTML = '<p style="grid-column: 1/-1; text-align:center; padding: 10px;">Rastreando conexões...</p>';
            try {
                const res = await fetch(`${this.basePath}api/admin/chat/buscar_vinculos_usuario.php?termo=${encodeURIComponent(termo)}`);
                const data = await res.json();
                results.innerHTML = '';

                if(data.sucesso && data.resultados.length > 0) {
                    data.resultados.forEach(u => {
                        let userCard = `
                            <div class="admin-card" style="margin:0; border-top: 3px solid #0C2D54; background: #fdfdfd;">
                                <h3 style="font-size:1em; color:#0C2D54; margin-bottom:10px;"><i class="fas fa-user"></i> ${u.nome} <small>(ID: ${u.id})</small></h3>
                                <div style="display:flex; flex-direction:column; gap:5px;">
                        `;

                        if(u.vinculos.length > 0) {
                            u.vinculos.forEach(v => {
                                const icon = v.tipo === 'privada' ? 'fa-user-friends' : 'fa-users';
                                
                                userCard += `
                                    <div class="inspect-item">
                                        <div class="inspect-meta">
                                            <i class="fas ${icon}"></i> ${v.label}
                                        </div>
                                        <button onclick="ChatManager.openGhostMode(${v.conversa_id}, 'Investigação: ${u.nome}')" class="filter-btn" style="background:#6c757d; width:28px; height:28px; font-size:0.8em;" title="Abrir Ghost Mode">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                `;
                            });
                        } else {
                            userCard += '<p style="font-size:0.8em; color:#999; text-align:center;">Sem conversas ativas.</p>';
                        }

                        userCard += '</div></div>';
                        results.innerHTML += userCard;
                    });
                } else {
                    results.innerHTML = '<p style="grid-column: 1/-1; text-align:center; color:#d32f2f;">Utilizador não localizado.</p>';
                }
            } catch (e) { results.innerHTML = '<p style="grid-column: 1/-1; text-align:center; color:red;">Falha na investigação.</p>'; }
        }, 500);
    },

    /**
     * ATUALIZAÇÃO DE DASHBOARD
     */
    async refreshStats() {
        try {
            const res = await fetch(`${this.basePath}api/admin/chat/obter_estatisticas.php`);
            const data = await res.json();
            if(data.sucesso) {
                if(document.getElementById('stat-total-msgs')) document.getElementById('stat-total-msgs').innerText = data.dados.total_mensagens;
                if(document.getElementById('stat-total-grupos')) document.getElementById('stat-total-grupos').innerText = data.dados.total_grupos;
                if(document.getElementById('stat-msgs-hoje')) document.getElementById('stat-msgs-hoje').innerText = data.dados.mensagens_hoje;
                if(document.getElementById('stat-users-ativos')) document.getElementById('stat-users-ativos').innerText = data.dados.usuarios_ativos;
            }
        } catch (e) { console.debug("ChatManager: Sincronização offline."); }
    }
};

// Auto-atualização das estatísticas a cada 60 segundos
setInterval(() => ChatManager.refreshStats(), 60000);