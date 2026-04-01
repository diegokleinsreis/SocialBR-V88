/**
 * admin/assets/js/admin_erros.js
 * PAPEL: Motor de interatividade do Monitor Sentinela.
 * VERSÃO: 1.0 (socialbr.lol)
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // --- [CONFIGURAÇÃO DE AMBIENTE] ---
    const base_path = window.base_path || '/';
    const modalElement = document.getElementById('modalDetalhesErro');
    
    let modalInstance = null;
    if (modalElement && typeof bootstrap !== 'undefined') {
        modalInstance = new bootstrap.Modal(modalElement);
    }

    // --- [1. VER DETALHES DO ERRO] ---
    document.querySelectorAll('.btn-detalhes-erro').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');

            // Feedback visual de carregamento
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            this.disabled = true;

            fetch(`${base_path}api/admin/erros_acoes.php?acao=obter_detalhes&id=${id}`)
            .then(res => res.json())
            .then(data => {
                // Restaura o ícone original do botão
                this.innerHTML = '<i class="fas fa-search-plus"></i>';
                this.disabled = false;

                if (data.success && modalInstance) {
                    const e = data.dados;
                    
                    // Preenchimento do Modal
                    document.getElementById('detalhe_mensagem').innerText = e.mensagem;
                    document.getElementById('detalhe_localizacao').innerHTML = `Arquivo: <strong>${e.arquivo}</strong> na linha <strong>${e.linha}</strong>`;
                    document.getElementById('detalhe_url').innerText = e.url_acessada || 'N/A';
                    document.getElementById('detalhe_utilizador').innerText = e.nome_de_usuario ? `@${e.nome_de_usuario}` : 'Visitante';
                    document.getElementById('detalhe_ip').innerText = e.ip_endereco || '0.0.0.0';
                    document.getElementById('detalhe_criado').innerText = new Date(e.data_criacao).toLocaleString('pt-BR');
                    document.getElementById('detalhe_atualizado').innerText = new Date(e.data_atualizacao).toLocaleString('pt-BR');
                    document.getElementById('detalhe_ocorrencias').innerText = `${e.ocorrencias}x`;
                    document.getElementById('detalhe_stack').innerText = e.stack_trace || 'Nenhum rastro disponível.';
                    document.getElementById('detalhe_ua').innerText = e.user_agent;
                    
                    // Ajusta o select de status e vincula o ID ao botão de salvar
                    document.getElementById('input_status_erro').value = e.status;
                    document.getElementById('btnSalvarStatusErro').setAttribute('data-id', e.id);

                    modalInstance.show();
                } else {
                    if(typeof Swal !== 'undefined') Swal.fire('Erro', data.message || 'Falha ao carregar dados.', 'error');
                }
            })
            .catch(err => {
                console.error("Erro Sentinela:", err);
                this.innerHTML = '<i class="fas fa-search-plus"></i>';
                this.disabled = false;
            });
        });
    });

    // --- [2. ATUALIZAR STATUS DO ERRO] ---
    const btnSalvarStatus = document.getElementById('btnSalvarStatusErro');
    if (btnSalvarStatus) {
        btnSalvarStatus.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const status = document.getElementById('input_status_erro').value;

            fetch(`${base_path}api/admin/erros_acoes.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `acao=atualizar_status&id=${id}&status=${status}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (modalInstance) modalInstance.hide();
                    location.reload(); // Recarrega para atualizar os badges na tabela
                } else {
                    if(typeof Swal !== 'undefined') Swal.fire('Erro', data.message, 'error');
                }
            });
        });
    }

    // --- [3. EXCLUIR LOG INDIVIDUAL] ---
    document.querySelectorAll('.btn-excluir-erro').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            
            if(typeof Swal === 'undefined') return;

            Swal.fire({
                title: 'Eliminar este Log?',
                text: "Esta ação não pode ser desfeita.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                confirmButtonText: 'Sim, eliminar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`${base_path}api/admin/erros_acoes.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `acao=excluir&id=${id}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) location.reload();
                    });
                }
            });
        });
    });

    // --- [4. LIMPAR TODOS OS LOGS (BOTÃO GLOBAL)] ---
    const btnLimparTudo = document.getElementById('btnLimparTodosErros');
    if (btnLimparTudo) {
        btnLimparTudo.addEventListener('click', function() {
            if(typeof Swal === 'undefined') return;

            Swal.fire({
                title: 'Limpar Monitor Sentinela?',
                text: "Todos os registros de erros serão apagados permanentemente!",
                icon: 'danger',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                confirmButtonText: 'Sim, limpar tudo!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`${base_path}api/admin/erros_acoes.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `acao=limpar_tudo`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) location.reload();
                    });
                }
            });
        });
    }
});