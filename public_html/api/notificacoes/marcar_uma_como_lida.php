<?php
session_start();

header('Content-Type: application/json');

// 1. Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Acesso negado.']);
    exit();
}

// 2. Inclui a conexão com o banco de dados
require_once __DIR__ . '/../../../config/database.php';

$user_id = $_SESSION['user_id'];
// 3. Pega o ID da notificação enviado pelo JavaScript
$notification_id = $_POST['id'] ?? 0;

// Validação simples
if ($notification_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de notificação inválido.']);
    exit();
}

// 4. Query para atualizar APENAS a notificação clicada
// A cláusula 'AND usuario_id = ?' é uma segurança para garantir que um usuário
// não possa marcar como lida a notificação de outra pessoa.
$sql = "UPDATE notificacoes SET lida = 1 WHERE id = ? AND usuario_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $notification_id, $user_id);

// 5. Executa e retorna a resposta
if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    // Pode falhar se o ID da notificação não pertencer ao usuário
    echo json_encode(['success' => false, 'error' => 'Falha ao marcar notificação como lida.']);
}

$stmt->close();
$conn->close();
?>