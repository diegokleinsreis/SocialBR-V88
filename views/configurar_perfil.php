<?php
/**
 * ARQUIVO: views/configurar_perfil.php
 * PAPEL: Skeleton Orquestrador de Configurações.
 * VERSÃO: 3.0 - Suporte AJAX Premium & Deep Linking (socialbr.lol)
 */

// 1. VERIFICA SE O UTILIZADOR ESTÁ LOGADO
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: " . $config['base_path'] . "login");
    exit();
}
$user_id = $_SESSION['user_id'];

// 2. INCLUI O "CÉREBRO" E CONFIGURAÇÕES
require_once __DIR__ . '/../src/UserLogic.php';

// 3. LÓGICA DE ROTEAMENTO DE ABAS (DEEP LINKING)
$allowed_tabs = ['perfil', 'conta', 'privacidade'];
$current_tab = isset($_GET['tab']) && in_array($_GET['tab'], $allowed_tabs) ? $_GET['tab'] : 'perfil';

// 4. BUSCA OS DADOS (Necessários para preencher os formulários nas abas)
$user_data = UserLogic::getUserDataForSettings($conn, $user_id);
$result_bairros = UserLogic::getBairrosList($conn); 

// 5. DEFINE O TÍTULO DA PÁGINA DINAMICAMENTE
$titles = [
    'perfil'      => 'Editar Perfil',
    'conta'       => 'Configurações de Conta',
    'privacidade' => 'Privacidade e Segurança'
];
$page_title = $titles[$current_tab] . ' - ' . htmlspecialchars($config['site_nome']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <?php include '../templates/head_common.php'; ?>
    <link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_settings.css">
    <link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_settings_media_hub.css">
</head>
<body data-base-path="<?php echo $config['base_path']; ?>">
    <?php include '../templates/header.php'; ?>
    <?php include '../templates/mobile_nav.php'; ?>

    <div class="main-content-area">
        <?php include '../templates/sidebar.php'; ?>

        <main class="profile-main-content">
            <div class="page-section-header">
                <h1>Configurações</h1>
                <p>Gerencie suas informações de perfil, conta e privacidade.</p>
            </div>

            <div class="settings-tabs-nav" id="settings-tabs-nav">
                <a href="?tab=perfil" class="tab-link <?php echo ($current_tab == 'perfil') ? 'active' : ''; ?>" data-tab="perfil">
                    <i class="fas fa-user"></i> Perfil
                </a>
                <a href="?tab=conta" class="tab-link <?php echo ($current_tab == 'conta') ? 'active' : ''; ?>" data-tab="conta">
                    <i class="fas fa-cog"></i> Conta
                </a>
                <a href="?tab=privacidade" class="tab-link <?php echo ($current_tab == 'privacidade') ? 'active' : ''; ?>" data-tab="privacidade">
                    <i class="fas fa-user-secret"></i> Privacidade
                </a>
            </div>

            <div id="settings-render-area">
                <div class="settings-tab-content">
                    <?php 
                    // CARREGAMENTO DINÂMICO DA SUB-VIEW (ABA)
                    $tab_file = "../templates/settings/tab_{$current_tab}.php";
                    
                    if (file_exists(__DIR__ . '/' . $tab_file)) {
                        include $tab_file;
                    } else {
                        echo "<div class='settings-card'><p>Erro: Aba não encontrada.</p></div>";
                    }
                    ?>
                </div>

                <?php if ($current_tab == 'conta'): ?>
                <div class="settings-card danger-zone">
                    <h2><i class="fas fa-exclamation-triangle"></i> Zona de Perigo</h2>
                    <div class="danger-actions">
                        <div>
                            <strong>Desativar sua conta</strong>
                            <p>Sua conta será desativada temporariamente.</p>
                        </div>
                        <button class="danger-btn" id="btn-desativar-conta">Desativar Conta</button>
                    </div>
                </div>
                <?php endif; ?>
            </div></main>
    </div>

    <?php include '../templates/footer.php'; ?>
    <script src="<?php echo $config['base_path']; ?>assets/js/settings_media_hub.js"></script>
    <script src="<?php echo $config['base_path']; ?>assets/js/configuracoes.js"></script>
</body>
</html>