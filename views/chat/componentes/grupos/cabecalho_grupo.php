<?php
/**
 * views/chat/componentes/grupos/cabecalho_grupo.php
 * Sub-componente Especialista: Cabeçalho de Conversa de Grupo.
 * PAPEL: Exibir identidade do grupo e gatilhos de gestão de membros.
 * VERSÃO: V61.3 (Integração AJAX Media Hub - socialbr.lol)
 */

// 1. Proteção de contexto e Variáveis Globais
if (!isset($conversa)) exit;

// Estabilização: Garante que a cor de identidade exista (Evita PHP Warning e quebra de layout)
if (!isset($cor_padrao)) {
    $cor_padrao = "#0C2D54"; 
}

/**
 * 2. IDENTIDADE VISUAL DO GRUPO
 * Resolve o problema de imagem quebrada usando um ícone de fallback de grupo.
 */
$nome_grupo = !empty($conversa['titulo']) ? $conversa['titulo'] : 'Grupo SocialBR';
$avatar_grupo = !empty($conversa['capa_url']) 
    ? $config['base_path'] . $conversa['capa_url'] 
    : $config['base_path'] . 'assets/images/default-group.png';

/**
 * 3. LÓGICA DE PODER (Administração) V61.3
 * Validação real: Verifica se o utilizador logado é o proprietário do grupo.
 */
$is_admin = ChatLogic::isGroupOwner($conn, (int)$conversa['id'], (int)$user_id_logado);
?>

<header class="chat-header" style="border-bottom: 2px solid <?php echo $cor_padrao; ?>22;">
    <div class="chat-header-user">
        <button class="mobile-back-btn" onclick="chatMotor.voltarParaLista()" title="Voltar para a lista">
            <i class="fas fa-arrow-left"></i>
        </button>
        
        <div class="chat-header-avatar group-avatar-style">
            <img src="<?php echo $avatar_grupo; ?>" alt="Capa do grupo <?php echo htmlspecialchars($nome_grupo); ?>"
                 onerror="this.src='<?php echo $config['base_path']; ?>assets/images/default-group.png';">
        </div>
        
        <div class="chat-header-info">
            <h3 style="color: <?php echo $cor_padrao; ?>;"><?php echo htmlspecialchars($nome_grupo); ?></h3>
            <span class="chat-status">Conversa de Grupo</span>
        </div>
    </div>

    <div class="chat-header-actions">
        <div class="dropdown-wrapper">
            <button class="icon-btn chat-options-trigger" title="Opções do Grupo">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            
            <div class="chat-dropdown-menu is-hidden">
                <button onclick="chatAcoes.openGroupInfo(<?php echo (int)$conversa['id']; ?>)">
                    <i class="fas fa-info-circle"></i> Informações do Grupo
                </button>

                <?php if ($is_admin): ?>
                <button onclick="chatAcoes.openMemberManagement(<?php echo (int)$conversa['id']; ?>)">
                    <i class="fas fa-users-cog"></i> Gerenciar Membros
                </button>
                <?php endif; ?>
                
                <button onclick="chatAcoes.openMediaHub(<?php echo (int)$conversa['id']; ?>, 'grupo')">
                    <i class="fas fa-images"></i> Mídias do Grupo
                </button>

                <hr>

                <button onclick="chatAcoes.togglePin(<?php echo (int)$conversa['id']; ?>)" 
                        class="<?php echo ($conversa['fixada'] ?? 0) ? 'active' : ''; ?>">
                    <i class="fas fa-thumbtack"></i> 
                    <?php echo ($conversa['fixada'] ?? 0) ? 'Desafixar Grupo' : 'Fixar Grupo'; ?>
                </button>
                
                <button onclick="chatAcoes.toggleMute(<?php echo (int)$conversa['id']; ?>)" 
                        class="<?php echo ($conversa['silenciada'] ?? 0) ? 'active' : ''; ?>">
                    <i class="<?php echo ($conversa['silenciada'] ?? 0) ? 'fas fa-bell' : 'fas fa-bell-slash'; ?>"></i> 
                    <?php echo ($conversa['silenciada'] ?? 0) ? 'Ativar Som' : 'Silenciar Grupo'; ?>
                </button>
                
                <hr>
                
                <button onclick="chatAcoes.gerenciarMembro(<?php echo (int)$conversa['id']; ?>, <?php echo (int)$user_id_logado; ?>, 'sair')" class="text-danger">
                    <i class="fas fa-sign-out-alt"></i> Sair do Grupo
                </button>
            </div>
        </div>
    </div>
</header>