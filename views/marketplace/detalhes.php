<?php
/**
 * views/marketplace/detalhes.php
 * Versão: 11.4 - Galeria Interativa + Social Features + BLINDAGEM CSRF
 */

// 1. INICIALIZAÇÃO E SEGURANÇA
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// Localizadores de Arquivos (Robustez de Caminho)
$pathsConfig = [
    __DIR__ . '/../../config/database.php',
    __DIR__ . '/../../../config/database.php'
];
foreach ($pathsConfig as $path) {
    if (file_exists($path)) { require_once $path; break; }
}

$pathsLogic = [
    __DIR__ . '/../../src/MarketplaceLogic.php',
    __DIR__ . '/../../../src/MarketplaceLogic.php'
];
foreach ($pathsLogic as $path) {
    if (file_exists($path)) { require_once $path; break; }
}

// GATEKEEPER: Se não estiver logado, redireciona para login
if (!isset($_SESSION['user_id'])) {
    $base = isset($config['base_path']) ? $config['base_path'] : '../../';
    header("Location: " . $base . "login");
    exit;
}

// Garantia de conexão PDO
try {
    if (!isset($pdo)) {
        $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
} catch (PDOException $e) {
    die("Erro crítico de conexão com o banco de dados.");
}

// 2. CAPTURA DE DADOS
$anuncio_id = (int)($_GET['id'] ?? $_GET['anuncio'] ?? 0);
$user_id_sessao = $_SESSION['user_id'] ?? null;

$marketplaceLogic = new MarketplaceLogic($pdo);

// Busca dados do anúncio + status social
$item = $marketplaceLogic->obterDetalhesAnuncio($anuncio_id, $user_id_sessao);

if (!$item) {
    // Redireciona se não achar
    $baseRedirect = isset($config['base_path']) ? $config['base_path'] : './';
    header("Location: " . $baseRedirect . "marketplace");
    exit;
}

// 3. PREPARAÇÃO DA GALERIA
$galeriaImagens = [];
$basePath = isset($config['base_path']) ? $config['base_path'] : '../../';

if (!empty($item['fotos']) && is_array($item['fotos'])) {
    foreach ($item['fotos'] as $foto) {
        // Normaliza URL (verifica se é externa ou local)
        $url = (strpos($foto, 'http') === 0) ? $foto : $basePath . $foto;
        $galeriaImagens[] = $url;
    }
} else {
    // Fallback
    $galeriaImagens[] = $basePath . 'assets/images/placeholder-image.png';
}

// 4. PREPARAÇÃO DE DADOS VISUAIS (SOCIAL)
$eu_curti = !empty($item['eu_curti']);
$total_likes = $item['total_likes'] ?? 0;
$classe_btn_amei = $eu_curti ? 'active' : '';
$icone_amei = $eu_curti ? 'fas fa-heart' : 'far fa-heart';

$tenho_interesse = !empty($item['tenho_interesse']);
$total_interessados = $item['total_interessados'] ?? 0;
$classe_btn_interesse = $tenho_interesse ? 'active' : '';
$texto_interesse = $tenho_interesse ? 'Tenho Interesse (Enviado)' : 'Tenho Interesse';

/**
 * Tradução de Condição
 */
function traduzirCondicao($slug) {
    $mapa = [
        'novo' => 'Novo',
        'usado_bom' => 'Usado (Bom estado)',
        'usado_marcas' => 'Usado (Marcas de uso)',
        'defeito' => 'Com defeito / Peças',
        // Legado
        'usado_como_novo' => 'Usado (Como novo)',
        'usado_aceitavel' => 'Usado (Aceitável)',
        'para_pecas' => 'Para conserto/peças'
    ];
    return $mapa[$slug] ?? ucfirst(str_replace('_', ' ', $slug));
}

// 5. ESTRUTURA DA PÁGINA
$page_title = $item['titulo_produto'];

// Inclusão de templates com verificação
$headerPath = __DIR__ . '/../../templates/header.php';
if (file_exists($headerPath)) require_once $headerPath;

$navPath = __DIR__ . '/../../templates/mobile_nav.php'; 
if (file_exists($navPath)) require_once $navPath;
?>
<link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/components/_marketplace_detalhes.css?v=<?php echo time(); ?>">

<div class="mkt-detail-wrapper">
    <div class="mkt-container">
        
        <nav style="margin-bottom: 25px;">
            <a href="<?php echo $basePath; ?>marketplace" class="mkt-back-link">
                <i class="fas fa-arrow-left"></i> Voltar ao Marketplace
            </a>
        </nav>

        <div class="mkt-detail-grid">
            
            <section class="mkt-main-card">
                <div class="mkt-gallery-container">
                    
                    <div class="mkt-image-viewer">
                        <?php if (count($galeriaImagens) > 1): ?>
                            <button class="gallery-nav prev" onclick="MarketplaceGallery.prev()"><i class="fas fa-chevron-left"></i></button>
                            <button class="gallery-nav next" onclick="MarketplaceGallery.next()"><i class="fas fa-chevron-right"></i></button>
                        <?php endif; ?>

                        <img id="main-product-image" 
                             src="<?php echo $galeriaImagens[0]; ?>" 
                             alt="<?php echo htmlspecialchars($item['titulo_produto']); ?>"
                             data-index="0">
                        
                        <?php if ($item['status_venda'] === 'vendido'): ?>
                            <div class="badge-vendido-float">VENDIDO</div>
                        <?php endif; ?>
                    </div>

                    <?php if (count($galeriaImagens) > 1): ?>
                        <div class="mkt-thumbs-track">
                            <?php foreach ($galeriaImagens as $idx => $imgUrl): ?>
                                <div class="mkt-thumb-item <?php echo ($idx === 0) ? 'active' : ''; ?>" 
                                     onclick="MarketplaceGallery.set(<?php echo $idx; ?>)">
                                    <img src="<?php echo $imgUrl; ?>" alt="Foto <?php echo $idx + 1; ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </section>

            <aside class="mkt-sidebar-card">
                
                <div class="mkt-product-header">
                    <h1 class="mkt-product-title"><?php echo htmlspecialchars($item['titulo_produto']); ?></h1>
                    <div class="mkt-product-price">
                        <?php echo $item['preco_formatado']; ?>
                    </div>
                </div>

                <div class="mkt-specs-list" style="margin: 20px 0; padding-bottom: 20px; border-bottom: 1px solid #f0f2f5;">
                    <div style="margin-bottom: 10px; color: #65676b; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-tag" style="width: 20px; color: #1877f2;"></i> 
                        <span>Condição: <strong><?php echo traduzirCondicao($item['condicao']); ?></strong></span>
                    </div>
                    <div style="color: #65676b; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-map-marker-alt" style="width: 20px; color: #1877f2;"></i> 
                        <span>Local: <strong><?php echo htmlspecialchars($item['cidade'] . ' - ' . $item['estado']); ?></strong></span>
                    </div>
                </div>

                <div class="mkt-side-description">
                    <h3>Sobre este item</h3>
                    <div class="mkt-description-text">
                        <?php echo nl2br(htmlspecialchars($item['descricao_completa'])); ?>
                    </div>
                </div>

                <div class="mkt-seller-mini-card">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <img src="<?php echo $basePath . ($item['vendedor_avatar'] ?: 'assets/images/default-avatar.png'); ?>" 
                             class="seller-avatar-large">
                        <div>
                            <div style="font-weight:700; color:#1c1e21; font-size: 0.95rem;">
                                <?php echo htmlspecialchars($item['vendedor_nome_completo']); ?>
                                <?php if (!empty($item['verificado'])): ?>
                                    <i class="fas fa-check-circle" style="color: #1877f2; font-size: 0.85rem;" title="Verificado"></i>
                                <?php endif; ?>
                            </div>
                            <div style="font-size:0.75rem; color:#65676b;">No site desde <?php echo date('Y', strtotime($item['vendedor_desde'] ?? 'now')); ?></div>
                        </div>
                    </div>
                </div>

                <div class="mkt-actions-area">
                    <?php if ($item['is_owner']): ?>
                        <div class="mkt-owner-panel">
                            <a href="<?php echo $basePath; ?>marketplace/editar/<?php echo $item['id']; ?>" class="btn-mkt-small btn-mkt-light">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            
                            <?php if ($item['status_venda'] === 'vendido'): ?>
                                <button onclick="alterarStatus(<?php echo $item['id']; ?>, 'disponivel')" class="btn-mkt-small btn-mkt-blue">
                                    <i class="fas fa-undo"></i> Ativar Anúncio
                                </button>
                            <?php else: ?>
                                <button onclick="alterarStatus(<?php echo $item['id']; ?>, 'vendido')" class="btn-mkt-small btn-mkt-blue">
                                    <i class="fas fa-check"></i> Marcar Vendido
                                </button>
                            <?php endif; ?>

                            <button onclick="confirmarExclusao(<?php echo $item['id']; ?>)" class="btn-mkt-small btn-mkt-delete">
                                <i class="fas fa-trash-alt"></i> Excluir
                            </button>
                        </div>

                    <?php else: ?>
                        <?php if ($item['status_venda'] !== 'vendido'): ?>
                            <a href="https://wa.me/<?php echo preg_replace('/\D/', '', $item['telefone_vendedor'] ?? ''); ?>?text=Olá! Vi seu anúncio '<?php echo urlencode($item['titulo_produto']); ?>' no site e tenho interesse." 
                               target="_blank" class="btn-whatsapp-premium">
                                <i class="fab fa-whatsapp"></i> Conversar com Vendedor
                            </a>
                            
                            <button id="btn-amei-detalhe" 
                                    class="btn-love-big <?php echo $classe_btn_amei; ?>" 
                                    onclick="alternarCurtidaDetalhes(event, <?php echo $item['id']; ?>)">
                                <i class="<?php echo $icone_amei; ?>"></i>
                                <span class="btn-text-amei">Amei</span>
                                <span class="badge-count-detalhe" style="<?php echo ($total_likes == 0) ? 'display:none;' : ''; ?>">
                                    <?php echo $total_likes; ?>
                                </span>
                            </button>

                        <?php else: ?>
                            <div class="item-sold-banner">
                                <i class="fas fa-store-slash"></i> Este item já foi vendido
                            </div>

                            <button id="btn-interesse-detalhe" 
                                    class="btn-interest-big <?php echo $classe_btn_interesse; ?>"
                                    onclick="alternarInteresseDetalhes(event, <?php echo $item['id']; ?>)">
                                <i class="fas fa-hand-paper"></i>
                                <span class="btn-text-interest"><?php echo $texto_interesse; ?></span>
                                <span class="badge-count-detalhe" style="<?php echo ($total_interessados == 0) ? 'display:none;' : ''; ?>">
                                    <?php echo $total_interessados; ?>
                                </span>
                            </button>
                            <p class="interest-help-text">Demonstre interesse para ser notificado se a venda não for concretizada.</p>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>

            </aside>
        </div>
    </div>
</div>

<script>
// --- GLOBAL: Menu Mobile ---
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn') || document.getElementById('mobile-menu-trigger');
    const mobileNavPanel = document.getElementById('mobile-nav-panel');
    const overlay = document.getElementById('overlay');
    const closeBtn = document.getElementById('close-mobile-menu');

    function toggleMenu() {
        if(mobileNavPanel) mobileNavPanel.classList.toggle('active');
        if(overlay) overlay.classList.toggle('active');
    }

    if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function(e) {
        e.preventDefault();
        toggleMenu();
    });

    if(closeBtn) closeBtn.addEventListener('click', toggleMenu);
    if(overlay) overlay.addEventListener('click', toggleMenu);
});

