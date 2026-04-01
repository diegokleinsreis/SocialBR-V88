<?php
// --- CORREÇÃO DE CAMINHO ---
// O arquivo está em: /public_html/api/usuarios/
// Precisamos subir 3 níveis para chegar à raiz onde está a pasta 'config'
require_once '../../../config/database.php'; 
// ---------------------------

header('Content-Type: application/json');

// Removemos session_start() duplicado, pois o database.php já o faz.
// Se por acaso não estiver ativa, iniciamos com segurança:
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificação de Autenticação
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Você precisa estar logado para realizar esta ação.']);
    exit;
}

// 2. Verificação CSRF (Segurança)
$csrf_token = $_POST['csrf_token'] ?? '';
if (!function_exists('verify_csrf_token')) {
     echo json_encode(['success' => false, 'error' => 'Erro interno de configuração (funções de segurança ausentes).']);
     exit;
}

if (!verify_csrf_token($csrf_token)) {
    echo json_encode(['success' => false, 'error' => 'Token de segurança inválido. Atualize a página e tente novamente.']);
    exit;
}

// 3. Obter e Validar Dados
$bloqueador_id = $_SESSION['user_id'];
$bloqueado_id = filter_input(INPUT_POST, 'id_usuario_bloqueado', FILTER_VALIDATE_INT);

if (!$bloqueado_id) {
    echo json_encode(['success' => false, 'error' => 'ID do usuário inválido.']);
    exit;
}

if ($bloqueador_id === $bloqueado_id) {
    echo json_encode(['success' => false, 'error' => 'Você não pode bloquear a si mesmo.']);
    exit;
}

try {
    // Iniciar Transação (Modo MySQLi)
    $conn->begin_transaction();

    // A. Inserir o Bloqueio na tabela 'Bloqueios'
    $stmt = $conn->prepare("
        INSERT IGNORE INTO Bloqueios (bloqueador_id, bloqueado_id, data_bloqueio) 
        VALUES (?, ?, NOW())
    ");
    $stmt->bind_param("ii", $bloqueador_id, $bloqueado_id);
    $stmt->execute();

    // B. Destruir amizades existentes (COM OS NOMES CERTOS DAS COLUNAS)
    // Verifica se existe amizade onde um é usuario_um_id e o outro usuario_dois_id, e vice-versa.
    $stmtAmizade = $conn->prepare("
        DELETE FROM Amizades 
        WHERE (usuario_um_id = ? AND usuario_dois_id = ?) 
           OR (usuario_um_id = ? AND usuario_dois_id = ?)
    ");
    // Passamos os IDs duas vezes para cobrir as duas possibilidades (OR)
    // Possibilidade 1: Bloqueador é 'um', Bloqueado é 'dois'
    // Possibilidade 2: Bloqueador é 'dois', Bloqueado é 'um'
    $stmtAmizade->bind_param("iiii", $bloqueador_id, $bloqueado_id, $bloqueado_id, $bloqueador_id);
    $stmtAmizade->execute();

    // Confirmar as alterações
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Usuário bloqueado com sucesso.']);

} catch (Exception $e) {
    // Desfaz tudo em caso de erro
    $conn->rollback();
    
    error_log("Erro ao bloquear usuário: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Ocorreu um erro ao tentar bloquear o usuário.']);
}
?>