<?php
/**
 * views/salvos/componentes/barra_filtros.php
 * Componente Atómico: Barra de Busca e Filtros de Tipo.
 * PAPEL: Fornecer interface para busca textual e alternância entre categorias globais.
 * VERSÃO: V79.0 - Force-Style de Busca e Abas (socialbr.lol)
 */

// As variáveis $filtro_tipo e $busca_termo já foram capturadas no esqueleto home.php
?>

<style>
/* CALIBRAÇÃO DE BUSCA E ABAS - FORCE STYLE */

/* Container da Busca */
.saved-search-container {
    margin-bottom: 20px !important;
    width: 100% !important;
}

.search-input-wrapper {
    position: relative !important;
    display: flex !important;
    align-items: center !important;
    width: 100% !important;
}

/* Input de Pesquisa */
#input-busca-salvos {
    width: 100% !important;
    padding: 12px 45px 12px 40px !important; /* Espaço para lupa e fechar */
    border: 1.5px solid #e1e4e8 !important;
    border-radius: 12px !important;
    background-color: #f8f9fa !important;
    font-size: 0.95rem !important;
    color: #1c1e21 !important;
    transition: all 0.2s ease !important;
    outline: none !important;
    box-sizing: border-box !important;
}

#input-busca-salvos:focus {
    background-color: #ffffff !important;
    border-color: #0C2D54 !important;
    box-shadow: 0 0 0 3px rgba(12, 45, 84, 0.1) !important;
}

/* Ícone de Lupa */
.search-icon {
    position: absolute !important;
    left: 15px !important;
    color: #65676b !important;
    font-size: 0.9rem !important;
    pointer-events: none !important;
    z-index: 5 !important;
}

/* Botão de Limpar Busca (X) */
#btn-clear-saved-search {
    position: absolute !important;
    right: 12px !important;
    background: none !important;
    border: none !important;
    color: #bcc0c4 !important;
    cursor: pointer !important;
    font-size: 1.1rem !important;
    padding: 5px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    z-index: 5 !important;
    transition: color 0.2s !important;
}

#btn-clear-saved-search:hover {
    color: #65676b !important;
}

/* NAVEGAÇÃO DE ABAS */
.saved-tabs-wrapper {
    width: 100% !important;
    border-bottom: 1.5px solid #e1e4e8 !important;
    margin-top: 15px !important;
    overflow-x: auto !important;
}

.saved-filter-tabs {
    display: flex !important;
    list-style: none !important;
    padding: 0 !important;
    margin: 0 !important;
    gap: 5px !important;
}

.tab-link {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    padding: 12px 20px !important;
    text-decoration: none !important; /* REMOVE SUBLINHADO */
    color: #65676b !important;
    font-weight: 700 !important;
    font-size: 0.9rem !important;
    border-bottom: 3px solid transparent !important;
    transition: all 0.2s ease !important;
    white-space: nowrap !important;
    outline: none !important;
}

.tab-link:hover {
    background-color: rgba(12, 45, 84, 0.04) !important;
    color: #0C2D54 !important;
}

/* Estado Ativo da Aba */
.tab-item.is-active .tab-link {
    color: #0C2D54 !important;
    border-bottom-color: #0C2D54 !important;
}

.tab-icon {
    font-size: 1rem !important;
    display: flex !important;
    align-items: center !important;
}

/* Helpers */
.is-hidden { display: none !important; }

/* Mobile */
@media (max-width: 768px) {
    .tab-link {
        padding: 12px 15px !important;
        font-size: 0.85rem !important;
    }
}
</style>

<div class="saved-filters-wrapper">
    
    <div class="saved-search-container">
        <div class="search-input-wrapper">
            <i class="fas fa-search search-icon"></i>
            <input type="text" 
                   id="input-busca-salvos" 
                   placeholder="Pesquisar nos seus itens salvos..." 
                   value="<?php echo htmlspecialchars($busca_termo); ?>"
                   autocomplete="off"
                   aria-label="Pesquisar itens salvos">
            
            <button id="btn-clear-saved-search" class="<?php echo empty($busca_termo) ? 'is-hidden' : ''; ?>" title="Limpar busca">
                <i class="fas fa-times-circle"></i>
            </button>
        </div>
    </div>

    <nav class="saved-tabs-wrapper">
        <ul class="saved-filter-tabs">
            <li class="tab-item <?php echo ($filtro_tipo === 'todos') ? 'is-active' : ''; ?>">
                <a href="<?php echo $config['base_path']; ?>salvos?tipo=todos" class="tab-link">
                    <span class="tab-icon"><i class="fas fa-stream"></i></span>
                    <span class="tab-text">Tudo</span>
                </a>
            </li>

            <li class="tab-item <?php echo ($filtro_tipo === 'publicacoes' || $filtro_tipo === 'postagem') ? 'is-active' : ''; ?>">
                <a href="<?php echo $config['base_path']; ?>salvos?tipo=publicacoes" class="tab-link">
                    <span class="tab-icon"><i class="fas fa-newspaper"></i></span>
                    <span class="tab-text">Publicações</span>
                </a>
            </li>

            <li class="tab-item <?php echo ($filtro_tipo === 'marketplace') ? 'is-active' : ''; ?>">
                <a href="<?php echo $config['base_path']; ?>salvos?tipo=marketplace" class="tab-link">
                    <span class="tab-icon"><i class="fas fa-shopping-bag"></i></span>
                    <span class="tab-text">Marketplace</span>
                </a>
            </li>

            <li class="tab-item <?php echo ($filtro_tipo === 'enquete') ? 'is-active' : ''; ?>">
                <a href="<?php echo $config['base_path']; ?>salvos?tipo=enquete" class="tab-link">
                    <span class="tab-icon"><i class="fas fa-poll-h"></i></span>
                    <span class="tab-text">Enquetes</span>
                </a>
            </li>
        </ul>
    </nav>

</div>