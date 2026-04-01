<?php
/**
 * templates/post/menu_opcoes.php
 * Componente: Menu de Opções (Três Pontinhos).
 * VERSÃO: V6.7 (Sincronização de Atributos de Dados - socialbr.lol)
 * Responsabilidade: Gerir ações de Salvar, Editar, Excluir e Denunciar.
 * DADOS: $post_id, $autor_id e $post (Vindos do post_template.php)
 */

$user_id_logado = $_SESSION['user_id'] ?? 0;
$is_author = ($user_id_logado > 0 && $autor_id == $user_id_logado);

/**
 * LÓGICA DE RECONHECIMENTO DE SALVAMENTO:
 * Verificamos se o post já foi salvo para trocar o texto e o ícone.
 */
$foi_salvo = (($post['usuario_salvou'] ?? $post['salvo'] ?? 0) > 0);
?>

<style>
    /* Estilos específicos para o dropdown de opções do post */
    .post-options-container {
        position: relative;
    }

    .post-options-menu {
        position: absolute;
        right: 0;
        top: 35px;
        background: #fff;
        border: 1px solid #dddfe2;
        border-radius: 8px;
        box-shadow: 0 12px 28px 0 rgba(0, 0, 0, 0.2), 0 2px 4px 0 rgba(0, 0, 0, 0.1);
        padding: 8px;
        width: 190px;
        z-index: 1000;
    }

    .post-options-menu a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 12px;
        color: #050505;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
        border-radius: 6px;
        transition: background 0.2s;
    }

    .post-options-menu a:hover {
        background-color: #f2f2f2;
    }

    .post-options-menu i {
        width: 20px;
        text-align: center;
        font-size: 1rem;
    }

    /* Estilização suave para ações críticas */
    .post-options-menu a.post-report-btn {
        color: #ff5c5c !important;
    }
    .post-options-menu a.post-delete-btn {
        color: #dc3545 !important;
    }
    
    .is-hidden { display: none !important; }
</style>

<div class="post-options-container">
    
    <button type="button" 
            class="post-options-btn" 
            data-postid="<?php echo $post_id; ?>" 
            aria-label="Opções da publicação"
            onclick="toggleMenuOpcoes(event, <?php echo $post_id; ?>)"
            style="background: none; border: none; color: #65676b; cursor: pointer; font-size: 1.1rem; padding: 8px; border-radius: 50%;">
        <i class="fas fa-ellipsis-h"></i>
    </button>

    <div class="post-options-menu is-hidden" id="post-options-menu-<?php echo $post_id; ?>">
        
        <?php if ($user_id_logado > 0): ?>
            <?php if (!$foi_salvo): ?>
                <a href="javascript:void(0)" 
                   class="post-save-trigger" 
                   data-postid="<?php echo $post_id; ?>"
                   data-saved="0">
                    <i class="far fa-bookmark"></i> 
                    <span class="save-text">Salvar Publicação</span>
                </a>
            <?php else: ?>
                <a href="javascript:void(0)" 
                   class="post-save-trigger text-danger" 
                   data-postid="<?php echo $post_id; ?>"
                   data-saved="1">
                    <i class="fas fa-bookmark"></i> 
                    <span class="save-text">Remover dos Salvos</span>
                </a>
            <?php endif; ?>
            <hr style="border: 0; border-top: 1px solid #dddfe2; margin: 4px 0;">
        <?php endif; ?>

        <?php if ($is_author): ?>
            <a href="javascript:void(0)" class="post-edit-btn" data-postid="<?php echo $post_id; ?>">
                <i class="fas fa-edit"></i> Editar Publicação
            </a>

            <a href="javascript:void(0)" class="post-delete-btn" data-postid="<?php echo $post_id; ?>">
                <i class="fas fa-trash-alt"></i> Mover para a Lixeira
            </a>

        <?php elseif ($user_id_logado > 0): ?>
            <a href="javascript:void(0)" 
               class="post-report-btn" 
               data-content-type="post" 
               data-content-id="<?php echo $post_id; ?>" 
               data-postid="<?php echo $post_id; ?>">
                <i class="fas fa-flag"></i> Denunciar Publicação
            </a>
            
            <a href="javascript:void(0)" class="post-hide-btn" data-postid="<?php echo $post_id; ?>">
                <i class="fas fa-eye-slash"></i> Ocultar Publicação
            </a>
        <?php endif; ?>
        
    </div>
</div>

<script>
/**
 * Motor de Exibição do Menu de Opções
 */
if (typeof window.toggleMenuOpcoes !== 'function') {
    window.toggleMenuOpcoes = function(event, postId) {
        if (event) event.stopPropagation();

        const menu = document.getElementById('post-options-menu-' + postId);
        if (!menu) return;

        const isHidden = menu.classList.contains('is-hidden');
        
        document.querySelectorAll('.post-options-menu').forEach(el => el.classList.add('is-hidden'));
        
        if (isHidden) {
            menu.classList.remove('is-hidden');
        }
    };
}

if (!window.postOptionsMenuHandlerSet) {
    window.addEventListener('click', function(e) {
        if (!e.target.closest('.post-options-container')) {
            document.querySelectorAll('.post-options-menu').forEach(el => el.classList.add('is-hidden'));
        }
    });
    window.postOptionsMenuHandlerSet = true;
}
</script>