<?php
/**
 * templates/post_poll.php
 * Componente isolado para renderização de Enquetes.
 * VERSÃO: V9.0 (Sincronizada com Masterplan V9 e enquetes.js)
 */

// Se não houver enquete no array do post, encerra o componente
if (empty($post['enquete'])) {
    return;
}

$enquete = $post['enquete'];
$enquete_id = (int)$enquete['id'];
$pergunta = $enquete['pergunta'];
$total_geral = (int)($enquete['total_geral_votos'] ?? 0);
$ja_votou = (bool)($enquete['usuario_ja_votou'] ?? false);
?>

<div class="post-poll-container" 
     data-enqueteid="<?php echo $enquete_id; ?>" 
     style="margin-top: 15px; background: #f0f2f5; border-radius: 8px; padding: 15px; transition: opacity 0.3s ease;">
    
    <div class="poll-question-title" style="font-weight: 700; color: #050505; margin-bottom: 12px; font-size: 1rem;">
        <?php echo htmlspecialchars($pergunta); ?>
    </div>

    <div class="poll-options-list" style="display: flex; flex-direction: column; gap: 8px;">
        <?php foreach ($enquete['opcoes'] as $opcao): 
            $opcao_id = (int)$opcao['id'];
            $texto_opcao = $opcao['opcao_texto'];
            $votos_opcao = (int)($opcao['total_votos'] ?? 0);
            $usuario_votou_nesta = (bool)($opcao['usuario_votou'] ?? false);
            
            // Cálculo de percentagem seguro para evitar divisão por zero
            $percent = ($total_geral > 0) ? round(($votos_opcao / $total_geral) * 100) : 0;
        ?>
            <div class="poll-option-item <?php echo $usuario_votou_nesta ? 'voted' : ''; ?>" 
                 data-optionid="<?php echo $opcao_id; ?>"
                 style="position: relative; overflow: hidden; border: 1px solid #ccd0d5; border-radius: 6px; cursor: pointer; background: #fff;">
                
                <?php if ($ja_votou): ?>
                    <div class="poll-bar-bg" style="position: absolute; top: 0; left: 0; height: 100%; background: #e4e6eb; width: 100%; z-index: 1;"></div>
                    <div class="poll-bar-fill" style="position: absolute; top: 0; left: 0; height: 100%; background: <?php echo $usuario_votou_nesta ? '#1877f2' : '#bcc0c4'; ?>; width: <?php echo $percent; ?>%; z-index: 2; transition: width 0.5s ease;"></div>
                    
                    <div class="poll-option-info" style="position: relative; z-index: 3; padding: 10px 12px; display: flex; justify-content: space-between; align-items: center; font-weight: 600; font-size: 0.9rem; color: <?php echo ($percent > 50 || $usuario_votou_nesta) ? '#fff' : '#050505'; ?>;">
                        <span><?php echo htmlspecialchars($texto_opcao); ?></span>
                        <strong><?php echo $percent; ?>%</strong>
                    </div>
                <?php else: ?>
                    <button class="poll-vote-btn" 
                            data-optionid="<?php echo $opcao_id; ?>"
                            style="width: 100%; padding: 10px; border: none; background: transparent; text-align: left; font-size: 0.95rem; color: #050505; cursor: pointer; transition: background 0.2s; font-weight: 500;">
                        <?php echo htmlspecialchars($texto_opcao); ?>
                    </button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="poll-footer" style="margin-top: 10px; border-top: 1px solid #ccd0d5; padding-top: 8px;">
        <small style="color: #65676b; font-size: 0.8rem; display: flex; align-items: center; gap: 5px;">
            <i class="fas fa-users"></i> 
            <span class="total-votes-count"><?php echo $total_geral; ?></span> votos · 
            <span class="poll-status-text"><?php echo $ja_votou ? 'Clique na sua opção para cancelar ou mudar' : 'Clique para votar'; ?></span>
        </small>
    </div>
</div>