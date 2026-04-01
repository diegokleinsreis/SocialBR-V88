<?php
/**
 * views/marketplace/feed.php
 * Feed Principal (V11.1 - Clean Architecture + Social Likes + CSRF Security)
 * Estrutura nativa com sidebar do site e CSS 100% externo.
 */

// 1. SEGURANÇA E SESSÃO
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    // Ajuste o caminho se necessário conforme sua estrutura de pastas
    $baseLogin = isset($config['base_path']) ? $config['base_path'] : '../../';
    header("Location: " . $baseLogin . "login");
    exit;
}

$id_usuario_logado = $_SESSION['user_id'];

// 2. CONEXÃO E LÓGICA
// Localizador de arquivos robusto
$pathsLogic = [
    __DIR__ . '/../../src/MarketplaceLogic.php',
    __DIR__ . '/../../../src/MarketplaceLogic.php'
];
foreach ($pathsLogic as $path) {
    if (file_exists($path)) {
        require_once $path;
        break;
    }
}

if (!isset($pdo)) {
    $pathsDB = [
        __DIR__ . '/../../config/database.php',
        __DIR__ . '/../../../config/database.php'
    ];
    foreach ($pathsDB as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
    
    try {
        if (!isset($pdo)) {
            $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    } catch (PDOException $e) {
        die("Erro de conexão com o banco de dados.");
    }
}

// Atualiza status online
try {
    $pdo->prepare("UPDATE Usuarios SET ultimo_acesso = NOW() WHERE id = ?")->execute([$id_usuario_logado]);
} catch (Exception $e) {}

// 3. BUSCA DE DADOS
$configMktFile = __DIR__ . '/../../config/marketplace.php';
$configMkt = file_exists($configMktFile) ? require $configMktFile : [];

$marketplaceLogic = new MarketplaceLogic($pdo);

$filtros = [
    'busca'     => $_GET['q'] ?? null,
    'categoria' => $_GET['categoria'] ?? null,
    'estado'    => $_GET['estado'] ?? null
];

try {
    // ATUALIZAÇÃO V11.0: Passamos $id_usuario_logado como 4º parâmetro
    // Isso permite que a Logic identifique os itens que EU curti.
    $anuncios = $marketplaceLogic->listarAnuncios($filtros, 1, 40, $id_usuario_logado);
} catch (Exception $e) {
    $anuncios = [];
}

// 4. HEADER E NAVEGAÇÃO
$page_title = "Marketplace";
// Tenta incluir o header que deve conter o <meta name="csrf-token">
$headerPath = __DIR__ . '/../../templates/header.php';
if (file_exists($headerPath)) require_once $headerPath;

$mobileNavPath = __DIR__ . '/../../templates/mobile_nav.php'; 
if (file_exists($mobileNavPath)) require_once $mobileNavPath;
?>

<div class="main-content-area">
    
    <?php 
    $sidebarPath = __DIR__ . '/../../templates/sidebar.php';
    if (file_exists($sidebarPath)) require_once $sidebarPath; 
    ?>

    <main class="mkt-feed-content">
        
        <?php 
        $barraFerramentas = __DIR__ . '/componentes/barra_ferramentas.php';
        if (file_exists($barraFerramentas)) include $barraFerramentas; 
        ?>

        <?php 
        $barraCategorias = __DIR__ . '/componentes/barra_categorias.php';
        if (file_exists($barraCategorias)) include $barraCategorias; 
        ?>

        <div class="mkt-search-wrapper">
            <form action="" method="GET" class="mkt-search-box">
                <input type="text" name="q" placeholder="Pesquisar no Marketplace..." 
                       value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                <button type="submit" aria-label="Pesquisar">
                    <i class="fas fa-search"></i>
                </button>
                
                <?php if(!empty($_GET['categoria'])): ?>
                    <input type="hidden" name="categoria" value="<?php echo htmlspecialchars($_GET['categoria']); ?>">
                <?php endif; ?>
            </form>
        </div>

        <div class="marketplace-grid">
            <?php if (empty($anuncios)): ?>
                <div class="mkt-no-results">
                    <i class="fas fa-search"></i>
                    <h3>Nenhum item encontrado</h3>
                    <p>Tente buscar por outro termo ou categoria.</p>
                    <a href="<?php echo isset($config['base_path']) ? $config['base_path'] : '/'; ?>marketplace" class="btn-clear">Limpar Filtros</a>
                </div>
            <?php else: ?>
                <?php 
                foreach ($anuncios as $item) {
                    $cartaoPath = __DIR__ . '/componentes/cartao_produto.php';
                    if (file_exists($cartaoPath)) include $cartaoPath;
                } 
                ?>
            <?php endif; ?>
        </div>

    </main>
</div>

<script>
/**
 * Alterna curtida no Marketplace sem recarregar (AJAX)
 * Versão Blindada: Envia CSRF Token
 */
async function alternarCurtidaFeed(event, idAnuncio) {
    // 1. Impede que o clique abra o link do produto
    event.preventDefault();
    event.stopPropagation();

    // 2. Elementos visuais
    const btn = event.currentTarget;
    const icon = btn.querySelector('i');
    const badge = btn.querySelector('.like-count-badge');

    // 3. Feedback Tátil Imediato (Otimista)
    const isVazio = icon.classList.contains('far');
    if (isVazio) {
        icon.classList.replace('far', 'fas');
        icon.style.color = '#e41e3f';
    } else {
        icon.classList.replace('fas', 'far');
        icon.style.color = ''; // Volta a cor original
    }
    
    // Animação de pulso
    btn.style.transform = 'scale(1.2)';
    setTimeout(() => btn.style.transform = 'scale(1)', 200);

    // 🔒 SEGURANÇA: Captura o token do Header
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!csrfToken) {
        console.error("Erro de Segurança: CSRF Token não encontrado no <head>.");
        alert("Erro de segurança: Recarregue a página.");
        return;
    }

    try {
        // 4. Chamada à API com Token
        const response = await fetch('api/marketplace/alternar_curtida.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                id: idAnuncio,
                csrf_token: csrfToken // <--- O SEGREDO ESTÁ AQUI
            })
        });

        const data = await response.json();

        if (data.sucesso) {
            // 5. Sincronização Real com o Servidor
            if (data.curtiu) {
                icon.className = 'fas fa-heart';
                icon.style.color = '#e41e3f';
            } else {
                icon.className = 'far fa-heart';
                icon.style.color = '';
            }

            // 6. Atualiza Badge de Contagem
            if (data.total > 0) {
                if (badge) {
                    badge.textContent = data.total;
                } else {
                    const newBadge = document.createElement('span');
                    newBadge.className = 'like-count-badge';
                    newBadge.textContent = data.total;
                    btn.appendChild(newBadge);
                }
            } else {
                if (badge) badge.remove();
            }

        } else {
            console.error('Erro ao curtir:', data.erro);
            // Reverte visualmente em caso de erro
            if (isVazio) {
                icon.classList.replace('fas', 'far');
                icon.style.color = '';
            } else {
                icon.classList.replace('far', 'fas');
                icon.style.color = '#e41e3f';
            }
        }

    } catch (error) {
        console.error('Erro de conexão:', error);
    }
}
</script>

<?php 
$footerPath = __DIR__ . '/../../templates/footer.php';
if (file_exists($footerPath)) require_once $footerPath; 
?>