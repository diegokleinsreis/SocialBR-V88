<?php
/**
 * api/admin/toggle_user_status.php
 * PAPEL: Alternar status de usuários (Ativo/Suspenso) com auditoria detalhada.
 * VERSÃO: 2.0 (Integração com Logs de Auditoria - socialbr.lol)
 */

// Inclui a "guarita de segurança" para garantir que apenas um admin possa executar este script.
require_once __DIR__ . '/../../admin/admin_auth.php'; // Garante que só o admin pode executar
// $conn e $config['base_path'] já estão disponíveis aqui

// --- BLOCO DE SEGURANÇA: VERIFICAÇÃO CSRF E MÉTODO POST ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Método não permitido.']);
    exit();
}

// Verifica se o token existe e se é válido (usando a função do database.php)
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    http_response_code(403); 
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Erro de segurança: Token inválido.']);
    exit();
}
// --- FIM DO BLOCO ---

// Pega os IDs do POST
$user_id_to_toggle = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$denuncia_id = isset($_POST['denuncia_id']) ? (int)$_POST['denuncia_id'] : 0;

header('Content-Type: application/json');

// Regra de segurança: impede que um admin suspenda a si mesmo.
if ($user_id_to_toggle === $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'error' => 'Você não pode alterar o status da sua própria conta.']);
    exit();
}

if ($user_id_to_toggle > 0) {
    try {
        // 1. Busca o status atual e o nome de usuário para o log
        $sql_check = "SELECT status, nome_de_usuario FROM Usuarios WHERE id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("i", $user_id_to_toggle);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        
        if ($user = $result->fetch_assoc()) {
            $username = $user['nome_de_usuario'];
            $old_status = $user['status'];
            
            // 2. Decide qual será o novo status.
            $new_status = ($old_status === 'ativo') ? 'suspenso' : 'ativo';

            // 3. Atualiza o status do usuário no banco de dados.
            $sql_update = "UPDATE Usuarios SET status = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $new_status, $user_id_to_toggle);
            $stmt_update->execute();
            $stmt_update->close();

            // LÓGICA DE DENÚNCIA (Se a ação partiu de uma denúncia)
            if ($denuncia_id > 0) {
                $sql_update_denuncia = "UPDATE Denuncias SET status = 'revisado' WHERE id = ?";
                $stmt_denuncia = $conn->prepare($sql_update_denuncia);
                $stmt_denuncia->bind_param("i", $denuncia_id);
                $stmt_denuncia->execute();
                $stmt_denuncia->close();
            }

            // --- REGISTO DE AUDITORIA CLARO ---
            $status_final = ($new_status === 'suspenso') ? 'SUSPENSO (Acesso Bloqueado)' : 'ATIVADO (Acesso Liberado)';
            $via_denuncia = ($denuncia_id > 0) ? " através da denúncia #$denuncia_id" : "";
            
            $detalhe_log = "Status do usuário #$user_id_to_toggle ($username) alterado para $status_final$via_denuncia.";
            
            admin_log('alterar_status_usuario', 'usuario', $user_id_to_toggle, $detalhe_log);

            echo json_encode(['success' => true, 'message' => "Usuário $new_status com sucesso."]);
        } else {
            throw new Exception("Usuário não encontrado.");
        }
        $stmt_check->close();

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'ID de usuário inválido.']);
}

$conn->close();
exit();