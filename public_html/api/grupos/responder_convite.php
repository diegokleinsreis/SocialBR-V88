<?php
/**
 * api/grupos/responder_convite.php
 * API de Ação: Processar Aceitação ou Recusa de convites para grupos.
 * PAPEL: Validar segurança e executar a lógica de entrada/limpeza e notificar quem convidou.
 * VERSÃO: 1.2 - Padronização Global de Tipos (socialbr.lol)
 */

// 1. CONFIGURAÇÕES E SEGURANÇA DE SESSÃO
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Resposta em JSON para o orquestrador JS no ver.php
header('Content-Type: application/json; charset=utf-8');

// Inclusão das dependências
require_once __DIR__ . '/../../../config/database.php';
// IMPORTANTE: Inclui o dicionário de tipos de notificação para manter o padrão
require_once __DIR__ . '/../../../config/tipos_notificacoes.php';
require_once __DIR__ . '/../../../src/GruposLogic.php';

// Bloqueio: Apenas requisições POST de utilizadores autenticados
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'erro', 'msg' => 'Acesso não autorizado.']);
    exit();
}

// 2. VALIDAÇÃO CSRF (Blindagem contra ataques Cross-Site)
$token_recebido = $_POST['csrf_token'] ?? '';
if (empty($token_recebido) || $token_recebido !== $_SESSION['csrf_token']) {
    echo json_encode(['status' => 'erro', 'msg' => 'Falha na validação de segurança (CSRF).']);
    exit();
}

// 3. CAPTURA E HIGIENIZAÇÃO DE PARÂMETROS
$user_id  = (int)$_SESSION['user_id'];
$id_grupo = (int)($_POST['id_grupo'] ?? 0);
$acao     = $_POST['acao'] ?? ''; // 'aceitar' ou 'recusar'

// Validação básica de input
if ($id_grupo <= 0 || !in_array($acao, ['aceitar', 'recusar'])) {
    echo json_encode(['status' => 'erro', 'msg' => 'Parâmetros de resposta inválidos.']);
    exit();
}

// --- IDENTIFICAR QUEM CONVIDOU ANTES DE PROCESSAR ---
// Usamos a constante NOTIF_CONVITE_GRUPO para encontrar o remetente original
$id_quem_convidou = 0;
try {
    $tipo_busca = NOTIF_CONVITE_GRUPO;
    $sqlBusca = "SELECT remetente_id FROM notificacoes 
                  WHERE usuario_id = ? AND id_referencia = ? AND tipo = ? 
                  ORDER BY data_criacao DESC LIMIT 1";
    $stmtB = $conn->prepare($sqlBusca);
    // bind_param: iis (usuario, grupo, tipo_string)
    $stmtB->bind_param("iis", $user_id, $id_grupo, $tipo_busca);
    $stmtB->execute();
    $resB = $stmtB->get_result()->fetch_assoc();
    $id_quem_convidou = $resB['remetente_id'] ?? 0;
    $stmtB->close();
} catch (Exception $e) {
    // Erro silencioso na busca
}

try {
    // 4. EXECUÇÃO VIA CÉREBRO (GruposLogic)
    $sucesso = GruposLogic::responderConvite($conn, $id_grupo, $user_id, $acao);

    if ($sucesso) {
        // --- LÓGICA DE NOTIFICAÇÃO PADRONIZADA (Toast Sync) ---
        // Se o utilizador aceitou o convite, notificamos quem o convidou usando a constante oficial
        if ($acao === 'aceitar' && $id_quem_convidou > 0) {
            try {
                // USANDO A CONSTANTE PADRONIZADA:
                $tipo_notif = NOTIF_ACEITE_CONVITE_GRUPO;
                
                $sqlNotif = "INSERT INTO notificacoes (usuario_id, remetente_id, tipo, id_referencia, lida, data_criacao) 
                              VALUES (?, ?, ?, ?, 0, NOW())";
                $stmtN = $conn->prepare($sqlNotif);
                // bind_param: iisi (quem recebe, quem aceitou, tipo, id_grupo)
                $stmtN->bind_param("iisi", $id_quem_convidou, $user_id, $tipo_notif, $id_grupo);
                $stmtN->execute();
                $stmtN->close();
            } catch (Exception $e) {
                error_log("Erro ao notificar aceite de convite: " . $e->getMessage());
            }
        }

        echo json_encode([
            'status' => 'sucesso',
            'msg'    => ($acao === 'aceitar') ? 'Bem-vindo ao grupo!' : 'Convite removido.'
        ]);
    } else {
        echo json_encode([
            'status' => 'erro',
            'msg'    => 'Não foi possível processar a sua resposta no momento.'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'erro',
        'msg'    => 'Erro interno ao responder: ' . $e->getMessage()
    ]);
}

$conn->close();