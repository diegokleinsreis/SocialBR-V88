<?php
/**
 * views/chat/componentes/modal_iniciador_chat.php
 * Componente: Modal Iniciador de Conversas (Privadas e Grupos).
 * PAPEL: Orquestrador Principal da Interface de Início de Chat com Trava de Segurança.
 * VERSÃO: V61.1 - Correção de Inclusão & Nomenclatura "Confirmado" (socialbr.lol)
 */

if (session_status() == PHP_SESSION_NONE) { session_start(); }

// Proteção de Acesso Direto
if (!isset($_SESSION['user_id'])) { exit("Acesso negado."); }

/**
 * 💡 NOTA ARQUITETURAL: 
 * Não incluímos o database.php aqui pois este arquivo é chamado via AJAX pelo index.php,
 * que já provê a conexão $conn necessária para as consultas abaixo.
 */

// 🔍 Verificação de Status de Confirmação (Sincronizado com a API)
$user_id_logado = $_SESSION['user_id'];
$sql_v = "SELECT email_verificado FROM Usuarios WHERE id = ? LIMIT 1";
$stmt_v = $conn->prepare($sql_v);
$stmt_v->bind_param("i", $user_id_logado);
$stmt_v->execute();
$res_v = $stmt_v->get_result()->fetch_assoc();
$is_confirmado = ($res_v && (int)$res_v['email_verificado'] === 1);
$stmt_v->close();
?>

