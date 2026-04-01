<?php
/**
 * views/marketplace/componentes/cartao_produto.php
 * Componente: Cartão de Produto Individual
 * Versão: 11.0 - Botão Amei (Like) + Status Social
 */

if (!isset($id_usuario_logado)) {
    exit;
}

// 1. Tratamento de Imagem do Produto
$imagem_url = 'assets/images/placeholder-image.png';
if (!empty($item['capa_anuncio'])) {
    $img_bd = $item['capa_anuncio'];
    $imagem_url = (strpos($img_bd, 'http') === 0) ? $img_bd : $config['base_path'] . $img_bd;
} else {
    $imagem_url = $config['base_path'] . $imagem_url;
}

// 2. Tratamento do Avatar do Vendedor
$avatar_url = $config['base_path'] . 'assets/images/default-avatar.png'; // Fallback
if (!empty($item['vendedor_avatar'])) {
    $av_bd = $item['vendedor_avatar'];
    $avatar_url = (strpos($av_bd, 'http') === 0) ? $av_bd : $config['base_path'] . $av_bd;
}

// 3. Verifica Status de Venda
$is_vendido = (isset($item['status_venda']) && $item['status_venda'] === 'vendido');
$classe_status = $is_vendido ? 'card-item-vendido' : '';

// 4. Tratamento de Preço
$preco_formatado = isset($item['preco']) ? 'R$ ' . number_format($item['preco'], 2, ',', '.') : 'R$ 0,00';

// 5. Tratamento de Localização
$localizacao = 'Brasil';
if (!empty($item['cidade']) && !empty($item['estado'])) {
    $localizacao = htmlspecialchars($item['cidade']) . ' - ' . htmlspecialchars($item['estado']);
} elseif (!empty($item['estado'])) {
    $localizacao = htmlspecialchars($item['estado']);
}

// 6. Tratamento de Likes (Social)
$eu_curti = !empty($item['eu_curti']); // Booleano vindo da Logic
$total_likes = $item['total_likes'] ?? 0;
$classe_coracao = $eu_curti ? 'fas fa-heart' : 'far fa-heart'; // fas = solido (amei), far = contorno
$cor_botao = $eu_curti ? 'style="color: #e41e3f;"' : ''; // Vermelho se curtiu

// 7. Link do Produto
$link_produto = $config['base_path'] . 'marketplace/item/' . $item['id'];
?>

<a href="<?php echo $link_produto; ?>" class="product-card <?php echo $classe_status; ?>" title="<?php echo htmlspecialchars($item['titulo_produto']); ?>">
    
    <div class="product-img-wrapper">
        <img src="<?php echo htmlspecialchars($imagem_url); ?>" 
             alt="<?php echo htmlspecialchars($item['titulo_produto']); ?>" 
             class="product-img" 
             loading="lazy">
        
        <div class="btn-like-float" 
             onclick="alternarCurtidaFeed(event, <?php echo $item['id']; ?>)"
             <?php echo $cor_botao; ?>
             data-id="<?php echo $item['id']; ?>">
            <i class="<?php echo $classe_coracao; ?>"></i>
            <?php if ($total_likes > 0): ?>
                <span class="like-count-badge"><?php echo $total_likes; ?></span>
            <?php endif; ?>
        </div>

        <?php if ($is_vendido): ?>
            <div class="badge-vendido-overlay">VENDIDO</div>
        <?php endif; ?>

        <?php if (!empty($item['estado']) && !$is_vendido): ?>
            <span class="badge-local">
                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($item['estado']); ?>
            </span>
        <?php endif; ?>
    </div>

    <div class="product-info">
        
        <div class="seller-mini-row">
            <img src="<?php echo htmlspecialchars($avatar_url); ?>" alt="Vendedor" class="seller-avatar-mini">
            <span class="seller-name-mini">
                <?php echo htmlspecialchars($item['vendedor_nome'] ?? 'Vendedor'); ?>
            </span>
        </div>

        <div class="product-price">
            <?php echo $preco_formatado; ?>
        </div>
        
        <div class="product-title">
            <?php echo htmlspecialchars($item['titulo_produto']); ?>
        </div>
        
        <div class="product-location">
            <?php echo $localizacao; ?>
        </div>
    </div>
</a>