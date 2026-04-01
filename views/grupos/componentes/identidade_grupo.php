<?php
/**
 * views/grupos/componentes/identidade_grupo.php
 * Componente: Identidade Visual Organizada e Blindada.
 * PAPEL: Nome flutuante em #0C2D54 e ações/descrição na área externa (branca).
 * VERSÃO: 9.1 (Fluxo de Resposta a Convites Integrado - socialbr.lol)
 */

// 1. PREPARAÇÃO DE DADOS (Vindos do orquestrador ver.php)
$nome_grupo      = htmlspecialchars($grupo['nome']);
$descricao       = htmlspecialchars($grupo['descricao'] ?? '');
$privacidade     = $grupo['privacidade']; 
$total_membros   = (int)($grupo['total_membros'] ?? 0);
$nivel_permissao = $grupo['nivel_permissao']; 
$membro_id       = $grupo['membro_id']; 

// A variável $tem_convite é herdada do orquestrador ver.php v5.3
$usuario_foi_convidado = (isset($tem_convite) && $tem_convite === true);
?>

<style>
    /* Container Principal */
    .group-identity-main {
        width: 100% !important;
        position: relative !important;
        display: flex !important;
        flex-direction: column !important;
    }

    /* 1. ÁREA FLUTUANTE (APENAS O NOME SOBRE A CAPA) */
    .group-floating-header {
        margin-top: -85px !important; /* Força o nome a subir para a capa */
        margin-bottom: 25px !important;
        position: relative !important;
        z-index: 50 !important;
        display: flex !important;
        align-items: center !important;
        gap: 15px !important;
    }

    .group-floating-header h1 {
        font-size: 2.8rem !important;
        font-weight: 900 !important;
        color: #0C2D54 !important; /* Cor personalizada oficial */
        margin: 0 !important;
        line-height: 1 !important;
        letter-spacing: -1.5px !important;
        
        /* BORDA FINA + SOMBRA DE PROFUNDIDADE PARA LEITURA NA CAPA */
        text-shadow: 
            -1px -1px 0 #fff,  
             1px -1px 0 #fff,
            -1px  1px 0 #fff,
             1px  1px 0 #fff,
             0px 6px 15px rgba(0,0,0,0.4) !important;
    }

    .privacy-pill-floating {
        background: #fff !important;
        width: 38px !important;
        height: 38px !important;
        border-radius: 50% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2) !important;
        color: #0C2D54 !important;
    }

    /* 2. BARRA DE AÇÕES E META (FORA DA CAPA - ÁREA BRANCA) */
    .group-details-bar {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        padding-top: 10px !important;
        border-bottom: 1px solid #e4e6eb !important;
        padding-bottom: 15px !important;
        flex-wrap: wrap !important;
        gap: 20px !important;
        width: 100% !important;
    }

    .group-meta-data {
        color: #65676b !important;
        font-size: 1rem !important;
        font-weight: 600 !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
    }

    .group-buttons-row {
        display: flex !important;
        gap: 10px !important;
    }

    /* Estilo do Botão Premium SocialBR */
    .btn-action-br {
        padding: 10px 20px !important;
        border-radius: 8px !important;
        font-weight: 700 !important;
        font-size: 0.95rem !important;
        border: none !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        transition: all 0.2s !important;
        text-decoration: none !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    }

    .btn-action-br:hover {
        transform: translateY(-2px) !important;
        filter: brightness(0.95) !important;
    }

    .btn-br-primary { background-color: #1877f2 !important; color: #fff !important; }
    .btn-br-secondary { background-color: #e4e6eb !important; color: #050505 !important; }
    
    /* Cores de Convite (Novas V9.1) */
    .btn-br-success { background-color: #42b72a !important; color: #fff !important; }
    .btn-br-danger { background-color: #f02849 !important; color: #fff !important; }

    /* 3. DESCRIÇÃO (LARGURA TOTAL DESCOMPACTADA) */
    .group-description-box {
        width: 100% !important;
        display: block !important;
        margin-top: 20px !important;
        font-size: 1.1rem !important;
        color: #050505 !important;
        line-height: 1.6 !important;
        word-wrap: break-word !important;
    }

    @media (max-width: 768px) {
        .group-floating-header { 
            margin-top: -60px !important; 
            justify-content: center !important; 
        }
        .group-floating-header h1 { font-size: 2rem !important; }
        .group-details-bar { flex-direction: column !important; text-align: center !important; }
        .group-buttons-row { justify-content: center !important; width: 100% !important; }
    }
</style>

<div class="group-identity-main">
    
    <div class="group-floating-header">
        <h1><?php echo $nome_grupo; ?></h1>
        <div class="privacy-pill-floating">
            <i class="fas <?php echo ($privacidade === 'publico') ? 'fa-globe-americas' : 'fa-lock'; ?>"></i>
        </div>
    </div>

    <div class="group-details-bar">
        <div class="group-meta-data">
            <i class="fas fa-users"></i>
            <span><?php echo $total_membros; ?> membros · Grupo <?php echo ucfirst($privacidade); ?></span>
        </div>

        <div class="group-buttons-row">
            <?php if ($membro_id): ?>
                <button class="btn-action-br btn-br-secondary">
                    <i class="fas fa-check"></i> Membro
                </button>
                <button class="btn-action-br btn-br-secondary" onclick="convidarAmigos(<?php echo $id_grupo; ?>)">
                    <i class="fas fa-user-plus"></i>
                </button>
            <?php elseif ($usuario_foi_convidado): ?>
                <button class="btn-action-br btn-br-success" onclick="responderAoConvite('aceitar')">
                    <i class="fas fa-check-circle"></i> Aceitar Convite
                </button>
                <button class="btn-action-br btn-br-danger" onclick="responderAoConvite('recusar')">
                    <i class="fas fa-times-circle"></i> Recusar
                </button>
            <?php else: ?>
                <button class="btn-action-br btn-br-primary" onclick="participarDoGrupo(<?php echo $id_grupo; ?>)">
                    <i class="fas fa-plus"></i> Participar
                </button>
            <?php endif; ?>

            <button class="btn-action-br btn-br-secondary" onclick="compartilharGrupo(<?php echo $id_grupo; ?>)">
                <i class="fas fa-share"></i>
            </button>
        </div>
    </div>

    <?php if (!empty($descricao)): ?>
        <div class="group-description-box">
            <?php echo nl2br($descricao); ?>
        </div>
    <?php endif; ?>

</div>