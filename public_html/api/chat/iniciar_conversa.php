<?php
/**
 * api/chat/iniciar_conversa.php
 * Endpoint: Criação ou Recuperação de conversa privada.
 * PAPEL: Vincular dois usuários, validar confirmação de e-mail e redirecionar/responder.
 * VERSÃO: V53.0 - Blindagem Anti-Spam & Trava de E-mail (socialbr.lol)
 */

// 1. Inicialização de Ambiente e Segurança
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Definimos o cabeçalho JSON para suporte ao motor de ações AJAX
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/ChatLogic.php';

// Função auxiliar para respostas de erro
function responder_erro($msg, $code = 'erro', $base_path = '/') {
    // Se for uma requisição AJAX, mandamos JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(['success' => false, 'error' => $code, 'message' => $msg]);
    } else {
        // Fallback para redirecionamento via URL
        header("Location: " . $base_path . "chat?erro=" . $code);
    }
    exit;
}

// Proteção de acesso: Apenas usuários logados
if (!isset($_SESSION['user_id'])) {
    responder_erro("Acesso negado. Por favor, faça o login.", 'sessao_expirada', $config['base_path']);
}

$user_id_logado = $_SESSION['user_id'];
$target_user_id = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : 0;

// 2. Validação de Entrada
if ($target_user_id <= 0 || $target_user_id === $user_id_logado) {
    responder_erro("Utilizador inválido.", 'usuario_invalido', $config['base_path']);
}

// 3. --- [NOVO] BLINDAGEM DE CONFIRMAÇÃO DE E-MAIL ---
// Verificamos o status do utilizador logado
$sql_v = "SELECT email_verificado FROM Usuarios WHERE id = ? LIMIT 1";
$stmt_v = $conn->prepare($sql_v);
$stmt_v->bind_param("i", $user_id_logado);
$stmt_v->execute();
$res_v = $stmt_v->get_result()->fetch_assoc();
$is_confirmado = ($res_v && (int)$res_v['email_verificado'] === 1);
$stmt_v->close();

if (!$is_confirmado) {
    /**
     * REGRA DE OURO: Se não estiver confirmado, só permitimos o acesso 
     * se a conversa já existir (permitindo responder). 
     * Se for para criar uma NOVA conversa, bloqueamos.
     */
    $sql_check = "SELECT c.id FROM chat_conversas c 
                  JOIN chat_participantes p1 ON c.id = p1.conversa_id 
                  JOIN chat_participantes p2 ON c.id = p2.conversa_id 
                  WHERE c.tipo = 'privada' AND p1.usuario_id = ? AND p2.usuario_id = ? LIMIT 1";
    
    $stmt_c = $conn->prepare($sql_check);
    $stmt_c->bind_param("ii", $user_id_logado, $target_user_id);
    $stmt_c->execute();
    $conversa_existente = $stmt_c->get_result()->fetch_assoc();
    $stmt_c->close();

    if (!$conversa_existente) {
        responder_erro(
            "Ação Bloqueada: Confirme o seu e-mail para poder iniciar novas conversas com pessoas que ainda não conhece.", 
            'verificacao_pendente', 
            $config['base_path']
        );
    }
}

/**
 * 4. Orquestração da Conversa
 * Chamamos o método atômico que verifica se a conversa já existe.
 * Se não existir, ele cria a conversa e os participantes em uma transação segura.
 */
$conversa_id = ChatLogic::getOrCreatePrivateConversation($conn, $user_id_logado, $target_user_id);

if ($conversa_id) {
    /**
     * SUCESSO: Retorna o redirecionamento
     */
    $redirect_url = $config['base_path'] . "chat/" . $conversa_id;

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(['success' => true, 'redirect' => $redirect_url]);
    } else {
        header("Location: " . $redirect_url);
    }
    exit;
} else {
    responder_erro("Ocorreu uma falha técnica ao iniciar a conversa.", 'falha_ao_iniciar', $config['base_path']);
}