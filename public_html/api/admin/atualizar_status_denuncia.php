<?php
/**
 * api/admin/atualizar_status_denuncia.php
 * PAPEL: Marcar denúncias como revisadas ou ignoradas com log detalhado.
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
$denuncia_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$novo_status = $_POST['status'] ?? '';

header('Content-Type: application/json');

// Validação para garantir que o status é um dos valores permitidos
if ($denuncia_id > 0 && in_array($novo_status, ['revisado', 'ignorado'])) {
    
    try {
        // --- BUSCA INFORMAÇÕES DA DENÚNCIA PARA O LOG ---
        $sql_info = "SELECT tipo_conteudo, motivo FROM Denuncias WHERE id = ?";
        $stmt_info = $conn->prepare($sql_info);
        $stmt_info->bind_param("i", $denuncia_id);
        $stmt_info->execute();
        $denuncia_data = $stmt_info->get_result()->fetch_assoc();
        $stmt_info->close();

        if (!$denuncia_data) {
            throw new Exception("Denúncia não localizada.");
        }

        $motivo = $denuncia_data['motivo'];
        $tipo = strtoupper($denuncia_data['tipo_conteudo']);
        $status_final = ($novo_status === 'revisado') ? 'REVISADA (CONTEÚDO MANTIDO)' : 'IGNORADA';

        // 1. Atualiza o status da denúncia no banco de dados
        $sql_update = "UPDATE Denuncias SET status = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $novo_status, $denuncia_id);
        $sucesso = $stmt_update->execute();
        $stmt_update->close();

        if ($sucesso) {
            // --- REGISTRO DE AUDITORIA CLARO E DETALHADO ---
            $detalhe_log = "Denúncia #$denuncia_id de $tipo (Motivo: $motivo) marcada como $status_final.";
            admin_log('atualizar_status_denuncia', 'denuncia', $denuncia_id, $detalhe_log);

            echo json_encode(['success' => true, 'message' => "Denúncia marcada como $novo_status com sucesso."]);
        } else {
            throw new Exception("Erro ao atualizar banco de dados.");
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Dados inválidos para atualização.']);
}

$conn->close();
exit();