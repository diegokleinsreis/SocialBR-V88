<?php
/**
 * api/admin/toggle_post_status.php
 * PAPEL: Alternar status de postagens (Ativo/Inativo) com auditoria detalhada.
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

// Verifica se o token existe e se é válido
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    http_response_code(403); 
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Erro de segurança: Token inválido.']);
    exit();
}

// Pega os IDs do POST
$post_id_to_toggle = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$denuncia_id = isset($_POST['denuncia_id']) ? (int)$_POST['denuncia_id'] : 0;

header('Content-Type: application/json');

if ($post_id_to_toggle > 0) {
    try {
        // --- BUSCA DADOS DA POSTAGEM PARA O LOG ---
        $sql_check = "SELECT status, legenda FROM Postagens WHERE id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("i", $post_id_to_toggle);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        
        if ($post = $result->fetch_assoc()) {
            $old_status = $post['status'];
            $new_status = ($old_status === 'ativo') ? 'inativo' : 'ativo';
            
            // Resume a legenda para o log (primeiros 50 caracteres)
            $resumo = !empty($post['legenda']) ? mb_strimwidth($post['legenda'], 0, 50, "...") : "(Sem texto)";

            // 1. Atualiza o status da postagem
            $sql_update = "UPDATE Postagens SET status = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $new_status, $post_id_to_toggle);
            $stmt_update->execute();
            $stmt_update->close();

            // 2. LÓGICA DE DENÚNCIA (Se houver denúncia vinculada)
            if ($denuncia_id > 0) {
                $sql_update_denuncia = "UPDATE Denuncias SET status = 'revisado' WHERE id = ?";
                $stmt_denuncia = $conn->prepare($sql_update_denuncia);
                $stmt_denuncia->bind_param("i", $denuncia_id);
                $stmt_denuncia->execute();
                $stmt_denuncia->close();
            }

            // --- REGISTO DE AUDITORIA CLARO ---
            $status_acao = ($new_status === 'inativo') ? 'OCULTADA (Inativo)' : 'REATIVADA (Ativo)';
            $origem = ($denuncia_id > 0) ? " através da denúncia #$denuncia_id" : "";
            
            $detalhe_log = "Postagem #$post_id_to_toggle foi $status_acao$origem. Legenda: \"$resumo\"";
            
            admin_log('alterar_status_postagem', 'post', $post_id_to_toggle, $detalhe_log);

            echo json_encode(['success' => true, 'message' => 'Status da postagem atualizado com sucesso.']);
        } else {
            throw new Exception("Postagem não encontrada.");
        }
        $stmt_check->close();

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'ID de postagem inválido.']);
}

$conn->close();
exit();