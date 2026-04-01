<?php
/**
 * templates/post/post_shared_content.php
 * Componente para renderizar o conteúdo original dentro de um compartilhamento.
 * VERSÃO: V9.3 (Integração com CensuraLogic - socialbr.lol)
 */

if (!isset($post['post_original_id'])) return;

// 1. DADOS DO AUTOR ORIGINAL (Mantendo sua lógica original)
$orig_autor_nome      = $post['original_autor_nome'] ?? 'Usuário';
$orig_autor_sobrenome = $post['original_autor_sobrenome'] ?? '';
$orig_conteudo        = $post['original_conteudo_texto'] ?? '';
$orig_foto            = $post['original_autor_foto'] ?? null;
$orig_data            = $post['original_data_postagem'] ?? null;
$orig_avatar          = !empty($orig_foto) ? $config['base_path'].$orig_foto : $config['base_path'].'assets/images/default-avatar.png';

/**
 * APLICAÇÃO DA MÁSCARA SOCIAL (Higiene no Compartilhamento)
 * O objeto $censura é herdado do post_template.php.
 */
if (isset($censura)) {
    $orig_conteudo = $censura->aplicarMascaraSocial($orig_conteudo);
}

// 2. VERIFICAÇÃO DE TIPO (Marketplace)
$is_marketplace = ($post['tipo_post'] === 'venda' && !empty($post['anuncio_id']));
?>

<div class="shared-content-wrapper" style="border: 1px solid #e4e6eb; border-radius: 12px; margin-top: 10px; padding: 12px; background: #fff; box-shadow: inset 0 0 5px rgba(0,0,0,0.02);">
    
    <div class="shared-header" style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
        <img src="<?php echo $orig_avatar; ?>" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;" onerror="this.src='<?php echo $config['base_path']; ?>assets/images/default-avatar.png'">
        <div>
            <strong style="display: block; font-size: 0.85rem; color: #050505;"><?php echo htmlspecialchars($orig_autor_nome . ' ' . $orig_autor_sobrenome); ?></strong>
            <small style="color: #65676b; font-size: 0.7rem;"><?php echo $orig_data ? date("d/m/Y", strtotime($orig_data)) : ''; ?></small>
        </div>
    </div>

    <div class="shared-body-content">
        <?php if ($is_marketplace): ?>
            <?php include __DIR__ . '/post_marketplace_card.php'; ?>
        <?php else: ?>
            <p style="font-size: 0.9rem; color: #1c1e21; line-height: 1.4; margin-bottom: 10px;">
                <?php echo nl2br(htmlspecialchars($orig_conteudo)); ?>
            </p>
        <?php endif; ?>

        <div class="shared-media-container" style="margin-top: 10px; border-radius: 8px; overflow: hidden;">
            <?php 
                // Como este arquivo é um include, as mídias em $post['midias'] já estão disponíveis para a grade
                include __DIR__ . '/grade_midia.php'; 
            ?>
        </div>

        <?php if ($is_marketplace): ?>
            <a href="<?php echo $config['base_path']; ?>marketplace/item/<?php echo $post['anuncio_id']; ?>" 
               class="btn-mkt-shared-action" 
               style="margin-top: 12px; display: block; background: #f0f2f5; color: #050505; text-align: center; padding: 10px; border-radius: 8px; font-weight: 700; font-size: 0.85rem; text-decoration: none; border: 1px solid #ccd0d5; transition: 0.2s;">
                <i class="fas fa-shopping-bag"></i> Ver Anúncio Original
            </a>
        <?php endif; ?>
    </div>
</div>