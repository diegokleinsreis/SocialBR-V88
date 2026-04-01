<?php
/**
 * admin/busca/cards_estatisticas.php
 * PAPEL: Exibir indicadores chave de performance (KPIs) da busca.
 * VERSÃO: 1.0 - Integração com BuscaLogic v2.0
 */

// 1. GARANTIA DE DADOS (Caso as variáveis não tenham sido injetadas pelo orquestrador)
if (!isset($conn)) {
    return; // Proteção contra acesso direto
}

// 2. BUSCA DE MÉTRICAS EM TEMPO REAL
// Total de pesquisas realizadas
$sqlTotal = "SELECT COUNT(*) as total FROM busca_interacoes";
$resTotal = $conn->query($sqlTotal);
$totalBuscas = $resTotal ? $resTotal->fetch_assoc()['total'] : 0;

// Total de pesquisas que NÃO retornaram nada (Lacunas)
$sqlFalhas = "SELECT COUNT(*) as total FROM busca_interacoes WHERE total_resultados = 0";
$resFalhas = $conn->query($sqlFalhas);
$totalFalhas = $resFalhas ? $resFalhas->fetch_assoc()['total'] : 0;

// Total de cliques em resultados (Engajamento Real)
$sqlCliques = "SELECT COUNT(*) as total FROM busca_interacoes WHERE id_alvo IS NOT NULL";
$resCliques = $conn->query($sqlCliques);
$totalCliques = $resCliques ? $resCliques->fetch_assoc()['total'] : 0;

// Cálculo de Taxa de Sucesso (Buscas com resultado vs Total)
$taxaSucesso = ($totalBuscas > 0) ? round((($totalBuscas - $totalFalhas) / $totalBuscas) * 100, 1) : 0;
?>

<div class="busca-stat-card">
    <div class="stat-icon icon-blue">
        <i class="fas fa-search"></i>
    </div>
    <div class="stat-details">
        <span class="stat-label">Total de Pesquisas</span>
        <h2 class="stat-value"><?php echo number_format($totalBuscas, 0, ',', '.'); ?></h2>
        <span class="stat-meta">Volume acumulado</span>
    </div>
</div>

<div class="busca-stat-card">
    <div class="stat-icon icon-green">
        <i class="fas fa-check-circle"></i>
    </div>
    <div class="stat-details">
        <span class="stat-label">Taxa de Sucesso</span>
        <h2 class="stat-value"><?php echo $taxaSucesso; ?>%</h2>
        <span class="stat-meta">Buscas com resultados</span>
    </div>
</div>

<div class="busca-stat-card">
    <div class="stat-icon icon-purple">
        <i class="fas fa-mouse-pointer"></i>
    </div>
    <div class="stat-details">
        <span class="stat-label">Cliques Gerados</span>
        <h2 class="stat-value"><?php echo number_format($totalCliques, 0, ',', '.'); ?></h2>
        <span class="stat-meta">Interações com resultados</span>
    </div>
</div>

<div class="busca-stat-card">
    <div class="stat-icon icon-orange">
        <i class="fas fa-ghost"></i>
    </div>
    <div class="stat-details">
        <span class="stat-label">Buscas sem Retorno</span>
        <h2 class="stat-value"><?php echo number_format($totalFalhas, 0, ',', '.'); ?></h2>
        <span class="stat-meta">Oportunidades perdidas</span>
    </div>
</div>