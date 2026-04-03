<?php
/**
 * templates/modal_interacao.php
 * Estrutura do Modal Lightbox para Comentários e Interações.
 * VERSÃO: V9.1 - Correção de IDs Críticos para JS (socialbr.lol)
 */

if (session_status() == PHP_SESSION_NONE) { session_start(); }

// 🔍 Verificação de Status de Confirmação
$is_confirmado = false;
if (isset($_SESSION['user_id'])) {
    // Nota: Assume-se que $conn (database.php) já foi incluído pelo index.php ou chamador principal.
    $user_id_check = $_SESSION['user_id'];
    $stmt_v = $conn->prepare("SELECT email_verificado FROM Usuarios WHERE id = ? LIMIT 1");
    $stmt_v->bind_param("i", $user_id_check);
    $stmt_v->execute();
    $res_v = $stmt_v->get_result()->fetch_assoc();
    $is_confirmado = ($res_v && (int)$res_v['email_verificado'] === 1);
    $stmt_v->close();
}
?>

<style>
    /* Estilização para o Banner de Bloqueio de Comentário */
    .modal-comment-blocked-container {
        padding: 15px 20px;
        background: #f8f9fa;
        border-top: 1px solid #e4e6eb;
        text-align: center;
    }

    .comment-blocked-banner {
        background: #ffffff;
        border: 1px solid #e4e6eb;
        border-left: 4px solid #0C2D54;
        border-radius: 10px;
        padding: 12px 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    }

    .blocked-banner-info {
        display: flex;
        align-items: center;
        gap: 12px;
        text-align: left;
    }

    .blocked-banner-info i {
        font-size: 20px;
        color: #0C2D54;
    }

    .blocked-banner-info p {
        margin: 0;
        font-size: 13px;
        color: #4b4f56;
        line-height: 1.4;
    }

    .btn-confirmar-agora {
        background-color: #0C2D54;
        color: #ffffff !important;
        border: none;
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
        transition: all 0.2s;
    }

    .btn-confirmar-agora:hover {
        background-color: #08213d;
        transform: translateY(-1px);
    }

    @media (max-width: 600px) {
        .comment-blocked-banner { flex-direction: column; text-align: center; }
        .blocked-banner-info { flex-direction: column; text-align: center; }
        .btn-confirmar-agora { width: 100%; }
    }
</style>

<div id="comment-interaction-modal" class="modal-interaction-overlay is-hidden">
    
    <div class="modal-interaction-container">
        
        <div class="modal-interaction-header">
            <h3>Comentários</h3>
            <button type="button" id="close-comment-modal" class="modal-close-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="modal-interaction-body">
            
            <div class="modal-post-stats-summary">
                <div class="stat-item"><i class="fas fa-thumbs-up"></i> <span id="modal-like-count">0</span></div>
                <div class="stat-item"><i class="fas fa-share"></i> <span id="modal-share-count">0</span></div>
            </div>

            <div id="modal-full-comments-list" class="full-comment-list">
                <div class="modal-loading-placeholder">
                    <i class="fas fa-spinner fa-spin"></i> A carregar interações...
                </div>
            </div>
        </div>

        <div class="modal-interaction-footer">
            <input type="hidden" form="modal-comment-form" name="id_postagem" id="modal-post-id" value="">
            <input type="hidden" form="modal-comment-form" name="id_comentario_pai" id="modal-parent-id" value="">

            <?php if ($is_confirmado): ?>
                <form id="modal-comment-form" class="modal-add-comment-form">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    
                    <div class="modal-input-wrapper">
                        <div id="replying-to-info" class="reply-badge is-hidden">
                            A responder a <strong id="replying-to-name"></strong> 
                            <button type="button" id="cancel-reply-btn" onclick="cancelarResposta()"><i class="fas fa-times"></i></button>
                        </div>
                        
                        <div class="input-actions-flex">
                            <textarea name="conteudo_texto" id="modal-comment-input" 
                                      placeholder="Escreva um comentário..." required></textarea>
                            <button type="submit" class="modal-send-btn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div class="modal-comment-blocked-container">
                    <div class="comment-blocked-banner">
                        <div class="blocked-banner-info">
                            <i class="fas fa-user-shield"></i>
                            <p><strong>Quer participar na conversa?</strong><br>Confirme o seu e-mail para desbloquear os comentários.</p>
                        </div>
                        <a href="<?php echo $config['base_path']; ?>configurar_perfil?tab=conta" class="btn-confirmar-agora">
                            Confirmar E-mail
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>