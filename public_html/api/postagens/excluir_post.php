<?php
/**
 * ARQUIVO: api/postagens/excluir_post.php
 * PAPEL: Realizar o "Soft Delete" (exclusão lógica) de uma postagem.
 * VERSÃO: 3.5 - Padronização Arquiteto (socialbr.lol)
 * NOTA: Esta API mantém os arquivos físicos no servidor para fins de log/recuperação.
 */

session_start();

// Define o cabeçalho da resposta como JSON com suporte a caracteres especiais
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

// Verificação CSRF (Proteção contra ataques de submissão forçada)
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    http_response_code(403);
    error_response("Token de segurança inválido. Tente recarregar a página.", 'csrf');
}

// 2. --- [CAPTURA E VALIDAÇÃO DE DADOS] ---
$post_id_to_delete = (int)($_POST['post_id'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($post_id_to_delete <= 0) {
    error_response("ID da postagem inválido.");
}

// 3. --- [VERIFICAÇÃO DE PROPRIEDADE] ---
// Garante que o utilizador só possa apagar os seus próprios posts
$sql_check_owner = "SELECT id FROM Postagens WHERE id = ? AND id_usuario = ? LIMIT 1";
$stmt_check = $conn->prepare($sql_check_owner);
$stmt_check->bind_param("ii", $post_id_to_delete, $user_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 1) {
    
    // 4. --- [EXECUÇÃO DO SOFT DELETE] ---
    // Em vez de apagar o registo, mudamos o status para ocultá-lo da rede.
    $sql_delete = "UPDATE Postagens SET status = 'excluido_pelo_usuario' WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $post_id_to_delete);

    if ($stmt_delete->execute()) {
        // Sucesso!
        echo json_encode([
            'success' => true,
            'message' => 'A postagem foi removida com sucesso.'
        ]);
    } else {
        error_response("Erro ao atualizar o status na base de dados.", 'database');
    }
    $stmt_delete->close();

} else {
    // 5. --- [FALHA DE SEGURANÇA] ---
    // Tentativa de manipulação de ID que não pertence ao utilizador logado
    error_response("Você não tem permissão para excluir esta postagem.", 'security');
}

$stmt_check->close();
$conn->close();