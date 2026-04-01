<?php
/**
 * api/admin/salvar_anotacoes.php
 * PAPEL: Persistir o conteúdo do bloco de notas no banco de dados com auditoria.
 * VERSÃO: 2.0 (Integração com Logs de Auditoria - socialbr.lol)
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

// 1. GUARITA DE SEGURANÇA (Apenas administradores)
// Reutiliza a lógica de autenticação do admin
require_once __DIR__ . '/../../admin/admin_auth.php'; 
// Aqui o $conn e $config já estão disponíveis

// 2. VERIFICAÇÃO DO MÉTODO E TOKEN CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método não permitido.']);
    exit();
}

if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'error' => 'Token de segurança inválido.']);
    exit();
}

// 3. CAPTURA DOS DADOS
$conteudo = isset($_POST['conteudo']) ? $_POST['conteudo'] : '';

try {
    // 4. ATUALIZAÇÃO RECURSIVA (Sempre no ID 1 que criamos no SQL)
    $sql = "UPDATE Anotacoes_Admin SET conteudo_texto = ?, data_atualizacao = NOW() WHERE id = 1";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Erro ao preparar a query: " . $conn->error);
    }

    $stmt->bind_param("s", $conteudo);
    
    if ($stmt->execute()) {
        
        // --- REGISTO DE AUDITORIA CLARO ---
        // Registamos que as anotações do sistema foram alteradas
        $detalhe_log = "O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).";
        admin_log('atualizar_anotacoes', 'sistema', 1, $detalhe_log);

        echo json_encode([
            'success' => true, 
            'message' => 'Anotações guardadas com sucesso!',
            'last_update' => date('H:i:s')
        ]);
    } else {
        throw new Exception("Erro ao executar a gravação.");
    }

    $stmt->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
} finally {
    $conn->close();
}