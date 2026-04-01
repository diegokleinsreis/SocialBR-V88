<?php
/**
 * api/admin/chat/bloquear_conversa.php
 * PAPEL: Alternar status da conversa (Ativa / Bloqueada).
 * VERSÃO: 1.0 (Componentizado - socialbr.lol)
 */

// --- [PASSO 1: PROTEÇÃO E CONEXÃO] ---
// Sobe 3 níveis: chat -> admin -> api -> raiz
require_once __DIR__ . '/../../../admin/admin_auth.php'; 

header('Content-Type: application/json');

// --- [PASSO 2: CAPTURA DOS DADOS JSON] ---
$input = json_decode(file_get_contents('php://input'), true);

$id_conversa = isset($input['id']) ? (int)$input['id'] : 0;
$status_atual = isset($input['status']) ? trim($input['status']) : '';

if ($id_conversa <= 0 || empty($status_atual)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Parâmetros de moderação inválidos.']);
    exit;
}

// --- [PASSO 3: LÓGICA DE INVERSÃO DE STATUS] ---
/**
 * Se o status for 'ativa', bloqueamos. 
 * Se estiver 'bloqueada', reativamos.
 */
$novo_status = ($status_atual === 'ativa') ? 'bloqueada' : 'ativa';

try {
    // --- [PASSO 4: EXECUÇÃO NO BANCO DE DADOS] ---
    $sql = "UPDATE chat_conversas SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Falha na preparação da query: " . $conn->error);
    }

    $stmt->bind_param("si", $novo_status, $id_conversa);
    
    if ($stmt->execute()) {
        echo json_encode([
            'sucesso' => true, 
            'novo_status' => $novo_status,
            'mensagem' => "Conversa " . ($novo_status == 'ativa' ? 'reativada' : 'bloqueada') . " com sucesso."
        ]);
    } else {
        echo json_encode(['sucesso' => false, 'erro' => 'Não foi possível atualizar o status no banco.']);
    }

    $stmt->close();

} catch (Exception $e) {
    echo json_encode([
        'sucesso' => false, 
        'erro' => 'Erro interno na API de bloqueio: ' . $e->getMessage()
    ]);
}