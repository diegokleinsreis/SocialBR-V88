<?php
/**
 * templates/post_link_preview.php
 * Componente isolado para renderização de Preview de Links Externos.
 */

// Verifica se existem dados de link para este post
if (empty($post['link_data']['link_url'])) {
    return;
}

$link_url   = $post['link_data']['link_url'];
$link_title = $post['link_data']['link_title'] ?? 'Link Externo';
$link_desc  = $post['link_data']['link_desc'] ?? '';
$link_image = $post['link_data']['link_image'] ?? '';

// Extrai apenas o domínio para exibir no rodapé do card (ex: GOOGLE.COM)
$link_host = strtoupper(parse_url($link_url, PHP_URL_HOST));

// Monta o link de redirecionamento seguro passando pelo seu click.php
$tracking_url = $config['base_path'] . 'click.php?p=' . $post_id . '&u=' . urlencode($link_url);
?>

<div class="post-link-preview" style="margin-top: 12px; border: 1px solid #e4e6eb; border-radius: 8px; overflow: hidden; background: #f0f2f5;">
    <a href="<?php echo $tracking_url; ?>" target="_blank" class="link-preview-wrapper" rel="nofollow" style="text-decoration: none; display: block; color: inherit;">
        
        <?php if (!empty($link_image)): ?>
            <div class="lp-image" style="width: 100%; max-height: 250px; overflow: hidden; border-bottom: 1px solid #e4e6eb;">
                <img src="<?php echo htmlspecialchars($link_image); ?>" alt="Preview" style="width: 100%; height: auto; display: block; object-fit: cover;">
            </div>
        <?php endif; ?>
        
        <div class="lp-body" style="padding: 12px; background: #fff;">
            <h4 style="margin: 0 0 5px 0; font-size: 1rem; color: #050505; font-weight: 700; line-height: 1.2;">
                <?php echo htmlspecialchars($link_title); ?>
            </h4>
            
            <?php if (!empty($link_desc)): ?>
                <p style="margin: 0 0 8px 0; font-size: 0.9rem; color: #65676b; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    <?php echo htmlspecialchars($link_desc); ?>
                </p>
            <?php endif; ?>
            
            <small style="color: #65676b; text-transform: uppercase; font-size: 0.75rem; font-weight: 600; display: flex; align-items: center; gap: 5px;">
                <i class="fas fa-link" style="font-size: 0.7rem;"></i> <?php echo htmlspecialchars($link_host); ?>
            </small>
        </div>
    </a>
</div>