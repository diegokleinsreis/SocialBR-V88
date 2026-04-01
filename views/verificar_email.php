<?php
/**
 * ARQUIVO: views/verificar_email.php
 * PAPEL: Orquestrador de Verificação com Super-Sensor de Parâmetros.
 * VERSÃO: 2.0 - Blindagem contra perda de Query String (socialbr.lol)
 */

require_once __DIR__ . '/../src/UserLogic.php';

// --- [SUPER-SENSOR DE TOKEN] ---
// Tenta pegar do $_GET padrão, se falhar, extrai manualmente da URL do servidor.
$token = $_GET['token'] ?? '';

if (empty($token)) {
    // Se o servidor "limpou" o $_GET, buscamos direto na URI bruta
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($uri, 'token=') !== false) {
        $parts = explode('token=', $uri);
        $token = explode('&', $parts[1])[0];
    }
}

$status_verificacao = 'processando';
$mensagem_feedback = '';

// 1. --- [VALIDAÇÃO DE PRESENÇA] ---
if (empty($token)) {
    $status_verificacao = 'erro';
    $mensagem_feedback = 'O link de verificação parece estar incompleto ou inválido.';
} else {
    // 2. --- [CONSULTA NO BANCO] ---
    $sql_busca = "SELECT id, nome FROM Usuarios WHERE token_verificacao = ? LIMIT 1";
    $stmt = $conn->prepare($sql_busca);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();

    if ($resultado) {
        $user_id = $resultado['id'];
        $nome_user = htmlspecialchars($resultado['nome']);

        // 3. --- [ATIVAÇÃO ATÓMICA] ---
        $sql_update = "UPDATE Usuarios SET email_verificado = 1, token_verificacao = NULL WHERE id = ?";
        $stmt_up = $conn->prepare($sql_update);
        $stmt_up->bind_param("i", $user_id);
        
        if ($stmt_up->execute()) {
            $status_verificacao = 'sucesso';
            $mensagem_feedback = "Parabéns, <strong>{$nome_user}</strong>! Sua conta foi verificada com sucesso.";
        } else {
            $status_verificacao = 'erro';
            $mensagem_feedback = 'Erro técnico ao ativar conta. Tente novamente.';
        }
        $stmt_up->close();
    } else {
        $status_verificacao = 'erro';
        $mensagem_feedback = 'Este link de verificação é inválido ou já foi utilizado.';
    }
    $stmt->close();
}

include_once __DIR__ . '/../templates/header.php';
?>

<style>
    .verify-container { min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
    .verify-card { background: #fff; max-width: 500px; width: 100%; padding: 40px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); text-align: center; border-top: 5px solid #0C2D54; }
    .verify-icon { font-size: 60px; margin-bottom: 20px; }
    .verify-icon.success { color: #2ecc71; }
    .verify-icon.error { color: #e74c3c; }
    .verify-card h2 { color: #0C2D54; margin-bottom: 15px; font-weight: 800; }
    .verify-card p { color: #666; line-height: 1.6; margin-bottom: 30px; }
    .btn-verify-action { background-color: #0C2D54; color: #fff !important; padding: 12px 30px; border-radius: 10px; text-decoration: none; font-weight: bold; display: inline-block; transition: 0.2s; }
</style>

<main class="verify-container">
    <div class="verify-card">
        <?php if ($status_verificacao === 'sucesso'): ?>
            <div class="verify-icon success"><i class="fas fa-check-circle"></i></div>
            <h2>Conta Verificada!</h2>
            <p><?php echo $mensagem_feedback; ?></p>
            <a href="<?php echo $config['base_url']; ?>feed" class="btn-verify-action">Ir para o Feed</a>
        <?php else: ?>
            <div class="verify-icon error"><i class="fas fa-exclamation-triangle"></i></div>
            <h2>Ops! Verificação Falhou</h2>
            <p><?php echo $mensagem_feedback; ?></p>
            <a href="<?php echo $config['base_url']; ?>suporte" class="btn-verify-action">Contatar Suporte</a>
        <?php endif; ?>
    </div>
</main>

<?php include_once __DIR__ . '/../templates/footer.php'; ?>