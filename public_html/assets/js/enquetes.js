/**
 * assets/js/enquetes.js
 * VERSÃO V9.1: Motor SPA para Enquetes (Masterplan V9) + SweetAlert2 Integration
 * Funcionalidades: Voto em tempo real, Troca de Voto e Atualização Dinâmica.
 * socialbr.lol
 */

document.addEventListener('DOMContentLoaded', function() {

    // Ouvinte global de cliques para capturar interações com enquetes
    document.body.addEventListener('click', function(event) {
        
        // Deteta se o clique foi num botão de voto ou num item de enquete já votada (para troca)
        const voteTarget = event.target.closest('.poll-vote-btn, .poll-option-item');
        if (!voteTarget) return;

        event.preventDefault();

        // Localiza o contentor da enquete e os IDs necessários
        const container = voteTarget.closest('.post-poll-container');
        const enqueteId = container.dataset.enqueteid;
        const optionId  = voteTarget.dataset.optionid;

        if (!optionId || !enqueteId) return;

        // Feedback visual de "A Processar..."
        container.style.opacity = '0.6';
        container.style.pointerEvents = 'none';

        // Prepara os dados para a API (Incluindo o Token CSRF global)
        const formData = new FormData();
        formData.append('opcao_id', optionId);
        formData.append('enquete_id', enqueteId);
        
        // 🔒 BLINDAGEM CSRF
        if (typeof CSRF_TOKEN !== 'undefined') {
            formData.append('csrf_token', CSRF_TOKEN);
        }

        // Chama a API blindada de votação
        apiFetch('postagens/votar_enquete.php', 'POST', formData)
            .then(data => {
                if (data.success) {
                    // Atualiza a interface da enquete com os novos resultados
                    updatePollUI(container, data);
                    
                    // Notificação de sucesso Premium
                    if (window.showToast) {
                        const msg = (data.action === 'voto_removido') ? 'Voto cancelado.' : 'Voto processado com sucesso!';
                        window.showToast(msg);
                    }
                } else {
                    // Erro de Validação/Negócio via SweetAlert2
                    Swal.fire({
                        icon: 'warning',
                        title: 'Votação',
                        text: data.error || 'Não foi possível processar o seu voto.',
                        confirmButtonColor: '#0C2D54'
                    });
                }
            })
            .catch(error => {
                console.error('Erro na Enquete:', error);
                // Erro de Conexão via SweetAlert2
                Swal.fire({
                    icon: 'error',
                    title: 'Erro de Ligação',
                    text: 'Ocorreu um problema ao conectar ao servidor. Tente novamente.',
                    confirmButtonColor: '#d33'
                });
            })
            .finally(() => {
                // Restaura a interatividade do contentor
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
            });
    });

    /**
     * Função para redesenhar a Enquete com os novos dados (Modo Resultados)
     */
    function updatePollUI(container, data) {
        const optionsList = container.querySelector('.poll-options-list');
        const totalVotesElement = container.querySelector('.total-votes-count');
        const statusTextElement = container.querySelector('.poll-status-text');
        
        // Verifica se o utilizador tem um voto ativo após a operação
        const hasVoted = (data.voted_option !== null);
        
        // Limpa a lista atual para inserir o novo estado
        optionsList.innerHTML = '';

        data.opcoes.forEach(opt => {
            const isThisVoted = (opt.id == data.voted_option);
            let itemHTML = '';

            if (hasVoted) {
                // MODO RESULTADOS: Desenha as barras de percentagem
                itemHTML = `
                    <div class="poll-option-item ${isThisVoted ? 'voted' : ''}" 
                         data-optionid="${opt.id}"
                         style="position: relative; overflow: hidden; border: 1px solid #ccd0d5; border-radius: 6px; cursor: pointer; background: #fff; margin-bottom: 2px;">
                        
                        <div class="poll-bar-bg" style="position: absolute; top: 0; left: 0; height: 100%; background: #e4e6eb; width: 100%; z-index: 1;"></div>
                        <div class="poll-bar-fill" style="position: absolute; top: 0; left: 0; height: 100%; background: ${isThisVoted ? '#1877f2' : '#bcc0c4'}; width: ${opt.percentagem}%; z-index: 2; transition: width 0.5s ease;"></div>
                        
                        <div class="poll-option-info" style="position: relative; z-index: 3; padding: 10px 12px; display: flex; justify-content: space-between; align-items: center; font-weight: 600; font-size: 0.9rem; color: ${(opt.percentagem > 50 || isThisVoted) ? '#fff' : '#050505'};">
                            <span>${escapeHTML(opt.opcao_texto)}</span>
                            <strong>${opt.percentagem}%</strong>
                        </div>
                    </div>`;
            } else {
                // MODO VOTAÇÃO: Se o voto foi removido, volta a mostrar os botões
                itemHTML = `
                    <div class="poll-option-item" data-optionid="${opt.id}" style="border: 1px solid #ccd0d5; border-radius: 6px; background: #fff;">
                        <button class="poll-vote-btn" 
                                data-optionid="${opt.id}"
                                style="width: 100%; padding: 10px; border: none; background: transparent; text-align: left; font-size: 0.95rem; color: #050505; cursor: pointer; font-weight: 500;">
                            ${escapeHTML(opt.opcao_texto)}
                        </button>
                    </div>`;
            }
            optionsList.insertAdjacentHTML('beforeend', itemHTML);
        });

        // Atualiza o rodapé com o novo total e instruções
        if (totalVotesElement) totalVotesElement.textContent = data.total_votos;
        if (statusTextElement) {
            statusTextElement.textContent = hasVoted ? 'Clique na sua opção para cancelar ou mudar' : 'Clique para votar';
        }
    }

    /**
     * Função auxiliar para prevenir XSS na renderização dinâmica
     */
    function escapeHTML(str) {
        return str.replace(/[&<>"']/g, m => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[m]));
    }
});