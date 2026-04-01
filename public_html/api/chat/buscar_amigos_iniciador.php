<?php
/**
 * api/chat/buscar_amigos_iniciador.php
 * Endpoint: Busca de amigos para o Iniciador de Chat.
 * PAPEL: Retornar lista de amigos disponíveis (Aceites e não bloqueados).
 * VERSÃO: V1.0 (Sincronizado com ChatLogic V60.0 - socialbr.lol)
 */

header('Content-Type: application/json');

// 1. Inicialização e Segurança
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Sessão expirada ou acesso negado.']);
    exit;
}

/**
 * CAMINHOS DE INFRAESTRUTURA:
 * O ficheiro está em /public_html/api/chat/
 * ../ (api/) -> ../ (public_html/) -> ../ (raiz)
 */
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/ChatLogic.php';

$userId = $_SESSION['user_id'];

/**
 * 2. Captura de Parâmetros
 * group: 1 se for para criação de grupo (muda a lógica de filtragem no ChatLogic)
 * termo: filtro opcional de pesquisa
 */
$isForGroup = (isset($_GET['group']) && $_GET['group'] == '1');
$termo = trim($_GET['termo'] ?? '');

try {
    // 3. Consulta via ChatLogic (Utiliza a nova assinatura V60.0)
    $amigos = ChatLogic::searchFriendsForNewChat($conn, $userId, $termo, $isForGroup);

    // 4. Formatação de Dados para o Modal Iniciador
    $lista_formatada = [];
    foreach ($amigos as $amigo) {
        $lista_formatada[] = [
            'id' => (int)$amigo['id'],
            'nome_completo' => htmlspecialchars($amigo['nome'] . ' ' . $amigo['sobrenome']),
            'username' => htmlspecialchars($amigo['nome_de_usuario']),
            'avatar' => !empty($amigo['foto_perfil_url']) 
                        ? $config['base_path'] . $amigo['foto_perfil_url'] 
                        : $config['base_path'] . 'assets/images/default-avatar.png'
        ];
    }

    // Retorno compatível com a lógica fetch do modal_iniciador_chat.php
    echo json_encode([
        'success' => true,
        'amigos' => $lista_formatada
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => 'Erro técnico ao processar lista de amigos para o iniciador.'
    ]);
}