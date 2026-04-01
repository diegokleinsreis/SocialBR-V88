/**
 * assets/js/chat_midia.js
 * Componente: Gestor de Captura e Upload de Mídias.
 * PAPEL: Gerenciar Previews de arquivos, Barra de Progresso e Gravação de Áudio.
 * VERSÃO: V67.3 (Fix: Audio Identity & Recording Feedback - socialbr.lol)
 */

const chatMidia = {
    gravador: null,
    pedacosAudio: [],
    isGravando: false,
    arquivoSelecionado: null,
    tipoSelecionado: null, // 'foto', 'video' ou 'audio'
    blobUrlAtivo: null,

    // Elementos de UI
    ui: {
        previewContainer: null,
        previewContent: null,
        progressContainer: null,
        progressBar: null,
        progressText: null
    },

    /**
     * Inicializa os listeners e cache de elementos.
     */
    init: function() {
        console.log("📷 Gestor de Mídias V67.3 iniciado...");
        this.refreshUIElements();

        // Vincula o botão de áudio (usando delegação ou re-vinculação)
        const btnAudio = document.getElementById('audio-record-btn');
        if (btnAudio) {
            // Removemos listeners antigos clonando o botão para evitar execuções duplas
            const novoBtn = btnAudio.cloneNode(true);
            btnAudio.parentNode.replaceChild(novoBtn, btnAudio);
            novoBtn.addEventListener('click', () => this.alternarGravacao());
        }
    },

    /**
     * Re-escaneia o DOM para encontrar os elementos injetados via AJAX.
     * VITAL para o funcionamento em SPA (Single Page Application).
     */
    refreshUIElements: function() {
        this.ui.previewContainer = document.getElementById('chat-media-preview-container');
        this.ui.previewContent = document.getElementById('media-preview-content');
        this.ui.progressContainer = document.getElementById('upload-preview-bar');
        this.ui.progressBar = document.getElementById('upload-progress-bar');
        this.ui.progressText = document.getElementById('upload-percentage');
    },

    /**
     * Gera a prévia visual imediata do arquivo selecionado via seletor.
     */
    previewUpload: function(tipo) {
        this.refreshUIElements();

        let inputId = '';
        if (tipo === 'foto') inputId = 'chat-attach-photo';
        else if (tipo === 'video') inputId = 'chat-attach-video';
        else if (tipo === 'audio') inputId = 'chat-attach-audio';

        const input = document.getElementById(inputId);

        if (input && input.files && input.files[0]) {
            this.arquivoSelecionado = input.files[0];
            this.tipoSelecionado = tipo;

            if (this.blobUrlAtivo) URL.revokeObjectURL(this.blobUrlAtivo);
            this.blobUrlAtivo = URL.createObjectURL(this.arquivoSelecionado);

            let htmlPreview = '';
            if (tipo === 'foto') {
                htmlPreview = `<img src="${this.blobUrlAtivo}" alt="Preview">`;
            } else if (tipo === 'video') {
                htmlPreview = `<video src="${this.blobUrlAtivo}" muted autoplay loop></video>`;
            } else if (tipo === 'audio') {
                htmlPreview = `
                    <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; background:#0C2D54; color:#fff; font-size:0.7rem; text-align:center; padding:5px;">
                        <i class="fas fa-microphone" style="font-size:1.2rem; margin-bottom:5px;"></i>
                        <span>Áudio Pronto</span>
                    </div>`;
            }

            if (this.ui.previewContent && this.ui.previewContainer) {
                this.ui.previewContent.innerHTML = htmlPreview;
                this.ui.previewContainer.classList.remove('is-hidden');
            }

            document.getElementById('chat-message-input')?.focus();
        }
    },

    /**
     * Limpa a seleção e reseta a interface.
     */
    cancelUpload: function() {
        this.arquivoSelecionado = null;
        this.tipoSelecionado = null;

        if (this.blobUrlAtivo) {
            URL.revokeObjectURL(this.blobUrlAtivo);
            this.blobUrlAtivo = null;
        }

        ['chat-attach-photo', 'chat-attach-video', 'chat-attach-audio'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = "";
        });

        if (this.ui.previewContainer) this.ui.previewContainer.classList.add('is-hidden');
        if (this.ui.progressContainer) this.ui.progressContainer.classList.add('is-hidden');
        
        this.resetProgressBar();
    },

    resetProgressBar: function() {
        if (this.ui.progressBar) this.ui.progressBar.style.width = '0%';
        if (this.ui.progressText) this.ui.progressText.innerText = '0%';
    },

    /**
     * Executa o upload usando XMLHttpRequest para monitorar o progresso.
     */
    uploadComProgresso: function(formData) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            this.refreshUIElements();

            if (this.ui.previewContainer) this.ui.previewContainer.classList.add('is-hidden');
            if (this.ui.progressContainer) this.ui.progressContainer.classList.remove('is-hidden');

            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percentual = Math.round((e.loaded / e.total) * 100);
                    if (this.ui.progressBar) this.ui.progressBar.style.width = percentual + '%';
                    if (this.ui.progressText) this.ui.progressText.innerText = percentual + '%';
                }
            });

            xhr.onreadystatechange = () => {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            const res = JSON.parse(xhr.responseText);
                            resolve(res);
                        } catch (err) {
                            reject("Erro ao processar resposta do servidor.");
                        }
                    } else {
                        reject("Erro na conexão com o servidor.");
                    }
                    setTimeout(() => this.cancelUpload(), 800);
                }
            };

            const url = (typeof BASE_PATH !== 'undefined') ? BASE_PATH : '/';
            xhr.open('POST', `${url}api/chat/enviar_mensagem.php`, true);
            xhr.send(formData);
        });
    },

    /**
     * Lógica de Áudio Híbrida (Record + Native Fallback)
     * V67.3: Adicionado feedback de permissão.
     */
    alternarGravacao: async function() {
        const btn = document.getElementById('audio-record-btn');
        if (!btn) return;

        if (!this.isGravando) {
            // Feedback visual imediato enquanto solicita permissão
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                this.gravador = new MediaRecorder(stream);
                this.pedacosAudio = [];

                this.gravador.ondataavailable = (e) => this.pedacosAudio.push(e.data);
                
                this.gravador.onstop = () => {
                    const audioBlob = new Blob(this.pedacosAudio, { type: 'audio/webm' });
                    this.enviarMidiaDireta(audioBlob, 'audio');
                    stream.getTracks().forEach(track => track.stop());
                };

                this.gravador.start();
                this.isGravando = true;
                btn.classList.add('recording-active'); 
                btn.innerHTML = '<i class="fas fa-stop"></i>';
                btn.style.color = '#ef4444';
            } catch (err) {
                console.warn("MediaRecorder negado/não suportado. Ativando Plano de Fuga...");
                btn.innerHTML = '<i class="fas fa-microphone"></i>';
                btn.style.color = '';
                
                alert("Para gravar áudios, você precisa permitir o acesso ao microfone no seu navegador.");
                
                // PLANO DE FUGA: Abre o gravador nativo do sistema (Android/iOS)
                const inputAudio = document.getElementById('chat-attach-audio');
                if (inputAudio) inputAudio.click();
            }
        } else {
            if (this.gravador) this.gravador.stop();
            this.isGravando = false;
            btn.classList.remove('recording-active');
            btn.innerHTML = '<i class="fas fa-microphone"></i>';
            btn.style.color = '';
        }
    },

    /**
     * Envio imediato para áudios gravados ou capturados.
     * V67.3: Alterado para 'midia_audio' para sincronia com API V67.5.
     */
    enviarMidiaDireta: async function(blob, tipo) {
        const conversaId = (typeof chatMotor !== 'undefined') ? chatMotor.conversaAtivaId : null;
        const token = document.querySelector('input[name="token"]')?.value;
        if (!conversaId || !token) {
            console.error("Contexto de conversa ou token ausente para envio direto.");
            return;
        }

        const formData = new FormData();
        // [Sincronia V67.3] Usamos o campo específico midia_audio para forçar o tipo na API
        const fieldName = (tipo === 'audio') ? 'midia_audio' : 'midia';
        formData.append(fieldName, blob, (tipo === 'audio' ? 'audio_msg.webm' : 'midia_msg'));
        
        formData.append('conversa_id', conversaId);
        formData.append('tipo_midia', tipo);
        formData.append('token', token);

        try {
            const res = await this.uploadComProgresso(formData);
            if (res.sucesso) {
                if (typeof chatMotor !== 'undefined') chatMotor.buscarNovasMensagens();
            } else {
                alert(res.erro || "Erro no envio do áudio.");
            }
        } catch (error) {
            console.error("Falha técnica no envio direto:", error);
        }
    }
};

// Inicialização automática protegida
document.addEventListener('DOMContentLoaded', () => chatMidia.init());