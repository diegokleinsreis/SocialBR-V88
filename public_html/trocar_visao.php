<?php
/**
 * FICHEIRO: public_html/trocar_visao.php
 * OBJETIVO: Servir de ponto de acesso público para a lógica de administração.
 * DOCUMENTAÇÃO: Este ficheiro inclui o processador que está na pasta protegida.
 */

// Definimos o caminho para o ficheiro de lógica que está um nível acima
// __DIR__ refere-se à pasta atual (public_html)
// /../ sobe um nível para a raiz do servidor
$caminho_logica = __DIR__ . '/../acoes/admin_ver_como.php';

// Verificamos se o ficheiro de lógica existe antes de o chamar
if (file_exists($caminho_logica)) {
    require_once $caminho_logica;
} else {
    // Caso ocorra algum erro de caminho, exibe uma mensagem simples
    die("Erro crítico: O processador de visão não foi encontrado na pasta 'acoes'.");
}