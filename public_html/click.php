<?php
/**
 * click.php (V107.3 - FINAL)
 * RASTREADOR DE CLIQUES
 * Correção: Caminho da pasta config ajustado (../config/)
 */

// 1. Buffer de Saída: Segura qualquer erro de texto para não quebrar o redirecionamento
ob_start();

// Configuração para produção (esconde erros na tela)
ini_set('display_errors', 0);

// Captura a URL de destino
$url_destino = isset($_GET['u']) ? urldecode($_GET['u']) : '';

// Validação de Segurança Básica
if (empty($url_destino) || !filter_var($url_destino, FILTER_VALIDATE_URL)) {
    // Se a URL for inválida, manda para a home
    header("Location: index.php");
    exit;
}

// 2. TENTATIVA DE REGISTRO (Try/Catch)
try {
    // Inicia sessão apenas se não estiver ativa
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // --- CORREÇÃO DO CAMINHO ---
    // Sai da public_html (../) e entra na config
    $db_file = __DIR__ . '/../config/database.php'; 

    if (file_exists($db_file)) {
        require_once $db_file;

        if (isset($conn) && !$conn->connect_error) {
            $post_id    = isset($_GET['p']) ? (int)$_GET['p'] : 0;
            $usuario_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
            
            // Captura IP e User Agent
            $ip_address = $_SERVER['REMOTE_ADDR'];
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            // Trunca o User Agent para 255 chars para evitar erro no banco
            $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : 'Desconhecido';

            if ($post_id > 0) {
                // Prepara a query
                $sql = "INSERT INTO Links_Cliques (post_id, usuario_id, url_destino, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                
                if ($stmt) {
                    $stmt->bind_param("iisss", $post_id, $usuario_id, $url_destino, $ip_address, $user_agent);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            $conn->close();
        }
    }
} catch (Throwable $e) {
    // Se der erro, não fazemos nada. O redirecionamento acontece abaixo.
    // O usuário não percebe a falha.
}

// 3. REDIRECIONAMENTO FINAL
ob_end_clean(); // Limpa qualquer lixo de erro
header("Location: " . $url_destino);
exit;
?>