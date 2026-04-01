<?php
/**
 * admin_busca.php - Orquestrador do Módulo de Pesquisa
 * VERSÃO: 1.5 - Sincronização com Rota 'Palavras_Proibidas'
 * PAPEL: Centralizar métricas, relatório de erros, blacklist e sinônimos.
 */

// 1. SEGURANÇA: Impede acesso direto sem o roteador
if (!defined('ACESSO_ROTEADOR')) {
    header("Location: /admin/dashboard");
    exit;
}

// 2. INICIALIZAÇÃO DA LÓGICA
require_once __DIR__ . '/../../src/BuscaLogic.php';
$buscaAdmin = new BuscaLogic($conn, $_SESSION['user_id']);

// 3. CAPTURA DE DADOS PARA O DASHBOARD
$topTermos     = $buscaAdmin->getTopTermos(5);
$buscasFalhas  = $buscaAdmin->getBuscasSemSucesso(10);
$statsCliques  = $buscaAdmin->getEstatisticasCliques();
$volumePeriodo = $buscaAdmin->getVolumeBuscasPorPeriodo(7);

$page_title = "Inteligência de Busca";

// 1. O Header abre o HTML, HEAD e BODY e carrega o CSS oficial
include __DIR__ . '/templates/admin_header.php'; 
?>

<link rel="stylesheet" href="<?php echo $config['base_path']; ?>admin/assets/css/components/_admin_busca.css?v=<?php echo time(); ?>">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php 
// 2. Carrega componentes de navegação mobile (Necessário para o menu hambúrguer e fix de largura)
include __DIR__ . '/templates/admin_mobile_nav.php'; 
?>

<div class="main-layout">
    
    <?php include __DIR__ . '/templates/admin_sidebar.php'; ?>

    <main class="main-content">
        <div class="admin-content">
            
            <header class="admin-header-page">
                <div class="header-info">
                    <h1><i class="fas fa-search-plus"></i> Inteligência de Busca</h1>
                    <p>Monitore o comportamento dos usuários e otimize a descoberta de conteúdo.</p>
                </div>
                <div class="header-actions">
                    <button class="btn-premium btn-sync" onclick="location.reload();">
                        <i class="fas fa-sync"></i> Atualizar Dados
                    </button>
                </div>
            </header>

            <section class="busca-stats-grid">
                <?php 
                    // Componente de Cards (Total de Buscas, Taxa de Sucesso, etc)
                    include __DIR__ . '/busca/cards_estatisticas.php'; 
                ?>
            </section>

            <div class="busca-row-dupla">
                <section class="busca-card-container volume-grafico">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-area"></i> Volume de Buscas (Últimos 7 dias)</h3>
                    </div>
                    <div class="card-body" style="position: relative; height: 300px;">
                        <canvas id="chartVolumeBusca"></canvas>
                    </div>
                </section>

                <section class="busca-card-container top-termos">
                    <div class="card-header">
                        <h3><i class="fas fa-fire"></i> Termos em Alta</h3>
                    </div>
                    <div class="card-body">
                        <ul class="lista-termos-rank">
                            <?php foreach($topTermos as $index => $t): ?>
                                <li>
                                    <span class="rank-pos"><?php echo $index + 1; ?>º</span>
                                    <span class="rank-name"><?php echo htmlspecialchars($t['termo']); ?></span>
                                    <span class="rank-count"><?php echo $t['total']; ?> buscas</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </section>
            </div>

            <section class="busca-card-container tabela-erros">
                <div class="card-header">
                    <div class="header-text">
                        <h3><i class="fas fa-exclamation-triangle"></i> Relatório de Lacunas (0 Resultados)</h3>
                        <p>O que os usuários buscam, mas o site não oferece.</p>
                    </div>
                </div>
                <div class="card-body">
                    <?php include __DIR__ . '/busca/tabela_erros.php'; ?>
                </div>
            </section>

            <div class="busca-row-dupla">
                <section class="busca-card-container">
                    <div class="card-header">
                        <h3><i class="fas fa-book"></i> Dicionário de Sinônimos</h3>
                    </div>
                    <div class="card-body">
                        <p>Mapeie gírias e erros comuns para termos corretos.</p>
                        <a href="<?php echo $config['base_path']; ?>admin/busca_sinonimos" class="btn-link">Gerenciar Sinônimos <i class="fas fa-arrow-right"></i></a>
                    </div>
                </section>

                <section class="busca-card-container">
                    <div class="card-header">
                        <h3><i class="fas fa-user-slash"></i> Blacklist de Termos</h3>
                    </div>
                    <div class="card-body">
                        <p>Bloqueie palavras sensíveis das sugestões rápidas.</p>
                        <a href="<?php echo $config['base_path']; ?>admin/Palavras_Proibidas" class="btn-link">Gerenciar Blacklist <i class="fas fa-arrow-right"></i></a>
                    </div>
                </section>
            </div>

        </div>
    </main>
</div>

<script>
    // Configuração do Gráfico de Volume
    const ctxVolume = document.getElementById('chartVolumeBusca').getContext('2d');
    new Chart(ctxVolume, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_reverse(array_column($volumePeriodo, 'data'))); ?>,
            datasets: [{
                label: 'Total de Buscas',
                data: <?php echo json_encode(array_reverse(array_column($volumePeriodo, 'total'))); ?>,
                borderColor: '#0C2D54',
                backgroundColor: 'rgba(12, 45, 84, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
</body>
</html>