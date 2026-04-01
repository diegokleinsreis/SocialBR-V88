<?php
/**
 * views/perfil/abas/aba_postagens.php
 * Componente: Aba de Publicações (Feed do Perfil).
 * PAPEL: Renderizar o formulário de postagem e a lista de posts do utilizador.
 * VERSÃO: V1.1 (Estilos encapsulados em tag STYLE)
 */

// Variáveis recebidas do orquestrador (perfil.php):
// $is_own_profile, $id_usuario_logado, $posts_para_exibir, $id_do_perfil_a_exibir
?>

<style>
    /* Estilos do Feed do Perfil (Baseados no layout original) */
    .profile-posts-feed {
        display: flex;
        flex-direction: column;
        gap: 20px;
        width: 100%;
    }

    /* Estilo para o Card de Estado Vazio */
    .empty-feed-card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        padding: 40px 20px;
        text-align: center;
    }

    .empty-feed-card p {
        color: #65676b;
        font-size: 1.1rem;
        margin: 0;
    }

    /* Ajuste para o formulário de postagem no topo do perfil */
    .profile-posts-feed .form-postagem-wrapper {
        margin-bottom: 10px;
    }

    /* Suporte ao Modo Escuro */
    .dark-mode .empty-feed-card {
        background-color: #242526;
        border-color: #3e4042;
    }

    .dark-mode .empty-feed-card p {
        color: #b0b3b8;
    }
</style>

<div class="profile-posts-feed">
    
    <?php if ($id_do_perfil_a_exibir == $id_usuario_logado): ?>
        <div class="form-postagem-wrapper">
            <?php 
            // Inclui o formulário de postagem padrão do sistema
            include __DIR__ . '/../../../templates/form_postagem.php'; 
            ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($posts_para_exibir)): ?>
        <?php foreach ($posts_para_exibir as $post): ?>
            <div class="post-card" id="post-<?php echo (int)$post['id']; ?>">
                <?php 
                // Prepara variável necessária para o post_template
                $user_id = $id_usuario_logado; 
                include __DIR__ . '/../../../templates/post_template.php'; 
                ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-feed-card">
            <p>
                <?php echo ($id_do_perfil_a_exibir == $id_usuario_logado) 
                    ? "Você ainda não publicou nada. Compartilhe algo com seus amigos!" 
                    : "Este utilizador ainda não publicou nada."; ?>
            </p>
        </div>
    <?php endif; ?>

</div>