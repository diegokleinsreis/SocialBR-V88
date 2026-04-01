<?php
/**
 * views/chat/componentes/privado/cabecalho_privado.php
 * Sub-componente Especialista: Cabeçalho de Conversa Privada.
 * PAPEL: Exibir dados do amigo, status real e ações de moderação 1x1.
 * VERSÃO: V65.2 (Fix: Botão de Desbloqueio Dinâmico - socialbr.lol)
 */

// Proteção de contexto: Depende de $outro_usuario e $conversa vindos do orquestrador
if (!isset($outro_usuario)) exit;

/**
 * 1. IDENTIDADE VISUAL
 */
$nome_contato = $outro_usuario['nome'] . ' ' . $outro_usuario['sobrenome'];
$avatar_contato = !empty($outro_usuario['foto_perfil_url']) 
    ? $config['base_path'] . $outro_usuario['foto_perfil_url'] 
    : $config['base_path'] . 'assets/images/default-avatar.png';

/**
 * 2. LÓGICA DE STATUS REAL (DNA CheckYou)
 * Sincronizado com klscom_social.sql: Usamos 'ultimo_acesso' para o Heartbeat.
 */
if ($estou_bloqueado) {
    $status_label = "Indisponível";
    $status_class = "status-offline";
} elseif ($is_online) {
    $status_label = "Online agora";
    $status_class = "status-online";
} else {
    $ultima_vez = isset($outro_usuario['ultimo_acesso']) 
        ? date('H:i', strtotime($outro_usuario['ultimo_acesso'])) 
        : '--:--';
    $status_label = "Visto por último às " . $ultima_vez;
    $status_class = "status-offline";
}
?>

<header class="chat-header" style="border-bottom: 2px solid <?php echo $cor_padrao; ?>22;">
    <div class="chat-header-user">
        <button class="mobile-back-btn" onclick="chatMotor.voltarParaLista()" title="Voltar para a lista">
            <i class="fas fa-arrow-left"></i>
        </button>
        
        <div class="chat-header-avatar">
            <img src="<?php echo $avatar_contato; ?>" alt="Avatar de <?php echo htmlspecialchars($nome_contato); ?>">
            
            <?php if (!$estou_bloqueado && $is_online): ?>
                <span class="online-indicator-dot"></span>
            <?php endif; ?>
        </div>
        
        <div class="chat-header-info">
            <h3 style="color: <?php echo $cor_padrao; ?>;"><?php echo htmlspecialchars($nome_contato); ?></h3>
            <span class="chat-status <?php echo $status_class; ?>"><?php echo $status_label; ?></span>
        </div>
    </div>

    <div class="chat-header-actions">
        <div class="dropdown-wrapper">
            <button class="icon-btn chat-options-trigger" title="Mais opções">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            
            <div class="chat-dropdown-menu is-hidden">
                <button onclick="chatAcoes.openContactInfo(<?php echo (int)$outro_usuario['id']; ?>, <?php echo (int)($conversa['id'] ?? 0); ?>)">
                    <i class="fas fa-info-circle"></i> Informações do Contato
                </button>
                
                <button onclick="chatAcoes.openMediaHub(<?php echo (int)($conversa['id'] ?? 0); ?>, 'privada')">
                    <i class="fas fa-images"></i> Ver Mídias
                </button>

                <hr>

                <button onclick="chatAcoes.togglePin(<?php echo (int)($conversa['id'] ?? 0); ?>)" 
                        class="<?php echo ($conversa['fixada'] ?? 0) ? 'active' : ''; ?>">
                    <i class="fas fa-thumbtack"></i> 
                    <?php echo ($conversa['fixada'] ?? 0) ? 'Desafixar' : 'Fixar Conversa'; ?>
                </button>
                
                <button onclick="chatAcoes.toggleMute(<?php echo (int)($conversa['id'] ?? 0); ?>)" 
                        class="<?php echo ($conversa['silenciada'] ?? 0) ? 'active' : ''; ?>">
                    <i class="<?php echo ($conversa['silenciada'] ?? 0) ? 'fas fa-bell' : 'fas fa-bell-slash'; ?>"></i> 
                    <?php echo ($conversa['silenciada'] ?? 0) ? 'Ativar Som' : 'Silenciar'; ?>
                </button>
                
                <hr>
                
                <button onclick="chatAcoes.openReport(<?php echo (int)($outro_usuario['id'] ?? 0); ?>)" class="text-danger">
                    <i class="fas fa-flag"></i> Denunciar
                </button>
                
                <?php if (isset($eu_bloqueei) && $eu_bloqueei): ?>
                    <button onclick="chatAcoes.toggleUnblock(<?php echo (int)($outro_usuario['id'] ?? 0); ?>)" class="text-success">
                        <i class="fas fa-check-circle"></i> Desbloquear Usuário
                    </button>
                <?php else: ?>
                    <button onclick="chatAcoes.toggleBlock(<?php echo (int)($outro_usuario['id'] ?? 0); ?>)" class="text-danger">
                        <i class="fas fa-ban"></i> Bloquear Usuário
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>