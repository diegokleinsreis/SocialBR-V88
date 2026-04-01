<?php
/**
 * api/admin/toggle_comment_status.php
 * PAPEL: Alternar status de comentários (Ativo/Inativo) com auditoria clara.
 * VERSÃO: 2.0 (Integração com Logs de Auditoria - socialbr.lol)
 */

require_once __DIR__ . '/../../admin/admin_auth.php'; // Garante que só o admin pode executar
// $conn e $config['base_path'] já estão disponíveis aqui

// --- BLOCO DE SEGURANÇA: VERIFICAÇÃO CSRF E MÉTODO POST ---
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

// Pega os IDs do POST
$comment_id_to_toggle = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$denuncia_id = isset($_POST['denuncia_id']) ? (int)$_POST['denuncia_id'] : 0;

header('Content-Type: application/json');

if ($comment_id_to_toggle > 0) {
    try {
        // --- BUSCA DADOS DO COMENTÁRIO PARA O LOG ---
        $sql_check = "SELECT status, comentario_texto, post_id FROM Comentarios WHERE id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("i", $comment_id_to_toggle);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        
        if ($comment = $result->fetch_assoc()) {
            $old_status = $comment['status'];
            $new_status = ($old_status === 'ativo') ? 'inativo' : 'ativo';
            $post_id = $comment['post_id'];
            
            // Resume o texto do comentário para o log (primeiros 50 caracteres)
            $resumo = mb_strimwidth($comment['comentario_texto'], 0, 50, "...");

            // 1. Atualiza o status do comentário
            $sql_update = "UPDATE Comentarios SET status = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $new_status, $comment_id_to_toggle);
            $stmt_update->execute();
            $stmt_update->close();

            // 2. LÓGICA DE DENÚNCIA (Se houver uma denúncia vinculada)
            if ($denuncia_id > 0) {
                $sql_update_denuncia = "UPDATE Denuncias SET status = 'revisado' WHERE id = ?";
                $stmt_denuncia = $conn->prepare($sql_update_denuncia);
                $stmt_denuncia->bind_param("i", $denuncia_id);
                $stmt_denuncia->execute();
                $stmt_denuncia->close();
            }

            // --- REGISTO DE AUDITORIA CLARO ---
            $status_acao = ($new_status === 'inativo') ? 'OCULTADO (Inativo)' : 'REATIVADO (Ativo)';
            $origem = ($denuncia_id > 0) ? " através da denúncia #$denuncia_id" : "";
            
            $detalhe_log = "Comentário #$comment_id_to_toggle no post #$post_id foi $status_acao$origem. Conteúdo: \"$resumo\"";
            
            admin_log('alterar_status_comentario', 'comentario', $comment_id_to_toggle, $detalhe_log);

            echo json_encode(['success' => true, 'message' => 'Status do comentário atualizado.']);
        } else {
            throw new Exception("Comentário não encontrado.");
        }
        $stmt_check->close();

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'ID de comentário inválido.']);
}

$conn->close();
exit();