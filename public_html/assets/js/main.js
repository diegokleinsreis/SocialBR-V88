/**
 * main.js
 * Funções globais (apiFetch, showToast) e inicialização
 * dos menus, dropdowns e heartbeat.
 *
 * VERSÃO: V65.1 (Mobile Logic Shield & Scroll Lock - socialbr.lol)
 */

// ==========================================================
// FUNÇÕES GLOBAIS (V54)
// Definidas fora do DOMContentLoaded para serem acessíveis
// por outros scripts (curtidas.js, compartilhar.js, etc.)
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
        document.body.style.overflow = 'hidden'; // Trava o scroll do site ao abrir
    }

    function closeMenu() {
        if (mobileNav) mobileNav.classList.remove('is-open');
        if (overlay) overlay.classList.remove('is-visible');
        document.body.style.overflow = ''; // Libera o scroll
    }

    if (menuToggle) {
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Essencial: impede que o clique feche o menu na mesma hora
            openMenu();
        });
    }

    // Fechar pelo botão X
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            closeMenu();
        });
    }

    // Fechar ao clicar fora (Overlay) - Suporte Touch
    if (overlay) {
        overlay.addEventListener('click', closeMenu);
        overlay.addEventListener('touchstart', closeMenu, {passive: true});
    }


    // --- LÓGICA ATUALIZADA PARA O DROPDOWN DE CONFIGURAÇÕES ---
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


    // --- OUVINTE DE CLIQUES ÚNICO E CENTRALIZADO PARA TODA A PÁGINA ---
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

        document.querySelectorAll('.post-options-menu, .comment-options-menu, .dropdown-content').forEach(menu => {
            if (menu !== activeMenu) {
                menu.classList.add('is-hidden');
            }
        });

        const reportBtn = event.target.closest('.post-report-btn');
        if (reportBtn) {
            event.preventDefault();
            if (typeof openReportModal === 'function') {
                openReportModal(reportBtn);
            }
        }

        const commentBtn = event.target.closest('#focus-comment-btn, .btn-comentar-trigger');
        if(commentBtn) {
            if (window.location && window.location.pathname && !window.location.pathname.includes('/postagem/')) {
                event.preventDefault();
                const postCard = commentBtn.closest('.post-card');
                if (postCard) {
                    const commentInput = postCard.querySelector('.comment-input-field') || postCard.querySelector('.comment-input');
                    if (commentInput) {
                        commentInput.focus();
                    }
                }
            }
        }
    });

    // --- OUVINTE ADICIONAL PARA FECHAR MENUS AO CLICAR EM QUALQUER LUGAR ---
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.post-options-btn, .comment-options-btn, .friend-dropdown-toggle') && !event.target.closest('.post-options-menu, .comment-options-menu, .dropdown-content')) {
            document.querySelectorAll('.post-options-menu, .comment-options-menu, .dropdown-content').forEach(menu => {
                menu.classList.add('is-hidden');
            });
        }
    });

    // --- LÓGICA PARA UPLOAD AUTOMÁTICO DA FOTO DE CAPA (V64) ---
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
        const cacheBuster = new Date().getTime();
        if (typeof BASE_PATH === 'undefined') return; 
        
        const fullUrl = BASE_PATH + 'api/usuarios/atualizar_status_online.php?t=' + cacheBuster;

        fetch(fullUrl, { 
            method: 'POST', 
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        })
        .then(response => {
            if (!response.ok) {
                console.error('Falha no heartbeat do status online.');
            }
        })
        .catch(error => {
            console.error('Erro de rede no heartbeat:', error);
        });
    }

    enviarHeartbeat(); 
    setInterval(enviarHeartbeat, 120000); 

});