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
// Importa o dicionário de tipos para padronizar a notificação
require_once __DIR__ . '/../../../config/tipos_notificacoes.php'; 

// --- NOVO BLOCO DE SEGURANÇA: VERIFICAÇÃO CSRF ---
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    header('Content-Type: application/json');
    http_response_code(403); 
    echo json_encode(['success' => false, 'error' => 'Token de segurança inválido. Tente recarregar a página.']);
    exit();
}
// --- FIM DO NOVO BLOCO ---

// Pega o ID da postagem enviado pelo JavaScript
$post_id = $_POST['post_id'] ?? 0;
$user_id = $_SESSION['user_id'];

if ($post_id > 0) {
    // 1. Verifica se o usuário já curtiu este post
    $sql_check = "SELECT id FROM Curtidas WHERE id_usuario = ? AND id_postagem = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $user_id, $post_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // JÁ CURTIU -> DESCURTIR (remover a curtida)
        $sql_unlike = "DELETE FROM Curtidas WHERE id_usuario = ? AND id_postagem = ?";
        $stmt_unlike = $conn->prepare($sql_unlike);
        $stmt_unlike->bind_param("ii", $user_id, $post_id);
        $stmt_unlike->execute();
        $curtido = false;
    } else {
        // AINDA NÃO CURTIU -> CURTIR (adicionar a curtida)
        $sql_like = "INSERT INTO Curtidas (id_usuario, id_postagem) VALUES (?, ?)";
        $stmt_like = $conn->prepare($sql_like);
        $stmt_like->bind_param("ii", $user_id, $post_id);
        $stmt_like->execute();
        $curtido = true;

        // --- LÓGICA PARA CRIAR A NOTIFICAÇÃO ---

        // Primeiro, pegamos o ID do autor do post
        $sql_post_autor = "SELECT id_usuario FROM Postagens WHERE id = ?";
        $stmt_post_autor = $conn->prepare($sql_post_autor);
        $stmt_post_autor->bind_param("i", $post_id);
        $stmt_post_autor->execute();
        $result_post_autor = $stmt_post_autor->get_result();
        
        if ($row = $result_post_autor->fetch_assoc()) {
            $post_autor_id = $row['id_usuario'];

            // Apenas cria a notificação se o usuário não estiver curtindo o próprio post
            if ($post_autor_id != $user_id) {
                // USANDO A CONSTANTE PADRONIZADA: Agora envia 'curtida_post' para o banco
                $tipo_notificacao = NOTIF_CURTIDA_POST; 
                
                $sql_notificacao = "INSERT INTO notificacoes (usuario_id, remetente_id, tipo, id_referencia) VALUES (?, ?, ?, ?)";
                $stmt_notificacao = $conn->prepare($sql_notificacao);
                // Parâmetros: [quem recebe], [quem envia], [tipo], [id do post]
                $stmt_notificacao->bind_param("iisi", $post_autor_id, $user_id, $tipo_notificacao, $post_id);
                $stmt_notificacao->execute();
                $stmt_notificacao->close();
            }
        }
        $stmt_post_autor->close();
        // --- FIM DA LÓGICA DE NOTIFICAÇÃO ---
    }

    // 2. Conta o novo número total de curtidas para este post
    $sql_count = "SELECT COUNT(*) AS total_curtidas FROM Curtidas WHERE id_postagem = ?";
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param("i", $post_id);
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
    // Envia uma resposta de erro se o post_id for inválido
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'ID da postagem inválido.']);
}
?>