<?php
/**
 * api/grupos/gerenciar_membro.php
 * API de Ação: Gestão Individual de Membros.
 * PAPEL: Processar promoções, transferências de posse e expulsões (Toast Sync).
 * VERSÃO: 1.2 - Padronização Global de Tipos (socialbr.lol)
 */

// 1. CONFIGURAÇÃO E SEGURANÇA DE SESSÃO
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Resposta em JSON para interatividade fluida
header('Content-Type: application/json');

// Inclusão das dependências
require_once __DIR__ . '/../../../config/database.php';
// IMPORTANTE: Inclui o dicionário de tipos de notificação para manter o padrão
require_once __DIR__ . '/../../../config/tipos_notificacoes.php';
require_once __DIR__ . '/../../../src/GruposLogic.php';

// Bloqueio: Apenas requisições POST de utilizadores autenticados
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'erro', 'msg' => 'Acesso negado.']);
    exit();
}

// 2. VALIDAÇÃO CSRF (Segurança Obrigatória)
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['status' => 'erro', 'msg' => 'Falha de validação CSRF.']);
    exit();
}

// 3. CAPTURA DOS PARÂMETROS
$id_usuario_logado = (int)$_SESSION['user_id'];
$id_grupo          = (int)($_POST['id_grupo'] ?? 0);
$id_usuario_alvo   = (int)($_POST['id_usuario_alvo'] ?? 0);
$acao              = $_POST['acao'] ?? ''; // 'promover_mod', 'rebaixar_membro', 'tornar_dono', 'remover'

if ($id_grupo <= 0 || $id_usuario_alvo <= 0 || empty($acao)) {
    echo json_encode(['status' => 'erro', 'msg' => 'Dados incompletos na requisição.']);
    exit();
}

// 4. VERIFICAÇÃO DE AUTORIDADE (Apenas Dono ou Admin)
$grupo = GruposLogic::getGroupData($conn, $id_grupo, $id_usuario_logado);
$is_admin_site = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');

if (!$grupo || ($grupo['nivel_permissao'] !== 'dono' && !$is_admin_site)) {
    echo json_encode(['status' => 'erro', 'msg' => 'Você não tem permissão para gerenciar membros deste grupo.']);
    exit();
}

// 5. PROCESSAMENTO DAS AÇÕES VIA CÉREBRO (GruposLogic)
$sucesso = false;
$msg_retorno = "Operação realizada!";
$tipo_notif = null;

switch ($acao) {
    case 'promover_mod':
        $sucesso = GruposLogic::alterarPapelMembro($conn, $id_grupo, $id_usuario_alvo, 'moderador');
        $msg_retorno = "Membro promovido a moderador.";
        // USANDO CONSTANTE:
        $tipo_notif = NOTIF_PROMOCAO_MODERADOR; 
        break;

    case 'rebaixar_membro':
        $sucesso = GruposLogic::alterarPapelMembro($conn, $id_grupo, $id_usuario_alvo, 'membro');
        $msg_retorno = "Moderador rebaixado a membro comum.";
        // USANDO CONSTANTE:
        $tipo_notif = NOTIF_REBAIXAMENTO_MEMBRO;
        break;

    case 'tornar_dono':
        $sucesso = GruposLogic::alterarPapelMembro($conn, $id_grupo, $id_usuario_alvo, 'dono');
        $msg_retorno = "Propriedade do grupo transferida com sucesso.";
        // USANDO CONSTANTE:
        $tipo_notif = NOTIF_TRANSFERENCIA_DONO;
        break;

    case 'remover':
        $sucesso = GruposLogic::removerMembro($conn, $id_grupo, $id_usuario_alvo);
        $msg_retorno = "Membro removido do grupo.";
        // USANDO CONSTANTE:
        $tipo_notif = NOTIF_EXPULSAO_GRUPO;
        break;

    default:
        echo json_encode(['status' => 'erro', 'msg' => 'Ação de moderação inválida.']);
        exit();
}

// 6. LÓGICA DE NOTIFICAÇÃO PADRONIZADA (Toast Sync)
if ($sucesso && $tipo_notif && $id_usuario_alvo !== $id_usuario_logado) {
    try {
        $sqlNotif = "INSERT INTO notificacoes (usuario_id, remetente_id, tipo, id_referencia, lida, data_criacao) 
                      VALUES (?, ?, ?, ?, 0, NOW())";
        $stmtN = $conn->prepare($sqlNotif);
        // id_referencia é o ID do grupo para que o link da notificação funcione
        // bind_param: iisi (alvo, remetente, tipo_string, id_grupo)
        $stmtN->bind_param("iisi", $id_usuario_alvo, $id_usuario_logado, $tipo_notif, $id_grupo);
        $stmtN->execute();
        $stmtN->close();
    } catch (Exception $e) {
        error_log("Erro ao notificar alteração de cargo/membro: " . $e->getMessage());
    }
}

// 7. RESPOSTA FINAL
if ($sucesso) {
    echo json_encode(['status' => 'sucesso', 'msg' => $msg_retorno]);
} else {
    echo json_encode(['status' => 'erro', 'msg' => 'Erro ao processar a ação no banco de dados.']);
}