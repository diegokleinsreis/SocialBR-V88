<?php
/**
 * api/admin/atualizar_configuracoes.php
 * PAPEL: Atualizar chaves de configuração do sistema.
 * VERSÃO: 4.0 (Log de Auditoria Detalhado - socialbr.lol)
 */

// 1. GUARITA DE SEGURANÇA E CONEXÃO
require_once __DIR__ . '/../../admin/admin_auth.php'; // Garante que só o admin pode executar
// $conn e $config['base_path'] já estão disponíveis aqui

// 2. VERIFICA SE OS DADOS VIERAM DO FORMULÁRIO (MÉTODO POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- BLOCO DE SEGURANÇA: VERIFICAÇÃO CSRF ---
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        http_response_code(403); 
        die("Erro de segurança: Token inválido. Recarregue a página e tente novamente.");
    }

    // 3. PREPARA A QUERY E A VARIÁVEL DE RASTREAMENTO
    $sql = "UPDATE Configuracoes SET valor = ? WHERE chave = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Erro ao preparar a query: " . $conn->error);
    }

    // Array para guardar o que foi mudado para o log
    $alteracoes = [];

    // 4. LÓGICA PARA INTERRUPTORES (SWITCHES)
    $switch_keys = ['modo_manutencao', 'permite_cadastro', 'modo_dev', 'modo_censura'];
    
    foreach ($switch_keys as $chave) {
        $valor = isset($_POST[$chave]) ? '1' : '0';
        
        $stmt->bind_param("ss", $valor, $chave);
        if ($stmt->execute()) {
            // Registra a mudança para o log detalhado
            $status_texto = ($valor === '1') ? 'ATIVADO' : 'DESATIVADO';
            $alteracoes[] = strtoupper($chave) . ": " . $status_texto;
        }
    }

    // 5. LÓGICA PARA CAMPOS DE TEXTO RESTANTES
    foreach ($_POST as $chave => $valor) {
        
        // Pula os campos que já tratámos ou campos de controle
        if (in_array($chave, $switch_keys) || $chave === 'csrf_token') {
            continue;
        }

        $stmt->bind_param("ss", $valor, $chave);
        
        if ($stmt->execute()) {
            // Registra o novo valor para o log
            $alteracoes[] = strtoupper($chave) . ": '" . $valor . "'";
        }
    }

    // --- REGISTRO DE AUDITORIA DETALHADO ---
    if (!empty($alteracoes)) {
        // Junta todas as mudanças em uma única frase legível
        $detalhe_final = "Configurações alteradas: " . implode(" | ", $alteracoes);
        
        // O Alvo continua 0 (Sistema), mas o detalhe agora diz TUDO
        admin_log('atualizar_configuracoes', 'sistema', 0, $detalhe_final);
    }

    // 6. FECHA CONEXÕES
    $stmt->close();
    $conn->close();

    // 7. REDIRECIONA
    header("Location: " . $config['base_path'] . "admin/admin_configuracoes.php?success=1"); 
    exit();

} else {
    die("Acesso inválido.");
}