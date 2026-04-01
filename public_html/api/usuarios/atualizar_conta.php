<?php
session_start();

/**
 * API: Atualizar Dados da Conta (V66.0 - Trava de Confiança & Reset de Confirmado)
 * Responsável pela alteração de E-mail, Nome de Usuário e Senha.
 * VERSÃO: V66.0 (socialbr.lol)
 */

// Resposta padrão em JSON para o JavaScript
header('Content-Type: application/json');

// 1. Verificações de Segurança e Sessão
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Acesso negado. Você precisa estar logado.']);
    exit();
}

require_once __DIR__ . '/../../../config/database.php';

// 2. Validação do Token CSRF
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token de segurança inválido. Por favor, recarregue a página.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// 3. Pega e higieniza os dados do formulário
$email                = trim($_POST['email'] ?? '');
$nome_de_usuario      = trim($_POST['nome_de_usuario'] ?? '');
$senha_atual          = $_POST['senha_atual'] ?? '';
$nova_senha           = $_POST['nova_senha'] ?? '';
$confirmar_nova_senha = $_POST['confirmar_nova_senha'] ?? '';

// Iniciamos uma Transação para garantir integridade atômica
$conn->begin_transaction();

try {
    // 4. Busca dados atuais ANTES da alteração para comparação de integridade
    $sql_current = "SELECT email, senha_hash FROM Usuarios WHERE id = ?";
    $stmt_current = $conn->prepare($sql_current);
    $stmt_current->bind_param("i", $user_id);
    $stmt_current->execute();
    $current_user_data = $stmt_current->get_result()->fetch_assoc();
    
    if (!$current_user_data) {
        throw new Exception("Utilizador não encontrado.");
    }

    // 5. Verificação de disponibilidade (E-mail e Username)
    $sql_check = "SELECT id FROM Usuarios WHERE (email = ? OR nome_de_usuario = ?) AND id != ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ssi", $email, $nome_de_usuario, $user_id);
    $stmt_check->execute();
    
    if ($stmt_check->get_result()->num_rows > 0) {
        throw new Exception("O e-mail ou nome de usuário já está em uso por outra conta.");
    }

    // 6. Lógica de Validação de Senha (SE o usuário estiver tentando mudar a senha)
    $password_change = false;
    if (!empty($nova_senha)) {
        if (empty($senha_atual) || empty($confirmar_nova_senha)) {
            throw new Exception("Para alterar a senha, deve fornecer a senha atual e a confirmação.");
        }
        if ($nova_senha !== $confirmar_nova_senha) {
            throw new Exception("A nova senha e a confirmação não coincidem.");
        }
        if (strlen($nova_senha) < 6) {
            throw new Exception("A nova senha deve ter no mínimo 6 caracteres.");
        }

        if (!password_verify($senha_atual, $current_user_data['senha_hash'])) {
            throw new Exception("A sua senha atual está incorreta.");
        }
        $password_change = true;
    }

    // 7. LÓGICA DE RESET DE CONFIRMAÇÃO (Trava de Confiança)
    // Se o e-mail enviado for diferente do atual, resetamos o status de confirmação.
    $reset_confirmacao = false;
    if ($email !== $current_user_data['email']) {
        $reset_confirmacao = true;
    }

    // 8. Executa a atualização dos dados básicos
    if ($reset_confirmacao) {
        // Se mudou o e-mail, forçamos email_verificado = 0
        $sql_update_info = "UPDATE Usuarios SET email = ?, nome_de_usuario = ?, email_verificado = 0 WHERE id = ?";
        $stmt_info = $conn->prepare($sql_update_info);
        $stmt_info->bind_param("ssi", $email, $nome_de_usuario, $user_id);
    } else {
        $sql_update_info = "UPDATE Usuarios SET email = ?, nome_de_usuario = ? WHERE id = ?";
        $stmt_info = $conn->prepare($sql_update_info);
        $stmt_info->bind_param("ssi", $email, $nome_de_usuario, $user_id);
    }
    
    if (!$stmt_info->execute()) {
        throw new Exception("Ocorreu um erro ao atualizar os dados da conta.");
    }

    // 9. Executa a atualização da senha (se validada no passo 6)
    if ($password_change) {
        $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $sql_update_pass = "UPDATE Usuarios SET senha_hash = ? WHERE id = ?";
        $stmt_pass = $conn->prepare($sql_update_pass);
        $stmt_pass->bind_param("si", $nova_senha_hash, $user_id);
        
        if (!$stmt_pass->execute()) {
            throw new Exception("Erro ao processar a nova senha.");
        }
    }

    // 10. Se chegou aqui sem erros, confirma todas as alterações (COMMIT)
    $conn->commit();

    $sucesso_msg = 'As informações da sua conta foram atualizadas com sucesso!';
    if ($reset_confirmacao) {
        $sucesso_msg .= ' Como alterou o seu e-mail, será necessário realizar uma nova confirmação para liberar todos os recursos.';
    }

    echo json_encode([
        'success' => true, 
        'message' => $sucesso_msg,
        'email_reset' => $reset_confirmacao // Sinalizador para o Frontend (configuracoes.js)
    ]);

} catch (Exception $e) {
    // Se qualquer erro ocorreu, desfaz tudo (ROLLBACK)
    $conn->rollback();
    
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>