<?php
/**
 * views/marketplace/criar.php
 * Tela de Criação (V3.6 - Blindada com CSRF Token)
 */

if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { 
    $base = isset($config['base_path']) ? $config['base_path'] : '/';
    header("Location: " . $base . "login"); 
    exit; 
}

$id_usuario_logado = $_SESSION['user_id'];

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/MarketplaceLogic.php';
$configMkt = require __DIR__ . '/../../config/marketplace.php';

// Conexão Garantida
if (!isset($pdo)) {
    try {
        $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) { die("Erro de conexão."); }
}

// --- BUSCA DADOS DO VENDEDOR ---
$nome_usuario = 'Usuário';
$avatar_usuario = $config['base_path'] . 'assets/images/default-avatar.png';
$precisa_cpf = true;

try {
    $stmtU = $pdo->prepare("SELECT nome, foto_perfil_url, cpf FROM Usuarios WHERE id = ?");
    $stmtU->execute([$id_usuario_logado]);
    $userData = $stmtU->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        $nome_usuario = $userData['nome'];
        $precisa_cpf = empty($userData['cpf']);
        if (!empty($userData['foto_perfil_url'])) {
            $avatar_usuario = (strpos($userData['foto_perfil_url'], 'http') === 0) 
                ? $userData['foto_perfil_url'] 
                : $config['base_path'] . $userData['foto_perfil_url'];
        }
    }
} catch (Exception $e) {}

$page_title = "Vender no Marketplace";
require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/mobile_nav.php'; 
?>

<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_marketplace_create.css?v=<?php echo time(); ?>">

<div class="main-container mkt-create-wrapper">
    <div class="content-wrapper">
        
        <div class="create-header-area">
            <h1 class="create-page-title">O que você está desapegando?</h1>
            <a href="<?php echo $config['base_path']; ?>marketplace" class="btn-cancel-create">
                <i class="fas fa-times"></i>
            </a>
        </div>

        <form id="form-criar-anuncio" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

            <div class="create-layout-grid">
                
                <div class="create-col-form">
                    <?php include __DIR__ . '/componentes/form_criar.php'; ?>
                </div>

                <div class="create-col-preview">
                    <?php include __DIR__ . '/componentes/painel_preview.php'; ?>
                </div>

                <div class="create-footer-actions">
                    <button type="submit" form="form-criar-anuncio" class="btn-submit-mkt-final">
                        <i class="fas fa-check-circle"></i> Publicar Anúncio Agora
                    </button>
                    <p class="terms-hint">Ao publicar, você concorda com as diretrizes da nossa comunidade.</p>
                </div>

            </div>
        </form>

    </div>
</div>

<script src="<?php echo $config['base_path']; ?>assets/js/mkt_criar.js?v=<?php echo time(); ?>"></script>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>