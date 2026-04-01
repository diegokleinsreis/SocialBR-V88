<?php
/**
 * ARQUIVO: api/grupos/excluir.php
 * PAPEL: Realizar o "Soft Delete" (exclusão lógica) de um grupo.
 * VERSÃO: 3.5 - Padronização Arquiteto (socialbr.lol)
 * NOTA: Esta API apenas desativa o grupo no banco, mantendo os arquivos físicos.
 */

// 1. --- [CONFIGURAÇÃO E SEGURANÇA] ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define o cabeçalho da resposta como JSON com suporte a caracteres especiais
header('Content-Type: application/json; charset=utf-8');

// Inclusão das dependências
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/GruposLogic.php';

/**
 * FUNÇÃO AUXILIAR: Resposta de erro padronizada para Grupos
 */
function json_err($msg, $type = 'erro') {
    echo json_encode(['success' => false, 'error' => $type, 'message' => $msg]);
    exit();
}

// Bloqueio: Apenas requisições POST de utilizadores logados
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    json_err("Acesso negado. Por favor, realize o login.", 'auth');
}

// 2. --- [VALIDAÇÃO CSRF] ---
// Usando a função padronizada do sistema
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    http_response_code(403);
    json_err("Falha na validação de segurança (Token Inválido).", 'csrf');
}

// 3. --- [CAPTURA E VALIDAÇÃO DE DADOS] ---
$id_usuario_logado = (int)$_SESSION['user_id'];
$id_grupo = (int)($_POST['id_grupo'] ?? 0);

if ($id_grupo <= 0) {
    json_err("ID do grupo inválido.");
}

// 4. --- [VERIFICAÇÃO DE PERMISSÃO] ---
// Apenas o Dono ou o Admin do site podem excluir o grupo
$grupo = GruposLogic::getGroupData($conn, $id_grupo, $id_usuario_logado);
$is_admin_site = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');

if (!$grupo || ($grupo['nivel_permissao'] !== 'dono' && !$is_admin_site)) {
    json_err("Permissão negada. Apenas o proprietário pode excluir o grupo.", 'security');
}

// 5. --- [EXECUÇÃO VIA LÓGICA DE NEGÓCIO] ---
// O método excluirGrupo apenas muda o status na tabela 'Grupos' para 'excluido'
if (GruposLogic::excluirGrupo($conn, $id_grupo)) {
    echo json_encode([
        'success' => true,
        'message' => 'Grupo excluído com sucesso. Redirecionando...'
    ]);
} else {
    json_err("Erro ao processar a exclusão no banco de dados.", 'database');
}

$conn->close();