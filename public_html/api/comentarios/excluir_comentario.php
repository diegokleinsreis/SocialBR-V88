<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Acesso negado.']);
    exit();
}

// Inclui a conexão com o banco de dados
require_once __DIR__ . '/../../../config/database.php';

// --- NOVO BLOCO DE SEGURANÇA: VERIFICAÇÃO CSRF ---
// Verifica se é um POST, se o token existe e se é válido.
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    header('Content-Type: application/json');
    http_response_code(403); // Código 403: Acesso Proibido/Token Inválido
    echo json_encode(['success' => false, 'error' => 'Token de segurança inválido. Tente recarregar a página.']);
    exit();
}
// --- FIM DO NOVO BLOCO ---


// Pega o ID do comentário enviado pelo JavaScript
$comment_id_to_delete = $_POST['comment_id'] ?? 0;
$user_id = $_SESSION['user_id'];

if ($comment_id_to_delete > 0) {
    // 1. VERIFICAÇÃO DE SEGURANÇA: Confirma se o comentário pertence ao usuário logado.
    $sql_check_owner = "SELECT id FROM Comentarios WHERE id = ? AND id_usuario = ?";
    $stmt_check = $conn->prepare($sql_check_owner);
    $stmt_check->bind_param("ii", $comment_id_to_delete, $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 1) {
        // 2. O usuário é o dono, então prossiga com a "exclusão suave" (soft delete)
        // Nós não apagamos o registro, apenas mudamos o status.
        $sql_delete = "UPDATE Comentarios SET status = 'excluido_pelo_usuario' WHERE id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $comment_id_to_delete);

        if ($stmt_delete->execute()) {
            // Sucesso!
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            // Erro no banco de dados
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Erro ao atualizar o banco de dados.']);
        }
    } else {
        // 3. FALHA DE SEGURANÇA: O usuário tentou excluir um comentário que não é dele.
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Você não tem permissão para excluir este comentário.']);
    }

    $stmt_check->close();
    $conn->close();

} else {
    // ID do comentário inválido
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'ID do comentário inválido.']);
}