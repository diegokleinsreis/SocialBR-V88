<?php
/**
 * api/chat/fixar_silenciar.php
 * Endpoint: Alternar estados de fixação e silenciamento.
 * PAPEL: Atualizar as preferências do utilizador na tabela chat_participantes.
 * VERSÃO: V54.0 (Nomenclatura em Português - socialbr.lol)
 */

header('Content-Type: application/json');

// 1. Inicialização e Segurança de Sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * CAMINHOS DE SISTEMA:
 * Conforme estrutura: config e src fora da public_html.
 */
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/ChatLogic.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado.']);
    exit;
}

$user_id_logado = $_SESSION['user_id'];

// 2. Captura de Dados (JSON ou POST)
$dados = json_decode(file_get_contents('php://input'), true);
$conversa_id = (int)($dados['conversa_id'] ?? 0);
$acao = $dados['acao'] ?? ''; // 'fixar' ou 'silenciar'
$token_recebido = $dados['token'] ?? '';

// Validação de Token CSRF (Segurança SocialBR)
if (empty($token_recebido) || $token_recebido !== ($_SESSION['token'] ?? '')) {
    echo json_encode(['sucesso' => false, 'erro' => 'Token de segurança inválido.']);
    exit;
}

if ($conversa_id <= 0 || !in_array($acao, ['fixar', 'silenciar'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Parâmetros inválidos.']);
    exit;
}

/**
 * 3. PROCESSAMENTO DE PREFERÊNCIA:
 * Localizamos o registo do participante para alternar o valor booleano (0 ou 1).
 */
try {
    // Definimos qual coluna será alterada com base na ação
    $coluna = ($acao === 'fixar') ? 'fixada' : 'silenciada';

    // Verificamos o estado atual para fazer o "toggle" (inverter)
    $sql_check = "SELECT $coluna FROM chat_participantes WHERE conversa_id = ? AND usuario_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $conversa_id, $user_id_logado);
    $stmt_check->execute();
    $resultado = $stmt_check->get_result()->fetch_assoc();

    if (!$resultado) {
        echo json_encode(['sucesso' => false, 'erro' => 'Participante não encontrado nesta conversa.']);
        exit;
    }

    $novo_estado = $resultado[$coluna] ? 0 : 1;

    // Atualizamos o banco de dados
    $sql_update = "UPDATE chat_participantes SET $coluna = ? WHERE conversa_id = ? AND usuario_id = ?";
    $stmt_upd = $conn->prepare($sql_update);
    $stmt_upd->bind_param("iii", $novo_estado, $conversa_id, $user_id_logado);
    
    if ($stmt_upd->execute()) {
        echo json_encode([
            'sucesso' => true, 
            'acao' => $acao, 
            'estado' => $novo_estado,
            'mensagem' => 'Preferência atualizada com sucesso.'
        ]);
    } else {
        throw new Exception("Falha na execução da atualização.");
    }

} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro técnico ao processar preferência.']);
}