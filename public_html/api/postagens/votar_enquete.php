<?php
/**
 * api/postagens/votar_enquete.php
 * API: Votar em Enquete (Versão V9.2 - Padronização de Notificações)
 * SUPORTE: Localização de Path Dinâmica + Cancelar/Trocar Voto + Notificações.
 * VERSÃO: Sincronizada com tipos_notificacoes.php (socialbr.lol)
 */

session_start();
header('Content-Type: application/json');

// 1. VERIFICAÇÃO DE SESSÃO
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acesso negado. Por favor, faça login.']);
    exit;
}

// 2. LOCALIZAÇÃO ROBUSTA DO BANCO E CONFIGURAÇÕES
$db_found = false;
$db_paths = [
    __DIR__ . '/../../../config/database.php',
    __DIR__ . '/../../config/database.php',
    $_SERVER['DOCUMENT_ROOT'] . '/../config/database.php',
    $_SERVER['DOCUMENT_ROOT'] . '/config/database.php'
];

foreach ($db_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        // IMPORTANTE: Tenta carregar o dicionário de tipos na mesma pasta do banco
        $types_path = dirname($path) . '/tipos_notificacoes.php';
        if (file_exists($types_path)) {
            require_once $types_path;
        }
        $db_found = true;
        break;
    }
}

if (!$db_found) {
    echo json_encode(['success' => false, 'error' => 'Erro interno: Configurações não encontradas.']);
    exit();
}

// 3. VALIDAÇÃO DE SEGURANÇA (CSRF)
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token de segurança inválido ou expirado.']);
    exit;
}

$user_id   = (int)$_SESSION['user_id'];
$opcao_id  = isset($_POST['opcao_id']) ? (int)$_POST['opcao_id'] : 0;
$enquete_id = isset($_POST['enquete_id']) ? (int)$_POST['enquete_id'] : 0;

if ($opcao_id <= 0 || $enquete_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Dados de votação incompletos.']);
    exit;
}

try {
    // 4. BUSCA INFORMAÇÕES DA ENQUETE E AUTOR DO POST
    $sql_info = "SELECT p.id AS post_id, p.id_usuario AS autor_id 
                 FROM Enquetes e 
                 JOIN Postagens p ON e.post_id = p.id 
                 WHERE e.id = ?";
    $stmt_info = $conn->prepare($sql_info);
    $stmt_info->bind_param("i", $enquete_id);
    $stmt_info->execute();
    $info = $stmt_info->get_result()->fetch_assoc();
    $stmt_info->close();

    if (!$info) throw new Exception("Enquete não localizada.");

    // 5. VERIFICA SE O USUÁRIO JÁ VOTOU NESTA ENQUETE
    $sql_check = "SELECT ev.id, ev.opcao_id FROM Enquete_Votos ev 
                  JOIN Enquete_Opcoes eo ON ev.opcao_id = eo.id 
                  WHERE eo.enquete_id = ? AND ev.usuario_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $enquete_id, $user_id);
    $stmt_check->execute();
    $existing_vote = $stmt_check->get_result()->fetch_assoc();
    $stmt_check->close();

    $acao_realizada = 'voto_registado';
    $enviar_notificacao = false;
    $voted_option_id = $opcao_id;

    if ($existing_vote) {
        if ($existing_vote['opcao_id'] == $opcao_id) {
            // Ação: CANCELAR VOTO
            $sql_del = "DELETE FROM Enquete_Votos WHERE id = ?";
            $stmt_del = $conn->prepare($sql_del);
            $stmt_del->bind_param("i", $existing_vote['id']);
            $stmt_del->execute();
            $stmt_del->close();
            
            $acao_realizada = 'voto_removido';
            $voted_option_id = null;
        } else {
            // Ação: TROCAR VOTO
            $sql_upd = "UPDATE Enquete_Votos SET opcao_id = ? WHERE id = ?";
            $stmt_upd = $conn->prepare($sql_upd);
            $stmt_upd->bind_param("ii", $opcao_id, $existing_vote['id']);
            $stmt_upd->execute();
            $stmt_upd->close();
            
            $acao_realizada = 'voto_alterado';
            $enviar_notificacao = true;
        }
    } else {
        // Ação: NOVO VOTO
        $sql_ins = "INSERT INTO Enquete_Votos (opcao_id, usuario_id) VALUES (?, ?)";
        $stmt_ins = $conn->prepare($sql_ins);
        $stmt_ins->bind_param("ii", $opcao_id, $user_id);
        $stmt_ins->execute();
        $stmt_ins->close();
        
        $enviar_notificacao = true;
    }

    // 6. LÓGICA DE NOTIFICAÇÃO AO AUTOR PADRONIZADA
    if ($enviar_notificacao && $info['autor_id'] != $user_id) {
        // Usamos a constante global:
        $tipo_notif = NOTIF_VOTO_ENQUETE;

        $sql_notif = "INSERT INTO notificacoes (usuario_id, remetente_id, tipo, id_referencia, lida, data_criacao) 
                      SELECT ?, ?, ?, ?, 0, NOW()
                      WHERE NOT EXISTS (
                          SELECT 1 FROM notificacoes 
                          WHERE usuario_id = ? AND remetente_id = ? AND tipo = ? AND id_referencia = ? AND lida = 0
                      )";
        $stmt_notif = $conn->prepare($sql_notif);
        // Bind de 8 parâmetros: iisiiisi (autor, remetente, tipo, post, autor, remetente, tipo, post)
        $stmt_notif->bind_param("iisiissi", 
            $info['autor_id'], $user_id, $tipo_notif, $info['post_id'], 
            $info['autor_id'], $user_id, $tipo_notif, $info['post_id']
        );
        $stmt_notif->execute();
        $stmt_notif->close();
    }

    // 7. BUSCA RESULTADOS ATUALIZADOS
    $sql_results = "SELECT id, opcao_texto, 
                    (SELECT COUNT(*) FROM Enquete_Votos WHERE opcao_id = Enquete_Opcoes.id) AS total_votos 
                    FROM Enquete_Opcoes WHERE enquete_id = ?";
    $stmt_res = $conn->prepare($sql_results);
    $stmt_res->bind_param("i", $enquete_id);
    $stmt_res->execute();
    $result_set = $stmt_res->get_result();
    
    $novas_opcoes = [];
    $total_geral = 0;
    while ($row = $result_set->fetch_assoc()) {
        $total_geral += (int)$row['total_votos'];
        $novas_opcoes[] = $row;
    }
    $stmt_res->close();

    foreach ($novas_opcoes as &$opt) {
        $opt['percentagem'] = ($total_geral > 0) ? round(($opt['total_votos'] / $total_geral) * 100) : 0;
    }

    // 8. RETORNO SINCROZINADO
    echo json_encode([
        'success'      => true,
        'action'       => $acao_realizada,
        'total_votos'  => $total_geral,
        'opcoes'       => $novas_opcoes,
        'voted_option' => $voted_option_id
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}