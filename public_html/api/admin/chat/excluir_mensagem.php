<?php
/**
 * api/admin/chat/excluir_mensagem.php
 * PAPEL: Remoção física de mensagens por moderação.
 */
require_once __DIR__ . '/../../../admin/admin_auth.php'; 

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$id_mensagem = isset($input['id']) ? (int)$input['id'] : 0;

if ($id_mensagem <= 0) {
    echo json_encode(['sucesso' => false, 'erro' => 'ID da mensagem inválido.']);
    exit;
}

try {
    // Executa a exclusão direta na tabela
    $stmt = $conn->prepare("DELETE FROM chat_mensagens WHERE id = ?");
    $stmt->bind_param("i", $id_mensagem);
    
    if ($stmt->execute()) {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Mensagem removida.']);
    } else {
        echo json_encode(['sucesso' => false, 'erro' => 'Falha ao deletar registro.']);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro interno de servidor.']);
}