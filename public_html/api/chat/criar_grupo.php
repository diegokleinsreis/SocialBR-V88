<?php
/**
 * ARQUIVO: api/chat/criar_grupo.php
 * PAPEL: Orquestrar a criação de conversas coletivas (Grupos de Chat).
 * VERSÃO: 3.7 - Padronização Global CSRF (socialbr.lol)
 * AJUSTE: Sincronização do nome do token de segurança com o sistema blindado.
 */

// Define o cabeçalho da resposta como JSON com suporte a UTF-8
header('Content-Type: application/json; charset=utf-8');

// 1. --- [INICIALIZAÇÃO E SEGURANÇA DE SESSÃO] ---
if (session_status() == PHP_SESSION_NONE) { 
    session_start(); 
}

/**
 * FUNÇÃO AUXILIAR: Resposta de erro padronizada
 */
function error_response($message, $type = 'validacao') {
    echo json_encode(['success' => false, 'error' => $type, 'message' => $message]);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    error_response("Sessão expirada ou acesso negado.", 'auth');
}

// 2. --- [DEPENDÊNCIAS] ---
require_once __DIR__ . '/../../../config/database.php';
// Inclui o dicionário de tipos de notificação para manter o padrão do sistema
require_once __DIR__ . '/../../../config/tipos_notificacoes.php';
require_once __DIR__ . '/../../../src/ChatLogic.php';

$criadorId = (int)$_SESSION['user_id'];

// 3. --- [TRAVA DE SEGURANÇA: VERIFICAÇÃO DE E-MAIL] ---
$sql_v = "SELECT email_verificado FROM Usuarios WHERE id = ? LIMIT 1";
$stmt_v = $conn->prepare($sql_v);
$stmt_v->bind_param("i", $criadorId);
$stmt_v->execute();
$res_v = $stmt_v->get_result()->fetch_assoc();
$is_confirmado = ($res_v && (int)$res_v['email_verificado'] === 1);
$stmt_v->close();

if (!$is_confirmado) {
    error_response("Ação Bloqueada: Confirme o seu e-mail para criar grupos de chat.", 'verificacao_pendente');
}

// 4. --- [CAPTURA E VALIDAÇÃO DE DADOS] ---
$titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
$participantesRaw = isset($_POST['participantes']) ? $_POST['participantes'] : '[]';

// --- CORREÇÃO CSRF: Sincronizado com o padrão global 'csrf_token' ---
$token_recebido = $_POST['csrf_token'] ?? ''; 

// Validação de Segurança contra ataques Cross-Site
if (empty($token_recebido) || !isset($_SESSION['csrf_token']) || $token_recebido !== $_SESSION['csrf_token']) {
    error_response("Falha na validação de segurança.", 'csrf');
}

// Decodifica a lista de IDs de amigos enviados pelo frontend
$participantesIds = json_decode($participantesRaw, true);

if (empty($titulo)) {
    error_response("O grupo precisa de um nome.");
}

if (!is_array($participantesIds) || count($participantesIds) < 1) {
    error_response("Selecione pelo menos um amigo para o grupo.");
}

// 5. --- [EXECUÇÃO VIA CHATLOGIC] ---
// Cria a entrada na tabela chat_conversas e vincula os participantes
$conversaId = ChatLogic::createGroupConversation($conn, $criadorId, $titulo, $participantesIds);

if ($conversaId) {
    
    // 6. --- [SISTEMA DE NOTIFICAÇÃO PADRONIZADO] ---
    // Usamos a constante global para garantir a sincronia com o JS e Histórico
    $tipo_notif = NOTIF_CONVITE_CHAT_GRUPO;
    
    $sql_notif = "INSERT INTO notificacoes (usuario_id, remetente_id, tipo, id_referencia, lida, data_criacao) 
                  VALUES (?, ?, ?, ?, 0, NOW())";
    
    $stmt_notif = $conn->prepare($sql_notif);

    if ($stmt_notif) {
        foreach ($participantesIds as $convidadoId) {
            $convidadoId = (int)$convidadoId;
            if ($convidadoId > 0 && $convidadoId !== $criadorId) {
                // id_referencia aponta para o ID da conversa para o link do Toast/Notificação
                // bind_param: iisi (id_recebe, id_envia, string_tipo, id_referencia)
                $stmt_notif->bind_param("iisi", $convidadoId, $criadorId, $tipo_notif, $conversaId);
                $stmt_notif->execute();
            }
        }
        $stmt_notif->close();
    }

    // Retorna sucesso para o JavaScript realizar o redirecionamento
    echo json_encode([
        'success' => true,
        'message' => 'Grupo criado com sucesso!',
        'conversa_id' => $conversaId
    ]);

} else {
    error_response("Falha técnica ao criar o grupo no banco de dados.", 'database');
}

$conn->close();