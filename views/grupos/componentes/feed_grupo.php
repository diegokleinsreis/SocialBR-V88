<?php
/**
 * views/grupos/componentes/feed_grupo.php
 * Componente: Feed de Notícias do Grupo (Versão Alargada).
 * PAPEL: Renderizar o formulário de postagem e o loop de publicações ricas.
 * VERSÃO: 3.1 (Integração de Feed Rico SOOC - socialbr.lol)
 */

// 1. OBTENÇÃO DE DADOS (Pode usar a variável $posts pré-carregada no ver.php v5.1)
// Caso não esteja definida, fazemos o fallback para segurança.
if (!isset($posts) || !is_array($posts)) {
    $posts_grupo = GruposLogic::getPostsDoGrupo($conn, $id_grupo, $user_id_logado);
} else {
    $posts_grupo = $posts;
}

// Caminho para os templates atómicos globais
$template_path = __DIR__ . '/../../../templates/';
?>

<style>
    /* FORÇA BRUTA: Alarga o container do feed para alinhar com o topo. */
    .group-feed-container {
        display: flex !important;
        flex-direction: column !important;
        gap: 20px !important;
        width: 100% !important;
        max-width: 1000px !important; 
        margin: 0 auto !important;
        padding: 0 !important;
    }

    /* Garante que os cards de postagem ocupem 100% do container */
    .group-feed-container .post-card, 
    .group-feed-container .create-post-card {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    /* Estilização do Estado Vazio */
    .group-empty-feed {
        background: #fff !important;
        padding: 60px 20px !important;
        border-radius: 12px !important;
        text-align: center !important;
        border: 1px solid #e4e6eb !important;
        color: #65676b !important;
    }

    .group-empty-feed i {
        font-size: 4rem !important;
        margin-bottom: 20px !important;
        color: #0C2D54 !important; /* Cor oficial Social BR */
        opacity: 0.3 !important;
        display: block !important;
    }

    .group-empty-feed h3 {
        color: #0C2D54 !important;
        font-size: 1.5rem !important;
        font-weight: 800 !important;
    }

    /* Ajuste responsivo para telas menores */
    @media (max-width: 768px) {
        .group-feed-container {
            padding: 0 10px !important;
        }
    }
</style>

<div class="group-feed-container">

    <?php if ($is_membro || $is_admin): ?>
        <div class="group-form-wrapper">
            <?php 
            /**
             * O form_postagem.php vV91.0 utilizará a variável $id_grupo 
             * para vincular a postagem automaticamente a esta comunidade.
             */
            include $template_path . 'form_postagem.php'; 
            ?>
        </div>
    <?php endif; ?>

    <div id="group-posts-list">
        <?php if (!empty($posts_grupo)): ?>
            <?php foreach ($posts_grupo as $post): ?>
                
                <?php 
                /**
                 * Reutiliza o post_template.php (Orquestrador de posts).
                 * Este template processa as chaves 'midias', 'enquete' e 'link_data'
                 * fornecidas pelo motor GruposLogic v2.3.
                 */
                include $template_path . 'post_template.php'; 
                ?>

            <?php endforeach; ?>
        <?php else: ?>
            
            <div class="group-empty-feed">
                <i class="fas fa-comments"></i>
                <h3>Nenhuma publicação ainda</h3>
                <p>O feed está silencioso por enquanto. Seja o primeiro a partilhar algo!</p>
            </div>

        <?php endif; ?>
    </div>

</div>