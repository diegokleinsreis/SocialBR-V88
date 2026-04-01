document.addEventListener('DOMContentLoaded', function() {

    const postMediaInput = document.getElementById('post_media');
    const fileNameDisplay = document.getElementById('file-name-display');
    const previewContainer = document.getElementById('media-preview-container');

    // Função auxiliar para criar elementos HTML
    function createElement(tag, className) {
        const el = document.createElement(tag);
        if (className) el.className = className;
        return el;
    }

    if (postMediaInput && previewContainer) {
        
        postMediaInput.addEventListener('change', function(event) {
            const files = event.target.files;
            
            // 1. Limpa previews anteriores e esconde o container temporariamente
            previewContainer.innerHTML = '';
            if (fileNameDisplay) fileNameDisplay.textContent = ''; // Limpa o texto antigo "Ficheiro selecionado..."
            
            // 2. Validação de Quantidade
            if (files.length > 3) {
                alert('Você pode selecionar no máximo 3 arquivos.');
                this.value = ''; // Reseta o input (limpa a seleção)
                previewContainer.classList.add('is-hidden');
                return;
            }

            if (files.length > 0) {
                // Se houver arquivos, mostra o container
                previewContainer.classList.remove('is-hidden');

                // --- A. CRIA A OPÇÃO "SALVAR NA GALERIA" (Topo do Preview) ---
                // Inserimos isso dinamicamente para só aparecer quando o usuário seleciona algo
                const galleryOptionDiv = createElement('div', 'gallery-option-container');
                galleryOptionDiv.innerHTML = `
                    <label class="gallery-checkbox-wrapper">
                        <input type="checkbox" name="salvar_galeria" id="salvar_galeria" value="1">
                        <span>Salvar estas fotos na minha Galeria de Perfil</span>
                    </label>
                `;
                previewContainer.appendChild(galleryOptionDiv);

                // --- B. CRIA O GRID PARA AS FOTOS ---
                const gridDiv = createElement('div', 'media-preview-grid');
                previewContainer.appendChild(gridDiv);

                // --- C. GERA OS PREVIEWS ---
                Array.from(files).forEach(file => {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const mediaItem = createElement('div', 'media-preview-item');
                        
                        // Verifica se é imagem ou vídeo para criar a tag correta
                        if (file.type.startsWith('image/')) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            mediaItem.appendChild(img);
                        } else if (file.type.startsWith('video/')) {
                            const video = document.createElement('video');
                            video.src = e.target.result;
                            // controls=true permite dar play no preview se quiser
                            video.controls = true; 
                            mediaItem.appendChild(video);
                        }

                        gridDiv.appendChild(mediaItem);
                    };

                    // Lê o arquivo para gerar o DataURL (base64) para o preview
                    reader.readAsDataURL(file);
                });

            } else {
                // Se o utilizador cancelar a seleção (abrir a janela e não escolher nada)
                previewContainer.classList.add('is-hidden');
            }
        });
    }

});