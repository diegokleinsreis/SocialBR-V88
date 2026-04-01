<?php
// Caminho para a configuração
require_once '../../../config/database.php'; 

header('Content-Type: application/json');

// Iniciar sessão se necessário
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Autenticação
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Login necessário.']);
    exit;
}

// 2. CSRF
$csrf_token = $_POST['csrf_token'] ?? '';
if (!function_exists('verify_csrf_token') || !verify_csrf_token($csrf_token)) {
    echo json_encode(['success' => false, 'error' => 'Token inválido.']);
    exit;
}

// 3. Dados
$desbloqueador_id = $_SESSION['user_id'];
$desbloqueado_id = filter_input(INPUT_POST, 'id_usuario_desbloqueado', FILTER_VALIDATE_INT);

if (!$desbloqueado_id) {
    echo json_encode(['success' => false, 'error' => 'ID inválido.']);
    exit;
}

try {
    // Apagar o registo da tabela Bloqueios
    $stmt = $conn->prepare("DELETE FROM Bloqueios WHERE bloqueador_id = ? AND bloqueado_id = ?");
    $stmt->bind_param("ii", $desbloqueador_id, $desbloqueado_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Usuário desbloqueado.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Este usuário não estava bloqueado.']);
    }

} catch (Exception $e) {
    error_log("Erro desbloqueio: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erro ao desbloquear.']);
}
?>