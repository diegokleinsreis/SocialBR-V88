<?php
/**
 * ARQUIVO: api/admin/erros_acoes.php
 * PAPEL: Processar ações administrativas do Monitor de Erros (Sentinela).
 * VERSÃO: 1.0 (socialbr.lol)
 */

// 1. --- [CONFIGURAÇÃO E PROTEÇÃO] ---
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../admin/admin_auth.php'; // Garante que apenas admins acessem
require_once __DIR__ . '/../../../src/ErrorLogic.php';

header('Content-Type: application/json');

// Inicializa a lógica
$errorLogic = new ErrorLogic($pdo);

// 2. --- [CAPTURA DE PARÂMETROS] ---
$acao = $_POST['acao'] ?? $_GET['acao'] ?? null;
$id   = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : null;

$response = ['success' => false, 'message' => 'Ação não identificada.'];

// 3. --- [PROCESSAMENTO DE AÇÕES] ---
switch ($acao) {

    // Ação: Obter detalhes completos de um erro (para o Modal)
    case 'obter_detalhes':
        if (!$id) {
            $response['message'] = 'ID do erro é obrigatório.';
            break;
        }

        try {
            $sql = "SELECT e.*, u.nome_de_usuario 
                    FROM Logs_Erros_Sistema e 
                    LEFT JOIN Usuarios u ON e.usuario_id = u.id 
                    WHERE e.id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $erro = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($erro) {
                $response = [
                    'success' => true,
                    'dados' => $erro
                ];
            } else {
                $response['message'] = 'Erro não encontrado no banco.';
            }
        } catch (Exception $e) {
            $response['message'] = 'Falha ao buscar detalhes: ' . $e->getMessage();
        }
        break;

    // Ação: Alterar status (Pendente -> Corrigido, etc)
    case 'atualizar_status':
        $novo_status = $_POST['status'] ?? null;
        $status_permitidos = ['pendente', 'em_analise', 'corrigido', 'ignorado'];

        if (!$id || !in_array($novo_status, $status_permitidos)) {
            $response['message'] = 'Parâmetros inválidos para atualização.';
            break;
        }

        try {
            $sql = "UPDATE Logs_Erros_Sistema SET status = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$novo_status, $id])) {
                $response = ['success' => true, 'message' => 'Status atualizado com sucesso.'];
            }
        } catch (Exception $e) {
            $response['message'] = 'Erro ao atualizar: ' . $e->getMessage();
        }
        break;

    // Ação: Limpar todos os logs (Cuidado!)
    case 'limpar_tudo':
        try {
            // TRUNCATE é mais rápido e reseta os IDs
            $pdo->exec("TRUNCATE TABLE Logs_Erros_Sistema");
            $response = ['success' => true, 'message' => 'Todos os logs foram eliminados.'];
        } catch (Exception $e) {
            $response['message'] = 'Erro ao limpar logs: ' . $e->getMessage();
        }
        break;

    // Ação: Excluir um erro específico
    case 'excluir':
        if (!$id) break;

        try {
            $sql = "DELETE FROM Logs_Erros_Sistema WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$id])) {
                $response = ['success' => true, 'message' => 'Registro removido.'];
            }
        } catch (Exception $e) {
            $response['message'] = 'Erro ao excluir: ' . $e->getMessage();
        }
        break;
}

echo json_encode($response);
exit;