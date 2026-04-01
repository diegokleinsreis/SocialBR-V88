<?php
/**
 * views/suporte/form_novo.php
 * COMPONENTE: Formulário de Abertura de Chamado (V2.0)
 * PAPEL: Capturar dados com suporte a colagem de imagem e preview real.
 * VERSÃO: V2.0 - socialbr.lol
 */

// 1. DEFINIÇÃO DE CATEGORIAS
$categorias_suporte = [
    'Bug/Erro Técnico',
    'Problema no Perfil',
    'Dúvida de Uso',
    'Denúncia',
    'Sugestão',
    'Financeiro/Premium',
    'Outros'
];
?>

<div class="suporte-form-wrapper">
    <h3 style="margin-bottom: 20px; color: #333; font-weight: 800;">Abrir Novo Chamado</h3>
    
    <form id="form-novo-chamado" enctype="multipart/form-data">
        <div class="form-group" style="margin-bottom: 15px;">
            <label for="suporte_assunto" style="display: block; margin-bottom: 5px; font-weight: 500; color: #606770;">Assunto Curto</label>
            <input type="text" id="suporte_assunto" name="assunto" placeholder="Ex: Erro ao carregar foto no feed" 
                   style="width: 100%; padding: 12px; border: 1px solid #dddfe2; border-radius: 6px; font-size: 1rem;" required>
        </div>

        <div class="form-group" style="margin-bottom: 15px;">
            <label for="suporte_categoria" style="display: block; margin-bottom: 5px; font-weight: 500; color: #606770;">Categoria</label>
            <select id="suporte_categoria" name="categoria" 
                    style="width: 100%; padding: 12px; border: 1px solid #dddfe2; border-radius: 6px; font-size: 1rem; background: #fff;">
                <?php foreach ($categorias_suporte as $cat): ?>
                    <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 15px;">
            <label for="suporte_mensagem" style="display: block; margin-bottom: 5px; font-weight: 500; color: #606770;">Descrição do Problema (Suporta Ctrl+V para imagens)</label>
            <textarea id="suporte_mensagem" name="mensagem" rows="6" 
                      placeholder="Explique o que aconteceu. Você pode colar um print diretamente aqui com Ctrl+V."
                      style="width: 100%; padding: 12px; border: 1px solid #dddfe2; border-radius: 6px; font-size: 1rem; resize: vertical; font-family: inherit;" required></textarea>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #606770;">Anexar Foto/Screenshot (Opcional)</label>
            <div id="drop-zone" style="border: 2px dashed #dddfe2; padding: 20px; text-align: center; border-radius: 8px; position: relative; background: #f9fafb; transition: 0.2s;">
                <input type="file" name="foto_suporte" id="foto_suporte" accept="image/*" 
                       style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer;">
                
                <div id="preview-area" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100px;">
                    <i class="fas fa-camera" id="preview-icon" style="font-size: 2rem; color: #0C2D54; margin-bottom: 10px;"></i>
                    <p id="preview-text" style="font-size: 0.9rem; color: #606770; margin: 0;">Clique, arraste ou cole (Ctrl+V) uma imagem</p>
                    <div id="thumbnail-container" style="display: none; margin-top: 10px;">
                        <img id="img-preview" src="#" style="max-width: 150px; max-height: 150px; border-radius: 6px; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                        <p id="file-name-label" style="font-size: 0.75rem; color: #28a745; font-weight: bold; margin-top: 5px;"></p>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" name="diag_url" id="diag_url">
        <input type="hidden" name="diag_browser" id="diag_browser">
        <input type="hidden" name="diag_res" id="diag_res">

        <button type="submit" class="primary-btn" id="btn-enviar-chamado" style="width: 100%; font-weight: bold;">
            <i class="fas fa-paper-plane"></i> Enviar Chamado para Análise
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. CAPTURA DE DIAGNÓSTICO
    document.getElementById('diag_url').value = window.location.href;
    document.getElementById('diag_browser').value = navigator.userAgent;
    document.getElementById('diag_res').value = window.screen.width + 'x' + window.screen.height;

    const form = document.getElementById('form-novo-chamado');
    const inputFoto = document.getElementById('foto_suporte');
    const msgTextArea = document.getElementById('suporte_mensagem');
    const imgPreview = document.getElementById('img-preview');
    const thumbContainer = document.getElementById('thumbnail-container');
    const previewIcon = document.getElementById('preview-icon');
    const previewText = document.getElementById('preview-text');
    const fileNameLabel = document.getElementById('file-name-label');

    /**
     * FUNÇÃO: Exibir Miniatura
     */
    function handleFilePreview(file) {
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imgPreview.src = e.target.result;
                thumbContainer.style.display = 'block';
                previewIcon.style.display = 'none';
                previewText.style.display = 'none';
                fileNameLabel.textContent = '✓ ' + (file.name || 'Imagem Colada');
            }
            reader.readAsDataURL(file);
        }
    }

    // 2. LISTENER: Upload Manual
    inputFoto.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            handleFilePreview(this.files[0]);
        }
    });

    // 3. LISTENER: Colar Imagem (Ctrl+V)
    msgTextArea.addEventListener('paste', function(e) {
        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
        for (let index in items) {
            const item = items[index];
            if (item.kind === 'file' && item.type.startsWith('image/')) {
                const blob = item.getAsFile();
                
                // Cria um objeto File para o input
                const file = new File([blob], "pasted_image_" + Date.now() + ".png", { type: blob.type });
                
                // Injeta o ficheiro no input real usando DataTransfer
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                inputFoto.files = dataTransfer.files;

                handleFilePreview(file);
            }
        }
    });

    // 4. ENVIO ASSÍNCRONO
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-enviar-chamado');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

        const formData = new FormData(this);

        fetch('<?php echo $config['base_path']; ?>api/suporte/acao_chamado.php?acao=criar', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '<?php echo $config['base_path']; ?>suporte/ver/' + data.chamado_id;
            } else {
                alert('Erro: ' + data.message);
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro crítico de comunicação.');
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });
});
</script>