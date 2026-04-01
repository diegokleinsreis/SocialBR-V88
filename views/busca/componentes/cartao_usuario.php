<?php
/**
 * cartao_usuario.php - Componente Visual de Resultado (Pessoas)
 * VERSÃO: 1.7 - Design Quadrado com Botão Azul Padrão
 * PAPEL: Exibir perfis de forma compacta e centralizada no grid.
 */

// 1. Blindagem de Entrada
if (!isset($u)) return;

/**
 * 2. TRATAMENTO DE INFRAESTRUTURA
 * Garante estabilidade contra erros fatais de variáveis indefinidas.
 */
$base = $config['base_path'] ?? '/';

$nome_completo = htmlspecialchars($u['nome'] . ' ' . $u['sobrenome']);
$username      = htmlspecialchars($u['nome_de_usuario']);
$perfil_link   = $base . 'perfil/' . $username;

// Lógica de Foto com Fallback para o padrão do banco
$foto_url = !empty($u['foto_perfil_url']) 
    ? $base . $u['foto_perfil_url'] 
    : $base . 'assets/images/default-avatar.png';

// Verificação de Amizade baseada na lógica da query SQL
$eh_amigo = (isset($u['eh_amigo']) && $u['eh_amigo'] > 0);
?>

<div class="cartao-usuario-busca-v4">
    <a href="<?php echo $perfil_link; ?>" class="cartao-corpo" title="Ver perfil de <?php echo $nome_completo; ?>">
        <div class="cartao-avatar">
            <img src="<?php echo $foto_url; ?>" alt="Foto de <?php echo $nome_completo; ?>" loading="lazy">
        </div>
        
        <div class="cartao-info">
            <div class="cartao-nome-row">
                <span class="cartao-nome"><?php echo $nome_completo; ?></span>
            </div>
            
            <div class="cartao-meta-row">
                <span class="cartao-username">@<?php echo $username; ?></span>
                <?php if ($eh_amigo): ?>
                    <span class="badge-amigo-v4">
                        <i class="fas fa-user-check"></i> AMIGO
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </a>

    <a href="<?php echo $perfil_link; ?>" class="cartao-botao-base">
        Ver Perfil Completo
    </a>
</div>

<style>
/**
 * DESIGN QUADRADO COMPACTO - socialbr.lol
 */
.cartao-usuario-busca-v4 {
    background: #fff;
    border: 1px solid #dddfe2;
    border-radius: 12px;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    display: flex;
    flex-direction: column; /* Organização vertical */
    overflow: hidden;
    width: 100%;
    height: 100%; /* Força o preenchimento da altura da linha do grid */
    box-sizing: border-box;
    margin-bottom: 0;
}

.cartao-usuario-busca-v4:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(12, 45, 84, 0.12);
    border-color: #0C2D54;
}

/* ÁREA DO CORPO: Verticalizada e Centralizada para o visual "quadrado" */
.cartao-corpo {
    display: flex;
    flex-direction: column; /* Alinhamento em pé */
    align-items: center;    /* Centraliza foto e texto horizontalmente */
    justify-content: center;
    padding: 22px 15px;
    text-decoration: none;
    color: inherit;
    gap: 12px;
    background: #fff;
    flex: 1;                /* Expande para empurrar o botão para o rodapé */
    text-align: center;
}

.cartao-avatar img {
    width: 75px;            /* Tamanho equilibrado para o quadrado */
    height: 75px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #f0f2f5;
    flex-shrink: 0;
}

.cartao-info {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    min-width: 0;
}

.cartao-nome-row {
    width: 100%;
}

.cartao-nome {
    font-weight: 800;
    font-size: 1.05rem;     /* Ajuste fino para não quebrar linha */
    color: #1c1e21;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.cartao-meta-row {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    margin-top: 2px;
}

.cartao-username {
    color: #65676b;
    font-size: 0.88rem;
}

/* Badge Amigo Centralizado */
.badge-amigo-v4 {
    background: #e7f3ff;
    color: #1877f2;
    font-size: 0.65rem;
    padding: 3px 8px;
    border-radius: 6px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* BOTÃO DE BASE: Azul Padrão e Texto Branco */
.cartao-botao-base {
    display: block;
    width: 100%;
    padding: 12px 0;
    text-align: center;
    background-color: #0C2D54; /* Azul Oficial */
    color: #ffffff;            /* Texto Branco */
    text-decoration: none;
    font-weight: 700;
    font-size: 0.88rem;
    border-top: 1px solid #0C2D54;
    transition: background-color 0.2s ease;
    flex-shrink: 0;
}

.cartao-usuario-busca-v4:hover .cartao-botao-base {
    background-color: #113a6b; /* Leve variação no hover para feedback */
    color: #ffffff;
}

/**
 * RESPONSIVIDADE
 */
@media (max-width: 480px) {
    .cartao-corpo { padding: 15px 10px; }
    .cartao-avatar img { width: 62px; height: 62px; }
    .cartao-nome { font-size: 0.95rem; }
    .cartao-botao-base { padding: 10px 0; font-size: 0.82rem; }
}
</style>