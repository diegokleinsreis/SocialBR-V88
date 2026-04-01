<?php
/**
 * admin/erros/tabela_erros.php
 * Componente: Tabela de Monitoramento de Erros Sentinela.
 * VERSÃO: 1.1 (Layout Fix - socialbr.lol)
 */

// 1. Busca os erros mais recentes com os dados do utilizador (se houver)
$sql_erros = "SELECT e.*, u.nome_de_usuario, u.foto_perfil_url 
              FROM Logs_Erros_Sistema e 
              LEFT JOIN Usuarios u ON e.usuario_id = u.id 
              ORDER BY e.data_atualizacao DESC 
              LIMIT 50";
$stmt_erros = $pdo->query($sql_erros);
$lista_erros = $stmt_erros->fetchAll();
?>

<style>
    /* CSS Cirúrgico para impedir que a tabela quebre o layout */
    .sentinela-table-container {
        width: 100%;
        overflow-x: auto; /* Permite scroll horizontal na tabela sem empurrar o menu */
        -webkit-overflow-scrolling: touch;
    }

    .sentinela-table {
        table-layout: fixed; /* Força o navegador a respeitar as larguras definidas */
        min-width: 800px; /* Garante que a tabela não fique esmagada no mobile */
    }

    /* Truncamento Inteligente */
    .col-mensagem {
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .col-arquivo {
        max-width: 150px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 0.75rem;
    }

    @media (max-width: 768px) {
        .sentinela-table {
            min-width: 700px; /* No mobile, permitimos um scroll levemente menor */
        }
    }
</style>

<div class="sentinela-table-container shadow-sm rounded-bottom">
    <table class="table table-hover align-middle mb-0 sentinela-table bg-white">
        <thead class="bg-light text-muted uppercase small fw-bold">
            <tr>
                <th class="ps-3" style="width: 130px;">Tipo / Status</th>
                <th style="width: 350px;">Mensagem e Localização</th>
                <th class="d-none d-lg-table-cell" style="width: 150px;">Acessado por</th>
                <th class="text-center" style="width: 100px;">Qtd.</th>
                <th class="text-end pe-3" style="width: 100px;">Gestão</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lista_erros as $erro): ?>
                <?php 
                    // Lógica de Cores por Gravidade
                    $tipo_class = 'bg-secondary';
                    if (str_contains($erro['tipo'], 'Fatal')) $tipo_class = 'bg-danger';
                    if (str_contains($erro['tipo'], 'Warning')) $tipo_class = 'bg-warning text-dark';
                    if (str_contains($erro['tipo'], 'Exception')) $tipo_class = 'bg-dark';

                    // Lógica de Cores por Status
                    $status_dot = 'bg-danger'; // pendente
                    if ($erro['status'] === 'corrigido') $status_dot = 'bg-success';
                    if ($erro['status'] === 'em_analise') $status_dot = 'bg-primary';
                ?>
                <tr>
                    <td class="ps-3">
                        <div class="d-flex flex-column gap-1">
                            <span class="badge <?php echo $tipo_class; ?> text-uppercase p-1" style="font-size: 0.6rem;">
                                <?php echo htmlspecialchars($erro['tipo']); ?>
                            </span>
                            <div class="d-flex align-items-center gap-1">
                                <span class="rounded-circle <?php echo $status_dot; ?>" style="width: 8px; height: 8px; display: inline-block;"></span>
                                <small class="text-muted text-capitalize" style="font-size: 0.7rem;"><?php echo $erro['status']; ?></small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="col-mensagem fw-bold text-dark" title="<?php echo htmlspecialchars($erro['mensagem']); ?>">
                            <?php echo htmlspecialchars($erro['mensagem']); ?>
                        </div>
                        <div class="col-arquivo text-muted mt-1" title="<?php echo $erro['arquivo']; ?>">
                            <i class="fas fa-file-code me-1"></i><?php echo basename($erro['arquivo']); ?>:<strong><?php echo $erro['linha']; ?></strong>
                        </div>
                    </td>
                    <td class="d-none d-lg-table-cell">
                        <?php if ($erro['usuario_id']): ?>
                            <div class="d-flex align-items-center">
                                <img src="<?php echo $config['base_url'] . ($erro['foto_perfil_url'] ?? 'assets/img/default-avatar.png'); ?>" 
                                     class="rounded-circle me-2 border" width="24" height="24" style="object-fit: cover;">
                                <small class="fw-bold text-truncate" style="max-width: 100px;">@<?php echo htmlspecialchars($erro['nome_de_usuario']); ?></small>
                            </div>
                        <?php else: ?>
                            <small class="text-muted"><i class="fas fa-user-secret me-1"></i>Visitante</small>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border fw-bold">
                            <?php echo $erro['ocorrencias']; ?>x
                        </span>
                    </td>
                    <td class="text-end pe-3">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary btn-detalhes-erro" 
                                    data-id="<?php echo $erro['id']; ?>"
                                    title="Ver Rastro Completo">
                                <i class="fas fa-search-plus"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-excluir-erro" 
                                    data-id="<?php echo $erro['id']; ?>"
                                    title="Eliminar Log">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (empty($lista_erros)): ?>
    <div class="text-center py-5 bg-white rounded-bottom">
        <div class="bg-light d-inline-block p-4 rounded-circle mb-3">
            <i class="fas fa-check-circle fa-3x text-success"></i>
        </div>
        <h5 class="text-dark">Nenhum erro detectado!</h5>
        <p class="text-muted small">O Sentinela está vigilante e o sistema parece saudável.</p>
    </div>
<?php endif; ?>