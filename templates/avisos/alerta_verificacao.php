<?php
/**
 * TEMPLATE: templates/avisos/alerta_verificacao.php
 * PAPEL: Banner global de aviso para e-mail não confirmado com modal de detalhes.
 * VERSÃO: 3.2 - Transparência de Funcionalidades & SweetAlert2 (socialbr.lol)
 */
?>

<style>
    .alerta-global-verificacao {
        background-color: #ffffff;
        /* Borda e design estilo card do feed */
        border: 1px solid #e4e6eb;
        border-left: 5px solid #0C2D54; 
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        
        padding: 16px 24px;
        /* Centralização e largura alinhada com os posts */
        margin: 20px auto; 
        width: 90%;
        max-width: 1100px; 
        
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        
        /* Garantia de que aparece abaixo do menu fixo */
        position: relative;
        z-index: 99; 
        clear: both;
    }

    .alerta-verificacao-info {
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .alerta-verificacao-icone {
        font-size: 30px;
        color: #0C2D54;
        display: flex;
        align-items: center;
        flex-shrink: 0;
    }

    .alerta-verificacao-texto {
        color: #4b4f56;
        font-size: 14.5px;
        line-height: 1.5;
        margin: 0;
    }

    .alerta-verificacao-texto strong {
        color: #0C2D54;
        font-weight: 700;
    }

    /* Botão de Detalhes (Saiba Mais) */
    .btn-detalhes-alerta {
        color: #0C2D54;
        text-decoration: underline;
        font-weight: 700;
        cursor: pointer;
        margin-left: 4px;
        transition: color 0.2s;
        background: none;
        border: none;
        padding: 0;
        font-size: inherit;
    }

    .btn-detalhes-alerta:hover {
        color: #08213d;
    }

    .btn-reenviar-alerta {
        background-color: #0C2D54;
        color: #ffffff !important;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .btn-reenviar-alerta:hover {
        background-color: #08213d;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .btn-reenviar-alerta:disabled {
        background-color: #ccd0d5;
        color: #8d949e !important;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* Ajuste para telas menores */
    @media (max-width: 850px) {
        .alerta-global-verificacao {
            flex-direction: column;
            text-align: center;
            padding: 20px;
            width: 95%;
        }
        .alerta-verificacao-info {
            flex-direction: column;
            gap: 10px;
        }
        .btn-reenviar-alerta {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="alerta-global-verificacao" id="banner-verificacao-email">
    <div class="alerta-verificacao-info">
        <div class="alerta-verificacao-icone">
            <i class="fas fa-user-shield"></i>
        </div>
        <p class="alerta-verificacao-texto">
            <strong>Proteja a sua conta:</strong> O seu e-mail ainda não foi confirmado. 
            Verifique a sua caixa de entrada para ativar todos os recursos da rede.
            <button type="button" class="btn-detalhes-alerta" id="btn-detalhes-alerta">Ver detalhes</button>
        </p>
    </div>
    
    <button id="btn-reenviar-banner" class="btn-reenviar-alerta">
        <i class="fas fa-paper-plane"></i> Reenviar Link
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnReenviar = document.getElementById('btn-reenviar-banner');
    const btnDetalhes = document.getElementById('btn-detalhes-alerta');

    // 1. LÓGICA DO MODAL DE DETALHES (SweetAlert2)
    if (btnDetalhes) {
        btnDetalhes.addEventListener('click', function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '🛡️ Segurança da Conta',
                    html: `
                        <div style="text-align: left; font-size: 14.5px; line-height: 1.6; color: #4b4f56;">
                            <p>Para manter a <strong>Social BR</strong> protegida contra spam e perfis falsos, algumas ações exigem a confirmação do e-mail:</p>
                            <div style="margin-top: 15px;">
                                <p style="margin-bottom: 10px;"><strong>✅ O que você pode fazer:</strong></p>
                                <ul style="list-style: none; padding: 0; margin-bottom: 20px;">
                                    <li style="margin-bottom: 6px;"><i class="fas fa-check-circle" style="color: #2ecc71; margin-right: 8px;"></i> Publicar textos e pensamentos</li>
                                    <li style="margin-bottom: 6px;"><i class="fas fa-check-circle" style="color: #2ecc71; margin-right: 8px;"></i> Criar e votar em enquetes</li>
                                    <li><i class="fas fa-check-circle" style="color: #2ecc71; margin-right: 8px;"></i> Participar e comentar em grupos</li>
                                </ul>

                                <p style="margin-bottom: 10px;"><strong>🚫 O que está bloqueado:</strong></p>
                                <ul style="list-style: none; padding: 0;">
                                    <li style="margin-bottom: 6px;"><i class="fas fa-times-circle" style="color: #e74c3c; margin-right: 8px;"></i> Publicar fotos e vídeos no feed</li>
                                    <li style="margin-bottom: 6px;"><i class="fas fa-times-circle" style="color: #e74c3c; margin-right: 8px;"></i> Criar novas comunidades (Grupos)</li>
                                    <li><i class="fas fa-times-circle" style="color: #e74c3c; margin-right: 8px;"></i> Criar anúncios no Marketplace</li>
                                </ul>
                            </div>
                        </div>
                    `,
                    confirmButtonColor: '#0C2D54',
                    confirmButtonText: 'Entendido'
                });
            }
        });
    }

    // 2. LÓGICA DE REENVIO DE E-MAIL
    if (!btnReenviar) return;

    let tempoRestante = 0;
    let intervalo;

    const iniciarContador = (segundos) => {
        tempoRestante = segundos;
        btnReenviar.disabled = true;
        
        intervalo = setInterval(() => {
            if (tempoRestante <= 0) {
                clearInterval(intervalo);
                btnReenviar.innerHTML = '<i class="fas fa-paper-plane"></i> Reenviar Link';
                btnReenviar.disabled = false;
                return;
            }
            btnReenviar.innerHTML = `<i class="fas fa-clock"></i> Aguarde ${tempoRestante}s`;
            tempoRestante--;
        }, 1000);
    };

    btnReenviar.addEventListener('click', async function() {
        const originalHTML = btnReenviar.innerHTML;
        btnReenviar.disabled = true;
        btnReenviar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

        try {
            const basePath = document.body.getAttribute('data-base-path') || '/';
            
            const response = await fetch(basePath + 'api/usuarios/reenviar_verificacao.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });

            const data = await response.json();

            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'E-mail enviado!',
                        text: 'Um novo link de verificação foi enviado para o seu endereço de e-mail.',
                        confirmButtonColor: '#0C2D54'
                    });
                }
                iniciarContador(60); 
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Aviso',
                        text: data.message,
                        confirmButtonColor: '#0C2D54'
                    });
                }
                btnReenviar.disabled = false;
                btnReenviar.innerHTML = originalHTML;
            }
        } catch (error) {
            console.error('Erro no reenvio:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro técnico',
                    text: 'Não foi possível contactar o servidor de e-mail.',
                    confirmButtonColor: '#d33'
                });
            }
            btnReenviar.disabled = false;
            btnReenviar.innerHTML = originalHTML;
        }
    });
});
</script>