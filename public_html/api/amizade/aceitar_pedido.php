<?php
session_start();
header('Content-Type: application/json');

// 1. Verificação de Segurança: Garante que o utilizador está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Acesso negado. Você precisa estar logado.']);
    exit();
}

// --- IMPORTAÇÕES NECESSÁRIAS ---
require_once __DIR__ . '/../../../config/database.php';
// Importa o dicionário de tipos para padronizar as notificações
require_once __DIR__ . '/../../../config/tipos_notificacoes.php';

// --- NOVO BLOCO DE SEGURANÇA: VERIFICAÇÃO CSRF ---
// Verifica se é um POST, se o token existe e se é válido.
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    header('Content-Type: application/json');
    http_response_code(403); // Código 403: Acesso Proibido/Token Inválido
    echo json_encode(['success' => false, 'error' => 'Token de segurança inválido. Tente recarregar a página.']);
    exit();
}
// --- FIM DO NOVO BLOCO ---


$utilizador_logado_id = $_SESSION['user_id']; // Este é o Utilizador B (quem aceita)
$amizade_id = isset($_POST['id_amizade']) ? (int)$_POST['id_amizade'] : 0;

try {
    if ($amizade_id <= 0) {
        throw new Exception("ID do pedido de amizade inválido.");
    }

    // 2. Segurança Crucial: Atualiza o status
    $sql = "UPDATE Amizades 
            SET status = 'aceite' 
            WHERE id = ? 
              AND usuario_dois_id = ? 
              AND status = 'pendente'";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $amizade_id, $utilizador_logado_id);
    
    if ($stmt->execute()) {
        // Verifica se a linha foi realmente alterada
        if ($stmt->affected_rows > 0) {
            
            // --- [INÍCIO DA LÓGICA DE NOTIFICAÇÃO PADRONIZADA] ---
            try {
                // 1. Descobrir quem enviou o pedido (Utilizador A)
                $sql_get_sender = "SELECT usuario_um_id FROM Amizades WHERE id = ?";
                $stmt_get_sender = $conn->prepare($sql_get_sender);
                $stmt_get_sender->bind_param("i", $amizade_id);
                $stmt_get_sender->execute();
                $result_sender = $stmt_get_sender->get_result();
                
                if ($row = $result_sender->fetch_assoc()) {
                    $remetente_original_id = $row['usuario_um_id']; // ID de quem vai RECEBER a notificação (User A)
                    $utilizador_que_aceitou_id = $utilizador_logado_id; // ID de quem vai ENVIAR a notificação (User B)
                    
                    // 2. Criar a notificação usando a constante do dicionário
                    $tipo_notificacao = NOTIF_AMIZADE_ACEITA; 
                    // O id_referencia é o ID de quem aceitou (User B), para o link apontar para o perfil dele.
                    $id_referencia = $utilizador_que_aceitou_id; 

                    $sql_notificacao = "INSERT INTO notificacoes (usuario_id, remetente_id, tipo, id_referencia) VALUES (?, ?, ?, ?)";
                    $stmt_notificacao = $conn->prepare($sql_notificacao);
                    
                    $stmt_notificacao->bind_param("iisi", $remetente_original_id, $utilizador_que_aceitou_id, $tipo_notificacao, $id_referencia);
                    
                    $stmt_notificacao->execute(); 
                    $stmt_notificacao->close();
                }
                $stmt_get_sender->close();

            } catch (Exception $e) {
                // Se a notificação falhar, não estraga a operação principal.
                error_log("Falha ao criar notificação de amizade aceite: " . $e->getMessage());
            }
            // --- [FIM DA LÓGICA DE NOTIFICAÇÃO] ---

            // Envia a resposta de sucesso (a amizade foi aceite)
            echo json_encode(['success' => true, 'message' => 'Pedido de amizade aceito!']);

        } else {
            throw new Exception("Não foi possível aceitar este pedido. Ele pode já ter sido aceito, recusado ou não lhe pertencer.");
        }
    } else {
        throw new Exception("Ocorreu um erro no servidor ao tentar aceitar o pedido.");
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>