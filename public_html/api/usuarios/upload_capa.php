<?php
/**
 * ARQUIVO: api/usuarios/upload_capa.php
 * PAPEL: Processar o upload, redimensionamento e renomeação ultra-identificada da foto de capa.
 * VERSÃO: 3.2 - Padrão ID_NOME_DATA_TIPO e Estrutura /midias/ (socialbr.lol)
 */

session_start();

if (!isset($_SESSION['user_id'])) {
    die("Acesso negado.");
}

// 1. --- [DEPENDÊNCIAS] ---
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../utils/image_handler.php';

header('Content-Type: text/html; charset=utf-8');

/**
 * FUNÇÃO AUXILIAR: Sanitizar nomes para ficheiros
 * Transforma "João Silva" em "joao-silva"
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
    $string = preg_replace('/[^a-z0-9\-]/', '-', $string); // Remove o que não for letra ou número
    $string = preg_replace('/-+/', '-', $string);         // Remove traços duplos
    return trim($string, '-');                             // Limpa as pontas
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- BLOCO DE SEGURANÇA: VERIFICAÇÃO CSRF ---
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        http_response_code(403);
        die("Erro de segurança: Token inválido. Recarregue a página.");
    }

    $user_id = $_SESSION['user_id'];

    if (isset($_FILES['foto_capa']) && $_FILES['foto_capa']['error'] == 0) {
        $arquivo_enviado = $_FILES['foto_capa'];
        
        // Validamos a extensão de ENTRADA
        $extensao_origem = strtolower(pathinfo($arquivo_enviado['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extensao_origem, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            die("Erro: Formato de arquivo não suportado (apenas JPG, PNG, GIF ou WebP).");
        }

        // Limite de tamanho: 5MB
        if ($arquivo_enviado['size'] > 5000000) { 
            die("Erro: O arquivo é muito grande (máx 5MB)."); 
        }

        // 2. --- [OBTER DADOS PARA O NOME HUMANIZADO] ---
        // Buscamos o nome do utilizador no banco
        $sql_user = "SELECT nome FROM Usuarios WHERE id = ? LIMIT 1";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $res_user = $stmt_user->get_result();
        $user_data = $res_user->fetch_assoc();
        
        $nome_bruto = $user_data['nome'] ?? 'usuario';
        $nome_limpo = sanitizarParaFicheiro($nome_bruto);
        $data_hora  = date('Y-m-d_H-i-s');
        
        // DEFINIÇÃO DO TIPO
        $tipo = "capa";

        // 3. --- [DEFINIÇÃO DO NOVO NOME E ROTAS] ---
        // Padrão: 15_joao-silva_2026-03-26_18-30-05_capa.webp
        $novo_nome_arquivo = "{$user_id}_{$nome_limpo}_{$data_hora}_{$tipo}.webp";
        
        // Caminho Físico: Subimos para a raiz e entramos em midias/perfil/fotos/
        $caminho_destino = __DIR__ . "/../../midias/perfil/fotos/" . $novo_nome_arquivo;
        
        // Caminho Relativo para o DB
        $url_para_db = "midias/perfil/fotos/" . $novo_nome_arquivo;

        // 4. --- [PROCESSAMENTO DA IMAGEM] ---
        // Modo: 'resize_to_width' (1200px para capas) | Saída WebP
        if (process_and_save_image($arquivo_enviado['tmp_name'], $caminho_destino, 'resize_to_width', 1200)) {
            
            // 5. --- [ATUALIZAÇÃO NO BANCO DE DADOS] ---
            $sql = "UPDATE Usuarios SET foto_capa_url = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $url_para_db, $user_id);
            
            if ($stmt->execute()) {
                // Sucesso: Redireciona para configurar_perfil
                header("Location: " . $config['base_path'] . "configurar_perfil?success=capa");
                exit();
            } else {
                die("Erro ao atualizar o banco de dados.");
            }

        } else {
            die("Erro ao processar e salvar a imagem de capa no novo diretório.");
        }

    } else {
        die("Nenhum arquivo enviado ou erro no upload.");
    }
} else {
    header("Location: " . $config['base_path'] . "perfil");
    exit();
}