/**
 * assets/js/configuracoes.js
 * PAPEL: Gestão de formulários, Navegação AJAX e Trava de Confirmação de E-mail.
 * VERSÃO: V8.1 - Trust Lock & Nomenclatura "Confirmado" (socialbr.lol)
 */

document.addEventListener('DOMContentLoaded', function() {

    /**
     * --- 0. MOTOR DE NAVEGAÇÃO AJAX (FIX: F5 ISSUE) ---
     * Esta função permite trocar de aba sem recarregar o cabeçalho/menu.
     */
    const initTabsNavigation = () => {
        const tabsNav = document.getElementById('settings-tabs-nav');
        const renderArea = document.getElementById('settings-render-area');

        if (!tabsNav || !renderArea) return;

        tabsNav.addEventListener('click', async (e) => {
            const link = e.target.closest('.tab-link');
            if (!link) return;

            e.preventDefault();
            const tabName = link.getAttribute('data-tab');
            const targetURL = link.getAttribute('href');

            // Feedback visual de carregamento
            if (typeof NProgress !== 'undefined') NProgress.start();
            renderArea.style.opacity = '0.5';

            try {
                // Buscamos a página completa via AJAX
                const response = await fetch(targetURL);
                const html = await response.text();

                // Extraímos apenas o conteúdo do 'settings-render-area' da resposta
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.getElementById('settings-render-area');

                if (newContent) {
                    // Injetamos o novo conteúdo
                    renderArea.innerHTML = newContent.innerHTML;

                    // Atualizamos a classe 'active' na navegação
                    tabsNav.querySelectorAll('.tab-link').forEach(l => l.classList.remove('active'));
                    link.classList.add('active');

                    // Atualizamos a URL no navegador sem F5
                    history.pushState({ tab: tabName }, '', targetURL);

                    // Atualizamos o título da página
                    document.title = doc.title;

                    // RE-INICIALIZAÇÃO CRÍTICA: Fazemos o JS "acordar" para os novos elementos
                    initAllComponents();
                }
            } catch (error) {
                console.error('Erro na navegação AJAX:', error);
                window.location.href = targetURL; // Fallback: carrega normal se falhar
            } finally {
                renderArea.style.opacity = '1';
                if (typeof NProgress !== 'undefined') NProgress.done();
            }
        });

        // Suporte ao botão "Voltar" do navegador
        window.onpopstate = function() {
            window.location.reload(); 
        };
    };

    /**
     * --- 1. FUNÇÃO MESTRE DE SUBMISSÃO (Update: Suporte a Pré-Check) ---
     */
    const handleFormSubmit = (form, successCallback, preCheck = null) => {
        form.addEventListener('submit', async function(event) {
            event.preventDefault();

            // Executa pré-verificação se existir (ex: confirmação de troca de e-mail)
            if (preCheck && typeof preCheck === 'function') {
                const proceed = await preCheck();
                if (!proceed) return;
            }

            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            if (!submitButton) return;

            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> A guardar...';
            submitButton.disabled = true;

            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(async response => {
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error('Resposta inválida do servidor.');
                }
                if (!response.ok) throw new Error(`Erro ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: data.message || 'Alterações guardadas!',
                        confirmButtonColor: '#0C2D54'
                    }).then(() => {
                        // Se houve reset de e-mail, recarregamos para atualizar banners e badges
                        if (data.email_reset) {
                            location.reload();
                        }
                    });

                    if (window.showToast) window.showToast(data.message, 'success');
                    if (successCallback) successCallback(data);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Falha na Operação',
                        text: data.error || data.message || 'Erro inesperado.',
                        confirmButtonColor: '#0C2D54'
                    });
                }
            })
            .catch(error => {
                console.error('Erro no processamento:', error);
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Erro de Sistema', 
                    text: error.message, 
                    confirmButtonColor: '#d33' 
                });
            })
            .finally(() => {
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            });
        });
    };

    /**
     * --- 2. INICIALIZAÇÃO DE COMPONENTES ---
     */
    const initAllComponents = () => {
        
        // Form Perfil
        const formPerfil = document.getElementById('form-perfil');
        if (formPerfil) {
            handleFormSubmit(formPerfil, (data) => {
                if (data.new_avatar_url) {
                    document.querySelectorAll('#avatar-preview-img, .sidebar-nav .nav-avatar img').forEach(img => {
                        img.src = data.new_avatar_url;
                    });
                }
            });
        }

        // Form Conta (Com Trava de Confirmação de E-mail)
        const formConta = document.getElementById('form-conta');
        if (formConta) {
            const emailInput = document.getElementById('email');
            const initialEmail = emailInput ? emailInput.value : '';

            handleFormSubmit(formConta, () => {
                ['senha_atual', 'nova_senha', 'confirmar_nova_senha'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.value = '';
                });
            }, async () => {
                // Lógica de Pré-Check: Deteta mudança de e-mail
                if (emailInput && emailInput.value !== initialEmail) {
                    const result = await Swal.fire({
                        title: 'Confirmar Alteração?',
                        text: 'Ao alterar o seu e-mail, o seu status de "E-mail Confirmado" será removido e precisará de uma nova validação para aceder a todos os recursos.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#0C2D54',
                        cancelButtonColor: '#606770',
                        confirmButtonText: 'Sim, alterar e-mail',
                        cancelButtonText: 'Cancelar'
                    });
                    return result.isConfirmed;
                }
                return true;
            });
        }

        // Form Privacidade
        const formPrivacidade = document.getElementById('form-privacidade');
        if (formPrivacidade) {
            handleFormSubmit(formPrivacidade);
            initPrivacyToggle();
        }

        // Verificação de E-mail
        initEmailVerification();

        // Preview de Imagem & BMP Blocker
        initImagePreview();
    };

    /**
     * --- 3. LOGICA ESPECIFICA: CONFIRMAÇÃO ---
     */
    function initEmailVerification() {
        const btnReenviar = document.getElementById('btn-reenviar-verificacao');
        if (!btnReenviar) return;

        btnReenviar.onclick = async function() {
            const originalHTML = btnReenviar.innerHTML;
            btnReenviar.disabled = true;
            btnReenviar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

            try {
                const basePath = document.body.getAttribute('data-base-path') || '/';
                const response = await fetch(basePath + 'api/usuarios/reenviar_verificacao.php', {
                    method: 'POST'
                });
                const data = await response.json();

                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'E-mail Enviado!', text: data.message, confirmButtonColor: '#0C2D54' });
                } else {
                    Swal.fire({ icon: 'warning', title: 'Aviso', text: data.message, confirmButtonColor: '#0C2D54' });
                }
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'Erro', text: 'Falha na conexão.' });
            } finally {
                btnReenviar.disabled = false;
                btnReenviar.innerHTML = originalHTML;
            }
        };
    }

    /**
     * --- 4. LOGICA ESPECIFICA: PREVIEW & BMP ---
     */
    function initImagePreview() {
        const inputFile = document.getElementById('foto_perfil');
        const previewImg = document.getElementById('avatar-preview-img');
        if (!inputFile || !previewImg) return;

        inputFile.onchange = function() {
            const file = this.files[0];
            if (!file) return;

            if (file.type === 'image/bmp' || file.name.toLowerCase().endsWith('.bmp')) {
                Swal.fire({ icon: 'warning', title: 'Formato inválido', text: 'BMP não suportado.', confirmButtonColor: '#0C2D54' });
                this.value = ''; 
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => previewImg.src = e.target.result;
            reader.readAsDataURL(file);
        };
    }

    /**
     * --- 5. LOGICA ESPECIFICA: PRIVACIDADE ---
     */
    function initPrivacyToggle() {
        const privateSwitch = document.getElementById('perfil_privado_switch');
        const postPrivacySelect = document.getElementById('privacidade_posts_padrao_select');
        if (!privateSwitch || !postPrivacySelect) return;
        
        const update = () => {
            postPrivacySelect.disabled = privateSwitch.checked;
            if (privateSwitch.checked) postPrivacySelect.value = 'amigos';
        };
        update();
        privateSwitch.onchange = update;
    }

    // --- EXECUÇÃO INICIAL ---
    initTabsNavigation(); // Ativa o motor de abas
    initAllComponents();  // Ativa os formulários da aba atual
});