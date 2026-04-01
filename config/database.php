<?php
/**
 * FICHEIRO: config/database.php
 * PAPEL: Coração da Conexão e Ativação de Telemetria com Persistência
 * VERSÃO: 22.0 (Dual-Stack Edition - MySQLi + PDO - socialbr.lol)
 */

$servername = "localhost";
$username = "klscom_adm";
$password = "Di@56741634";
$dbname = "klscom_social";

// 0. GERAÇÃO DE IDENTIDADE ÚNICA DA REQUISIÇÃO
// Este ID agrupará todas as queries desta página no log de auditoria
$request_id = bin2hex(random_bytes(4)); 

// 1. CARREGAMENTO DO RASTREADOR (Caminho Físico Blindado)
// Ajustado para encontrar a pasta templates independente de onde o banco é chamado
$tracker_path = realpath(__DIR__ . '/../public_html/templates/admin/barra/componentes/super_debug/sd_sql_tracker.php');

if ($tracker_path && file_exists($tracker_path)) {
    require_once $tracker_path;
}

/**
 * Classe LoggedMySQLi
 * Estende o mysqli original com telemetria de performance e proteção PHP 8.1+
 */
class LoggedMySQLi extends mysqli {
    private static bool $in_log = false;

    /**
     * Sobrescrita da função query compatível com PHP 8.1+
     */
    #[\ReturnTypeWillChange]
    public function query(string $query, int $result_mode = MYSQLI_STORE_RESULT): mysqli_result|bool {
        global $request_id;

        // Evita recursividade infinita ao logar o próprio EXPLAIN do tracker
        if (self::$in_log) {
            return parent::query($query, $result_mode);
        }

        self::$in_log = true;
        $start = microtime(true);
        
        $result = parent::query($query, $result_mode);
        
        $duration = microtime(true) - $start;

        // Só tenta logar se a classe foi incluída corretamente acima
        if (class_exists('SQLPerfTracker')) {
            // Transmite o Request_ID para o Singleton
            SQLPerfTracker::init($this, $request_id)->logQuery($query, $duration);
        }

        self::$in_log = false;
        return $result;
    }
}

// 2. INICIALIZAÇÃO DA CONEXÃO MYSQLI (Para Legado e Logics Atuais)
$conn = new LoggedMySQLi($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Falha na conexão MySQLi: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// 2.1. INICIALIZAÇÃO DA CONEXÃO PDO (Para Novos Módulos e SalvosLogic)
try {
    $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    // Em caso de erro na conexão PDO, não interrompemos o site todo (Blindagem)
    // Mas registramos o erro para manutenção rápida.
    error_log("Erro Crítico PDO: " . $e->getMessage());
    // Definimos como null para evitar erros de 'undefined variable'
    $pdo = null;
}

// 3. CARREGA AS CONFIGURAÇÕES DO SITE (Usando MySQLi para manter compatibilidade)
$config = [];
$sql_config = "SELECT chave, valor FROM Configuracoes";
$result_config = $conn->query($sql_config); 

if ($result_config) {
    while ($row_config = $result_config->fetch_assoc()) {
        $config[$row_config['chave']] = $row_config['valor'];
    }
    $result_config->free();
} else {
    die("Erro fatal: Não foi possível carregar as configurações do site.");
}

// 4. ARQUITETURA DE CAMINHOS (URLs) - [LIMPEZA DE DOMÍNIO]
$config['base_path'] = '/';
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

// Garante que a base_url sempre tenha a barra final correta
$config['base_url'] = $protocol . '://' . $host . $config['base_path'];
if (substr($config['base_url'], -1) !== '/') {
    $config['base_url'] .= '/';
}

// 5. VERSÃO DE ASSETS (MODO DEV)
global $asset_version;
if (isset($config['modo_dev']) && $config['modo_dev'] == '1') {
    $asset_version = time();
} else {
    $asset_version = $config['versao_assets'] ?? '1.0.0';
}

// 6. INICIALIZAÇÃO DE SESSÃO
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 7. SEGURANÇA CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); 
}

function get_csrf_token() {
    return $_SESSION['csrf_token'] ?? '';
}

function verify_csrf_token($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}