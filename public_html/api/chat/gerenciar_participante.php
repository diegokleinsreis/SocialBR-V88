<?php
/**
 * api/chat/gerenciar_participante.php
 * Ponte de Comando: Processador de Ações Administrativas.
 * PAPEL: Validar e executar expulsões, promoções e saídas de grupos com Blindagem.
 * VERSÃO: V67.1 - Blindagem CSRF e Estabilidade de Saída (socialbr.lol)
 */

// Inicia o buffer para evitar que avisos do PHP quebrem o JSON no console
ob_start();
header('Content-Type: application/json; charset=utf-8');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. DEPENDÊNCIAS DE SISTEMA (Caminhos relativos a api/chat/)
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/tipos_notificacoes.php';
require_once __DIR__ . '/../../../src/ChatLogic.php';

// 2. CAPTURA DE DADOS (JSON Input)
$input = json_decode(file_get_contents('php://input'), true);

$user_id_logado = $_SESSION['user_id'] ?? 0;
$conversa_id    = (int)($input['conversa_id'] ?? 0);
$target_user_id = (int)($input['usuario_id'] ?? 0); 
$acao           = $input['acao'] ?? '';

// Sincronizado com o padrão global 'csrf_token' do sistema
$csrf_token     = $input['csrf_token'] ?? ''; 

// 3. BLINDAGEM DE SEGURANÇA
if (!$user_id_logado) {
    ob_clean();
    echo json_encode(['sucesso' => false, 'erro' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

// Validação de CSRF unificada
if (empty($csrf_token) || !isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
    ob_clean();
    echo json_encode(['sucesso' => false, 'erro' => 'Falha na validação de segurança (CSRF).']);
    exit;
}

if (!$conversa_id || !$acao) {
    ob_clean();
    echo json_encode(['sucesso' => false, 'erro' => 'Parâmetros de requisição incompletos.']);
    exit;
}

try {
    // 4. LÓGICA DE DECISÃO ADMINISTRATIVA
    switch ($acao) {
        
        case 'sair':
            // Saída voluntária: o alvo deve ser o próprio usuário logado
            if (ChatLogic::removeParticipant($conn, $conversa_id, $user_id_logado)) {
                ob_clean();
                echo json_encode(['sucesso' => true, 'mensagem' => 'Você saiu do grupo com sucesso.']);
            } else {
                ob_clean();
                echo json_encode(['sucesso' => false, 'erro' => 'Falha técnica ao processar a sua saída.']);
            }
            break;

        case 'remover':
            // Apenas o dono pode remover membros
            if (!ChatLogic::isGroupOwner($conn, $conversa_id, $user_id_logado)) {
                ob_clean();
                echo json_encode(['sucesso' => false, 'erro' => 'Ação negada. Apenas administradores podem remover membros.']);
                exit;
            }

            if (ChatLogic::kickMember($conn, $conversa_id, $target_user_id)) {
                ob_clean();
                echo json_encode(['sucesso' => true, 'mensagem' => 'O membro foi removido do grupo.']);
            } else {
                ob_clean();
                echo json_encode(['sucesso' => false, 'erro' => 'Não é permitido remover o proprietário do grupo.']);
            }
            break;

        case 'promover':
            // Apenas o dono pode transferir a posse
            if (!ChatLogic::isGroupOwner($conn, $conversa_id, $user_id_logado)) {
                ob_clean();
                echo json_encode(['sucesso' => false, 'erro' => 'Apenas o proprietário pode transferir a gestão.']);
                exit;
            }

            if (ChatLogic::transferOwnership($conn, $conversa_id, $target_user_id)) {
                ob_clean();
                echo json_encode(['sucesso' => true, 'mensagem' => 'Gestão do grupo transferida com sucesso!']);
            } else {
                ob_clean();
                echo json_encode(['sucesso' => false, 'erro' => 'Erro ao transferir propriedade. O membro ainda está no grupo?']);
            }
            break;

        default:
            ob_clean();
            echo json_encode(['sucesso' => false, 'erro' => 'Comando de gestão não reconhecido.']);
            break;
    }

} catch (Exception $e) {
    ob_clean();
    error_log("Erro em gerenciar_participante: " . $e->getMessage());
    echo json_encode(['sucesso' => false, 'erro' => 'Erro interno ao processar a ação.']);
}

// Libera o buffer e encerra
ob_end_flush();