// ===============================================
// 🖼️ NOVA LÓGICA DE GALERIA (MarketplaceGallery)
// ===============================================
const MarketplaceGallery = (function() {
    // Array PHP -> JS
    const images = <?php echo json_encode($galeriaImagens); ?>;
    let currentIndex = 0;

    // Elementos DOM
    const mainImg = document.getElementById('main-product-image');
    const thumbItems = document.querySelectorAll('.mkt-thumb-item');

    // Preload (Otimização de Performance)
    function preloadImages() {
        if (images.length > 1) {
            images.forEach(src => {
                const img = new Image();
                img.src = src;
            });
        }
    }

    function updateView() {
        if (!mainImg) return;
        
        mainImg.style.opacity = '0.8';
        setTimeout(() => {
            mainImg.src = images[currentIndex];
            mainImg.style.opacity = '1';
        }, 150);

        thumbItems.forEach((thumb, idx) => {
            if (idx === currentIndex) thumb.classList.add('active');
            else thumb.classList.remove('active');
        });
    }

    return {
        init: function() { preloadImages(); },
        next: function() {
            if (images.length <= 1) return;
            currentIndex = (currentIndex + 1) % images.length;
            updateView();
        },
        prev: function() {
            if (images.length <= 1) return;
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            updateView();
        },
        set: function(index) {
            if (index >= 0 && index < images.length) {
                currentIndex = index;
                updateView();
            }
        }
    };
})();

