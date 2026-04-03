document.addEventListener('DOMContentLoaded', function() {
    
    // Adiciona um "ouvinte" de cliques ao corpo do documento
    document.body.addEventListener('click', function(event) {
        
        // Verifica se o elemento clicado é um botão de curtir comentário (ou ícone dentro dele)
        const likeButton = event.target.closest('.comment-like-btn');

        // Se não for o botão certo, não faz nada
        if (!likeButton) {
            return;
        }

        event.preventDefault(); // Impede que o link '#' recarregue a página

        const commentId = likeButton.dataset.commentId;
        const formData = new FormData();
        formData.append('comment_id', commentId);

        // --- [INÍCIO DA CORREÇÃO] ---
        // Trocamos o 'fetch(fullUrl, ...)' pela nossa função global 'apiFetch'
        // que já inclui o BASE_PATH e o CSRF_TOKEN.
        
        apiFetch('comentarios/curtir_comentario.php', 'POST', formData)
            .then(data => {
        // --- [FIM DA CORREÇÃO] ---
            if (data.success) {
                // Encontra TODOS os botões e contadores para este comentário específico
                const allLikeButtons = document.querySelectorAll(`.comment-like-btn[data-comment-id="${commentId}"]`);
                const allLikeCountSpans = document.querySelectorAll(`.comment-like-count[data-comment-id="${commentId}"]`);

                // Atualiza a aparência de todos os botões
                allLikeButtons.forEach(button => {
                    button.classList.toggle('active', data.curtido);
                });

                // Atualiza todos os contadores
                allLikeCountSpans.forEach(span => {
                    // Atualiza o texto do contador (o nó de texto após o ícone <i>)
                    const icon = span.querySelector('i');
                    if (icon) {
                        icon.nextSibling.textContent = ' ' + data.total_curtidas;
                    } else {
                        // Fallback caso não tenha ícone (embora deva ter)
                        span.textContent = ' ' + data.total_curtidas;
                    }
                    
                    if (data.total_curtidas > 0) {
                        span.style.display = 'inline-flex'; // Usar inline-flex para alinhar o ícone e o texto
                    } else {
                        span.style.display = 'none';
                    }
                });

            } else {
                // Mostra um alerta se algo der errado
                alert(data.error || 'Ocorreu um erro ao processar sua curtida.');
            }
        })
        .catch(error => console.error('Erro na requisição:', error));
    });
});