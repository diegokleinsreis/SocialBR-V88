<?php
/**
 * ARQUIVO: api/grupos/atualizar_capa.php
 * PAPEL: Processar a troca de capa do grupo com conversão WebP e nomenclatura humanizada.
 * VERSÃO: 3.5 - Estrutura /midias/grupos/ e Nomenclatura ID_NOME_DATA_TIPO (socialbr.lol)
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. --- [DEPENDÊNCIAS E SEGURANÇA] ---
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/GruposLogic.php';
require_once __DIR__ . '/../../utils/image_handler.php';

// Bloqueio de acesso não logado
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header("Location: " . $config['base_path'] . "grupos");
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

// 2. --- [VALIDAÇÃO CSRF] ---
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    http_response_code(403);
    die("Erro Crítico: Falha na validação de segurança (CSRF).");
}

$id_usuario_logado = (int)$_SESSION['user_id'];
$id_grupo = (int)($_POST['id_grupo'] ?? 0);

if ($id_grupo <= 0) {
    die("Erro: ID de grupo inválido.");
}

// 3. --- [VERIFICAÇÃO DE PERMISSÃO] ---
// Busca dados do grupo e nível de permissão do usuário
$grupo = GruposLogic::getGroupData($conn, $id_grupo, $id_usuario_logado);
$is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');

// Apenas o dono do grupo ou o administrador do sistema podem trocar a capa
if (!$grupo || ($grupo['nivel_permissao'] !== 'dono' && !$is_admin)) {
    die("Erro: Sem permissão para editar este grupo.");
}

// 4. --- [PROCESSAMENTO DA IMAGEM] ---
if (isset($_FILES['foto_capa']) && $_FILES['foto_capa']['error'] === UPLOAD_ERR_OK) {
    
    // Obter nome do usuário para a nomenclatura humanizada
    $sql_u = "SELECT nome FROM Usuarios WHERE id = ? LIMIT 1";
    $stmt_u = $conn->prepare($sql_u);
    $stmt_u->bind_param("i", $id_usuario_logado);
    $stmt_u->execute();
    $nome_bruto = $stmt_u->get_result()->fetch_assoc()['nome'] ?? 'usuario';
    $stmt_u->close();

    $nome_user_limpo = sanitizarParaFicheiro($nome_bruto);
    $data_hora = date('Y-m-d_H-i-s');
    $tipo = "capa"; // Identificador do tipo de arquivo

    // PADRÃO DEFINIDO: ID_Nome_Data_Hora_Tipo.webp
    $novo_nome_arquivo = "{$id_usuario_logado}_{$nome_user_limpo}_{$data_hora}_{$tipo}.webp";
    
    // Caminhos Físicos e de Banco
    $caminho_fisico = __DIR__ . "/../../midias/grupos/fotos/" . $novo_nome_arquivo;
    $url_para_db    = "midias/grupos/fotos/" . $novo_nome_arquivo;

    // Processamento: Redimensiona para 1200px de largura e converte para WebP
    if (process_and_save_image($_FILES['foto_capa']['tmp_name'], $caminho_fisico, 'resize_to_width', 1200)) {
        
        // 5. --- [ATUALIZAÇÃO NO BANCO DE DADOS] ---
        $sql = "UPDATE Grupos SET foto_capa_url = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $url_para_db, $id_grupo);
        
        if ($stmt->execute()) {
            $stmt->close();
            // Redirecionamento de sucesso
            header("Location: " . $config['base_path'] . "grupos/ver/" . $id_grupo . "?sucesso=capa");
            exit();
        } else {
            die("Erro ao atualizar a base de dados.");
        }
    } else {
        die("Erro ao processar e salvar a imagem na pasta midias/grupos/fotos/");
    }
}

die("Nenhum arquivo enviado ou erro no processamento do upload.");