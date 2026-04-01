<?php
session_start();
header('Content-Type: application/json');

// 1. Verificação de Segurança: Garante que o utilizador está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Acesso negado. Você precisa estar logado.']);
    exit();
}

// --- IMPORTAÇÕES NECESSÁRIAS ---
require_once __DIR__ . '/../../../config/database.php';
// Importa o dicionário de tipos para padronizar as notificações
require_once __DIR__ . '/../../../config/tipos_notificacoes.php';

// --- NOVO BLOCO DE SEGURANÇA: VERIFICAÇÃO CSRF ---
// Verifica se é um POST, se o token existe e se é válido.
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    header('Content-Type: application/json');
    http_response_code(403); // Código 403: Acesso Proibido/Token Inválido
    echo json_encode(['success' => false, 'error' => 'Token de segurança inválido. Tente recarregar a página.']);
    exit();
}
// --- FIM DO NOVO BLOCO ---


$remetente_id = $_SESSION['user_id'];
$destinatario_id = isset($_POST['id_usuario_recebe']) ? (int)$_POST['id_usuario_recebe'] : 0;

try {
    // 2. Validações Lógicas
    if ($destinatario_id <= 0) {
        throw new Exception("ID de destinatário inválido.");
    }

    if ($remetente_id === $destinatario_id) {
        throw new Exception("Você não pode enviar um pedido de amizade para si mesmo.");
    }

    // 3. Verifica se já existe uma amizade ou pedido (em qualquer direção)
    $sql_check = "SELECT id FROM Amizades 
                  WHERE (usuario_um_id = ? AND usuario_dois_id = ?) 
                     OR (usuario_um_id = ? AND usuario_dois_id = ?)";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("iiii", $remetente_id, $destinatario_id, $destinatario_id, $remetente_id);
    $stmt_check->execute();
    
    if ($stmt_check->get_result()->num_rows > 0) {
        throw new Exception("Já existe um pedido de amizade ou uma amizade com este utilizador.");
    }
    $stmt_check->close();

    // 4. Insere o novo pedido de amizade na tabela
    $sql_insert = "INSERT INTO Amizades (usuario_um_id, usuario_dois_id, status) VALUES (?, ?, 'pendente')";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("ii", $remetente_id, $destinatario_id);

    if ($stmt_insert->execute()) {
        // --- BLOCO DE CÓDIGO PARA CRIAR A NOTIFICAÇÃO PADRONIZADA ---
        // Usamos a constante definida no dicionário
        $tipo_notificacao = NOTIF_PEDIDO_AMIZADE;
        // O id_referencia será o ID de quem enviou o pedido, para o link apontar para o perfil dele.
        $id_referencia = $remetente_id; 

        $sql_notificacao = "INSERT INTO notificacoes (usuario_id, remetente_id, tipo, id_referencia) VALUES (?, ?, ?, ?)";
        $stmt_notificacao = $conn->prepare($sql_notificacao);
        // Parâmetros: [quem recebe], [quem envia], [tipo], [ID de referência]
        $stmt_notificacao->bind_param("iisi", $destinatario_id, $remetente_id, $tipo_notificacao, $id_referencia);
        $stmt_notificacao->execute();
        $stmt_notificacao->close();
        // --- FIM DO BLOCO DE NOTIFICAÇÃO ---

        // Sucesso!
        echo json_encode(['success' => true, 'message' => 'Pedido de amizade enviado com sucesso!']);
    } else {
        throw new Exception("Ocorreu um erro ao enviar o pedido. Tente novamente.");
    }
    $stmt_insert->close();

} catch (Exception $e) {
    // Captura qualquer erro e envia uma resposta JSON
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>