<?php
/**
 * api/grupos/participar.php
 * API de Ação: Participar de um Grupo.
 * PAPEL: Processar entrada direta ou criar solicitação pendente e notificar o dono.
 * VERSÃO: 1.2 - Padronização Global de Tipos (socialbr.lol)
 */

// 1. CONFIGURAÇÃO E SEGURANÇA DE SESSÃO
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Definimos o retorno como JSON para interatividade via AJAX
header('Content-Type: application/json');

// Caminhos baseados na sua estrutura de diretórios
require_once __DIR__ . '/../../../config/database.php';
// IMPORTANTE: Inclui o dicionário de tipos de notificação para manter o padrão
require_once __DIR__ . '/../../../config/tipos_notificacoes.php';
require_once __DIR__ . '/../../../src/GruposLogic.php';

// Bloqueio: Apenas requisições POST de usuários logados
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'erro', 
        'msg'    => 'Acesso negado ou sessão expirada.'
    ]);
    exit();
}

// 2. VALIDAÇÃO DE SEGURANÇA (TOKEN CSRF)
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode([
        'status' => 'erro', 
        'msg'    => 'Falha de validação de segurança (Token Inválido).'
    ]);
    exit();
}

// 3. CAPTURA DOS PARÂMETROS
$id_usuario_logado = (int)$_SESSION['user_id'];
$id_grupo = (int)($_POST['id_grupo'] ?? 0);

if ($id_grupo <= 0) {
    echo json_encode([
        'status' => 'erro', 
        'msg'    => 'Identificação do grupo inválida.'
    ]);
    exit();
}

// 4. EXECUÇÃO VIA CÉREBRO (GruposLogic)
// O método participar() cuida da distinção entre público e privado
$resultado = GruposLogic::participar($conn, $id_grupo, $id_usuario_logado);

// 5. LÓGICA DE NOTIFICAÇÃO PADRONIZADA (Toast Sync)
// Se o grupo for privado e a solicitação foi enviada, notificamos o dono
if ($resultado['status'] === 'sucesso' && $resultado['acao'] === 'solicitacao_enviada') {
    try {
        // Buscar o ID do dono do grupo
        $sqlDono = "SELECT id_dono FROM Grupos WHERE id = ?";
        $stmtD = $conn->prepare($sqlDono);
        $stmtD->bind_param("i", $id_grupo);
        $stmtD->execute();
        $resDono = $stmtD->get_result()->fetch_assoc();
        $id_dono = $resDono['id_dono'] ?? 0;
        $stmtD->close();

        if ($id_dono > 0 && $id_dono != $id_usuario_logado) {
            // USANDO A CONSTANTE PADRONIZADA:
            $tipo_notif = NOTIF_SOLICITACAO_GRUPO;
            
            $sqlNotif = "INSERT INTO notificacoes (usuario_id, remetente_id, tipo, id_referencia, lida, data_criacao) 
                          VALUES (?, ?, ?, ?, 0, NOW())";
            $stmtN = $conn->prepare($sqlNotif);
            // id_referencia aponta para o ID do grupo para o link do Toast funcionar
            // bind_param: iisi (id_recebe, id_envia, string_tipo, id_referencia)
            $stmtN->bind_param("iisi", $id_dono, $id_usuario_logado, $tipo_notif, $id_grupo);
            $stmtN->execute();
            $stmtN->close();
        }
    } catch (Exception $e) {
        // Erro silencioso na notificação para não travar a ação principal do usuário
        error_log("Erro ao notificar solicitação de grupo: " . $e->getMessage());
    }
}

// 6. RESPOSTA PARA O FRONT-END
echo json_encode($resultado);