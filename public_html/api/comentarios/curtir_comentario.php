<?php
/**
 * api/comentarios/curtir_comentario.php
 * VERSÃO V9.3: MySQLi Stable & SQL Case Hardening
 * PAPEL: Processar curtidas em comentários (Feed/Modal) de forma resiliente.
 * CONSTITUIÇÃO: socialbr.lol
 */

session_start();

// 1. Verificação de Identidade (Nível de Sessão)
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Acesso negado. Por favor, inicie sessão.']);
    exit();
}

// 2. Importações de Infraestrutura (Caminhos Absolutos Blindados)
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/sentinela.php'; 
require_once __DIR__ . '/../../../config/tipos_notificacoes.php'; 

// 3. Validação de Segurança CSRF (Obrigatória)
$csrf_token = $_POST['csrf_token'] ?? '';
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !verify_csrf_token($csrf_token)) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Segurança: Token inválido ou expirado. Tente recarregar.']);
    exit();
}

// 4. Captura e Sanitização de Entrada
$comment_id = (int)($_POST['comment_id'] ?? 0);
$user_id = (int)$_SESSION['user_id'];

if ($comment_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'ID do comentário inválido.']);
    exit();
}

// 5. Execução do Motor de Interação (MySQLi)
try {
    // 5.1 Verifica se o usuário já curtiu este comentário
    $sql_check = "SELECT id FROM Curtidas_Comentarios WHERE id_usuario = ? AND id_comentario = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $user_id, $comment_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // JÁ CURTIU -> REMOVER (Unlike)
        $sql_unlike = "DELETE FROM Curtidas_Comentarios WHERE id_usuario = ? AND id_comentario = ?";
        $stmt_unlike = $conn->prepare($sql_unlike);
        $stmt_unlike->bind_param("ii", $user_id, $comment_id);
        $stmt_unlike->execute();
        $curtido = false;
        $stmt_unlike->close();
    } else {
        // AINDA NÃO CURTIU -> ADICIONAR (Like)
        $sql_like = "INSERT INTO Curtidas_Comentarios (id_usuario, id_comentario) VALUES (?, ?)";
        $stmt_like = $conn->prepare($sql_like);
        $stmt_like->bind_param("ii", $user_id, $comment_id);
        $stmt_like->execute();
        $curtido = true;
        $stmt_like->close();

        // 5.2 LÓGICA DE NOTIFICAÇÃO (CamelCase Hardened)
        $sql_details = "SELECT id_usuario, id_postagem FROM Comentarios WHERE id = ?";
        $stmt_details = $conn->prepare($sql_details);
        $stmt_details->bind_param("i", $comment_id);
        $stmt_details->execute();
        $res_details = $stmt_details->get_result()->fetch_assoc();
        $stmt_details->close();

        if ($res_details && (int)$res_details['id_usuario'] !== $user_id) {
            $comment_author_id = (int)$res_details['id_usuario'];
            $post_id_ref = (int)$res_details['id_postagem'];
            $tipo_notif = NOTIF_CURTIDA_COMENTARIO;

            $sql_notif = "INSERT INTO Notificacoes (usuario_id, remetente_id, tipo, id_referencia) VALUES (?, ?, ?, ?)";
            $stmt_notif = $conn->prepare($sql_notif);
            $stmt_notif->bind_param("iisi", $comment_author_id, $user_id, $tipo_notif, $post_id_ref);
            $stmt_notif->execute();
            $stmt_notif->close();
        }
    }

    // 6. Contagem Final Sincronizada
    $sql_count = "SELECT COUNT(*) AS total FROM Curtidas_Comentarios WHERE id_comentario = ?";
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param("i", $comment_id);
    $stmt_count->execute();
    $total_curtidas = $stmt_count->get_result()->fetch_assoc()['total'];
    $stmt_count->close();

    // 7. Resposta de Sucesso
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'curtido' => $curtido,
        'total_curtidas' => (int)$total_curtidas
    ]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Falha interna no processamento.']);
}
