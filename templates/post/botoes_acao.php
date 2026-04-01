<?php
/**
 * templates/post/botoes_acao.php
 * Componente: Botões de Interação (Curtir, Comentar, Compartilhar).
 * VERSÃO: V6.3 (Sincronização Master de Atributos - socialbr.lol)
 * Responsabilidade: Gerir as interações rápidas do utilizador com o post.
 */

// 1. VERIFICAÇÃO DE ESTADO SOCIAL
// O dado 'usuario_curtiu' é injetado pelo motor PostLogic para definir o estado visual do botão
$usuario_curtiu = (isset($post['usuario_curtiu']) && $post['usuario_curtiu'] > 0);

// 2. DEFINIÇÃO DE ALVOS DE INTERAÇÃO
// Para curtidas e comentários, usamos o ID da postagem atual
$id_para_interacao = $post_id; 

// Para compartilhamento, se for um "share", apontamos para o ID original para evitar loops de share
$id_para_share = !empty($post['post_original_id']) ? $post['post_original_id'] : $post_id;
?>

<style>
    /* Contentor dos botões alinhado com o design Premium */
    .post-actions-wrapper {
        display: flex; 
        justify-content: space-around; 
        padding: 4px 12px;
        border-top: 1px solid #f0f2f5;
    }

    /* Estilo base do botão de ação */
    .action-btn {
        flex: 1;
        background: none;
        border: none;
        padding: 8px;
        color: #65676b;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 4px;
        transition: background 0.2s, color 0.2s;
    }

    .action-btn:hover {
        background-color: #f2f2f2;
    }

    /* Estado Ativo: Cor azul oficial para curtidas realizadas */
    .action-btn.like-active {
        color: #1877f2;
    }

    .action-btn i {
        font-size: 1.1rem;
    }

    /* Responsividade Mobile: Foco em ícones para ecrãs pequenos */
    @media (max-width: 480px) {
        .action-btn span { display: none; } 
        .action-btn i { font-size: 1.3rem; }
    }
</style>

<div class="post-actions-wrapper">
    
    <button type="button" 
            class="action-btn like-btn <?php echo $usuario_curtiu ? 'like-active' : ''; ?>" 
            data-postid="<?php echo $id_para_interacao; ?>" 
            aria-label="Curtir publicação">
        <i class="<?php echo $usuario_curtiu ? 'fas' : 'far'; ?> fa-thumbs-up"></i> 
        <span>Curtir</span>
    </button>
    
    <button type="button" 
            class="action-btn open-modal-comments btn-comentar-trigger" 
            data-postid="<?php echo $id_para_interacao; ?>"
            aria-label="Comentar publicação">
        <i class="far fa-comment"></i> 
        <span>Comentar</span>
    </button>

    <button type="button" 
            class="action-btn btn-compartilhar" 
            data-postid="<?php echo $id_para_share; ?>"
            aria-label="Compartilhar publicação">
        <i class="fas fa-share"></i> 
        <span>Compartilhar</span>
    </button>
    
</div>