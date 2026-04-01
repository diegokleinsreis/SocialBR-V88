<?php
/**
 * ARQUIVO: api/admin/registrar_erro_js.php
 * VERSÃO: 1.2 (Reversion: Estabilização de Rede - socialbr.lol)
 * PAPEL: Receber falhas do navegador (JS/Promises) e persistir via ErrorLogic.
 */

// 1. --- [CONFIGURAÇÃO E CABEÇALHOS] ---
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/ErrorLogic.php';

header('Content-Type: application/json');

// 2. --- [SEGURANÇA DE ORIGEM] ---
// Só aceita requisições se vierem do seu próprio domínio (Proteção contra spam externo)
$allowed_origin = "socialbr.lol"; 
if (isset($_SERVER['HTTP_HOST']) && !str_contains($_SERVER['HTTP_HOST'], $allowed_origin)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Origem não autorizada.']);
    exit;
}

// 3. --- [CAPTURA DO INPUT JSON] ---
$inputJSON = file_get_contents('php://input');
$dados = json_decode($inputJSON, true);

if (!$dados) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Payload inválido.']);
    exit;
}

// 4. --- [PROCESSAMENTO VIA ERRORLOGIC] ---
// O objeto $pdo é fornecido pelo arquivo database.php
$errorLogic = new ErrorLogic($pdo);

// Mapeamento de campos padrão do JavaScript para o Backend
$tipo     = $dados['tipo'] ?? 'JavaScript Error';
$mensagem = $dados['mensagem'] ?? 'Sem mensagem';
$arquivo  = $dados['arquivo'] ?? 'N/A';
$linha    = (int)($dados['linha'] ?? 0);
$stack    = $dados['stack'] ?? null;
$url_real = $dados['url_atual'] ?? 'Desconhecida';

/**
 * Nota: Removido o suporte a metadados de rede (HTTP Status/Método) 
 * para estabilizar o tráfego do servidor e evitar loops de 403.
 */

// Formatação final: Inclui a URL de origem para facilitar a localização do bug
$mensagem_completa = "[URL: $url_real] - " . $mensagem;

$sucesso = $errorLogic->registrarErro(
    $tipo,
    $mensagem_completa,
    $arquivo,
    $linha,
    $stack
);

// 5. --- [RESPOSTA] ---
if ($sucesso) {
    echo json_encode(['success' => true, 'message' => 'Evento Sentinela registrado com sucesso.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Falha na persistência do log.']);
}