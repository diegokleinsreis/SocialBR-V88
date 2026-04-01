<?php
/**
 * admin/erros/cards_estatisticas.php
 * Componente: Cards de Resumo do Monitor Sentinela.
 * VERSÃO: 1.0 (socialbr.lol)
 * PAPEL: Exibir métricas rápidas sobre falhas capturadas.
 */

// 1. Coleta de métricas do Sentinela em tempo real
// Total acumulado de erros registrados
$count_total = $pdo->query("SELECT COUNT(*) FROM Logs_Erros_Sistema")->fetchColumn();

// Erros que ocorreram especificamente no dia de hoje
$count_hoje  = $pdo->query("SELECT COUNT(*) FROM Logs_Erros_Sistema WHERE DATE(data_criacao) = CURDATE()")->fetchColumn();

// Erros que ainda não foram analisados ou corrigidos
$count_pendentes = $pdo->query("SELECT COUNT(*) FROM Logs_Erros_Sistema WHERE status = 'pendente'")->fetchColumn();
?>

<div class="col-12 col-md-4 mb-3">
    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0C2D54 !important;">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 bg-light p-3 rounded">
                    <i class="fas fa-history fa-2x" style="color: #0C2D54;"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h6 class="text-muted mb-1 text-uppercase small fw-bold">Total Histórico</h6>
                    <h2 class="mb-0 fw-bold"><?php echo number_format($count_total, 0, ',', '.'); ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-12 col-md-4 mb-3">
    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #3498db !important;">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 bg-light p-3 rounded">
                    <i class="fas fa-calendar-day fa-2x text-primary"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h6 class="text-muted mb-1 text-uppercase small fw-bold">Erros Hoje</h6>
                    <h2 class="mb-0 fw-bold"><?php echo number_format($count_hoje, 0, ',', '.'); ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-12 col-md-4 mb-3">
    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #e74c3c !important;">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 bg-light p-3 rounded">
                    <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h6 class="text-muted mb-1 text-uppercase small fw-bold">Pendentes</h6>
                    <h2 class="mb-0 fw-bold"><?php echo number_format($count_pendentes, 0, ',', '.'); ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>