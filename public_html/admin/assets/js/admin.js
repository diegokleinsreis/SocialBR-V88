/**
 * assets/js/admin.js
 * PAPEL: Gestão de interações do painel administrativo.
 * VERSÃO: 5.1 (FIX: Exibição de Descrição Adicional no Modal - socialbr.lol)
 */

// Finaliza a barra iniciada no header assim que o script carrega e o DOM está pronto
if (typeof NProgress !== 'undefined') NProgress.done();

document.addEventListener('DOMContentLoaded', function() {

    // --- 1. LÓGICA PARA O MENU MOBILE DO ADMIN ---
    const menuToggle = document.getElementById('mobile-menu-toggle');
    const mobileNav = document.getElementById('mobile-nav-panel');
    const overlay = document.getElementById('overlay');
    const closeBtn = document.getElementById('close-mobile-menu');

    function openMenu() {
        if (mobileNav) mobileNav.classList.add('is-open');
        if (overlay) overlay.classList.add('is-visible');
    }
    function closeMenu() {
        if (mobileNav) mobileNav.classList.remove('is-open');
        if (overlay) overlay.classList.remove('is-visible');
    }
    
    if (menuToggle) menuToggle.addEventListener('click', openMenu);
    if (overlay) overlay.addEventListener('click', closeMenu);
    if (closeBtn) closeBtn.addEventListener('click', closeMenu);


    // --- 2. LÓGICA DE API ADMIN (POST + CSRF) ---

    /**
     * Função mestre para enviar comandos POST para as APIs administrativas.
     * Agora com suporte a barra de progresso e alertas profissionais.
     */
    async function apiFetchAdmin(url, formData) {
        if (typeof CSRF_TOKEN === 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Erro de Segurança',
                text: 'Token CSRF não encontrado na página.',
                confirmButtonColor: '#0C2D54'
            });
            return { success: false, error: 'Token ausente.' };
        }

        NProgress.start(); // Inicia a barra no topo
        formData.append('csrf_token', CSRF_TOKEN);

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (!response.ok || !data.success) {
                throw new Error(data.error || `Erro de processamento (${response.status})`);
            }
            
            return data;

        } catch (error) {
            console.error('Falha no apiFetchAdmin:', error);
            Swal.fire({
                icon: 'error',
                title: 'Falha na Operação',
                text: error.message,
                confirmButtonColor: '#0C2D54'
            });
            throw error;
        } finally {
            NProgress.done(); // Finaliza a barra independente do resultado
        }
    }

    /**
     * Ouvinte Global para Ações POST (Status, Privacidade, etc)
     * Substituído confirm() e alert() pelo SweetAlert2
     */
    document.body.addEventListener('click', function(event) {
        const actionButton = event.target.closest('.admin-action-btn');
        if (!actionButton) return;

        event.preventDefault();

        const url = actionButton.dataset.url;
        const confirmMessage = actionButton.dataset.confirmMessage || "Deseja realizar esta ação?";

        if (!url) return;

        // SweetAlert2 substitui o confirm() nativo
        Swal.fire({
            title: 'Confirmar Ação',
            text: confirmMessage,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0C2D54',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, executar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                for (const key in actionButton.dataset) {
                    if (key !== 'url' && key !== 'confirmMessage') {
                        formData.append(key, actionButton.dataset[key]);
                    }
                }

                apiFetchAdmin(url, formData)
                    .then(data => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: data.message || 'Operação realizada com sucesso!',
                            confirmButtonColor: '#0C2D54'
                        }).then(() => {
                            location.reload();
                        });
                    });
            }
        });
    });


    // --- 3. LÓGICA PARA O MODAL DE DENÚNCIAS ---
    const modal = document.getElementById('denunciaModal');
    const span = document.getElementsByClassName('admin-modal-close')[0];
    const viewButtons = document.querySelectorAll('.view-btn');

    if (modal && viewButtons.length > 0) {
        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                const denunciaId = this.dataset.denunciaId;
                const modalContent = document.getElementById('denunciaConteudo');
                const modalHeaderActions = document.getElementById('admin-modal-header-actions');
                const modalFooterActions = document.getElementById('denunciaAcoes');
                
                // [NOVO] Captura a descrição integral passada pelo atributo data
                const descricaoDetalhada = this.dataset.descricaoCompleta;
                const descBox = document.getElementById('denunciaDescricaoDetalhada');

                if (!modalContent) return;

                NProgress.start(); // Feedback visual no topo
                modalContent.innerHTML = '<p><i class="fas fa-spinner fa-spin"></i> Carregando detalhes...</p>';
                if (modalHeaderActions) modalHeaderActions.innerHTML = '';
                if (modalFooterActions) modalFooterActions.innerHTML = '';

                // [NOVO] Injeta a descrição detalhada se ela existir
                if (descBox) {
                    if (descricaoDetalhada && descricaoDetalhada.trim() !== '' && descricaoDetalhada !== '---') {
                        descBox.innerHTML = `
                            <div class="denuncia-detalhes-box">
                                <span class="denuncia-detalhes-label">Informações Adicionais do Denunciante:</span>
                                <div class="denuncia-detalhes-texto">${descricaoDetalhada}</div>
                            </div>
                        `;
                    } else {
                        descBox.innerHTML = '';
                    }
                }

                modal.style.display = "block";

                const finalBasePath = (typeof BASE_PATH !== 'undefined') ? BASE_PATH : '';
                const fetchUrl = `${finalBasePath}api/admin/obter_detalhes_denuncia.php?id=${denunciaId}`;

                fetch(fetchUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            modalContent.innerHTML = data.html;
                            const d = data.denuncia;
                            
                            if (data.post_id_referencia && modalHeaderActions) {
                                const postLink = `${finalBasePath}postagem/${data.post_id_referencia}`;
                                modalHeaderActions.innerHTML = `<a href="${postLink}" class="action-btn view-post-btn" target="_blank">Ver Publicação</a>`;
                            }

                            if (modalFooterActions) {
                                let urlOcultar = '';
                                if (d.tipo_conteudo === 'post') {
                                    urlOcultar = `${finalBasePath}api/admin/toggle_post_status.php`;
                                } else if (d.tipo_conteudo === 'comentario') {
                                    urlOcultar = `${finalBasePath}api/admin/toggle_comment_status.php`;
                                }
                                
                                const urlIgnorar = `${finalBasePath}api/admin/atualizar_status_denuncia.php`;

                                modalFooterActions.innerHTML = `
                                    <button class="action-btn ignore-btn admin-action-btn" data-url="${urlIgnorar}" data-id="${d.id}" data-status="ignorado" data-confirm-message="Ignorar esta denúncia?">Ignorar</button>
                                    <button class="action-btn approve-btn admin-action-btn" data-url="${urlIgnorar}" data-id="${d.id}" data-status="revisado" data-confirm-message="Manter este conteúdo como ativo?">Manter Conteúdo</button>
                                    <button class="action-btn hide-btn admin-action-btn" data-url="${urlOcultar}" data-id="${d.id_conteudo}" data-denuncia-id="${d.id}" data-confirm-message="Ocultar conteúdo permanentemente?">Ocultar Conteúdo</button>
                                `;
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: data.message,
                                confirmButtonColor: '#0C2D54'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Fetch Error:', error);
                        modalContent.innerHTML = '<p>Erro de ligação ao servidor.</p>';
                    })
                    .finally(() => {
                        NProgress.done(); // Finaliza a barra
                    });
            });
        });
    }


    // --- 4. LÓGICA PARA O MODAL DE LOGS DE AUDITORIA ---
    const logModal = document.getElementById('logDetalhesModal');
    const viewLogButtons = document.querySelectorAll('.view-log-btn');

    if (logModal && viewLogButtons.length > 0) {
        viewLogButtons.forEach(button => {
            button.addEventListener('click', function() {
                const logId = this.dataset.logId;
                const logContent = document.getElementById('logConteudo');

                if (!logContent) return;

                NProgress.start(); // Inicia barra para carregamento do log
                logContent.innerHTML = '<p><i class="fas fa-spinner fa-spin"></i> Carregando detalhes completos...</p>';
                logModal.style.display = "block";

                const finalBasePath = (typeof BASE_PATH !== 'undefined') ? BASE_PATH : '';
                const fetchUrl = `${finalBasePath}api/admin/obter_detalhes_log.php?id=${logId}`;

                fetch(fetchUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const l = data.log;
                            logContent.innerHTML = `
                                <div class="log-detail-item">
                                    <span class="log-detail-label">Administrador</span>
                                    <div class="admin-info-cell" style="margin-top:5px;">
                                        <img src="${l.admin_foto}" class="admin-img-xs" style="width:32px; height:32px;">
                                        <span class="admin-name" style="font-size:1rem;">${l.admin_nome}</span>
                                    </div>
                                </div>
                                <div class="log-detail-item">
                                    <span class="log-detail-label">Ação Executada</span>
                                    <span class="log-detail-value"><strong>${l.acao}</strong></span>
                                </div>
                                <div class="log-detail-item">
                                    <span class="log-detail-label">Alvo do Sistema</span>
                                    <span class="log-detail-value">${l.tipo} (ID #${l.id_alvo})</span>
                                </div>
                                <div class="log-detail-item">
                                    <span class="log-detail-label">Data e Hora do Registro</span>
                                    <span class="log-detail-value">${l.data}</span>
                                </div>
                                <div class="log-detail-item">
                                    <span class="log-detail-label">Detalhes Completos da Atividade</span>
                                    <div class="log-full-text">${l.detalhes}</div>
                                </div>
                            `;
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: data.message,
                                confirmButtonColor: '#0C2D54'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Fetch Error:', error);
                        logContent.innerHTML = '<p>Erro ao obter dados do log.</p>';
                    })
                    .finally(() => {
                        NProgress.done(); // Finaliza barra
                    });
            });
        });
    }


    // --- FECHAMENTO GLOBAL DE MODAIS ---
    if (span && modal) {
        span.onclick = function() { modal.style.display = "none"; }
    }

    window.onclick = function(event) {
        if (modal && event.target == modal) { modal.style.display = "none"; }
        if (logModal && event.target == logModal) { logModal.style.display = "none"; }
    }

});