<?php
/**
 * api/admin/menus_rotas_acoes.php
 * PAPEL: Processar CRUD de Rotas, Sincronização e Agendamento de Eventos.
 * VERSÃO: 1.4 (Redundancy Sync & Parametric Persistence - socialbr.lol)
 */

header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. SEGURANÇA E CONEXÃO
require_once __DIR__ . '/../../../config/database.php';
// Middleware de autenticação admin
require_once __DIR__ . '/../../admin/admin_auth.php'; 
// Motor de inteligência para verificação de arquivos e gestão de logs
require_once __DIR__ . '/../../../src/RotasLogic.php';

$rotasLogic = new RotasLogic($pdo);
$response = ['success' => false, 'message' => 'Ação não identificada.'];
$acao = $_REQUEST['acao'] ?? '';

try {
    switch ($acao) {
        
        // --- [AÇÃO: VERIFICAR EXISTÊNCIA DE ARQUIVO] ---
        case 'verificar_arquivo':
            $arquivo = $_GET['arquivo'] ?? '';
            if ($rotasLogic->arquivoExiste($arquivo)) {
                $response = ['success' => true, 'message' => 'Arquivo localizado com sucesso.'];
            } else {
                $response = ['success' => false, 'message' => 'Arquivo não encontrado no servidor.'];
            }
            break;

        // --- [AÇÃO: OBTER DADOS PARA EDIÇÃO] ---
        case 'obter':
            $id = (int)($_GET['id'] ?? 0);
            $stmt = $pdo->prepare("SELECT * FROM Menus_Sistema WHERE id = ?");
            $stmt->execute([$id]);
            $rota = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($rota) {
                $response = ['success' => true, 'rota' => $rota];
            } else {
                $response['message'] = 'Rota não encontrada.';
            }
            break;

        // --- [AÇÃO: LIGAR/DESLIGAR STATUS] ---
        case 'toggle_status':
            $id = (int)($_POST['id'] ?? 0);
            $status = (int)($_POST['status'] ?? 0);
            
            $stmt = $pdo->prepare("UPDATE Menus_Sistema SET status = ? WHERE id = ?");
            if ($stmt->execute([$status, $id])) {
                $response = ['success' => true, 'message' => 'Status atualizado.'];
            }
            break;

        // --- [AÇÃO: SALVAR OU CRIAR ROTA] ---
        case 'salvar':
            $id = (int)($_POST['id'] ?? 0);
            $dados = [
                'parent_id'          => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
                'slug'               => trim($_POST['slug']),
                'label'              => trim($_POST['label']),
                'icone'              => trim($_POST['icone']),
                'arquivo_destino'    => trim($_POST['arquivo_destino']),
                'permissao'          => $_POST['permissao'],
                'exibir_no_menu'     => isset($_POST['exibir_no_menu']) && $_POST['exibir_no_menu'] == '1' ? 1 : 0,
                'permite_parametros' => isset($_POST['permite_parametros']) && $_POST['permite_parametros'] == '1' ? 1 : 0,
                'status'             => isset($_POST['status']) && $_POST['status'] == '1' ? 1 : 0,
                'manutencao_modulo'  => isset($_POST['manutencao_modulo']) && $_POST['manutencao_modulo'] == '1' ? 1 : 0,
                'liberacao_em'       => !empty($_POST['liberacao_em']) ? $_POST['liberacao_em'] : null,
                'ordem'              => (int)($_POST['ordem'] ?? 0)
            ];

            // Validação de Integridade Física
            if (!$rotasLogic->arquivoExiste($dados['arquivo_destino'])) {
                $response = [
                    'success' => false, 
                    'message' => 'ERRO: O arquivo "' . $dados['arquivo_destino'] . '" não foi encontrado no servidor.'
                ];
                break;
            }

            if ($id > 0) {
                $sql = "UPDATE Menus_Sistema SET 
                        parent_id = :parent_id, slug = :slug, label = :label, icone = :icone, 
                        arquivo_destino = :arquivo_destino, permissao = :permissao, 
                        exibir_no_menu = :exibir_no_menu, permite_parametros = :permite_parametros, 
                        status = :status, manutencao_modulo = :manutencao_modulo, 
                        liberacao_em = :liberacao_em, ordem = :ordem 
                        WHERE id = :id";
                $dados['id'] = $id;
            } else {
                $sql = "INSERT INTO Menus_Sistema 
                        (parent_id, slug, label, icone, arquivo_destino, permissao, exibir_no_menu, permite_parametros, status, manutencao_modulo, liberacao_em, ordem) 
                        VALUES 
                        (:parent_id, :slug, :label, :icone, :arquivo_destino, :permissao, :exibir_no_menu, :permite_parametros, :status, :manutencao_modulo, :liberacao_em, :ordem)";
            }

            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($dados)) {
                $response = ['success' => true, 'message' => 'Rota guardada com sucesso!'];
            }
            break;

        // --- [AÇÃO: EXCLUIR ROTA] ---
        case 'excluir':
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM Menus_Sistema WHERE id = ?");
            if ($stmt->execute([$id])) {
                $response = ['success' => true, 'message' => 'Rota removida.'];
            }
            break;

        // --- [AÇÃO: LIMPAR REGISTROS DE CLIQUES MORTOS] ---
        case 'limpar_logs':
            if ($rotasLogic->limparLogsNegados()) {
                $response = ['success' => true, 'message' => 'Histórico de auditoria limpo com sucesso!'];
            } else {
                $response['message'] = 'Falha ao tentar limpar os registos no servidor.';
            }
            break;

        // --- [AÇÃO: REGENERAR PARA-QUEDAS (JSON)] ---
        case 'regenerar_json':
            // CORREÇÃO: Adicionado permite_parametros na query de backup
            $stmt = $pdo->query("SELECT slug, arquivo_destino, permissao, permite_parametros, status FROM Menus_Sistema WHERE status = 1");
            $rotas_ativas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $backup_data = [
                'ultima_atualizacao' => date('Y-m-d H:i:s'),
                'total_rotas' => count($rotas_ativas),
                'rotas' => $rotas_ativas
            ];

            $caminho_json = __DIR__ . '/../../../config/emergencia.json';
            
            if (file_put_contents($caminho_json, json_encode($backup_data, JSON_PRETTY_PRINT))) {
                $response = ['success' => true, 'message' => 'Ficheiro de emergência atualizado com sucesso!'];
            } else {
                $response['message'] = 'Erro ao escrever o ficheiro JSON. Verifique as permissões de pasta.';
            }
            break;
    }

} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()];
}

echo json_encode($response);