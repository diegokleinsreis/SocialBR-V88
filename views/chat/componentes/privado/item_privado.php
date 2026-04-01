<?php
/**
 * views/chat/componentes/privado/item_privado.php
 * Sub-componente Especialista: Item de conversa privada na sidebar.
 * PAPEL: Renderizar a linha de um contacto individual com status e preview.
 * VERSÃO: V61.0 (Arquitetura Modular - socialbr.lol)
 */

// 1. Processamento de Identidade
$nome_exibicao = $conv['outro_usuario_nome'] . ' ' . $conv['outro_usuario_sobrenome'];
$avatar_exibicao = !empty($conv['outro_usuario_avatar']) 
    ? $config['base_path'] . $conv['outro_usuario_avatar'] 
    : $config['base_path'] . 'assets/images/default-avatar.png';

// 2. Prévia inteligente com Ícones de Mídia e Recibo de Leitura
$previa_msg = "Inicie uma conversa...";
$visto_icon = "";

if ($conv['ultima_msg_data']) {
    // Se a última mensagem foi minha, mostra o ícone de visto
    if ((int)$conv['ultima_msg_remetente'] === (int)$user_id_logado) {
        $visto_icon = '<i class="fas fa-check-double" style="font-size: 0.7rem; margin-right: 3px; opacity: 0.6;"></i> ';
    }

    if ($conv['ultima_msg_tipo'] === 'foto') {
        $previa_msg = "📷 Foto";
    } elseif ($conv['ultima_msg_tipo'] === 'video') {
        $previa_msg = "🎥 Vídeo";
    } elseif ($conv['ultima_msg_tipo'] === 'audio') {
        $previa_msg = "🎤 Áudio";
    } else {
        $previa_msg = mb_strimwidth($conv['ultima_msg_texto'], 0, 35, "... ");
    }
}

// 3. Classes Dinâmicas e Estados
$is_active = (isset($_GET['id']) && (int)$_GET['id'] === (int)$conv['conversa_id']) ? 'is-active' : '';
$has_unread = ($conv['unread_count'] > 0) ? 'has-unread' : '';
$is_pinned = ($conv['fixada'] ?? 0) ? 'is-pinned' : '';
?>

<div class="chat-item <?php echo $is_active; ?> <?php echo $has_unread; ?> <?php echo $is_pinned; ?>" 
     data-conversa-id="<?php echo $conv['conversa_id']; ?>"
     onclick="chatMotor.trocarConversa(<?php echo $conv['conversa_id']; ?>)">
    
    <div class="chat-item-avatar">
        <img src="<?php echo $avatar_exibicao; ?>" alt="Avatar de <?php echo htmlspecialchars($nome_exibicao); ?>">
        
        <?php if ($conv['is_online'] ?? false): ?>
            <span class="status-indicator online"></span>
        <?php endif; ?>
    </div>

    <div class="chat-item-info">
        <div class="chat-item-header">
            <span class="chat-item-name"><?php echo htmlspecialchars($nome_exibicao); ?></span>
            <span class="chat-item-time">
                <?php echo $conv['ultima_msg_data'] ? date('H:i', strtotime($conv['ultima_msg_data'])) : ''; ?>
            </span>
        </div>
        
        <div class="chat-item-footer">
            <span class="chat-item-preview">
                <?php echo $visto_icon . htmlspecialchars($previa_msg); ?>
            </span>
            
            <div class="chat-item-badges">
                <?php if ($conv['silenciada'] ?? 0): ?>
                    <i class="fas fa-bell-slash icon-muted" title="Silenciada"></i>
                <?php endif; ?>

                <?php if ($conv['fixada'] ?? 0): ?>
                    <i class="fas fa-thumbtack icon-pinned" title="Fixada"></i>
                <?php endif; ?>

                <?php if ($conv['unread_count'] > 0): ?>
                    <span class="unread-badge"><?php echo $conv['unread_count']; ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>