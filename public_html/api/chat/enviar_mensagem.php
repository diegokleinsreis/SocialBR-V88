<?php
/**
 * ARQUIVO: api/chat/enviar_mensagem.php
 * PAPEL: Processar o envio de mensagens e mídias no chat (Privado/Grupo).
 * VERSÃO: 3.6 - Padronização de Notificações (socialbr.lol)
 */

header('Content-Type: application/json; charset=utf-8');

// 1. --- [INICIALIZAÇÃO E SEGURANÇA] ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/database.php';
// IMPORTANTE: Inclui o dicionário de tipos de notificação
require_once __DIR__ . '/../../../config/tipos_notificacoes.php';
require_once __DIR__ . '/../../../src/ChatLogic.php';
require_once __DIR__ . '/../../utils/image_handler.php'; 

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

/**
 * FUNÇÃO AUXILIAR: Sanitizar nomes para ficheiros
 */
function sanitizarParaFicheiro($string) {
    $string = mb_strtolower($string, 'UTF-8');
    $acentos = [
        'a' => ['á','à','â','ã','ä'], 'e' => ['é','è','ê','ë'],
        'i' => ['í','ì','î','ï'], 'o' => ['ó','ò','ô','õ','ö'],
        'u' => ['ú','ù','û','ü'], 'c' => ['ç'], 'n' => ['ñ']
    ];
    foreach ($acentos as $letra => $padrao) {
        $string = str_replace($padrao, $letra, $string);
    }
    $string = preg_replace('/[^a-z0-9\-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

$user_id_logado = $_SESSION['user_id'];

// 2. --- [CAPTURA E VALIDAÇÃO DE DADOS] ---
$conversa_id    = (int)($_POST['conversa_id'] ?? 0);
$mensagem_texto = isset($_POST['mensagem']) ? trim($_POST['mensagem']) : ''; 
$token_recebido = $_POST['token'] ?? '';
$tipo_midia     = 'texto'; 

// Validação CSRF
if (empty($token_recebido) || $token_recebido !== ($_SESSION['token'] ?? '')) {
    echo json_encode(['sucesso' => false, 'erro' => 'Falha na validação de segurança (Token Inválido).']);
    exit;
}

if ($conversa_id <= 0) {
    echo json_encode(['sucesso' => false, 'erro' => 'A conversa de destino não é válida.']);
    exit;
}

// 3. --- [OBTER DADOS DO USUÁRIO E TIPO DE CONVERSA] ---
$sql_info = "SELECT u.nome, c.tipo as tipo_conversa 
             FROM Usuarios u, chat_conversas c 
             WHERE u.id = ? AND c.id = ? LIMIT 1";
$stmt_info = $conn->prepare($sql_info);
$stmt_info->bind_param("ii", $user_id_logado, $conversa_id);
$stmt_info->execute();
$info_res = $stmt_info->get_result()->fetch_assoc();
$stmt_info->close();

$nome_user_limpo = sanitizarParaFicheiro($info_res['nome'] ?? 'usuario');
$tipo_conversa   = ($info_res['tipo_conversa'] === 'grupo') ? 'grupo' : 'privado';

// Verificação de Bloqueio (Apenas para chats privados)
$sql_dest = "SELECT usuario_id FROM chat_participantes WHERE conversa_id = ? AND usuario_id != ? LIMIT 1";
$stmt_dest = $conn->prepare($sql_dest);
$stmt_dest->bind_param("ii", $conversa_id, $user_id_logado);
$stmt_dest->execute();
$res_dest = $stmt_dest->get_result()->fetch_assoc();
$destinatario_id = $res_dest['usuario_id'] ?? 0;
$stmt_dest->close();

if ($destinatario_id > 0 && ChatLogic::isUserBlocked($conn, $user_id_logado, $destinatario_id)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não é possível enviar mensagens para este usuário.']);
    exit;
}

// 4. --- [PROCESSAMENTO DE UPLOAD (ESTRUTURA /MIDIAS/)] ---
$media_url = null;
$arquivo_upload = null;
$campo_origem = '';
$data_hora = date('Y-m-d_H-i-s');

$campos_possiveis = ['midia_audio', 'midia_foto', 'midia_video', 'midia']; 

foreach ($campos_possiveis as $campo) {
    if (isset($_FILES[$campo]) && $_FILES[$campo]['error'] === UPLOAD_ERR_OK) {
        $arquivo_upload = $_FILES[$campo];
        $campo_origem = $campo;
        break;
    }
}

if ($arquivo_upload) {
    $extensao = strtolower(pathinfo($arquivo_upload['name'], PATHINFO_EXTENSION));
    $nome_base = "{$user_id_logado}_{$nome_user_limpo}_{$data_hora}_{$tipo_conversa}";
    
    if ($campo_origem === 'midia_audio' || in_array($extensao, ['mp3', 'ogg', 'wav', 'm4a', 'webm'])) {
        $tipo_midia = 'audio';
        $subpasta = 'audios';
        $caminho_fisico = __DIR__ . "/../../midias/chat/{$subpasta}/" . $nome_base . "." . $extensao;
        if (move_uploaded_file($arquivo_upload['tmp_name'], $caminho_fisico)) {
            $media_url = "midias/chat/{$subpasta}/" . $nome_base . "." . $extensao;
        }

    } elseif ($campo_origem === 'midia_foto' || in_array($extensao, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        $tipo_midia = 'foto';
        $subpasta = 'fotos';
        $caminho_fisico = __DIR__ . "/../../midias/chat/{$subpasta}/" . $nome_base . ".webp";
        if (process_and_save_image($arquivo_upload['tmp_name'], $caminho_fisico, 'resize_to_width', 1080)) {
            $media_url = "midias/chat/{$subpasta}/" . $nome_base . ".webp";
        }

    } elseif ($campo_origem === 'midia_video' || in_array($extensao, ['mp4', 'mov', 'avi', 'webm'])) {
        $tipo_midia = 'video';
        $subpasta = 'videos';
        $caminho_fisico = __DIR__ . "/../../midias/chat/{$subpasta}/" . $nome_base . "." . $extensao;
        if (move_uploaded_file($arquivo_upload['tmp_name'], $caminho_fisico)) {
            $media_url = "midias/chat/{$subpasta}/" . $nome_base . "." . $extensao;
        }
    } else {
        echo json_encode(['sucesso' => false, 'erro' => 'Formato de arquivo não permitido.']);
        exit;
    }

    if (!$media_url) {
        echo json_encode(['sucesso' => false, 'erro' => 'Erro ao processar o upload no servidor.']);
        exit;
    }
}

if ($mensagem_texto === '' && $media_url === null) {
    echo json_encode(['sucesso' => false, 'erro' => 'Mensagem vazia.']);
    exit;
}

// 5. --- [PERSISTÊNCIA E NOTIFICAÇÃO] ---
$sucesso = ChatLogic::sendMessage($conn, $user_id_logado, $conversa_id, $mensagem_texto, $tipo_midia, $media_url);

if ($sucesso) {
    // Lógica de Notificação Toast (Respeitando Silêncio)
    if ($destinatario_id > 0) {
        $sql_silencio = "SELECT silenciada FROM chat_participantes WHERE conversa_id = ? AND usuario_id = ?";
        $stmt_silencio = $conn->prepare($sql_silencio);
        $stmt_silencio->bind_param("ii", $conversa_id, $destinatario_id);
        $stmt_silencio->execute();
        $is_silenciada = $stmt_silencio->get_result()->fetch_assoc()['silenciada'] ?? 0;
        $stmt_silencio->close();

        if (!$is_silenciada) {
            // USANDO A CONSTANTE PADRONIZADA:
            $tipo_notif = NOTIF_MENSAGEM_PRIVADA;
            $sql_notif = "INSERT INTO notificacoes (usuario_id, remetente_id, tipo, id_referencia, lida, data_criacao) VALUES (?, ?, ?, ?, 0, NOW())";
            $stmt_n = $conn->prepare($sql_notif);
            // Parâmetros: [recebe], [envia], [tipo], [id_conversa] -> iisi
            $stmt_n->bind_param("iisi", $destinatario_id, $user_id_logado, $tipo_notif, $conversa_id);
            $stmt_n->execute();
            $stmt_n->close();
        }
    }

    echo json_encode([
        'sucesso'   => true, 
        'media_url' => $media_url, 
        'tipo'      => $tipo_midia,
        'mensagem'  => 'Mensagem enviada.'
    ]);
} else {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao registrar mensagem.']);
}