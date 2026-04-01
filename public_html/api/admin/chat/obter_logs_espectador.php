<?php
/**
 * api/admin/chat/obter_logs_espectador.php
 * PAPEL: Extração de histórico para auditoria administrativa (Ghost Mode).
 * VERSÃO: 1.1 (Suporte a Multimédia - socialbr.lol)
 */

// --- [PASSO 1: PROTEÇÃO E CONEXÃO] ---
require_once __DIR__ . '/../../../admin/admin_auth.php'; 

header('Content-Type: application/json');

// --- [PASSO 2: VALIDAÇÃO DE ENTRADA] ---
$conversa_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($conversa_id <= 0) {
    echo json_encode(['sucesso' => false, 'erro' => 'ID da conversa inválido.']);
    exit;
}

try {
    // --- [PASSO 3: QUERY DE AUDITORIA ATUALIZADA] ---
    /**
     * Adicionamos m.midia_url e m.tipo_midia para capturar arquivos.
     * Continuamos sem tocar na tabela chat_participantes para manter o sigilo (Ghost Mode).
     */
    $sql = "SELECT m.mensagem, m.criado_em, m.midia_url, m.tipo_midia, u.nome, u.sobrenome 
            FROM chat_mensagens m
            JOIN Usuarios u ON m.remetente_id = u.id
            WHERE m.conversa_id = ?
            ORDER BY m.criado_em ASC
            LIMIT 100";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $conversa_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $mensagens = [];
    while ($row = $result->fetch_assoc()) {
        $mensagens[] = [
            'remetente'  => htmlspecialchars($row['nome'] . ' ' . $row['sobrenome']),
            'texto'      => htmlspecialchars($row['mensagem']),
            'data'       => date('d/m H:i', strtotime($row['criado_em'])),
            'midia_url'  => $row['midia_url'], // URL bruta para processamento no JS
            'tipo_midia' => $row['tipo_midia'] // texto, foto, video ou audio
        ];
    }

    echo json_encode([
        'sucesso' => true,
        'mensagens' => $mensagens
    ]);

} catch (Exception $e) {
    echo json_encode([
        'sucesso' => false, 
        'erro' => 'Erro interno ao processar logs de mídia.'
    ]);
}