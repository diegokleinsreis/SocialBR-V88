/**
 * assets/js/salvar_post.js
 * PAPEL: Gerenciar a ação de salvar/remover postagens com suporte a coleções.
 * VERSÃO: V71.7 - Sincronia Instantânea de Menu (socialbr.lol)
 */

document.addEventListener('DOMContentLoaded', function() {

    // Engenharia de Delegação: Monitora cliques em qualquer botão de salvar do site
    document.body.addEventListener('click', function(event) {
        const saveBtn = event.target.closest('.post-save-trigger');
        if (!saveBtn) return;

        event.preventDefault();

        const postId = saveBtn.dataset.postid;
        // Verifica o estado atual pelo dataset para decidir a ação
        const isSaved = saveBtn.dataset.saved === "1";

        if (isSaved) {
            // Se já está salvo, removemos diretamente (UX Rápida com SweetAlert2)
            executarAcaoSalvamento(postId, 'remover', null, saveBtn);
        } else {
            // Se não está salvo, abrimos o seletor de coleções
            abrirSeletorColecao(postId, saveBtn);
        }
    });
});

/**
 * Abre o modal de seleção e prepara o estado
 */
function abrirSeletorColecao(postId, btnOrigem) {
    const modal = document.getElementById('modal-selecionar-colecao');
    if (!modal) return;

    // Armazena a referência do post e do botão para uso posterior no footer
    const inputPostId = document.getElementById('post-id-para-salvar');
    if (inputPostId) inputPostId.value = postId;
    
    window.currentSaveBtn = btnOrigem;

    modal.classList.remove('is-hidden');
}

function fecharModalSelecao() {
    const modal = document.getElementById('modal-selecionar-colecao');
    if (modal) modal.classList.add('is-hidden');
}

/**
 * Chamado pelo clique em uma coleção dentro do modal selecionar_colecao.php
 */
function executarSalvamento(colecaoId) {
    const postId = document.getElementById('post-id-para-salvar').value;
    const btnOrigem = window.currentSaveBtn;

    fecharModalSelecao();
    executarAcaoSalvamento(postId, 'salvar', colecaoId, btnOrigem);
}

/**
 * Core: Comunicação com a API unificada api/salvos/itens.php
 */
function executarAcaoSalvamento(postId, acao, colecaoId = null, btnElement = null) {
    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('acao_tipo', acao);
    
    // O token CSRF é buscado globalmente conforme definido no footer.php
    const token = document.querySelector('input[name="csrf_token"]')?.value || window.csrf_token;
    if (token) formData.append('csrf_token', token);
    
    if (colecaoId) formData.append('colecao_id', colecaoId);

    // apiFetch centralizada (definida no main.js)
    apiFetch('salvos/itens.php', 'POST', formData)
    .then(data => {
        if (data.success) {
            // Gatilho de atualização visual instantânea
            atualizarVisualBotao(btnElement, data.salvo);
            
            // Feedback UX Premium (Toast)
            Swal.fire({
                icon: data.salvo ? 'success' : 'info',
                title: data.salvo ? 'Salvo!' : 'Removido',
                text: data.message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });

            // Se o usuário remover um item estando na tela de salvos, o card desaparece
            verificarRemocaoCard(postId, btnElement, data.salvo);

        } else {
            Swal.fire({ icon: 'error', title: 'Ação Negada', text: data.error });
        }
    })
    .catch(error => {
        console.error('Erro no motor de salvos:', error);
        Swal.fire({ icon: 'error', title: 'Erro de Sistema', text: 'Não foi possível processar seu pedido.' });
    });
}

/**
 * Inteligência de Interface: Alterna estados do menu sem reload
 */
function atualizarVisualBotao(btn, estaSalvo) {
    if (!btn) return;

    const saveTextSpan = btn.querySelector('.save-text');
    const icon = btn.querySelector('i');

    // Atualiza o estado lógico
    btn.dataset.saved = estaSalvo ? "1" : "0";

    if (saveTextSpan) {
        saveTextSpan.textContent = estaSalvo ? 'Remover dos Salvos' : 'Salvar Publicação';
    }

    if (estaSalvo) {
        // Estilo: Salvo (Ativo)
        btn.classList.add('text-danger');
        if (icon) {
            icon.classList.remove('far');
            icon.classList.add('fas');
        }
    } else {
        // Estilo: Não Salvo (Padrão)
        btn.classList.remove('text-danger');
        if (icon) {
            icon.classList.remove('fas');
            icon.classList.add('far');
        }
    }
}

/**
 * Limpeza Dinâmica: Remove o card se for desmarcado na visualização de salvos
 */
function verificarRemocaoCard(postId, btn, estaSalvo) {
    if (estaSalvo) return; 

    const urlPath = window.location.pathname;
    const isPaginaSalvos = urlPath.includes('/salvos') || (urlPath.includes('/perfil') && window.location.search.includes('tab=salvos'));

    if (isPaginaSalvos && btn) {
        const card = btn.closest('.saved-card-item') || btn.closest('.post-card');
        if (card) {
            card.style.transition = 'all 0.4s ease';
            card.style.opacity = '0';
            card.style.transform = 'translateY(10px)';
            setTimeout(() => card.remove(), 400);
        }
    }
}

/**
 * Ponte entre Modais: Criação rápida de pasta
 */
function abrirModalCriarColecaoRapida() {
    fecharModalSelecao();
    if (typeof abrirModalCriarColecao === 'function') {
        abrirModalCriarColecao();
    }
}