<?php
/**
 * views/perfil/acoes_relacionamento.php
 * Componente: Ações de Relacionamento e Gestão.
 * PAPEL: Exibir botões de amizade, mensagem ou configuração de conta com integridade de dados.
 * VERSÃO: V60.7 - FIX: Amizade ID & CSRF Readiness (socialbr.lol)
 */

// Variáveis garantidas pelo orquestrador perfil.php:
// $is_own_profile, $is_logged_in, $id_do_perfil_a_exibir, $status_amizade, $sao_amigos, $amizade_id, $id_remetente_pedido, $id_usuario_logado
?>

<div class="profile-header-actions">

    <?php if ($is_own_profile): ?>
        <a href="<?php echo $config['base_path']; ?>configurar_perfil" class="action-btn-edit">
            <i class="fas fa-edit"></i> Editar Perfil
        </a>

    <?php elseif ($is_logged_in): ?>
        <div class="friendship-wrapper">
            
            <?php if ($sao_amigos): ?>
                <div class="friend-actions-dropdown" id="dropdown-amizade-container">
                    <button class="action-btn-friends" id="btn-toggle-amigos" type="button">
                        <i class="fas fa-user-check"></i> Amigos <i class="fas fa-caret-down"></i>
                    </button>
                    <div class="dropdown-content">
                        <a href="javascript:void(0);" 
                           class="cancelar-amizade-btn" 
                           data-amizade-id="<?php echo (int)$amizade_id; ?>">
                            <i class="fas fa-user-times"></i> Desfazer Amizade
                        </a>
                        <a href="javascript:void(0);" 
                           class="bloquear-usuario-btn" 
                           data-usuario-id="<?php echo (int)$id_do_perfil_a_exibir; ?>" 
                           data-acao="bloquear">
                            <i class="fas fa-user-slash"></i> Bloquear
                        </a>
                    </div>
                </div>

            <?php elseif ($status_amizade === 'pendente'): ?>
                <?php if ($id_remetente_pedido == $id_usuario_logado): ?>
                    <button class="action-btn-pending cancelar-pedido-btn" 
                            data-amizade-id="<?php echo (int)$amizade_id; ?>"
                            title="Clique para cancelar o pedido enviado">
                        <i class="fas fa-user-clock"></i> Cancelar Pedido
                    </button>
                <?php else: ?>
                    <div style="display: flex; gap: 5px;">
                        <button class="action-btn-add aceitar-pedido-btn" 
                                data-amizade-id="<?php echo (int)$amizade_id; ?>" 
                                type="button">
                            <i class="fas fa-user-plus"></i> Aceitar
                        </button>
                        <button class="action-btn-edit recusar-pedido-btn" 
                                data-amizade-id="<?php echo (int)$amizade_id; ?>" 
                                type="button">
                            <i class="fas fa-times"></i> Recusar
                        </button>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <button id="add-friend-btn" 
                        class="action-btn-add" 
                        data-destinatario-id="<?php echo (int)$id_do_perfil_a_exibir; ?>" 
                        type="button">
                    <i class="fas fa-user-plus"></i> Adicionar Amigo
                </button>
            <?php endif; ?>
        </div>

        <a href="<?php echo $config['base_path']; ?>api/chat/iniciar_conversa.php?usuario_id=<?php echo (int)$id_do_perfil_a_exibir; ?>" class="action-btn-message">
            <i class="fas fa-envelope"></i> Mensagem
        </a>

    <?php endif; ?>
</div>