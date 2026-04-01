<?php
/**
 * api/grupos/convidar.php
 * API de Ação: Disparar convite de amizade para um grupo.
 * PAPEL: Validar segurança e inserir notificação de convite no sistema (Toast Sync).
 * VERSÃO: 1.2 - Padronização Global de Tipos (socialbr.lol)
 */

// 1. CONFIGURAÇÕES E SEGURANÇA DE SESSÃO
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Resposta em JSON
header('Content-Type: application/json; charset=utf-8');

// Inclusão das dependências
require_once __DIR__ . '/../../../config/database.php';
// IMPORTANTE: Inclui o dicionário de tipos de notificação
require_once __DIR__ . '/../../../config/tipos_notificacoes.php';
require_once __DIR__ . '/../../../src/GruposLogic.php';

// Bloqueio: Apenas requisições POST de utilizadores autenticados
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'erro', 'msg' => 'Acesso não autorizado.']);
    exit();
}

// 2. VALIDAÇÃO CSRF (Segurança contra ataques cross-site)
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['status' => 'erro', 'msg' => 'Falha na validação de segurança (CSRF).']);
    exit();
}

// 3. CAPTURA E LIMPEZA DE PARÂMETROS
$id_remetente = (int)$_SESSION['user_id'];
$id_amigo     = (int)($_POST['id_amigo'] ?? 0);
$id_grupo     = (int)($_POST['id_grupo'] ?? 0);

if ($id_amigo <= 0 || $id_grupo <= 0) {
    echo json_encode(['status' => 'erro', 'msg' => 'Parâmetros de convite inválidos.']);
    exit();
}

// 4. VALIDAÇÃO DE INTEGRIDADE
// Verifica se o remetente é membro do grupo antes de permitir o convite
$membro = GruposLogic::getGroupData($conn, $id_grupo, $id_remetente);
if (!$membro || empty($membro['membro_id'])) {
    echo json_encode(['status' => 'erro', 'msg' => 'Apenas membros podem convidar amigos.']);
    exit();
}

// Definimos o tipo de notificação usando a constante oficial
$tipo_notif = NOTIF_CONVITE_GRUPO;

try {
    // --- LÓGICA ANTI-FLOOD SINCRONIZADA ---
    // Verifica se já existe um convite pendente usando a constante padronizada
    $sqlCheck = "SELECT id FROM notificacoes WHERE usuario_id = ? AND remetente_id = ? AND tipo = ? AND id_referencia = ? AND lida = 0";
    $stmtCheck = $conn->prepare($sqlCheck);
    // bind_param: iisi (id_amigo, id_remetente, string_tipo, id_grupo)
    $stmtCheck->bind_param("iisi", $id_amigo, $id_remetente, $tipo_notif, $id_grupo);
    $stmtCheck->execute();
    if ($stmtCheck->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'erro', 'msg' => 'Este amigo já possui um convite pendente para este grupo.']);
        $stmtCheck->close();
        exit();
    }
    $stmtCheck->close();

    // 5. EXECUÇÃO VIA CÉREBRO (GruposLogic)
    if (GruposLogic::enviarConvite($conn, $id_grupo, $id_remetente, $id_amigo)) {
        
        // --- INÍCIO DA INJEÇÃO DE NOTIFICAÇÃO PADRONIZADA ---
        // O id_referencia deve ser o ID do grupo para o link do Toast funcionar corretamente.
        $sqlNotif = "INSERT INTO notificacoes (usuario_id, remetente_id, tipo, id_referencia, lida, data_criacao) 
                      VALUES (?, ?, ?, ?, 0, NOW())";
        $stmtN = $conn->prepare($sqlNotif);
        $stmtN->bind_param("iisi", $id_amigo, $id_remetente, $tipo_notif, $id_grupo);
        $stmtN->execute();
        $stmtN->close();
        // --- FIM DA INJEÇÃO ---

        echo json_encode([
            'status' => 'sucesso',
            'msg'    => 'Convite enviado com sucesso!'
        ]);
    } else {
        echo json_encode([
            'status' => 'erro',
            'msg'    => 'Não foi possível enviar o convite no momento.'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'erro',
        'msg'    => 'Erro interno ao processar convite: ' . $e->getMessage()
    ]);
}

$conn->close();