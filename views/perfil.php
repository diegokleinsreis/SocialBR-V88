<?php
/**
 * views/perfil.php
 * PAPEL: Esqueleto Orquestrador (Orquestrador Master).
 * RESPONSABILIDADE: Processar lógica de negócio, segurança e gerenciar inclusão de componentes.
 * VERSÃO: V60.6 - FIX: Purificação Estrutural (socialbr.lol)
 */

// Garantia extra: Se a conexão não existir, tenta carregar
if (!isset($conn)) {
    $dbPath = __DIR__ . '/../config/database.php';
    if (file_exists($dbPath)) require_once $dbPath;
}

// 1. LÓGICA DE LOGIN E DETERMINAÇÃO DE PERFIL
$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$id_usuario_logado = $is_logged_in ? (int)$_SESSION['user_id'] : 0;

// Captura o Identificador (pode vir como ID ou Username)
$identificador = $_GET['id'] ?? '';

if (empty($identificador)) {
    // Captura da URL amigável (ex: /perfil/13 ou /perfil/diegoteste)
    $url_partes = explode('/', rtrim($_SERVER['REQUEST_URI'], '/'));
    $identificador = end($url_partes);
}

$id_do_perfil_a_exibir = 0;

// VERIFICAÇÃO INTELIGENTE: É número ou nome de usuário?
if (is_numeric($identificador)) {
    $id_do_perfil_a_exibir = (int)$identificador;
} elseif (!empty($identificador) && $identificador !== 'perfil') {
    $stmt_u = $conn->prepare("SELECT id FROM Usuarios WHERE nome_de_usuario = ? LIMIT 1");
    $stmt_u->bind_param("s", $identificador);
    $stmt_u->execute();
    $res_u = $stmt_u->get_result()->fetch_assoc();
    if ($res_u) {
        $id_do_perfil_a_exibir = (int)$res_u['id'];
    }
    $stmt_u->close();
}

if (empty($id_do_perfil_a_exibir) && $is_logged_in) {
    $id_do_perfil_a_exibir = $id_usuario_logado;
}

$id_do_perfil_a_exibir = (int)$id_do_perfil_a_exibir;

if ($id_do_perfil_a_exibir <= 0) {
    header("Location: " . $config['base_path'] . ($is_logged_in ? "pagina-nao-encontrada" : "login"));
    exit();
}

/**
 * --- LÓGICA DE MODERAÇÃO ---
 */
$perfil_denuncias_count = 0;
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin' && isset($conn)) {
    $sqlDen = "SELECT COUNT(*) as total FROM Denuncias WHERE tipo_conteudo = 'usuario' AND id_conteudo = ?";
    $stmtDen = $conn->prepare($sqlDen);
    if ($stmtDen) {
        $stmtDen->bind_param("i", $id_do_perfil_a_exibir);
        $stmtDen->execute();
        $resDen = $stmtDen->get_result()->fetch_assoc();
        $perfil_denuncias_count = $resDen['total'] ?? 0;
        $stmtDen->close();
    }
}

$active_page = $_GET['tab'] ?? 'posts';

if (!$is_logged_in) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
}

// 2. INCLUI OS "CÉREBROS" (LÓGICA)
require_once __DIR__ . '/../src/UserLogic.php';
require_once __DIR__ . '/../src/PostLogic.php';

// --- VERIFICAÇÃO DE BLOQUEIO ---
$eu_bloqueie = false;
$fui_bloqueado = false;

if ($is_logged_in && $id_do_perfil_a_exibir != $id_usuario_logado) {
    $stmtBlock = $conn->prepare("SELECT id FROM Bloqueios WHERE bloqueador_id = ? AND bloqueado_id = ?");
    $stmtBlock->bind_param("ii", $id_usuario_logado, $id_do_perfil_a_exibir);
    $stmtBlock->execute();
    if ($stmtBlock->get_result()->num_rows > 0) { $eu_bloqueie = true; }
    $stmtBlock->close();

    if (!$eu_bloqueie) {
        $stmtBlockMe = $conn->prepare("SELECT id FROM Bloqueios WHERE bloqueador_id = ? AND bloqueado_id = ?");
        $stmtBlockMe->bind_param("ii", $id_do_perfil_a_exibir, $id_usuario_logado);
        $stmtBlockMe->execute();
        if ($stmtBlockMe->get_result()->num_rows > 0) { $fui_bloqueado = true; }
        $stmtBlockMe->close();
    }
}

if ($fui_bloqueado) {
    header("Location: " . $config['base_path'] . "pagina-nao-encontrada");
    exit();
}

