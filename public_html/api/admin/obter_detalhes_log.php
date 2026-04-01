<?php
/**
 * api/admin/obter_detalhes_log.php
 * PAPEL: Fornecer dados completos de um log específico para o Modal.
 * LOCALIZAÇÃO: public_html/api/admin/
 */

// 1. CARREGAMENTO DE SEGURANÇA E CONEXÃO
// Sobe 3 níveis para encontrar o admin_auth.php
require_once __DIR__ . '/../../admin/admin_auth.php';

header('Content-Type: application/json');

// 2. VALIDAÇÃO DO ID
$log_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($log_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de log inválido.']);
    exit;
}

try {
    // 3. CONSULTA DETALHADA
    // Fazemos um JOIN com Usuarios para saber quem foi o admin
    $sql = "SELECT l.*, u.nome, u.sobrenome, u.foto_perfil_url 
            FROM Logs_Admin l
            JOIN Usuarios u ON l.admin_id = u.id
            WHERE l.id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $log_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $log = $resultado->fetch_assoc();

    if (!$log) {
        echo json_encode(['success' => false, 'message' => 'Registo não encontrado.']);
        exit;
    }

    // 4. FORMATAÇÃO PARA O MODAL
    // Preparamos uma resposta limpa para o JavaScript processar
    $dados_log = [
        'id'           => $log['id'],
        'admin_nome'   => $log['nome'] . ' ' . $log['sobrenome'],
        'admin_foto'   => $config['base_path'] . ($log['foto_perfil_url'] ?: 'assets/img/default_avatar.png'),
        'acao'         => strtoupper(str_replace('_', ' ', $log['acao'])),
        'tipo'         => strtoupper($log['tipo_objeto']),
        'id_alvo'      => $log['id_objeto'],
        'detalhes'     => $log['detalhes'], // Aqui o texto vem completo!
        'data'         => date('d/m/Y - H:i:s', strtotime($log['data_log']))
    ];

    echo json_encode([
        'success' => true,
        'log'     => $dados_log
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Erro interno: ' . $e->getMessage()
    ]);
}

$conn->close();