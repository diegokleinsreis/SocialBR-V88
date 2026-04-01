<?php
require_once 'admin_auth.php'; // CORRIGIDO (agora inclui o database.php)
// $conn, $config, e $asset_version (do database.php) já estão disponíveis aqui

// Pega os valores dos filtros da URL (se existirem)
$busca = $_GET['busca'] ?? '';
$status_filter = $_GET['status'] ?? '';

// --- [LÓGICA DE FILTRO DE DATA] ---
$data_filtro = $_GET['data'] ?? ''; 
// --- [FIM DA LÓGICA] ---


// --- [INÍCIO DA CORREÇÃO SQL COMPLETA] ---
// Esta query funde a nova estrutura de colunas (V59) com a lógica de JOIN, WHERE e ORDER BY do seu código antigo.

$sql = "SELECT 
            p.id, p.conteudo_texto, p.data_postagem, p.status, p.tipo_media,
            u.nome, u.sobrenome,
            (SELECT COUNT(id) FROM Postagens_Edicoes WHERE id_postagem = p.id) as total_edicoes,
            (SELECT COUNT(id) FROM Curtidas WHERE id_postagem = p.id) as total_curtidas,
            (SELECT COUNT(id) FROM Comentarios WHERE id_postagem = p.id AND status = 'ativo') as total_comentarios,
            COUNT(DISTINCT lvp.id) as total_visualizacoes
        FROM Postagens AS p
        JOIN Usuarios AS u ON p.id_usuario = u.id
        LEFT JOIN Logs_Visualizacao_Post AS lvp ON p.id = lvp.id_postagem"; // Corrigido

$where_clauses = [];
$params = [];
$types = '';

if (!empty($busca)) {
    $where_clauses[] = "p.conteudo_texto LIKE ?";
    $busca_param = "%" . $busca . "%";
    array_push($params, $busca_param);
    $types .= 's';
}

if (!empty($status_filter)) {
    $where_clauses[] = "p.status = ?";
    array_push($params, $status_filter);
    $types .= 's';
}

