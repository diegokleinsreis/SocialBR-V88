<?php
/**
 * cartao_postagem.php - Componente Visual de Resultado (Postagens)
 * VERSÃO: 1.6 - Alinhamento Vertical por Flex-Grow e Blindagem PHP 8
 * PAPEL: Exibir um fragmento de postagem garantindo simetria no grid de busca.
 */

// 1. Blindagem de Entrada
if (!isset($p)) return;

/**
 * 2. TRATAMENTO DE INFRAESTRUTURA
 * Garante estabilidade contra erros fatais de variáveis indefinidas.
 */
$base = $config['base_path'] ?? '/';

$autor_nome    = htmlspecialchars($p['nome']);
$username      = htmlspecialchars($p['nome_de_usuario']);
$conteudo_raw  = strip_tags($p['conteudo_texto']); // Remove HTML para o resumo
$post_link     = $base . 'postagem/' . $p['id'];
$perfil_link   = $base . 'perfil/' . $username;

// Lógica de Data (Formatação brasileira)
$data_postagem = date('d/m/Y H:i', strtotime($p['data_postagem']));

// Lógica de Avatar do Autor com Fallback Robusto
$foto_url = !empty($p['foto_perfil_url']) 
    ? $base . $p['foto_perfil_url'] 
    : $base . 'assets/images/default-avatar.png';

// Truncar o texto (Aproveitando o novo espaço do layout expandido)
$resumo_texto = mb_strimwidth($conteudo_raw, 0, 200, "...");
?>

<div class="cartao-post-busca-v5">
    <div class="cartao-post-superior">
        <div class="post-header-meta">
            <a href="<?php echo $perfil_link; ?>" class="autor-box">
                <img src="<?php echo $foto_url; ?>" alt="<?php echo $autor_nome; ?>" class="autor-avatar">
                <div class="autor-identidade">
                    <span class="autor-nome"><?php echo $autor_nome; ?></span>
                    <span class="autor-username">@<?php echo $username; ?></span>
                </div>
            </a>
            <span class="post-data-tag"><?php echo $data_postagem; ?></span>
        </div>

        <a href="<?php echo $post_link; ?>" class="post-link-corpo">
            <div class="post-snippet-premium">
                <i class="fas fa-quote-left"></i>
                <p><?php echo $resumo_texto; ?></p>
            </div>
        </a>
    </div>

    <a href="<?php echo $post_link; ?>" class="post-botao-base">
        Ver Postagem Completa
    </a>
</div>

<style>
/**
 * DESIGN PREMIUM - POSTAGENS (Sincronizado com Usuários e Grupos)
 */
.cartao-post-busca-v5 {
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
    margin-bottom: 0; /* Espaçamento gerido pelo gap do grid */
}

.cartao-post-busca-v5:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(12, 45, 84, 0.1);
    border-color: #0C2D54;
}

.cartao-post-superior {
    padding: 18px 20px;
    display: flex;
    flex-direction: column;
    flex: 1; /* MÁGICA: Expande para ocupar o espaço e empurrar o botão */
}

.post-header-meta {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.autor-box {
    display: flex;
    align-items: center;
    text-decoration: none;
    gap: 12px;
}

.autor-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid #f0f2f5;
    flex-shrink: 0;
}

.autor-identidade {
    display: flex;
    flex-direction: column;
}

.autor-nome {
    font-size: 0.95rem;
    font-weight: 700;
    color: #1c1e21;
}

.autor-username {
    font-size: 0.8rem;
    color: #65676b;
}

.post-data-tag {
    font-size: 0.75rem;
    color: #90949c;
}

/* Corpo do Snippet */
.post-link-corpo {
    text-decoration: none;
    color: #4b4f56;
    flex: 1; /* Permite que o link ocupe o espaço para clique */
}

.post-snippet-premium {
    position: relative;
    padding: 15px 15px 15px 35px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #0C2D54;
    height: 100%; /* Garante que o fundo cinza também se expanda se necessário */
}

.post-snippet-premium i {
    position: absolute;
    left: 12px;
    top: 15px;
    font-size: 0.9rem;
    color: #0C2D54;
    opacity: 0.2;
}

.post-snippet-premium p {
    margin: 0;
    font-size: 1rem;
    line-height: 1.6;
    font-style: italic;
    color: #333;
}

/* Botão na Base Oficial */
.post-botao-base {
    display: block;
    width: 100%;
    padding: 12px 0;
    text-align: center;
    background-color: #0C2D54; 
    color: #ffffff;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.9rem;
    border-top: 1px solid #0C2D54;
    transition: all 0.2s ease;
    flex-shrink: 0; /* Impede o botão de achatar */
}

.cartao-post-busca-v5:hover .post-botao-base {
    background-color: #113a6b;
    color: #ffffff;
}

@media (max-width: 480px) {
    .cartao-post-superior {
        padding: 15px;
    }
    .post-snippet-premium p {
        font-size: 0.9rem;
    }
    .post-botao-base {
        padding: 10px 0;
    }
}
</style>