<?php
/**
 * logout.php - Encerra a sessão do usuário.
 */

// PASSO 1: Inicia a sessão para poder acessá-la.
session_start();

// PASSO 1.5: Carrega a configuração (para o base_path)
// Adicionamos isto para que o redirecionamento funcione corretamente.
require_once __DIR__ . '/../../../config/database.php';
// $config['base_path'] já está disponível

// PASSO 2: Limpa todas as variáveis da sessão.
$_SESSION = array();

// PASSO 3: Destrói o cookie da sessão no navegador do usuário.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// PASSO 4: Finalmente, destrói a sessão no servidor.
session_destroy();

// PASSO 5: Redireciona o usuário de volta para a página de login (Corrigido)
// Usa a nova rota: /~klscom/login
header("Location: " . $config['base_path'] . "login"); // <-- CORRIGIDO
exit();
?>