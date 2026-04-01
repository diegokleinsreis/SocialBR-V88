<?php
/**
 * API DE HEARTBEAT (PULSAÇÃO)
 * * Este script é chamado silenciosamente pelo JavaScript a cada X minutos
 * para atualizar o 'ultimo_acesso' do usuário logado.
 * * Propósito: Manter o status "Online" preciso enquanto o usuário 
 * estiver com a página aberta, mesmo sem navegar.
 */

// 1. Carrega a configuração e inicia a sessão
// Usamos o caminho relativo desde /api/usuarios/ para a raiz do projeto (onde está a pasta config)
// (public_html/api/usuarios/ -> public_html/api/ -> public_html/ -> [RAIZ])
require_once __DIR__ . '/../../../config/database.php';

// 2. Verifica se o usuário está logado
if (isset($_SESSION['user_id'])) {
    
    $user_id = (int)$_SESSION['user_id'];

    // 3. Prepara e executa a atualização (a mesma lógica que removemos do database.php)
    $sql_update_acesso = "UPDATE Usuarios SET ultimo_acesso = NOW() WHERE id = ?";
    
    try {
        $stmt_acesso = $conn->prepare($sql_update_acesso);
        if ($stmt_acesso) {
            $stmt_acesso->bind_param("i", $user_id);
            $stmt_acesso->execute();
            $stmt_acesso->close();
        }
    } catch (Exception $e) {
        // Regista o erro, mas não envia output para o cliente
        error_log("Falha no heartbeat de ultimo_acesso: " . $e->getMessage());
    }

}

// 4. Fecha a conexão e termina o script
$conn->close();
exit; // Termina silenciosamente, sem enviar HTML