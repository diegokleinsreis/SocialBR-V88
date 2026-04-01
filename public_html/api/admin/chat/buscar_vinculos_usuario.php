<?php
/**
 * api/admin/chat/buscar_vinculos_usuario.php
 * PAPEL: Rastrear conexões de um utilizador (Conversas Privadas e Grupos).
 * VERSÃO: 1.0 (Masterplan v9.4 - socialbr.lol)
 */

// --- [PASSO 1: PROTEÇÃO E CONEXÃO] ---
require_once __DIR__ . '/../../../admin/admin_auth.php'; 

header('Content-Type: application/json');

// --- [PASSO 2: VALIDAÇÃO DE ENTRADA] ---
$termo = isset($_GET['termo']) ? trim($_GET['termo']) : '';

if (strlen($termo) < 1) {
    echo json_encode(['sucesso' => false, 'erro' => 'Digite um nome ou ID para pesquisar.']);
    exit;
}

try {
    // --- [PASSO 3: BUSCA DO UTILIZADOR ALVO] ---
    // Verificamos se o termo é um ID numérico ou um nome
    $query_user = is_numeric($termo) 
        ? "SELECT id, nome, sobrenome FROM Usuarios WHERE id = ? LIMIT 1"
        : "SELECT id, nome, sobrenome FROM Usuarios WHERE nome LIKE ? OR sobrenome LIKE ? LIMIT 5";
    
    $stmt_user = $conn->prepare($query_user);
    if (is_numeric($termo)) {
        $stmt_user->bind_param("i", $termo);
    } else {
        $busca = "%$termo%";
        $stmt_user->bind_param("ss", $busca, $busca);
    }
    
    $stmt_user->execute();
    $res_user = $stmt_user->get_result();

    if ($res_user->num_rows === 0) {
        echo json_encode(['sucesso' => false, 'erro' => 'Utilizador não encontrado.']);
        exit;
    }

    $usuarios_encontrados = [];
    while ($u = $res_user->fetch_assoc()) {
        $user_id = $u['id'];
        
        // --- [PASSO 4: MAPEAMENTO DE VÍNCULOS] ---
        /**
         * Buscamos conversas onde o utilizador participa.
         * Se for PRIVADA: Identificamos quem é a outra pessoa.
         * Se for GRUPO: Identificamos o título do grupo.
         */
        $sql_vinculos = "SELECT 
                            c.id AS conversa_id, 
                            c.tipo, 
                            c.titulo AS grupo_titulo,
                            u2.nome AS outro_nome, 
                            u2.sobrenome AS outro_sobrenome
                         FROM chat_participantes p
                         JOIN chat_conversas c ON p.conversa_id = c.id
                         LEFT JOIN chat_participantes p2 ON c.id = p2.conversa_id AND p2.usuario_id != p.usuario_id AND c.tipo = 'privada'
                         LEFT JOIN Usuarios u2 ON p2.usuario_id = u2.id
                         WHERE p.usuario_id = ?
                         ORDER BY c.ultima_mensagem_at DESC";

        $stmt_v = $conn->prepare($sql_vinculos);
        $stmt_v->bind_param("i", $user_id);
        $stmt_v->execute();
        $res_v = $stmt_v->get_result();

        $conexoes = [];
        while ($v = $res_v->fetch_assoc()) {
            $conexoes[] = [
                'conversa_id' => (int)$v['conversa_id'],
                'tipo'        => $v['tipo'],
                'label'       => ($v['tipo'] === 'privada') 
                                 ? "Chat com: " . ($v['outro_nome'] . " " . $v['outro_sobrenome'])
                                 : "Grupo: " . $v['grupo_titulo']
            ];
        }

        $usuarios_encontrados[] = [
            'id'       => $u['id'],
            'nome'     => $u['nome'] . " " . $u['sobrenome'],
            'vinculos' => $conexoes
        ];
    }

    echo json_encode([
        'sucesso'    => true,
        'resultados' => $usuarios_encontrados
    ]);

} catch (Exception $e) {
    error_log("Erro na Inspeção de Vínculos: " . $e->getMessage());
    echo json_encode(['sucesso' => false, 'erro' => 'Erro interno na investigação.']);
}