document.addEventListener('DOMContentLoaded', MarketplaceGallery.init);


// ===============================================
// ⚡ LÓGICA DE AÇÕES BLINDADA (CSRF + FETCH)
// ===============================================

// Helper para obter o token com segurança
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : null;
}

// --- CURTIDA (AMEI) ---
async function alternarCurtidaDetalhes(event, idAnuncio) {
    event.preventDefault();
    
    // Verifica Token
    const csrfToken = getCsrfToken();
    if (!csrfToken) { alert('Erro de segurança: Token não encontrado. Recarregue a página.'); return; }

    const btn = document.getElementById('btn-amei-detalhe');
    if (!btn) return; 

    const icon = btn.querySelector('i');
    const badge = btn.querySelector('.badge-count-detalhe');
    
    // Feedback Otimista
    const isAtivo = btn.classList.contains('active');
    if (isAtivo) {
        btn.classList.remove('active');
        icon.classList.replace('fas', 'far');
    } else {
        btn.classList.add('active');
        icon.classList.replace('far', 'fas');
    }

    try {
        const response = await fetch('<?php echo $basePath; ?>api/marketplace/alternar_curtida.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                id: idAnuncio,
                csrf_token: csrfToken // 🔒 Token enviado aqui
            })
        });
        const data = await response.json();

        if (data.sucesso) {
            if (data.curtiu) {
                btn.classList.add('active');
                icon.className = 'fas fa-heart';
            } else {
                btn.classList.remove('active');
                icon.className = 'far fa-heart';
            }
            if (data.total > 0) {
                badge.textContent = data.total;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        } else {
            console.error(data.erro);
            // Reverte erro silenciosamente ou alerta
            if(data.erro.includes('Token')) alert(data.erro);
            btn.classList.toggle('active');
        }
    } catch (err) { console.error('Erro de conexão', err); }
}

