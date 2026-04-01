<?php
/**
 * views/grupos/componentes/card_meu_grupo.php
 * Componente: Card de Grupo (Membro).
 * PAPEL: Exibir de forma elegante os grupos onde o utilizador já participa.
 * VERSÃO: 1.0 (UX Premium - SOOC)
 */

// 1. PREPARAÇÃO DE DADOS (Vindo do loop no orquestrador home.php)
// As variáveis $grupo e $config são injetadas pelo arquivo pai.
$id_grupo     = (int)$grupo['id'];
$nome_grupo   = htmlspecialchars($grupo['nome']);
$nivel_acesso = $grupo['nivel_permissao'] ?? 'membro';
$grupo_link   = $config['base_path'] . 'grupos/ver/' . $id_grupo;

// Tratamento da imagem de capa com fallback seguro
$capa_src = !empty($grupo['foto_capa_url']) 
    ? $config['base_path'] . htmlspecialchars($grupo['foto_capa_url']) 
    : $config['base_path'] . 'assets/images/default-cover.jpg';
?>

<style>
    /* Estilos do Card de Grupos que Participo */
    .my-group-card {
        background: #fff;
        border: 1px solid #e4e6eb;
        border-radius: 10px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        align-items: center;
        padding: 12px;
        text-decoration: none !important;
    }

    .my-group-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        background-color: #f9f9f9;
    }

    /* Miniatura da Capa */
    .my-group-thumb {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        object-fit: cover;
        margin-right: 15px;
        flex-shrink: 0;
    }

    /* Conteúdo Textual */
    .my-group-info {
        flex-grow: 1;
        overflow: hidden;
    }

    .my-group-name {
        display: block;
        font-weight: 700;
        color: #050505;
        font-size: 1rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 2px;
    }

    /* Badges de Nível de Permissão */
    .my-group-badge {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        padding: 2px 8px;
        border-radius: 4px;
        display: inline-block;
    }

    .badge-dono { background-color: #fff0f0; color: #d70000; }
    .badge-moderador { background-color: #e7f3ff; color: #1877f2; }
    .badge-membro { background-color: #f0f2f5; color: #65676b; }

    /* Ícone de Acesso Rápido */
    .my-group-arrow {
        color: #bec3c9;
        font-size: 0.9rem;
        margin-left: 10px;
    }
</style>

<a href="<?php echo $grupo_link; ?>" class="my-group-card" title="Entrar no grupo <?php echo $nome_grupo; ?>">
    <img src="<?php echo $capa_src; ?>" alt="Capa de <?php echo $nome_grupo; ?>" class="my-group-thumb">

    <div class="my-group-info">
        <span class="my-group-name"><?php echo $nome_grupo; ?></span>
        
        <?php if ($nivel_acesso === 'dono'): ?>
            <span class="my-group-badge badge-dono">Proprietário</span>
        <?php elseif ($nivel_acesso === 'moderador'): ?>
            <span class="my-group-badge badge-moderador">Moderador</span>
        <?php else: ?>
            <span class="my-group-badge badge-membro">Membro</span>
        <?php endif; ?>
    </div>

    <div class="my-group-arrow">
        <i class="fas fa-chevron-right"></i>
    </div>
</a>