<?php
/**
 * ARQUIVO: api/usuarios/reenviar_verificacao.php
 * PAPEL: Gerenciar o reenvio de e-mail com inteligência de reuso de token.
 * VERSÃO: 3.1 - Implementação de Idempotência (socialbr.lol)
 */

// 1. --- [DEPENDÊNCIAS & SEGURANÇA] ---
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/UserLogic.php';
require_once __DIR__ . '/../../../src/EmailLogic.php';

header('Content-Type: application/json; charset=utf-8');

// Verificação de Sessão (Apenas usuários logados podem solicitar reenvio)
$user_id = $_SESSION['user_id'] ?? 0;

if ($user_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Sessão expirada. Por favor, faça login novamente.']);
    exit;
}

// 2. --- [BUSCA DE DADOS DO UTILIZADOR] ---
// Buscamos os dados incluindo a coluna token_verificacao para verificar reuso
$userData = UserLogic::getUserDataForSettings($conn, $user_id);

if (!$userData) {
    echo json_encode(['success' => false, 'message' => 'Utilizador não encontrado no sistema.']);
    exit;
}

// 3. --- [BLOQUEIOS DE SEGURANÇA (GUARDS)] ---

// Caso 1: Usuário já está verificado
if ((int)$userData['email_verificado'] === 1) {
    echo json_encode(['success' => false, 'message' => 'Este e-mail já foi verificado com sucesso.']);
    exit;
}

// Caso 2: Respeitar o intervalo de 24h (Blindagem Anti-Spam)
if (!UserLogic::precisaMostrarAvisoVerificacao($userData)) {
    echo json_encode(['success' => false, 'message' => 'Um link já foi enviado recentemente. Por favor, aguarde 24 horas para um novo reenvio ou verifique sua caixa de spam.']);
    exit;
}

// 4. --- [INTELIGÊNCIA DE TOKEN (IDEMPOTÊNCIA)] ---
/**
 * Se o utilizador já tem um token na base de dados, vamos reutilizá-lo.
 * Isto evita que links de e-mails disparados anteriormente fiquem "mortos".
 */
$token_para_envio = $userData['token_verificacao'];

if (empty($token_para_envio)) {
    // Apenas geramos um novo se não houver nenhum registado
    $token_para_envio = UserLogic::gerarTokenVerificacao();
    
    // Atualizamos o token e a data do aviso no banco (Atomicamente via MySQLi)
    $sql_update = "UPDATE Usuarios 
                   SET token_verificacao = ?, 
                       data_ultimo_aviso_verificacao = NOW() 
                   WHERE id = ?";

    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("si", $token_para_envio, $user_id);
} else {
    // Se já existe um token, apenas atualizamos a data do aviso para renovar a trava de 24h
    $sql_update = "UPDATE Usuarios 
                   SET data_ultimo_aviso_verificacao = NOW() 
                   WHERE id = ?";

    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("i", $user_id);
}

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Erro interno ao processar a segurança da conta.']);
    exit;
}
$stmt->close();

// 5. --- [DISPARO VIA MOTOR CENTRAL] ---
/**
 * Construímos o link usando a constante base_url obrigatória.
 * O envio é delegado à EmailLogic, que usa os dados do config/mail.php.
 */
$link_verificacao = $config['base_url'] . "verificar-email?token=" . $token_para_envio;

$emailService = new EmailLogic($pdo);
$enviou = $emailService->enviarLinkVerificacao($userData['email'], $userData['nome'], $link_verificacao);

if ($enviou) {
    echo json_encode([
        'success' => true, 
        'message' => 'Link de verificação enviado! Verifique sua caixa de entrada (e a pasta de spam).'
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'O serviço de e-mail está temporariamente instável. Tente novamente mais tarde.'
    ]);
}