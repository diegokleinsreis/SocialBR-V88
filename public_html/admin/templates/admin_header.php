<?php
/**
 * admin/templates/admin_header.php
 * PAPEL: Cabeçalho universal do painel administrativo.
 * VERSÃO: 3.1 (Fix: NProgress Auto-Finish & No-Spinner - socialbr.lol)
 * LOCALIZAÇÃO: public_html/admin/templates/
 */

// --- CORREÇÃO DE ARQUITETURA ---
if (!isset($config)) {
    $db_path_header = __DIR__ . '/../../../config/database.php';
    if (file_exists($db_path_header)) {
        require_once $db_path_header;
    }
}
// ---------------------------------
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" crossorigin="anonymous">
    
    <link rel="stylesheet" href="<?php echo rtrim($config['base_url'], '/') . '/admin/assets/css/admin.css?v=' . time(); ?>">
    <link rel="stylesheet" href="<?php echo rtrim($config['base_url'], '/') . '/admin/assets/css/components/_admin_menu.css?v=' . time(); ?>">
    <link rel="stylesheet" href="<?php echo rtrim($config['base_url'], '/') . '/admin/assets/css/components/_admin_modal.css?v=' . time(); ?>">
    <link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_lightbox.css?v=<?php echo time(); ?>">
    
    <style>
        /* 1. RESET DE VÁCUO: Impede que o admin.css empurre o body */
        body { margin: 0 !important; padding: 0 !important; }

        /* 2. FIXAÇÃO DO HEADER */
        .admin-header {
            position: fixed !important;
            top: 0;
            left: 0;
            width: 100%;
            height: 70px !important;
            z-index: 1050 !important;
            background: #0C2D54;
            display: flex;
            align-items: center;
        }

        .header-content {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            width: 100%;
            padding: 0 20px;
        }

        /* 3. ALINHAMENTO DOS BOTÕES */
        .desktop-nav {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 15px !important;
        }
        .desktop-nav a { white-space: nowrap; }

        /* 4. FIX DA SIDEBAR */
        .site-sidebar, .admin-sidebar {
            position: fixed !important;
            top: 70px !important;
            left: 0 !important;
            width: 260px !important;
            height: calc(100vh - 70px) !important;
            z-index: 1040 !important;
            overflow-y: auto !important;
        }

        /* 5. FIX DO WRAPPER */
        .admin-main-wrapper {
            margin-left: 260px !important; 
            margin-top: 70px !important;
            padding: 20px !important;
            min-height: calc(100vh - 70px);
            background: #f8f9fa;
        }

        /* Ajustes de Camada NProgress */
        #nprogress .bar { background: #ffffff !important; height: 7px !important; z-index: 999999 !important; }
        
        /* Forçamos a remoção do spinner via CSS caso o JS demore a carregar */
        #nprogress .spinner { display: none !important; }

        /* Responsividade */
        @media (max-width: 768px) {
            .admin-main-wrapper {
                margin-left: 0 !important;
                margin-top: 70px !important;
                padding: 15px !important;
            }
            .desktop-nav { display: none !important; } 
        }
    </style>

    <script>
        /**
         * Variável Global base_path (Sentinela & Admin)
         */
        window.base_path = '<?php echo $config['base_path']; ?>';
        var BASE_PATH = window.base_path;

        if (typeof CSRF_TOKEN === 'undefined') {
            var CSRF_TOKEN = '<?php echo (function_exists('get_csrf_token') ? get_csrf_token() : ""); ?>';
        }
    </script>

    <script src="<?php echo $config['base_path']; ?>assets/js/sentinela_global.js?v=<?php echo time(); ?>"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/nprogress@0.2.0/nprogress.js" crossorigin="anonymous"></script>
    
    <script>
        /**
         * MOTOR DE CARREGAMENTO (NProgress Orchestrator)
         * Resolve: Race Condition, Spinner indesejado e Barra Infinita.
         */
        if (typeof NProgress !== 'undefined') {
            // 1. Desativa a bolinha (spinner)
            NProgress.configure({ showSpinner: false });

            // 2. Inicia a barra respeitando a renderização do Body
            if (document.body) {
                NProgress.start();
            } else {
                window.addEventListener('DOMContentLoaded', function() {
                    NProgress.start();
                });
            }

            // 3. FINALIZAÇÃO ÓBVIA: Quando a página termina de carregar, a barra some.
            window.addEventListener('load', function() {
                NProgress.done();
            });
        }
    </script>
    
    <script src="<?php echo rtrim($config['base_url'], '/') . '/admin/assets/js/admin.js?v=' . time(); ?>" defer></script>
    <script src="<?php echo $config['base_path']; ?>assets/js/chat_lightbox.js?v=<?php echo time(); ?>" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer crossorigin="anonymous"></script>
</head>
<body>

<header class="admin-header">
    <div class="header-content">
        <a href="<?php echo $config['base_path']; ?>admin/dashboard" class="logo text-decoration-none text-white d-flex align-items-center"> 
            <i class="fas fa-shield-alt me-2"></i>
            <span class="logo-text fw-bold">Painel Admin</span>
        </a>

        <nav class="desktop-nav">
            <a href="<?php echo $config['base_path']; ?>feed" class="btn btn-sm btn-outline-light" target="_blank">
                <i class="fas fa-external-link-alt me-1"></i> Ver o Site
            </a>
            
            <a href="<?php echo $config['base_path']; ?>api/usuarios/logout.php" class="btn btn-sm btn-danger text-white">
                <i class="fas fa-sign-out-alt me-1"></i> Sair
            </a>
        </nav>

        <button class="mobile-menu-toggle btn text-white d-md-none" id="mobile-menu-toggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</header>