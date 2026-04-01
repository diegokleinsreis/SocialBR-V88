<?php
/**
 * views/perfil/abas/aba_fotos.php
 * Componente: Galeria de Fotos e Vídeos.
 * PAPEL: Exibir uma grade visual de todas as mídias publicadas pelo utilizador.
 * VERSÃO: V1.1 (Estilos encapsulados em tag STYLE)
 */

// Variáveis recebidas do orquestrador (perfil.php):
// $perfil_data, $galeria_midia, $config
?>

<style>
    /* Estilos da Galeria (Baseados no _profile.css original) */
    .gallery-container {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .page-section-header {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        padding: 20px;
        border-left: 4px solid #0c2d54;
    }

    .page-section-header h1 {
        font-size: 1.5rem;
        margin: 0;
        color: #050505;
    }

    /* Grid de Mídia - Proporção e Espaçamento */
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 8px; /* Espaçamento entre mídias original do design FB-style */
        background-color: #fff;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .gallery-item {
        position: relative;
        width: 100%;
        padding-top: 100%; /* Garante que todos os itens sejam quadrados (aspect-ratio 1:1) */
        overflow: hidden;
        background-color: #f0f2f5;
        cursor: pointer;
        border-radius: 4px;
        transition: opacity 0.2s ease;
    }

    .gallery-item:hover {
        opacity: 0.9;
    }

    .gallery-item img,
    .gallery-item video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover; /* Recorta a imagem para preencher o quadrado sem distorcer */
    }

    /* Overlay de Vídeo */
    .video-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 2.2rem;
        text-shadow: 0 2px 6px rgba(0,0,0,0.5);
        pointer-events: none;
        z-index: 2;
    }

    /* Mensagem de Galeria Vazia */
    .empty-gallery-msg {
        grid-column: 1 / -1;
        text-align: center;
        color: #65676b;
        padding: 50px 20px;
        font-size: 1.1rem;
    }

    /* Responsividade */
    @media (max-width: 600px) {
        .gallery-grid {
            grid-template-columns: repeat(3, 1fr); /* No mobile, 3 por linha é o padrão */
            gap: 4px;
            padding: 10px;
        }
    }

    /* Suporte ao Modo Escuro */
    .dark-mode .gallery-grid,
    .dark-mode .page-section-header {
        background-color: #242526;
        border-color: #3e4042;
    }
    
    .dark-mode .page-section-header h1 {
        color: #e4e6eb;
    }
</style>

<div class="gallery-container">
    <div class="page-section-header">
        <h1>Galeria de Fotos</h1>
    </div>

    <div class="gallery-grid">
        <?php if (!empty($galeria_midia)): ?>
            <?php foreach ($galeria_midia as $midia): ?>
                <?php 
                    $midia_url = $config['base_path'] . htmlspecialchars($midia['url_midia']); 
                ?>
                <div class="gallery-item post-image-clickable" data-postid="<?php echo (int)$midia['id_postagem']; ?>">
                    <?php if ($midia['tipo_midia'] === 'imagem'): ?>
                        <img src="<?php echo $midia_url; ?>" alt="Foto da galeria" loading="lazy">
                    <?php else: ?>
                        <video src="<?php echo $midia_url; ?>"></video>
                        <div class="video-overlay"><i class="fas fa-play"></i></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-gallery-msg">
                <p>Ainda não há fotos ou vídeos nesta galeria.</p>
            </div>
        <?php endif; ?>
    </div>
</div>