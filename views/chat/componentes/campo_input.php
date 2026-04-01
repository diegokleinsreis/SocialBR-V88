<?php
/**
 * views/chat/componentes/campo_input.php
 * Sub-componente Atómico: Formulário de Envio e Ferramentas.
 * PAPEL: Gerir a entrada de texto, anexos de média e gravação de áudio.
 * VERSÃO: V67.4 (Fix de Conflito de Nomes & Preview Gap-Zero - socialbr.lol)
 */

// 1. Proteção de contexto e Variáveis Globais
if (!isset($conversa)) exit;

if (!isset($cor_padrao)) {
    $cor_padrao = "#0C2D54"; 
}

$user_token = $_SESSION['token'] ?? '';
?>

<footer class="chat-footer">
    <div id="chat-media-preview-container" class="is-hidden">
        <div class="sb-preview-wrapper">
            <div id="media-preview-content">
                </div>
            <button type="button" class="sb-remove-preview" onclick="chatMidia.cancelUpload()" title="Remover anexo">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <form id="chat-send-form" enctype="multipart/form-data" onsubmit="return false;">
        <input type="hidden" name="conversa_id" value="<?php echo (int)($conversa['id'] ?? 0); ?>">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($user_token); ?>">
        
        <input type="file" id="chat-attach-photo" name="midia_foto" accept="image/*" class="is-hidden" onchange="chatMidia.previewUpload('foto')">
        <input type="file" id="chat-attach-video" name="midia_video" accept="video/*" class="is-hidden" onchange="chatMidia.previewUpload('video')">
        
        <input type="file" id="chat-attach-audio" name="midia_audio" accept="audio/*" capture class="is-hidden" onchange="chatMidia.previewUpload('audio')">

        <div class="chat-input-wrapper">
            
            <div class="chat-input-tools">
                <button type="button" class="tool-btn" onclick="document.getElementById('chat-attach-photo').click()" title="Enviar Foto">
                    <i class="fas fa-camera"></i>
                </button>
                <button type="button" class="tool-btn" onclick="document.getElementById('chat-attach-video').click()" title="Enviar Vídeo">
                    <i class="fas fa-video"></i>
                </button>
                <button type="button" class="tool-btn" id="audio-record-btn" title="Gravar Áudio">
                    <i class="fas fa-microphone"></i>
                </button>
            </div>

            <div class="chat-input-field">
                <textarea name="mensagem" 
                          id="chat-message-input" 
                          placeholder="Escreva uma mensagem..." 
                          rows="1"
                          onkeydown="if(event.keyCode===13 && !event.shiftKey){ event.preventDefault(); chatMotor.enviarMensagem(); }"></textarea>
            </div>

            <button type="button" 
                    id="chat-send-btn-trigger" 
                    class="chat-send-btn" 
                    style="background-color: <?php echo $cor_padrao; ?>; 
                           color: #FFFFFF; 
                           border: none; 
                           width: 42px; 
                           height: 42px; 
                           border-radius: 50%; 
                           display: flex; 
                           align-items: center; 
                           justify-content: center; 
                           box-shadow: 0 4px 12px <?php echo $cor_padrao; ?>44;
                           cursor: pointer;
                           transition: transform 0.2s ease, background-color 0.2s ease;"
                    onclick="chatMotor.enviarMensagem()">
                <i class="fas fa-paper-plane" style="color: #FFFFFF; font-size: 1.1rem;"></i>
            </button>
            
        </div>
    </form>
    
    <div id="upload-preview-bar" class="is-hidden">
        <div class="preview-content">
            <div class="sb-upload-info">
                <i class="fas fa-cloud-upload-alt fa-spin"></i>
                <span id="preview-filename">A enviar ficheiro...</span>
                <span id="upload-percentage">0%</span>
            </div>
            <div class="preview-progress">
                <div id="upload-progress-bar" class="progress-fill" style="width: 0%; background-color: <?php echo $cor_padrao; ?>;"></div>
            </div>
        </div>
    </div>
</footer>

<style>
/* 1. CONTAINER DE PREVIEW (SANEAMENTO VISUAL) */
#chat-media-preview-container {
    padding: 12px 15px;
    background: #f8fafc;
    border-top: 1px solid var(--chat-border, #e5e7eb);
    display: flex;
    justify-content: flex-start;
    align-items: center;
}

#chat-media-preview-container.is-hidden { display: none !important; }

.sb-preview-wrapper {
    position: relative;
    width: 100px;
    height: 100px;
    border-radius: 12px;
    background: #000;
    border: 2px solid #ffffff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    overflow: hidden;
}

/* FIX DEFINITIVO: Morte ao espaço em branco (Gap-Zero) */
#media-preview-content {
    width: 100%; height: 100%;
    line-height: 0; font-size: 0;
    display: block;
}

#media-preview-content img, 
#media-preview-content video {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block; /* Remove respiro de fonte */
    margin: 0; padding: 0;
}

.sb-remove-preview {
    position: absolute;
    top: 5px; right: 5px;
    background: rgba(0,0,0,0.7);
    color: #ffffff;
    border: none; width: 22px; height: 22px;
    border-radius: 50%; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.65rem; z-index: 10;
}

/* 2. BARRA DE PROGRESSO */
#upload-preview-bar { padding: 10px 20px; background: #ffffff; border-top: 1px solid var(--chat-border, #e5e7eb); }
#upload-preview-bar.is-hidden { display: none !important; }
.sb-upload-info { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; font-size: 0.8rem; font-weight: 700; color: #0C2D54; }
#upload-percentage { margin-left: auto; }
.preview-progress { height: 6px; background: #f1f5f9; border-radius: 10px; overflow: hidden; }
.progress-fill { height: 100%; transition: width 0.3s ease; }

@media (max-width: 768px) {
    #chat-media-preview-container { padding: 8px; }
    .sb-preview-wrapper { width: 85px; height: 85px; }
}
</style>