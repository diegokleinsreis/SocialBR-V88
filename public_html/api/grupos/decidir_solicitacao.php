<?php
/**
 * api/grupos/decidir_solicitacao.php
 * API de Ação: Aprovar ou Recusar solicitações de entrada.
 * PAPEL: Validar permissões de moderação, persistir a entrada e notificar o utilizador.
 * VERSÃO: 1.2 - Padronização Global de Tipos (socialbr.lol)
 */

// 1. CONFIGURAÇÃO E SEGURANÇA DE SESSÃO
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Resposta em JSON para o componente solicitacoes_grupo.php
header('Content-Type: application/json');

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

// 2. VALIDAÇÃO CSRF (Segurança contra ataques cross-site)
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['status' => 'erro', 'msg' => 'Falha na validação de segurança.']);
    exit();
}

// 3. CAPTURA E LIMPEZA DE PARÂMETROS
$id_usuario_logado = (int)$_SESSION['user_id'];
$id_solicitacao    = (int)($_POST['id_solicitacao'] ?? 0);
$id_grupo          = (int)($_POST['id_grupo'] ?? 0);
$acao               = $_POST['acao'] ?? ''; // 'aprovar' ou 'recusar'

if ($id_solicitacao <= 0 || $id_grupo <= 0 || !in_array($acao, ['aprovar', 'recusar'])) {
    echo json_encode(['status' => 'erro', 'msg' => 'Parâmetros de requisição inválidos.']);
    exit();
}

// 4. VERIFICAÇÃO DE AUTORIDADE (Apenas Dono ou Moderador podem decidir)
$grupo = GruposLogic::getGroupData($conn, $id_grupo, $id_usuario_logado);

// Verificação de permissão: Precisa ser Dono, Moderador ou Admin do site
$tem_permissao = ($grupo && in_array($grupo['nivel_permissao'], ['dono', 'moderador']));
$is_admin_site = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');

if (!$tem_permissao && !$is_admin_site) {
    echo json_encode(['status' => 'erro', 'msg' => 'Você não tem permissão para moderar este grupo.']);
    exit();
}

// --- Identificar o solicitante ANTES da ação ---
// Buscamos o ID do usuário que pediu para entrar antes que o GruposLogic remova o registro da solicitação
$id_solicitante = 0;
try {
    $sqlBusca = "SELECT id_usuario FROM Grupos_Solicitacoes WHERE id = ?";
    $stmtB = $conn->prepare($sqlBusca);
    $stmtB->bind_param("i", $id_solicitacao);
    $stmtB->execute();
    $resB = $stmtB->get_result()->fetch_assoc();
    $id_solicitante = $resB['id_usuario'] ?? 0;
    $stmtB->close();
} catch (Exception $e) {
    // Erro silencioso na busca prévia
}

// 5. EXECUÇÃO VIA CÉREBRO (GruposLogic)
if (GruposLogic::decidirSolicitacao($conn, $id_solicitacao, $id_grupo, $acao)) {
    
    // --- LÓGICA DE NOTIFICAÇÃO PADRONIZADA (Toast Sync) ---
    // Se a ação foi aprovação, notificamos o novo membro usando a constante oficial
    if ($acao === 'aprovar' && $id_solicitante > 0) {
        try {
            // USANDO A CONSTANTE PADRONIZADA:
            $tipo_notif = NOTIF_ACEITE_SOLICITACAO;
            
            $sqlNotif = "INSERT INTO notificacoes (usuario_id, remetente_id, tipo, id_referencia, lida, data_criacao) 
                          VALUES (?, ?, ?, ?, 0, NOW())";
            $stmtN = $conn->prepare($sqlNotif);
            // id_referencia é o ID do grupo para que o link leve à comunidade
            // bind_param: iisi (id_recebe, id_envia, string_tipo, id_referencia)
            $stmtN->bind_param("iisi", $id_solicitante, $id_usuario_logado, $tipo_notif, $id_grupo);
            $stmtN->execute();
            $stmtN->close();
        } catch (Exception $e) {
            error_log("Erro ao notificar aprovação de grupo: " . $e->getMessage());
        }
    }

    $msg_sucesso = ($acao === 'aprovar') ? 'Solicitação aprovada com sucesso!' : 'Solicitação recusada.';
    echo json_encode([
        'status' => 'sucesso',
        'msg'    => $msg_sucesso
    ]);
} else {
    echo json_encode([
        'status' => 'erro',
        'msg'    => 'Erro ao processar a decisão no banco de dados.'
    ]);
}