<?php
/**
 * api/admin/chat/buscar_auditoria.php
 * PAPEL: Scanner global de mensagens para auditoria administrativa.
 * VERSÃO: 1.3 (Fiscalização de Privadas & Origem de Dados - socialbr.lol)
 */

// --- [PASSO 1: PROTEÇÃO E CONEXÃO] ---
// Estrutura de pastas: /api/admin/chat/ -> subimos 3 níveis para alcançar a raiz e o admin_auth
require_once __DIR__ . '/../../../admin/admin_auth.php'; 

header('Content-Type: application/json');

// --- [PASSO 2: VALIDAÇÃO DE ENTRADA] ---
$termo = isset($_GET['termo']) ? trim($_GET['termo']) : '';

// Exigimos pelo menos 3 caracteres para evitar sobrecarga no banco de dados
if (strlen($termo) < 3) {
    echo json_encode([
        'sucesso' => false, 
        'erro' => 'O termo de busca deve ter pelo menos 3 caracteres.'
    ]);
    exit;
}

try {
    // --- [PASSO 3: QUERY DE VARREDURA GLOBAL COM CONTEXTO] ---
    /**
     * Realiza uma busca em todas as mensagens da plataforma.
     * Cruzamos com Usuarios para identificar o remetente.
     * ADICIONADO: JOIN com chat_conversas para identificar o tipo (privada/grupo).
     * Capturamos m.conversa_id para permitir o "Salto" para o Ghost Mode.
     */
    $sql = "SELECT m.id, m.conversa_id, m.mensagem, m.criado_em, m.midia_url, m.tipo_midia, 
                   u.nome, u.sobrenome, c.tipo AS conversa_tipo
            FROM chat_mensagens m
            JOIN Usuarios u ON m.remetente_id = u.id
            JOIN chat_conversas c ON m.conversa_id = c.id
            WHERE m.mensagem LIKE ?
            ORDER BY m.criado_em DESC
            LIMIT 100";

    $stmt = $conn->prepare($sql);
    $busca = "%" . $termo . "%";
    $stmt->bind_param("s", $busca);
    $stmt->execute();
    $result = $stmt->get_result();

    $resultados = [];
    while ($row = $result->fetch_assoc()) {
        $resultados[] = [
            'id'            => (int)$row['id'], // ID para exclusão individual
            'conversa_id'   => (int)$row['conversa_id'], // ID para o salto de auditoria
            'remetente'     => htmlspecialchars($row['nome'] . ' ' . $row['sobrenome']),
            'mensagem'      => htmlspecialchars($row['mensagem']),
            'data'          => date('d/m/Y H:i', strtotime($row['criado_em'])),
            'midia_url'     => $row['midia_url'],
            'tipo_midia'    => $row['tipo_midia'],
            'conversa_tipo' => $row['conversa_tipo'] // 'privada' ou 'grupo'
        ];
    }

    // --- [PASSO 4: RETORNO DE DADOS] ---
    echo json_encode([
        'sucesso' => true,
        'resultados' => $resultados
    ]);

    $stmt->close();

} catch (Exception $e) {
    // Log de erro silencioso no servidor e aviso genérico para a API
    error_log("Erro na Auditoria de Chat (v1.3): " . $e->getMessage());
    echo json_encode([
        'sucesso' => false, 
        'erro' => 'Falha técnica ao executar a varredura contextual no banco de dados.'
    ]);
}