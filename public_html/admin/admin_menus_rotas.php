<?php
/**
 * admin/admin_menus_rotas.php
 * Orquestrador da Central de Menus e Rotas.
 * VERSÃO: 1.3 (Nuclear Top Spacing Reset - socialbr.lol)
 */

if (!defined('ACESSO_ROTEADOR')) {
    die("Acesso direto negado.");
}

// 1. TÍTULO E DEPENDÊNCIAS
$titulo_pagina = "Gestão de Menus e Rotas";
require_once __DIR__ . '/templates/admin_header.php';
require_once __DIR__ . '/templates/admin_sidebar.php';
?>

<style>
    /* FORÇA BRUTA: Remove qualquer espaço herdado que esteja empurrando o conteúdo.
       O margin-top de 70px compensa apenas a altura do header fixo.
    */
    .admin-main-wrapper {
        margin-top: 70px !important; 
        padding-top: 0 !important;
        margin-bottom: 0 !important;
    }

    /* Remove margens internas do container principal */
    .admin-main-wrapper main.container-fluid {
        margin-top: 0 !important;
        padding-top: 15px !important; /* Apenas um pequeno respiro para não colar no header */
    }

    /* Ajuste para telas menores */
    @media (max-width: 768px) {
        .admin-main-wrapper {
            margin-top: 60px !important;
            padding-top: 5px !important;
        }
    }
</style>

<div class="admin-main-wrapper">
    <main class="container-fluid">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h1 class="h3 mb-0" style="color: #0C2D54; font-weight: 700;">
                    <i class="fas fa-route me-2"></i>Central de Menus e Rotas
                </h1>
                <p class="text-muted mb-0">Gerencie a arquitetura de navegação da socialbr.lol</p>
            </div>
            
            <div class="d-flex gap-2">
                <?php include __DIR__ . '/menus/painel_emergencia.php'; ?>
                
                <button class="btn btn-primary shadow-sm fw-bold" style="background-color: #0C2D54; border: none;" data-bs-toggle="modal" data-bs-target="#modalEditorRota">
                    <i class="fas fa-plus-circle me-1"></i> Nova Rota
                </button>
            </div>
        </div>

        <div class="row mb-4">
            <?php include __DIR__ . '/menus/cards_estatisticas.php'; ?>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0" style="font-weight: 600; font-size: 1rem; color: #0C2D54;">
                    <i class="fas fa-list me-2"></i>Listagem de Rotas e Links
                </h5>
                <div class="input-group input-group-sm w-auto d-none d-md-flex">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="filtroBuscaRotas" class="form-control bg-light border-start-0" placeholder="Filtrar slug ou nome...">
                </div>
            </div>
            <div class="card-body p-0">
                <?php include __DIR__ . '/menus/tabela_listagem.php'; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <?php include __DIR__ . '/menus/rastreador_cliques.php'; ?>
            </div>
        </div>

    </main>
</div>

<?php include __DIR__ . '/menus/modal_editor.php'; ?>

<script src="<?php echo $config['base_path']; ?>admin/assets/js/admin_menus.js"></script>

<?php 
// Finaliza a estrutura HTML com navegação mobile se houver
require_once __DIR__ . '/templates/admin_mobile_nav.php'; 
?>