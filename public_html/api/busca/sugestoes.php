<?php
/**
 * sugestoes.php - API de Busca Rápida (Dropdown)
 * VERSÃO: 1.2 - Suporte a Categorização (Dica de Ouro)
 * PAPEL: Servir resultados em JSON organizados por tipo para o dropdown.
 */

// 1. Cabeçalhos de Segurança e Conteúdo
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// 2. Inicialização do Ambiente
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Blindagem: Apenas usuários logados podem usar a API
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sessão encerrada']);
    exit;
}

// 3. Carregamento de Dependências
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/BuscaLogic.php';

/**
 * 4. ESTABILIZAÇÃO DA CONEXÃO
 */
if (!isset($db)) {
    if (isset($pdo)) {
        $db = $pdo;
    } elseif (isset($conn)) {
        $db = $conn;
    } elseif (isset($conexao)) {
        $db = $conexao;
    }
}

// Verificação de Sanidade da Conexão
if (!isset($db) || $db === null) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => 'Erro de infraestrutura: Conexão com banco não encontrada']);
    exit;
}

try {
    // 5. Captura e Sanitização do Input
    $termo = isset($_GET['q']) ? trim($_GET['q']) : '';
    $userId = (int)$_SESSION['user_id'];
    
    // Instancia o Cérebro da Busca
    $busca = new BuscaLogic($db, $userId);
    
    $resposta = [
        'sucesso' => true,
        'termo' => $termo,
        'resultados' => [],
        'historico' => false
    ];

    // 6. Lógica de Resposta
    if (empty($termo)) {
        // Se vazio, mostra histórico de buscas recentes
        $resposta['resultados'] = $busca->obterHistoricoSugestoes(5);
        $resposta['historico'] = true;
    } else {
        // Busca Global Balanceada (Usa a nova lógica de fatias 3-3-2)
        $resultadosRaw = $busca->buscarGlobal($termo, 8);
        
        // Recupera base_path (essencial para URLs de imagens e links)
        $base = $config['base_path'] ?? '/';
        
        // 7. Processamento e Formatação de Dados para o Dropdown
        foreach ($resultadosRaw as $item) {
            $formatado = [
                'id'   => $item['id'],
                'tipo' => $item['tipo_resultado']
            ];

            // Formatação específica por tipo para o visual do dropdown
            if ($item['tipo_resultado'] === 'perfil') {
                $formatado['titulo']    = $item['nome'] . ' ' . $item['sobrenome'];
                $formatado['subtitulo'] = '@' . $item['nome_de_usuario'];
                $formatado['imagem']    = !empty($item['foto_perfil_url']) 
                    ? $base . $item['foto_perfil_url'] 
                    : $base . 'assets/images/default-avatar.png';
                $formatado['link']      = $base . 'perfil/' . $item['nome_de_usuario'];
                $formatado['label']     = 'Pessoas';
            } 
            elseif ($item['tipo_resultado'] === 'grupo') {
                $formatado['titulo']    = $item['nome'];
                $formatado['subtitulo'] = 'Grupo ' . ucfirst($item['privacidade']);
                $formatado['imagem']    = !empty($item['foto_capa_url']) 
                    ? $base . $item['foto_capa_url'] 
                    : $base . 'assets/images/default-group.png';
                $formatado['link']      = $base . 'grupos/ver/' . $item['id'];
                $formatado['label']     = 'Grupos';
            } 
            elseif ($item['tipo_resultado'] === 'post') {
                // Remove HTML e limita o texto para caber na linha do dropdown
                $textoLimpo = strip_tags($item['conteudo_texto']);
                $formatado['titulo']    = mb_strimwidth($textoLimpo, 0, 45, '...');
                $formatado['subtitulo'] = 'Postagem de ' . $item['nome'];
                $formatado['imagem']    = !empty($item['foto_perfil_url']) 
                    ? $base . $item['foto_perfil_url'] 
                    : $base . 'assets/images/default-avatar.png';
                $formatado['link']      = $base . 'postagem/' . $item['id'];
                $formatado['label']     = 'Postagens';
            }

            $resposta['resultados'][] = $formatado;
        }
    }

    echo json_encode($resposta);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false, 
        'erro'    => 'Erro interno ao processar a busca',
        'debug'   => $e->getMessage()
    ]);
}