<?php
/**
 * public_html/api/notificacoes/obter_toasts.php
 * Endpoint de entrega para o sistema de Toasts.
 * PAPEL: Ponte entre ToastLogic (PHP) e MotorToast (JS).
 * VERSÃO: 2.0 (OLED Broadcast Sync - socialbr.lol)
 */

// 1. Configurações de Cabeçalho e Supressão de Erros de Output
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

// Inicia buffer para garantir que nenhum aviso de PHP (Warnings/Notices) quebre o JSON
ob_start();

// --- [BLINDAGEM DE SESSÃO] ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    ob_clean();
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Não autorizado']);
    exit;
}

// --- [LOCALIZADOR DE CAMINHOS ROBUSTO] ---
// Busca a raiz do projeto para incluir dependências de forma segura
$baseDir = __DIR__;
while ($baseDir !== '/' && !file_exists($baseDir . '/config/database.php')) {
    $baseDir = dirname($baseDir);
}

// Inclusão de dependências obrigatórias
if (file_exists($baseDir . '/config/database.php')) {
    require_once $baseDir . '/config/database.php';
} else {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Configuração não encontrada']);
    exit;
}

require_once $baseDir . '/src/ToastLogic.php';

// --- [PROCESSAMENTO DA REQUISIÇÃO] ---

// Captura o ID do último Toast exibido no front-end para evitar duplicidade
// Se for a primeira chamada, o last_id será 0.
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
$user_id = (int)$_SESSION['user_id'];

try {
    // A variável $conn vem do database.php incluído acima.
    // O ToastLogic v3.9 já processa a injeção de Avisos_Sistema (Broadcast).
    $toasts = ToastLogic::getRecentToasts($conn, $user_id, $last_id);

    // Resposta de sucesso
    ob_clean();
    echo json_encode([
        'status' => 'success',
        'data'   => $toasts,
        'count'  => count($toasts),
        'timestamp' => time()
    ]);

} catch (Exception $e) {
    // Log para diagnóstico admin (sem vazar detalhes para o cliente)
    error_log("API Toast Error: " . $e->getMessage());
    
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro interno ao processar notificações'
    ]);
}