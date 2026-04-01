<?php
/**
 * views/marketplace/editar.php
 * Tela de Edição (V2.1 - Blindada com CSRF Token)
 * CORREÇÃO: Sincronização com o Roteador V87 (Uso de $_GET['id'])
 */

if (session_status() == PHP_SESSION_NONE) { session_start(); }

// 0. Configuração de Base Path (Proteção extra)
$base = isset($config['base_path']) ? $config['base_path'] : '/';

// 1. SEGURANÇA: Login Obrigatório
if (!isset($_SESSION['user_id'])) { 
    header("Location: " . $base . "login"); 
    exit; 
}

$id_usuario_logado = $_SESSION['user_id'];

/**
 * MUDANÇA CRÍTICA: O roteador V87 deposita o ID em $_GET['id'].
 * Antigamente usava-se $id_url, que agora resultava em 0 e causava o redirecionamento.
 */
$anuncio_id = isset($_GET['id']) ? (int)$_GET['id'] : 0; 

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/MarketplaceLogic.php';
$configMkt = require __DIR__ . '/../../config/marketplace.php';

// Conexão Garantida (Singleton-like)
if (!isset($pdo)) {
    try {
        $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) { 
        error_log("Erro de Conexão MKT: " . $e->getMessage());
        die("Erro de conexão com o servidor de dados."); 
    }
}

$marketplaceLogic = new MarketplaceLogic($pdo);

// 2. BUSCA DADOS COMPLETOS PARA EDIÇÃO (Garante que o anúncio pertence ao logado)
$dados = $marketplaceLogic->obterAnuncioParaEdicao($anuncio_id, $id_usuario_logado);

// Validação de Existência e Posse: Se falhar, ejetar para meus-anuncios
if (!$dados) {
    header("Location: " . $base . "marketplace/meus-anuncios");
    exit;
}

// 3. PREPARAÇÃO DE VARIÁVEIS PARA O PREVIEW
$nome_usuario = $dados['vendedor_nome'];
$avatar_usuario = (!empty($dados['vendedor_avatar'])) 
    ? $base . $dados['vendedor_avatar'] 
    : $base . 'assets/images/default-avatar.png';

$page_title = "Editar: " . htmlspecialchars($dados['titulo_produto']);

// 4. ORQUESTRAÇÃO DE TEMPLATES
require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/mobile_nav.php'; 
?>

<link rel="stylesheet" href="<?php echo $base; ?>assets/css/components/_marketplace_create.css?v=<?php echo time(); ?>">

<div class="main-container mkt-create-wrapper">
    <div class="content-wrapper">
        
        <div class="create-header-area">
            <h1 class="create-page-title">Editar Anúncio</h1>
            <a href="<?php echo $base; ?>marketplace/meus-anuncios" class="btn-cancel-create">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>

        <form id="form-editar-anuncio" enctype="multipart/form-data">
            <input type="hidden" name="anuncio_id" value="<?php echo $anuncio_id; ?>">
            
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            
            <div class="create-layout-grid">
                
                <div class="create-col-form">
                    <?php include __DIR__ . '/componentes/form_editar.php'; ?>
                </div>

                <div class="create-col-preview">
                    <?php include __DIR__ . '/componentes/painel_preview.php'; ?>
                </div>

                <div class="create-footer-actions">
                    <button type="submit" form="form-editar-anuncio" class="btn-submit-mkt-final">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                    <p class="terms-hint">As suas alterações serão publicadas imediatamente após o salvamento.</p>
                </div>

            </div>
        </form>

    </div>
</div>

<script src="<?php echo $base; ?>assets/js/mkt_editar.js?v=<?php echo time(); ?>"></script>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>