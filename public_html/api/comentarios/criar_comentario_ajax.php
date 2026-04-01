<?php
/**
 * api/comentarios/criar_comentario_ajax.php
 * Endpoint: Criação de comentários via AJAX.
 * PAPEL: Processar novas interações e validar a confirmação de identidade.
 * VERSÃO: V2.1 - Padronização de Tipos de Notificação (socialbr.lol)
 */

session_start();

// Define o cabeçalho da resposta como JSON
header('Content-Type: application/json');

// Função para padronizar as respostas de erro
function error_response($message, $code = 400, $error_type = 'erro_geral') {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $error_type, 'message' => $message]);
    exit();
}

// 1. Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    error_response("Acesso negado. Por favor, faça o login.", 403, 'sessao_expirada');
}

// --- IMPORTAÇÕES NECESSÁRIAS ---
require_once __DIR__ . '/../../../config/database.php';
// IMPORTANTE: Inclui o dicionário de tipos de notificação para manter o padrão
require_once __DIR__ . '/../../../config/tipos_notificacoes.php';

// 2. Verifica se a requisição foi feita usando o método POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    error_response("Método de requisição inválido.");
}

// --- BLOCO DE SEGURANÇA: VERIFICAÇÃO CSRF ---
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    error_response("Token de segurança inválido. Tente recarregar a página.", 403, 'csrf_invalido');
}

$id_usuario = (int)$_SESSION['user_id'];

// 🛡️ BLINDAGEM DE IDENTIDADE: VERIFICAÇÃO DE E-MAIL
$sql_v = "SELECT email_verificado FROM Usuarios WHERE id = ? LIMIT 1";
$stmt_v = $conn->prepare($sql_v);
$stmt_v->bind_param("i", $id_usuario);
$stmt_v->execute();
$res_v = $stmt_v->get_result()->fetch_assoc();
$is_confirmado = ($res_v && (int)$res_v['email_verificado'] === 1);
$stmt_v->close();

if (!$is_confirmado) {
    error_response(
        "Ação Bloqueada: Confirme o seu e-mail para poder participar nas conversas da rede.", 
        403, 
        'verificacao_pendente'
    );
}

// 3. Captura e higienização de dados
$conteudo_texto = trim($_POST['conteudo_texto'] ?? '');
$id_postagem = isset($_POST['id_postagem']) ? (int)$_POST['id_postagem'] : 0;
$id_comentario_pai = isset($_POST['id_comentario_pai']) && !empty($_POST['id_comentario_pai']) ? (int)$_POST['id_comentario_pai'] : null;

// Validações básicas
if (empty($conteudo_texto) || $id_postagem <= 0) {
    error_response("O comentário não pode estar vazio e precisa estar associado a uma postagem válida.");
}

// Inicia uma transação para garantir integridade atômica
$conn->begin_transaction();

try {
    // 1. INSERE O NOVO COMENTÁRIO
    $sql_insert = "INSERT INTO Comentarios (id_postagem, id_usuario, id_comentario_pai, conteudo_texto) VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iiis", $id_postagem, $id_usuario, $id_comentario_pai, $conteudo_texto);

    if (!$stmt_insert->execute()) {
        throw new Exception("Erro ao salvar o comentário no banco de dados.");
    }

    $new_comment_id = $conn->insert_id;
    $stmt_insert->close();

    // 2. BUSCA OS DADOS DO COMENTÁRIO PARA RESPOSTA IMEDIATA NA UI
    $sql_select = "SELECT
                        c.id, c.conteudo_texto, c.data_comentario, c.id_comentario_pai,
                        u.id AS autor_id, u.nome, u.sobrenome, u.foto_perfil_url
                   FROM Comentarios AS c
                   JOIN Usuarios AS u ON c.id_usuario = u.id
                   WHERE c.id = ?";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param("i", $new_comment_id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $new_comment_data = $result->fetch_assoc();
    $stmt_select->close();

    if (!$new_comment_data) {
        throw new Exception("Não foi possível recuperar o comentário após a criação.");
    }
    
    // 3. LÓGICA DE NOTIFICAÇÃO PADRONIZADA
    $sql_post_autor = "SELECT id_usuario FROM Postagens WHERE id = ?";
    $stmt_post_autor = $conn->prepare($sql_post_autor);
    $stmt_post_autor->bind_param("i", $id_postagem);
    $stmt_post_autor->execute();
    $post_autor_data = $stmt_post_autor->get_result()->fetch_assoc();
    $stmt_post_autor->close();

    if ($post_autor_data && (int)$post_autor_data['id_usuario'] !== $id_usuario) {
        $post_autor_id = (int)$post_autor_data['id_usuario'];
        
        // USANDO A CONSTANTE: Agora envia 'comentario_post' para o banco
        $tipo_notificacao = NOTIF_COMENTARIO_POST; 
        
        $sql_notificacao = "INSERT INTO notificacoes (usuario_id, remetente_id, tipo, id_referencia) VALUES (?, ?, ?, ?)";
        $stmt_notificacao = $conn->prepare($sql_notificacao);
        // O bind_param iisi está correto (inteiro, inteiro, string, inteiro)
        $stmt_notificacao->bind_param("iisi", $post_autor_id, $id_usuario, $tipo_notificacao, $id_postagem);
        $stmt_notificacao->execute();
        $stmt_notificacao->close();
    }
    
    // Confirma as operações
    $conn->commit();

    echo json_encode(['success' => true, 'comment' => $new_comment_data]);

} catch (Exception $e) {
    $conn->rollback();
    error_response($e->getMessage());
}

$conn->close();