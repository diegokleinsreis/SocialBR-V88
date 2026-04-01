<?php
/**
 * views/grupos/componentes/card_sugestao.php
 * Componente: Card de Sugestão de Grupo.
 * PAPEL: Exibir grupos recomendados para descoberta.
 * VERSÃO: 1.0 (UX Premium - SOOC)
 */

// 1. PREPARAÇÃO DE DADOS (Vindo do loop no orquestrador home.php)
$id_sugestao     = (int)$item_sugestao['id'];
$nome_sugestao   = htmlspecialchars($item_sugestao['nome']);
$total_membros   = (int)$item_sugestao['total_membros'];
$sugestao_link   = $config['base_path'] . 'grupos/ver/' . $id_sugestao;

// Tratamento da imagem de capa com fallback seguro
$capa_sugestao_src = !empty($item_sugestao['foto_capa_url']) 
    ? $config['base_path'] . htmlspecialchars($item_sugestao['foto_capa_url']) 
    : $config['base_path'] . 'assets/images/default-cover.jpg';
?>

<style>
    /* Estilos do Card de Sugestão */
    .suggested-group-card {
        background: #fff;
        border: 1px solid #e4e6eb;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: box-shadow 0.3s ease;
        height: 100%;
    }

    .suggested-group-card:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }

    /* Área da Imagem de Capa */
    .suggested-group-cover {
        width: 100%;
        height: 120px;
        position: relative;
    }

    .suggested-group-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Badge de Membros (Prova Social) */
    .suggested-group-members-badge {
        position: absolute;
        bottom: 8px;
        right: 8px;
        background: rgba(0, 0, 0, 0.6);
        color: #fff;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        backdrop-filter: blur(4px);
    }

    /* Corpo do Card */
    .suggested-group-body {
        padding: 15px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-align: center;
    }

    .suggested-group-name {
        font-size: 1.05rem;
        font-weight: 800;
        color: #050505;
        margin: 0 0 10px 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.2;
    }

    /* Botões de Ação */
    .suggested-group-footer {
        margin-top: 15px;
    }

    .btn-view-group {
        display: block;
        background-color: #1877f2;
        color: #fff;
        text-align: center;
        padding: 10px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.9rem;
        text-decoration: none !important;
        transition: background-color 0.2s;
    }

    .btn-view-group:hover {
        background-color: #166fe5;
    }
</style>

<div class="suggested-group-card">
    <div class="suggested-group-cover">
        <img src="<?php echo $capa_sugestao_src; ?>" alt="Capa de <?php echo $nome_sugestao; ?>">
        <div class="suggested-group-members-badge">
            <i class="fas fa-users"></i> <?php echo $total_membros; ?> membros
        </div>
    </div>

    <div class="suggested-group-body">
        <h3 class="suggested-group-name" title="<?php echo $nome_sugestao; ?>">
            <?php echo $nome_sugestao; ?>
        </h3>

        <div class="suggested-group-footer">
            <a href="<?php echo $sugestao_link; ?>" class="btn-view-group">
                Ver Grupo
            </a>
        </div>
    </div>
</div>