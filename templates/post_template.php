<?php
/**
 * templates/post_template.php
 * VERSÃO: V130.5 (Orquestração Limpa para Modal de Edição - socialbr.lol)
 * PAPEL: Orquestrador Master do Post - Minimalismo e Performance.
 */

if (!isset($config)) {
    $paths = [__DIR__ . '/../config/database.php', $_SERVER['DOCUMENT_ROOT'] . '/config/database.php'];
    foreach ($paths as $path) { if (file_exists($path)) { require_once $path; break; } }
}

// --- INICIALIZAÇÃO DO ESCUDO DE CENSURA (Exibição apenas) ---
require_once __DIR__ . '/../src/CensuraLogic.php';
if (!isset($db)) {
    if (isset($conn)) { $db = $conn; } 
    elseif (isset($pdo)) { $db = $pdo; }
    elseif (isset($conexao)) { $db = $conexao; }
}
$censura = new CensuraLogic($db, $config);

// --- 1. PREPARAÇÃO DE DADOS (Cérebro do Post) ---
$post_id         = $post['id'] ?? 0;
$autor_id        = $post['autor_id'] ?? 0;
$autor_nome      = $post['nome'] ?? 'Usuário';
$autor_sobrenome = $post['sobrenome'] ?? '';
$autor_foto      = $post['foto_perfil_url'] ?? null;
$post_data       = $post['data_postagem'] ?? date('Y-m-d H:i:s');
$conteudo_texto  = $post['conteudo_texto'] ?? '';
$tipo_post       = $post['tipo_post'] ?? 'comum'; 
$is_share        = !empty($post['post_original_id']);
$limite_chars    = 350;

/**
 * APLICAÇÃO DA MÁSCARA SOCIAL (Higiene de Exibição)
 * Traduz ofensas para símbolos apenas na memória.
 */
$conteudo_texto = $censura->aplicarMascaraSocial($conteudo_texto);

// --- 2. DADOS SOCIAIS ---
$total_curtidas           = $post['total_curtidas'] ?? 0;
$total_comentarios        = $post['total_comentarios'] ?? 0;
$total_compartilhamentos  = $post['total_compartilhamentos'] ?? 0;

// --- 3. ASSETS E LINKS ---
$perfil_link = $config['base_path'] . 'perfil/' . $autor_id;
$avatar_src  = !empty($autor_foto) ? $config['base_path'] . htmlspecialchars($autor_foto) : $config['base_path'] . 'assets/images/default-avatar.png';
$sharable_post_id = $is_share ? $post['post_original_id'] : $post_id;

$component_path = __DIR__ . '/post/';
?>

<div class="post post-card" 
     id="post-<?php echo $post_id; ?>" 
     data-post-id="<?php echo $post_id; ?>"
     style="overflow: hidden; border: 1px solid #e4e6eb; border-radius: 12px; margin-bottom: 20px; background: #fff; position: relative;">
    
    <?php 
        // A. CABEÇALHO (Foto, Nome e Opções)
        include $component_path . 'cabecalho.php'; 

        // B. CONTEÚDO DE TEXTO (Envolvido em .post-content para sincronia com a atualização via Modal)
        echo '<div class="post-content">';
            if ($tipo_post !== 'venda' || $is_share) {
                include $component_path . 'corpo_texto.php';
            }
        echo '</div>';
    ?>

    <div class="post-content-body">
        <?php 
            if ($is_share) {
                include $component_path . 'post_shared_content.php';
            } else {
                if ($tipo_post === 'venda') {
                    include $component_path . 'post_marketplace_card.php';
                }
                // Grelha de Mídia (Fotos e Vídeos)
                include $component_path . 'grade_midia.php';
            }

            // Integrações (Links e Enquetes)
            if (!empty($post['link_data']['link_url'])) include $component_path . 'post_link_preview.php';
            if (!empty($post['enquete'])) include $component_path . 'post_poll.php';
        ?>
    </div>

    <div class="post-social-footer" style="background: #fafafa; border-top: 1px solid #f0f2f5;">
        <?php 
            include $component_path . 'barra_estatisticas.php';
            include $component_path . 'botoes_acao.php';
            include $component_path . 'lista_comentarios.php';
            include $component_path . 'campo_comentario.php';
        ?>
    </div>
</div>