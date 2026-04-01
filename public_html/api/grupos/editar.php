<?php
/**
 * api/grupos/editar.php
 * API de Ação: Editar Informações do Grupo.
 * PAPEL: Validar permissões e persistir alterações de nome, descrição e privacidade.
 * VERSÃO: 1.0 (Arquitetura por Endpoints - socialbr.lol)
 */

// 1. CONFIGURAÇÃO E SEGURANÇA DE SESSÃO
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Definimos o retorno como JSON para interatividade via AJAX
header('Content-Type: application/json');

// Inclusão das dependências (Sobe 3 níveis para sair de api/grupos/)
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/GruposLogic.php';

// Bloqueio: Apenas requisições POST de utilizadores logados
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
        'msg'    => 'Falha de validação de segurança (Token Inválido).'
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

// 4. VERIFICAÇÃO DE PERMISSÃO (Apenas Dono ou Admin do Site)
$grupo = GruposLogic::getGroupData($conn, $id_grupo, $id_usuario_logado);
$is_admin_site = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');

if (!$grupo || ($grupo['nivel_permissao'] !== 'dono' && !$is_admin_site)) {
    echo json_encode([
        'status' => 'erro', 
        'msg'    => 'Você não tem permissão para editar as configurações deste grupo.'
    ]);
    exit();
}

// 5. PREPARAÇÃO DOS DADOS
$dados = [
    'nome'        => trim($_POST['nome'] ?? ''),
    'descricao'   => trim($_POST['descricao'] ?? ''),
    'privacidade' => in_array($_POST['privacidade'], ['publico', 'privado']) ? $_POST['privacidade'] : 'publico'
];

// Validação mínima obrigatória
if (empty($dados['nome'])) {
    echo json_encode([
        'status' => 'erro', 
        'msg'    => 'O nome do grupo é obrigatório.'
    ]);
    exit();
}

// 6. EXECUÇÃO VIA CÉREBRO (GruposLogic)
if (GruposLogic::atualizarGrupo($conn, $id_grupo, $dados)) {
    echo json_encode([
        'status' => 'sucesso',
        'msg'    => 'As informações do grupo foram atualizadas com sucesso!'
    ]);
} else {
    echo json_encode([
        'status' => 'erro',
        'msg'    => 'Ocorreu um erro ao salvar as alterações no banco de dados.'
    ]);
}