<?php
/**
 * admin/menus/tabela_listagem.php
 * Componente: Tabela de Gerenciamento de Rotas e Menus.
 * VERSÃO: 1.1 (Data-Injection Fix - socialbr.lol)
 * PAPEL: Listar rotas e prover gatilhos de edição com metadados para o JS.
 */

// Busca todas as rotas cadastradas para a gestão total
$sql_todas = "SELECT * FROM Menus_Sistema ORDER BY parent_id ASC, ordem ASC";
$stmt_todas = $pdo->query($sql_todas);
$todas_rotas = $stmt_todas->fetchAll();
?>

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light">
            <tr>
                <th class="ps-3" style="width: 50px;">Ícone</th>
                <th>Nome / Label</th>
                <th class="d-none d-md-table-cell">Slug (URL)</th>
                <th class="d-none d-lg-table-cell">Destino Físico</th>
                <th>Acesso</th>
                <th class="text-center">Status</th>
                <th class="text-end pe-3">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($todas_rotas as $rota): ?>
                <tr>
                    <td class="ps-3">
                        <div class="d-flex align-items-center justify-content-center bg-light rounded" style="width: 35px; height: 35px;">
                            <i class="<?php echo $rota['icone']; ?> text-primary"></i>
                        </div>
                    </td>
                    <td>
                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($rota['label']); ?></div>
                        <?php if ($rota['parent_id']): ?>
                            <small class="text-muted"><i class="fas fa-level-up-alt fa-rotate-90 me-1"></i>Submenu</small>
                        <?php elseif ($rota['exibir_no_menu']): ?>
                            <small class="text-success"><i class="fas fa-eye me-1"></i>Visível no Menu</small>
                        <?php else: ?>
                            <small class="text-warning"><i class="fas fa-eye-slash me-1"></i>Rota Oculta</small>
                        <?php endif; ?>
                    </td>
                    <td class="d-none d-md-table-cell">
                        <code class="text-primary">/<?php echo $rota['slug']; ?></code>
                    </td>
                    <td class="d-none d-lg-table-cell">
                        <small class="text-muted"><?php echo $rota['arquivo_destino']; ?></small>
                    </td>
                    <td>
                        <?php 
                        $badge_class = 'bg-secondary';
                        if ($rota['permissao'] === 'admin') $badge_class = 'bg-danger';
                        if ($rota['permissao'] === 'logado') $badge_class = 'bg-info';
                        ?>
                        <span class="badge <?php echo $badge_class; ?> text-uppercase" style="font-size: 0.65rem;">
                            <?php echo $rota['permissao']; ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="form-check form-switch d-flex justify-content-center">
                            <input class="form-check-input btn-toggle-status" type="checkbox" 
                                   data-id="<?php echo $rota['id']; ?>"
                                   <?php echo $rota['status'] ? 'checked' : ''; ?>>
                        </div>
                    </td>
                    <td class="text-end pe-3">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary btn-editar-rota" 
                                    data-id="<?php echo $rota['id']; ?>"
                                    data-permite-parametros="<?php echo $rota['permite_parametros']; ?>"
                                    title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-excluir-rota" 
                                    data-id="<?php echo $rota['id']; ?>"
                                    title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (empty($todas_rotas)): ?>
    <div class="text-center py-5">
        <i class="fas fa-route fa-3x text-light mb-3"></i>
        <p class="text-muted">Nenhuma rota encontrada no sistema.</p>
    </div>
<?php endif; ?>