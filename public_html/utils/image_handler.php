<?php

/**
 * Função centralizada para processar e guardar imagens enviadas.
 * Converte AUTOMATICAMENTE para WebP mantendo as dimensões lógicas.
 *
 * @param string $source_path Caminho do ficheiro temporário.
 * @param string $destination_path Caminho completo onde a nova imagem será guardada.
 * @param string $mode 'resize_to_width' ou 'crop_to_square'.
 * @param int $max_size Dimensão alvo (padrão 1080px).
 * @return bool Retorna true em sucesso, false em falha.
 */
function process_and_save_image($source_path, $destination_path, $mode = 'resize_to_width', $max_size = 1080) {
    
    // Pega as informações da imagem original
    list($width, $height, $type) = getimagesize($source_path);
    if (!$type) return false;

    // Carrega a imagem original para a memória
    $src_image = null;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $src_image = imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $src_image = imagecreatefrompng($source_path);
            break;
        case IMAGETYPE_GIF:
            $src_image = imagecreatefromgif($source_path);
            break;
        case IMAGETYPE_WEBP: // Adicionado suporte caso o PHP suporte ler WebP nativo
             $src_image = imagecreatefromwebp($source_path);
             break;
        default:
            return false; 
    }

    if (!$src_image) return false;

    // --- LÓGICA DE REDIMENSIONAMENTO (MANTIDA INTACTA) ---
    $new_width = $max_size;
    $new_height = $max_size;
    $source_x = 0;
    $source_y = 0;
    $source_w = $width;
    $source_h = $height;

    if ($mode === 'resize_to_width') {
        if ($width > $max_size) {
            $new_width = $max_size;
            $new_height = ($height / $width) * $max_size;
        } else {
            $new_width = $width;
            $new_height = $height;
        }
    } elseif ($mode === 'crop_to_square') {
        $source_w = min($width, $height);
        $source_h = min($width, $height);
        if ($width > $height) {
            $source_x = ($width - $height) / 2;
        } elseif ($height > $width) {
            $source_y = ($height - $width) / 2;
        }
    }
    // -----------------------------------------------------

    // Cria a nova imagem em branco
    $new_image = imagecreatetruecolor($new_width, $new_height);

    // --- LÓGICA DE TRANSPARÊNCIA (CRÍTICO PARA WEBP/PNG) ---
    // WebP suporta transparência, então preservamos o canal Alpha
    imagealphablending($new_image, false);
    imagesavealpha($new_image, true);
    $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
    imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);

    // Copia e redimensiona
    imagecopyresampled($new_image, $src_image, 0, 0, $source_x, $source_y, $new_width, $new_height, $source_w, $source_h);

    // --- SALVAMENTO EM WEBP ---
    // Qualidade 80 é um excelente equilíbrio entre tamanho e visual
    $success = imagewebp($new_image, $destination_path, 80);

    // Liberta a memória
    imagedestroy($src_image);
    imagedestroy($new_image);

    return $success;
}
?>