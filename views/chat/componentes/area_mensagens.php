<?php
/**
 * views/chat/componentes/area_mensagens.php
 * Sub-componente Atómico: Contentor de Histórico de Mensagens.
 * PAPEL: Renderizar mensagens e gerir estados de bloqueio/carregamento.
 * VERSÃO: V53.2 (Estilização Premium de Bloqueio - socialbr.lol)
 */

// Proteção de acesso direto: O componente depende do contexto do orquestrador
if (!isset($estou_bloqueado)) exit;
?>

<div class="chat-messages-area" id="chat-messages-scroll" style="position: relative; flex: 1; overflow-y: auto; background-color: rgba(0,0,0,0.02);">
    
    <?php if ($estou_bloqueado): ?>
        <div class="chat-alert-warning" style="background: #fff9db; border: 1px solid #ffe066; border-radius: 12px; padding: 20px; margin: 25px auto; max-width: 85%; display: flex; align-items: center; gap: 18px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); animation: fadeIn 0.4s ease-out;">
            
            <div class="alert-icon" style="background: #fcc419; color: #fff; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; box-shadow: 0 3px 8px rgba(252, 196, 25, 0.3);">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            
            <div class="alert-text">
                <p style="margin: 0; font-weight: 700; color: #856404; font-size: 0.95rem; letter-spacing: -0.01em;">
                    Não podes enviar nem receber mensagens deste utilizador no momento.
                </p>
                <span style="display: block; margin-top: 5px; color: #92700e; font-size: 0.82rem; line-height: 1.5; font-weight: 500; opacity: 0.9;">
                    Isto pode dever-se a um bloqueio mútuo ou restrições de privacidade.
                </span>
            </div>
        </div>

        <style>
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }
        </style>
    <?php endif; ?>
    
    <div id="messages-loader" class="chat-loader is-hidden" style="padding: 20px; text-align: center; color: #0C2D54; font-weight: 600; font-size: 0.85rem;">
        <i class="fas fa-circle-notch fa-spin" style="margin-right: 8px;"></i>
        <span>A carregar mensagens...</span>
    </div>

    <div id="messages-container" class="messages-flow-wrapper" style="display: flex; flex-direction: column; padding: 10px 0;"></div>

    <div id="chat-anchor" style="height: 1px; width: 100%; pointer-events: none;"></div>
</div>