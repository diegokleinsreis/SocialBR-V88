<?php
/**
 * api/notificacoes/buscar_notificacoes.php
 * Endpoint: Busca de Notificações com suporte a Metadados, Agrupamento e Broadcast.
 * PAPEL: Retornar alertas recentes agrupados priorizando NÃO LIDAS.
 * VERSÃO: 2.0 (Full Name Sync & Convite Chat Support - socialbr.lol)
 */

// 1. Configurações de Cabeçalho e Erros
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

ob_start();
session_start();

// 2. Verificação de Login
if (!isset($_SESSION['user_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Usuário não autenticado']);
    exit;
}

$user_id = $_SESSION['user_id'];

// 3. Conexão com o Banco de Dados
$caminhos_possiveis = [
    __DIR__ . '/../../config/database.php',
    $_SERVER['DOCUMENT_ROOT'] . '/config/database.php',
    dirname($_SERVER['DOCUMENT_ROOT']) . '/config/database.php'
];

$conn = null;
foreach ($caminhos_possiveis as $caminho) {
    if (file_exists($caminho)) {
        require_once $caminho;
        break;
    }
}

if (!isset($conn) || $conn->connect_error) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Erro fatal: Conexão não encontrada.']);
    exit;
}
$conn->set_charset("utf8mb4");

try {
    $lista_final = [];

    // ----------------------------------------------------------------
    // PASSO 1: Buscar Avisos Globais (Broadcast)
    // ----------------------------------------------------------------
    $sql_broadcast = "
        SELECT 
            s.id, 
            'broadcast' as tipo, 
            s.titulo as remetente_nome, 
            s.mensagem as grupo_nome, 
            s.cor_preset as id_referencia,
            s.data_criacao,
            0 as lida,
            1 as total_agrupado,
            NULL as remetente_sobrenome,
            NULL as remetente_foto
        FROM Avisos_Sistema s
        WHERE s.data_expiracao > NOW()
        AND s.id NOT IN (SELECT id_aviso FROM Avisos_Lidos WHERE id_usuario = ?)
        ORDER BY s.data_criacao DESC
    ";
    
    $stmt_b = $conn->prepare($sql_broadcast);
    $stmt_b->bind_param("i", $user_id);
    $stmt_b->execute();
    $res_b = $stmt_b->get_result();
    while ($aviso = $res_b->fetch_assoc()) {
        $lista_final[] = $aviso;
    }
    $stmt_b->close();

    // ----------------------------------------------------------------
    // PASSO 2: Buscar Notificações (Priorizando Não Lidas)
    // ----------------------------------------------------------------
    // Sincronizado com NotificationLogic: Busca em Grupos e Chat_Conversas
    $sql_lista = "
        SELECT 
            MAX(n.id) as id, 
            n.tipo, 
            n.id_referencia, 
            MIN(n.lida) as lida, 
            MAX(n.data_criacao) as data_criacao,
            u.nome AS remetente_nome,
            u.sobrenome AS remetente_sobrenome,
            u.foto_perfil_url AS remetente_foto,
            COALESCE(g.nome, cc.titulo) AS grupo_nome,
            COUNT(*) as total_agrupado
        FROM notificacoes n
        LEFT JOIN Usuarios u ON n.remetente_id = u.id
        LEFT JOIN Grupos g ON (n.id_referencia = g.id AND n.tipo IN (
            'convite_grupo', 'solicitacao_grupo', 'aceite_solicitacao_grupo',
            'promocao_moderador', 'rebaixamento_membro', 'transferencia_dono', 
            'expulsao_grupo', 'aceite_convite_grupo'
        ))
        LEFT JOIN chat_conversas cc ON (n.id_referencia = cc.id AND n.tipo = 'convite_chat_grupo')
        WHERE n.usuario_id = ?
        GROUP BY n.remetente_id, n.tipo, n.id_referencia
        ORDER BY lida ASC, id DESC 
        LIMIT 40
    ";

    $stmt = $conn->prepare($sql_lista);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $lista_final[] = $row;
    }
    $stmt->close();

    // Re-ordena o array combinado para visualização cronológica inteligente
    usort($lista_final, function($a, $b) {
        if ($a['lida'] != $b['lida']) {
            return $a['lida'] <=> $b['lida'];
        }
        return strtotime($b['data_criacao']) <=> strtotime($a['data_criacao']);
    });

    // ----------------------------------------------------------------
    // PASSO 3: Contagem Real para o Badge (Sincronia Total)
    // ----------------------------------------------------------------
    $sql_count = "SELECT COUNT(*) as total FROM notificacoes WHERE usuario_id = ? AND lida = 0";
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param("i", $user_id);
    $stmt_count->execute();
    $res_count = $stmt_count->get_result();
    $total_nao_lidas = $res_count->fetch_assoc()['total'];
    $stmt_count->close();

    // Adiciona Broadcasts não lidos à contagem
    $broadcast_nao_lidos = count(array_filter($lista_final, fn($item) => $item['tipo'] === 'broadcast'));
    $total_nao_lidas += $broadcast_nao_lidos;

    // ----------------------------------------------------------------
    // RESPOSTA FINAL
    // ----------------------------------------------------------------
    ob_clean();
    echo json_encode([
        'success' => true,
        'notificacoes' => array_slice($lista_final, 0, 20), 
        'nao_lidas' => (int)$total_nao_lidas
    ]);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()]);
}