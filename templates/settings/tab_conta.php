<?php
/**
 * Sub-view: Aba de Conta (E-mail, Usuário e Senha)
 * VERSÃO: 3.1 - Trust Lock Aware & Termo "Confirmado" (socialbr.lol)
 * PAPEL: Gestão de dados sensíveis com alerta de reset de confirmação.
 */

// Verificamos o status de confirmação vindo do $user_data (carregado via UserLogic)
$is_confirmado = (int)($user_data['email_verificado'] ?? 0);
?>

<style>
    /* Estilização Premium para a Caixa de Aviso */
    .verification-notice {
        background: #f8f9fa;
        border: 1px solid #e4e6eb;
        border-left: 5px solid #0C2D54; /* Cor Oficial */
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        display: flex;
        gap: 20px;
        align-items: center;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
    }

    .notice-icon {
        font-size: 32px;
        color: #0C2D54;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(12, 45, 84, 0.05);
        width: 60px;
        height: 60px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .notice-content p {
        margin: 0 0 12px 0;
        font-size: 14.5px;
        color: #4b4f56;
        line-height: 1.5;
    }

    .notice-content strong {
        color: #0C2D54;
        font-weight: 700;
    }

    /* Botão de Reenvio Estilizado */
    .secondary-btn-tiny {
        background-color: #0C2D54;
        color: #ffffff !important;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        box-shadow: 0 4px 6px rgba(12, 45, 84, 0.15);
    }

    .secondary-btn-tiny:hover {
        background-color: #08213d;
        transform: translateY(-1px);
        box-shadow: 0 6px 12px rgba(12, 45, 84, 0.25);
    }

    .secondary-btn-tiny:disabled {
        background-color: #bdc3c7;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* Ajuste para inputs com ícones internos */
    .input-with-status {
        position: relative;
        display: flex;
        align-items: center;
    }

    .icon-inside.success {
        position: absolute;
        right: 12px;
        color: #2ecc71;
        font-size: 1.1rem;
    }

    /* NOVO: Alerta de Alteração de E-mail */
    .email-change-warning {
        display: block;
        margin-top: 8px;
        padding: 8px 12px;
        background-color: #fff4e5;
        border-radius: 6px;
        border-left: 3px solid #ffa000;
        font-size: 12px;
        color: #663c00;
        font-weight: 500;
    }

    .email-change-warning i {
        margin-right: 5px;
    }

    @media (max-width: 600px) {
        .verification-notice {
            flex-direction: column;
            text-align: center;
            gap: 15px;
        }
        .notice-icon { margin: 0 auto; }
        .secondary-btn-tiny { width: 100%; justify-content: center; }
    }
</style>

<div class="settings-card">
    <div class="settings-card-header">
        <h2><i class="fas fa-cog"></i> Configuração da Conta</h2>
        
        <?php if ($is_confirmado === 1): ?>
            <span class="status-badge verified"><i class="fas fa-check-circle"></i> E-mail Confirmado</span>
        <?php else: ?>
            <span class="status-badge unverified"><i class="fas fa-exclamation-triangle"></i> Confirmação Pendente</span>
        <?php endif; ?>
    </div>

    <?php if ($is_confirmado === 0): ?>
        <div class="verification-notice">
            <div class="notice-icon">
                <i class="fas fa-envelope-open-text"></i>
            </div>
            <div class="notice-content">
                <p><strong>Proteja sua conta!</strong> O seu e-mail ainda não foi confirmado. Confirme agora para liberar todos os recursos do Marketplace e aumentar sua segurança.</p>
                <button type="button" id="btn-reenviar-verificacao" class="secondary-btn-tiny">
                    <i class="fas fa-paper-plane"></i> Reenviar E-mail de Confirmação
                </button>
            </div>
        </div>
    <?php endif; ?>
    
    <form id="form-conta" action="<?php echo $config['base_path']; ?>api/usuarios/atualizar_conta.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

        <div class="form-group">
            <label for="email">E-mail</label>
            <div class="input-with-status">
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                <?php if ($is_confirmado === 1): ?>
                    <i class="fas fa-check-circle icon-inside success" title="E-mail Confirmado"></i>
                <?php endif; ?>
            </div>
            
            <p class="email-change-warning">
                <i class="fas fa-info-circle"></i> 
                <strong>Nota:</strong> A alteração do e-mail exigirá uma nova confirmação para manter o acesso total aos recursos da rede.
            </p>
            
            <p class="form-group-description">Este e-mail é utilizado para login e recuperação de conta.</p>
        </div>

        <div class="form-group">
            <label for="nome_de_usuario">Nome de Usuário (@)</label>
            <input type="text" id="nome_de_usuario" name="nome_de_usuario" value="<?php echo htmlspecialchars($user_data['nome_de_usuario']); ?>" required>
            <p class="form-group-description">O seu identificador único na rede social.</p>
        </div>

        <hr>

        <p class="form-section-title" style="font-weight: 700; margin-bottom: 15px; color: #0C2D54;">
            <i class="fas fa-key"></i> Alterar Senha
        </p>
        
        <div class="form-group">
            <label for="senha_atual">Senha Atual</label>
            <input type="password" id="senha_atual" name="senha_atual" placeholder="Digite sua senha atual">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="nova_senha">Nova Senha</label>
                <input type="password" id="nova_senha" name="nova_senha" placeholder="Mínimo de 6 caracteres">
            </div>
            <div class="form-group">
                <label for="confirmar_nova_senha">Confirmar Nova Senha</label>
                <input type="password" id="confirmar_nova_senha" name="confirmar_nova_senha" placeholder="Repita a nova senha">
            </div>
        </div>

        <div class="form-actions-right">
            <button type="submit" class="primary-btn-small">Salvar Alterações da Conta</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnReenviar = document.getElementById('btn-reenviar-verificacao');
    
    if (btnReenviar) {
        btnReenviar.addEventListener('click', async function() {
            const originalHTML = btnReenviar.innerHTML;
            btnReenviar.disabled = true;
            btnReenviar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

            try {
                const response = await fetch('<?php echo $config['base_path']; ?>api/usuarios/reenviar_verificacao.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });

                const data = await response.json();

                if (data.success) {
                    if(typeof MotorDeAlertas !== 'undefined') {
                        MotorDeAlertas.exibir({
                            titulo: "E-mail Enviado!",
                            mensagem: data.message,
                            tipo: "success",
                            cor: "#0C2D54"
                        });
                    }
                } else {
                    if(typeof MotorDeAlertas !== 'undefined') {
                        MotorDeAlertas.exibir({
                            titulo: "Aviso",
                            mensagem: data.message,
                            tipo: "warning"
                        });
                    }
                }
            } catch (error) {
                console.error('Erro no reenvio:', error);
                if(typeof MotorDeAlertas !== 'undefined') {
                    MotorDeAlertas.exibir({
                        titulo: "Erro Crítico",
                        mensagem: "Não foi possível conectar ao servidor de e-mail.",
                        tipo: "error"
                    });
                }
            } finally {
                btnReenviar.disabled = false;
                btnReenviar.innerHTML = originalHTML;
            }
        });
    }
});
</script>