<?php
/**
 * views/salvos/componentes/card_item_salvo.php
 * Componente Atómico: Card de Item Salvo (Estilo Premium Adaptativo).
 * PAPEL: Renderizar posts normais, enquetes ou Marketplace com suporte a mídias.
 * VERSÃO: V76.0 (Sincronizado com SalvosLogic V75.0 - socialbr.lol)
 */

// 1. Preparação e Sanitização
$id_postagem    = (int)$item['id_postagem'];
$tipo_post      = $item['post_tipo'] ?? 'padrao'; 
$texto_raw      = $item['conteudo_texto'] ?? '';
$texto_resumo   = mb_strimwidth(strip_tags($texto_raw), 0, 160, "...");
$data_formatada = date('d/m/Y', strtotime($item['data_criacao'] ?? 'now'));
$autor_nome     = htmlspecialchars($item['autor_nome'] ?? 'Usuário');
$colecao_nome   = htmlspecialchars($item['colecao_nome'] ?? 'Geral');
$link_origem    = $config['base_path'] . "postagem/" . $id_postagem . "?ref=saved_module";

// 2. Inteligência de Mídia (Captura fotos de Posts Normais e Marketplace)
$tem_midia = !empty($item['url_media']);
$url_capa  = $tem_midia ? $config['base_path'] . htmlspecialchars($item['url_media']) : null;

// 3. Lógica de Identificação Estrutural
$eh_enquete = (bool)($item['is_enquete'] ?? false);
$eh_venda   = ($tipo_post === 'venda');

$badge_icon = 'fa-newspaper';
$badge_label = 'Publicação';

if ($eh_venda) {
    $badge_icon = 'fa-shopping-bag';
    $badge_label = 'Marketplace';
} elseif ($eh_enquete) {
    $badge_icon = 'fa-poll-h';
    $badge_label = 'Enquete';
}

// 4. Inteligência de Título e Preço
$titulo_exibicao = $texto_resumo;
$preco_venda     = null;

if ($eh_venda) {
    // Prioriza o título do produto e o preço se for Marketplace
    $titulo_exibicao = htmlspecialchars($item['mkt_titulo'] ?? 'Produto sem título');
    $valor_bruto     = $item['mkt_preco'] ?? 0;
    $preco_venda     = ($valor_bruto > 0) ? 'R$ ' . number_format($valor_bruto, 2, ',', '.') : 'Grátis';
} elseif (empty($titulo_exibicao) && $eh_enquete) {
    // Prioriza a pergunta se for enquete sem texto
    $pergunta_enquete = $item['enquete_pergunta'] ?? '';
    $titulo_exibicao = mb_strimwidth(htmlspecialchars($pergunta_enquete), 0, 160, "...");
}

if (empty($titulo_exibicao)) {
    $titulo_exibicao = '<span class="empty-text">Sem descrição textual</span>';
}
?>

<article class="saved-card-item <?php echo !$tem_midia ? 'no-media-card' : ''; ?> <?php echo $eh_venda ? 'mkt-card-style' : ''; ?>" data-id="<?php echo $id_postagem; ?>">
    
    <?php if ($tem_midia): ?>
        <div class="saved-card-media">
            <a href="<?php echo $link_origem; ?>">
                <img src="<?php echo $url_capa; ?>" alt="Imagem do item" loading="lazy" onerror="this.src='<?php echo $config['base_path']; ?>assets/images/placeholder-saved.png'">
            </a>
            <div class="type-badge" title="<?php echo $badge_label; ?>">
                <i class="fas <?php echo $badge_icon; ?>"></i>
            </div>
        </div>
    <?php endif; ?>

    <div class="saved-card-content">
        
        <div class="saved-card-header">
            <div class="author-info">
                <span class="author-name"><?php echo $autor_nome; ?></span>
                <span class="post-date">• <?php echo $data_formatada; ?></span>
            </div>
            
            <div class="saved-card-options">
                <button class="btn-item-options" onclick="toggleDropdownSalvos(<?php echo $id_postagem; ?>)" aria-label="Opções">
                    <i class="fas fa-ellipsis-h"></i>
                </button>
                <div id="dropdown-salvo-<?php echo $id_postagem; ?>" class="saved-dropdown-menu is-hidden">
                    <a href="javascript:void(0)" onclick="abrirModalMoverColecao(<?php echo $id_postagem; ?>)">
                        <i class="fas fa-folder-open"></i> Mover para Coleção
                    </a>
                    <a href="javascript:void(0)" onclick="removerItemSalvo(<?php echo $id_postagem; ?>)" class="text-danger">
                        <i class="fas fa-trash-alt"></i> Remover dos Salvos
                    </a>
                </div>
            </div>
        </div>

        <div class="saved-card-body">
            <a href="<?php echo $link_origem; ?>" class="saved-item-link">
                <?php if ($eh_venda && $preco_venda): ?>
                    <div class="saved-mkt-price" style="color: #1a7f37; font-weight: 900; font-size: 1.1rem; margin-bottom: 4px;">
                        <?php echo $preco_venda; ?>
                    </div>
                <?php endif; ?>

                <?php if ($eh_enquete): ?>
                    <div class="saved-poll-indicator" style="font-size: 0.7rem; color: #0C2D54; font-weight: 800; margin-bottom: 6px; display: flex; align-items: center; gap: 5px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="fas fa-poll-h"></i> Enquete Ativa
                    </div>
                <?php endif; ?>
                
                <h4 class="saved-item-text" style="<?php echo $eh_venda ? 'font-weight: 800; color: #050505;' : ''; ?>">
                    <?php echo $titulo_exibicao; ?>
                </h4>
            </a>
        </div>

        <div class="saved-card-footer">
            <div class="collection-tag">
                <i class="fas fa-folder"></i> <span><?php echo $colecao_nome; ?></span>
            </div>
            
            <div class="saved-actions">
                <a href="<?php echo $link_origem; ?>" class="btn-go-to">
                    Ver <?php echo $eh_venda ? 'Anúncio' : 'Original'; ?>
                </a>
            </div>
        </div>

    </div>
</article>