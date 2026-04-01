<?php
// public_html/api/postagens/compartilhar_post.php
// VERSÃO V101.1 - Padronização de Tipos de Notificação

header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- INCLUDES ---
require_once '../../../config/database.php';
require_once '../../../config/tipos_notificacoes.php'; // Dicionário de tipos
require_once '../../../src/NotificationLogic.php'; 

// --- RESPOSTA PADRÃO ---
$response = ['success' => false, 'erro' => 'Acesso negado ou dados inválidos.', 'compartilhado' => false, 'novo_total' => 0];

try {
    // 1. O módulo está ativo?
    if (!isset($config['MODULO_COMPARTILHAR_ATIVO']) || $config['MODULO_COMPARTILHAR_ATIVO'] !== '1') {
        throw new Exception('O módulo de compartilhamento está desativado.');
    }

    // 2. O usuário está logado?
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Usuário não autenticado. Faça login para compartilhar.');
    }
    $usuario_id = (int)$_SESSION['user_id'];

    // 3. Verificação CSRF
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        throw new Exception('Token de segurança inválido. Tente recarregar a página.');
    }

    // 4. Dados enviados?
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['post_id'])) {
        throw new Exception('Requisição inválida.');
    }
    
    $post_id_original = (int)$_POST['post_id'];
    $conteudo_texto = trim($_POST['conteudo_texto'] ?? ''); 

    if ($post_id_original <= 0) {
        throw new Exception('ID de postagem inválido.');
    }

    $conn->begin_transaction();

    // 5. Verifica existência do post original e identifica o autor
    $stmt_check_original = $conn->prepare("SELECT id, id_usuario FROM Postagens WHERE id = ? AND status = 'ativo'");
    $stmt_check_original->bind_param("i", $post_id_original);
    $stmt_check_original->execute();
    $result_original = $stmt_check_original->get_result();
    $post_original = $result_original->fetch_assoc();
    $stmt_check_original->close();

    if (!$post_original) {
        throw new Exception('A postagem original não existe ou foi removida.');
    }
    $autor_original_id = (int)$post_original['id_usuario'];

    // 6. Busca privacidade padrão
    $stmt_priv = $conn->prepare("SELECT privacidade_posts_padrao FROM Usuarios WHERE id = ?");
    $stmt_priv->bind_param("i", $usuario_id);
    $stmt_priv->execute();
    $priv_data = $stmt_priv->get_result()->fetch_assoc();
    $privacidade_padrao = ($priv_data && $priv_data['privacidade_posts_padrao']) ? $priv_data['privacidade_posts_padrao'] : 'publico';
    $stmt_priv->close();
    
    // 7. Cria a nova postagem de compartilhamento
    $stmt_insert = $conn->prepare(
        "INSERT INTO Postagens (id_usuario, post_original_id, conteudo_texto, data_postagem, status, privacidade) 
         VALUES (?, ?, ?, NOW(), 'ativo', ?)"
    );
    $stmt_insert->bind_param("iiss", $usuario_id, $post_id_original, $conteudo_texto, $privacidade_padrao);
    $stmt_insert->execute();
    $novo_post_id = $conn->insert_id; // ID da nova postagem (o compartilhamento)
    $stmt_insert->close();

    // 8. Incrementa o contador no post ORIGINAL
    $stmt_update_count = $conn->prepare("UPDATE Postagens SET contador_compartilhamentos = contador_compartilhamentos + 1 WHERE id = ?");
    $stmt_update_count->bind_param("i", $post_id_original);
    $stmt_update_count->execute();
    $stmt_update_count->close();
    
    // --- [INÍCIO DA LÓGICA DE NOTIFICAÇÃO PADRONIZADA] ---
    // Notifica o autor original apenas se ele não for a mesma pessoa que está compartilhando
    if ($usuario_id !== $autor_original_id) {
        // Agora usamos a constante que aponta para 'compartilhamento_post'
        $tipo_notif = NOTIF_COMPARTILHAMENTO_POST; 
        
        $stmt_notif = $conn->prepare(
            "INSERT INTO notificacoes (usuario_id, remetente_id, tipo, id_referencia, lida, data_criacao) 
             VALUES (?, ?, ?, ?, 0, NOW())"
        );
        // Parâmetros: [recebe], [envia], [tipo], [novo post]
        // Alterado para 'iisi' para incluir a string do tipo corretamente
        $stmt_notif->bind_param("iisi", $autor_original_id, $usuario_id, $tipo_notif, $novo_post_id);
        $stmt_notif->execute();
        $stmt_notif->close();
    }
    // --- [FIM DA LÓGICA DE NOTIFICAÇÃO] ---

    // 9. Busca o novo total para o feedback visual no front-end
    $stmt_get_total = $conn->prepare("SELECT contador_compartilhamentos FROM Postagens WHERE id = ?");
    $stmt_get_total->bind_param("i", $post_id_original);
    $stmt_get_total->execute();
    $result_total = $stmt_get_total->get_result();
    $novo_total = (int)$result_total->fetch_assoc()['contador_compartilhamentos'];
    $stmt_get_total->close();

    $conn->commit();

    $response = [
        'success' => true,
        'compartilhado' => true,
        'novo_total' => $novo_total,
        'message' => 'Postagem compartilhada com sucesso!',
        'erro' => ''
    ];

} catch (Exception $e) {
    if ($conn) $conn->rollback();
    http_response_code(500);
    $response['erro'] = $e->getMessage();
}

if ($conn) $conn->close();
echo json_encode($response);
exit;