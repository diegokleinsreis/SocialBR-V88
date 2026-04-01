<?php
/**
 * ARQUIVO: api/postagens/criar_post.php
 * PAPEL: Criar publicações com suporte a múltiplas mídias, enquetes e links.
 * VERSÃO: 3.5 - Nova Estrutura /midias/feed/ e Nomenclatura Humanizada (socialbr.lol)
 */

session_start();

// Define o cabeçalho da resposta como JSON
header('Content-Type: application/json; charset=utf-8');

/**
 * FUNÇÃO AUXILIAR: Resposta de erro padronizada
 */
function error_response($message, $type = 'validacao') {
    echo json_encode(['success' => false, 'error' => $type, 'message' => $message]);
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

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    error_response("Acesso negado. Por favor, faça o login.", 'auth');
}

// 1. --- [DEPENDÊNCIAS] ---
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../utils/image_handler.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- SEGURANÇA CSRF ---
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        http_response_code(403);
        error_response("Token de segurança inválido.", 'csrf');
    }

    $id_usuario = $_SESSION['user_id'];
    $conteudo_texto = trim($_POST['conteudo_texto'] ?? '');
    $salvo_na_galeria = isset($_POST['salvar_galeria']) ? 1 : 0;
    $privacidade_escolhida = $_POST['privacidade'] ?? 'publico';
    $id_grupo = (int)($_POST['id_grupo'] ?? 0);

    // Dados de Link e Enquete
    $link_url    = $_POST['link_url'] ?? null;
    $link_title  = $_POST['link_title'] ?? null;
    $link_image  = $_POST['link_image'] ?? null;
    $link_desc   = $_POST['link_description'] ?? null;
    $poll_question = trim($_POST['poll_question'] ?? '');
    $poll_options  = $_POST['poll_options'] ?? [];

    // 2. --- [VERIFICAÇÃO DE STATUS E DADOS DO USUÁRIO] ---
    $sql_u = "SELECT nome, email_verificado, perfil_privado FROM Usuarios WHERE id = ? LIMIT 1";
    $stmt_u = $conn->prepare($sql_u);
    $stmt_u->bind_param("i", $id_usuario);
    $stmt_u->execute();
    $user_data = $stmt_u->get_result()->fetch_assoc();
    $stmt_u->close();

    if (!$user_data) error_response("Usuário não encontrado.");

    $is_verificado = ((int)$user_data['email_verificado'] === 1);
    $nome_limpo = sanitizarParaFicheiro($user_data['nome']);
    
    // Detectamos mídias e enquetes
    $tem_arquivos = (isset($_FILES['post_media']) && !empty($_FILES['post_media']['name'][0]));
    $tem_enquete  = (!empty($poll_question) && count(array_filter($poll_options)) >= 2);

    // Trava de Verificação de E-mail para mídias
    if ($tem_arquivos && !$is_verificado) {
        error_response("Confirme seu e-mail para postar fotos e vídeos.", 'verificacao_pendente');
    }

    // Lógica de Privacidade
    $privacidade_final = ($user_data['perfil_privado'] == 1) ? 'amigos' : $privacidade_escolhida;
    $toast_message = ($user_data['perfil_privado'] == 1 && $privacidade_escolhida === 'publico') 
                     ? "Perfil privado: publicação partilhada apenas com amigos." : null;

    if (empty($conteudo_texto) && !$tem_arquivos && !$tem_enquete && empty($link_url)) {
        error_response("A postagem está vazia.");
    }

    // 3. --- [TRANSAÇÃO DE BANCO DE DADOS] ---
    $conn->begin_transaction();

    try {
        $val_grupo = ($id_grupo > 0) ? $id_grupo : null;
        
        $sql_post = "INSERT INTO Postagens (id_usuario, conteudo_texto, privacidade, status, id_grupo) VALUES (?, ?, ?, 'ativo', ?)";
        $stmt_post = $conn->prepare($sql_post);
        $stmt_post->bind_param("issi", $id_usuario, $conteudo_texto, $privacidade_final, $val_grupo);
        
        if (!$stmt_post->execute()) throw new Exception("Erro ao criar postagem.");
        $post_id = $conn->insert_id;
        $stmt_post->close();

        // [METADADOS DE LINK]
        if (!empty($link_url)) {
            $sql_meta = "INSERT INTO Post_Meta (post_id, meta_key, meta_value) VALUES (?, ?, ?)";
            $stmt_meta = $conn->prepare($sql_meta);
            $meta_data = ['link_url' => $link_url, 'link_title' => $link_title, 'link_image' => $link_image, 'link_desc' => $link_desc];
            foreach ($meta_data as $key => $val) {
                if (!empty($val)) {
                    $stmt_meta->bind_param("iss", $post_id, $key, $val);
                    $stmt_meta->execute();
                }
            }
            $stmt_meta->close();
        }

        // [ENQUETE]
        if ($tem_enquete) {
            $sql_poll = "INSERT INTO Enquetes (post_id, pergunta) VALUES (?, ?)";
            $stmt_poll = $conn->prepare($sql_poll);
            $stmt_poll->bind_param("is", $post_id, $poll_question);
            $stmt_poll->execute();
            $enquete_id = $conn->insert_id;
            $stmt_poll->close();

            $sql_opt = "INSERT INTO Enquete_Opcoes (enquete_id, opcao_texto) VALUES (?, ?)";
            $stmt_opt = $conn->prepare($sql_opt);
            foreach ($poll_options as $opcao) {
                $opcao = trim($opcao);
                if (!empty($opcao)) {
                    $stmt_opt->bind_param("is", $enquete_id, $opcao);
                    $stmt_opt->execute();
                }
            }
            $stmt_opt->close();
        }

        // 4. --- [PROCESSAR MÍDIAS - NOVO SISTEMA] ---
        if ($tem_arquivos) {
            $files = $_FILES['post_media'];
            $file_count = count($files['name']);
            if ($file_count > 3) throw new Exception("Máximo de 3 arquivos permitidos.");

            $data_hora = date('Y-m-d_H-i-s');
            $tipo_nome = "postagem";

            for ($i = 0; $i < $file_count; $i++) {
                if ($files['error'][$i] !== 0) continue;

                $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                // PADRÃO: ID_Nome_Data_Tipo_Index
                $nome_base = "{$id_usuario}_{$nome_limpo}_{$data_hora}_{$tipo_nome}_{$i}";
                
                $url_db = null; 
                $tipo_midia = null;

                // --- LÓGICA PARA FOTOS ---
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                    $caminho_foto = __DIR__ . "/../../midias/feed/fotos/" . $nome_base . ".webp";
                    if (process_and_save_image($files['tmp_name'][$i], $caminho_foto, 'resize_to_width', 1080)) {
                        $url_db = "midias/feed/fotos/" . $nome_base . ".webp";
                        $tipo_midia = 'imagem';
                    }
                } 
                // --- LÓGICA PARA VÍDEOS ---
                elseif (in_array($ext, ['mp4', 'webm', 'mov'])) {
                    $caminho_video = __DIR__ . "/../../midias/feed/videos/" . $nome_base . "." . $ext;
                    if (move_uploaded_file($files['tmp_name'][$i], $caminho_video)) {
                        $url_db = "midias/feed/videos/" . $nome_base . "." . $ext;
                        $tipo_midia = 'video';
                    }
                }

                if ($url_db) {
                    $sql_m = "INSERT INTO Postagens_Midia (id_postagem, url_midia, tipo_midia, salvo_na_galeria) VALUES (?, ?, ?, ?)";
                    $stmt_m = $conn->prepare($sql_m);
                    $stmt_m->bind_param("issi", $post_id, $url_db, $tipo_midia, $salvo_na_galeria);
                    $stmt_m->execute();
                    $stmt_m->close();
                }
            }
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Publicação criada com sucesso!', 'toast_message' => $toast_message]);

    } catch (Exception $e) {
        $conn->rollback();
        error_response($e->getMessage());
    }

    $conn->close();
}