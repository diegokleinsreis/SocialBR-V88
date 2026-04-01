<?php
/**
 * api/marketplace/alternar_curtida.php
 * Endpoint para curtir anúncios (Sincronizado com o Feed Social)
 * Versão: 3.1 - Agora com disparador de Notificação (Toast Sync)
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();
header('Content-Type: application/json; charset=utf-8');

if (session_status() == PHP_SESSION_NONE) { session_start(); }

// Localizador de config
$pathsConfig = [
    __DIR__ . '/../../../config/database.php',
    __DIR__ . '/../../../../config/database.php'
];
foreach ($pathsConfig as $path) {
    if (file_exists($path)) {
        require_once $path;
        break;
    }
}

if (!isset($_SESSION['user_id'])) {
    ob_clean();
    echo json_encode(['sucesso' => false, 'erro' => 'Login necessário.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// 🛡️ SEGURANÇA CSRF: O Porteiro Digital
if (!isset($input['csrf_token']) || !verify_csrf_token($input['csrf_token'])) {
    ob_clean();
    echo json_encode(['sucesso' => false, 'erro' => 'Erro de segurança: Token inválido. Recarregue a página.']);
    exit;
}

$id_recebido = (int)($input['id'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($id_recebido <= 0) {
    ob_clean();
    echo json_encode(['sucesso' => false, 'erro' => 'ID inválido.']);
    exit;
}

try {
    if (!isset($pdo)) {
        $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    // 1. DESCOBRIR O ID DA POSTAGEM REAL
    $stmtFind = $pdo->prepare("SELECT id_postagem FROM Marketplace_Anuncios WHERE id = ?");
    $stmtFind->execute([$id_recebido]);
    $anuncio = $stmtFind->fetch(PDO::FETCH_ASSOC);

    $post_id_real = $anuncio ? $anuncio['id_postagem'] : $id_recebido;

    // 2. TOGGLE LIKE
    $stmtCheck = $pdo->prepare("SELECT id FROM Curtidas WHERE id_postagem = ? AND id_usuario = ?");
    $stmtCheck->execute([$post_id_real, $user_id]);
    
    if ($stmtCheck->rowCount() > 0) {
        // Remover curtida
        $pdo->prepare("DELETE FROM Curtidas WHERE id_postagem = ? AND id_usuario = ?")->execute([$post_id_real, $user_id]);
        $curtiu = false;
    } else {
        // Adicionar curtida
        $pdo->prepare("INSERT INTO Curtidas (id_postagem, id_usuario, data_curtida) VALUES (?, ?, NOW())")->execute([$post_id_real, $user_id]);
        $curtiu = true;

        // --- INÍCIO DA LÓGICA DE NOTIFICAÇÃO ---
        // 1. Descobrir quem é o dono da postagem original
        $stmtOwner = $pdo->prepare("SELECT id_usuario FROM Postagens WHERE id = ?");
        $stmtOwner->execute([$post_id_real]);
        $owner_id = $stmtOwner->fetchColumn();

        // 2. Inserir notificação se o dono não for quem curtiu
        if ($owner_id && $owner_id != $user_id) {
            $stmtNotif = $pdo->prepare("
                INSERT INTO notificacoes (usuario_id, remetente_id, tipo, id_referencia, lida, data_criacao)
                VALUES (?, ?, 'curtida', ?, 0, NOW())
            ");
            // id_referencia aponta para o post_id_real para o link do Toast funcionar
            $stmtNotif->execute([$owner_id, $user_id, $post_id_real]);
        }
        // --- FIM DA LÓGICA DE NOTIFICAÇÃO ---
    }

    // 3. CONTAR TOTAIS
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM Curtidas WHERE id_postagem = ?");
    $stmtCount->execute([$post_id_real]);
    $total = $stmtCount->fetchColumn();

    ob_clean();
    echo json_encode([
        'sucesso' => true, 
        'curtiu' => $curtiu, 
        'total' => $total,
        'debug_post_id' => $post_id_real
    ]);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['sucesso' => false, 'erro' => 'Erro interno: ' . $e->getMessage()]);
}
ob_end_flush();
?>