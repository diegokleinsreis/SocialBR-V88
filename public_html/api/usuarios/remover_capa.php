<?php
/**
 * api/usuarios/remover_capa.php
 * PAPEL: Processar a remo«®«ªo l«Ñgica da foto de capa.
 * VERSªªO: 1.1 (Security Fix - socialbr.lol)
 */
session_start();
header('Content-Type: application/json');

// 1. VALIDAª®ªªO DE SESSªªO
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'erro' => 'Sess«ªo expirada. Por favor, fa«®a login novamente.']);
    exit;
}

require_once __DIR__ . '/../../../config/database.php';

// 2. ACEITA APENAS POST (Para maior seguran«®a)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'erro' => 'M«±todo n«ªo permitido.']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

try {
    // 3. ATUALIZAª®ªªO NO BANCO DE DADOS
    // Conforme klscom_social.sql, a coluna correta «± foto_capa_url
    $sql = "UPDATE Usuarios SET foto_capa_url = NULL WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Capa removida com sucesso!']);
    } else {
        throw new Exception("Erro ao atualizar o banco de dados.");
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'erro' => $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}