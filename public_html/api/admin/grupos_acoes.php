<?php
/**
 * api/admin/grupos_acoes.php
 * PAPEL: Processar ações administrativas (Privacidade, Status, Dono).
 * LOCALIZAÇÃO: public_html/api/admin/
 * VERSÃO: 3.0 (Log Inteligente com Nome e Status Exato - socialbr.lol)
 */

// 1. CARREGAMENTO DE SEGURANÇA E DEPENDÊNCIAS
require_once __DIR__ . '/../../admin/admin_auth.php'; 
require_once __DIR__ . '/../../../src/GruposLogic.php'; 

header('Content-Type: application/json');

// 2. VALIDAÇÃO DE ACESSO
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'error' => 'Método não permitido.']));
}

// 3. CAPTURA DE DADOS
$id_grupo = (int)($_POST['id'] ?? 0);
$acao = $_POST['acao'] ?? '';
$admin_id = $_SESSION['user_id'];

if ($id_grupo <= 0) {
    exit(json_encode(['success' => false, 'error' => 'ID de grupo inválido.']));
}

try {
    $log_acao = '';
    $detalhes = '';

    // --- BUSCA DADOS ATUAIS DO GRUPO PARA O LOG ---
    $stmt_info = $conn->prepare("SELECT nome, privacidade, status FROM Grupos WHERE id = ?");
    $stmt_info->bind_param("i", $id_grupo);
    $stmt_info->execute();
    $grupo_info = $stmt_info->get_result()->fetch_assoc();
    $stmt_info->close();

    if (!$grupo_info) {
        throw new Exception("Grupo não encontrado.");
    }

    $nome_grupo = $grupo_info['nome'];

    switch ($acao) {
        case 'toggle_privacidade':
            // Define o novo valor para o log
            $nova_privacidade = ($grupo_info['privacidade'] === 'publico') ? 'privado' : 'publico';
            
            $sql = "UPDATE Grupos SET privacidade = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $nova_privacidade, $id_grupo);
            $stmt->execute();
            
            $log_acao = 'alterar_privacidade';
            $detalhes = "Privacidade do grupo #$id_grupo ($nome_grupo) alterada para " . strtoupper($nova_privacidade) . ".";
            break;

        case 'toggle_status':
            // Define o novo status para o log
            $novo_status = ($grupo_info['status'] === 'ativo') ? 'suspenso' : 'ativo';
            
            // Ignora grupos já excluídos
            $sql = "UPDATE Grupos SET status = ? WHERE id = ? AND status != 'excluido'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $novo_status, $id_grupo);
            $stmt->execute();

            $log_acao = 'alterar_status';
            $detalhes = "Status do grupo #$id_grupo ($nome_grupo) alterado para " . strtoupper($novo_status) . ".";
            break;

        case 'trocar_dono':
            $id_novo_dono = (int)($_POST['id_alvo'] ?? 0);
            if ($id_novo_dono <= 0) throw new Exception("ID do novo dono é obrigatório.");

            // Utiliza o cérebro da GruposLogic
            $sucesso = GruposLogic::alterarPapelMembro($conn, $id_grupo, $id_novo_dono, 'dono');
            
            if (!$sucesso) throw new Exception("Falha ao transferir posse. Verifique se o usuário existe.");

            $log_acao = 'transferencia_posse_admin';
            $detalhes = "Propriedade do grupo #$id_grupo ($nome_grupo) transferida para o UID $id_novo_dono.";
            break;

        default:
            throw new Exception("Ação administrativa desconhecida.");
    }

    // 4. REGISTRO DE LOG ADMINISTRATIVO CENTRALIZADO
    admin_log($log_acao, 'grupo', $id_grupo, $detalhes);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();