<?php
/**
 * api/postagens/obter_posts_paginados.php
 * API: Fornece blocos de posts via AJAX para o Scroll Infinito.
 * VERSÃO INTEGRAL: Retorno JSON robusto, tratamento de erros e inclusão correta do template.
 * VERSÃO: V70.1 (socialbr.lol)
 */

// 1. Configurações de Cabeçalho e Erros
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0); // Oculta erros na tela (produção)
error_reporting(E_ALL);       // Mas registra tudo no log

// Inicia sessão se necessário
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. SEGURANÇA: Verifica Login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Sessão expirada.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// 3. INCLUDES E DEPENDÊNCIAS
$base_dir = __DIR__ . '/../../../'; // Sobe para a raiz public_html/

// Carrega conexão com o banco
$db_path = $base_dir . 'config/database.php';
if (file_exists($db_path)) {
    require_once $db_path;
} else {
    echo json_encode(['success' => false, 'error' => 'Erro interno: Banco não encontrado.']);
    exit;
}

// Carrega a lógica de Feed (Essencial para herdar o Escudo de Bloqueio V111.5)
$logic_path = $base_dir . 'src/FeedLogic.php';
if (!file_exists($logic_path)) {
    echo json_encode(['success' => false, 'error' => 'Erro interno: Lógica do Feed não encontrada.']);
    exit;
}
require_once $logic_path;

// 4. PARÂMETROS DA REQUISIÇÃO
$limit = 10; 
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Calcula o ponto de partida (Offset)
$offset = ($page - 1) * $limit;

try {
    // 5. BUSCA NO BANCO DE DADOS
    // AJUSTE CRÍTICO: Agora herda automaticamente a proteção contra usuários bloqueados
    $posts_adicionais = FeedLogic::getFeedPosts($conn, $user_id, $limit, $offset);

    // 6. RENDERIZAÇÃO DO HTML
    ob_start(); 

    if (!empty($posts_adicionais)) {
        foreach ($posts_adicionais as $post) {
            // O container "post-card" é obrigatório para o JS localizar os botões de ação
            echo '<div class="post-card" id="post-' . $post['id'] . '">';
            
            $is_pagina_postagem = false; // Define o contexto de feed
            
            // Renderiza o visual usando o template central
            include $base_dir . 'templates/post_template.php';
            
            echo '</div>';
        }
    }
    
    $html_renderizado = ob_get_clean();

    // 7. VERIFICA SE HÁ MAIS POSTS
    $has_more = (count($posts_adicionais) >= $limit);

    // 8. RESPOSTA FINAL
    echo json_encode([
        'success'  => true,
        'html'     => $html_renderizado,
        'has_more' => $has_more,
        'page'     => $page 
    ]);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro no servidor: ' . $e->getMessage()]);
}
?>