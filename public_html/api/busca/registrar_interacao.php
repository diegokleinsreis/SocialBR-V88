<?php
/**
 * registrar_interacao.php - Rastreador de Comportamento
 * VERSÃO: 1.2 - Suporte a Taxa de Sucesso (Total de Resultados)
 * PAPEL: Gravar cliques e buscas garantindo que a contagem de resultados seja salva.
 */

// 1. Configurações de Resposta (JSON)
header('Content-Type: application/json; charset=utf-8');

// 2. Inicialização e Segurança
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Blindagem: Apenas usuários logados podem registrar interações
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado']);
    exit;
}

// 3. Carregamento de Dependências
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/BuscaLogic.php';

/**
 * 4. ESTABILIZAÇÃO DA CONEXÃO
 * Garante que o objeto de conexão seja encontrado, independente do nome (pdo, conn, conexao).
 */
if (!isset($db)) {
    if (isset($pdo)) { $db = $pdo; } 
    elseif (isset($conn)) { $db = $conn; } 
    elseif (isset($conexao)) { $db = $conexao; }
}

// Se mesmo após a busca a conexão for nula, interrompe com erro claro
if (!isset($db) || $db === null) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => 'Conexão com banco não encontrada']);
    exit;
}

try {
    // 5. Captura de Dados (Vindo do MotorBusca.js via POST)
    $termo           = isset($_POST['termo']) ? trim($_POST['termo']) : '';
    $tipo            = isset($_POST['tipo'])  ? trim($_POST['tipo'])  : 'geral';
    $idAlvo          = (isset($_POST['id_alvo']) && !empty($_POST['id_alvo'])) ? (int)$_POST['id_alvo'] : null;
    $totalResultados = isset($_POST['total_resultados']) ? (int)$_POST['total_resultados'] : 0; 
    $userId          = (int)$_SESSION['user_id'];

    // Validação de tipos permitidos conforme o ENUM do banco
    $tiposPermitidos = ['perfil', 'grupo', 'post', 'geral'];
    if (!in_array($tipo, $tiposPermitidos)) { $tipo = 'geral'; }

    // 6. Execução da Lógica
    if (!empty($termo)) {
        // Inicializa o BuscaLogic com a conexão blindada
        $busca = new BuscaLogic($db, $userId);
        
        // Agora enviamos o 4º parâmetro ($totalResultados) para o BuscaLogic v2.0
        $sucesso = $busca->registrarInteracao($termo, $tipo, $idAlvo, $totalResultados);

        echo json_encode([
            'sucesso' => $sucesso,
            'mensagem' => $sucesso ? 'Interação registrada' : 'Falha ao registrar no banco',
            'debug_total' => $totalResultados
        ]);
    } else {
        echo json_encode(['sucesso' => false, 'erro' => 'Termo de busca vazio']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => 'Erro interno ao processar']);
}