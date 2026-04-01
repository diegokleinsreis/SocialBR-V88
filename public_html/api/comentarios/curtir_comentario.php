<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    // Envia uma resposta de erro em formato JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Acesso negado.']);
    exit();
}

// --- IMPORTAÇÕES NECESSÁRIAS ---
require_once __DIR__ . '/../../../config/database.php';
// Importa o dicionário de tipos para padronizar as notificações
require_once __DIR__ . '/../../../config/tipos_notificacoes.php'; 

// --- NOVO BLOCO DE SEGURANÇA: VERIFICAÇÃO CSRF ---
// Verifica se é um POST, se o token existe e se é válido.
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    header('Content-Type: application/json');
    http_response_code(403); // Código 403: Acesso Proibido/Token Inválido
    echo json_encode(['success' => false, 'error' => 'Token de segurança inválido. Tente recarregar a página.']);
    exit();
}
// --- FIM DO NOVO BLOCO ---

// Pega o ID do comentário enviado pelo JavaScript
$comment_id = $_POST['comment_id'] ?? 0;
$user_id = $_SESSION['user_id'];

if ($comment_id > 0) {
    // 1. Verifica se o usuário já curtiu este comentário
    $sql_check = "SELECT id FROM Curtidas_Comentarios WHERE id_usuario = ? AND id_comentario = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $user_id, $comment_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // JÁ CURTIU -> DESCURTIR (remover a curtida)
        $sql_unlike = "DELETE FROM Curtidas_Comentarios WHERE id_usuario = ? AND id_comentario = ?";
        $stmt_unlike = $conn->prepare($sql_unlike);
        $stmt_unlike->bind_param("ii", $user_id, $comment_id);
        $stmt_unlike->execute();
        $curtido = false;
    } else {
        // AINDA NÃO CURTIU -> CURTIR (adicionar a curtida)
        $sql_like = "INSERT INTO Curtidas_Comentarios (id_usuario, id_comentario) VALUES (?, ?)";
        $stmt_like = $conn->prepare($sql_like);
        $stmt_like->bind_param("ii", $user_id, $comment_id);
        $stmt_like->execute();
        $curtido = true;

        // --- LÓGICA PADRONIZADA PARA CRIAR A NOTIFICAÇÃO DE CURTIDA NO COMENTÁRIO ---

        // Primeiro, pegamos o ID do autor do comentário e o ID do post original
        $sql_comment_details = "SELECT id_usuario, id_postagem FROM Comentarios WHERE id = ?";
        $stmt_comment_details = $conn->prepare($sql_comment_details);
        $stmt_comment_details->bind_param("i", $comment_id);
        $stmt_comment_details->execute();
        $result_comment_details = $stmt_comment_details->get_result();
        
        if ($row = $result_comment_details->fetch_assoc()) {
            $comment_autor_id = $row['id_usuario'];
            $post_id_referencia = $row['id_postagem']; // ID do post para o link da notificação

            // Apenas cria a notificação se o usuário não estiver curtindo o próprio comentário
            if ($comment_autor_id != $user_id) {
                // USANDO A CONSTANTE PADRONIZADA
                $tipo_notificacao = NOTIF_CURTIDA_COMENTARIO; 
                
                $sql_notificacao = "INSERT INTO notificacoes (usuario_id, remetente_id, tipo, id_referencia) VALUES (?, ?, ?, ?)";
                $stmt_notificacao = $conn->prepare($sql_notificacao);
                // Parâmetros: [quem recebe], [quem envia], [tipo], [ID do post original]
                $stmt_notificacao->bind_param("iisi", $comment_autor_id, $user_id, $tipo_notificacao, $post_id_referencia);
                $stmt_notificacao->execute();
                $stmt_notificacao->close();
            }
        }
        $stmt_comment_details->close();
        // --- FIM DA LÓGICA DE NOTIFICAÇÃO ---
    }

    // 2. Conta o novo número total de curtidas para este comentário
    $sql_count = "SELECT COUNT(*) AS total_curtidas FROM Curtidas_Comentarios WHERE id_comentario = ?";
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param("i", $comment_id);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result()->fetch_assoc();
    $total_curtidas = $result_count['total_curtidas'];

    // 3. Envia uma resposta de sucesso em formato JSON para o JavaScript
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'curtido' => $curtido,
        'total_curtidas' => $total_curtidas
    ]);

} else {
    // Envia uma resposta de erro se o comment_id for inválido
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'ID do comentário inválido.']);
}
?>