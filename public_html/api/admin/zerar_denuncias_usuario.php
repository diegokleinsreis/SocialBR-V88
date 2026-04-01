<?php
/**
 * api/admin/zerar_denuncias_usuario.php
 * PAPEL: Limpar o histórico de denúncias resolvidas de um utilizador específico.
 * VERSÃO: 2.1 (Audit Log Integrado - socialbr.lol)
 */

// 1. GUARITA DE SEGURANÇA E CONEXÃO
require_once __DIR__ . '/../../admin/admin_auth.php'; // Garante que só o admin pode executar
// $conn e $config['base_path'] já estão disponíveis aqui

// 2. VERIFICAÇÃO DE SEGURANÇA (POST + CSRF)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Método não permitido.']);
    exit();
}

if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    http_response_code(403); 
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Erro de segurança: Token inválido.']);
    exit();
}

// 3. CAPTURA DO ID
$user_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

header('Content-Type: application/json');

if ($user_id > 0) {
    try {
        // --- BUSCA NOME DO UTILIZADOR PARA O LOG ---
        $sql_info = "SELECT nome_de_usuario FROM Usuarios WHERE id = ?";
        $stmt_info = $conn->prepare($sql_info);
        $stmt_info->bind_param("i", $user_id);
        $stmt_info->execute();
        $user_data = $stmt_info->get_result()->fetch_assoc();
        $stmt_info->close();

        $username = $user_data ? $user_data['nome_de_usuario'] : "ID #$user_id";

        // 4. EXECUÇÃO DA LIMPEZA (Apenas denúncias resolvidas/revisadas/ignoradas)
        
        // Limpa denúncias do perfil
        $sql_user = "UPDATE Denuncias SET status = 'excluida_pelo_adm' WHERE tipo_conteudo = 'usuario' AND id_conteudo = ? AND status != 'pendente'";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $stmt_user->close();

        // Limpa denúncias de posts do usuário
        $sql_posts = "UPDATE Denuncias d JOIN Postagens p ON d.id_conteudo = p.id SET d.status = 'excluida_pelo_adm' WHERE d.tipo_conteudo = 'post' AND p.id_usuario = ? AND d.status != 'pendente'";
        $stmt_posts = $conn->prepare($sql_posts);
        $stmt_posts->bind_param("i", $user_id);
        $stmt_posts->execute();
        $stmt_posts->close();

        // Limpa denúncias de comentários do usuário
        $sql_comments = "UPDATE Denuncias d JOIN Comentarios c ON d.id_conteudo = c.id SET d.status = 'excluida_pelo_adm' WHERE d.tipo_conteudo = 'comentario' AND c.id_usuario = ? AND d.status != 'pendente'";
        $stmt_comments = $conn->prepare($sql_comments);
        $stmt_comments->bind_param("i", $user_id);
        $stmt_comments->execute();
        $stmt_comments->close();

        // --- REGISTO DE AUDITORIA CLARO ---
        $detalhe_log = "O administrador zerou o histórico de denúncias resolvidas do utilizador #$user_id ($username). Perfil, posts e comentários foram limpos.";
        admin_log('zerar_denuncias_usuario', 'usuario', $user_id, $detalhe_log);

        echo json_encode(['success' => true, 'message' => 'Denúncias zeradas com sucesso.']);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'ID de utilizador inválido.']);
}

$conn->close();
exit();