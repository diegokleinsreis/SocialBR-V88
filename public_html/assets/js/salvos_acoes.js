/**
 * assets/js/salvos_acoes.js
 * Motor de Interação do Módulo de Salvos Premium.
 * PAPEL: Gerenciar AJAX, Modais, Busca Dinâmica, Filtros e Modo Gerenciamento.
 * VERSÃO: V73.0 - Motor de Pastas & Modo Gerenciamento (socialbr.lol)
 */

document.addEventListener('DOMContentLoaded', function() {
    initBuscaSalvos();
    initFiltrosAjax();
});

// --- 1. MOTOR DE BUSCA E FILTROS (AJAX) ---

let buscaTimeout = null;

/**
 * Inicializa a busca com debounce para performance
 */
function initBuscaSalvos() {
    const inputBusca = document.getElementById('input-busca-salvos');
    const btnClear = document.getElementById('btn-clear-saved-search');

    if (!inputBusca) return;

    inputBusca.addEventListener('input', function() {
        const termo = this.value;
        
        if (termo.length > 0) {
            btnClear?.classList.remove('is-hidden');
        } else {
            btnClear?.classList.add('is-hidden');
        }

        clearTimeout(buscaTimeout);
        buscaTimeout = setTimeout(() => {
            atualizarListaSalvos();
        }, 500);
    });

    btnClear?.addEventListener('click', function() {
        inputBusca.value = '';
        this.classList.add('is-hidden');
        atualizarListaSalvos();
    });
}

/**
 * Captura cliques em abas e no novo Grid de Pastas para carregar via AJAX
 */
function initFiltrosAjax() {
    document.addEventListener('click', function(e) {
        // Suporte para Abas e para os novos Cards de Pasta
        const link = e.target.closest('.tab-link, .folder-card');
        
        // Bloqueia a navegação se o modo gerenciamento estiver ativo (para permitir editar/excluir)
        const isManagement = document.querySelector('.saved-collections-grid')?.classList.contains('is-management-active');
        
        if (link && link.href) {
            if (isManagement && link.classList.contains('folder-card')) {
                e.preventDefault();
                return; // No modo gerenciamento, o clique na pasta não navega
            }

            e.preventDefault();
            const url = new URL(link.href);
            
            window.history.pushState({}, '', url);
            
            // CORREÇÃO: Atualiza o estado visual "Ativo" imediatamente
            document.querySelectorAll('.tab-item, .folder-card').forEach(el => el.classList.remove('is-active'));
            
            if (link.classList.contains('folder-card')) {
                link.classList.add('is-active');
            } else {
                link.parentElement.classList.add('is-active');
            }

            atualizarListaSalvos();
        }
    });
}

/**
 * Função Core: Recarrega o container de itens salvos
 */
function atualizarListaSalvos() {
    const container = document.getElementById('saved-items-container');
    const loader = document.getElementById('saved-loader');
    
    if (!container) return;

    loader?.classList.remove('is-hidden');
    container.style.opacity = '0.5';

    const params = new URLSearchParams(window.location.search);
    const termoBusca = document.getElementById('input-busca-salvos')?.value;
    if (termoBusca) params.set('busca', termoBusca);

    fetch(`${window.location.pathname}?ajax=1&${params.toString()}`)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const novoConteudo = doc.getElementById('saved-items-container');
            
            if (novoConteudo) {
                container.innerHTML = novoConteudo.innerHTML;
            }
        })
        .catch(err => {
            console.error("Erro ao atualizar lista:", err);
            Swal.fire({
                icon: 'error',
                title: 'Erro de Sincronia',
                text: 'Não conseguimos atualizar a lista de itens.',
                confirmButtonColor: '#0C2D54'
            });
        })
        .finally(() => {
            loader?.classList.add('is-hidden');
            container.style.opacity = '1';
        });
}

// --- 2. GESTÃO DE COLEÇÕES (MODAIS & MODO GERENCIAMENTO) ---

/**
 * Alterna a exibição das ferramentas de edição/exclusão nas pastas
 */
function toggleGerenciamentoColecoes() {
    const grid = document.querySelector('.saved-collections-grid');
    const btn = document.querySelector('.btn-manage-collections');
    if (!grid || !btn) return;

    const isActive = grid.classList.toggle('is-management-active');
    btn.classList.toggle('is-active');

    if (isActive) {
        btn.innerHTML = '<i class="fas fa-check"></i> Concluir';
    } else {
        btn.innerHTML = '<i class="fas fa-cog"></i> Gerenciar';
    }
}

function abrirModalCriarColecao() {
    const modal = document.getElementById('modal-colecao-salvos');
    const form = document.getElementById('form-gestao-colecao');
    
    document.getElementById('modal-colecao-titulo').innerHTML = '<i class="fas fa-folder-plus"></i> Nova Coleção';
    document.getElementById('btn-submit-colecao').innerText = 'Criar Coleção';
    document.getElementById('modal-acao-tipo').value = 'criar';
    form.reset();

    modal.classList.remove('is-hidden');
}

function abrirModalEditarColecao(event, id, nome, privacidade) {
    if (event) event.stopPropagation(); // Impede que o clique abra a pasta
    
    const modal = document.getElementById('modal-colecao-salvos');
    
    document.getElementById('modal-colecao-titulo').innerHTML = '<i class="fas fa-edit"></i> Editar Coleção';
    document.getElementById('btn-submit-colecao').innerText = 'Salvar Alterações';
    document.getElementById('modal-acao-tipo').value = 'editar';
    document.getElementById('colecao-id-edicao').value = id;
    
    document.getElementById('colecao-nome').value = nome;
    document.getElementById('colecao-privacidade').value = privacidade;

    modal.classList.remove('is-hidden');
}

