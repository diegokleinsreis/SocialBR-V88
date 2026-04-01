<?php
/**
 * views/perfil/abas/aba_amigos.php
 * Componente: Aba de Listagem de Amigos.
 * PAPEL: Exibir a grade de conexões do utilizador com suporte a privacidade.
 * VERSÃO: V1.1 (Estilos encapsulados em tag STYLE)
 */

// Variáveis recebidas do orquestrador (perfil.php):
// $perfil_data, $pode_ver_lista_amigos, $lista_amigos, $config
?>

<style>
    /* Estilos da Seção de Amigos (Baseados no _profile.css original) */
    .friends-section-container {
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

    /* Grid de Amigos */
    .friends-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 15px;
        width: 100%;
    }

    /* Card Individual de Amigo */
    .friend-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        text-align: center;
        padding: 20px 15px;
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid #f0f2f5;
    }

    .friend-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .friend-card a {
        text-decoration: none;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .friend-avatar {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        margin-bottom: 12px;
        object-fit: cover;
        border: 3px solid #f0f2f5;
    }

    .friend-name {
        font-weight: 700;
        font-size: 0.95rem;
        color: #050505;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        width: 100%;
    }

    .friend-username {
        font-size: 0.8rem;
        color: #65676b;
        margin-top: 4px;
    }

    /* Estados de Erro/Privacidade */
    .friends-status-card {
        background: #fff;
        border-radius: 8px;
        padding: 40px 20px;
        text-align: center;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .friends-status-card i {
        font-size: 3rem;
        color: #65676b;
        margin-bottom: 15px;
    }

    .friends-status-card h3 {
        color: #050505;
        margin-bottom: 10px;
    }

    /* Responsividade */
    @media (max-width: 480px) {
        .friends-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .friend-avatar {
            width: 70px;
            height: 70px;
        }
    }
</style>

<div class="friends-section-container">
    <div class="page-section-header">
        <h1>Amigos de <?php echo htmlspecialchars($perfil_data['nome']); ?></h1>
    </div>

    <?php if ($pode_ver_lista_amigos): ?>
        <?php if (!empty($lista_amigos)): ?>
            <div class="friends-grid">
                <?php foreach ($lista_amigos as $amigo): ?>
                    <div class="friend-card">
                        <a href="<?php echo $config['base_path']; ?>perfil/<?php echo (int)$amigo['id']; ?>">
                            <?php
                            $avatar_amigo_src = !empty($amigo['foto_perfil_url'])
                                ? $config['base_path'] . htmlspecialchars($amigo['foto_perfil_url'])
                                : $config['base_path'] . 'assets/images/default-avatar.png';
                            ?>
                            <img src="<?php echo $avatar_amigo_src; ?>" 
                                 alt="Foto de <?php echo htmlspecialchars($amigo['nome']); ?>" 
                                 class="friend-avatar"
                                 onerror="this.src='<?php echo $config['base_path']; ?>assets/images/default-avatar.png'">
                            
                            <p class="friend-name">
                                <?php echo htmlspecialchars($amigo['nome'] . ' ' . $amigo['sobrenome']); ?>
                            </p>
                            <p class="friend-username">
                                @<?php echo htmlspecialchars($amigo['nome_de_usuario']); ?>
                            </p>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="friends-status-card">
                <p><?php echo htmlspecialchars($perfil_data['nome']); ?> ainda não tem amigos adicionados.</p>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="friends-status-card">
            <i class="fas fa-user-friends"></i>
            <h3>Lista de amigos privada</h3>
            <p>Apenas amigos de <?php echo htmlspecialchars($perfil_data['nome']); ?> podem ver esta lista.</p>
        </div>
    <?php endif; ?>
</div>