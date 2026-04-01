<?php
/**
 * index.php - O Roteador Dinâmico (Front Controller)
 * VERSÃO: V87.0 - Estabilização de Rotas Parametrizadas (socialbr.lol)
 * PAPEL: Orquestrar rotas, gerenciar Sentinela e despachar IDs para os módulos.
 */

// 1. --- [CONFIGURAÇÃO DE AMBIENTE] ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. --- [CARREGAMENTO DE DEPENDÊNCIAS] ---
$baseDir = dirname(__DIR__, 1);
if (!file_exists($baseDir . '/config/database.php')) {
    die("Erro Crítico: Configuração do banco não encontrada.");
}
require_once $baseDir . '/config/database.php';

// Ativação do Sentinela (Monitoramento de Erros)
if (file_exists($baseDir . '/config/sentinela.php')) {
    require_once $baseDir . '/config/sentinela.php';
}

if (!file_exists($baseDir . '/src/RotasLogic.php')) {
    die("Erro Crítico: Classe RotasLogic não encontrada em /src/.");
}
require_once $baseDir . '/src/RotasLogic.php';

// 3. --- [PROCESSAMENTO DA URL] ---
$url = isset($_GET['url']) ? $_GET['url'] : '';
$url = trim($url, '/'); 

// Lógica da Raiz (/)
if ($url === '') {
    $url = (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) ? 'feed' : 'login';
}

// Extraímos a primeira parte apenas para verificações globais (manutenção/emergência)
$parts = explode('/', $url);
$route_primary = $parts[0]; 

// 4. --- [INICIALIZAÇÃO DO MOTOR] ---
$motor = new RotasLogic($pdo);
$rota_encontrada = $motor->buscarRota($url);

// 5. --- [SISTEMA DE REDUNDÂNCIA (PARA-QUEDAS)] ---
if (!$rota_encontrada) {
    $caminho_json = $baseDir . '/config/emergencia.json';
    if (file_exists($caminho_json)) {
        $backup = json_decode(file_get_contents($caminho_json), true);
        if (isset($backup['rotas']) && is_array($backup['rotas'])) {
            foreach ($backup['rotas'] as $r) {
                if ($r['slug'] === $route_primary) {
                    $rota_encontrada = $r;
                    break;
                }
            }
        }
    }
}

// 6. --- [MODO MANUTENÇÃO GLOBAL] ---
// Verificamos se o site todo ou a rota específica está em manutenção
if (isset($config['modo_manutencao']) && $config['modo_manutencao'] == '1') {
    $is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
    if (!$is_admin && !in_array($route_primary, ['login', 'manutencao'])) {
        http_response_code(503);
        require_once $baseDir . '/views/manutencao.php';
        exit;
    }
}

// 7. --- [VALIDAÇÃO DE ACESSO E EXECUÇÃO] ---
$verificacao = $motor->validarAcesso($rota_encontrada);

if ($verificacao['autorizado']) {
    
    /**
     * --- [INTERCEPTOR DE LEGADO: CHAT AJAX] ---
     * Mantemos a compatibilidade com os gatilhos AJAX do Chat V85.
     */
    if ($route_primary === 'chat' && (
        isset($_GET['ajax']) || isset($_GET['ajax_ver_midia']) || 
        isset($_GET['ajax_sidebar']) || isset($_GET['ajax_iniciador']) ||
        isset($_GET['ajax_info_contato']) || isset($_GET['ajax_info_grupo']) ||
        isset($_GET['ajax_painel_gestao'])
    )) {
        require_once $baseDir . '/src/ChatLogic.php';

        if (isset($_GET['ajax_ver_midia'])) {
            include $baseDir . '/views/chat/componentes/ver_midia_conversa.php';
        } elseif (isset($_GET['ajax_sidebar'])) {
            include $baseDir . '/views/chat/componentes/lista_conversas.php';
        } elseif (isset($_GET['ajax_iniciador'])) {
            include $baseDir . '/views/chat/componentes/modal_iniciador_chat.php';
        } elseif (isset($_GET['ajax_info_contato'])) {
            include $baseDir . '/views/chat/componentes/privado/info_contato.php';
        } elseif (isset($_GET['ajax_info_grupo'])) {
            include $baseDir . '/views/chat/componentes/grupos/info_grupo.php';
        } elseif (isset($_GET['ajax_painel_gestao'])) {
            include $baseDir . '/views/chat/componentes/grupos/painel_gestao.php';
        } elseif (isset($_GET['ajax'])) {
            if (isset($_GET['id'])) include $baseDir . '/views/chat/componentes/janela_conversa.php';
            else include $baseDir . '/views/chat/componentes/estado_vazio.php';
        }
        exit; 
    }

    // --- [CARREGAMENTO DA PÁGINA COMPLETA] ---
    // O RotasLogic já injetou o ID no $_GET se a rota for parametrizada
    require_once $verificacao['arquivo'];

} else {
    // --- [TRATAMENTO DE ERROS DE ROTA] ---
    switch ($verificacao['erro']) {
        case 'login_required':
            require_once $baseDir . '/views/perfil/convite_login.php';
            break;

        case 'em_breve':
            http_response_code(403); 
            if (file_exists($baseDir . '/views/eventos/em_breve.php')) {
                require_once $baseDir . '/views/eventos/em_breve.php';
            } else {
                die("Este módulo será liberado em breve! Aguarde o lançamento.");
            }
            break;

        case 503:
            http_response_code(503);
            require_once $baseDir . '/views/manutencao.php';
            break;

        case 404:
        default:
            http_response_code(404);
            require_once $baseDir . '/views/404.php';
            break;
    }
}