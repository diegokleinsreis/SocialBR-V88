<?php
/**
 * views/perfil/identidade_perfil.php
 * Componente: Avatar e Identidade Visual.
 * PAPEL: Exibir a foto de perfil com borda de destaque e botão de edição.
 * VERSÃO: V1.3 (Refinamento Visual conforme Masterplan)
 */

// Definição da imagem de avatar com fallback para o ícone padrão
$avatar_src = !empty($perfil_data['foto_perfil_url']) 
    ? $config['base_path'] . htmlspecialchars($perfil_data['foto_perfil_url']) 
    : '';
?>

<style>
    /* Container principal do Avatar com destaque branco */
    .profile-avatar-wrapper {
        width: 168px;
        height: 168px;
        border-radius: 50%;
        background-color: #fff;
        border: 4px solid #fff; /* Borda branca para o overlap na capa */
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: visible; /* Permite que o botão de edição saia levemente da borda */
        flex-shrink: 0;
        margin-right: 20px;
    }

    .profile-avatar-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    /* Ícone fallback caso não haja foto */
    .profile-avatar-wrapper i.fa-user {
        font-size: 80px;
        color: #65676b;
    }

    /* Botão Flutuante de Edição (Câmera) */
    .edit-avatar-trigger {
        position: absolute;
        bottom: 8px;
        right: 8px;
        background-color: #e4e6eb;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #050505;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        cursor: pointer;
        transition: background-color 0.2s, transform 0.1s;
        z-index: 5;
    }

    .edit-avatar-trigger:hover {
        background-color: #d8dbde;
        transform: scale(1.05);
    }

    .edit-avatar-trigger i {
        font-size: 1.1rem;
    }

    /* Ajustes para Mobile (Conforme image_a788e9.png) */
    @media (max-width: 768px) {
        .profile-avatar-wrapper {
            width: 150px;
            height: 150px;
            margin-right: 0;
            /* O margin-top negativo de -85px é controlado pelo container pai perfil.php */
        }
        
        .profile-avatar-wrapper i.fa-user {
            font-size: 70px;
        }

        .edit-avatar-trigger {
            width: 32px;
            height: 32px;
            bottom: 5px;
            right: 5px;
        }
    }
</style>

<div class="profile-avatar-wrapper" id="profile-avatar-container">
    
    <?php if (!empty($avatar_src)): ?>
        <img src="<?php echo $avatar_src; ?>" 
             alt="Foto de Perfil de <?php echo htmlspecialchars($perfil_data['nome']); ?>"
             id="main-profile-avatar">
    <?php else: ?>
        <i class="fas fa-user" id="avatar-placeholder-icon"></i>
    <?php endif; ?>

    <?php if ($is_own_profile): ?>
        <div class="edit-avatar-trigger" onclick="document.getElementById('upload-avatar').click();" title="Alterar foto de perfil">
            <i class="fas fa-camera"></i>
        </div>
        
        <form action="<?php echo $config['base_path']; ?>acoes/perfil_avatar.php" method="POST" enctype="multipart/form-data" id="form-upload-avatar" style="display: none;">
            <input type="file" name="foto_perfil" id="upload-avatar" accept="image/*" onchange="document.getElementById('form-upload-avatar').submit();">
        </form>
    <?php endif; ?>

</div>