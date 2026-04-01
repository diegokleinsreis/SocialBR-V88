<?php
/**
 * templates/post/grade_midia.php
 * Componente: Orquestrador e Distribuidor de Grelha de Mídia.
 * VERSÃO: V4.0 (AI Orientation Detection)
 * RESPONSABILIDADE: Detectar orientação da imagem e definir layout.
 */

$midias = $post['midias'] ?? [];
$post_id = $post['id'] ?? 0;

if (empty($midias)) return;

$total_midias = count($midias);
$layout_direction = 'layout-h'; // Padrão seguro

/**
 * LÓGICA DE DETECÇÃO DE ORIENTAÇÃO (Heurística Facebook)
 * Se temos 3 fotos, analisamos a primeira para decidir o layout.
 */
if ($total_midias === 3) {
    $primeira_midia = $midias[0];
    
    // Construímos o caminho físico para o PHP ler o arquivo
    // Ajuste este caminho conforme a estrutura real do seu servidor
    $caminho_fisico = dirname(__DIR__, 2) . '/public_html/' . $primeira_midia['url_midia'];

    if (file_exists($caminho_fisico) && $primeira_midia['tipo_midia'] === 'imagem') {
        $dimensoes = getimagesize($caminho_fisico);
        if ($dimensoes) {
            $largura = $dimensoes[0];
            $altura = $dimensoes[1];

            // Se a altura for maior que a largura, o layout é Vertical (Destaque na Esquerda)
            if ($altura > $largura) {
                $layout_direction = 'layout-v';
            }
        }
    }
}

$grid_class = "grid-" . ($total_midias > 3 ? 3 : $total_midias);
?>

<div class="post-media-grid <?php echo $grid_class; ?> <?php echo $layout_direction; ?>">
    
    <?php foreach ($midias as $index => $midia): ?>
        <?php 
            if ($index >= 3) break; 
            
            $media_src = $config['base_path'] . htmlspecialchars($midia['url_midia']);
            $tipo_midia = $midia['tipo_midia'] ?? 'imagem';
            
            // O arquivo incluído abaixo deve conter o container 'media-item-container'
            if ($tipo_midia === 'imagem' || empty($tipo_midia)) {
                include __DIR__ . '/grade_fotos.php';
            } else {
                include __DIR__ . '/grade_video.php';
            }
        ?>
    <?php endforeach; ?>

</div>