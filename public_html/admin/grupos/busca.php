<?php
/**
 * admin/grupos/busca.php
 * PAPEL: Formulário de pesquisa administrativa para filtragem de grupos.
 * LOCALIZAÇÃO: Deve estar dentro da pasta 'admin/grupos/'
 */
?>
<style>
    /* Estilização da barra de busca */
    .admin-search-box { 
        background: #fff; 
        padding: 20px; 
        border-radius: 10px; 
        margin-bottom: 20px; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
        display: flex; 
        gap: 10px; 
        align-items: center;
    }

    .admin-search-box form {
        display: flex;
        flex: 1;
        gap: 10px;
    }

    .admin-search-box input { 
        flex: 1; 
        padding: 12px 15px; 
        border: 1px solid #e4e6eb; 
        border-radius: 6px; 
        outline: none; 
        font-size: 0.95rem;
        transition: border-color 0.2s;
    }

    .admin-search-box input:focus { 
        border-color: #0C2D54; 
    }

    /* Botão de Busca */
    .btn-search { 
        background: #0C2D54; 
        color: #fff; 
        border: none; 
        padding: 10px 25px; 
        border-radius: 6px; 
        cursor: pointer; 
        font-weight: 700; 
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background 0.2s; 
    }

    .btn-search:hover { 
        background: #153e6f; 
    }

    /* Botão de Limpar */
    .btn-clear {
        background: #f0f2f5;
        color: #4b4b4b;
        text-decoration: none;
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        transition: background 0.2s;
    }

    .btn-clear:hover {
        background: #e4e6eb;
    }
</style>

<div class="admin-search-box">
    <form action="" method="GET">
        <input type="hidden" name="sub_route" value="grupos">
        
        <input type="text" name="q" 
               value="<?php echo htmlspecialchars($busca); ?>" 
               placeholder="Pesquisar por nome do grupo ou ID (UID)...">
        
        <button type="submit" class="btn-search">
            <i class="fas fa-search"></i> BUSCAR
        </button>

        <?php if(!empty($busca)): ?>
            <a href="?sub_route=grupos" class="btn-clear">
                <i class="fas fa-times"></i> LIMPAR
            </a>
        <?php endif; ?>
    </form>
</div>