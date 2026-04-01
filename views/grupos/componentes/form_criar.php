<?php
/**
 * views/grupos/componentes/form_criar.php
 * Componente: Formulário de Criação de Grupo.
 * PAPEL: Capturar dados do novo grupo e enviar via AJAX para a API.
 * VERSÃO: 1.1 (UX Premium com Trava de Verificação)
 */

// O token CSRF é injetado pelo orquestrador (criar.php)
?>

<style>
    .form-create-group {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e4e6eb;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
    }

    /* Área de Preview da Capa */
    .cover-preview-container {
        width: 100%;
        height: 200px;
        background-color: #f0f2f5;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        cursor: pointer;
        border-bottom: 1px solid #e4e6eb;
    }

    .cover-preview-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: none; /* Escondido até carregar imagem */
    }

    .cover-placeholder {
        text-align: center;
        color: #65676b;
    }

    .cover-placeholder i {
        font-size: 3rem;
        margin-bottom: 10px;
        display: block;
    }

    /* Campos do Formulário */
    .form-body {
        padding: 25px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 700;
        color: #050505;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ccd0d5;
        border-radius: 8px;
        font-size: 1rem;
        outline: none;
        transition: border-color 0.2s;
        box-sizing: border-box;
    }

    .form-control:focus {
        border-color: #1877f2;
    }

    textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }

    /* Select de Privacidade */
    .privacy-selector {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-top: 10px;
    }

    .privacy-option {
        border: 2px solid #e4e6eb;
        border-radius: 10px;
        padding: 15px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .privacy-option i {
        font-size: 1.2rem;
        color: #65676b;
    }

    .privacy-option.active {
        border-color: #1877f2;
        background-color: #e7f3ff;
    }

    .privacy-option.active i {
        color: #1877f2;
    }

    .privacy-details strong {
        display: block;
        font-size: 0.9rem;
    }

    .privacy-details span {
        font-size: 0.8rem;
        color: #65676b;
    }

    /* Rodapé do Form */
    .form-footer {
        padding: 0 25px 25px 25px;
        display: flex;
        gap: 10px;
    }

    .btn-submit-group {
        flex: 1;
        background-color: #1877f2;
        color: #fff;
        border: none;
        padding: 14px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .btn-submit-group:hover {
        background-color: #166fe5;
    }

    .btn-submit-group:disabled {
        background-color: #bdc3c7;
        cursor: not-allowed;
    }

    .btn-cancel {
        padding: 14px 25px;
        background: #e4e6eb;
        color: #050505;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        text-align: center;
    }

    input[type="radio"] { display: none; }
</style>

<form action="<?php echo $config['base_path']; ?>api/grupos/processar_criacao.php" 
      method="POST" 
      enctype="multipart/form-data" 
      class="form-create-group"
      id="form-criar-grupo">

    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

    <div class="cover-preview-container" onclick="document.getElementById('grupo-capa-input').click();" id="cover-dropzone">
        <div class="cover-placeholder" id="placeholder-text">
            <i class="fas fa-camera"></i>
            <span>Adicionar Foto de Capa</span>
        </div>
        <img src="" id="image-preview" alt="Preview da Capa">
        <input type="file" name="foto_capa" id="grupo-capa-input" accept="image/*" style="display: none;" required>
    </div>

    <div class="form-body">
        
        <div class="form-group">
            <label for="nome_grupo">Nome do Grupo</label>
            <input type="text" name="nome" id="nome_grupo" class="form-control" placeholder="Dê um nome à sua comunidade" required maxlength="150">
        </div>

        <div class="form-group">
            <label for="desc_grupo">Descrição <small style="font-weight: 400; color: #65676b;">(Máx. 200 caracteres)</small></label>
            <textarea name="descricao" id="desc_grupo" class="form-control" placeholder="Sobre o que é este grupo?" maxlength="200"></textarea>
        </div>

        <div class="form-group">
            <label>Privacidade</label>
            <div class="privacy-selector">
                
                <label class="privacy-option active" data-value="publico">
                    <input type="radio" name="privacidade" value="publico" checked>
                    <i class="fas fa-globe-americas"></i>
                    <div class="privacy-details">
                        <strong>Público</strong>
                        <span>Qualquer pessoa pode ver tudo.</span>
                    </div>
                </label>

                <label class="privacy-option" data-value="privado">
                    <input type="radio" name="privacidade" value="privado">
                    <i class="fas fa-lock"></i>
                    <div class="privacy-details">
                        <strong>Privado</strong>
                        <span>Apenas membros veem o conteúdo.</span>
                    </div>
                </label>

            </div>
        </div>

    </div>

    <div class="form-footer">
        <a href="<?php echo $config['base_path']; ?>grupos" class="btn-cancel">Cancelar</a>
        <button type="submit" class="btn-submit-group" id="btn-submit-grupo-final">Criar Grupo</button>
    </div>

</form>

<script>
/**
 * Lógica Vanilla JS para interatividade e submissão AJAX
 */
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-criar-grupo');
    const inputCapa = document.getElementById('grupo-capa-input');
    const imgPreview = document.getElementById('image-preview');
    const placeholder = document.getElementById('placeholder-text');
    const privacyOptions = document.querySelectorAll('.privacy-option');
    const btnSubmit = document.getElementById('btn-submit-grupo-final');

    // 1. Preview da Imagem
    inputCapa.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imgPreview.src = e.target.result;
                imgPreview.style.display = 'block';
                placeholder.style.display = 'none';
            }
            reader.readAsDataURL(file);
        }
    });

    // 2. Alternar Estilo de Privacidade
    privacyOptions.forEach(option => {
        option.addEventListener('click', function() {
            privacyOptions.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // 3. Envio AJAX com SweetAlert2
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // UI Feedback
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Criando...';

        const formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Erro na comunicação com o servidor.');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Redireciona para o novo grupo
                window.location.href = data.redirect;
            } else {
                // Tratamento de Erro Especial: Verificação Pendente
                if (data.error === 'verificacao_pendente') {
                    Swal.fire({
                        title: '🛡️ Identidade Necessária',
                        text: data.message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#0C2D54', // Azul Oficial
                        cancelButtonColor: '#606770',
                        confirmButtonText: 'Verificar E-mail Agora',
                        cancelButtonText: 'Talvez mais tarde'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Redireciona para a aba de conta correta (?tab=conta)
                            const path = '<?php echo $config["base_path"]; ?>';
                            window.location.href = path + 'configurar_perfil?tab=conta';
                        }
                    });
                } else {
                    // Erros Comuns
                    Swal.fire({
                        title: 'Ops!',
                        text: data.message || 'Ocorreu um erro ao criar o grupo.',
                        icon: 'error',
                        confirmButtonColor: '#0C2D54'
                    });
                }
                
                // Restaura o botão em caso de erro
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = 'Criar Grupo';
            }
        })
        .catch(err => {
            console.error('Erro Crítico:', err);
            Swal.fire({
                title: 'Erro de Ligação',
                text: 'Não foi possível contactar o servidor. Verifique a sua internet.',
                icon: 'error',
                confirmButtonColor: '#d33'
            });
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = 'Criar Grupo';
        });
    });
});
</script>