<?php
/**
 * views/marketplace/componentes/barra_categorias.php
 * Componente: Navegação Horizontal de Categorias (Versão Limpa)
 * CSS centralizado em: assets/css/components/_marketplace.css
 */

if (!isset($id_usuario_logado)) {
    exit('Acesso restrito.');
}

// Carrega configurações se necessário
if (!isset($configMkt)) {
    $configMkt = require __DIR__ . '/../../../config/marketplace.php';
}

$categoria_atual = $_GET['categoria'] ?? '';
?>

<section class="mkt-category-bar">
    <div class="category-list-wrapper">
        
        <a href="<?php echo $config['base_path']; ?>marketplace" 
           class="category-item <?php echo empty($categoria_atual) ? 'active' : ''; ?>">
            <i class="fas fa-th-large"></i>
            <span>Tudo</span>
        </a>

        <?php foreach ($configMkt['categorias'] as $slug => $dados): ?>
            <a href="<?php echo $config['base_path']; ?>marketplace?categoria=<?php echo $slug; ?>" 
               class="category-item <?php echo ($categoria_atual === $slug) ? 'active' : ''; ?>">
                <i class="fas <?php echo $dados['icon']; ?>"></i>
                <span><?php echo $dados['label']; ?></span>
            </a>
        <?php endforeach; ?>

    </div>
</section>