<?php
/**
 * api/grupos/amigos_convite.php
 * API de Consulta: Retorna amigos aptos a serem convidados para o grupo.
 * PAPEL: Filtrar amigos do usuário que ainda não são membros da comunidade.
 * VERSÃO: 1.0 (Integração com GruposLogic v2.6 - socialbr.lol)
 */

// 1. CONFIGURAÇÕES E SEGURANÇA DE SESSÃO
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Resposta em JSON para o modal_convite.php
header('Content-Type: application/json; charset=utf-8');

// Inclusão das dependências
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/GruposLogic.php';

// Bloqueio: Apenas utilizadores autenticados via GET
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Acesso negado.']);
    exit();
}

// 2. CAPTURA DE PARÂMETROS
$id_usuario_logado = (int)$_SESSION['user_id'];
$id_grupo = (int)($_GET['id_grupo'] ?? 0);

if ($id_grupo <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID do grupo inválido.']);
    exit();
}

// 3. VERIFICAÇÃO DE PERMISSÃO (Opcional: Apenas membros podem convidar?)
// Se desejar restringir convites apenas a membros, descomente as linhas abaixo:
/*
$membro = GruposLogic::getGroupData($conn, $id_grupo, $id_usuario_logado);
if (!$membro || empty($membro['membro_id'])) {
    echo json_encode(['success' => false, 'error' => 'Apenas membros podem convidar amigos.']);
    exit();
}
*/

try {
    // 4. EXECUÇÃO VIA CÉREBRO (GruposLogic v2.6)
    // Busca amigos com status 'aceite' que não estão na tabela Grupos_Membros para este ID
    $amigos = GruposLogic::getAmigosParaConvidar($conn, $id_grupo, $id_usuario_logado);

    // 5. RESPOSTA PADRONIZADA PARA O JAVASCRIPT
    echo json_encode([
        'success' => true,
        'amigos'  => $amigos
    ]);

} catch (Exception $e) {
    // Tratamento de erros inesperados
    echo json_encode([
        'success' => false,
        'error'   => 'Erro ao processar lista de amigos: ' . $e->getMessage()
    ]);
}

$conn->close();