<?php
/**
 * api/admin/marketplace_admin_acoes.php
 * Versão: 14.0 - Integração com Logs de Auditoria (socialbr.lol)
 */

// 1. INICIAR SESSÃO OBRIGATORIAMENTE (Primeira linha)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 0);
header('Content-Type: application/json');

// 2. DEBUG DE SESSÃO (Se der erro, saberemos o porquê)
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false, 
        'error' => 'Acesso Negado: Sessão não encontrada.'
    ]); 
    exit;
}

// 3. CARREGAMENTO DAS DEPENDÊNCIAS (Banco e Lógica de Logs)
$dbPaths = [
    __DIR__ . '/../../../config/database.php', 
    __DIR__ . '/../../config/database.php'
];

$dbLoaded = false;
foreach ($dbPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $dbLoaded = true;
        break;
    }
}

if (!$dbLoaded) {
    echo json_encode(['success' => false, 'error' => 'Configuração de banco não encontrada.']);
    exit;
}

// Carrega o motor de logs
require_once __DIR__ . '/../../../src/LogsLogic.php';

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';
$admin_id = $_SESSION['user_id'];

if (!$id || !$action) {
    echo json_encode(['success' => false, 'error' => 'Dados inválidos enviados para API.']);
    exit;
}

try {
    // Garante conexão PDO para as queries existentes
    if (!isset($pdo)) {
        if (isset($servername, $dbname, $username, $password)) {
            $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } else {
            throw new Exception("Dados de conexão MySQL ausentes.");
        }
    }

    // --- BUSCA TÍTULO E ID DA POSTAGEM PARA O LOG ---
    $stmt = $pdo->prepare("SELECT id_postagem, titulo FROM Marketplace_Anuncios WHERE id = ?");
    $stmt->execute([$id]);
    $anuncio = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$anuncio || !$anuncio['id_postagem']) {
        throw new Exception("Anúncio não encontrado (ID: $id).");
    }

    $id_postagem = $anuncio['id_postagem'];
    $titulo = $anuncio['titulo'];
    $log_detalhes = "";

    // 4. PROCESSAMENTO DAS AÇÕES
    if ($action === 'banir') {
        $pdo->prepare("UPDATE Postagens SET status = 'inativo' WHERE id = ?")->execute([$id_postagem]);
        $log_detalhes = "Anúncio #$id ($titulo) foi BANIDO (postagem marcada como inativa).";
        $log_acao = 'banir_anuncio';
    } 
    elseif ($action === 'reativar') {
        $pdo->prepare("UPDATE Postagens SET status = 'ativo' WHERE id = ?")->execute([$id_postagem]);
        $log_detalhes = "Anúncio #$id ($titulo) foi REATIVADO (postagem marcada como ativa).";
        $log_acao = 'reativar_anuncio';
    } 
    elseif ($action === 'marcar_vendido') {
        $pdo->prepare("UPDATE Marketplace_Anuncios SET status_venda = 'vendido' WHERE id = ?")->execute([$id]);
        $log_detalhes = "Anúncio #$id ($titulo) marcado como VENDIDO pelo administrador.";
        $log_acao = 'venda_anuncio';
    } 
    elseif ($action === 'marcar_disponivel') {
        $pdo->prepare("UPDATE Marketplace_Anuncios SET status_venda = 'disponivel' WHERE id = ?")->execute([$id]);
        $log_detalhes = "Anúncio #$id ($titulo) marcado como DISPONÍVEL pelo administrador.";
        $log_acao = 'disponivel_anuncio';
    } 
    else {
        throw new Exception("Ação desconhecida: " . htmlspecialchars($action));
    }

    // --- REGISTO DE AUDITORIA ---
    // Usamos a variável $conn (mysqli) que o database.php fornece para o LogsLogic
    LogsLogic::registrar($conn, $admin_id, $log_acao, 'marketplace', $id, $log_detalhes);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>