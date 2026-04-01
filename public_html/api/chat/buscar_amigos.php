<?php
/**
 * api/chat/buscar_amigos.php
 * Endpoint: Busca de amigos para novas conversas.
 * PAPEL: Retornar lista de amigos filtrados com suporte a Busca Híbrida.
 * VERSÃO: V52.2 (Suporte a Busca Híbrida SPA - socialbr.lol)
 */

header('Content-Type: application/json');

// 1. Inicialização e Segurança
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * CORREÇÃO DE CAMINHO:
 * O ficheiro está em /public_html/api/chat/
 * ../ (api/) -> ../ (public_html/) -> ../ (raiz/home)
 */
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/ChatLogic.php';

// Verifica se o usuário está autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sessão expirada ou acesso negado.']);
    exit;
}

$user_id_logado = $_SESSION['user_id'];

/**
 * 2. Captura do Termo de Busca
 * O termo pode vir vazio (exibindo amigos sugeridos) ou com filtro.
 */
$termo = trim($_GET['termo'] ?? '');

try {
    // 3. Consulta via ChatLogic (Utiliza o método atualizado V67.5 com conversa_existente_id)
    // Nota: O objeto $conn é fornecido pelo database.php
    $amigos = ChatLogic::searchFriendsForNewChat($conn, $user_id_logado, $termo);

    // 4. Formatação de Dados para o Frontend
    $lista_formatada = [];
    foreach ($amigos as $amigo) {
        $lista_formatada[] = [
            'id' => (int)$amigo['id'],
            'nome' => htmlspecialchars($amigo['nome'] . ' ' . $amigo['sobrenome']),
            'username' => htmlspecialchars($amigo['nome_de_usuario']),
            'avatar' => !empty($amigo['foto_perfil_url']) 
                        ? $config['base_path'] . $amigo['foto_perfil_url'] 
                        : $config['base_path'] . 'assets/images/default-avatar.png',
            // [NOVIDADE V52.2] Entrega o ID da conversa se ela já existir
            'conversa_id' => !empty($amigo['conversa_existente_id']) ? (int)$amigo['conversa_existente_id'] : null
        ];
    }

    echo json_encode([
        'sucesso' => true,
        'resultados' => $lista_formatada
    ]);

} catch (Exception $e) {
    // Log de erro interno para segurança do sistema
    echo json_encode([
        'sucesso' => false, 
        'erro' => 'Erro técnico ao processar busca de amigos.'
    ]);
}