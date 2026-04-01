<?php
/**
 * ARQUIVO: api/marketplace/editar_anuncio.php
 * PAPEL: Processar a edição de anúncios, gestão de fotos (remoção/adição) e sincronização.
 * VERSÃO: 5.6 - Estrutura /midias/ e Caminhos Corrigidos (socialbr.lol)
 */

header('Content-Type: application/json; charset=utf-8');

// 1. --- [GESTÃO DE SESSÃO E SEGURANÇA] ---
if (session_status() == PHP_SESSION_NONE) { 
    session_start(); 
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sessão expirada. Por favor, faça login novamente.']);
    exit;
}

/**
 * FUNÇÃO AUXILIAR: Sanitizar nomes para ficheiros
 */
function sanitizarParaFicheiro($string) {
    $string = mb_strtolower($string, 'UTF-8');
    $acentos = [
        'a' => ['á','à','â','ã','ä'], 'e' => ['é','è','ê','ë'],
        'i' => ['í','ì','î','ï'], 'o' => ['ó','ò','ô','õ','ö'],
        'u' => ['ú','ù','û','ü'], 'c' => ['ç'], 'n' => ['ñ']
    ];
    foreach ($acentos as $letra => $padrao) {
        $string = str_replace($padrao, $letra, $string);
    }
    $string = preg_replace('/[^a-z0-9\-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

// 2. --- [DEPENDÊNCIAS E CONEXÃO] ---
try {
    // Caminhos Fixos Seguros
    require_once __DIR__ . '/../../../config/database.php';
    require_once __DIR__ . '/../../utils/image_handler.php';

    // Inicializa PDO usando as variáveis do database.php
    if (!isset($pdo)) {
        $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro de configuração: ' . $e->getMessage()]);
    exit;
}

// 3. --- [SEGURANÇA CSRF E MÉTODO] ---
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Erro de segurança: Token inválido.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
    exit;
}

// 4. --- [CAPTURA E VALIDAÇÃO DE PROPRIEDADE] ---
$usuario_id = $_SESSION['user_id'];
$anuncio_id = filter_input(INPUT_POST, 'anuncio_id', FILTER_VALIDATE_INT);

if (!$anuncio_id) {
    echo json_encode(['success' => false, 'message' => 'ID do anúncio inválido.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Validação: Garante que só o dono pode editar
    $stmtValida = $pdo->prepare("
        SELECT ma.id_postagem, u.nome
        FROM Marketplace_Anuncios ma
        INNER JOIN Postagens p ON ma.id_postagem = p.id
        INNER JOIN Usuarios u ON p.id_usuario = u.id
        WHERE ma.id = ? AND p.id_usuario = ?
    ");
    $stmtValida->execute([$anuncio_id, $usuario_id]);
    $anuncioBase = $stmtValida->fetch(PDO::FETCH_ASSOC);

    if (!$anuncioBase) {
        throw new Exception("Permissão negada ou anúncio inexistente.");
    }

    $id_postagem = $anuncioBase['id_postagem'];
    $nome_user_limpo = sanitizarParaFicheiro($anuncioBase['nome'] ?? 'vendedor');

    // 5. --- [TRATAMENTO DOS DADOS DO PRODUTO] ---
    $titulo    = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
    $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);
    $precoRaw  = str_replace(',', '.', str_replace('.', '', $_POST['preco'] ?? '0'));
    $preco     = floatval($precoRaw);
    
    $categoria = $_POST['categoria'] ?? '';
    $condicao  = $_POST['condicao'] ?? '';
    $estado    = $_POST['estado'] ?? '';
    $cidade    = $_POST['cidade'] ?? '';

    // Atualização na tabela Marketplace
    $stmtUpdate = $pdo->prepare("
        UPDATE Marketplace_Anuncios SET 
            titulo_produto = ?, descricao_produto = ?, preco = ?, 
            categoria = ?, condicao = ?, estado = ?, cidade = ?, atualizado_em = NOW()
        WHERE id = ?
    ");
    $stmtUpdate->execute([$titulo, $descricao, $preco, $categoria, $condicao, $estado, $cidade, $anuncio_id]);

    // Sincroniza o conteúdo na tabela Postagens (Feed)
    $conteudo_post = "🛒 ANÚNCIO: " . $titulo . "\n\n" . $descricao;
    $stmtPost = $pdo->prepare("UPDATE Postagens SET conteudo_texto = ? WHERE id = ?");
    $stmtPost->execute([$conteudo_post, $id_postagem]);

    // 6. --- [GESTÃO DE FOTOS: REMOÇÃO] ---
    if (!empty($_POST['fotos_remover'])) {
        $idsParaRemover = explode(',', $_POST['fotos_remover']);
        foreach ($idsParaRemover as $foto_id) {
            $foto_id = (int)$foto_id;
            
            $stmtBuscaF = $pdo->prepare("SELECT url_midia FROM Postagens_Midia WHERE id = ? AND id_postagem = ?");
            $stmtBuscaF->execute([$foto_id, $id_postagem]);
            $fotoPath = $stmtBuscaF->fetchColumn();

            if ($fotoPath) {
                // Remove o arquivo físico (suporta caminhos antigos e novos)
                $caminhoFisico = __DIR__ . '/../../' . $fotoPath;
                if (file_exists($caminhoFisico) && !is_dir($caminhoFisico)) {
                    unlink($caminhoFisico); 
                }
                // Remove o registro do banco
                $pdo->prepare("DELETE FROM Postagens_Midia WHERE id = ?")->execute([$foto_id]);
            }
        }
    }

    // 7. --- [GESTÃO DE FOTOS: NOVOS UPLOADS] ---
    if (isset($_FILES['fotos']) && !empty($_FILES['fotos']['name'][0])) {
        $uploadDir = __DIR__ . '/../../midias/marketplace/fotos/';
        $urlBaseDB = 'midias/marketplace/fotos/';
        $data_hora = date('Y-m-d_H-i-s');

        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        foreach ($_FILES['fotos']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['fotos']['error'][$key] === UPLOAD_ERR_OK) {
                // PADRÃO: ID_Nome_Data_Hora_Index.webp
                $novoNome = "{$usuario_id}_{$nome_user_limpo}_{$data_hora}_upd_{$key}.webp";
                $destinoFinal = $uploadDir . $novoNome;

                // Processa, redimensiona e converte para WebP
                if (process_and_save_image($tmpName, $destinoFinal, 'resize_to_width', 1080)) {
                    $url_banco = $urlBaseDB . $novoNome;
                    $stmtInsereF = $pdo->prepare("
                        INSERT INTO Postagens_Midia (id_postagem, url_midia, tipo_midia, salvo_na_galeria, data_criacao) 
                        VALUES (?, ?, 'imagem', 1, NOW())
                    ");
                    $stmtInsereF->execute([$id_postagem, $url_banco]);
                }
            }
        }
    }

    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Anúncio atualizado com sucesso!',
        'redirect' => $config['base_path'] . 'marketplace/meus-anuncios'
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}