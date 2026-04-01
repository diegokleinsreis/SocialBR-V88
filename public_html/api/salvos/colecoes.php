<?php
/**
 * api/salvos/colecoes.php
 * PAPEL: Endpoint para Criar, Editar e Excluir coleções.
 * RESPONSABILIDADE: Validar requisições e delegar para SalvosLogic.
 * VERSÃO: V71.7 (socialbr.lol)
 */

header('Content-Type: application/json');

// 1. Inicialização e Segurança de Sessão
// AJUSTE DE ENGENHARIA: Caminho para buscar config fora da public_html
require_once __DIR__ . '/../../../config/database.php';

// Inicia sessão caso o database.php não o faça automaticamente
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

// 2. Verificação de Segurança CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'error' => 'Falha na validação de segurança (CSRF).']);
    exit;
}

// 3. Carregamento da Lógica e Dependências
// CORREÇÃO CIRÚRGICA: Subindo 3 níveis para encontrar a pasta src fora da public_html
require_once __DIR__ . '/../../../src/SalvosLogic.php';
$salvosLogic = new SalvosLogic($pdo); // Usa a instância PDO da arquitetura global

$usuario_id = (int)$_SESSION['user_id'];
$acao       = $_POST['acao_tipo'] ?? ''; // 'criar', 'editar' ou 'excluir'
$nome       = trim($_POST['nome'] ?? '');
$privacidade = $_POST['privacidade'] ?? 'privada';

// --- MOTOR DE DECISÃO ---

try {
    switch ($acao) {
        
        case 'criar':
            if (empty($nome)) {
                throw new Exception('O nome da coleção é obrigatório.');
            }
            
            $resultado = $salvosLogic->criarColecao($usuario_id, $nome, $privacidade);
            
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Coleção criada com sucesso!']);
            } else {
                throw new Exception('Erro ao criar coleção. Verifique se o nome já existe.');
            }
            break;

        case 'editar':
            $id_colecao = (int)($_POST['colecao_id'] ?? 0);
            
            if ($id_colecao <= 0 || empty($nome)) {
                throw new Exception('Dados inválidos para edição.');
            }

            $resultado = $salvosLogic->editarColecao($id_colecao, $usuario_id, $nome, $privacidade);
            
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Coleção atualizada!']);
            } else {
                throw new Exception('Não foi possível editar esta coleção (Pastas padrão não podem ser alteradas).');
            }
            break;

        case 'excluir':
            $id_colecao = (int)($_POST['colecao_id'] ?? 0);

            if ($id_colecao <= 0) {
                throw new Exception('ID da coleção inválido.');
            }

            $resultado = $salvosLogic->excluirColecao($id_colecao, $usuario_id);

            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Coleção removida com sucesso.']);
            } else {
                throw new Exception('Esta coleção não pode ser removida.');
            }
            break;

        default:
            throw new Exception('Ação não identificada pelo servidor.');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error'   => $e->getMessage()
    ]);
}