<?php
/**
 * api/comentarios/criar_comentario.php
 * Endpoint: Criação de comentários (Unificado para AJAX/SweetAlert2).
 * PAPEL: Processar comentários do Feed e disparar alertas de identidade pendente via JSON.
 * VERSÃO: V3.1 - Padronização de Tipos de Notificação (socialbr.lol)
 */

// Inicia a sessão para pegar o ID do usuário logado
session_start();

// [VITAL] Define o cabeçalho JSON para que o SweetAlert2 consiga ler a resposta
header('Content-Type: application/json; charset=utf-8');

/**
 * Função auxiliar para responder ao Frontend.
 */
function responder_json($sucesso, $msg, $tipo_erro = 'erro', $extras = []) {
    echo json_encode(array_merge([
        'success' => $sucesso,
        'error'   => $tipo_erro,
        'message' => $msg
    ], $extras));
    exit;
}

// 1. Verificação de Sessão
if (!isset($_SESSION['user_id'])) {
    responder_json(false, "Sessão expirada. Por favor, faça o login.", 'sessao_expirada');
}

// Inclui a conexão com o banco de dados
require_once __DIR__ . '/../../../config/database.php';
// IMPORTANTE: Inclui o dicionário de tipos de notificação
require_once __DIR__ . '/../../../config/tipos_notificacoes.php';

// 2. Verificação de Método e Segurança CSRF
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    responder_json(false, "Método de requisição inválido.");
}

if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    responder_json(false, "Token de segurança inválido. Recarregue a página.", 'csrf_invalido');
}

// Pega os dados enviados
$id_usuario = $_SESSION['user_id'];
$id_postagem = isset($_POST['id_postagem']) ? (int)$_POST['id_postagem'] : 0;
$conteudo_texto = trim($_POST['conteudo_texto'] ?? '');
$id_comentario_pai = isset($_POST['id_comentario_pai']) && !empty($_POST['id_comentario_pai']) ? (int)$_POST['id_comentario_pai'] : null;

// 3. BLINDAGEM DE IDENTIDADE
$sql_v = "SELECT email_verificado FROM Usuarios WHERE id = ? LIMIT 1";
$stmt_v = $conn->prepare($sql_v);
$stmt_v->bind_param("i", $id_usuario);
$stmt_v->execute();
$res_v = $stmt_v->get_result()->fetch_assoc();
$is_confirmado = ($res_v && (int)$res_v['email_verificado'] === 1);
$stmt_v->close();

if (!$is_confirmado) {
    responder_json(
        false, 
        "Ação Bloqueada: Confirme o seu e-mail para poder postar comentários e interagir na rede.", 
        'verificacao_pendente'
    );
}

// 4. Validação de Conteúdo
if (empty($conteudo_texto) || $id_postagem <= 0) {
    responder_json(false, "O comentário não pode estar vazio e precisa de uma postagem válida.");
}

// 5. Inserção no Banco de Dados (Tabela Comentarios)
$sql = "INSERT INTO Comentarios (id_postagem, id_usuario, id_comentario_pai, conteudo_texto) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiis", $id_postagem, $id_usuario, $id_comentario_pai, $conteudo_texto);

if ($stmt->execute()) {
    
    // --- [LÓGICA DE NOTIFICAÇÃO PADRONIZADA] ---
    // Pegamos o autor do post original
    $sql_post_autor = "SELECT id_usuario FROM Postagens WHERE id = ?";
    $stmt_p = $conn->prepare($sql_post_autor);
    $stmt_p->bind_param("i", $id_postagem);
    $stmt_p->execute();
    $res_p = $stmt_p->get_result()->fetch_assoc();
    
    if ($res_p && (int)$res_p['id_usuario'] !== $id_usuario) {
        $post_autor_id = $res_p['id_usuario'];
        
        // USANDO A CONSTANTE: Agora grava 'comentario_post'
        $tipo_notif = NOTIF_COMENTARIO_POST; 
        
        $sql_n = "INSERT INTO notificacoes (usuario_id, remetente_id, tipo, id_referencia) VALUES (?, ?, ?, ?)";
        $stmt_n = $conn->prepare($sql_n);
        $stmt_n->bind_param("iisi", $post_autor_id, $id_usuario, $tipo_notif, $id_postagem);
        $stmt_n->execute();
        $stmt_n->close();
    }
    $stmt_p->close();

    // Retorna sucesso (sem redirecionamento forçado para permitir AJAX atualizar o modal)
    responder_json(true, "Comentário enviado!");

} else {
    responder_json(false, "Erro técnico ao salvar o comentário.");
}

$stmt->close();
$conn->close();