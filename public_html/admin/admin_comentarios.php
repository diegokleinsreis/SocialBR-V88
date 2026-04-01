<?php
require_once 'admin_auth.php'; // CORRIGIDO (agora inclui o database.php)
// O $config['base_path'] e $asset_version já estão disponíveis aqui
// require_once __DIR__ . '/../../config/database.php'; // Já não é necessário, pois o admin_auth.php já o chama

// Pega os valores dos filtros da URL (se existirem)
$busca = $_GET['busca'] ?? '';
$status_filter = $_GET['status'] ?? '';

// ATUALIZAÇÃO: Adicionamos um sub-select para contar o número de edições de cada comentário.
$sql = "SELECT
            c.id, c.conteudo_texto, c.data_comentario, c.status, c.id_postagem,
            u.nome, u.sobrenome,
            (SELECT COUNT(id) FROM Comentarios_Edicoes WHERE id_comentario = c.id) as total_edicoes
        FROM Comentarios AS c
        JOIN Usuarios AS u ON c.id_usuario = u.id";

$where_clauses = [];
$params = [];
$types = '';

if (!empty($busca)) {
    $where_clauses[] = "c.conteudo_texto LIKE ?";
    $busca_param = "%" . $busca . "%";
    array_push($params, $busca_param);
    $types .= 's';
}

if (!empty($status_filter)) {
    $where_clauses[] = "c.status = ?";
    array_push($params, $status_filter);
    $types .= 's';
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY c.data_comentario DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result_comments = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Comentários - Painel Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css?v=2.5">
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
            <h1><i class="fas fa-comments"></i> Gerenciar Comentários</h1>
            <p>Filtre, visualize e gerencie todos os comentários da plataforma.</p>
        </div>

        <div class="admin-card">
            <h2><i class="fas fa-filter"></i> Filtros de Busca</h2>
            <form class="admin-filter-form" action="admin_comentarios.php" method="GET">
                <div class="form-group">
                    <label for="busca">Buscar (Conteúdo do Comentário)</label>
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
                <button type="submit" class="filter-btn">Filtrar</button>
                <a href="admin_comentarios.php" class="filter-btn clear-btn">Limpar Filtros</a>
            </form>
        </div>

        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Conteúdo</th>
                        <th>Autor</th>
                        <th>Em Resposta a (Post ID)</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_comments && $result_comments->num_rows > 0): ?>
                        <?php while($comment = $result_comments->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $comment['id']; ?></td>
                                <td class="comment-content-cell">
                                    <a href="<?php echo $config['base_path']; ?>postagem/<?php echo $comment['id_postagem']; ?>#comment-<?php echo $comment['id']; ?>" target="_blank" title="Ver comentário no site">
                                        <?php echo htmlspecialchars(mb_strimwidth($comment['conteudo_texto'], 0, 100, "...")); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($comment['nome'] . ' ' . $comment['sobrenome']); ?></td>
                                <td>
                                    <a href="<?php echo $config['base_path']; ?>postagem/<?php echo $comment['id_postagem']; ?>" target="_blank">
                                        Post #<?php echo $comment['id_postagem']; ?>
                                    </a>
                                </td>
                                <td><?php echo date("d/m/Y H:i", strtotime($comment['data_comentario'])); ?></td>
                                <td>
                                    <span class="status-tag <?php echo ($comment['status'] === 'ativo') ? 'status-ativo' : 'status-inativo'; ?>">
                                        <?php echo htmlspecialchars($comment['status']); ?>
                                    </span>
                                </td>
                                <td class="actions-cell">
                                    <a href="<?php echo $config['base_path']; ?>postagem/<?php echo $comment['id_postagem']; ?>#comment-<?php echo $comment['id']; ?>" target="_blank" title="Ver Comentário"><i class="fas fa-external-link-alt"></i></a>
                                    
                                    <?php 
                                    // --- [INÍCIO DA REFATORAÇÃO (GET para POST)] ---
                                    
                                    // 1. A URL da API (sem parâmetros GET)
                                    $link_toggle_status_api = $config['base_path'] . 'api/admin/toggle_comment_status.php';
                                    
                                    if ($comment['status'] === 'ativo'):
                                        $confirm_msg = "Tem certeza que deseja OCULTAR este comentário?";
                                        $icon_class = "fa-eye-slash";
                                        $title = "Ocultar Comentário";
                                    else:
                                        $confirm_msg = "Tem certeza que deseja REATIVAR este comentário?";
                                        $icon_class = "fa-eye";
                                        $title = "Reativar Comentário";
                                    endif;
                                    ?>
                                    
                                    <a href="#" 
                                       class="admin-action-btn" 
                                       data-url="<?php echo $link_toggle_status_api; ?>"
                                       data-id="<?php echo $comment['id']; ?>"
                                       data-confirm-message="<?php echo $confirm_msg; ?>"
                                       title="<?php echo $title; ?>">
                                        <i class="fas <?php echo $icon_class; ?>"></i>
                                    </a>
                                    
                                    <?php if ($comment['total_edicoes'] > 0): ?>
                                        <a href="admin_historicos.php?tipo=comentario&id=<?php echo $comment['id']; ?>" title="Ver Histórico de Edições (<?php echo $comment['total_edicoes']; ?>)">
                                            <i class="fas fa-history"></i>
                                        </a>
                                    <?php endif; ?>
                                    </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">Nenhum comentário encontrado com os filtros aplicados.</td>
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