// 3. BUSCA DADOS DO CABEÇALHO DO PERFIL
$headerData = UserLogic::getProfileHeaderData($conn, $id_do_perfil_a_exibir, $id_usuario_logado);
if ($headerData === null) {
    header("Location: " . $config['base_path'] . "pagina-nao-encontrada");
    exit();
}

$perfil_data = $headerData['perfil_data'];
$amizade_details = $headerData['amizade_details'];
$pode_ver_conteudo = $headerData['pode_ver_conteudo'];

if ($eu_bloqueie) {
    $pode_ver_conteudo = false;
    $status_amizade = 'bloqueado';
} else {
    $status_amizade = $amizade_details['status_amizade'];
}

$amizade_id = $amizade_details['amizade_id'];
$id_remetente_pedido = $amizade_details['id_remetente_pedido'];
$sao_amigos = $amizade_details['sao_amigos'];
$is_own_profile = ($id_usuario_logado === $id_do_perfil_a_exibir);

$page_title = htmlspecialchars($perfil_data['nome'] . ' ' . $perfil_data['sobrenome']);

// 4. PREPARAÇÃO DE DADOS PARA AS ABAS
$posts_para_exibir = [];
$lista_amigos = [];
$galeria_midia = [];
$pode_ver_lista_amigos = false;

if ($is_logged_in && $pode_ver_conteudo && !$eu_bloqueie) {
    switch ($active_page) {
        case 'amigos':
            $friendsData = UserLogic::getFriendsPageData($conn, $id_do_perfil_a_exibir, $id_usuario_logado, $perfil_data, $sao_amigos);
            $pode_ver_lista_amigos = $friendsData['pode_ver_lista_amigos'];
            $lista_amigos = $friendsData['lista_amigos'];
            break;
        case 'galeria':
            $galeria_midia = PostLogic::getGalleryMedia($conn, $id_do_perfil_a_exibir);
            break;
        case 'salvos':
            if ($is_own_profile) { $posts_para_exibir = PostLogic::getSavedPosts($conn, $id_usuario_logado); }
            break;
        default:
            $posts_para_exibir = PostLogic::getPostsForProfile($conn, $id_usuario_logado, $id_do_perfil_a_exibir);
            break;
    }
}

// 5. RENDERIZAÇÃO DA PÁGINA (ORQUESTRAÇÃO PURA)
if ($is_logged_in):
    /**
     * O header.php já inicia <!DOCTYPE html>, <html>, <head> e <body>.
     * NÃO duplique tags aqui.
     */
    include __DIR__ . '/../templates/header.php'; 
    include __DIR__ . '/../templates/mobile_nav.php'; 
?>

    <div class="main-content-area">
        <?php include __DIR__ . '/../templates/sidebar.php'; ?>

        <main class="profile-main-content">
            <div class="profile-page-wrapper">
                
                <?php if ($eu_bloqueie): ?>
                    <?php include __DIR__ . '/perfil/perfil_bloqueado.php'; ?>
                <?php else: ?>

                    <div class="profile-header-container">
                        <?php include __DIR__ . '/perfil/capa_perfil.php'; ?>
                        
                        <div class="profile-page-header">
                            <?php include __DIR__ . '/perfil/identidade_perfil.php'; ?>
                            <?php include __DIR__ . '/perfil/informacoes_topo.php'; ?>
                            <?php include __DIR__ . '/perfil/acoes_relacionamento.php'; ?>
                        </div>
                    </div>

                    <?php include __DIR__ . '/perfil/menu_abas.php'; ?>

                    <div class="profile-dynamic-content">
                        <?php 
                        if ($perfil_data['status'] === 'suspenso'):
                            echo '<div class="post-card"><h2 style="text-align: center; color: #8a1717; padding: 20px;">Esta conta está suspensa.</h2></div>';
                        elseif (!$pode_ver_conteudo):
                            include __DIR__ . '/perfil/perfil_privado.php';
                        else:
                            switch ($active_page) {
                                case 'sobre':   include __DIR__ . '/perfil/abas/aba_sobre.php'; break;
                                case 'amigos':  include __DIR__ . '/perfil/abas/aba_amigos.php'; break;
                                case 'galeria': include __DIR__ . '/perfil/abas/aba_fotos.php'; break;
                                case 'salvos':  include __DIR__ . '/perfil/abas/aba_salvos.php'; break;
                                default:        include __DIR__ . '/perfil/abas/aba_postagens.php'; break;
                            }
                        endif; 
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php include __DIR__ . '/../templates/footer.php'; ?>

<?php else: ?>
    <?php 
    // Para visitantes não logados, incluímos um head básico antes do convite
    include __DIR__ . '/../templates/head_common.php'; 
    include __DIR__ . '/perfil/convite_login.php'; 
    ?>
<?php endif; ?>