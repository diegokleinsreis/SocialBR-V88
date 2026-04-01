<?php
/**
 * templates/post/corpo_texto.php
 * Componente: Exibição do Texto da Postagem com Expansão.
 * VERSÃO: V6.1 (Integração com CensuraLogic - socialbr.lol)
 * Responsabilidade: Filtrar ofensas visualmente e gerir lógica de "ver mais".
 */

// --- APLICAÇÃO DA MÁSCARA SOCIAL (Higiene de Exibição) ---
// O objeto $censura é herdado do post_template.php ou instanciado se necessário
$texto_para_exibir = $conteudo_texto ?? '';
if (isset($censura)) {
    $texto_para_exibir = $censura->aplicarMascaraSocial($texto_para_exibir);
}
?>

<div class="post-text-container" style="padding: 0 15px 10px 15px; font-size: 1rem; line-height: 1.5; color: #050505;">
    <?php 
    // Formata o texto para exibição segura (mantendo a tradução dos símbolos)
    $texto_formatado = nl2br(htmlspecialchars($texto_para_exibir));
    
    // Verifica se o texto excede o limite e se não estamos em uma página de post único
    if (mb_strlen($texto_para_exibir) > $limite_chars && !isset($is_single_page)) {
        // Exibe versão curta (Mascarada)
        echo '<div class="post-content-short" id="post-content-short-'.$post_id.'">' . 
             mb_strimwidth($texto_formatado, 0, $limite_chars, "...") . 
             ' <a href="#" class="see-more-link" data-content-id="'.$post_id.'">ver mais</a></div>';
        
        /**
         * MANTIDO: Classe .is-hidden
         * O Super-Debug Admin detecta esta classe e exibe o texto oculto com borda laranja
         * para validar se o conteúdo foi carregado corretamente pelo PHP.
         */
        echo '<div class="post-content-full is-hidden" id="post-content-full-'.$post_id.'">'.$texto_formatado.'</div>';
    } else {
        // Texto curto ou página única exibe o parágrafo completo (Mascarado)
        echo '<p>'.$texto_formatado.'</p>';
    }
    ?>
</div>