function fecharModalColecao() {
    document.getElementById('modal-colecao-salvos').classList.add('is-hidden');
}

/**
 * Envia os dados para a API usando SweetAlert2 para feedback
 */
function processarFormColecao(event) {
    event.preventDefault();
    const btn = document.getElementById('btn-submit-colecao');
    const originalText = btn.innerText;
    
    btn.disabled = true;
    btn.innerText = 'Processando...';

    const formData = new FormData(event.target);
    const tipo = document.getElementById('modal-acao-tipo').value;
    const id = document.getElementById('colecao-id-edicao').value;

    formData.append('acao_tipo', tipo);
    if (id) formData.append('colecao_id', id);
    
    const token = document.querySelector('input[name="csrf_token"]')?.value || window.csrf_token;
    if (token) formData.append('csrf_token', token);

    const apiEndpoint = `${base_path}api/salvos/colecoes.php`;

    fetch(apiEndpoint, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Tudo pronto!',
                text: data.message || 'Operação realizada com sucesso.',
                showConfirmButton: false,
                timer: 1500
            }).then(() => location.reload());
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Ops!',
                text: data.error || 'Erro ao processar a coleção.',
                confirmButtonColor: '#0C2D54'
            });
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Erro de Conexão',
            text: 'Não conseguimos falar com o servidor. Tente novamente.',
            confirmButtonColor: '#0C2D54'
        });
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerText = originalText;
    });
}

/**
 * Exclusão Segura com SweetAlert2 (Texto personalizado pelo usuário)
 */
function confirmarExclusaoColecao(event, id) {
    if (event) event.stopPropagation(); // Impede que o clique abra a pasta

    Swal.fire({
        title: 'Excluir Coleção?',
        text: "Todos os itens salvos nela serão excluido permanentemente. Essa ação não pode ser desfeita!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#0C2D54',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const token = document.querySelector('input[name="csrf_token"]')?.value || window.csrf_token;
            const formData = new FormData();
            formData.append('acao_tipo', 'excluir');
            formData.append('colecao_id', id);
            if (token) formData.append('csrf_token', token);

            fetch(`${base_path}api/salvos/colecoes.php`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Excluída!',
                        text: 'A coleção foi removida com sucesso.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.href = `${base_path}salvos`;
                    });
                } else {
                    Swal.fire('Erro!', data.error || 'Não foi possível excluir.', 'error');
                }
            })
            .catch(err => console.error("Erro na exclusão:", err));
        }
    });
}

// --- 3. AÇÕES DOS ITENS (CARD) ---

function toggleDropdownSalvos(id) {
    const dropdown = document.getElementById(`dropdown-salvo-${id}`);
    if (!dropdown) return;

    const isHidden = dropdown.classList.contains('is-hidden');
    document.querySelectorAll('.saved-dropdown-menu').forEach(el => el.classList.add('is-hidden'));
    
    if (isHidden) dropdown.classList.remove('is-hidden');
}

/**
 * Remove item apontando para a API central itens.php
 */
function removerItemSalvo(postId) {
    Swal.fire({
        title: 'Remover dos Salvos?',
        text: "Você pode salvar este item novamente a qualquer momento.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0C2D54',
        cancelButtonColor: '#e4e6eb',
        confirmButtonText: 'Sim, remover',
        cancelButtonText: 'Manter'
    }).then((result) => {
        if (result.isConfirmed) {
            const token = document.querySelector('input[name="csrf_token"]')?.value || window.csrf_token;

            fetch(`${base_path}api/salvos/itens.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    acao_tipo: 'remover',
                    post_id: postId,
                    csrf_token: token
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const card = document.querySelector(`.saved-card-item[data-id="${postId}"]`);
                    if (card) {
                        card.style.opacity = '0';
                        setTimeout(() => card.remove(), 300);
                    }
                } else {
                    Swal.fire('Erro!', data.error || 'Erro ao remover item.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Erro!', 'Não foi possível processar a remoção.', 'error');
            });
        }
    });
}

// --- 4. GESTÃO DE DESTINO (MOVER COLEÇÃO) ---

function abrirModalMoverColecao(postId) {
    const modal = document.getElementById('modal-selecionar-colecao');
    if (!modal) return;
    document.getElementById('post-id-para-salvar').value = postId;
    modal.classList.remove('is-hidden');
}

function fecharModalSelecao() {
    const modal = document.getElementById('modal-selecionar-colecao');
    if (modal) modal.classList.add('is-hidden');
}

/**
 * Move item apontando para a API central itens.php
 */
function executarSalvamento(colecaoId) {
    const postId = document.getElementById('post-id-para-salvar').value;
    const token = document.querySelector('input[name="csrf_token"]')?.value || window.csrf_token;

    fetch(`${base_path}api/salvos/itens.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            acao_tipo: 'mover',
            post_id: postId, 
            colecao_id: colecaoId,
            csrf_token: token 
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            fecharModalSelecao();
            Swal.fire({
                icon: 'success',
                title: 'Movido!',
                text: 'Item organizado com sucesso.',
                timer: 1000,
                showConfirmButton: false
            }).then(() => location.reload());
        } else {
            Swal.fire('Erro!', data.error || 'Erro ao mover item.', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire('Erro!', 'Falha ao mover item entre coleções.', 'error');
    });
}

function abrirModalCriarColecaoRapida() {
    fecharModalSelecao();
    abrirModalCriarColecao();
}

// Fecha dropdowns ao clicar fora
window.addEventListener('click', function(e) {
    if (!e.target.closest('.saved-card-options')) {
        document.querySelectorAll('.saved-dropdown-menu').forEach(el => el.classList.add('is-hidden'));
    }
});