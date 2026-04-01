<?php
/**
 * api/admin/chat/obter_estatisticas.php
 * PAPEL: Fornecer dados numéricos em tempo real para o Dashboard de Chat.
 * VERSÃO: 1.0 (Componentizado - socialbr.lol)
 */

// --- [PASSO 1: PROTEÇÃO E CONEXÃO] ---
// Subimos 3 níveis para alcançar a raiz e o admin_auth
require_once __DIR__ . '/../../../admin/admin_auth.php'; 

header('Content-Type: application/json');

try {
    // --- [PASSO 2: EXECUÇÃO DAS QUERIES DE PERFORMANCE] ---
    
    // 2.1 - Total histórico de mensagens
    $total_msgs = $conn->query("SELECT COUNT(*) FROM chat_mensagens")->fetch_row()[0] ?? 0;

    // 2.2 - Total de comunidades (grupos) criadas
    $total_grupos = $conn->query("SELECT COUNT(*) FROM chat_conversas WHERE tipo = 'grupo'")->fetch_row()[0] ?? 0;

    // 2.3 - Volume de mensagens apenas no dia de hoje
    $hoje_msgs = $conn->query("SELECT COUNT(*) FROM chat_mensagens WHERE DATE(criado_em) = CURDATE()")->fetch_row()[0] ?? 0;

    // 2.4 - Utilizadores únicos que interagiram nas últimas 24 horas
    $ativos_24h = $conn->query("SELECT COUNT(DISTINCT remetente_id) FROM chat_mensagens WHERE criado_em >= (NOW() - INTERVAL 24 HOUR)")->fetch_row()[0] ?? 0;

    // --- [PASSO 3: RETORNO ESTRUTURADO] ---
    echo json_encode([
        'sucesso' => true,
        'dados' => [
            'total_mensagens' => number_format($total_msgs, 0, ',', '.'),
            'total_grupos'    => $total_grupos,
            'mensagens_hoje'  => $hoje_msgs,
            'usuarios_ativos' => $ativos_24h
        ]
    ]);

} catch (Exception $e) {
    // Log de segurança em caso de falha na base de dados
    error_log("Erro Stats Chat Admin: " . $e->getMessage());
    echo json_encode([
        'sucesso' => false, 
        'erro' => 'Não foi possível extrair os dados de tráfego.'
    ]);
}