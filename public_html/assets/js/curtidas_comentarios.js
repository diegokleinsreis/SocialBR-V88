/**
 * assets/js/curtidas_comentarios.js
 * VERSÃO V9.2: Shielded Delegation & Sync
 * PAPEL: Gerir curtidas em comentários de forma resiliente em Feed e Modal.
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Adiciona um "ouvinte" de cliques ao corpo do documento (Delegação Global)
    document.body.addEventListener('click', function(event) {
        
        // Verifica se o elemento clicado é um botão de curtir comentário (ou ícone dentro dele)
        const likeButton = event.target.closest('.comment-like-btn');

        // Se não for o botão certo, não faz nada
        if (!likeButton) return;

        event.preventDefault(); 

        const commentId = likeButton.dataset.commentId;
        const formData = new FormData();
        formData.append('comment_id', commentId);

        // Feedback visual imediato (Otimismo de UI)
        likeButton.style.opacity = '0.5';

        // Chamada à API centralizada
        apiFetch('comentarios/curtir_comentario.php', 'POST', formData)
            .then(data => {
                if (data.success) {
                    // Encontra TODOS os botões e contadores para este comentário (Feed e Modal)
                    const allLikeButtons = document.querySelectorAll(`.comment-like-btn[data-comment-id="${commentId}"]`);
                    const allLikeCountSpans = document.querySelectorAll(`.comment-like-count[data-comment-id="${commentId}"]`);

                    // Atualiza a aparência de todos os botões vinculados a este ID
                    allLikeButtons.forEach(button => {
                        button.classList.toggle('active', data.curtido);
                        button.style.opacity = '1'; // Restaura opacidade
                    });

                    // Atualiza todos os contadores de forma robusta
                    allLikeCountSpans.forEach(span => {
                        // Busca o span interno para não quebrar o ícone <i>
                        const countText = span.querySelector('span') || span;
                        
                        if (data.total_curtidas > 0) {
                            // Se tiver o span interno (Padrão V9.1), atualiza apenas ele
                            if (span.querySelector('span')) {
                                span.querySelector('span').textContent = data.total_curtidas;
                            } else {
                                // Fallback para manter compatibilidade
                                const icon = span.querySelector('i');
                                span.innerHTML = '';
                                if (icon) span.appendChild(icon);
                                span.appendChild(document.createTextNode(' ' + data.total_curtidas));
                            }
                            span.style.display = 'inline-flex';
                        } else {
                            span.style.display = 'none';
                        }
                    });

                } else {
                    // Tratamento Premium de Erros
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Ops!', text: data.error || 'Erro ao curtir.', confirmButtonColor: '#0C2D54' });
                    } else {
                        alert(data.error || 'Ocorreu um erro ao processar sua curtida.');
                    }
                    likeButton.style.opacity = '1';
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                likeButton.style.opacity = '1';
            });
    });
});
