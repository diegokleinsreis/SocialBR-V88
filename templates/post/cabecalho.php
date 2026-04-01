<?php
/**
 * templates/post/cabecalho.php
 * Componente: Cabeçalho do Post (Identidade do Autor e Metadados).
 * VERSÃO: V6.0 (Integração com Super-Debug Admin)
 * Inclui: menu_opcoes.php
 */

// Nota: As variáveis ($perfil_link, $avatar_src, $autor_nome, etc.) 
// já foram preparadas pelo orquestrador post_template.php.
?>

<div class="post-header" style="padding: 15px; display: flex; align-items: center; gap: 1px;">
    
    <div class="post-author-avatar" style="width: 42px; height: 42px; border-radius: 50%; overflow: hidden;">
        <a href="<?php echo $perfil_link; ?>">
            <img src="<?php echo $avatar_src; ?>" 
                 alt="Avatar" 
                 style="width: 100%; height: 100%; object-fit: cover;" 
                 onerror="this.src='<?php echo $config['base_path']; ?>assets/images/default-avatar.png'">
        </a>
    </div>

    <div class="post-author-info">
        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;"> 
            <a href="<?php echo $perfil_link; ?>" class="post-author-name-link" style="text-decoration: none; font-weight: 700; color: #050505;">
                <span class="post-author-name"><?php echo htmlspecialchars($autor_nome . ' ' . $autor_sobrenome); ?></span>
            </a> 
            
            <?php if ($is_share): ?>
                <span class="post-action-text" style="color: #65676b; font-size: 0.9rem;">compartilhou:</span>
            <?php endif; ?>

            <?php if ($tipo_post === 'venda' && !$is_share): ?>
                <i class="fas fa-shopping-bag" style="color: #1877f2; font-size: 0.9rem;" title="Anúncio"></i>
            <?php endif; ?>
        </div>
        
        <span class="post-timestamp" style="color: #65676b; font-size: 0.8rem;">
            <?php echo date("d/m/Y \à\s H:i", strtotime($post_data)); ?>
        </span>
    </div>
    
    <div class="post-options" style="margin-left: auto;">
        <?php include __DIR__ . '/menu_opcoes.php'; ?>
    </div>

</div>