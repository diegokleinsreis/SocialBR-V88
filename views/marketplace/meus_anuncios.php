<?php
/**
 * views/marketplace/meus_anuncios.php
 * Painel de Gestão do Vendedor (Dashboard com Sidebar Desktop)
 * Versão: 1.4 - Estrutura Identificada com feed.php
 */

// 1. SEGURANÇA E SESSÃO
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: " . $config['base_path'] . "login");
    exit;
}

// Define variável para compatibilidade com sidebar.php e menu_lateral.php
$id_usuario_logado = $_SESSION['user_id'];

// 2. CONEXÃO E LÓGICA
require_once __DIR__ . '/../../src/MarketplaceLogic.php';

if (!isset($pdo)) {
    require_once __DIR__ . '/../../config/database.php';
    try {
        $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Erro de conexão com o banco de dados.");
    }
}

// 3. BUSCA DE DADOS
$marketplaceLogic = new MarketplaceLogic($pdo);

try {
    // Busca estatísticas e lista de anúncios do usuário logado
    $stats = $marketplaceLogic->obterEstatisticasVendedor($id_usuario_logado);
    $meus_anuncios = $marketplaceLogic->listarAnunciosDoUsuario($id_usuario_logado);
} catch (Exception $e) {
    $stats = ['ativos' => 0, 'vendidos' => 0, 'total_views' => 0];
    $meus_anuncios = [];
}

// 4. HEADER E NAVEGAÇÃO
$page_title = "Meus Anúncios";
require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/mobile_nav.php'; 
?>

<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_marketplace_meus_anuncios.css?v=<?php echo time(); ?>">

<div class="main-content-area">
    
    <?php require_once __DIR__ . '/../../templates/sidebar.php'; ?>

    <main class="mkt-feed-content">
        
        <div class="mkt-dashboard-wrapper">
            <div class="mkt-container">
                
                <div class="mkt-dash-header">
                    <div>
                        <h1 class="dash-title">Painel de Vendas</h1>
                        <p class="dash-subtitle">Gerencie seus produtos e acompanhe seus resultados.</p>
                    </div>
                    <a href="<?php echo $config['base_path']; ?>marketplace/vender" class="btn-new-ad">
                        <i class="fas fa-plus-circle"></i> Anunciar Agora
                    </a>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon icon-blue"><i class="fas fa-box-open"></i></div>
                        <div class="stat-info">
                            <span class="stat-value"><?php echo $stats['ativos'] ?? 0; ?></span>
                            <span class="stat-label">Anúncios Ativos</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon icon-green"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-info">
                            <span class="stat-value"><?php echo $stats['vendidos'] ?? 0; ?></span>
                            <span class="stat-label">Vendas Concluídas</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-purple"><i class="fas fa-eye"></i></div>
                        <div class="stat-info">
                            <span class="stat-value"><?php echo number_format($stats['total_views'] ?? 0, 0, ',', '.'); ?></span>
                            <span class="stat-label">Total de Visualizações</span>
                        </div>
                    </div>
                </div>

                <div class="listings-section">
                    <h2 class="section-title">Seu Estoque (<?php echo count($meus_anuncios); ?>)</h2>

                    <?php if (empty($meus_anuncios)): ?>
                        <div class="empty-state-panel">
                            <img src="<?php echo $config['base_path']; ?>assets/images/empty-box.svg" alt="Sem anúncios" onerror="this.style.display='none'">
                            <h3>Comece a vender hoje!</h3>
                            <p>Você ainda não tem anúncios ativos. Desapegue do que não usa e faça uma renda extra.</p>
                            <a href="<?php echo $config['base_path']; ?>marketplace/vender" class="btn-cta-empty">Criar Primeiro Anúncio</a>
                        </div>
                    <?php else: ?>
                        <div class="listings-list">
                            <?php foreach ($meus_anuncios as $item): ?>
                                <?php 
                                    $statusClass = '';
                                    $statusLabel = '';
                                    switch($item['status_venda']) {
                                        case 'vendido': 
                                            $statusClass = 'status-sold'; 
                                            $statusLabel = 'Vendido';
                                            break;
                                        case 'reservado': 
                                            $statusClass = 'status-reserved'; 
                                            $statusLabel = 'Reservado';
                                            break;
                                        default: 
                                            $statusClass = 'status-active'; 
                                            $statusLabel = 'Ativo';
                                    }
                                ?>
                                <div class="listing-item-card">
                                    <div class="listing-thumb">
                                        <img src="<?php echo (strpos($item['capa'], 'http') === 0 ? $item['capa'] : $config['base_path'] . $item['capa']); ?>" alt="Produto">
                                        <span class="badge-status <?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span>
                                    </div>

                                    <div class="listing-details">
                                        <h3 class="listing-title">
                                            <a href="<?php echo $config['base_path']; ?>marketplace/detalhes/<?php echo $item['id']; ?>">
                                                <?php echo htmlspecialchars($item['titulo_produto']); ?>
                                            </a>
                                        </h3>
                                        <div class="listing-price"><?php echo $item['preco_formatado']; ?></div>
                                        <div class="listing-meta">
                                            <span><i class="far fa-clock"></i> <?php echo $item['data_formatada']; ?></span>
                                            <span><i class="far fa-eye"></i> <?php echo $item['visualizacoes']; ?> views</span>
                                        </div>
                                    </div>

                                    <div class="listing-actions">
                                        <a href="<?php echo $config['base_path']; ?>marketplace/editar/<?php echo $item['id']; ?>" class="btn-action" title="Editar">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        
                                        <button onclick="confirmarExclusao(<?php echo $item['id']; ?>)" class="btn-action btn-delete" title="Excluir">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>

                                        <?php if ($item['status_venda'] === 'disponivel'): ?>
                                            <button onclick="alterarStatusRapido(<?php echo $item['id']; ?>, 'vendido')" class="btn-status-toggle btn-mark-sold">
                                                Marcar Vendido
                                            </button>
                                        <?php else: ?>
                                            <button onclick="alterarStatusRapido(<?php echo $item['id']; ?>, 'disponivel')" class="btn-status-toggle btn-mark-active">
                                                Reativar
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    </main>
</div>

<script>
async function alterarStatusRapido(id, status) {
    if(!confirm('Confirmar alteração de status?')) return;
    try {
        const response = await fetch('<?php echo $config['base_path']; ?>api/marketplace/atualizar_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: id, status: status})
        });
        const data = await response.json();
        if(data.sucesso) window.location.reload();
        else alert('Erro: ' + (data.erro || 'Falha na operação'));
    } catch(e) { console.error(e); alert('Erro de conexão'); }
}

async function confirmarExclusao(id) {
    if(!confirm('Tem certeza? Essa ação não pode ser desfeita.')) return;
    try {
        const response = await fetch('<?php echo $config['base_path']; ?>api/marketplace/excluir_anuncio.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: id})
        });
        const data = await response.json();
        if(data.sucesso) window.location.reload();
        else alert('Erro: ' + (data.erro || 'Falha na exclusão'));
    } catch(e) { console.error(e); alert('Erro de conexão'); }
}
</script>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>