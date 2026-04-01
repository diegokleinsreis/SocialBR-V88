<?php
/**
 * views/grupos/componentes/solicitacoes_grupo.php
 * Componente: Gestão de Pedidos de Entrada.
 * PAPEL: Listar usuários que solicitaram participar e processar aprovação/recusa.
 * VERSÃO: 1.4 (Sincronização Master JS - socialbr.lol)
 */

// 1. BUSCA DE PEDIDOS (Via GruposLogic)
// Note: $conn e $id_grupo são herdados do ver.php
$pedidos = GruposLogic::getSolicitacoesPendentes($conn, $id_grupo);
?>

<style>
    .solicitacoes-wrapper {
        width: 100% !important;
        max-width: 1000px !important;
        margin: 0 auto !important;
    }

    .solicitacoes-card {
        background: #fff !important;
        border: 1px solid #e4e6eb !important;
        border-radius: 12px !important;
        padding: 25px !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05) !important;
    }

    .solicitacoes-header {
        font-size: 1.05rem !important;
        font-weight: 850 !important;
        color: #0C2D54 !important;
        margin-bottom: 20px !important;
        border-bottom: 1px solid #f0f2f5 !important;
        padding-bottom: 12px !important;
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
    }

    .pedido-item {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding: 12px !important;
        border: 1px solid #f0f2f5 !important;
        border-radius: 10px !important;
        margin-bottom: 10px !important;
        background-color: #fafbfc !important;
        transition: all 0.3s ease !important;
    }

    .pedido-item:hover {
        transform: scale(1.01) !important;
        border-color: #ccd0d5 !important;
    }

    .pedido-user-info {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
    }

    .pedido-avatar {
        width: 45px !important;
        height: 45px !important;
        border-radius: 50% !important;
        object-fit: cover !important;
        border: 1px solid #e4e6eb !important;
    }

    .pedido-text h4 {
        margin: 0 !important;
        font-size: 0.95rem !important;
        font-weight: 700 !important;
        color: #050505 !important;
    }

    .pedido-text span {
        font-size: 0.8rem !important;
        color: #65676b !important;
    }

    .pedido-actions {
        display: flex !important;
        gap: 8px !important;
    }

    .btn-decisao {
        padding: 8px 15px !important;
        border-radius: 6px !important;
        font-weight: 700 !important;
        font-size: 0.8rem !important;
        border: none !important;
        cursor: pointer !important;
        transition: filter 0.2s !important;
    }

    .btn-aprovar { background-color: #1877f2 !important; color: #fff !important; }
    .btn-recusar { background-color: #e4e6eb !important; color: #050505 !important; }

    .btn-decisao:hover { filter: brightness(0.9) !important; }

    @media (max-width: 600px) {
        .pedido-item { flex-direction: column; gap: 15px; text-align: center; }
        .pedido-user-info { flex-direction: column; }
    }
</style>

<div class="solicitacoes-wrapper">
    <div class="solicitacoes-card">
        <h3 class="solicitacoes-header">
            <i class="fas fa-user-clock"></i> Pedidos Pendentes (<?php echo count($pedidos); ?>)
        </h3>

        <?php if (!empty($pedidos)): ?>
            <?php foreach ($pedidos as $p): ?>
                <div class="pedido-item" id="pedido-<?php echo $p['id']; ?>">
                    <div class="pedido-user-info">
                        <?php 
                        $foto = !empty($p['foto_perfil_url']) ? $p['foto_perfil_url'] : 'assets/images/default-avatar.png'; 
                        ?>
                        <img src="<?php echo $config['base_path'] . $foto; ?>" class="pedido-avatar">
                        <div class="pedido-text">
                            <h4><?php echo htmlspecialchars($p['nome'] . ' ' . $p['sobrenome']); ?></h4>
                            <span>Solicitou em <?php echo date('d/m/Y H:i', strtotime($p['data_pedido'])); ?></span>
                        </div>
                    </div>

                    <div class="pedido-actions">
                        <button class="btn-decisao btn-aprovar" onclick="decidirSolicitacao(<?php echo $p['id']; ?>, 'aprovar')">
                            Aprovar
                        </button>
                        <button class="btn-decisao btn-recusar" onclick="decidirSolicitacao(<?php echo $p['id']; ?>, 'recusar')">
                            Recusar
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 40px 20px; color: #65676b;">
                <i class="fas fa-check-circle" style="font-size: 2.5rem; display: block; margin-bottom: 15px; opacity: 0.2; color: #0C2D54;"></i>
                <p>Tudo em dia! Nenhuma solicitação pendente para este grupo.</p>
            </div>
        <?php endif; ?>
    </div>
</div>