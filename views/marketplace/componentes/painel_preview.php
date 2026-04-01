<?php
// views/marketplace/componentes/painel_preview.php
if (!isset($id_usuario_logado)) exit;
?>

<div class="mkt-preview-box">
    
    <div class="preview-section-label">
        <i class="fas fa-th-large"></i> Como aparece no Feed
    </div>

    <div class="preview-block">
        <div class="product-card-preview" style="pointer-events: none;">
            <div class="product-img-wrapper">
                <div id="preview-placeholder-container" class="preview-empty-state-img">
                    <div class="empty-state-content">
                        <i class="fas fa-images"></i>
                        <span>Sua Foto</span>
                    </div>
                </div>
                
                <img id="preview-img-main" src="" class="product-img" style="display:none;">
                
                <button type="button" id="preview-btn-prev" class="preview-nav-btn preview-nav-prev" style="display:none;">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button type="button" id="preview-btn-next" class="preview-nav-btn preview-nav-next" style="display:none;">
                    <i class="fas fa-chevron-right"></i>
                </button>

                <span class="badge-local" id="preview-badge-local" style="display:none;">
                    <span id="preview-text-estado">UF</span>
                </span>
            </div>

            <div class="preview-info">
                <div class="preview-price" id="preview-text-preco">R$ 0,00</div>
                <div class="preview-title" id="preview-text-titulo">Título do anúncio</div>
                <div class="preview-meta" id="preview-text-cidade">Localização</div>
            </div>
        </div>
    </div>

    <div class="preview-section-label">
        <i class="fas fa-info-circle"></i> Detalhes do Item
    </div>

    <div class="preview-block">
        <div class="preview-detail-content">
            <div class="detail-row-item">
                <span>Categoria</span>
                <span id="preview-detail-categoria">--</span>
            </div>
            <div class="detail-row-item">
                <span>Condição</span>
                <span id="preview-detail-condicao">--</span>
            </div>
            
            <h4 style="font-size:0.9rem; font-weight:700; margin:15px 0 8px 0; color:#050505;">Descrição</h4>
            <p id="preview-detail-desc" style="font-size:0.9rem; color:#65676b; white-space: pre-wrap; line-height:1.5;">A descrição que você digitar aparecerá aqui...</p>

            <div class="preview-seller-row">
                <img src="<?php echo htmlspecialchars($avatar_usuario); ?>" class="preview-seller-avatar">
                <div>
                    <div style="font-weight:700; font-size:0.95rem; color:#050505;"><?php echo htmlspecialchars($nome_usuario); ?></div>
                    <div style="font-size:0.8rem; color:#65676b;">Vendedor</div>
                </div>
            </div>
        </div>
    </div>

</div>