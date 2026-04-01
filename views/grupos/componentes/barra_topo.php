<?php
/**
 * views/grupos/componentes/barra_topo.php
 * Componente: Barra de Ferramentas de Grupos.
 * PAPEL: Providenciar busca interna de grupos com botão de submissão e criação.
 * VERSÃO: 1.3 (Botão de Pesquisa Compacto - socialbr.lol)
 */
?>

<style>
    /* Estilos da Barra de Ferramentas Superior */
    .groups-topo-bar {
        background: #fff !important;
        padding: 15px 20px !important;
        border-radius: 12px !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1) !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 20px !important;
        margin-bottom: 30px !important;
        border: 1px solid #e4e6eb !important;
    }

    /* Lado Esquerdo: Busca */
    .groups-search-form {
        flex-grow: 1 !important;
        max-width: 500px !important;
        display: flex !important;
        gap: 10px !important;
    }

    .groups-search-wrapper {
        position: relative !important;
        flex-grow: 1 !important;
    }

    .groups-search-input {
        width: 100% !important;
        height: 38px !important; /* Altura fixa para alinhar com o botão */
        background-color: #f0f2f5 !important;
        border: 1px solid transparent !important;
        padding: 0 15px 0 40px !important;
        border-radius: 20px !important;
        font-size: 0.95rem !important;
        outline: none !important;
        transition: all 0.2s !important;
    }

    .groups-search-input:focus {
        background-color: #fff !important;
        border-color: #1877f2 !important;
        box-shadow: 0 0 0 2px rgba(24, 119, 242, 0.1) !important;
    }

    .groups-search-icon {
        position: absolute !important;
        left: 15px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        color: #65676b !important;
        pointer-events: none !important;
    }

    /* Botão de Pesquisar: Agora Circular e Compacto */
    .btn-search-submit {
        background-color: #1877f2 !important;
        color: #fff !important;
        border: none !important;
        width: 38px !important; /* Tamanho fixo para ser um círculo */
        height: 38px !important;
        padding: 0 !important; /* Removemos o padding que o deixava grande */
        border-radius: 50% !important; /* Círculo perfeito */
        font-size: 0.9rem !important;
        cursor: pointer !important;
        transition: filter 0.2s !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important; /* Impede que o flex o amasse */
    }

    .btn-search-submit:hover {
        filter: brightness(0.9) !important;
    }

    /* Lado Direito: Ações */
    .groups-actions-wrapper {
        display: flex !important;
        gap: 10px !important;
        flex-shrink: 0 !important;
    }

    .btn-create-group {
        background-color: #e7f3ff !important;
        color: #1877f2 !important;
        border: none !important;
        padding: 0 16px !important;
        height: 38px !important; /* Alinhado com a busca */
        border-radius: 8px !important;
        font-weight: 700 !important;
        font-size: 0.9rem !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        text-decoration: none !important;
        transition: background-color 0.2s !important;
    }

    .btn-create-group:hover {
        background-color: #dbe7f2 !important;
    }

    /* Ajustes para Mobile */
    @media (max-width: 768px) {
        .groups-topo-bar {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 15px !important;
            padding: 15px !important;
        }
        .groups-search-form {
            max-width: 100% !important;
        }
        .btn-create-group {
            justify-content: center !important;
            width: 100% !important;
        }
    }
</style>

<div class="groups-topo-bar">
    
    <div class="groups-search-form">
        <form action="<?php echo $config['base_path']; ?>grupos" method="GET" style="display: flex; width: 100%; gap: 8px;">
            <div class="groups-search-wrapper">
                <i class="fas fa-search groups-search-icon"></i>
                <input type="text" 
                       name="q" 
                       class="groups-search-input" 
                       placeholder="Procurar grupos..." 
                       value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>"
                       autocomplete="off">
            </div>
            
            <button type="submit" class="btn-search-submit" title="Pesquisar">
                <i class="fas fa-arrow-right"></i>
            </button>
        </form>
    </div>

    <div class="groups-actions-wrapper">
        <a href="<?php echo $config['base_path']; ?>grupos/criar" class="btn-create-group">
            <i class="fas fa-plus-circle"></i>
            <span>Criar novo grupo</span>
        </a>
    </div>

</div>