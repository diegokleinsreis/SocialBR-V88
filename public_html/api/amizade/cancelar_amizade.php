<?php
session_start();
header('Content-Type: application/json');

// 1. Verificação de Segurança: Garante que o utilizador está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Acesso negado. Você precisa estar logado.']);
    exit();
}

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

$utilizador_logado_id = $_SESSION['user_id'];
// O ID que vem do POST é o ID da amizade (da tabela Amizades)
$amizade_id = isset($_POST['id_amizade']) ? (int)$_POST['id_amizade'] : 0;

try {
    if ($amizade_id <= 0) {
        throw new Exception("ID de amizade inválido.");
    }

    // 2. Segurança: Apaga o registo APENAS SE a amizade estiver 'aceite'
    // E SE o utilizador logado for UMA DAS DUAS PARTES da amizade.
    $sql = "DELETE FROM Amizades 
            WHERE id = ? 
              AND (usuario_um_id = ? OR usuario_dois_id = ?)
              AND status = 'aceite'";
              
    $stmt = $conn->prepare($sql);
    // Vincula o ID do utilizador logado a ambas as verificações
    $stmt->bind_param("iii", $amizade_id, $utilizador_logado_id, $utilizador_logado_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Amizade desfeita com sucesso.']);
        } else {
            throw new Exception("Não foi possível cancelar esta amizade.");
        }
    } else {
        throw new Exception("Ocorreu um erro no servidor ao tentar cancelar a amizade.");
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();