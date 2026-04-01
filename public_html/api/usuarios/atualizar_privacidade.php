<?php
session_start();

/**
 * API: Atualizar Configurações de Privacidade (V65.9 - Seguro)
 * Controla a visibilidade do perfil e a privacidade retroativa de posts.
 */

// Resposta padrão em JSON para o JavaScript
header('Content-Type: application/json');

// 1. Verificações de Segurança e Sessão
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Acesso negado. Você precisa estar logado.']);
    exit();
}

require_once __DIR__ . '/../../../config/database.php';

// 2. Validação do Token CSRF (O selo de segurança da View)
// Verifica se o token existe e se é válido (usando a função do database.php)
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token de segurança inválido. Tente recarregar a página.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// 3. Processa e higieniza os valores do formulário
$perfil_privado           = isset($_POST['perfil_privado']) ? 1 : 0;
$privacidade_amigos       = $_POST['privacidade_amigos'] ?? 'amigos';
$privacidade_posts_padrao = $_POST['privacidade_posts_padrao'] ?? 'publico';

// --- LÓGICA INTELIGENTE (TEMPLATE WHITE-LABEL) ---
// Se o utilizador define o perfil como PRIVADO, 
// forçamos a privacidade padrão de novos posts para 'amigos' por segurança.
if ($perfil_privado == 1) {
    $privacidade_posts_padrao = 'amigos';
}

// Validação rigorosa de valores permitidos (Segurança de integridade)
if (!in_array($privacidade_amigos, ['todos', 'amigos', 'ninguem'])) {
    $privacidade_amigos = 'amigos';
}
if (!in_array($privacidade_posts_padrao, ['publico', 'amigos'])) {
    $privacidade_posts_padrao = 'publico';
}

try {
    // 4. Atualiza as configurações na tabela de Usuários
    $sql = "UPDATE Usuarios SET 
                perfil_privado = ?, 
                privacidade_amigos = ?, 
                privacidade_posts_padrao = ? 
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $perfil_privado, $privacidade_amigos, $privacidade_posts_padrao, $user_id);

    if (!$stmt->execute()) {
        throw new Exception("Ocorreu um erro ao atualizar a sua configuração de privacidade.");
    }
    $stmt->close();

    // 5. MIGRAÇÃO RETROATIVA (Dica de Ouro de Segurança)
    // Se o utilizador ACABOU DE TORNAR O PERFIL PRIVADO,
    // transformamos todos os seus posts 'públicos' antigos em posts para 'amigos'.
    if ($perfil_privado == 1) {
        $sql_migrate = "UPDATE Postagens SET privacidade = 'amigos' 
                        WHERE id_usuario = ? AND privacidade = 'publico'";
        
        $stmt_migrate = $conn->prepare($sql_migrate);
        $stmt_migrate->bind_param("i", $user_id);
        $stmt_migrate->execute();
        $stmt_migrate->close();
    }

    // Resposta final de sucesso
    echo json_encode([
        'success' => true, 
        'message' => 'Configurações de privacidade atualizadas com sucesso!'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>