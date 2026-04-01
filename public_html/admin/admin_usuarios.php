<?php
require_once 'admin_auth.php'; // CORRIGIDO (agora inclui o database.php)
// $conn e $config (com $config['base_path']) já estão disponíveis aqui

$busca = $_GET['busca'] ?? '';
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';

$sql = "SELECT id, nome, sobrenome, email, role, data_cadastro, status FROM Usuarios";
$where_clauses = [];
$params = [];
$types = '';

if (!empty($busca)) {
    $where_clauses[] = "(nome LIKE ? OR sobrenome LIKE ? OR email LIKE ?)";
    $busca_param = "%" . $busca . "%";
    array_push($params, $busca_param, $busca_param, $busca_param);
    $types .= 'sss';
}
if (!empty($role_filter)) {
    $where_clauses[] = "role = ?";
    array_push($params, $role_filter);
    $types .= 's';
}
if (!empty($status_filter)) {
    $where_clauses[] = "status = ?";
    array_push($params, $status_filter);
    $types .= 's';
}
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY data_cadastro DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result_usuarios = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - Painel Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css?v=2.5">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .admin-filter-form {
            display: flex;
            flex-wrap: wrap; /* Permite que os itens quebrem para a linha de baixo em telas pequenas */
            align-items: flex-end; /* Alinha os botões com a base dos inputs */
            gap: 15px; /* Espaçamento entre todos os elementos */
        }
        .admin-filter-form .form-group {
            flex: 1; /* Faz os grupos de formulário crescerem */
            min-width: 180px; /* Largura mínima antes de quebrar a linha */
        }
        .admin-filter-form .filter-btn,
        .admin-filter-form .clear-btn {
            flex-shrink: 0; /* Impede que os botões encolham */
            margin-bottom: 0; /* Alinhado por flex-end */
        }
    </style>
    </head>
<body>

    <?php 
    include 'templates/admin_header.php'; 
    include 'templates/admin_mobile_nav.php';
    ?>

    <main class="admin-main-content">
        <a href="index.php" class="admin-back-button"><i class="fas fa-arrow-left"></i> Voltar ao Dashboard</a>
        <div class="admin-card">
            <h1><i class="fas fa-users-cog"></i> Gerenciar Usuários</h1>
            <p>Filtre, visualize e gerencie todos os usuários cadastrados na plataforma.</p>
        </div>

        <div class="admin-card">
            <h2><i class="fas fa-filter"></i> Filtros de Busca</h2>
            <form class="admin-filter-form" action="admin_usuarios.php" method="GET">
                <div class="form-group">
                    <label for="busca">Buscar (Nome, Sobrenome, E-mail)</label>
                    <input type="text" id="busca" name="busca" value="<?php echo htmlspecialchars($busca); ?>" placeholder="Digite um termo...">
                </div>
                <div class="form-group">
                    <label for="role">Função</label>
                    <select id="role" name="role">
                        <option value="">Todas</option>
                        <option value="membro" <?php echo ($role_filter === 'membro') ? 'selected' : ''; ?>>Membro</option>
                        <option value="admin" <?php echo ($role_filter === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Todos</option>
                        <option value="ativo" <?php echo ($status_filter === 'ativo') ? 'selected' : ''; ?>>Ativo</option>
                        <option value="suspenso" <?php echo ($status_filter === 'suspenso') ? 'selected' : ''; ?>>Suspenso</option>
                    </select>
                </div>
                <button type="submit" class="filter-btn">Filtrar</button>
                <a href="admin_usuarios.php" class="filter-btn clear-btn">Limpar Filtros</a>
            </form>
        </div>

        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome Completo</th>
                        <th>E-mail</th>
                        <th>Função</th>
                        <th>Data de Cadastro</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_usuarios && $result_usuarios->num_rows > 0): ?>
                        <?php while($user = $result_usuarios->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td class="user-info-cell">
                                    <a href="admin_editar_usuario.php?id=<?php echo $user['id']; ?>" title="Ver detalhes e estatísticas">
                                        <?php echo htmlspecialchars($user['nome'] . ' ' . $user['sobrenome']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="status-tag <?php echo ($user['role'] === 'admin') ? 'status-admin' : 'status-membro'; ?>">
                                        <?php echo htmlspecialchars($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo date("d/m/Y", strtotime($user['data_cadastro'])); ?></td>
                                <td>
                                    <span class="status-tag <?php echo ($user['status'] === 'ativo') ? 'status-ativo' : 'status-inativo'; ?>">
                                        <?php echo htmlspecialchars($user['status']); ?>
                                    </span>
                                </td>
                                <td class="actions-cell">
                                    <a href="admin_editar_usuario.php?id=<?php echo $user['id']; ?>" title="Editar Usuário"><i class="fas fa-edit"></i></a>
                                    
                                    <?php if ($user['id'] !== $_SESSION['user_id']): // Impede o admin de suspender a si mesmo ?>
                                        
                                        <?php 
                                        // --- [INÍCIO DA REFATORAÇÃO (GET para POST)] ---
                                        
                                        // 1. A URL da API (sem parâmetros GET)
                                        $link_toggle_status_api = $config['base_path'] . 'api/admin/toggle_user_status.php';
                                        
                                        if ($user['status'] === 'ativo'):
                                            $confirm_msg = "Tem certeza que deseja SUSPENDER este usuário?";
                                            $icon_class = "fa-ban";
                                            $title = "Suspender Usuário";
                                        else:
                                            $confirm_msg = "Tem certeza que deseja REATIVAR este usuário?";
                                            $icon_class = "fa-user-check";
                                            $title = "Reativar Usuário";
                                        endif;
                                        ?>
                                        
                                        <a href="#" 
                                           class="admin-action-btn" 
                                           data-url="<?php echo $link_toggle_status_api; ?>"
                                           data-id="<?php echo $user['id']; ?>"
                                           data-confirm-message="<?php echo $confirm_msg; ?>"
                                           title="<?php echo $title; ?>">
                                            <i class="fas <?php echo $icon_class; ?>"></i>
                                        </a>
                                        <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">Nenhum usuário encontrado com os filtros aplicados.</td>
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
<?php $stmt->close(); $conn->close(); ?>