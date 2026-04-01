<?php
/**
 * api/busca/api_historico.php - API de Consultas Recentes Inteligente
 * PAPEL: Retornar histórico enriquecido (Perfis, Grupos e Termos) para o dropdown.
 * VERSÃO: 2.0 (Suporte a Entidades - socialbr.lol)
 */

// 1. Cabeçalhos de Resposta (JSON seguro)
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// 2. Segurança e Sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Blindagem: Apenas usuários logados podem ver seu próprio histórico de pesquisa
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado: Usuário não logado']);
    exit;
}

// 3. Carregamento de Dependências (Caminhos relativos à pasta api/busca/)
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/BuscaLogic.php';

/**
 * 4. ESTABILIZAÇÃO DA CONEXÃO
 */
if (!isset($db)) {
    if (isset($pdo)) { $db = $pdo; } 
    elseif (isset($conn)) { $db = $conn; } 
    elseif (isset($conexao)) { $db = $conexao; }
}

if (!isset($db) || $db === null) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => 'Erro de conexão com o banco de dados']);
    exit;
}

try {
    $userId = (int)$_SESSION['user_id'];
    $busca = new BuscaLogic($db, $userId);
    
    /**
     * 5. OBTENÇÃO DO HISTÓRICO BRUTO
     * Busca os últimos 5 registros (termo, tipo_clicado e id_alvo)
     */
    $rawHistorico = $busca->obterHistoricoSugestoes(5);
    $finalHistorico = [];
    $base = $config['base_path'] ?? '/';

    /**
     * 6. ENRIQUECIMENTO DE DADOS (Transforma IDs em nomes e fotos)
     */
    foreach ($rawHistorico as $item) {
        // Caso A: É apenas uma pesquisa por texto (sem alvo específico)
        if (empty($item['id_alvo'])) {
            $finalHistorico[] = [
                'tipo'   => 'termo',
                'titulo' => $item['termo'],
                'link'   => $base . 'pesquisa?q=' . urlencode($item['termo'])
            ];
            continue;
        }

        // Caso B: Foi um clique em uma entidade (Perfil, Grupo ou Post)
        $entidade = null;
        $idAlvo = (int)$item['id_alvo'];

        if ($item['tipo_clicado'] === 'perfil') {
            $stmt = $db->prepare("SELECT nome, sobrenome, foto_perfil_url FROM Usuarios WHERE id = ?");
            $stmt->bind_param("i", $idAlvo);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            if ($res) {
                $entidade = [
                    'tipo'   => 'perfil',
                    'titulo' => $res['nome'] . ' ' . $res['sobrenome'],
                    'imagem' => !empty($res['foto_perfil_url']) ? $base . $res['foto_perfil_url'] : $base . 'assets/images/default-avatar.png',
                    'link'   => $base . 'perfil/' . $idAlvo
                ];
            }
        } 
        elseif ($item['tipo_clicado'] === 'grupo') {
            $stmt = $db->prepare("SELECT nome, foto_capa_url FROM Grupos WHERE id = ?");
            $stmt->bind_param("i", $idAlvo);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            if ($res) {
                $entidade = [
                    'tipo'   => 'grupo',
                    'titulo' => $res['nome'],
                    'imagem' => !empty($res['foto_capa_url']) ? $base . $res['foto_capa_url'] : $base . 'assets/images/default-group.png',
                    'link'   => $base . 'grupos/ver/' . $idAlvo
                ];
            }
        }
        elseif ($item['tipo_clicado'] === 'post') {
            $stmt = $db->prepare("SELECT p.conteudo_texto, u.foto_perfil_url FROM Postagens p JOIN Usuarios u ON p.id_usuario = u.id WHERE p.id = ?");
            $stmt->bind_param("i", $idAlvo);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            if ($res) {
                $textoLimpo = strip_tags($res['conteudo_texto']);
                $entidade = [
                    'tipo'   => 'post',
                    'titulo' => mb_strimwidth($textoLimpo, 0, 40, '...'),
                    'imagem' => !empty($res['foto_perfil_url']) ? $base . $res['foto_perfil_url'] : $base . 'assets/images/default-avatar.png',
                    'link'   => $base . 'postagem/' . $idAlvo
                ];
            }
        }

        // Adiciona ao resultado final se a entidade ainda existir, caso contrário trata como termo
        $finalHistorico[] = $entidade ?: [
            'tipo'   => 'termo',
            'titulo' => $item['termo'],
            'link'   => $base . 'pesquisa?q=' . urlencode($item['termo'])
        ];
    }

    // 7. Resposta JSON estruturada para o MotorBusca.js
    echo json_encode([
        'sucesso'    => true,
        'resultados' => $finalHistorico,
        'total'      => count($finalHistorico)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false, 
        'erro'    => 'Falha ao processar histórico enriquecido',
        'debug'   => $e->getMessage()
    ]);
}