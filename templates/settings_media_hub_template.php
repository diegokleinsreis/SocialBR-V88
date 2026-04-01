<?php
/**
 * View Modular: Centro de Mídia Visual (V65.5 - Suporte a Capa Padrão)
 * Gerencia Foto de Perfil e Capa de forma intuitiva na página de configurações.
 */

// Define URL do Avatar (Usa padrão se estiver vazio)
$foto_perfil = !empty($user_data['foto_perfil_url'])
    ? $config['base_path'] . htmlspecialchars($user_data['foto_perfil_url'])
    : $config['base_path'] . 'assets/images/default-avatar.png.png';

// --- [LÓGICA DE CAPA PADRÃO] ---
// Se o utilizador não tiver uma capa personalizada, carregamos a imagem padrão
$foto_capa = !empty($user_data['foto_capa_url']) 
    ? $config['base_path'] . htmlspecialchars($user_data['foto_capa_url']) 
    : $config['base_path'] . 'assets/images/default-cover.jpg';

// Pega a posição vertical da capa para a pré-visualização
$posicao_y = isset($user_data['capa_posicao_y']) ? (int)$user_data['capa_posicao_y'] : 50;
?>

<div class="settings-media-hub">
    <div class="settings-cover-preview" style="background-image: url('<?php echo $foto_capa; ?>'); background-position: center <?php echo $posicao_y; ?>%;">
        
        <div class="settings-cover-overlay-top"></div>

        <div class="cover-actions-overlay">
            <form action="<?php echo $config['base_path']; ?>api/usuarios/upload_capa.php" method="POST" enctype="multipart/form-data" class="auto-upload-form">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                <input type="hidden" name="redirect_to" value="settings">
                
                <label for="hub-cover-input" class="hub-action-btn" title="Alterar Capa">
                    <i class="fas fa-camera"></i>
                </label>
                <input type="file" name="foto_capa" id="hub-cover-input" class="is-hidden" accept="image/*">
            </form>

            <?php 
            // O botão de remoção só aparece se a capa NÃO for a padrão
            if (!empty($user_data['foto_capa_url'])): 
            ?>
                <button type="button" class="hub-action-btn delete-btn" id="btn-remover-capa" title="Remover Capa">
                    <i class="fas fa-trash-alt"></i>
                </button>
            <?php endif; ?>
        </div>

        <div class="settings-avatar-preview">
            <img src="<?php echo $foto_perfil; ?>" alt="Sua foto de perfil">
            
            <form action="<?php echo $config['base_path']; ?>api/usuarios/upload_avatar.php" method="POST" enctype="multipart/form-data" class="auto-upload-form">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                
                <label for="hub-avatar-input" class="avatar-edit-icon" title="Mudar Foto de Perfil">
                    <i class="fas fa-camera"></i>
                </label>
                <input type="file" name="foto_perfil" id="hub-avatar-input" class="is-hidden" accept="image/*">
            </form>
        </div>
    </div>
    
    <div class="hub-info-text">
        <h3>Sua identidade visual</h3>
        <p>Clique nos ícones de câmera para atualizar sua foto de perfil ou capa.</p>
    </div>
</div>