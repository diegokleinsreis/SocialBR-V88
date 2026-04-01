<?php
/**
 * FICHEIRO: public_html/perfil.php
 * OBJETIVO: Ponte de acesso que garante a base de dados e o orquestrador.
 */

// 1. Inicia a sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Carrega as configurações e a base de dados (Essencial para evitar Erro 500)
// Ajustamos o caminho para subir um nível e entrar na pasta config
$caminho_config = __DIR__ . '/../config/database.php';

if (file_exists($caminho_config)) {
    require_once $caminho_config;
} else {
    die("Erro Crítico: Configurações de base de dados não encontradas em: " . $caminho_config);
}

// 3. Chama o Orquestrador Real que está na pasta views
$caminho_perfil_real = __DIR__ . '/../views/perfil.php';

if (file_exists($caminho_perfil_real)) {
    require_once $caminho_perfil_real;
} else {
    header("HTTP/1.1 404 Not Found");
    die("Erro Crítico: O orquestrador de perfil não foi encontrado.");
}