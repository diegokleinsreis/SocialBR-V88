<?php
/**
 * ARQUIVO: api/marketplace/excluir_anuncio.php
 * PAPEL: Realizar o "Soft Delete" (exclusão lógica) de anúncios no Marketplace.
 * VERSÃO: 5.6 - Caminhos Fixos e Padronização Arquiteto (socialbr.lol)
 */

// 1. --- [CONFIGURAÇÕES DE AMBIENTE] ---
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Buffer de saída para evitar problemas com headers
ob_start();

// Define o cabeçalho da resposta como JSON com suporte a caracteres especiais
header('Content-Type: application/json; charset=utf-8');

if (session_status() == PHP_SESSION_NONE) { 
    session_start(); 
}

// 2. --- [DEPENDÊNCIAS E CONEXÃO (Caminhos Diretos)] ---
try {
    // Sobe para api/ -> public_html/ -> raiz e entra em config/
    require_once __DIR__ . '/../../../config/database.php';
    
    // Sobe para api/ -> public_html/ -> raiz e entra em src/
    require_once __DIR__ . '/../../../src/MarketplaceLogic.php';

    // Inicializa PDO se não estiver definido
    if (!isset($pdo)) {
        $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Erro de configuração: ' . $e->getMessage()]);
    exit;
}

// 3. --- [VERIFICAÇÃO DE SESSÃO] ---
if (!isset($_SESSION['user_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

// 4. --- [LEITURA DE DADOS E SEGURANÇA CSRF] ---
// Captura o payload JSON enviado pelo JavaScript
$input = json_decode(file_get_contents('php://input'), true);

// 🛡️ SEGURANÇA CSRF: O Guardião
if (!isset($input['csrf_token']) || !verify_csrf_token($input['csrf_token'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Erro de segurança: Token inválido ou expirado.']);
    exit;
}

$anuncio_id = (int)($input['id'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($anuncio_id <= 0) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'ID do anúncio inválido.']);
    exit;
}

try {
    // 5. --- [EXECUÇÃO VIA MARKETPLACELOGIC] ---
    // Instanciamos a classe de lógica passando a conexão PDO
    $mktLogic = new MarketplaceLogic($pdo);
    
    /**
     * O método excluirAnuncio realiza a exclusão lógica (status = 'excluido').
     * Ele internamente valida se o $user_id é realmente o dono do anúncio.
     */
    $sucesso = $mktLogic->excluirAnuncio($anuncio_id, $user_id);

    if ($sucesso) {
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Anúncio removido com sucesso.'
        ]);
    } else {
        throw new Exception("Não foi possível excluir. Verifique se tem permissão sobre este anúncio.");
    }

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Envia o conteúdo do buffer e encerra
ob_end_flush();