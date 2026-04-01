<?php
/**
 * ARQUIVO: api/grupos/processar_criacao.php
 * PAPEL: Processar a criação de novos grupos, validando segurança e mídias.
 * VERSÃO: 3.5 - Estrutura /midias/grupos/ e Nomenclatura Humanizada (socialbr.lol)
 */

session_start();

// Define o cabeçalho da resposta como JSON
header('Content-Type: application/json; charset=utf-8');

/**
 * FUNÇÃO AUXILIAR: Resposta de erro padronizada
 */
function json_err($msg, $type = 'erro') {
    echo json_encode(['success' => false, 'error' => $type, 'message' => $msg]);
    exit();
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

// 1. --- [VERIFICAÇÕES INICIAIS] ---
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../utils/image_handler.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    json_err("Acesso negado. Por favor, realize o login.");
}

// 2. --- [SEGURANÇA CSRF] ---
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    http_response_code(403);
    json_err("Falha na validação de segurança (CSRF).", 'csrf');
}

$id_usuario_logado = (int)$_SESSION['user_id'];

// 3. --- [OBTER DADOS DO USUÁRIO E STATUS DE VERIFICAÇÃO] ---
$sql_u = "SELECT nome, email_verificado FROM Usuarios WHERE id = ? LIMIT 1";
$stmt_u = $conn->prepare($sql_u);
$stmt_u->bind_param("i", $id_usuario_logado);
$stmt_u->execute();
$user_data = $stmt_u->get_result()->fetch_assoc();
$stmt_u->close();

if (!$user_data) json_err("Usuário não encontrado.");

$is_verificado = ((int)$user_data['email_verificado'] === 1);
$nome_user_limpo = sanitizarParaFicheiro($user_data['nome']);

// Trava de Verificação de E-mail
if (!$is_verificado) {
    json_err("Ação Bloqueada: Confirme seu e-mail para criar comunidades.", 'verificacao_pendente');
}

// 4. --- [CAPTURA E LIMPEZA DE DADOS DO GRUPO] ---
$nome_grupo  = trim($_POST['nome']);
$descricao   = trim($_POST['descricao']);
$privacidade = ($_POST['privacidade'] === 'privado') ? 'privado' : 'publico';

if (empty($nome_grupo)) {
    json_err("O nome do grupo é obrigatório.");
}

// 5. --- [PROCESSAMENTO DA FOTO DE CAPA DO GRUPO] ---
$foto_capa_url = 'assets/images/default-cover.jpg'; // Capa padrão
$tipo_nome = "grupo";
$data_hora = date('Y-m-d_H-i-s');

if (isset($_FILES['foto_capa']) && $_FILES['foto_capa']['error'] === UPLOAD_ERR_OK) {
    
    // PADRÃO: ID_Nome_Data_Tipo.webp
    $novo_nome_arquivo = "{$id_usuario_logado}_{$nome_user_limpo}_{$data_hora}_{$tipo_nome}.webp";
    
    // Caminhos
    $caminho_fisico = __DIR__ . "/../../midias/grupos/fotos/" . $novo_nome_arquivo;
    $url_para_db    = "midias/grupos/fotos/" . $novo_nome_arquivo;

    // Processamento: Redimensiona para 1200px de largura (padrão de capa) e converte para WebP
    if (process_and_save_image($_FILES['foto_capa']['tmp_name'], $caminho_fisico, 'resize_to_width', 1200)) {
        $foto_capa_url = $url_para_db;
    }
}

// 6. --- [TRANSAÇÃO DE BANCO DE DADOS] ---
$conn->begin_transaction();

try {
    // A. Inserir o Grupo
    $sqlGrupo = "INSERT INTO Grupos (nome, descricao, privacidade, foto_capa_url, id_dono) VALUES (?, ?, ?, ?, ?)";
    $stmtG = $conn->prepare($sqlGrupo);
    $stmtG->bind_param("ssssi", $nome_grupo, $descricao, $privacidade, $foto_capa_url, $id_usuario_logado);
    $stmtG->execute();
    
    $id_novo_grupo = $conn->insert_id;
    $stmtG->close();

    // B. Inserir o Criador como Membro Nível 'dono'
    $sqlMembro = "INSERT INTO Grupos_Membros (id_grupo, id_usuario, nivel_permissao) VALUES (?, ?, 'dono')";
    $stmtM = $conn->prepare($sqlMembro);
    $stmtM->bind_param("ii", $id_novo_grupo, $id_usuario_logado);
    $stmtM->execute();
    $stmtM->close();

    $conn->commit();

    // Retorna sucesso e a URL de redirecionamento
    echo json_encode([
        'success' => true, 
        'redirect' => $config['base_path'] . "grupos/ver/" . $id_novo_grupo . "?sucesso=criado"
    ]);

} catch (Exception $e) {
    $conn->rollback();
    json_err("Erro ao criar grupo: " . $e->getMessage());
}

$conn->close();