<?php
/**
 * api/chat/contar_nao_lidas.php
 * CONTROLADOR: Contador de Mensagens Pendentes.
 * PAPEL: Retornar o total de mensagens não lidas para o badge do menu (#menu-chat-badge).
 * VERSÃO: 1.2 (Fix de Caminho Absoluto - socialbr.lol)
 */

header('Content-Type: application/json');

// 1. INICIALIZAÇÃO E SEGURANÇA
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o utilizador está autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sessão expirada.']);
    exit;
}

/**
 * 2. DEFINIÇÃO DE CAMINHOS ABSOLUTOS
 * Utilizamos o dirname($_SERVER['DOCUMENT_ROOT']) para sair da 'public_html'
 * e alcançar as pastas 'config' e 'src' na raiz da conta /home/klscom/
 */
$raiz_servidor = dirname($_SERVER['DOCUMENT_ROOT']); // Sobe para /home/klscom/

$caminho_banco = $raiz_servidor . '/config/database.php';
$caminho_logic = $raiz_servidor . '/src/ChatLogic.php';

// Verificação de segurança: Se os ficheiros não existirem no local esperado, avisa o log.
if (!file_exists($caminho_banco) || !file_exists($caminho_logic)) {
    error_log("❌ Erro de Caminho Absoluto: Ficheiros de config ou src não encontrados em $raiz_servidor");
    echo json_encode(['success' => false, 'total' => 0, 'error' => 'Erro interno de caminhos no servidor.']);
    exit;
}

require_once $caminho_banco;
require_once $caminho_logic;

$user_id = (int)$_SESSION['user_id'];

try {
    /**
     * LÓGICA DE CONTAGEM:
     * Procura mensagens em conversas onde o utilizador é participante
     * e a data da mensagem é superior à 'ultima_leitura_at' registada.
     */
    $sql = "SELECT COUNT(m.id) as total 
            FROM Chat_Mensagens m
            JOIN Chat_Participantes p ON m.conversa_id = p.conversa_id
            WHERE p.usuario_id = ? 
            AND m.remetente_id != ?
            AND m.criado_em > p.ultima_leitura_at
            AND p.silenciada = 0";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    $total_nao_lidas = (int)($data['total'] ?? 0);

    echo json_encode([
        'success' => true,
        'total' => $total_nao_lidas
    ]);

} catch (Exception $e) {
    // Em caso de erro técnico, devolvemos 0 para não quebrar a UI
    echo json_encode([
        'success' => false,
        'total' => 0,
        'error' => $e->getMessage()
    ]);
}