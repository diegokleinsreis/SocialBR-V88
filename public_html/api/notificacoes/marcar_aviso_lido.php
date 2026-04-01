<?php
/**
 * api/notificacoes/marcar_aviso_lido.php
 * Endpoint: Registo de Leitura de Alertas Globais (Broadcast).
 * PAPEL: Inserir ID do utilizador e do aviso na tabela Avisos_Lidos.
 * VERSÃO: 1.0 (socialbr.lol)
 */

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0); // Blindagem contra erros de output que sujam o JSON

// 1. Início de Sessão e Autenticação
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Acesso negado. Sessão expirada.']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// 2. Conexão com o Banco de Dados (Subida de diretórios robusta)
$baseDir = __DIR__;
while ($baseDir !== '/' && !file_exists($baseDir . '/config/database.php')) {
    $baseDir = dirname($baseDir);
}

if (!file_exists($baseDir . '/config/database.php')) {
    echo json_encode(['success' => false, 'error' => 'Erro interno de configuração.']);
    exit;
}

require_once $baseDir . '/config/database.php';

// 3. Captura e Validação do ID do Aviso
$aviso_id = isset($_POST['aviso_id']) ? (int)$_POST['aviso_id'] : 0;

if ($aviso_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de aviso inválido.']);
    exit;
}

try {
    // 4. Lógica de Persistência (Evita duplicados com IGNORE ou verificação)
    // Se o utilizador já clicou em fechar antes, não fazemos nada.
    $sql_check = "SELECT id FROM Avisos_Lidos WHERE id_usuario = ? AND id_aviso = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $user_id, $aviso_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 0) {
        // Inserção do registo de leitura
        $sql_insert = "INSERT INTO Avisos_Lidos (id_usuario, id_aviso, data_leitura) VALUES (?, ?, NOW())";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ii", $user_id, $aviso_id);
        
        if ($stmt_insert->execute()) {
            echo json_encode(['success' => true, 'message' => 'Aviso silenciado.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Falha ao registar leitura.']);
        }
        $stmt_insert->close();
    } else {
        // Já está marcado como lido
        echo json_encode(['success' => true, 'message' => 'Aviso já constava como lido.']);
    }

    $stmt_check->close();

} catch (Exception $e) {
    error_log("Erro Alertas: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erro interno ao processar a leitura.']);
}

$conn->close();