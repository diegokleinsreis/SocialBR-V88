/**
 * admin/assets/js/admin_menus.js
 * PAPEL: Gestão de Rotas, Validação de Arquivos e Correção de Checkbox de Manutenção.
 * VERSÃO: 2.8 (Full State Mapping & Maintenance Fix - socialbr.lol)
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // --- [CONFIGURAÇÃO DE AMBIENTE: RECUPERAÇÃO DE CAMINHO] ---
    const base_path = window.base_path || '/';

    // --- [PROTEÇÃO DE CRASH: SELETORES ADMIN] ---
    const formRota = document.getElementById('formGerenciarRota');
    const modalElement = document.getElementById('modalEditorRota');
    const btnSalvar = document.getElementById('btnSalvarRota');
    const iconInput = document.getElementById('rota_icone');
    const currentIconDisplay = document.getElementById('current_icon_display');
    const fileInput = document.getElementById('rota_arquivo_destino');

    let modalInstance = null;
    if (modalElement && typeof bootstrap !== 'undefined') {
        modalInstance = new bootstrap.Modal(modalElement);
    }

    // 1. FILTRO DE BUSCA
    const filtroBusca = document.getElementById('filtroBuscaRotas');
    if (filtroBusca) {
        filtroBusca.addEventListener('keyup', function() {
            const termo = this.value.toLowerCase();
            const linhas = document.querySelectorAll('table tbody tr');
            
            linhas.forEach(linha => {
                const texto = linha.innerText.toLowerCase();
                linha.style.display = texto.includes(termo) ? '' : 'none';
            });
        });
    }

    // 2. LÓGICA DE PREVIEW DE ÍCONE
    if (iconInput && currentIconDisplay) {
        iconInput.addEventListener('input', function() {
            const iconClass = this.value.trim();
            currentIconDisplay.className = iconClass + ' fa-3x';
            
            if (iconClass === "") {
                currentIconDisplay.className = 'fas fa-icons fa-3x';
            }
        });
    }

    // 3. VALIDAÇÃO DE INTEGRIDADE DO ARQUIVO
    if (fileInput) {
        fileInput.addEventListener('input', function() {
            verificarIntegridadeArquivo(this.value);
        });
    }

    function verificarIntegridadeArquivo(caminho) {
        if (!fileInput || caminho.length < 3) {
            if (fileInput) fileInput.classList.remove('is-valid', 'is-invalid');
            return;
        }

        fetch(`${base_path}api/admin/menus_rotas_acoes.php?acao=verificar_arquivo&arquivo=${encodeURIComponent(caminho)}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                fileInput.classList.remove('is-invalid');
                fileInput.classList.add('is-valid');
                if (btnSalvar) btnSalvar.disabled = false;
            } else {
                fileInput.classList.remove('is-valid');
                fileInput.classList.add('is-invalid');
                if (btnSalvar) btnSalvar.disabled = true;
            }
        }).catch(err => console.log("Erro na validação: ", err));
    }

    // 4. ALTERNAR STATUS (Switch rápido na tabela)
    document.querySelectorAll('.btn-toggle-status').forEach(switchInput => {
        switchInput.addEventListener('change', function() {
            const id = this.getAttribute('data-id');
            const status = this.checked ? 1 : 0;

            fetch(`${base_path}api/admin/menus_rotas_acoes.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `acao=toggle_status&id=${id}&status=${status}`
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success && typeof Swal !== 'undefined') {
                    this.checked = !this.checked; 
                    Swal.fire('Erro', data.message || 'Falha ao mudar status', 'error');
                }
            });
        });
    });

    // 5. EDITAR ROTA (Mapeamento Seguro de Campos)
    document.querySelectorAll('.btn-editar-rota').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            
            const tituloModal = document.getElementById('tituloModal');
            if (tituloModal) tituloModal.innerText = "Editar Configurações do Módulo";
            
            const firstTabEl = document.querySelector('#modalTabs button[data-bs-target="#tab-basico"]');
            if (firstTabEl && typeof bootstrap !== 'undefined') {
                const firstTab = new bootstrap.Tab(firstTabEl);
                firstTab.show();
            }

            // Inicia o carregamento dos dados via API
            fetch(`${base_path}api/admin/menus_rotas_acoes.php?acao=obter&id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && modalInstance) {
                    const r = data.rota;
                    if(document.getElementById('rota_id')) document.getElementById('rota_id').value = r.id;
                    if(document.getElementById('rota_label')) document.getElementById('rota_label').value = r.label;
                    if(document.getElementById('rota_slug')) document.getElementById('rota_slug').value = r.slug;
                    if(document.getElementById('rota_parent_id')) document.getElementById('rota_parent_id').value = r.parent_id || '';
                    if(document.getElementById('rota_arquivo_destino')) document.getElementById('rota_arquivo_destino').value = r.arquivo_destino;
                    if(document.getElementById('rota_permissao')) document.getElementById('rota_permissao').value = r.permissao;
                    if(document.getElementById('rota_ordem')) document.getElementById('rota_ordem').value = r.ordem;
                    
                    if(document.getElementById('rota_liberacao_em')) {
                        document.getElementById('rota_liberacao_em').value = r.liberacao_em ? r.liberacao_em.replace(' ', 'T') : '';
                    }

                    if(iconInput) {
                        iconInput.value = r.icone;
                        currentIconDisplay.className = r.icone + ' fa-3x';
                    }

                    // --- [CORREÇÃO CRÍTICA: SINCRONIZAÇÃO TOTAL DOS CHECKBOXES] ---
                    if(document.getElementById('rota_permite_parametros')) {
                        document.getElementById('rota_permite_parametros').checked = (r.permite_parametros == 1);
                    }
                    if(document.getElementById('rota_exibir_no_menu')) {
                        document.getElementById('rota_exibir_no_menu').checked = (r.exibir_no_menu == 1);
                    }
                    if(document.getElementById('rota_status')) {
                        document.getElementById('rota_status').checked = (r.status == 1);
                    }
                    // NOVA LINHA: Sincroniza o estado de manutenção/bloqueio
                    if(document.getElementById('rota_manutencao_modulo')) {
                        document.getElementById('rota_manutencao_modulo').checked = (r.manutencao_modulo == 1);
                    }

                    verificarIntegridadeArquivo(r.arquivo_destino);
                    modalInstance.show();
                }
            });
        });
    });

    // 6. SALVAR / CRIAR ROTA
    if (formRota) {
        formRota.addEventListener('submit', function(e) {
            e.preventDefault();
            if (fileInput && fileInput.classList.contains('is-invalid')) {
                if(typeof Swal !== 'undefined') Swal.fire('Atenção', 'Arquivo inexistente.', 'warning');
                return;
            }

            const formData = new FormData(this);
            formData.append('acao', 'salvar');

            // Garantia para Checkboxes (Resolve o problema de checkboxes desmarcados não serem enviados)
            if (!document.getElementById('rota_permite_parametros').checked) formData.append('permite_parametros', '0');
            if (!document.getElementById('rota_exibir_no_menu').checked) formData.append('exibir_no_menu', '0');
            if (!document.getElementById('rota_status').checked) formData.append('status', '0');
            if (!document.getElementById('rota_manutencao_modulo').checked) formData.append('manutencao_modulo', '0');

            fetch(`${base_path}api/admin/menus_rotas_acoes.php`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) location.reload();
                else if(typeof Swal !== 'undefined') Swal.fire('Erro', data.message || 'Falha ao salvar', 'error');
            });
        });
    }

    // 7. EXCLUIR ROTA
    document.querySelectorAll('.btn-excluir-rota').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            if(typeof Swal === 'undefined') return;

            Swal.fire({
                title: 'Eliminar Módulo?',
                text: 'Esta ação não pode ser desfeita.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0C2D54',
                confirmButtonText: 'Sim, eliminar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`${base_path}api/admin/menus_rotas_acoes.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `acao=excluir&id=${id}`
                    }).then(() => location.reload());
                }
            });
        });
    });

    // 8. REGENERAR BACKUP JSON
    const btnBackup = document.getElementById('btnRegenerarBackup');
    if (btnBackup) {
        btnBackup.addEventListener('click', function() {
            fetch(`${base_path}api/admin/menus_rotas_acoes.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `acao=regenerar_json`
            }).then(res => res.json()).then(data => {
                if (data.success && typeof Swal !== 'undefined') Swal.fire('Sucesso', data.message, 'success');
            });
        });
    }

    // 9. LIMPAR LOGS DE ACESSOS NEGADOS
    const btnLimparLogs = document.getElementById('btnLimparLogs');
    if (btnLimparLogs) {
        btnLimparLogs.addEventListener('click', function() {
            fetch(`${base_path}api/admin/menus_rotas_acoes.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `acao=limpar_logs`
            }).then(() => location.reload());
        });
    }

    // 10. MOTOR DE CONTAGEM REGRESSIVA (Event Mode)
    function inicializarMotorEventos() {
        const itensAgendados = document.querySelectorAll('[data-liberacao]');
        
        itensAgendados.forEach(item => {
            const rawDate = item.getAttribute('data-liberacao');
            if(!rawDate) return;

            const dataAlvo = new Date(rawDate.replace(/-/g, "/")).getTime();
            
            const interval = setInterval(() => {
                const agora = new Date().getTime();
                const distancia = dataAlvo - agora;

                if (distancia < 0) {
                    clearInterval(interval);
                    const labelTempo = item.querySelector('.countdown-label');
                    if (labelTempo) labelTempo.innerText = ""; 
                    return;
                }

                const dias = Math.floor(distancia / (1000 * 60 * 60 * 24));
                const horas = Math.floor((distancia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutos = Math.floor((distancia % (1000 * 60 * 60)) / (1000 * 60));
                const segundos = Math.floor((distancia % (1000 * 60)) / 1000);

                const labelTempo = item.querySelector('.countdown-label');
                if (labelTempo) {
                    labelTempo.innerText = `${dias}d ${horas}h ${minutos}m ${segundos}s`;
                }
            }, 1000);

            item.addEventListener('click', function(e) {
                const agora = new Date().getTime();
                if (dataAlvo > agora) {
                    e.preventDefault();
                    e.stopPropagation();

                    if(typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'info',
                            title: 'Módulo em Breve',
                            text: 'Aguarde o lançamento oficial!',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                }
            });
        });
    }

    inicializarMotorEventos();
});