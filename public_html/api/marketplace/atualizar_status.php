<?php
/**
 * api/marketplace/atualizar_status.php
 * Versão: 10.0 - SQL Direto (Sem dependência de Logic externa)
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();
header('Content-Type: application/json; charset=utf-8');

if (session_status() == PHP_SESSION_NONE) { session_start(); }

// Ajuste o caminho conforme sua estrutura
require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    ob_clean();
    echo json_encode(['sucesso' => false, 'erro' => 'Login necessário.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$anuncio_id = (int)($input['id'] ?? 0);
$novo_status = $input['status'] ?? ''; // 'vendido' ou 'disponivel'

// Validação básica dos dados recebidos
if ($anuncio_id <= 0 || !in_array($novo_status, ['vendido', 'disponivel', 'reservado'])) {
    ob_clean();
    echo json_encode(['sucesso' => false, 'erro' => 'Dados inválidos.']);
    exit;
}

try {
    if (!isset($pdo)) {
        $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    // 1. VERIFICAR DONO E PEGAR ID_POSTAGEM
    // É crucial verificar se o usuário logado é dono do POST associado ao anúncio
    $stmtDono = $pdo->prepare("
        SELECT ma.id, ma.id_postagem 
        FROM Marketplace_Anuncios ma
        JOIN Postagens p ON ma.id_postagem = p.id
        WHERE ma.id = ? AND p.id_usuario = ?
    ");
    $stmtDono->execute([$anuncio_id, $_SESSION['user_id']]);
    $dados = $stmtDono->fetch(PDO::FETCH_ASSOC);

    if (!$dados) {
        throw new Exception("Permissão negada ou anúncio não encontrado.");
    }

    $pdo->beginTransaction();

    // 2. ATUALIZAR STATUS NO MARKETPLACE
    // Executa a mudança de status diretamente na tabela certa
    $stmtUpd = $pdo->prepare("UPDATE Marketplace_Anuncios SET status_venda = ? WHERE id = ?");
    $stmtUpd->execute([$novo_status, $anuncio_id]);

    // 3. ATUALIZAR O POST PARA REFLETIR NO FEED
    // "Tocar" no post (atualizar timestamp) ajuda a limpar caches de visualização se houver
    // e garante integridade referencial lógica.
    $stmtTouch = $pdo->prepare("UPDATE Postagens SET data_postagem = data_postagem WHERE id = ?");
    $stmtTouch->execute([$dados['id_postagem']]);

    $pdo->commit();

    ob_clean();
    echo json_encode(['sucesso' => true, 'novo_status' => $novo_status]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    ob_clean();
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}
ob_end_flush();
?>