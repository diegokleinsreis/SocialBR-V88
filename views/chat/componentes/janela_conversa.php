<?php
/**
 * views/chat/componentes/janela_conversa.php
 * Componente Mestre (Orquestrador): Janela de Interação Ativa.
 * PAPEL: "Router" de Componentes e Gestor de Contexto com Suporte a Painéis Overlay.
 * VERSÃO: V64.3 (Lógica de Identificação de Autor de Bloqueio - socialbr.lol)
 */

// 1. Recuperação de Contexto
$target_id = (int)($_GET['id'] ?? 0);
$user_id_logado = $_SESSION['user_id'] ?? 0;

/**
 * CONFIGURAÇÃO VISUAL:
 * Define a identidade visual para os sub-componentes.
 */
$cor_padrao = "#0C2D54"; 

/**
 * LÓGICA DE IDENTIFICAÇÃO:
 * Diferencia se o alvo é uma conversa existente ou o início de um novo chat.
 */
$conversa = null;
$outro_usuario = null;
$tipo_conversa = 'privada'; 

// Tenta buscar como conversa existente
$sql_conv = "SELECT c.*, p.silenciada FROM chat_conversas c 
             JOIN chat_participantes p ON c.id = p.conversa_id 
             WHERE c.id = ? AND p.usuario_id = ?";
$stmt = $conn->prepare($sql_conv);
$stmt->bind_param("ii", $target_id, $user_id_logado);
$stmt->execute();
$conversa = $stmt->get_result()->fetch_assoc();

if ($conversa) {
    $tipo_conversa = $conversa['tipo'];

    if ($tipo_conversa === 'privada') {
        $sql_u = "SELECT u.id, u.nome, u.sobrenome, u.foto_perfil_url, u.nome_de_usuario, u.ultimo_acesso 
                  FROM chat_participantes p 
                  JOIN Usuarios u ON p.usuario_id = u.id 
                  WHERE p.conversa_id = ? AND p.usuario_id != ?";
        $stmt_u = $conn->prepare($sql_u);
        $stmt_u->bind_param("ii", $conversa['id'], $user_id_logado);
        $stmt_u->execute();
        $outro_usuario = $stmt_u->get_result()->fetch_assoc();
    }
} else {
    // Lógica para novo chat: $target_id aqui é o ID do utilizador alvo
    $sql_new = "SELECT id, nome, sobrenome, foto_perfil_url, nome_de_usuario, ultimo_acesso 
                FROM Usuarios WHERE id = ?";
    $stmt_n = $conn->prepare($sql_new);
    $stmt_n->bind_param("i", $target_id);
    $stmt_n->execute();
    $outro_usuario = $stmt_n->get_result()->fetch_assoc();
    
    if (!$outro_usuario) {
        include __DIR__ . '/estado_vazio.php';
        return;
    }
}

/**
 * MOTOR DE AMBIENTE:
 * Cálculo de status e bloqueios para chats privados.
 */
$is_online = false;
$estou_bloqueado = false;
$eu_bloqueei = false; // [NOVO] Variável de controle para ações de desbloqueio

if ($tipo_conversa === 'privada' && $outro_usuario) {
    if (isset($outro_usuario['ultimo_acesso'])) {
        $ultima_atv = strtotime($outro_usuario['ultimo_acesso']);
        $agora = time();
        if (($agora - $ultima_atv) < 300) { $is_online = true; }
    }
    
    // Verifica se há qualquer tipo de bloqueio (Geral)
    $estou_bloqueado = ChatLogic::isUserBlocked($conn, $user_id_logado, $outro_usuario['id']);
    
    // [V64.3] Verifica especificamente se FUI EU quem bloqueou
    // Essencial para decidir se o menu deve mostrar "Bloquear" ou "Desbloquear"
    $eu_bloqueei = ChatLogic::didIBlockThisUser($conn, $user_id_logado, $outro_usuario['id']);
}

$token_sessao = $_SESSION['token'] ?? '';
?>

<div class="chat-window-container" 
     data-conversa-id="<?php echo (int)($conversa['id'] ?? 0); ?>" 
     data-target-id="<?php echo (int)($outro_usuario['id'] ?? 0); ?>"
     data-tipo="<?php echo htmlspecialchars($tipo_conversa); ?>"
     style="position: relative; height: 100%; width: 100%; display: flex; flex-direction: column; overflow: hidden;"> 
    
    <div class="chat-view-main" style="flex: 1; display: flex; flex-direction: column; height: 100%; min-height: 0; overflow: hidden; position: relative; width: 100%;">
        <?php 
        // 1. Cabeçalho (Nome, Avatar, Ações)
        // Passamos $eu_bloqueei para este sub-componente
        include __DIR__ . '/cabecalho_conversa.php';

        // 2. Área de Mensagens (Timeline dinâmica)
        include __DIR__ . '/area_mensagens.php';

        // 3. Campo de Entrada (Footer orquestrado)
        if (!$estou_bloqueado) {
            include __DIR__ . '/campo_input.php';
        } else {
            echo '<div class="chat-blocked-footer" style="padding: 15px; text-align: center; background: rgba(0,0,0,0.05); color: var(--chat-text-sub); font-weight: 600; font-size: 0.85rem;">
                    <i class="fas fa-ban" style="margin-right: 8px;"></i> Interações desativadas para esta conversa.
                  </div>';
        }
        ?>
    </div>

    <aside id="chat-right-sidebar" class="is-hidden"></aside>

</div>