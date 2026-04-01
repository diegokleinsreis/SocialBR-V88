<?php
/**
 * api/marketplace/alternar_interesse.php
 * Endpoint para Gerenciar Fila de Espera com SISTEMA DE NOTIFICAÇÃO
 * Versão: 2.3 - Padronização Global de Tipos (socialbr.lol)
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();
header('Content-Type: application/json; charset=utf-8');

if (session_status() == PHP_SESSION_NONE) { session_start(); }

// 1. INCLUSÃO ROBUSTA
$pathsConfig = [
    __DIR__ . '/../../../config/database.php',
    __DIR__ . '/../../../../config/database.php'
];
foreach ($pathsConfig as $path) {
    if (file_exists($path)) { 
        require_once $path; 
        // IMPORTANTE: Inclui o dicionário de tipos de notificação
        $configPath = dirname($path) . '/tipos_notificacoes.php';
        if (file_exists($configPath)) { require_once $configPath; }
        break; 
    }
}

if (!isset($_SESSION['user_id'])) {
    ob_clean();
    echo json_encode(['sucesso' => false, 'erro' => 'Login necessário.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// 🛡️ SEGURANÇA CSRF: O Guardião do JSON
if (!isset($input['csrf_token']) || !verify_csrf_token($input['csrf_token'])) {
    ob_clean();
    echo json_encode(['sucesso' => false, 'erro' => 'Erro de segurança: Token inválido. Recarregue a página.']);
    exit;
}

$anuncio_id = (int)($input['id'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($anuncio_id <= 0) {
    ob_clean();
    echo json_encode(['sucesso' => false, 'erro' => 'ID inválido.']);
    exit;
}

try {
    if (!isset($pdo)) {
        $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    // 2. GARANTIA DE INFRAESTRUTURA
    $pdo->exec("CREATE TABLE IF NOT EXISTS Marketplace_Interesses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_anuncio INT NOT NULL,
        id_usuario INT NOT NULL,
        data_interesse DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_interesse (id_anuncio, id_usuario)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 3. TOGGLE INTERESSE
    $stmtCheck = $pdo->prepare("SELECT id FROM Marketplace_Interesses WHERE id_anuncio = ? AND id_usuario = ?");
    $stmtCheck->execute([$anuncio_id, $user_id]);

    if ($stmtCheck->rowCount() > 0) {
        // --- REMOVER INTERESSE ---
        $pdo->prepare("DELETE FROM Marketplace_Interesses WHERE id_anuncio = ? AND id_usuario = ?")->execute([$anuncio_id, $user_id]);
        
        // Limpar notificação não lida usando a constante padronizada
        $stmtDelNotif = $pdo->prepare("DELETE FROM notificacoes WHERE tipo = ? AND id_referencia = ? AND remetente_id = ? AND lida = 0");
        $stmtDelNotif->execute([NOTIF_INTERESSE_MKT, $anuncio_id, $user_id]);
        
        $interessado = false;
    } else {
        // --- ADICIONAR INTERESSE ---
        $pdo->prepare("INSERT INTO Marketplace_Interesses (id_anuncio, id_usuario) VALUES (?, ?)")->execute([$anuncio_id, $user_id]);
        $interessado = true;

        // 4. NOTIFICAR VENDEDOR
        $stmtDono = $pdo->prepare("
            SELECT p.id_usuario 
            FROM Marketplace_Anuncios ma
            INNER JOIN Postagens p ON ma.id_postagem = p.id
            WHERE ma.id = ?
        ");
        $stmtDono->execute([$anuncio_id]);
        $donoId = $stmtDono->fetchColumn();

        if ($donoId && $donoId != $user_id) {
            // USANDO A CONSTANTE PADRONIZADA PARA O INSERT
            $stmtNotif = $pdo->prepare("
                INSERT INTO notificacoes (usuario_id, remetente_id, tipo, id_referencia, data_criacao)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmtNotif->execute([$donoId, $user_id, NOTIF_INTERESSE_MKT, $anuncio_id]);
        }
    }

    // 5. CONTAGEM ATUALIZADA
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM Marketplace_Interesses WHERE id_anuncio = ?");
    $stmtCount->execute([$anuncio_id]);
    $total = $stmtCount->fetchColumn();

    ob_clean();
    echo json_encode([
        'sucesso' => true, 
        'interessado' => $interessado, 
        'total' => $total
    ]);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['sucesso' => false, 'erro' => 'Erro interno: ' . $e->getMessage()]);
}
ob_end_flush();
?>