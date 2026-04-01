<?php
// Inicia a sessão.
session_start();

// Puxa a conexão com o banco de dados.
require_once __DIR__ . '/../../../config/database.php';
// $config['base_path'] já está disponível

// Verifica se os dados foram enviados via POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email_ou_usuario = $_POST['email_ou_usuario'];
    $senha = $_POST['senha'];

    if (empty($email_ou_usuario) || empty($senha)) {
        $_SESSION['login_error'] = "Preencha todos os campos.";
        // Redirecionamento de Erro (Corrigido)
        header("Location: " . $config['base_path'] . "login"); // <-- CORRIGIDO
        exit();
    }

    $sql = "SELECT id, senha_hash, role, status FROM Usuarios WHERE email = ? OR nome_de_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email_ou_usuario, $email_ou_usuario);
    
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $usuario = $result->fetch_assoc();
        
        if (password_verify($senha, $usuario['senha_hash'])) {
            
            if ($usuario['status'] === 'ativo') {
                
                session_regenerate_id(true);

                $_SESSION['user_id'] = $usuario['id'];
                $_SESSION['user_role'] = $usuario['role'];
                
                // --- LÓGICA DE LOG DE LOGIN (não precisa de alteração) ---
                try {
                    $id_usuario_logado = $usuario['id'];
                    $ip_usuario = $_SERVER['REMOTE_ADDR'] ?? 'Desconhecido';
                    $sql_log = "INSERT INTO Logs_Login (id_usuario, ip_usuario) VALUES (?, ?)";
                    $stmt_log = $conn->prepare($sql_log);
                    $stmt_log->bind_param("is", $id_usuario_logado, $ip_usuario);
                    $stmt_log->execute();
                    $stmt_log->close();
                } catch (Exception $e) {
                    error_log("Falha ao registar Log de Login: " . $e->getMessage());
                }
                // --- FIM DA LÓGICA DE LOG ---

                // --- LÓGICA DE REDIRECIONAMENTO (Corrigida) ---

                // Verifica se há um URL de redirecionamento guardado na sessão
                if (isset($_SESSION['redirect_url']) && !empty($_SESSION['redirect_url'])) {
                    $redirect_url = $_SESSION['redirect_url'];
                    unset($_SESSION['redirect_url']); 
                    
                    // Este redirecionamento JÁ ESTÁ CORRETO, pois $redirect_url contém o /~klscom/
                    header("Location: " . $redirect_url);
                    exit();
                } else {
                    // Se não houver, redireciona para o feed (comportamento padrão)
                    // Usa a nova rota: /~klscom/feed
                    header("Location: " . $config['base_path'] . "feed"); // <-- CORRIGIDO
                    exit();
                }

                // --- FIM DA MODIFICAÇÃO ---

            } else {
                $_SESSION['login_error'] = "Sua conta está suspensa. Entre em contato com o suporte.";
                // Redirecionamento de Erro (Corrigido)
                header("Location: " . $config['base_path'] . "login"); // <-- CORRIGIDO
                exit();
            }

        } else {
            $_SESSION['login_error'] = "E-mail/usuário ou senha inválidos.";
            // Redirecionamento de Erro (Corrigido)
            header("Location: " . $config['base_path'] . "login"); // <-- CORRIGIDO
            exit();
        }
    } else {
        $_SESSION['login_error'] = "E-mail/usuário ou senha inválidos.";
        // Redirecionamento de Erro (Corrigido)
        header("Location: " . $config['base_path'] . "login"); // <-- CORRIGIDO
        exit();
    }

    $stmt->close();
    $conn->close();

} else {
    echo "Acesso inválido.";
}
?>