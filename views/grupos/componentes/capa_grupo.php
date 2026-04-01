<?php
/**
 * views/grupos/componentes/capa_grupo.php
 * Componente: Seção da Foto de Capa do Grupo.
 * PAPEL: Exibir a imagem de cobertura e prover controlos de edição para o dono.
 * VERSÃO: 1.1 (Correção Mobile e Sincronização de API - socialbr.lol)
 */

// 1. PREPARAÇÃO DE DADOS (Vindo do orquestrador ver.php)
$capa_url = !empty($grupo['foto_capa_url']) 
    ? $config['base_path'] . htmlspecialchars($grupo['foto_capa_url']) 
    : $config['base_path'] . 'assets/images/default-cover.jpg';
?>

<style>
    /* Estilos da Seção de Capa do Grupo */
    .group-cover-wrapper {
        position: relative;
        width: 100%;
        height: 300px; /* Altura generosa para destaque */
        background-color: #f0f2f5;
        overflow: hidden;
    }

    .group-cover-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* Overlay gradiente para melhorar a leitura de botões */
    .group-cover-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, transparent 40%, rgba(0,0,0,0.4) 100%);
        pointer-events: none;
    }

    /* Botão de Edição (Apenas para o Dono) */
    .group-cover-actions {
        position: absolute;
        bottom: 20px;
        right: 20px;
        z-index: 100; /* Z-index elevado para garantir clique no mobile */
        display: flex;
        gap: 10px;
    }

    .btn-edit-cover {
        background-color: #fff;
        color: #050505;
        border: none;
        padding: 8px 15px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        transition: background-color 0.2s;
    }

    /* Input de Ficheiro Invisível (Correção Mobile) */
    /* Esta técnica coloca o input real por cima do botão com opacidade 0 */
    .input-file-mobile-fix {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 101;
    }

    .btn-edit-cover:hover {
        background-color: #f2f2f2;
    }

    /* Ajuste Mobile */
    @media (max-width: 768px) {
        .group-cover-wrapper {
            height: 200px;
        }
        .btn-edit-cover span {
            display: none; /* No mobile mostra apenas o ícone para economizar espaço */
        }
    }
</style>

<div class="group-cover-wrapper">
    <img src="<?php echo $capa_url; ?>" alt="Capa do Grupo" class="group-cover-image">
    
    <div class="group-cover-overlay"></div>

    <?php if ($is_dono): ?>
        <div class="group-cover-actions">
            
            <form action="<?php echo $config['base_path']; ?>api/grupos/atualizar_capa.php" method="POST" enctype="multipart/form-data" id="form-capa-grupo">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="id_grupo" value="<?php echo $id_grupo; ?>">
                
                <div style="position: relative; display: inline-block;">
                    <button type="button" class="btn-edit-cover">
                        <i class="fas fa-camera"></i>
                        <span>Alterar Capa</span>
                    </button>
                    
                    <input type="file" name="foto_capa" class="input-file-mobile-fix" accept="image/*" onchange="document.getElementById('form-capa-grupo').submit();">
                </div>
            </form>

        </div>
    <?php endif; ?>
</div>