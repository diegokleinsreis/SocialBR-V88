<?php
/**
 * admin/busca/tabela_erros.php
 * PAPEL: Listar termos pesquisados que não retornaram resultados.
 * VERSÃO: 1.1 - Inteligência de Lacunas com Rotas Corrigidas (socialbr.lol)
 */

// 1. GARANTIA DE DADOS (Injetados pelo admin_busca.php)
// $buscasFalhas contém: [termo, total]
?>

<div class="admin-table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th><i class="fas fa-keyboard"></i> Termo Pesquisado</th>
                <th class="text-center"><i class="fas fa-redo"></i> Ocorrências</th>
                <th class="text-center"><i class="fas fa-lightbulb"></i> Sugestão de Ação</th>
                <th class="text-right"><i class="fas fa-tools"></i> Gestão</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($buscasFalhas)): ?>
                <tr>
                    <td colspan="4" class="text-center py-4">
                        <div class="empty-state-table">
                            <i class="fas fa-check-circle text-success"></i>
                            <p>Excelente! Não há registros de buscas sem resultados recentemente.</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($buscasFalhas as $erro): ?>
                    <tr>
                        <td class="td-termo">
                            <strong>"<?php echo htmlspecialchars($erro['termo']); ?>"</strong>
                        </td>
                        <td class="text-center">
                            <span class="badge-count"><?php echo $erro['total']; ?> vezes</span>
                        </td>
                        <td class="text-center">
                            <?php 
                                // Lógica visual de sugestão baseada no volume de erros
                                if ($erro['total'] > 10) {
                                    echo '<span class="text-danger"><i class="fas fa-fire"></i> Crítico: Criar conteúdo</span>';
                                } else {
                                    echo '<span class="text-muted">Avaliar sinônimo</span>';
                                }
                            ?>
                        </td>
                        <td class="text-right">
                            <div class="btn-group-actions">
                                <a href="<?php echo $config['base_path']; ?>admin/busca_sinonimos?sugerir=<?php echo urlencode($erro['termo']); ?>" 
                                   class="btn-action btn-outline" title="Mapear Sinônimo">
                                    <i class="fas fa-book"></i>
                                </a>
                                <a href="<?php echo $config['base_path']; ?>admin/Palavras_Proibidas?adicionar=<?php echo urlencode($erro['termo']); ?>" 
                                   class="btn-action btn-danger-outline" title="Banir Termo">
                                    <i class="fas fa-ban"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
/**
 * ESTILOS DA TABELA DE ERROS
 */
.admin-table-responsive {
    width: 100%;
    overflow-x: auto;
    background: #fff;
    border-radius: 8px;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.admin-table th {
    background: #f8f9fa;
    padding: 15px;
    text-align: left;
    color: #0C2D54;
    font-weight: 700;
    border-bottom: 2px solid #dee2e6;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.admin-table td {
    padding: 15px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.td-termo strong {
    color: #1c1e21;
    font-size: 0.95rem;
}

.badge-count {
    background: #fff3cd;
    color: #856404;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 700;
}

.btn-group-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

.btn-action {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s;
    text-decoration: none;
}

.btn-outline {
    border: 1px solid #0C2D54;
    color: #0C2D54;
}

.btn-outline:hover {
    background: #0C2D54;
    color: #fff;
}

.btn-danger-outline {
    border: 1px solid #dc3545;
    color: #dc3545;
}

.btn-danger-outline:hover {
    background: #dc3545;
    color: #fff;
}

.empty-state-table {
    padding: 30px;
    color: #65676b;
}

.empty-state-table i {
    font-size: 2rem;
    margin-bottom: 10px;
}
</style>