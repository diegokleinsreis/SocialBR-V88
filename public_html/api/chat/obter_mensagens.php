<?php
/**
 * api/chat/obter_mensagens.php
 * Endpoint: Recuperação dinâmica de mensagens com Identidade de Remetente.
 * PAPEL: Retornar histórico de mensagens enriquecido com fotos/nomes e contador de novidades.
 * VERSÃO: V61.0 (Identidade de Grupos & JOIN Usuarios - socialbr.lol)
 */

header('Content-Type: application/json');

// 1. Inicialização e Segurança
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * CAMINHOS DE SEGURANÇA:
 * Conforme instrução: config e src estão um nível acima da public_html.
 * api/chat/ (0) -> api/ (1) -> public_html/ (2) -> root/ (3)
 */
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/ChatLogic.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sessão expirada.']);
    exit;
}

$user_id_logado = $_SESSION['user_id'];
$conversa_id = (int)($_GET['conversa_id'] ?? 0);

if ($conversa_id <= 0) {
    echo json_encode(['sucesso' => false, 'erro' => 'ID de conversa inválido.']);
    exit;
}

// 2. Validação de Pertença e Contexto de Leitura
$sql_permissao = "SELECT ultima_leitura_at FROM chat_participantes 
                  WHERE conversa_id = ? AND usuario_id = ?";
$stmt_perm = $conn->prepare($sql_permissao);
$stmt_perm->bind_param("ii", $conversa_id, $user_id_logado);
$stmt_perm->execute();
$resultado_perm = $stmt_perm->get_result()->fetch_assoc();

if (!$resultado_perm) {
    echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado à conversa.']);
    exit;
}

$ultima_leitura = $resultado_perm['ultima_leitura_at'];

/**
 * 3. RECUPERAÇÃO DAS MENSAGENS V61.0:
 * JOIN com Usuarios para obter dados visuais do remetente (Essencial para Grupos).
 *
 */
$sql_msgs = "SELECT 
                m.id, 
                m.remetente_id, 
                m.mensagem, 
                m.midia_url, 
                m.tipo_midia, 
                m.criado_em,
                u.nome AS remetente_nome,
                u.foto_perfil_url AS remetente_avatar
             FROM chat_mensagens m
             JOIN Usuarios u ON m.remetente_id = u.id
             WHERE m.conversa_id = ? 
             ORDER BY m.criado_em ASC";

$stmt_msgs = $conn->prepare($sql_msgs);
$stmt_msgs->bind_param("i", $conversa_id);
$stmt_msgs->execute();
$result_msgs = $stmt_msgs->get_result();

$mensagens = [];
$novas_count = 0;

while ($msg = $result_msgs->fetch_assoc()) {
    // Contagem de novas mensagens para o Heartbeat
    if ($msg['remetente_id'] != $user_id_logado && $msg['criado_em'] > $ultima_leitura) {
        $novas_count++;
    }
    
    $mensagens[] = [
        'id' => (int)$msg['id'],
        'remetente_id' => (int)$msg['remetente_id'],
        'remetente_nome' => htmlspecialchars($msg['remetente_nome']), // [V61.0] Nome para bolha
        'remetente_avatar' => $msg['remetente_avatar'],               // [V61.0] Foto para bolha
        'mensagem' => $msg['mensagem'],
        'midia_url' => $msg['midia_url'],
        'tipo_midia' => $msg['tipo_midia'],
        'criado_em' => $msg['criado_em']
    ];
}

// 4. Resposta JSON completa para o chat_visual.js
echo json_encode([
    'sucesso' => true,
    'mensagens' => $mensagens,
    'novas' => (int)$novas_count
]);