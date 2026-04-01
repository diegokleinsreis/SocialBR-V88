<?php
/**
 * templates/post_marketplace_card.php
 * Versão: 6.0 - ANTI-TRANSBORDAMENTO & COMPACTAÇÃO
 */

if (!isset($post['anuncio_id']) || empty($post['anuncio_id'])) {
    return;
}

// Configurações de categoria
$categoria_slug = $post['categoria'] ?? 'outros';
$categoria_info = $configMkt['categorias'][$categoria_slug] ?? ['label' => 'Outros', 'icon' => 'fa-box-open'];
$categoria_label = is_array($categoria_info) ? ($categoria_info['label'] ?? 'Outros') : $categoria_info;
$categoria_icon  = is_array($categoria_info) ? ($categoria_info['icon'] ?? 'fa-box-open') : 'fa-box-open';

$is_vendido = ($post['status_venda'] === 'vendido');
$preco_exibicao = $post['preco_formatado'] ?? 'R$ 0,00';
$titulo_anuncio = $post['titulo_produto'] ?? 'Produto sem título';
$desc_anuncio   = $post['descricao_produto'] ?? '';
?>

<style>
    .post-mkt-info-card {
        background: #ffffff;
        border: 1px solid #e4e6eb;
        border-radius: 10px;
        margin: 10px 0;
        padding: 12px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        box-sizing: border-box; /* DICA DE OURO: Garante que o padding não estoure a largura */
        width: 100%;
        max-width: 100%;
        overflow: hidden; /* Evita qualquer transbordamento */
    }

    .mkt-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #f0f2f5;
        padding-bottom: 6px;
    }

    .mkt-price-hero {
        font-size: 1.3rem;
        font-weight: 900;
        color: #1a7f37;
    }

    /* TÍTULO EM NEGRITO E MAIOR (Sua solicitação) */
    .mkt-title-hero {
        font-size: 1.25rem;
        font-weight: 850;
        color: #050505;
        margin: 4px 0;
        line-height: 1.2;
        /* Limita a 1 linha para não empurrar o layout */
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .mkt-meta-row {
        display: flex;
        gap: 10px;
        font-size: 0.8rem;
        color: #65676b;
    }

    /* BLOCO DE DESCRIÇÃO COM LABEL (Sua solicitação) */
    .mkt-desc-container {
        font-size: 0.9rem;
        color: #050505;
        line-height: 1.3;
        margin-top: 2px;
    }

    .mkt-label-bold {
        font-weight: 800;
        color: #4b4b4b;
    }

    .mkt-text-clamped {
        display: inline; /* Mantém na mesma linha do label */
    }

    /* Ajuste para o botão que estava passando da parede direita */
    .btn-mkt-compact-action {
        width: 100% !important;
        box-sizing: border-box !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<div class="post-mkt-info-card">
    <div class="mkt-header-flex">
        <div class="mkt-price-hero"><?php echo $preco_exibicao; ?></div>
        <span class="mkt-badge-status" style="font-size: 0.65rem; font-weight: 800; color: #1877f2;">
            <?php echo $is_vendido ? 'VENDIDO' : 'À VENDA'; ?>
        </span>
    </div>

    <h2 class="mkt-title-hero" title="<?php echo htmlspecialchars($titulo_anuncio); ?>">
        <?php echo htmlspecialchars($titulo_anuncio); ?>
    </h2>

    <div class="mkt-meta-row">
        <span><i class="fas <?php echo $categoria_icon; ?>"></i> <?php echo htmlspecialchars($categoria_label); ?></span>
        <?php if (!empty($post['cidade'])): ?>
            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($post['cidade']); ?></span>
        <?php endif; ?>
    </div>

    <?php if (!empty($desc_anuncio)): ?>
    <div class="mkt-desc-container">
        <span class="mkt-label-bold">Descrição:</span>
        <div class="mkt-text-clamped">
            <?php echo mb_strimwidth(htmlspecialchars($desc_anuncio), 0, 80, "..."); ?>
        </div>
    </div>
    <?php endif; ?>
</div>