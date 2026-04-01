<?php
/**
 * api/denuncias/criar_denuncia.php
 * VERSÃO INTEGRAL V2.1 - FIX: ArgumentCountError no bind_param
 * PAPEL: Processar e gravar denúncias de posts, comentários e usuários.
 * VERSÃO: V2.1 (socialbr.lol)
 */

session_start();

// 1. Verificação de Segurança: Garante que o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Acesso negado. Você precisa estar logado para denunciar.']);
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


$user_id = $_SESSION['user_id'];
$content_type = $_POST['content_type'] ?? '';
$content_id = isset($_POST['content_id']) ? (int)$_POST['content_id'] : 0;
$motivo = trim($_POST['motivo'] ?? '');
// [NOVO] Captura a descrição detalhada enviada pelo modal
$descricao = trim($_POST['descricao'] ?? '');

// 2. Validação dos Dados Recebidos
if (!in_array($content_type, ['post', 'comentario', 'usuario'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Tipo de conteúdo inválido.']);
    exit();
}
if ($content_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'ID de conteúdo inválido.']);
    exit();
}
if (empty($motivo)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'O motivo da denúncia não pode estar vazio.']);
    exit();
}

// 3. Prepara e Executa a Inserção no Banco de Dados
try {
    // [ATUALIZADO] SQL agora inclui a coluna 'descricao'
    $sql = "INSERT INTO Denuncias (id_usuario_denunciou, tipo_conteudo, id_conteudo, motivo, descricao) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    // [CORREÇÃO V2.1] Removido o espaço da string de tipos "isiss" para bater com as 5 variáveis enviadas
    $stmt->bind_param("isiss", $user_id, $content_type, $content_id, $motivo, $descricao);

    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Denúncia enviada com sucesso. Agradecemos sua colaboração!']);
    } else {
        throw new Exception('Erro ao registrar a denúncia no banco de dados.');
    }
    $stmt->close();
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    // Em um ambiente de produção, você poderia registrar $e->getMessage() em um log de erros.
    echo json_encode(['success' => false, 'error' => 'Ocorreu um erro interno. Por favor, tente novamente mais tarde.']);
}

$conn->close();
?>