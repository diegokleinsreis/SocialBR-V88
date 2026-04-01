<?php
/**
 * api/admin/chat/transferir_coroa.php
 * PAPEL: Transferência administrativa de propriedade de grupo (Coroa).
 * VERSÃO: 1.0 (Componentizado - socialbr.lol)
 */

// --- [PASSO 1: PROTEÇÃO E CONEXÃO] ---
// Subimos 3 níveis para alcançar a raiz e o admin_auth: chat -> admin -> api -> raiz
require_once __DIR__ . '/../../../admin/admin_auth.php'; 

/**
 * CORREÇÃO DE CAMINHO:
 * ChatLogic está em /src/ChatLogic.php (fora da public_html).
 */
require_once __DIR__ . '/../../../../src/ChatLogic.php';

header('Content-Type: application/json');

// --- [PASSO 2: CAPTURA DOS DADOS JSON] ---
$input = json_decode(file_get_contents('php://input'), true);

$id_conversa = isset($input['conversa_id']) ? (int)$input['conversa_id'] : 0;
$id_novo_dono = isset($input['usuario_id']) ? (int)$input['usuario_id'] : 0;

if ($id_conversa <= 0 || $id_novo_dono <= 0) {
    echo json_encode(['sucesso' => false, 'erro' => 'Dados de transferência incompletos.']);
    exit;
}

try {
    // --- [PASSO 3: EXECUÇÃO DA TRANSFERÊNCIA] ---
    /**
     * Utilizamos o método transferOwnership do ChatLogic.
     * Ele valida se o novo dono é um participante real do grupo antes de atualizar.
     */
    $sucesso = ChatLogic::transferOwnership($conn, $id_conversa, $id_novo_dono);

    if ($sucesso) {
        echo json_encode([
            'sucesso' => true, 
            'mensagem' => 'Propriedade do grupo transferida com sucesso!'
        ]);
    } else {
        echo json_encode([
            'sucesso' => false, 
            'erro' => 'Falha ao transferir coroa. Verifique se o utilizador pertence ao grupo.'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'sucesso' => false, 
        'erro' => 'Erro interno na API de hierarquia.'
    ]);
}