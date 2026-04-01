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


// Pega os dados enviados pelo JavaScript
$comment_id_to_edit = $_POST['comment_id'] ?? 0;
$new_text = trim($_POST['new_text'] ?? '');
$user_id = $_SESSION['user_id'];

// Validações básicas
if ($comment_id_to_edit <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'ID do comentário inválido.']);
    exit();
}
if (empty($new_text)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'O comentário não pode ficar vazio.']);
    exit();
}

// 1. VERIFICAÇÃO DE SEGURANÇA: Busca o comentário e confirma se pertence ao usuário logado
$sql_check_owner = "SELECT conteudo_texto FROM Comentarios WHERE id = ? AND id_usuario = ?";
$stmt_check = $conn->prepare($sql_check_owner);
$stmt_check->bind_param("ii", $comment_id_to_edit, $user_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 1) {
    // Pega o conteúdo atual do comentário para guardar no histórico
    $comment_data = $result_check->fetch_assoc();
    $conteudo_antigo = $comment_data['conteudo_texto'];

    // --- NOVA LÓGICA ADICIONADA ---
    // Se o texto novo for realmente diferente do antigo, salvamos a versão antiga na tabela de histórico
    if ($new_text !== $conteudo_antigo) {
        $sql_history = "INSERT INTO Comentarios_Edicoes (id_comentario, conteudo_antigo) VALUES (?, ?)";
        $stmt_history = $conn->prepare($sql_history);
        $stmt_history->bind_param("is", $comment_id_to_edit, $conteudo_antigo);
        $stmt_history->execute();
        $stmt_history->close();
    }
    // --- FIM DA NOVA LÓGICA ---

    // 2. Prossiga com a atualização do comentário na tabela principal (lógica que já existia)
    $sql_update = "UPDATE Comentarios SET conteudo_texto = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $new_text, $comment_id_to_edit);

    if ($stmt_update->execute()) {
        // Sucesso!
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'new_text_html' => nl2br(htmlspecialchars($new_text))]);
    } else {
        // Erro no banco de dados
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Erro ao atualizar o banco de dados.']);
    }
    $stmt_update->close();

} else {
    // 3. FALHA DE SEGURANÇA: O usuário tentou editar um comentário que não é dele.
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Você não tem permissão para editar este comentário.']);
}

$stmt_check->close();
$conn->close();
?>