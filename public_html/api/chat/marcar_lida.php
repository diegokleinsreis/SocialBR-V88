<?php
/**
 * api/chat/marcar_lida.php
 * Endpoint: Atualização de status de leitura.
 * PAPEL: Resetar o contador de notificações e validar visualização para o remetente.
 * VERSÃO: V54.1 (Integração com Cérebro V54.1 - socialbr.lol)
 */

header('Content-Type: application/json');

// 1. Inicialização e Segurança
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/ChatLogic.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado.']);
    exit;
}

$user_id_logado = $_SESSION['user_id'];

/**
 * CAPTURA DE DADOS (JSON INPUT)
 * Captura o input bruto enviado pelo chat_motor.js via fetch.
 */
$input_raw = file_get_contents('php://input');
$dados = json_decode($input_raw, true);

$conversa_id = (int)($dados['conversa_id'] ?? 0);
$token_recebido = $dados['token'] ?? '';

// 2. Validação de Segurança (CSRF)
// Verifica se o token da sessão bate com o enviado pelo front-end
if ($token_recebido !== $_SESSION['token']) {
    echo json_encode(['sucesso' => false, 'erro' => 'Falha de segurança: Token inválido.']);
    exit;
}

if ($conversa_id <= 0) {
    echo json_encode(['sucesso' => false, 'erro' => 'ID de conversa inválido.']);
    exit;
}

// 3. Execução da Atualização via ChatLogic
try {
    /**
     * Chamamos o método centralizado no ChatLogic.
     * Isso atualiza chat_participantes.ultima_leitura_at para NOW().
     */
    $resultado = ChatLogic::markAsRead($conn, $user_id_logado, $conversa_id);

    if ($resultado) {
        echo json_encode(['sucesso' => true]);
    } else {
        echo json_encode(['sucesso' => false, 'erro' => 'Erro ao atualizar base de dados.']);
    }

} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Falha técnica no servidor: ' . $e->getMessage()]);
}