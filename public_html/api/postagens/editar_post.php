<?php
/**
 * ARQUIVO: api/postagens/editar_post.php
 * PAPEL: Editar o conteúdo de texto de uma postagem e manter histórico de edições.
 * VERSÃO: 3.5 - Padronização de Resposta e Segurança (socialbr.lol)
 * NOTA: Esta API não manipula mídias, por isso mantém os caminhos originais de banco.
 */

session_start();

// Define o cabeçalho da resposta como JSON
header('Content-Type: application/json; charset=utf-8');

/**
 * FUNÇÃO AUXILIAR: Resposta de erro padronizada
 */
function error_response($message, $type = 'validacao') {
    echo json_encode(['success' => false, 'error' => $type, 'message' => $message]);
    exit();
}

// 1. --- [VERIFICAÇÕES DE SEGURANÇA] ---
if (!isset($_SESSION['user_id'])) {
    error_response("Acesso negado. Por favor, faça o login.", 'auth');
}

require_once __DIR__ . '/../../../config/database.php';

// Verificação CSRF (Crucial para edições)
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    http_response_code(403);
    error_response("Token de segurança inválido. Tente recarregar a página.", 'csrf');
}

// 2. --- [CAPTURA E VALIDAÇÃO DE DADOS] ---
$post_id_to_edit = (int)($_POST['post_id'] ?? 0);
$new_text = trim($_POST['new_text'] ?? '');
$user_id = $_SESSION['user_id'];

if ($post_id_to_edit <= 0 || empty($new_text)) {
    error_response("Dados inválidos: o texto não pode estar vazio.");
}

// 3. --- [VERIFICAÇÃO DE PROPRIEDADE E CONTEÚDO ANTIGO] ---
$sql_check_owner = "SELECT conteudo_texto FROM Postagens WHERE id = ? AND id_usuario = ? LIMIT 1";
$stmt_check = $conn->prepare($sql_check_owner);
$stmt_check->bind_param("ii", $post_id_to_edit, $user_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 1) {
    $post_data = $result_check->fetch_assoc();
    $conteudo_antigo = $post_data['conteudo_texto'];

    // 4. --- [SALVAR HISTÓRICO DE EDIÇÃO] ---
    // Só grava no histórico se o texto realmente mudou
    if ($new_text !== $conteudo_antigo) {
        $sql_history = "INSERT INTO Postagens_Edicoes (id_postagem, conteudo_antigo) VALUES (?, ?)";
        $stmt_history = $conn->prepare($sql_history);
        $stmt_history->bind_param("is", $post_id_to_edit, $conteudo_antigo);
        $stmt_history->execute();
        $stmt_history->close();
    }

    // 5. --- [ATUALIZAÇÃO DO POST] ---
    $sql_update = "UPDATE Postagens SET conteudo_texto = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $new_text, $post_id_to_edit);

    if ($stmt_update->execute()) {
        // Sucesso! Retorna o texto formatado para o JS atualizar a tela sem refresh
        echo json_encode([
            'success' => true, 
            'message' => 'Postagem atualizada!',
            'new_text_html' => nl2br(htmlspecialchars($new_text))
        ]);
    } else {
        error_response("Erro ao atualizar o banco de dados.", 'database');
    }
    $stmt_update->close();

} else {
    // Tentativa de editar post de outro usuário
    error_response("Você não tem permissão para editar esta postagem.", 'security');
}

$stmt_check->close();
$conn->close();