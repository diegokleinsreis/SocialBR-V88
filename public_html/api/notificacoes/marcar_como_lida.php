<?php
session_start();

header('Content-Type: application/json');

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Acesso negado.']);
    exit();
}

// Inclui a conexão com o banco de dados (ajustando o caminho para subir 3 níveis)
require_once __DIR__ . '/../../../config/database.php';

$user_id = $_SESSION['user_id'];

// Query que atualiza todas as notificações não lidas do usuário para 'lida' (lida = 1)
$sql = "UPDATE notificacoes SET lida = 1 WHERE usuario_id = ? AND lida = 0";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

// Executa a query e retorna uma resposta de sucesso ou erro
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Falha ao atualizar notificações.']);
}

$stmt->close();
$conn->close();
?>