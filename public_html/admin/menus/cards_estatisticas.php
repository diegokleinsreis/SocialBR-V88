<?php
/**
 * admin/menus/cards_estatisticas.php
 * Componente: Cards de Resumo da Central de Rotas.
 * VERSÃO: 1.0 (Mobile First - socialbr.lol)
 */

// 1. Coleta de métricas em tempo real
$count_total = $pdo->query("SELECT COUNT(*) FROM Menus_Sistema")->fetchColumn();
$count_menu  = $pdo->query("SELECT COUNT(*) FROM Menus_Sistema WHERE exibir_no_menu = 1 AND status = 1")->fetchColumn();
$count_manut = $pdo->query("SELECT COUNT(*) FROM Menus_Sistema WHERE manutencao_modulo = 1")->fetchColumn();
?>

<div class="col-12 col-md-4 mb-3">
    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0C2D54 !important;">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 bg-light p-3 rounded">
                    <i class="fas fa-database fa-2x" style="color: #0C2D54;"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h6 class="text-muted mb-1 text-uppercase small fw-bold">Total de Rotas</h6>
                    <h2 class="mb-0 fw-bold"><?php echo $count_total; ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-12 col-md-4 mb-3">
    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #2ecc71 !important;">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 bg-light p-3 rounded">
                    <i class="fas fa-list-ul fa-2x text-success"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h6 class="text-muted mb-1 text-uppercase small fw-bold">Links no Menu</h6>
                    <h2 class="mb-0 fw-bold"><?php echo $count_menu; ?></h2>
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
                    <i class="fas fa-tools fa-2x text-danger"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h6 class="text-muted mb-1 text-uppercase small fw-bold">Em Manutenção</h6>
                    <h2 class="mb-0 fw-bold"><?php echo $count_manut; ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>