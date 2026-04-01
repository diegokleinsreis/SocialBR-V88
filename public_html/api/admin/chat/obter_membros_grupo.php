<?php
/**
 * api/admin/chat/obter_membros_grupo.php
 * PAPEL: Listagem de participantes de um grupo para gestão administrativa.
 * VERSÃO: 1.0 (Componentizado - socialbr.lol)
 */

// --- [PASSO 1: PROTEÇÃO E CONEXÃO] ---
// Subimos 3 níveis para alcançar a raiz da public_html e encontrar o admin_auth
require_once __DIR__ . '/../../../admin/admin_auth.php'; 

/**
 * CORREÇÃO DE CAMINHO (Dica de Ouro):
 * ChatLogic está fora da pasta pública. 
 * api/ (1) -> admin/ (2) -> chat/ (3) -> public_html/ (4) -> raiz/
 */
require_once __DIR__ . '/../../../../src/ChatLogic.php';

header('Content-Type: application/json');

// --- [PASSO 2: VALIDAÇÃO DE ENTRADA] ---
$conversa_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($conversa_id <= 0) {
    echo json_encode([
        'sucesso' => false, 
        'erro' => 'ID da conversa/grupo inválido.'
    ]);
    exit;
}

try {
    // --- [PASSO 3: CONSULTA VIA CÉREBRO DO CHAT] ---
    /**
     * Utilizamos o método estático getGroupMembers para extrair a lista oficial.
     * Esta lista já inclui nomes, fotos e quem é o atual dono (coroa).
     */
    $membros = ChatLogic::getGroupMembers($conn, $conversa_id);

    // --- [PASSO 4: RETORNO DOS DADOS] ---
    if ($membros !== false) {
        echo json_encode([
            'sucesso' => true,
            'membros' => $membros
        ]);
    } else {
        echo json_encode([
            'sucesso' => false, 
            'erro' => 'Não foi possível localizar os membros deste grupo.'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'sucesso' => false, 
        'erro' => 'Erro interno ao processar a listagem de membros.'
    ]);
}