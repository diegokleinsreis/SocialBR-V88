<?php
/**
 * FICHEIRO: api/admin/limpar_sql_audit.php
 * PAPEL: API de Manutenção para o SQL Audit Log (Versão Ultra-Resiliente).
 * VERSÃO: 1.3 (Audit Logs Integration - socialbr.lol)
 */

// 1. CARREGAMENTO DO AMBIENTE
require_once __DIR__ . '/../../../config/database.php';
// Carregamos a lógica de logs para registar a ação na base de dados
require_once __DIR__ . '/../../../src/LogsLogic.php';

header('Content-Type: application/json');

// 2. VALIDAÇÃO DE PODERES (Nível 1: Administrativo)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acesso negado: Requer privilégios de administrador.']);
    exit;
}

// 3. CAPTURA RESILIENTE DO TOKEN (Nível 2: Segurança CSRF)
$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

if (empty($csrfToken) && function_exists('getallheaders')) {
    $allHeaders = getallheaders();
    $csrfToken = $allHeaders['X-CSRF-TOKEN'] ?? $allHeaders['x-csrf-token'] ?? '';
}

// Validação final do Token
if (empty($csrfToken) || !verify_csrf_token($csrfToken)) {
    http_response_code(403);
    echo json_encode([
        'success' => false, 
        'error' => 'Falha de segurança: Token CSRF inválido, expirado ou não enviado.',
        'debug_received' => !empty($csrfToken) ? 'Token presente' : 'Token ausente'
    ]);
    exit;
}

// 4. DEFINIÇÃO DO CAMINHO DO LOG
$logFile = __DIR__ . '/../../../config/sql_audit.log';

try {
    // 5. OPERAÇÃO DE LIMPEZA
    $adminId = $_SESSION['user_id'] ?? 0;
    $timestamp = date('Y-m-d H:i:s');
    
    // Cabeçalho de reinicialização para o ficheiro físico
    $initialEntry = "[LOG RESET] Histórico limpo pelo Administrador #{$adminId} em {$timestamp}" . PHP_EOL;

    // file_put_contents com LOCK_EX garante que ninguém escreva no log enquanto limpamos
    if (file_put_contents($logFile, $initialEntry, LOCK_EX) !== false) {
        
        // --- REGISTO NA AUDITORIA DA BASE DE DADOS (LOGS_ADMIN) ---
        $detalhe_claro = "Limpeza total do arquivo de logs brutos (sql_audit.log). O histórico foi reiniciado e o arquivo físico sobrescrito.";
        
        // Usamos LogsLogic diretamente pois este ficheiro não carrega o admin_auth.php (para evitar o redirect de API)
        LogsLogic::registrar($conn, $adminId, 'limpar_log_bruto', 'sistema', 0, $detalhe_claro);

        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Erro de permissão: O PHP não conseguiu escrever no ficheiro em config/.");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Erro interno na limpeza: ' . $e->getMessage()
    ]);
}