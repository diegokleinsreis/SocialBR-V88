/**
 * amizade.js
 * Lógica para amizade (Aceitar/Recusar/Cancelar) e Bloqueio/Desbloqueio.
 * PAPEL: Intercetar cliques, injetar segurança CSRF e gerir APIs com SweetAlert2.
 * VERSÃO: V60.9 - Integração MotorDeAlertas (UX Premium) - socialbr.lol
 */
document.addEventListener('DOMContentLoaded', function() {

    /**
     * Função genérica para enviar pedidos para a API.
     */
    const enviarRequisicao = (endpoint, formData) => {
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        if (!csrfToken) {
            console.error('Erro de Segurança: Token CSRF não encontrado.');
            if (MotorDeAlertas) MotorDeAlertas.erro('Erro de Sessão', 'Recarregue a página para continuar.');
            return;
        }

        formData.append('csrf_token', csrfToken);

        if (typeof apiFetch !== 'function') {
            console.error('Erro Crítico: Utilitário apiFetch não encontrado.');
            return;
        }

        apiFetch(endpoint, 'POST', formData)
            .then(data => {
                if (data.success) {
                    // Feedback visual via Toast (mantido por ser menos intrusivo no sucesso)
                    if (window.showToast) {
                        window.showToast(data.message || 'Ação concluída!');
                    }
                    setTimeout(() => {
                        location.reload(); 
                    }, 1200); 
                } else {
                    throw new Error(data.error || 'Ocorreu um erro desconhecido.');
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                // Uso do MotorDeAlertas para Erros Críticos
                if (typeof MotorDeAlertas !== 'undefined') {
                    MotorDeAlertas.erro('Ops!', error.message);
                } else {
                    alert(error.message);
                }
            });
    };

    /**
     * "Ouvinte" Global de Cliques (Event Delegation)
     * Transformado em ASYNC para suportar os novos alertas de confirmação.
     */
    document.body.addEventListener('click', async function(event) {
        
        // --- 0. LÓGICA DO DROPDOWN ---
        const toggleBtn = event.target.closest('#btn-toggle-amigos');
        const container = document.getElementById('dropdown-amizade-container');

        if (toggleBtn) {
            event.preventDefault();
            event.stopPropagation();
            if (container) {
                container.classList.toggle('is-active');
            }
            return;
        }

        if (container && container.classList.contains('is-active')) {
            if (!container.contains(event.target)) {
                container.classList.remove('is-active');
            }
        }

        // --- 1. AÇÃO: ENVIAR PEDIDO DE AMIZADE ---
        const addBtn = event.target.closest('#add-friend-btn');
        if (addBtn) {
            event.preventDefault();
            const destinatarioId = addBtn.dataset.destinatarioId;
            if (!destinatarioId) return;

            const formData = new FormData();
            formData.append('id_usuario_recebe', destinatarioId);
            enviarRequisicao('amizade/enviar_pedido.php', formData);
        }

        // --- 2. AÇÃO: ACEITAR PEDIDO ---
        const acceptBtn = event.target.closest('.aceitar-pedido-btn');
        if (acceptBtn) {
            event.preventDefault();
            const amizadeId = acceptBtn.dataset.amizadeId;
            if (!amizadeId) return;

            const formData = new FormData();
            formData.append('id_amizade', amizadeId);
            enviarRequisicao('amizade/aceitar_pedido.php', formData);
        }

        // --- 3. AÇÃO: RECUSAR PEDIDO ---
        const refuseBtn = event.target.closest('.recusar-pedido-btn');
        if (refuseBtn) {
            event.preventDefault();
            const amizadeId = refuseBtn.dataset.amizadeId;
            if (!amizadeId) return;

            const formData = new FormData();
            formData.append('id_amizade', amizadeId);
            enviarRequisicao('amizade/recusar_pedido.php', formData);
        }

        // --- 4. AÇÃO: CANCELAR PEDIDO ENVIADO ---
        const cancelRequestBtn = event.target.closest('.cancelar-pedido-btn');
        if (cancelRequestBtn) {
            event.preventDefault();
            const amizadeId = cancelRequestBtn.dataset.amizadeId;
            if (!amizadeId) return;

            // NOVO: Confirmação Premium
            const confirmar = await MotorDeAlertas.confirmar(
                'Cancelar Pedido', 
                'Deseja cancelar esta solicitação de amizade?'
            );

            if (confirmar) {
                const formData = new FormData();
                formData.append('id_amizade', amizadeId);
                enviarRequisicao('amizade/cancelar_pedido.php', formData);
            }
        }

        // --- 5. AÇÃO: DESFAZER AMIZADE ---
        const cancelBtn = event.target.closest('.cancelar-amizade-btn');
        if (cancelBtn) {
            event.preventDefault();
            const amizadeId = cancelBtn.dataset.amizadeId;
            if (!amizadeId) return;

            // NOVO: Confirmação Premium
            const confirmar = await MotorDeAlertas.confirmar(
                'Desfazer Amizade', 
                'Tem a certeza de que deseja remover esta pessoa da sua lista de amigos?'
            );

            if (confirmar) {
                const formData = new FormData();
                formData.append('id_amizade', amizadeId);
                enviarRequisicao('amizade/cancelar_amizade.php', formData);
            }
        }

        // --- 6. AÇÃO: BLOQUEIO ---
        const blockBtn = event.target.closest('.bloquear-usuario-btn');
        if (blockBtn) {
            event.preventDefault();
            const usuarioId = blockBtn.dataset.usuarioId;
            const acao = blockBtn.dataset.acao;

            if (acao === 'bloquear' && usuarioId) {
                // NOVO: Confirmação Premium com cor de alerta
                const confirmar = await MotorDeAlertas.confirmar(
                    'Bloquear Usuário', 
                    'A amizade será desfeita e vocês não verão mais as publicações um do outro.',
                    'Sim, Bloquear',
                    '#e74c3c' // Vermelho para ação destrutiva
                );

                if (confirmar) {
                    const formData = new FormData();
                    formData.append('id_usuario_bloqueado', usuarioId);
                    enviarRequisicao('usuarios/bloquear_usuario.php', formData);
                }
            }
        }
    });
});