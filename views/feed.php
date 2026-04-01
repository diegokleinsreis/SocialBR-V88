<?php
/**
 * views/feed.php
 * FEED PRINCIPAL (V125.4)
 * PAPEL: Orquestrador do Feed - Estrutura Atômica e Limpa.
 * VERSÃO: V125.4 (socialbr.lol)
 * AJUSTE: Remoção de tags estruturais duplicadas para correção de JS/UX.
 */

// 1. VERIFICA SE O UTILIZADOR ESTÁ LOGADO
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $config['base_path'] . "login"); 
    exit();
}
$user_id = $_SESSION['user_id'];

// 2. INCLUI O "CÉREBRO" DO FEED
require_once __DIR__ . '/../src/FeedLogic.php';

// 3. BUSCA OS POSTS (Página 1: Limit 10, Offset 0)
$posts_para_exibir = FeedLogic::getFeedPosts($conn, $user_id, 10, 0);

// 4. DEFINE O TÍTULO DA PÁGINA (Deve ser antes do header)
$page_title = 'Feed - ' . htmlspecialchars($config['site_nome']);

// 5. INCLUI O HEADER (Abre <!DOCTYPE>, <html>, <head> e <body>)
include '../templates/header.php'; 

// 6. INCLUI NAVEGAÇÃO MOBILE (Painel lateral mobile)
include '../templates/mobile_nav.php'; 
?>

<div class="main-content-area" style="align-items: flex-start;">
    
    <?php 
    // Sidebar Principal (Desktop)
    include '../templates/sidebar.php'; 
    ?> 

    <main class="feed-container">
        
        <?php 
        // Template do formulário de postagem
        include '../templates/form_postagem.php'; 
        ?>

        <div id="feed-posts-container">
            <?php if (!empty($posts_para_exibir)): ?>
                <?php foreach ($posts_para_exibir as $post): ?>
                    <?php include '../templates/post_template.php'; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="post-card" id="empty-feed-msg">
                    <p>Ainda não há nenhuma postagem no feed.</p>
                </div>
            <?php endif; ?>
        </div>

        <div id="infinite-scroll-sentinel" class="infinite-scroll-sentinel">
            <div class="spinner" id="feed-loader"></div>
            <p class="no-more-posts" id="no-more-posts-msg">Você chegou ao fim das publicações.</p>
        </div>
        
    </main>
</div>

<?php 
// 7. INCLUI O RODAPÉ (Scripts Globais e fecho de </body></html>)
include '../templates/footer.php'; 
?>