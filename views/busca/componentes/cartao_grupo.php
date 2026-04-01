<?php
/**
 * cartao_grupo.php - Componente Visual de Resultado (Grupos)
 * VERSÃO: 1.6 - Alinhamento Vertical por Flex-Grow e Blindagem PHP 8
 * PAPEL: Exibir o resumo de um grupo garantindo que o botão fique sempre na base do grid.
 */

// 1. Blindagem de Entrada
if (!isset($g)) return;

/**
 * 2. TRATAMENTO DE INFRAESTRUTURA
 */
$base = $config['base_path'] ?? '/';

$nome_grupo  = htmlspecialchars($g['nome']);
$descricao   = !empty($g['descricao']) ? htmlspecialchars($g['descricao']) : 'Sem descrição disponível.';
$privacidade = ucfirst($g['privacidade']); 
$grupo_link  = $base . 'grupos/ver/' . $g['id'];

$capa_url = !empty($g['foto_capa_url']) 
    ? $base . $g['foto_capa_url'] 
    : $base . 'assets/images/default-group.png';

$descricao_curta = mb_strimwidth($descricao, 0, 120, "...");
?>

<div class="cartao-grupo-busca-v5">
    <a href="<?php echo $grupo_link; ?>" class="cartao-grupo-corpo" title="Ver grupo <?php echo $nome_grupo; ?>">
        <div class="cartao-grupo-capa-box">
            <img src="<?php echo $capa_url; ?>" alt="Capa do grupo <?php echo $nome_grupo; ?>" loading="lazy">
            <div class="badge-privacidade-v5 <?php echo strtolower($g['privacidade']); ?>">
                <i class="fas <?php echo ($g['privacidade'] === 'publico') ? 'fa-globe-americas' : 'fa-lock'; ?>"></i>
                <?php echo $privacidade; ?>
            </div>
        </div>
        
        <div class="cartao-grupo-info">
            <h3 class="cartao-grupo-nome"><?php echo $nome_grupo; ?></h3>
            <p class="cartao-grupo-desc"><?php echo $descricao_curta; ?></p>
        </div>
    </a>

    <a href="<?php echo $grupo_link; ?>" class="cartao-grupo-botao-base">
        Explorar Grupo
    </a>
</div>

<style>
/**
 * DESIGN PREMIUM - GRUPOS (Sincronizado com Usuários e Postagens)
 */
.cartao-grupo-busca-v5 {
    background: #fff;
    border: 1px solid #dddfe2;
    border-radius: 12px;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    display: flex;
    flex-direction: column; /* Organização vertical */
    overflow: hidden;
    width: 100%;
    height: 100%; /* FORÇA O PREENCHIMENTO DA ALTURA DA LINHA DO GRID */
    box-sizing: border-box;
    margin-bottom: 0; /* Removido: agora o grid-gap controla o espaço */
}

.cartao-grupo-busca-v5:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(12, 45, 84, 0.12);
    border-color: #0C2D54;
}

.cartao-grupo-corpo {
    display: flex;
    flex-direction: column;
    flex: 1; /* MÁGICA: Expande o corpo para ocupar o espaço sobrando e empurrar o botão */
    text-decoration: none;
    color: inherit;
    background: #fff;
}

/* Capa do Grupo */
.cartao-grupo-capa-box {
    width: 100%;
    height: 130px;
    position: relative;
    background: #f0f2f5;
    flex-shrink: 0;
}

.cartao-grupo-capa-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.badge-privacidade-v5 {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 0.7rem;
    font-weight: 800;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-privacidade-v5.publico {
    background: rgba(40, 167, 69, 0.9);
}

.badge-privacidade-v5.privado {
    background: rgba(220, 53, 69, 0.9);
}

.cartao-grupo-info {
    padding: 18px 20px;
}

.cartao-grupo-nome {
    margin: 0 0 10px 0;
    font-size: 1.15rem;
    font-weight: 800;
    color: #1c1e21;
}

.cartao-grupo-desc {
    margin: 0;
    font-size: 0.95rem;
    color: #65676b;
    line-height: 1.5;
}

/* Botão na Base Oficial */
.cartao-grupo-botao-base {
    display: block;
    width: 100%;
    padding: 14px 0;
    text-align: center;
    background-color: #0C2D54; 
    color: #ffffff;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.9rem;
    border-top: 1px solid #0C2D54;
    transition: all 0.2s ease;
    flex-shrink: 0; /* Garante que o botão mantenha o tamanho original */
}

.cartao-grupo-busca-v5:hover .cartao-grupo-botao-base {
    background-color: #113a6b;
    color: #ffffff;
}

@media (max-width: 480px) {
    .cartao-grupo-capa-box {
        height: 110px;
    }
    .cartao-grupo-info {
        padding: 15px;
    }
    .cartao-grupo-botao-base {
        padding: 12px 0;
    }
}
</style>