// Lógica de filtro de data (do seu código antigo)
if (!empty($data_filtro)) {
    $where_clauses[] = "DATE(lvp.data_visualizacao) = ?";
    array_push($params, $data_filtro);
    $types .= 's';
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

// Agrupamento (do seu código antigo)
$sql .= " GROUP BY p.id";

// Lógica de ordenação (do seu código antigo)
$order_by = !empty($data_filtro) ? "total_visualizacoes DESC" : "p.data_postagem DESC";
$sql .= " ORDER BY $order_by";

// --- [FIM DA CORREÇÃO SQL COMPLETA] ---


$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result_posts = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Postagens - Painel Admin</title>
    
    <link rel="stylesheet" href="assets/css/admin.css?v=<?php echo $asset_version; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    </head>
<body>

    <?php 
    include 'templates/admin_header.php'; 
    include 'templates/admin_mobile_nav.php';
    ?>

    <main class="admin-main-content">
        <a href="index.php" class="admin-back-button"><i class="fas fa-arrow-left"></i> Voltar ao Dashboard</a>
        <div class="admin-card">
            <h1><i class="fas fa-file-alt"></i> Gerenciar Postagens</h1>
            <p>Filtre, visualize e gerencie todas as postagens da plataforma.</p>
        </div>

        <div class="admin-card">
            <h2><i class="fas fa-filter"></i> Filtros de Busca</h2>
            <form class="admin-filter-form" action="admin_postagens.php" method="GET">
                <div class="form-group">
                    <label for="busca">Buscar (Conteúdo do Post)</label>
                    <input type="text" id="busca" name="busca" value="<?php echo htmlspecialchars($busca); ?>" placeholder="Digite um termo...">
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Todos</option>
                        <option value="ativo" <?php echo ($status_filter === 'ativo') ? 'selected' : ''; ?>>Ativo</option>
                        <option value="inativo" <?php echo ($status_filter === 'inativo') ? 'selected' : ''; ?>>Inativo (Oculto)</option>
                        <option value="excluido_pelo_usuario" <?php echo ($status_filter === 'excluido_pelo_usuario') ? 'selected' : ''; ?>>Excluído pelo Usuário</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="data">Data de Visualização (YYYY-MM-DD)</label>
                    <input type="date" id="data" name="data" value="<?php echo htmlspecialchars($data_filtro); ?>">
                </div>
                <button type="submit" class="filter-btn">Filtrar</button>
                <a href="admin_postagens.php" class="filter-btn clear-btn">Limpar Filtros</a>
            </form>
        </div>

        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Conteúdo</th>
                        <th>Autor</th>
                        <th>Data</th>
                        <th>Mídia</th>
                        <th>Números</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_posts && $result_posts->num_rows > 0): ?>
                        <?php while($post = $result_posts->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $post['id']; ?></td>
                                <td class="post-content-cell">
                                    <a href="<?php echo $config['base_path']; ?>postagem/<?php echo $post['id']; ?>" target="_blank" title="Ver postagem no site">
                                        <?php echo htmlspecialchars(mb_strimwidth($post['conteudo_texto'], 0, 100, "...")); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($post['nome'] . ' ' . $post['sobrenome']); ?></td>
                                <td><?php echo date("d/m/Y H:i", strtotime($post['data_postagem'])); ?></td>
                                <td>
                                    <?php if ($post['tipo_media']): ?>
                                        <span class="status-tag <?php echo ($post['tipo_media'] === 'video') ? 'status-membro' : 'status-pendente'; ?>">
                                            <?php echo htmlspecialchars($post['tipo_media']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span>N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="stats-cell">
                                    <i class="fas fa-eye" title="Visualizações"></i> <?php echo $post['total_visualizacoes']; ?><br>
                                    <i class="fas fa-thumbs-up" title="Curtidas"></i> <?php echo $post['total_curtidas']; ?><br>
                                    <i class="fas fa-comments" title="Comentários"></i> <?php echo $post['total_comentarios']; ?>
                                </td>
                                <td>
                                    <span class="status-tag <?php echo ($post['status'] === 'ativo') ? 'status-ativo' : 'status-inativo'; ?>">
                                        <?php echo htmlspecialchars($post['status']); ?>
                                    </span>
                                </td>
                                <td class="actions-cell">
                                    <a href="<?php echo $config['base_path']; ?>postagem/<?php echo $post['id']; ?>" target="_blank" title="Ver Postagem"><i class="fas fa-external-link-alt"></i></a>
                                    
                                    <?php 
                                    // --- [INÍCIO DA REFATORAÇÃO (GET para POST)] ---
                                    
                                    // 1. A URL da API (sem parâmetros GET)
                                    $link_toggle_status_api = $config['base_path'] . 'api/admin/toggle_post_status.php';
                                    
                                    if ($post['status'] === 'ativo'):
                                        $confirm_msg = "Tem certeza que deseja OCULTAR esta postagem?";
                                        $icon_class = "fa-eye-slash";
                                        $title = "Ocultar Postagem";
                                    else:
                                        $confirm_msg = "Tem certeza que deseja REATIVAR esta postagem?";
                                        $icon_class = "fa-eye";
                                        $title = "Reativar Postagem";
                                    endif;
                                    ?>
                                    
                                    <a href="#" 
                                       class="admin-action-btn" 
                                       data-url="<?php echo $link_toggle_status_api; ?>"
                                       data-id="<?php echo $post['id']; ?>"
                                       data-confirm-message="<?php echo $confirm_msg; ?>"
                                       title="<?php echo $title; ?>">
                                        <i class="fas <?php echo $icon_class; ?>"></i>
                                    </a>
                                    
                                    <?php if ($post['total_edicoes'] > 0): ?>
                                        <a href="admin_historicos.php?tipo=post&id=<?php echo $post['id']; ?>" title="Ver Histórico de Edições (<?php echo $post['total_edicoes']; ?>)">
                                            <i class="fas fa-history"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9">Nenhuma postagem encontrada com os filtros aplicados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <script>
        const BASE_PATH = '<?php echo $config['base_path']; ?>';
        const CSRF_TOKEN = '<?php echo get_csrf_token(); ?>';
    </script>
    
</body>
</html>
<?php 
$stmt->close();
$conn->close();
?>