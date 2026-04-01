<?php
session_start();

/**
 * API: Atualizar Dados de Perfil (V65.7 - Clean Architecture)
 * Responsável apenas pela atualização de dados textuais do utilizador.
 */

header('Content-Type: application/json');

// 1. Verificações de Segurança e Sessão
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Acesso negado. Você precisa estar logado.']);
    exit();
}

require_once __DIR__ . '/../../../config/database.php';

// 2. Validação do Token CSRF (O selo de segurança que adicionamos à View)
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token de segurança inválido. Por favor, recarregue a página.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$response = ['success' => true];

// 3. Processamento dos Dados do Formulário
try {
    // Sanitização básica de entradas
    $nome            = trim($_POST['nome'] ?? '');
    $sobrenome       = trim($_POST['sobrenome'] ?? '');
    $biografia       = trim($_POST['biografia'] ?? '');
    $data_nascimento = $_POST['data_nascimento'] ?? '';
    $relacionamento  = $_POST['relacionamento'] ?? 'Não especificado';
    $id_bairro       = (int)($_POST['id_bairro'] ?? 0);

    // Validação de campos obrigatórios
    if (empty($nome) || empty($sobrenome) || empty($data_nascimento) || $id_bairro <= 0) {
        throw new Exception("Nome, sobrenome, data de nascimento e bairro são obrigatórios.");
    }
    
    // Preparação da Query SQL (Segurança contra SQL Injection via Prepared Statements)
    $sql = "UPDATE Usuarios SET 
                nome = ?, 
                sobrenome = ?, 
                biografia = ?, 
                data_nascimento = ?, 
                relacionamento = ?, 
                id_bairro = ? 
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssii", $nome, $sobrenome, $biografia, $data_nascimento, $relacionamento, $id_bairro, $user_id);

    if (!$stmt->execute()) {
        throw new Exception("Erro ao guardar as alterações no banco de dados.");
    }

    $response['message'] = "As informações do seu perfil foram atualizadas com sucesso!";

} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

// 4. Fecho da ligação e resposta
echo json_encode($response);
$conn->close();
?>