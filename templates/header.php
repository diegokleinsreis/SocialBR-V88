<?php
/**
 * templates/header.php
 * VERSÃO 8.4 - Header Notifications Fix & Structural Integrity (socialbr.lol)
 * PAPEL: Cabeçalho global com sino de notificações unificado e busca centralizada.
 * CORREÇÃO: Painel de notificações separado do gatilho para permitir cliques nos links internos.
 */

// 0. MONITOR DE PERFORMANCE
if (!defined('PERF_INICIO')) {
    define('PERF_INICIO', microtime(true));
}

// 1. Garante configurações do banco e constantes
if (!isset($config)) {
    $dbPath = __DIR__ . '/../config/database.php';
    if (file_exists($dbPath)) require_once $dbPath;
}

// 2. [SEGURANÇA] Gestão de Sessão e CSRF
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3. [LÓGICA DE VERIFICAÇÃO DE E-MAIL]
$mostrarAvisoVerificacao = false;
if (isset($_SESSION['user_id']) && isset($conn)) {
    $stmt_v = $conn->prepare("SELECT email_verificado FROM Usuarios WHERE id = ? LIMIT 1");
    $stmt_v->bind_param("i", $_SESSION['user_id']);
    $stmt_v->execute();
    $user_check = $stmt_v->get_result()->fetch_assoc();
    
    if ($user_check && (int)$user_check['email_verificado'] === 0) {
        $mostrarAvisoVerificacao = true;
    }
    $stmt_v->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <?php require_once __DIR__ . '/head_common.php'; ?>
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    <link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_notifications.css?v=<?php echo time(); ?>">
</head>
<body class="bg-light" data-base-path="<?php echo $config['base_path']; ?>">

<?php 
/**
 * INCLUSÃO DA BARRA DE ADMINISTRAÇÃO
 */
include_once __DIR__ . '/admin/barra/barra_principal.php'; 
?>

<header class="site-header" id="main-header">
    <div class="header-content">
        
        <div class="header-left">
            <a href="<?php echo $config['base_path']; ?>feed" class="header-title" title="Ir para o Feed">
                <?php echo htmlspecialchars($config['site_nome'] ?? 'Social BR'); ?>
            </a>
        </div>

        <div class="header-center">
            <div class="search-wrapper desktop-only">
                <input type="text" 
                       class="search-input" 
                       id="input-busca-desktop"
                       placeholder="Pesquisar pessoas, grupos ou posts..." 
                       autocomplete="off">
                <button class="search-button" title="Clique para pesquisar">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <div class="header-right">
            
            <button class="mobile-search-toggle" id="open-mobile-search" title="Abrir Pesquisa">
                <i class="fas fa-search"></i>
            </button>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="notification-container" style="position: relative; display: flex; align-items: center;">
                    
                    <div class="header-action-item" id="notification-trigger" title="Notificações">
                        <i class="fas fa-bell"></i>
                        <span class="notification-count" id="header-notification-badge" style="display: none;">0</span>
                    </div>
                    
                    <div class="notifications-dropdown" id="notifications-panel-unificado">
                        <div class="notifications-header">
                            <h3>Notificações</h3>
                        </div>
                        <div class="notifications-list" id="header-notifications-list">
                            </div>
                        <div class="notifications-footer">
                            <a href="<?php echo $config['base_path']; ?>historico_notificacoes" class="see-all-btn">
                                Ver todas as notificações
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <nav class="desktop-nav">
                <a href="<?php echo $config['base_path']; ?>api/usuarios/logout.php" class="logout-btn">Sair</a>
            </nav>
            
            <div class="mobile-menu-container">
                <button class="mobile-menu-toggle" id="mobile-menu-toggle" title="Menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>

        <div class="mobile-search-overlay" id="search-overlay">
            <div class="overlay-header">
                <button class="close-search-mobile" id="close-mobile-search" title="Voltar">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <input type="text" 
                       class="search-input-mobile" 
                       id="input-busca-mobile"
                       placeholder="Pesquisar no Social BR..."
                       autocomplete="off">
            </div>
            <div id="mobile-search-results"></div>
        </div>

    </div>
</header>

<?php
/**
 * INJEÇÃO DO ALERTA DE VERIFICAÇÃO
 */
if ($mostrarAvisoVerificacao) {
    include_once __DIR__ . '/avisos/alerta_verificacao.php';
}
?>