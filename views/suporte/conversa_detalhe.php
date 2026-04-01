<?php
/**
 * views/suporte/conversa_detalhe.php
 * COMPONENTE: Interface de Chat SPA (V3.1)
 * PAPEL: Chat imersivo com suporte a Lightbox e Otimização de Imagens.
 * AJUSTE: Limitação de tamanho de fotos e integração com chat_lightbox.js.
 * VERSÃO: 3.1 - socialbr.lol
 */

// 1. BUSCA DADOS DO CHAMADO E MENSAGENS
$chamado = SuporteLogic::getDetalhesChamado($conn, $chamado_id);

if (!$chamado || ($chamado['user_id'] != $user_id && $_SESSION['user_role'] !== 'admin')) {
    echo "<div style='padding: 20px;' class='error-message'>Acesso negado.</div>";
    return;
}

$mensagens = SuporteLogic::getMensagensChamado($conn, $chamado_id);
$diagnostico = !empty($chamado['diagnostico_json']) ? json_decode($chamado['diagnostico_json'], true) : null;
?>

<div class="suporte-conversa-wrapper" style="display: flex; flex-direction: column; height: 100%; overflow: hidden;">
    
    <div class="ticket-info-header" style="background: #fcfcfc; padding: 12px 20px; border-bottom: 1px solid #eee; flex-shrink: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 0.8rem; background: #eef3ff; color: #0C2D54; padding: 3px 8px; border-radius: 4px; font-weight: 800;">
                    #<?php echo $chamado['id']; ?>
                </span>
                <h2 style="color: #1c1e21; font-size: 0.95rem; margin: 0; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 300px;">
                    <?php echo htmlspecialchars($chamado['assunto']); ?>
                </h2>
                <span style="font-size: 0.65rem; color: #28a745; background: #e8f5e9; padding: 2px 6px; border-radius: 10px; font-weight: bold; text-transform: uppercase;">
                    <?php echo $chamado['status']; ?>
                </span>
            </div>

            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="button" id="btn-refresh-user" title="Sincronizar" style="background: #fff; border: 1px solid #ddd; color: #555; width: 30px; height: 30px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;">
                    <i class="fas fa-sync-alt" id="icon-refresh-user" style="font-size: 0.8rem;"></i>
                </button>
                <span style="font-size: 0.75rem; color: #90949c; font-weight: 500;">
                    <?php echo date('d/m H:i', strtotime($chamado['data_criacao'])); ?>
                </span>
            </div>
        </div>
    </div>

    <div id="user-timeline-box" class="suporte-timeline" style="flex: 1; overflow-y: auto; background: #f0f2f5; padding: 20px; display: flex; flex-direction: column; gap: 12px;">
        
        <?php if ($diagnostico): ?>
            <div style="background: #fff3cd; color: #856404; padding: 10px 15px; border-radius: 8px; font-size: 0.75rem; border: 1px solid #ffeeba; margin-bottom: 10px; flex-shrink: 0;">
                <i class="fas fa-info-circle"></i> O suporte técnico recebeu os dados da página para agilizar a sua solução.
            </div>
        <?php endif; ?>

        <?php foreach ($mensagens as $msg): ?>
            <?php 
                $sou_eu = ($msg['remetente_tipo'] === 'usuario');
                $alinhamento = $sou_eu ? 'flex-end' : 'flex-start';
                $cor_fundo = $sou_eu ? '#0C2D54' : '#ffffff';
                $cor_texto = $sou_eu ? '#ffffff' : '#1c1e21';
                $borda_radius = $sou_eu ? '14px 14px 2px 14px' : '14px 14px 14px 2px';
            ?>
            <div style="display: flex; flex-direction: column; align-items: <?php echo $alinhamento; ?>; max-width: 85%; align-self: <?php echo $alinhamento; ?>; animation: fadeInMsg 0.2s ease;">
                <div style="background: <?php echo $cor_fundo; ?>; color: <?php echo $cor_texto; ?>; padding: 10px 14px; border-radius: <?php echo $borda_radius; ?>; box-shadow: 0 1px 2px rgba(0,0,0,0.08); <?php echo $sou_eu ? '' : 'border: 1px solid #dddfe2;'; ?>">
                    <p style="margin: 0; white-space: pre-wrap; font-size: 0.9rem; line-height: 1.4;"><?php echo htmlspecialchars($msg['mensagem']); ?></p>
                    
                    <?php if (!empty($msg['foto_url'])): ?>
                        <div style="margin-top: 8px;" class="msg-foto">
                            <img src="<?php echo $config['base_path'] . $msg['foto_url']; ?>" 
                                 class="suporte-msg-foto"
                                 style="max-width: 100%; max-height: 200px; width: auto; border-radius: 6px; display: block; cursor: pointer; transition: opacity 0.2s;"
                                 onmouseover="this.style.opacity='0.9'" 
                                 onmouseout="this.style.opacity='1'">
                        </div>
                    <?php endif; ?>
                </div>
                <span style="font-size: 0.65rem; color: #90949c; margin-top: 4px; font-weight: 500; padding: 0 4px;">
                    <?php echo $sou_eu ? 'Enviado' : 'Suporte'; ?> • <?php echo date('H:i', strtotime($msg['data_envio'])); ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="resposta-form-box" style="background: #fff; border-top: 1px solid #dddfe2; padding: 15px 20px; flex-shrink: 0;">
        <?php if ($chamado['status'] !== 'resolvido'): ?>
            <form id="form-resposta-suporte" enctype="multipart/form-data">
                <input type="hidden" name="chamado_id" value="<?php echo $chamado['id']; ?>">
                
                <div style="position: relative; margin-bottom: 12px;">
                    <textarea id="resposta_mensagem" name="mensagem" rows="2" 
                              placeholder="Escreva aqui... (Ctrl+V para colar prints)"
                              style="width: 100%; padding: 12px; border: 1px solid #dddfe2; border-radius: 8px; font-size: 0.9rem; resize: none; font-family: inherit; outline: none; transition: border 0.2s;" required></textarea>
                    
                    <div id="thumb-container-res" style="display: none; position: absolute; bottom: 100%; left: 0; margin-bottom: 10px; align-items: center; gap: 8px; background: #fff; padding: 6px; border-radius: 8px; border: 1px solid #ddd; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                        <img id="img-preview-res" src="#" style="width: 45px; height: 45px; object-fit: cover; border-radius: 4px;">
                        <span style="font-size: 0.7rem; color: #28a745; font-weight: 800;">✓ ANEXADA</span>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="position: relative;">
                        <button type="button" style="background: #f0f2f5; color: #1c1e21; border: 1px solid #dddfe2; padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: bold; cursor: pointer;">
                            <i class="fas fa-paperclip"></i> Anexar Foto
                        </button>
                        <input type="file" name="foto_suporte" id="foto_suporte_res" accept="image/*" 
                               style="position: absolute; left: 0; top: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;">
                    </div>

                    <button type="submit" id="btn-enviar-resposta" style="background: #0C2D54; color: #fff; border: none; padding: 8px 25px; border-radius: 6px; font-weight: bold; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        ENVIAR <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div style="text-align: center; color: #28a745; font-size: 0.85rem; font-weight: 700; padding: 10px;">
                <i class="fas fa-lock"></i> Este ticket está resolvido e fechado.
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
@keyframes fadeInMsg { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
#resposta_mensagem:focus { border-color: #0C2D54 !important; }
#btn-refresh-user:hover { background: #f0f2f5 !important; border-color: #0C2D54; }
#user-timeline-box::-webkit-scrollbar { width: 6px; }
#user-timeline-box::-webkit-scrollbar-track { background: transparent; }
#user-timeline-box::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-resposta-suporte');
    const inputFoto = document.getElementById('foto_suporte_res');
    const msgTextArea = document.getElementById('resposta_mensagem');
    const thumbContainer = document.getElementById('thumb-container-res');
    const imgPreview = document.getElementById('img-preview-res');
    const timelineBox = document.getElementById('user-timeline-box');
    const btnRefresh = document.getElementById('btn-refresh-user');
    const iconRefresh = document.getElementById('icon-refresh-user');
    
    const chamadoId = "<?php echo $chamado['id']; ?>";
    const basePath = "<?php echo $config['base_path']; ?>";

    function scrollToBottom() {
        if (timelineBox) timelineBox.scrollTop = timelineBox.scrollHeight;
    }
    
    scrollToBottom();

    function handleFilePreview(file) {
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = e => { imgPreview.src = e.target.result; thumbContainer.style.display = 'flex'; }
            reader.readAsDataURL(file);
        }
    }

    function syncChat() {
        if (iconRefresh) iconRefresh.classList.add('fa-spin');
        fetch(`${basePath}api/suporte/acao_chamado.php?acao=get_mensagens&chamado_id=${chamadoId}`)
            .then(r => r.json())
            .then(data => {
                if (data.success && timelineBox) {
                    timelineBox.innerHTML = data.html;
                    scrollToBottom();
                }
            })
            .catch(err => console.error(err))
            .finally(() => setTimeout(() => iconRefresh.classList.remove('fa-spin'), 500));
    }

    if (inputFoto) inputFoto.addEventListener('change', e => handleFilePreview(e.target.files[0]));

    if (msgTextArea) msgTextArea.addEventListener('paste', e => {
        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
        for (let item of items) {
            if (item.type.indexOf("image") !== -1) {
                const blob = item.getAsFile();
                const file = new File([blob], "pasted_img.png", {type: blob.type});
                const dt = new DataTransfer(); dt.items.add(file);
                inputFoto.files = dt.files;
                handleFilePreview(file);
            }
        }
    });

    if (btnRefresh) btnRefresh.addEventListener('click', syncChat);

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-enviar-resposta');
            const original = btn.innerHTML;
            btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(`${basePath}api/suporte/acao_chamado.php?acao=responder`, { method: 'POST', body: new FormData(this) })
            .then(r => r.json())
            .then(data => {
                if (data.success) { form.reset(); thumbContainer.style.display = 'none'; syncChat(); }
                else alert(data.message);
            })
            .finally(() => { btn.disabled = false; btn.innerHTML = original; });
        });
    }
});
</script>