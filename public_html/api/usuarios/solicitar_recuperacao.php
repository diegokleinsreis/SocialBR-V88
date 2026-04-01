<?php
/**
 * ARQUIVO: api/usuarios/solicitar_recuperacao.php
 * PAPEL: Receber o e-mail, gerir o código de 6 dígitos e disparar via EmailLogic.
 * VERSÃO: 2.1 - Saneamento e Inteligência de Reuso (socialbr.lol)
 */

// 1. --- [DEPENDÊNCIAS] ---
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/RecuperacaoLogic.php';
require_once __DIR__ . '/../../../src/EmailLogic.php';

header('Content-Type: application/json; charset=utf-8');

// 2. --- [CAPTURA E VALIDAÇÃO DE INPUT] ---
$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? null;

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Por favor, insira um e-mail válido.']);
    exit;
}

// 3. --- [PROCESSAMENTO LÓGICO] ---
$recLogic = new RecuperacaoLogic($pdo);
$usuario = $recLogic->buscarUsuarioPorEmail($email);

/**
 * REGRA DE SEGURANÇA:
 * Mesmo que o utilizador não exista, retornamos sucesso.
 * Isto impede que atacantes façam "brute-force" para descobrir e-mails registados.
 */
if (!$usuario) {
    echo json_encode([
        'success' => true, 
        'message' => 'Se o e-mail estiver registado, receberá um código em breve.'
    ]);
    exit;
}

// 4. --- [INTELIGÊNCIA DE CÓDIGO (REUSO)] ---
/**
 * Verificamos se já existe um código ativo para este utilizador.
 * Isto evita disparar vários códigos diferentes em curto espaço de tempo.
 */
$sql_check = "SELECT codigo FROM Usuarios_Recuperacao 
              WHERE usuario_id = ? AND usado = 0 AND data_expiracao > NOW() 
              ORDER BY id DESC LIMIT 1";
$stmt_check = $pdo->prepare($sql_check);
$stmt_check->execute([$usuario['id']]);
$codigo_ativo = $stmt_check->fetchColumn();

if ($codigo_ativo) {
    $codigo = $codigo_ativo;
} else {
    // Se não houver código ativo ou expirou, gera um novo de 6 dígitos (15 min)
    $codigo = $recLogic->gerarCodigoRecuperacao($usuario['id'], 15);
}

if (!$codigo) {
    echo json_encode(['success' => false, 'message' => 'Erro interno ao processar o seu pedido.']);
    exit;
}

// 5. --- [DISPARO VIA MOTOR CENTRAL] ---
$emailService = new EmailLogic($pdo);
$enviou = $emailService->enviarCodigoRecuperacao($email, $usuario['nome'], $codigo);

if ($enviou) {
    echo json_encode([
        'success' => true, 
        'message' => 'O código de segurança foi enviado para o seu e-mail.'
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'O serviço de e-mail está instável. Tente novamente em instantes.'
    ]);
}