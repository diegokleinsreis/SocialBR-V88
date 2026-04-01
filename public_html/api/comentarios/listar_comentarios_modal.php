<?php
/**
 * api/comentarios/listar_comentarios_modal.php
 * Endpoint para carregar a árvore completa de interações de um post.
 * VERSÃO: V1.3 (Integração com CensuraLogic - socialbr.lol)
 * PAPEL: Retornar comentários filtrados para o modal sem alterar o banco de dados.
 */

session_start();
header('Content-Type: application/json');

// 1. VERIFICAÇÃO DE ACESSO
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Sessão expirada.']);
    exit();
}

// 2. LOCALIZAÇÃO ROBUSTA DO BANCO DE DADOS (Busca fora da public_html)
$db_found = false;
$db_paths = [
    __DIR__ . '/../../../config/database.php',        // Caso esteja fora da public_html (3 níveis)
    __DIR__ . '/../../config/database.php',           // Caso esteja na raiz da public_html (2 níveis)
    $_SERVER['DOCUMENT_ROOT'] . '/../config/database.php',
    $_SERVER['DOCUMENT_ROOT'] . '/config/database.php'
];

foreach ($db_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $db_found = true;
        break;
    }
}

if (!$db_found) {
    echo json_encode(['success' => false, 'error' => 'Configurações de banco de dados não encontradas.']);
    exit();
}

// 3. LOCALIZAÇÃO ROBUSTA DOS MOTORES DE LÓGICA
$logic_found = false;
$logic_paths = [
    __DIR__ . '/../../../src/ComentariosLogic.php',
    __DIR__ . '/../../src/ComentariosLogic.php',
    $_SERVER['DOCUMENT_ROOT'] . '/../src/ComentariosLogic.php',
    $_SERVER['DOCUMENT_ROOT'] . '/src/ComentariosLogic.php'
];

foreach ($logic_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $logic_found = true;
        break;
    }
}

// NOVO: Localização do CensuraLogic
$censura_found = false;
$censura_paths = [
    __DIR__ . '/../../../src/CensuraLogic.php',
    __DIR__ . '/../../src/CensuraLogic.php',
    $_SERVER['DOCUMENT_ROOT'] . '/../src/CensuraLogic.php',
    $_SERVER['DOCUMENT_ROOT'] . '/src/CensuraLogic.php'
];

foreach ($censura_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $censura_found = true;
        break;
    }
}

if (!$logic_found || !$censura_found) {
    echo json_encode(['success' => false, 'error' => 'Motores de lógica não encontrados. Verifique a pasta /src.']);
    exit();
}

// 4. CAPTURA E VALIDAÇÃO DE PARÂMETROS
$post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : (isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0);
$user_id = (int)$_SESSION['user_id'];

if ($post_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de postagem inválido.']);
    exit();
}

try {
    // 5. INICIALIZAÇÃO DO ESCUDO DE CENSURA
    $censura = new CensuraLogic($conn, $config);

    // 6. BUSCA ESTATÍSTICAS TOTAIS DO POST
    $sql_stats = "SELECT 
                    (SELECT COUNT(*) FROM Curtidas WHERE id_postagem = ?) AS total_curtidas,
                    (SELECT COUNT(*) FROM Curtidas WHERE id_postagem = ? AND id_usuario = ?) AS usuario_curtiu,
                    (SELECT COUNT(*) FROM Comentarios WHERE id_postagem = ? AND status = 'ativo') AS total_comentarios,
                    (SELECT COUNT(*) FROM Postagens WHERE post_original_id = ?) AS total_compartilhamentos
                  FROM Postagens WHERE id = ? LIMIT 1";
    
    $stmt_stats = $conn->prepare($sql_stats);
    $stmt_stats->bind_param("iiiiii", $post_id, $post_id, $user_id, $post_id, $post_id, $post_id);
    $stmt_stats->execute();
    $stats = $stmt_stats->get_result()->fetch_assoc();
    $stmt_stats->close();

    if (!$stats) {
        throw new Exception("Postagem não encontrada.");
    }

    // 7. BUSCA A ÁRVORE DE COMENTÁRIOS (Recursividade)
    $comentarios_tree = ComentariosLogic::getComentariosCompletos($conn, $post_id, $user_id);

    /**
     * 8. APLICAÇÃO DA CENSURA NA ÁRVORE (Processamento de Exibição)
     * Como a árvore pode ter vários níveis de respostas, usamos uma função recursiva.
     */
    function aplicarCensuraRecursiva(&$itens, $motor) {
        foreach ($itens as &$item) {
            // Censura o comentário atual
            if (isset($item['conteudo_texto'])) {
                $item['conteudo_texto'] = $motor->aplicarMascaraSocial($item['conteudo_texto']);
            }
            // Se houver respostas aninhadas, processa-as também
            if (!empty($item['respostas'])) {
                aplicarCensuraRecursiva($item['respostas'], $motor);
            }
        }
    }

    aplicarCensuraRecursiva($comentarios_tree, $censura);

    // 9. RESPOSTA FINAL (JSON)
    echo json_encode([
        'success'     => true,
        'post_id'     => $post_id,
        'stats'       => [
            'curtidas'          => (int)$stats['total_curtidas'],
            'usuario_curtiu'    => (bool)$stats['usuario_curtiu'],
            'comentarios'       => (int)$stats['total_comentarios'],
            'compartilhamentos' => (int)$stats['total_compartilhamentos']
        ],
        'comentarios' => $comentarios_tree
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}