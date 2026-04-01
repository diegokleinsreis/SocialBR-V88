<?php
/**
 * ARQUIVO: api/usuarios/upload_avatar.php
 * PAPEL: Processar o upload, redimensionamento e renomeação ultra-identificada da foto de perfil.
 * VERSÃO: 3.2 - Padrão ID_NOME_DATA_TIPO (socialbr.lol)
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
        die("Erro de segurança: Token inválido. Recarregue a página e tente novamente.");
    }

    $user_id = $_SESSION['user_id'];

    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
        $arquivo_enviado = $_FILES['foto_perfil'];
        
        // Validamos a extensão de ENTRADA
        $extensao_origem = strtolower(pathinfo($arquivo_enviado['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extensao_origem, ['jpg', 'jpeg', 'png', 'gif'])) { 
            die("Erro: Apenas arquivos de imagem (JPG, PNG, GIF) são permitidos."); 
        }

        if ($arquivo_enviado['size'] > 5000000) { 
            die("Erro: O arquivo é muito grande (máx 5MB)."); 
        }

        // 2. --- [OBTER DADOS PARA O NOME HUMANIZADO] ---
        $sql_user = "SELECT nome FROM Usuarios WHERE id = ? LIMIT 1";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $res_user = $stmt_user->get_result();
        $user_data = $res_user->fetch_assoc();
        
        $nome_bruto = $user_data['nome'] ?? 'usuario';
        $nome_limpo = sanitizarParaFicheiro($nome_bruto);
        $data_hora  = date('Y-m-d_H-i-s');
        
        // DEFINIÇÃO DO TIPO (Para o padrão solicitado: id+nome+data+hora+tipo)
        $tipo = "avatar";

        // 3. --- [DEFINIÇÃO DO NOVO NOME E ROTAS] ---
        // Padrão Final: 15_joao-silva_2026-03-26_18-30-05_avatar.webp
        $novo_nome_arquivo = "{$user_id}_{$nome_limpo}_{$data_hora}_{$tipo}.webp";
        
        // Caminho Físico (Onde o PHP grava)
        $caminho_destino = __DIR__ . "/../../midias/perfil/fotos/" . $novo_nome_arquivo;
        
        // Caminho Relativo (O que vai para a Base de Dados)
        $url_para_db = "midias/perfil/fotos/" . $novo_nome_arquivo;
        
        // 4. --- [PROCESSAMENTO E SALVAMENTO] ---
        // Redimensiona para quadrado 200x200 e converte para .webp
        if (process_and_save_image($arquivo_enviado['tmp_name'], $caminho_destino, 'crop_to_square', 200)) {
            
            // 5. --- [ATUALIZAÇÃO NO BANCO DE DADOS] ---
            $sql = "UPDATE Usuarios SET foto_perfil_url = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $url_para_db, $user_id);
            
            if ($stmt->execute()) {
                header("Location: " . $config['base_path'] . "configurar_perfil?success=avatar");
                exit();
            } else {
                die("Erro ao atualizar o banco de dados.");
            }

        } else {
            die("Erro ao processar e salvar a imagem de perfil no novo diretório.");
        }
    } else {
        die("Erro no envio do arquivo: " . ($_FILES['foto_perfil']['error'] ?? 'Nenhum arquivo enviado'));
    }
} else {
    header("Location: " . $config['base_path'] . "perfil");
    exit();
}