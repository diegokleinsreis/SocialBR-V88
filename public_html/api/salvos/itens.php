<?php
/**
 * api/salvos/itens.php
 * PAPEL: Controlador Único para Salvar, Remover e Mover itens entre coleções.
 * RESPONSABILIDADE: Gerenciar o vínculo entre usuários e postagens (Suporte a Marketplace).
 * VERSÃO: V72.0 (socialbr.lol)
 */

header('Content-Type: application/json');

// 1. Inicialização e Segurança
require_once __DIR__ . '/../../../config/database.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Acesso negado.']);
    exit;
}

// 2. Inteligência de Entrada (Suporte a JSON e $_POST)
$input = json_decode(file_get_contents('php://input'), true);
$dados = !empty($input) ? $input : $_POST;

// 3. Verificação CSRF
$token = $dados['csrf_token'] ?? '';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($token) || !verify_csrf_token($token)) {
    echo json_encode(['success' => false, 'error' => 'Token de segurança inválido ou expirado.']);
    exit;
}

// 4. Dependências de Lógica
require_once __DIR__ . '/../../../src/SalvosLogic.php';
$salvosLogic = new SalvosLogic($pdo); 

$usuario_id = (int)$_SESSION['user_id'];
$acao        = $dados['acao_tipo'] ?? '';
$post_id     = (int)($dados['post_id'] ?? 0);
$colecao_id  = isset($dados['colecao_id']) ? (int)$dados['colecao_id'] : null;

// --- 5. VALIDAÇÃO DE ELITE (Foco Marketplace) ---
if ($post_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de postagem inválido.']);
    exit;
}

/**
 * Verificação de Existência e Tipo:
 * Garante que o item existe e é salvável (padrão ou venda).
 */
$stmtCheck = $pdo->prepare("SELECT tipo_post FROM Postagens WHERE id = ? AND status = 'ativo' LIMIT 1");
$stmtCheck->execute([$post_id]);
$postInfo = $stmtCheck->fetch(PDO::FETCH_ASSOC);

if (!$postInfo) {
    echo json_encode(['success' => false, 'error' => 'A postagem não existe ou foi removida.']);
    exit;
}

// Permite apenas tipos salváveis conhecidos
$tiposPermitidos = ['padrao', 'venda'];
if (!in_array($postInfo['tipo_post'], $tiposPermitidos)) {
    echo json_encode(['success' => false, 'error' => 'Este tipo de conteúdo não pode ser salvo nas coleções.']);
    exit;
}

// --- PROCESSAMENTO ATÔMICO ---

try {
    switch ($acao) {

        case 'salvar':
            $resultado = $salvosLogic->salvarItem($usuario_id, $post_id, $colecao_id);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Item salvo com sucesso!',
                    'salvo'   => true
                ]);
            } else {
                throw new Exception('Erro ao tentar salvar este item.');
            }
            break;

        case 'remover':
            $resultado = $salvosLogic->removerItem($post_id, $usuario_id);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Item removido dos salvos.',
                    'salvo'   => false
                ]);
            } else {
                throw new Exception('Erro ao tentar remover este item.');
            }
            break;

        case 'mover':
            if (!$colecao_id) {
                throw new Exception('A coleção de destino é obrigatória.');
            }

            $resultado = $salvosLogic->moverItem($post_id, $usuario_id, $colecao_id);

            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Item movido com sucesso!']);
            } else {
                throw new Exception('Não foi possível mover o item.');
            }
            break;

        case 'verificar':
            // Rota interna para verificar status via AJAX se necessário
            $stmtV = $pdo->prepare("SELECT id FROM Postagens_Salvas WHERE id_postagem = ? AND id_usuario = ? LIMIT 1");
            $stmtV->execute([$post_id, $usuario_id]);
            echo json_encode(['success' => true, 'salvo' => (bool)$stmtV->fetch()]);
            break;

        default:
            throw new Exception('Ação não identificada pela API.');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error'   => $e->getMessage()
    ]);
}