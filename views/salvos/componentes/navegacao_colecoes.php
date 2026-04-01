<?php
/**
 * views/salvos/componentes/navegacao_colecoes.php
 * Componente: Navegação por Grid de Pastas Premium.
 * PAPEL: Listar coleções com indicadores de privacidade e correção de ancoragem.
 * VERSÃO: V80.0 - Identificação de Privacidade (socialbr.lol)
 */

// 1. Busca as coleções do usuário via Logic
$colecoes = $salvosLogic->listarColecoes($usuario_id);
?>

<style>
    /* GRID E ESTRUTURA DE PASTAS */
    .saved-collections-grid {
        display: grid !important;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)) !important; 
        gap: 12px !important;
        margin-bottom: 30px !important;
        padding: 10px 2px 20px 2px !important;
        border-bottom: 1.5px solid #e1e4e8 !important;
    }

    .folder-card {
        position: relative !important; 
        background: #f8f9fa !important;
        border: 2px solid transparent !important;
        border-radius: 10px !important;
        padding: 12px 5px !important;
        text-align: center !important;
        text-decoration: none !important; 
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        gap: 5px !important;
        transition: all 0.2s ease !important;
        outline: none !important;
    }

    .folder-card:hover {
        background: #f0f2f5 !important;
        transform: translateY(-2px) !important;
        text-decoration: none !important;
    }

    /* ESTADO ATIVO */
    .folder-card.is-active {
        background: rgba(12, 45, 84, 0.05) !important;
        border-color: #0C2D54 !important;
    }

    .folder-card.is-active .folder-icon,
    .folder-card.is-active .folder-name {
        color: #0C2D54 !important;
    }

    /* INDICADOR DE PRIVACIDADE (NOVO V80.0) */
    .folder-privacy-indicator {
        position: absolute !important;
        top: 6px !important;
        left: 8px !important;
        font-size: 0.65rem !important;
        color: #bcc0c4 !important;
        transition: all 0.2s ease !important;
    }

    .folder-card:hover .folder-privacy-indicator {
        color: #65676b !important;
    }

    .folder-card.is-active .folder-privacy-indicator {
        color: #0C2D54 !important;
        opacity: 0.5 !important;
    }

    /* ELEMENTOS INTERNOS */
    .folder-icon {
        font-size: 1.4rem !important;
        color: #0C2D54 !important;
        opacity: 0.8 !important;
    }

    .folder-name {
        font-size: 0.78rem !important;
        font-weight: 700 !important;
        color: #1c1e21 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        max-width: 90% !important;
        text-decoration: none !important;
    }

    .folder-count {
        font-size: 0.62rem !important;
        color: #65676b !important;
        font-weight: 600 !important;
    }

    /* BOTÕES DE GERENCIAMENTO - FIXOS POR PASTA */
    .folder-management-tools {
        position: absolute !important;
        top: -6px !important;
        right: -6px !important;
        display: flex !important;
        gap: 4px !important;
        opacity: 0 !important;
        visibility: hidden !important;
        z-index: 50 !important;
    }

    .is-management-active .folder-management-tools {
        opacity: 1 !important;
        visibility: visible !important;
    }

    .btn-folder-edit, .btn-folder-delete {
        width: 20px !important;
        height: 20px !important;
        border-radius: 50% !important;
        border: none !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 0.6rem !important;
        cursor: pointer !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
    }

    .btn-folder-edit { background: #0C2D54 !important; color: #fff !important; }
    .btn-folder-delete { background: #dc3545 !important; color: #fff !important; }

    /* ANIMAÇÃO IPHONE SHAKE */
    .is-management-active .folder-card:not(.is-geral) {
        animation: folderShake 0.3s infinite ease-in-out !important;
    }

    @keyframes folderShake {
        0% { transform: rotate(0deg); }
        25% { transform: rotate(0.8deg); }
        75% { transform: rotate(-0.8deg); }
        100% { transform: rotate(0deg); }
    }

    @media (max-width: 480px) {
        .saved-collections-grid {
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 8px !important;
        }
    }
</style>

<div class="saved-collections-grid">
    
    <a href="<?php echo $config['base_path']; ?>salvos" 
       class="folder-card <?php echo (is_null($colecao_id)) ? 'is-active' : ''; ?> is-geral">
        <div class="folder-privacy-indicator" title="Apenas você pode ver seus salvos gerais">
            <i class="fas fa-user-lock"></i>
        </div>
        <div class="folder-icon">
            <i class="fas fa-layer-group"></i>
        </div>
        <span class="folder-name">Tudo</span>
        <span class="folder-count"><?php echo array_sum(array_column($colecoes, 'total_itens')); ?> itens</span>
    </a>

    <?php foreach ($colecoes as $colecao): ?>
        <?php 
            $active_class = ($colecao_id == $colecao['id']) ? 'is-active' : '';
            $is_geral = ($colecao['nome'] === 'Geral');
            
            // Lógica de ícones de privacidade
            $is_public = (isset($colecao['privacidade']) && $colecao['privacidade'] === 'publica');
            $priv_icon = $is_public ? 'fa-globe-americas' : 'fa-lock';
            $priv_title = $is_public ? 'Coleção Pública' : 'Coleção Privada';
        ?>
        
        <a href="<?php echo $config['base_path']; ?>salvos/<?php echo $colecao['id']; ?>" 
           class="folder-card <?php echo $active_class; ?> <?php echo $is_geral ? 'is-geral' : ''; ?>" 
           data-id="<?php echo $colecao['id']; ?>">
            
            <div class="folder-privacy-indicator" title="<?php echo $priv_title; ?>">
                <i class="fas <?php echo $priv_icon; ?>"></i>
            </div>

            <?php if (!$is_geral): ?>
                <div class="folder-management-tools">
                    <button type="button" 
                            class="btn-folder-edit" 
                            title="Editar Pasta"
                            onclick="abrirModalEditarColecao(event, <?php echo $colecao['id']; ?>, '<?php echo addslashes($colecao['nome']); ?>', '<?php echo $colecao['privacidade']; ?>')">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button type="button" 
                            class="btn-folder-delete" 
                            title="Excluir Pasta"
                            onclick="confirmarExclusaoColecao(event, <?php echo $colecao['id']; ?>)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <div class="folder-icon">
                <i class="fas <?php echo $is_geral ? 'fa-archive' : 'fa-folder'; ?>"></i>
            </div>
            
            <span class="folder-name" title="<?php echo htmlspecialchars($colecao['nome']); ?>">
                <?php echo htmlspecialchars($colecao['nome']); ?>
            </span>
            
            <span class="folder-count"><?php echo $colecao['total_itens']; ?> itens</span>
        </a>
    <?php endforeach; ?>

</div>