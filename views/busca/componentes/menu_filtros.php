<?php
/**
 * menu_filtros.php - Navegação por Abas de Alta Precisão
 * VERSÃO: 2.6 - Eliminação de Espaço Vertical Sobrando
 * PAPEL: Alternar filtros garantindo que a linha ativa encoste no fundo da div.
 */

// 1. Captura dos dados da URL para manter o contexto
$termo_url = isset($_GET['q']) ? urlencode($_GET['q']) : '';
$filtro_ativo = isset($_GET['filtro']) ? $_GET['filtro'] : 'tudo';

// 2. Definição das abas com Ícones
$abas = [
    'tudo'     => ['label' => 'Tudo',      'icone' => 'fas fa-search'],
    'usuarios' => ['label' => 'Pessoas',   'icone' => 'fas fa-users'],
    'grupos'   => ['label' => 'Grupos',    'icone' => 'fas fa-layer-group'],
    'posts'    => ['label' => 'Postagens', 'icone' => 'fas fa-comment-alt']
];
?>

<nav class="menu-filtros-horizontal">
    <div class="filtros-lista-row">
        <?php foreach ($abas as $chave => $dados): 
            $esta_ativa = ($filtro_ativo === $chave) ? 'active' : '';
            
            // Constrói o link mantendo o termo de busca
            $link = ($config['base_path'] ?? '/') . "pesquisa?q={$termo_url}";
            if ($chave !== 'tudo') {
                $link .= "&filtro={$chave}";
            }
        ?>
            <a href="<?php echo $link; ?>" class="filtro-aba <?php echo $esta_ativa; ?>">
                <div class="aba-conteudo">
                    <i class="<?php echo $dados['icone']; ?>"></i>
                    <span><?php echo $dados['label']; ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</nav>

<style>
/**
 * ESTILIZAÇÃO PREMIUM 100% - CORREÇÃO DE VÁCUO VERTICAL
 */
.menu-filtros-horizontal {
    width: 100% !important;
    background: #fff;
    /* REMOVIDO: border-bottom que causava o espaço extra abaixo da linha azul */
    border-bottom: none !important; 
    margin-bottom: 25px; /* Espaço para os resultados abaixo */
    padding: 0;
    box-sizing: border-box;
    display: flex;
}

.filtros-lista-row {
    display: flex !important;
    flex-direction: row !important;
    width: 100% !important;
    max-width: none !important; 
    margin: 0 !important; 
    padding: 0 !important;
    overflow-x: auto;
    scrollbar-width: none;
    box-sizing: border-box;
    /* Adicionada borda cinza clara aqui para ser a linha de fundo contínua */
    border-bottom: 1px solid #dddfe2; 
}

.filtros-lista-row::-webkit-scrollbar {
    display: none;
}

.filtro-aba {
    flex: 1 1 0 !important; 
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 18px 10px;
    text-decoration: none;
    color: #65676b;
    font-weight: 700;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    white-space: nowrap;
    box-sizing: border-box;
    
    /* A linha azul agora senta DIRETAMENTE sobre a borda do container */
    border-bottom: 4px solid transparent;
    margin-bottom: -1px; /* Sobrepõe a borda cinza do container para perfeição visual */
}

.aba-conteudo {
    display: flex;
    align-items: center;
    gap: 10px;
}

.filtro-aba i {
    font-size: 1.1rem;
    color: #8a8d91;
    transition: color 0.3s ease;
}

/* Estado Hover */
.filtro-aba:hover {
    background-color: #f8f9fa;
    color: #0c2d54;
}

.filtro-aba:hover i {
    color: #0c2d54;
}

/* Estado Ativo: Azul Oficial Social BR */
.filtro-aba.active {
    color: #0c2d54; 
    border-bottom-color: #0c2d54 !important; /* Força a linha azul no limite inferior */
    background: rgba(12, 45, 84, 0.02); 
}

.filtro-aba.active i {
    color: #0c2d54;
}

/**
 * AJUSTE PARA DISPOSITIVOS MÓVEIS
 */
@media (max-width: 768px) {
    .filtro-aba {
        padding: 14px 5px;
        font-size: 0.85rem;
    }
    .aba-conteudo {
        gap: 6px;
    }
}
</style>