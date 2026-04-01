<?php
/**
 * ARQUIVO: admin/admin_erros.php
 * VERSÃO: 1.2 (Final Layout Fix - socialbr.lol)
 * PAPEL: Esqueleto Orquestrador para gestão de erros e exceções.
 * ESTILO: Responsividade Premium (Bootstrap 5 + Custom CSS)
 */

// 1. --- [SEGURANÇA E DEPENDÊNCIAS] ---
if (!defined('ACESSO_ROTEADOR')) {
    die("Acesso direto negado.");
}

// Ajuste de caminho para ErrorLogic
require_once __DIR__ . '/../../src/ErrorLogic.php';
$sentinelaLogic = new ErrorLogic($pdo);

// 2. --- [CABEÇALHO E ASSETS] ---
require_once __DIR__ . '/templates/admin_header.php';
?>

<style>
    /**
     * AJUSTE DE CONTENÇÃO (Cirúrgico)
     * Garante que o conteúdo não colida com a sidebar e header fixos.
     */
    .admin-wrapper {
        min-height: 100vh;
        background-color: #f8f9fa;
    }

    .admin-main-content {
        /* Compensa o Header Fixo (70px) */
        margin-top: 70px; 
        transition: all 0.3s ease;
        width: 100%;
        display: block;
    }

    /* Ajuste para Desktop (Respeita a Sidebar de 280px) */
    @media (min-width: 992px) {
        .admin-main-content {
            margin-left: 280px;
            max-width: calc(100% - 280px);
        }
    }

    /* Ajuste para Mobile (Ocupa a tela toda) */
    @media (max-width: 991px) {
        .admin-main-content {
            margin-left: 0;
            padding: 15px !important;
        }
    }

    /* Estilização extra para os cards de estatísticas no mobile */
    .row-stats {
        display: flex;
        flex-wrap: wrap;
    }
</style>

<div class="admin-wrapper d-flex">
    <?php require_once __DIR__ . '/templates/admin_sidebar.php'; ?>

    <main class="admin-main-content flex-grow-1 p-3 p-md-4">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h1 class="h3 mb-1 fw-bold" style="color: #0C2D54;">
                    <i class="fas fa-shield-virus me-2"></i>Monitor Sentinela
                </h1>
                <p class="text-muted small mb-0">Gestão centralizada de erros, exceções e saúde do sistema.</p>
            </div>
            <div class="d-flex gap-2">
                <button id="btnLimparTodosErros" class="btn btn-outline-danger btn-sm shadow-sm px-3">
                    <i class="fas fa-trash-alt me-1"></i>Limpar Tudo
                </button>
                <a href="<?php echo $config['base_path']; ?>admin/dashboard" class="btn btn-light btn-sm border shadow-sm px-3">
                    <i class="fas fa-arrow-left me-1"></i>Voltar
                </a>
            </div>
        </div>

        <div class="row row-stats mb-4">
            <?php include __DIR__ . '/erros/cards_estatisticas.php'; ?>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-list-ul me-2 text-muted"></i>Ocorrências Recentes
                </h6>
                <span class="badge bg-soft-info text-primary border border-info px-3" style="font-size: 0.7rem;">Tempo Real</span>
            </div>
            <div class="card-body p-0">
                <?php include __DIR__ . '/erros/tabela_erros.php'; ?>
            </div>
        </div>

        <div class="mt-4 text-center">
            <div class="d-inline-block bg-white px-3 py-2 rounded-pill shadow-sm border">
                <p class="text-muted small mb-0">
                    <i class="fas fa-info-circle me-1 text-primary"></i>
                    Dica: Erros idênticos são agrupados para manter seu banco de dados leve.
                </p>
            </div>
        </div>

    </main>
</div>

<?php include __DIR__ . '/erros/modal_detalhes.php'; ?>

<script src="<?php echo $config['base_path']; ?>admin/assets/js/admin_erros.js?v=<?php echo time(); ?>"></script>

<?php 
// Navegação mobile inferior (se aplicável)
require_once __DIR__ . '/templates/admin_mobile_nav.php'; 
?>