/**
 * main.js
 * Funções globais (apiFetch, showToast) e inicialização
 * dos menus, dropdowns e heartbeat.
 *
 * VERSÃO: V65.2 (Rigor Arquitetônico - socialbr.lol)
 * PAPEL: Orquestrador de Infraestrutura Global.
 */

// ==========================================================
// FUNÇÕES GLOBAIS
// ==========================================================

/**
 * Wrapper de Fetch para a API.
 * Lida com a URL base, FormData e resposta JSON.
 */
async function apiFetch(endpoint, method = 'POST', body = null) {
    if (typeof BASE_PATH === 'undefined') {
        console.error('Erro Crítico: BASE_PATH não está definida. O AJAX falhará.');
        throw new Error('Configuração de script ausente.');
    }
    if (typeof CSRF_TOKEN === 'undefined' || !CSRF_TOKEN) {
        console.error('Erro Crítico: CSRF_TOKEN não está definida ou está vazia.');
        throw new Error('CSRF Token ausente.');
    }

    const url = BASE_PATH + 'api/' + endpoint;
    const options = {
        method: method,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        }
    };

    if (method === 'POST' && body) {
        if (body instanceof FormData) {
            body.append('csrf_token', CSRF_TOKEN);
        }
        options.body = body;
    }

    try {
        const response = await fetch(url, options);
        if (!response.ok) {
            let errorMsg = `Erro HTTP ${response.status} ao aceder ${endpoint}`;
            try {
                const errorData = await response.json();
                errorMsg = errorData.erro || errorMsg;
            } catch (e) { }
            throw new Error(errorMsg);
        }
        return await response.json();
    } catch (error) {
        console.error('Falha na apiFetch:', error);
        throw error;
    }
}

/**
 * Exibe uma notificação 'toast' global.
 */
function showToast(message, type = 'success') {
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) {
        existingToast.remove();
    }
    
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type === 'error' ? 'toast-error' : 'toast-success'}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
    
    setTimeout(() => {
        toast.remove();
    }, 3500);
}


// ==========================================================
// INICIALIZAÇÃO DO SITE
// ==========================================================

document.addEventListener('DOMContentLoaded', function() {

    // --- LÓGICA PARA O MENU MOBILE (SHIELDED V65.1) ---
    const menuToggle = document.getElementById('mobile-menu-toggle');
    const mobileNav = document.getElementById('mobile-nav-panel');
    const overlay = document.getElementById('overlay');
    const closeBtn = document.getElementById('close-mobile-menu');

    function openMenu() {
        if (mobileNav) mobileNav.classList.add('is-open');
        if (overlay) overlay.classList.add('is-visible');
        document.body.style.overflow = 'hidden'; 
    }

    function closeMenu() {
        if (mobileNav) mobileNav.classList.remove('is-open');
        if (overlay) overlay.classList.remove('is-visible');
        document.body.style.overflow = ''; 
    }

    if (menuToggle) {
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); 
            openMenu();
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            closeMenu();
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeMenu);
        overlay.addEventListener('touchstart', closeMenu, {passive: true});
    }


    // --- LÓGICA PARA O DROPDOWN DE CONFIGURAÇÕES ---
    const configToggles = document.querySelectorAll('.config-dropdown-toggle');

    configToggles.forEach(toggleBtn => {
        toggleBtn.addEventListener('click', function(event) {
            event.preventDefault(); 
            const configSubmenu = toggleBtn.closest('.nav-dropdown-toggle').nextElementSibling;

            if (configSubmenu && configSubmenu.classList.contains('config-submenu')) {
                configSubmenu.classList.toggle('is-hidden');
                toggleBtn.classList.toggle('active');
            }
        });
    });


    // --- OUVINTE DE CLIQUES ÚNICO E CENTRALIZADO ---
    document.body.addEventListener('click', function(event) {
        const clickedMenuBtn = event.target.closest('.post-options-btn, .comment-options-btn, .friend-dropdown-toggle');
        let activeMenu = null;

        if (clickedMenuBtn) {
            event.stopPropagation(); 
            activeMenu = clickedMenuBtn.nextElementSibling;
            if (activeMenu) {
                activeMenu.classList.toggle('is-hidden');
            }
        }

        // Fecha outros menus abertos
        document.querySelectorAll('.post-options-menu, .comment-options-menu, .dropdown-content').forEach(menu => {
            if (menu !== activeMenu) {
                menu.classList.add('is-hidden');
            }
        });

        // Denúncias
        const reportBtn = event.target.closest('.post-report-btn');
        if (reportBtn) {
            event.preventDefault();
            if (typeof openReportModal === 'function') {
                openReportModal(reportBtn);
            }
        }

        // CORREÇÃO: Removido .btn-comentar-trigger desta lógica de 'focus'.
        // Agora o clicar em 'Comentar' abrirá o modal via comentarios.js
        const focusBtn = event.target.closest('#focus-comment-btn');
        if(focusBtn) {
            if (window.location && window.location.pathname && !window.location.pathname.includes('/postagem/')) {
                event.preventDefault();
                const postCard = focusBtn.closest('.post-card');
                if (postCard) {
                    const commentInput = postCard.querySelector('.comment-input-field') || postCard.querySelector('.comment-input');
                    if (commentInput) {
                        commentInput.focus();
                    }
                }
            }
        }
    });

    // Fechar menus ao clicar em qualquer lugar (fora dos botões de menu)
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.post-options-btn, .comment-options-btn, .friend-dropdown-toggle') && !event.target.closest('.post-options-menu, .comment-options-menu, .dropdown-content')) {
            document.querySelectorAll('.post-options-menu, .comment-options-menu, .dropdown-content').forEach(menu => {
                menu.classList.add('is-hidden');
            });
        }
    });

    // --- UPLOAD AUTOMÁTICO DE CAPA ---
    const coverInput = document.getElementById('cover-input');
    const coverForm = document.getElementById('cover-upload-form');

    if (coverInput && coverForm) {
        coverInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                showToast('A processar nova capa...', 'success');
                coverForm.submit();
            }
        });
    }

    // --- HEARTBEAT DE STATUS ONLINE ---
    function enviarHeartbeat() {
        if (typeof BASE_PATH === 'undefined') return; 
        const cacheBuster = new Date().getTime();
        const fullUrl = BASE_PATH + 'api/usuarios/atualizar_status_online.php?t=' + cacheBuster;

        fetch(fullUrl, { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        })
        .catch(error => console.error('Erro no heartbeat:', error));
    }

    enviarHeartbeat(); 
    setInterval(enviarHeartbeat, 120000); 

});
