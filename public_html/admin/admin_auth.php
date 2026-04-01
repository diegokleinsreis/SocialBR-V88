<?php
/**
 * admin/admin_auth.php
 * Middleware de Segurança (Guarda de Acesso).
 * PAPEL: Validar sessão administrativa, prover conexão e motor de logs.
 * VERSÃO: 52.0 (Audit Log Integration - socialbr.lol)
 */

// 1. INÍCIO DA SESSÃO
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. CONEXÃO COM O BANCO (FORA DA PUBLIC_HTML)
// O database está em /config (fora da public_html).
// Saltos: (../) sai de 'admin' para 'public_html' -> (../) sai de 'public_html' para a raiz.
$db_path = __DIR__ . '/../../config/database.php';

if (file_exists($db_path)) {
    require_once $db_path;
} else {
    // Log de erro técnico para auxiliar o diagnóstico se o path falhar
    die("Erro Crítico de Infraestrutura: O banco de dados não foi localizado em: " . realpath($db_path));
}

// 3. CARREGAMENTO DO MOTOR DE LOGS (FORA DA PUBLIC_HTML)
// Carrega o cérebro de logs que criámos anteriormente.
$logs_path = __DIR__ . '/../../src/LogsLogic.php';
if (file_exists($logs_path)) {
    require_once $logs_path;
}

// 4. REGRA DE SEGURANÇA MESTRE
// 1. O usuário precisa estar logado (ter um 'user_id' na sessão).
// 2. E o 'role' do usuário na sessão precisa ser exatamente 'admin'.
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    
    // Redireciona para a página de login principal usando o base_path dinâmico.
    $login_url = (isset($config['base_path']) ? $config['base_path'] : '/') . "login";
    header("Location: " . $login_url);
    exit(); 
}

/**
 * 5. FUNÇÃO GLOBAL DE AUDITORIA: admin_log
 * Este é um atalho para registrar ações sem ter que fazer SQL manual.
 * Disponível automaticamente em todas as APIs que dão 'require_once' neste arquivo.
 * * @param string $acao Nome da ação (ex: 'banir_usuario')
 * @param string $tipo_objeto Tipo do alvo (ex: 'grupo', 'post', 'usuario')
 * @param int $id_objeto ID do item afetado
 * @param string $detalhes Texto explicativo opcional
 */
function admin_log($acao, $tipo_objeto, $id_objeto, $detalhes = '') {
    global $conn;
    $admin_id = $_SESSION['user_id'] ?? 0;
    
    if (class_exists('LogsLogic')) {
        return LogsLogic::registrar($conn, $admin_id, $acao, $tipo_objeto, $id_objeto, $detalhes);
    }
    return false;
}

// Se o script chegar aqui, o admin está validado e o banco/logs estão prontos.
?>