<?php
/**
 * views/chat/componentes/grupos/item_grupo.php
 * Sub-componente Especialista: Item de conversa de grupo na sidebar.
 * PAPEL: Renderizar a linha de uma comunidade com identidade visual e preview de remetente.
 * VERSÃO: V61.1 (Arquitetura Modular & Estabilização - socialbr.lol)
 */

// 1. Processamento de Identidade
$nome_exibicao = !empty($conv['grupo_titulo']) ? $conv['grupo_titulo'] : 'Grupo Sem Nome';

/**
 * ESTABILIZAÇÃO VISUAL:
 * Resolve o problema da imagem quebrada com Fallback de segurança.
 *
 */
$avatar_exibicao = !empty($conv['grupo_capa']) 
    ? $config['base_path'] . $conv['grupo_capa'] 
    : $config['base_path'] . 'assets/images/default-group.png';

/**
 * 2. PRÉVIA DINÂMICA V61.1:
 * Identifica o remetente da última mensagem para contexto coletivo.
 * Utiliza o JOIN do ChatLogic V61.0.
 */
$previa_msg = "Inicie a conversa no grupo...";
$remetente_label = "";

if ($conv['ultima_msg_data']) {
    // Identifica se a mensagem é do próprio usuário ou de outro membro
    if ((int)$conv['ultima_msg_remetente'] === (int)$user_id_logado) {
        $remetente_label = "Você: ";
    } else {
        // Usa o nome retornado pelo novo JOIN do ChatLogic
        $remetente_label = (!empty($conv['ultima_msg_remetente_nome'])) 
            ? $conv['ultima_msg_remetente_nome'] . ": " 
            : "Membro: ";
    }

    // Tratamento de tipos de mídia
    if ($conv['ultima_msg_tipo'] === 'foto') {
        $msg_corpo = "📷 Foto";
    } elseif ($conv['ultima_msg_tipo'] === 'video') {
        $msg_corpo = "🎥 Vídeo";
    } elseif ($conv['ultima_msg_tipo'] === 'audio') {
        $msg_corpo = "🎤 Áudio";
    } else {
        $msg_corpo = mb_strimwidth($conv['ultima_msg_texto'], 0, 30, "... ");
    }

    $previa_msg = $remetente_label . $msg_corpo;
}

// 3. Classes Dinâmicas e Estados
$is_active = (isset($_GET['id']) && (int)$_GET['id'] === (int)$conv['conversa_id']) ? 'is-active' : '';
$has_unread = ($conv['unread_count'] > 0) ? 'has-unread' : '';
$is_pinned = ($conv['fixada'] ?? 0) ? 'is-pinned' : '';
?>

<div class="chat-item chat-item-group <?php echo $is_active; ?> <?php echo $has_unread; ?> <?php echo $is_pinned; ?>" 
     data-conversa-id="<?php echo $conv['conversa_id']; ?>"
     onclick="chatMotor.trocarConversa(<?php echo $conv['conversa_id']; ?>)">
    
    <div class="chat-item-avatar group-avatar-frame">
        <img src="<?php echo $avatar_exibicao; ?>" alt="Capa do grupo <?php echo htmlspecialchars($nome_exibicao); ?>"
             onerror="this.src='<?php echo $config['base_path']; ?>assets/images/default-group.png';">
    </div>

    <div class="chat-item-info">
        <div class="chat-item-header">
            <span class="chat-item-name">
                <i class="fas fa-users" style="font-size: 0.8rem; opacity: 0.5; margin-right: 4px;"></i>
                <?php echo htmlspecialchars($nome_exibicao); ?>
            </span>
            <span class="chat-item-time">
                <?php echo $conv['ultima_msg_data'] ? date('H:i', strtotime($conv['ultima_msg_data'])) : ''; ?>
            </span>
        </div>
        
        <div class="chat-item-footer">
            <span class="chat-item-preview">
                <?php echo htmlspecialchars($previa_msg); ?>
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