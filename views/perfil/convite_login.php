<?php
/**
 * views/perfil/convite_login.php
 * Componente: Convite de Acesso (Visitantes).
 * PAPEL: Exibir prévia do perfil e incentivar o Login ou Cadastro.
 * VERSÃO: V1.1 (Estilos encapsulados em tag STYLE)
 */

// Variáveis recebidas do orquestrador (perfil.php):
// $perfil_data, $config
?>

<style>
    /* Layout da Área Pública (Baseado no monolito original) */
    .main-content-public {
        max-width: 600px;
        margin: 40px auto;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Cartão de Pré-visualização do Perfil */
    .profile-preview-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.2);
        padding: 30px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .public-avatar-wrapper {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        overflow: hidden;
        background-color: #f0f2f5;
        margin-bottom: 15px;
        border: 1px solid #ddd;
    }

    .public-avatar-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .public-avatar-wrapper i {
        font-size: 60px;
        color: #65676b;
        line-height: 120px;
    }

    .profile-preview-card h1 {
        font-size: 1.8rem;
        margin: 0;
        color: #050505;
    }

    .profile-preview-card p {
        color: #65676b;
        font-size: 1.1rem;
        margin: 5px 0 0 0;
    }

    /* Cartão de Prompt (CTA) */
    .public-prompt-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.2);
        padding: 30px;
        text-align: center;
    }

    .public-prompt-card h2 {
        font-size: 1.4rem;
        color: #050505;
        margin-bottom: 10px;
    }

    .public-prompt-card p {
        color: #65676b;
        margin-bottom: 25px;
        line-height: 1.4;
    }

    /* Botões de Ação Pública */
    .public-prompt-actions {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 15px;
    }

    .btn-public-primary {
        background-color: #0c2d54;
        color: white;
        width: 100%;
        max-width: 300px;
        padding: 12px;
        border-radius: 6px;
        font-weight: bold;
        text-decoration: none;
        transition: background 0.2s;
    }

    .btn-public-secondary {
        background-color: #f0f2f5;
        color: #050505;
        width: 100%;
        max-width: 300px;
        padding: 12px;
        border-radius: 6px;
        font-weight: bold;
        text-decoration: none;
        transition: background 0.2s;
    }

    .btn-public-primary:hover { background-color: #0a2444; }
    .btn-public-secondary:hover { background-color: #e4e6eb; }

    .separator-text {
        color: #65676b;
        font-size: 0.9rem;
    }
</style>

<div class="main-content-public">
    <div class="profile-preview-card">
        <div class="public-avatar-wrapper">
            <?php if (!empty($perfil_data['foto_perfil_url'])): ?>
                <img src="<?php echo $config['base_path'] . htmlspecialchars($perfil_data['foto_perfil_url']); ?>" alt="Avatar">
            <?php else: ?>
                <i class="fas fa-user"></i>
            <?php endif; ?>
        </div>
        <h1><?php echo htmlspecialchars(($perfil_data['nome'] ?? '') . ' ' . ($perfil_data['sobrenome'] ?? '')); ?></h1>
        <p>@<?php echo htmlspecialchars($perfil_data['nome_de_usuario'] ?? ''); ?></p>
    </div>

    <div class="public-prompt-card">
        <h2>Entre ou cadastre-se para ver o perfil completo</h2>
        <p>Conecte-se com os seus amigos, familiares e outras pessoas que você talvez conheça.</p>
        
        <div class="public-prompt-actions">
            <a href="<?php echo $config['base_path']; ?>login" class="btn-public-primary">Entrar</a> 
            
            <?php if (isset($config['permite_cadastro']) && $config['permite_cadastro'] == '1'): ?>
                <span class="separator-text">ou</span>
                <a href="<?php echo $config['base_path']; ?>cadastro" class="btn-public-secondary">Criar nova conta</a> 
            <?php endif; ?>
        </div>
    </div>
</div>