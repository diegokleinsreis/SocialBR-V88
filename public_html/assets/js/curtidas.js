document.addEventListener('DOMContentLoaded', function() {
    
    // Adiciona um único "ouvinte" de cliques ao corpo do documento
    document.body.addEventListener('click', function(event) {
        
        // Verifica se o elemento que foi clicado (ou um dos seus pais) é um botão de curtir de POST
        const likeBtn = event.target.closest('.like-btn');

        // Se o clique não foi num botão de curtir de post, não faz nada
        if (!likeBtn) {
            return;
        }

        // CORREÇÃO: Sincronização de atributo para data-postid (conforme padrão lightbox)
        const postId = likeBtn.dataset.postid;
        const formData = new FormData();
        formData.append('post_id', postId);

        // Uso da função global apiFetch para garantir segurança e caminhos corretos
        apiFetch('postagens/curtir_post.php', 'POST', formData)
            .then(data => {

            if (data.success) {
                // Seleciona TODOS os botões de curtir para este post (no feed E no modal, se estiver aberto)
                const allLikeButtons = document.querySelectorAll(`.like-btn[data-postid="${postId}"]`);
                
                // CORREÇÃO DE SELETOR: Ajustado para '.post-stats-container' conforme definido na barra_estatisticas.php
                const allLikeCountSpans = document.querySelectorAll(
                    `#post-${postId} .post-stats-container .like-count, #lightbox-modal .post-stats .like-count`
                );

                // Atualiza a aparência de todos os botões correspondentes (toggle da classe active)
                allLikeButtons.forEach(button => {
                    button.classList.toggle('active', data.curtido);
                    
                    // Ajusta o ícone se necessário
                    const icon = button.querySelector('i');
                    if (icon) {
                        icon.className = data.curtido ? 'fas fa-thumbs-up' : 'far fa-thumbs-up';
                    }
                });

                // Atualiza o texto de todos os contadores correspondentes
                allLikeCountSpans.forEach(span => {
                    span.textContent = data.total_curtidas;
                });

            } else {
                alert(data.error || 'Ocorreu um erro.');
            }
        })
        .catch(error => console.error('Error no processamento da curtida:', error));
    });
});