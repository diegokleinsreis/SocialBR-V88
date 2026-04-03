<?php
/**
 * api/comentarios/curtir_comentario.php
 * VERSÃO V9.2: PDO Migration & CSRF Shield Fixed
 * PAPEL: Processar curtidas em comentários com motor PDO.
 */

session_start();

// 1. Verificação de Identidade
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Acesso negado.']);
    exit();
}

// 2. Importações de Infraestrutura (Caminhos Robustos)
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/sentinela.php'; // Essencial para verify_csrf_token
require_once __DIR__ . '/../../../config/tipos_notificacoes.php'; 

// 3. Validação de Segurança CSRF
// Nota: O seu JS envia o token via FormData
$csrf_token = $_POST['csrf_token'] ?? '';
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !verify_csrf_token($csrf_token)) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Segurança: Token inválido ou expirado.']);
    exit();
}

// 4. Captura e Sanitização
$comment_id = (int)($_POST['comment_id'] ?? 0);
$user_id = (int)$_SESSION['user_id'];

if ($comment_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'ID do comentário inválido.']);
    exit();
}

try {
    // 5. Lógica de "Toggle" (Alternar Curtida) usando PDO
    $sql_check = "SELECT id FROM Curtidas_Comentarios WHERE id_usuario = :uid AND id_comentario = :cid";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute(['uid' => $user_id, 'cid' => $comment_id]);
    $existente = $stmt_check->fetch();

    if ($existente) {
        // DESCURTIR
        $sql_unlike = "DELETE FROM Curtidas_Comentarios WHERE id_usuario = :uid AND id_comentario = :cid";
        $pdo->prepare($sql_unlike)->execute(['uid' => $user_id, 'cid' => $comment_id]);
        $curtido = false;
    } else {
        // CURTIR
        $sql_like = "INSERT INTO Curtidas_Comentarios (id_usuario, id_comentario) VALUES (:uid, :cid)";
        $pdo->prepare($sql_like)->execute(['uid' => $user_id, 'cid' => $comment_id]);
        $curtido = true;

        // --- LÓGICA DE NOTIFICAÇÃO ---
        $sql_details = "SELECT id_usuario, id_postagem FROM Comentarios WHERE id = :cid";
        $stmt_details = $pdo->prepare($sql_details);
        $stmt_details->execute(['cid' => $comment_id]);
        $comment_data = $stmt_details->fetch();

        if ($comment_data && (int)$comment_data['id_usuario'] !== $user_id) {
            $sql_notif = "INSERT INTO notificacoes (usuario_id, remetente_id, tipo, id_referencia) 
                          VALUES (:target, :sender, :type, :ref)";
            $pdo->prepare($sql_notif)->execute([
                'target' => $comment_data['id_usuario'],
                'sender' => $user_id,
                'type'   => NOTIF_CURTIDA_COMENTARIO,
                'ref'    => $comment_data['id_postagem']
            ]);
        }
    }

    // 6. Contagem Final Sincronizada
    $sql_count = "SELECT COUNT(*) as total FROM Curtidas_Comentarios WHERE id_comentario = :cid";
    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute(['cid' => $comment_id]);
    $total_curtidas = $stmt_count->fetch()['total'];

    // 7. Resposta JSON de Sucesso
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'curtido' => $curtido,
        'total_curtidas' => (int)$total_curtidas
    ]);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Erro de banco de dados: ' . $e->getMessage()]);
}