// --- INTERESSE (FILA DE ESPERA) ---
async function alternarInteresseDetalhes(event, idAnuncio) {
    event.preventDefault();

    const csrfToken = getCsrfToken();
    if (!csrfToken) { alert('Erro de segurança: Token não encontrado.'); return; }

    const btn = document.getElementById('btn-interesse-detalhe');
    const textSpan = btn.querySelector('.btn-text-interest');
    const badge = btn.querySelector('.badge-count-detalhe');
    
    const isAtivo = btn.classList.contains('active');
    
    if (isAtivo) {
        btn.classList.remove('active');
        textSpan.textContent = 'Tenho Interesse';
    } else {
        btn.classList.add('active');
        textSpan.textContent = 'Tenho Interesse (Enviado)';
    }

    try {
        const response = await fetch('<?php echo $basePath; ?>api/marketplace/alternar_interesse.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                id: idAnuncio, 
                csrf_token: csrfToken // 🔒 Token enviado aqui
            })
        });
        const data = await response.json();

        if (data.sucesso) {
            if (data.interessado) {
                btn.classList.add('active');
                textSpan.textContent = 'Tenho Interesse (Enviado)';
            } else {
                btn.classList.remove('active');
                textSpan.textContent = 'Tenho Interesse';
            }
            
            if (data.total > 0) {
                badge.textContent = data.total;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        } else {
            alert('Erro: ' + (data.erro || 'Falha ao registrar interesse.'));
            btn.classList.toggle('active');
        }
    } catch (err) {
        alert('Erro de conexão.');
        btn.classList.toggle('active');
    }
}

// --- GERENCIAMENTO DO DONO ---
async function alterarStatus(anuncioId, novoStatus) {
    const confirmMsg = novoStatus === 'vendido' 
        ? 'Deseja marcar este item como VENDIDO?' 
        : 'Deseja reativar este anúncio?';

    if (!confirm(confirmMsg)) return;

    const csrfToken = getCsrfToken();
    if (!csrfToken) { alert('Erro de segurança.'); return; }

    try {
        const response = await fetch('<?php echo $basePath; ?>api/marketplace/atualizar_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                id: anuncioId, 
                status: novoStatus,
                csrf_token: csrfToken // 🔒 Token enviado aqui
            })
        });
        const data = await response.json();
        if (data.sucesso) window.location.reload();
        else alert('Erro: ' + (data.erro || 'Falha ao atualizar.'));
    } catch (err) { alert('Erro de conexão.'); }
}

async function confirmarExclusao(anuncioId) {
    if (!confirm('Tem certeza que deseja excluir este anúncio?')) return;

    const csrfToken = getCsrfToken();
    if (!csrfToken) { alert('Erro de segurança.'); return; }

    try {
        // Assume-se que excluir_anuncio.php também será blindado no futuro
        const response = await fetch('<?php echo $basePath; ?>api/marketplace/excluir_anuncio.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                id: anuncioId,
                csrf_token: csrfToken 
            })
        });
        const data = await response.json();
        if (data.sucesso) {
            alert('Anúncio excluído.');
            window.location.href = '<?php echo $basePath; ?>marketplace';
        } else { alert('Erro: ' + (data.erro || 'Falha ao excluir.')); }
    } catch (err) { alert('Erro de conexão.'); }
}
</script>

<?php 
$footerPath = __DIR__ . '/../../templates/footer.php';
if (file_exists($footerPath)) require_once $footerPath; 
?>