<style>
    /* 1. ESTRUTURA BASE DO MODAL */
    .sb-modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.7); z-index: 9999;
        display: flex; align-items: center; justify-content: center;
        backdrop-filter: blur(5px);
    }

    .sb-modal-card {
        background: #fff; width: 95%; max-width: 550px; 
        border-radius: 16px; overflow: hidden;
        box-shadow: 0 25px 70px rgba(0,0,0,0.4);
        display: flex; flex-direction: column;
        max-height: 85vh; animation: sbModalFadeIn 0.3s ease-out;
    }

    @keyframes sbModalFadeIn {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .sb-modal-header {
        padding: 22px 25px; border-bottom: 1px solid #f0f2f5;
        display: flex; justify-content: space-between; align-items: center;
    }

    .sb-modal-header h2 { 
        margin: 0; font-size: 1.35rem; font-weight: 800; color: #0c2d54; 
    }

    .sb-btn-close {
        background: #f0f2f5; border: none; font-size: 1.4rem; color: #65676b;
        cursor: pointer; width: 32px; height: 32px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        transition: background 0.2s;
    }

    .sb-btn-close:hover { background: #e4e6eb; color: #050505; }

    .sb-modal-body { flex: 1; overflow-y: auto; padding: 25px; }

    /* --- BANNER DE AVISO DE SEGURANÇA --- */
    .sb-verification-banner {
        background: #fff4e5; border-left: 4px solid #ffa000;
        padding: 15px; border-radius: 8px; margin-bottom: 20px;
        display: flex; gap: 15px; align-items: center;
    }
    .sb-verification-banner i { font-size: 22px; color: #ffa000; }
    .sb-verification-banner p { margin: 0; font-size: 13.5px; color: #663c00; line-height: 1.4; }
    .sb-verification-banner strong { display: block; margin-bottom: 2px; }

    /* 2. FASE 1: SELEÇÃO DE TIPO */
    .sb-type-selector { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

    .sb-type-card {
        border: 2px solid #f0f2f5; border-radius: 12px; padding: 30px 15px;
        text-align: center; cursor: pointer; transition: all 0.25s ease;
        display: flex; flex-direction: column; align-items: center; gap: 15px;
        position: relative;
    }

    .sb-type-card:hover { border-color: #0c2d54; background: #f0f7ff; transform: translateY(-3px); }
    .sb-type-card i { font-size: 2.8rem; color: #0c2d54; }
    .sb-type-card span { font-weight: 700; color: #050505; font-size: 1.05rem; }

    /* Estilo para cartões bloqueados visualmente */
    .sb-type-card.is-locked { opacity: 0.7; border-style: dashed; }
    .sb-type-card.is-locked i { color: #8a8d91; }
    .sb-lock-badge { 
        position: absolute; top: 10px; right: 10px; font-size: 0.8rem; color: #ffa000; 
    }

    /* 3. FASE 2: ESTILOS DE LISTAGEM */
    .sb-selection-view { display: none; } 

    .sb-friends-list { display: flex; flex-direction: column; gap: 8px; min-height: 120px; }
    
    .sb-friend-item {
        display: flex; align-items: center; 
        justify-content: space-between; 
        padding: 12px 15px; border-radius: 10px; cursor: pointer; 
        transition: background 0.2s; border: 1px solid transparent;
    }

    .sb-friend-item:hover { background: #f0f2f5; }
    
    .sb-friend-item-left { display: flex; align-items: center; gap: 14px; flex: 1; }
    
    .sb-friend-avatar { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; background: #eee; }
    .sb-friend-info { flex: 1; }
    .sb-friend-name { font-weight: 600; font-size: 1rem; color: #050505; display: block; }

    .sb-group-check {
        width: 20px; height: 20px; cursor: pointer; accent-color: #0c2d54;
        margin-left: 15px;
    }

    .sb-search-wrapper { position: relative; margin-bottom: 20px; }
    .sb-search-input {
        width: 100%; padding: 14px 15px 14px 45px; border-radius: 25px;
        border: 1px solid #e4e6eb; background: #f7f8fa; font-size: 0.95rem;
    }
    .sb-search-wrapper i { position: absolute; left: 18px; top: 16px; color: #8a8d91; }

    .sb-group-config { margin-bottom: 20px; display: block; } 
    .sb-input-group-name {
        width: 100%; padding: 15px; border-radius: 10px;
        border: 2px solid #f0f2f5; margin-bottom: 5px; font-weight: 700;
        font-size: 1.1rem; color: #0c2d54;
    }
    .sb-input-group-name:focus { border-color: #0c2d54; outline: none; background: #f0f7ff; }

    /* 4. RODAPÉ */
    .sb-modal-footer {
        padding: 18px 25px; border-top: 1px solid #f0f2f5;
        display: flex; justify-content: flex-end; gap: 12px; background: #fcfcfc;
    }

    .sb-btn-primary {
        background: #0c2d54; color: white; border: none; padding: 12px 24px;
        border-radius: 8px; font-weight: 700; cursor: pointer; transition: opacity 0.2s;
    }
    .sb-btn-primary:hover { opacity: 0.9; }

    .sb-btn-secondary {
        background: #e4e6eb; color: #050505; border: none; padding: 12px 24px;
        border-radius: 8px; font-weight: 700; cursor: pointer;
    }
    .sb-btn-secondary:hover { background: #d8dadf; }

    .is-hidden { display: none !important; }
</style>

<div class="sb-modal-overlay" id="sb-chat-initiator-overlay">
    <div class="sb-modal-card">
        
        <div class="sb-modal-header">
            <h2 id="sb-modal-title">Nova Conversa</h2>
            <button class="sb-btn-close" id="sb-close-initiator" title="Fechar">&times;</button>
        </div>

        <div class="sb-modal-body">
            
            <?php if (!$is_confirmado): ?>
                <div class="sb-verification-banner">
                    <i class="fas fa-shield-alt"></i>
                    <p>
                        <strong>Identidade Pendente</strong>
                        Confirme o seu e-mail nas configurações para poder iniciar novas conversas e criar grupos.
                    </p>
                </div>
            <?php endif; ?>

            <div class="sb-type-selector" id="sb-step-1">
                <div class="sb-type-card <?php echo !$is_confirmado ? 'is-locked' : ''; ?>" data-type="privada">
                    <?php if (!$is_confirmado): ?><i class="fas fa-lock sb-lock-badge"></i><?php endif; ?>
                    <i class="fas fa-user-circle"></i>
                    <span>Conversa Privada</span>
                </div>
                <div class="sb-type-card <?php echo !$is_confirmado ? 'is-locked' : ''; ?>" data-type="grupo">
                    <?php if (!$is_confirmado): ?><i class="fas fa-lock sb-lock-badge"></i><?php endif; ?>
                    <i class="fas fa-users"></i>
                    <span>Criar Grupo</span>
                </div>
            </div>

            <div class="sb-selection-view" id="sb-step-2">
                
                <div id="sb-privado-ui-container" class="is-hidden">
                    <?php include __DIR__ . '/sub_iniciador_privado.php'; ?>
                </div>

                <div id="sb-grupo-ui-container" class="is-hidden">
                    <?php include __DIR__ . '/sub_iniciador_grupo.php'; ?>
                </div>

            </div>

        </div>

        <div class="sb-modal-footer is-hidden" id="sb-modal-footer">
            <button class="sb-btn-secondary" id="sb-btn-back">Voltar</button>
            <button class="sb-btn-primary is-hidden" id="sb-btn-create-group">Criar Grupo</button>
        </div>

    </div>
</div>