<?php
/**
 * api/grupos/sair.php
 * API de Ação: Sair de um Grupo.
 * PAPEL: Remover o vínculo do utilizador na tabela Grupos_Membros.
 * VERSÃO: 1.0 (Componentização AJAX - socialbr.lol)
 */

// 1. CONFIGURAÇÃO E SEGURANÇA DE SESSÃO
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Resposta em formato JSON para processamento via JavaScript
header('Content-Type: application/json');

// Inclusão das dependências (3 níveis para sair de api/grupos/)
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/GruposLogic.php';

// Bloqueio: Apenas requisições POST de utilizadores autenticados
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'erro', 
        'msg'    => 'Acesso negado ou sessão expirada.'
    ]);
    exit();
}

// 2. VALIDAÇÃO DE SEGURANÇA (TOKEN CSRF)
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode([
        'status' => 'erro', 
        'msg'    => 'Falha na validação de segurança (Token Inválido).'
    ]);
    exit();
}

// 3. CAPTURA E VALIDAÇÃO DE PARÂMETROS
$id_usuario_logado = (int)$_SESSION['user_id'];
$id_grupo = (int)($_POST['id_grupo'] ?? 0);

if ($id_grupo <= 0) {
    echo json_encode([
        'status' => 'erro', 
        'msg'    => 'Identificação do grupo inválida.'
    ]);
    exit();
}

// 4. EXECUÇÃO VIA MOTOR DE LÓGICA (GruposLogic)
// O método sair() já possui a trava interna que impede o Dono de sair.
$sucesso = GruposLogic::sair($conn, $id_grupo, $id_usuario_logado);

// 5. RESPOSTA FINAL
if ($sucesso) {
    echo json_encode([
        'status' => 'sucesso',
        'msg'    => 'Você saiu do grupo com sucesso.'
    ]);
} else {
    echo json_encode([
        'status' => 'erro',
        'msg'    => 'Não foi possível processar a saída. Verifique se você é o proprietário.'
    ]);
}