<?php
/**
 * views/suporte.php
 * ORQUESTRADOR MASTER DO MÓDULO SUPORTE (V3.2)
 * PAPEL: Gerir o layout fixo "App-Like" e roteamento de sub-views.
 * AJUSTE: Limpeza de tags HTML duplicadas e ajuste de altura (calc - 70px).
 * VERSÃO: 3.2 - socialbr.lol
 */

// 1. SEGURANÇA E AUTH
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $config['base_path'] . "login");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. LOGICA DE ROTEAMENTO INTERNO (SUB-VIEWS)
$url_parts = explode('/', trim($_GET['url'] ?? '', '/'));
$sub_page = $url_parts[1] ?? 'home'; 
$chamado_id = (int)($_GET['id'] ?? ($url_parts[2] ?? 0));

// 3. DEFINE O TÍTULO DINÂMICO
$page_title = 'Suporte - ' . htmlspecialchars($config['site_nome']);

// 4. PREPARAÇÃO DE DADOS
require_once __DIR__ . '/../src/SuporteLogic.php';

// --- INÍCIO DA RENDERIZAÇÃO ---

// O head_common e o header já abrem as tags <html> e <body>
include '../templates/head_common.php'; 
?>

<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_suporte.css">
<style>
    /* RESET DE ROLAGEM: O corpo do site não deve rolar no suporte */
    body { overflow-x: hidden; }

    /* MOLDURA APP-LIKE: Altura fixa calculada para preenchimento total */
    .suporte-container {
        flex: 1;
        max-width: 1000px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        margin-bottom: 20px;
        overflow: hidden; 
        display: flex;
        flex-direction: column;
        
        /* Ajuste fino conforme seu teste: calc(100vh - 70px) */
        height: calc(100vh - 70px); 
        min-height: 500px;
    }

    /* HEADER MASTER */
    .suporte-header-master {
        background-color: #0C2D54;
        color: #ffffff;
        padding: 15px 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }

    .suporte-header-master h1 {
        color: #ffffff;
        font-size: 1.25rem;
        margin: 0;
        font-weight: 800;
    }

    /* CORPO DE CONTEÚDO */
    .suporte-content-body {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden; 
        background: #fff;
    }

    /* Ajustes para mobile */
    @media (max-width: 768px) {
        .suporte-container {
            height: calc(100vh - 70px);
            margin: 0;
            border-radius: 0;
        }
    }
</style>

<?php 
// O header.php contém a abertura da tag <body> e o menu superior
include '../templates/header.php'; 
include '../templates/mobile_nav.php'; 
?>

<div class="main-content-area" style="display: flex; align-items: flex-start; gap: 20px;">
    <?php include '../templates/sidebar.php'; ?>

    <main class="suporte-container">
        
        <div class="suporte-header-master">
            <div>
                <?php if ($sub_page === 'ver'): ?>
                    <h1>Conversa #<?php echo $chamado_id; ?></h1>
                <?php else: ?>
                    <h1>Centro de Suporte</h1>
                <?php endif; ?>
            </div>
            
            <div class="header-actions">
                <?php if ($sub_page === 'home' || $sub_page === ''): ?>
                    <a href="<?php echo $config['base_path']; ?>suporte/abrir" class="primary-btn-small" style="background: #ffffff; color: #0C2D54; border-radius: 6px; font-weight: bold;">
                        <i class="fas fa-plus"></i> Novo Chamado
                    </a>
                <?php else: ?>
                    <a href="<?php echo $config['base_path']; ?>suporte" class="primary-btn-small" style="background: rgba(255,255,255,0.2); color: #fff; border-radius: 6px;">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="suporte-content-body">
            <?php
                switch ($sub_page) {
                    case 'abrir':
                        include 'suporte/form_novo.php';
                        break;
                    
                    case 'ver':
                        if ($chamado_id > 0) {
                            include 'suporte/conversa_detalhe.php';
                        } else {
                            echo "<div style='padding: 20px;' class='error-message'>Chamado não encontrado.</div>";
                        }
                        break;

                    case 'home':
                    default:
                        include 'suporte/lista_historico.php';
                        break;
                }
            ?>
        </div>

    </main>
</div>

<?php 
// O footer.php fecha as tags <body> e <html> e carrega os scripts
include '../templates/footer.php'; 
?>