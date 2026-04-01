<?php
/**
 * ARQUIVO: api/marketplace/criar_anuncio.php
 * PAPEL: Criar anúncio no Marketplace e postagem correspondente no Feed.
 * VERSÃO: 5.6 - Estrutura /midias/ e Caminhos de Inclusão Fixos (socialbr.lol)
 */

// 1. CONFIGURAÇÕES DE AMBIENTE E DIAGNÓSTICO
ini_set('display_errors', 0); 
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log_mkt.txt');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

$response = ['success' => false, 'message' => 'Erro desconhecido.'];

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

/**
 * FUNÇÃO AUXILIAR: Validação de CPF
 */
function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) return false;
    }
    return true;
}

try {
    // 2. IMPORTAÇÃO DE DEPENDÊNCIAS (Caminhos Diretos)
    // Sobe para api/ -> public_html/ -> raiz e entra em config/
    require_once __DIR__ . '/../../../config/database.php';
    // Sobe para api/ -> public_html/ -> entra em utils/
    require_once __DIR__ . '/../../utils/image_handler.php';

    // 3. SESSÃO E AUTH
    if (session_status() == PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_id'])) throw new Exception('Sessão expirada. Faça login novamente.');
    
    // Verificação CSRF
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        throw new Exception('Erro de segurança: Token inválido. Recarregue a página.');
    }

    $user_id = $_SESSION['user_id'];
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Método inválido.');

    // 4. CONEXÃO PDO (Mantendo compatibilidade)
    if (!isset($pdo)) {
        $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // 5. RECEBIMENTO E FILTRAGEM DE DADOS
    $titulo    = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
    $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);
    $categoria = filter_input(INPUT_POST, 'categoria', FILTER_SANITIZE_SPECIAL_CHARS);
    $condicao  = filter_input(INPUT_POST, 'condicao', FILTER_SANITIZE_SPECIAL_CHARS);
    $estado    = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_SPECIAL_CHARS);
    $cidade    = filter_input(INPUT_POST, 'cidade', FILTER_SANITIZE_SPECIAL_CHARS);
    $cpf_env   = $_POST['cpf'] ?? '';

    $precoRaw = str_replace(',', '.', str_replace('.', '', $_POST['preco'] ?? '0'));
    $preco = floatval($precoRaw);

    if (empty($titulo) || empty($categoria) || empty($condicao)) {
        throw new Exception('Preencha os campos obrigatórios.');
    }

    // 6. PROCESSAMENTO DO CPF E IDENTIDADE DO VENDEDOR
    $stmtMe = $pdo->prepare("SELECT nome, cpf FROM Usuarios WHERE id = ?");
    $stmtMe->execute([$user_id]);
    $user_row = $stmtMe->fetch(PDO::FETCH_ASSOC);
    $nome_user_limpo = sanitizarParaFicheiro($user_row['nome'] ?? 'vendedor');

    $cpf_limpo = preg_replace('/[^0-9]/', '', $cpf_env);
    if (!empty($cpf_limpo)) {
        if (!validarCPF($cpf_limpo)) throw new Exception('CPF inválido.');
        $meuCpf = $user_row['cpf'];

        if ($meuCpf !== $cpf_limpo) {
            $stmtDup = $pdo->prepare("SELECT id FROM Usuarios WHERE cpf = ? AND id != ?");
            $stmtDup->execute([$cpf_limpo, $user_id]);
            if ($stmtDup->rowCount() > 0) throw new Exception('Este CPF já está em uso por outro usuário.');
            
            $stmtUp = $pdo->prepare("UPDATE Usuarios SET cpf = ? WHERE id = ?");
            $stmtUp->execute([$cpf_limpo, $user_id]);
        }
    }

    // 7. CONFIGURAÇÃO DE ROTAS DE MÍDIA
    $pastaUpload = __DIR__ . '/../../midias/marketplace/fotos/';
    $caminhoBanco = 'midias/marketplace/fotos/';
    $data_hora = date('Y-m-d_H-i-s');

    if (!is_dir($pastaUpload)) mkdir($pastaUpload, 0755, true);

    if (!isset($_FILES['fotos']) || count($_FILES['fotos']['name']) == 0 || $_FILES['fotos']['error'][0] === UPLOAD_ERR_NO_FILE) {
        throw new Exception('Selecione pelo menos uma foto para o seu anúncio.');
    }

    // 8. TRANSAÇÃO E PERSISTÊNCIA
    $pdo->beginTransaction();

    try {
        // A) Criar Postagem no Feed (Integração Social)
        $conteudoPost = "🛒 ANÚNCIO: " . $titulo . "\n\n" . $descricao;
        $sqlPost = "INSERT INTO Postagens (id_usuario, conteudo_texto, tipo_media, privacidade, status, tipo_post, data_postagem) 
                    VALUES (:uid, :txt, 'imagem', 'publico', 'ativo', 'venda', NOW())";
        $stmtPost = $pdo->prepare($sqlPost);
        $stmtPost->execute([':uid' => $user_id, ':txt' => $conteudoPost]);
        $id_postagem = $pdo->lastInsertId();

        // B) Criar Dados do Anúncio no Marketplace
        $sqlMkt = "INSERT INTO Marketplace_Anuncios 
                   (id_postagem, titulo_produto, descricao_produto, preco, moeda, categoria, condicao, estado, cidade, status_venda, criado_em, atualizado_em) 
                   VALUES (?, ?, ?, ?, 'BRL', ?, ?, ?, ?, 'disponivel', NOW(), NOW())";
        $stmtMkt = $pdo->prepare($sqlMkt);
        $stmtMkt->execute([$id_postagem, $titulo, $descricao, $preco, $categoria, $condicao, $estado, $cidade]);

        // C) Salvar Fotos (Conversão WebP e Nomenclatura Humanizada)
        $countFotos = 0;
        $total = count($_FILES['fotos']['name']);
        
        $sqlMidia = "INSERT INTO Postagens_Midia (id_postagem, url_midia, tipo_midia, salvo_na_galeria, data_criacao) 
                     VALUES (?, ?, 'imagem', 1, NOW())";
        $stmtMidia = $pdo->prepare($sqlMidia);

        for ($i = 0; $i < $total; $i++) {
            if ($_FILES['fotos']['error'][$i] === UPLOAD_ERR_OK) {
                // PADRÃO: ID_Nome_Data_Hora_Index.webp
                $novoNome = "{$user_id}_{$nome_user_limpo}_{$data_hora}_{$i}.webp";
                
                // Redimensionamento e Conversão via image_handler
                if (process_and_save_image($_FILES['fotos']['tmp_name'][$i], $pastaUpload . $novoNome, 'resize_to_width', 1080)) {
                    $stmtMidia->execute([$id_postagem, $caminhoBanco . $novoNome]);
                    $countFotos++;
                }
            }
        }

        if ($countFotos === 0) throw new Exception("Não foi possível processar as imagens. Verifique se o formato é válido.");

        $pdo->commit();

        $response['success'] = true;
        $response['message'] = 'Anúncio publicado com sucesso!';
        $response['redirect'] = '../../marketplace'; 

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Erro no Marketplace: " . $e->getMessage());
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;