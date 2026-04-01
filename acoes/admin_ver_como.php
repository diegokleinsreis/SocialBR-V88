<?php
/**
 * FICHEIRO: acoes/admin_ver_como.php
 * OBJETIVO: Gerir o modo de simulação para qualquer utilizador Admin.
 * VERSÃO: 3.2 (Correção de variável: user_role)
 */

// Iniciamos a sessão para poder gravar as preferências de visão
session_start();

/**
 * 1. SEGURANÇA MULTI-ADMIN
 * Verificamos se o utilizador logado possui o cargo de 'admin'.
 * Utilizamos a chave 'user_role' conforme definido no seu sistema de menu.
 */
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("HTTP/1.1 403 Forbidden");
    die("Erro: Acesso restrito a administradores.");
}

/**
 * 2. CAPTURAR O MODO DE VISÃO
 * O modo é enviado via URL pela ponte trocar_visao.php (ex: ?modo=amigo). 
 */
$modo_solicitado = $_GET['modo'] ?? 'reset';

/**
 * 3. PROCESSAMENTO DA ESCOLHA
 * 'reset' -> Limpa a simulação e volta à visão real.
 * Outros -> Define a "máscara" (dono, amigo, visitante, bloqueado) na sessão.
 */
if ($modo_solicitado === 'reset') {
    // Remove a variável de simulação da memória da sessão
    unset($_SESSION['admin_modo_visao']);
} else {
    // Grava o modo que será interceptado pelo orquestrador do perfil
    $_SESSION['admin_modo_visao'] = $modo_solicitado;
}

/**
 * 4. REDIRECIONAMENTO FLUIDO
 * O servidor envia o administrador de volta para a página onde ele estava.
 */
$url_retorno = $_SERVER['HTTP_REFERER'] ?? '../public_html/index.php';

header("Location: " . $url_retorno);
exit;