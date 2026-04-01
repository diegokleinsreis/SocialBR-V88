<?php
require_once 'admin_auth.php'; // CORRIGIDO (agora inclui o database.php)
// $conn e $config (com $config['base_path']) já estão disponíveis aqui
// require_once __DIR__ . '/../../config/database.php'; // Já não é necessário

$user_id_to_edit = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id_to_edit <= 0) { die("ID de usuário inválido."); }

// Busca dados do usuário para o formulário
$sql = "SELECT * FROM Usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id_to_edit);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
if (!$usuario) { die("Usuário não encontrado."); }

// --- CORREÇÃO: Busca bairros usando o ID da cidade da configuração ---
$id_cidade_padrao = (int)($config['CIDADE_PADRAO_ID'] ?? 129);
$sql_bairros = "SELECT id, nome FROM Bairros WHERE id_cidade = " . $id_cidade_padrao . " ORDER BY nome ASC";
$result_bairros = $conn->query($sql_bairros);
// --- FIM DA CORREÇÃO ---

// --- QUERIES DE ESTATÍSTICAS (não precisam de alteração) ---
$uid = $user_id_to_edit;

// Totais de conteúdo criado
$total_posts = $conn->query("SELECT COUNT(id) AS total FROM Postagens WHERE id_usuario = $uid")->fetch_assoc()['total'];
$total_comentarios = $conn->query("SELECT COUNT(id) AS total FROM Comentarios WHERE id_usuario = $uid")->fetch_assoc()['total'];

// Totais de curtidas
$curtidas_feitas_posts = $conn->query("SELECT COUNT(id) AS total FROM Curtidas WHERE id_usuario = $uid")->fetch_assoc()['total'];
$curtidas_feitas_comentarios = $conn->query("SELECT COUNT(id) AS total FROM Curtidas_Comentarios WHERE id_usuario = $uid")->fetch_assoc()['total'];
$total_curtidas_feitas = $curtidas_feitas_posts + $curtidas_feitas_comentarios;
$total_curtidas_recebidas = $conn->query("SELECT COUNT(c.id) AS total FROM Curtidas c JOIN Postagens p ON c.id_postagem = p.id WHERE p.id_usuario = $uid")->fetch_assoc()['total'];

// Denúncias Recebidas (Total e Pendentes)
$where_not_archived = "AND d.status != 'excluida_pelo_adm'";
$total_denuncias_recebidas_perfil = $conn->query("SELECT COUNT(id) AS total FROM Denuncias d WHERE d.tipo_conteudo = 'usuario' AND d.id_conteudo = $uid $where_not_archived")->fetch_assoc()['total'];
$total_denuncias_recebidas_posts = $conn->query("SELECT COUNT(d.id) AS total FROM Denuncias d JOIN Postagens p ON d.id_conteudo = p.id WHERE d.tipo_conteudo = 'post' AND p.id_usuario = $uid $where_not_archived")->fetch_assoc()['total'];
$total_denuncias_recebidas_comentarios = $conn->query("SELECT COUNT(d.id) AS total FROM Denuncias d JOIN Comentarios c ON d.id_conteudo = c.id WHERE d.tipo_conteudo = 'comentario' AND c.id_usuario = $uid $where_not_archived")->fetch_assoc()['total'];
$total_denuncias_recebidas = $total_denuncias_recebidas_perfil + $total_denuncias_recebidas_posts + $total_denuncias_recebidas_comentarios;

$pendentes_denuncias_recebidas_perfil = $conn->query("SELECT COUNT(id) AS total FROM Denuncias WHERE tipo_conteudo = 'usuario' AND id_conteudo = $uid AND status = 'pendente'")->fetch_assoc()['total'];
$pendentes_denuncias_recebidas_posts = $conn->query("SELECT COUNT(d.id) AS total FROM Denuncias d JOIN Postagens p ON d.id_conteudo = p.id WHERE d.tipo_conteudo = 'post' AND p.id_usuario = $uid AND d.status = 'pendente'")->fetch_assoc()['total'];
$pendentes_denuncias_recebidas_comentarios = $conn->query("SELECT COUNT(d.id) AS total FROM Denuncias d JOIN Comentarios c ON d.id_conteudo = c.id WHERE d.tipo_conteudo = 'comentario' AND c.id_usuario = $uid AND d.status = 'pendente'")->fetch_assoc()['total'];
$total_pendentes_denuncias_recebidas = $pendentes_denuncias_recebidas_perfil + $pendentes_denuncias_recebidas_posts + $pendentes_denuncias_recebidas_comentarios;

$total_salvos_recebidos = $conn->query("SELECT COUNT(ps.id) as total FROM Postagens_Salvas ps JOIN Postagens p ON ps.id_postagem = p.id WHERE p.id_usuario = $uid")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário #<?php echo $usuario['id']; ?> - Painel Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css?v=2.9">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'templates/admin_header.php'; ?>
    <main class="admin-main-content">
        <a href="admin_usuarios.php" class="admin-back-button"><i class="fas fa-arrow-left"></i> Voltar para a Lista</a>
        <div class="admin-card">
            <h1><i class="fas fa-user-cog"></i> Usuário: <?php echo htmlspecialchars($usuario['nome'] . ' ' . $usuario['sobrenome']); ?></h1>
            <p>Gerencie as informações e veja as estatísticas de atividade do usuário abaixo.</p>
        </div>

        <div class="admin-card">
            <h2><i class="fas fa-chart-bar"></i> Estatísticas e Atividade</h2>
            <div class="stats-list">
                <div class="stats-list-item">
                    <i class="fas fa-flag stat-icon"></i>
                    <span class="stat-label">Denúncias Recebidas (Visíveis / Pendentes)</span>
                    <?php
                        $denuncia_color = '#28a745'; // Verde
                        if ($total_denuncias_recebidas > 4) $denuncia_color = '#ffc107'; // Amarelo
                        if ($total_denuncias_recebidas > 9) $denuncia_color = '#dc3545'; // Vermelho
                    ?>
                    <span class="stat-value" style="color: <?php echo $denuncia_color; ?>;"><?php echo $total_denuncias_recebidas; ?> (<?php echo $total_pendentes_denuncias_recebidas; ?>)</span>
                    <?php if ($total_denuncias_recebidas > 0): ?>
                        <?php if ($total_pendentes_denuncias_recebidas > 0): ?>
                            <a href="#" class="zerar-btn disabled" onclick="alert('Impossível arquivar o histórico. Resolva as denúncias pendentes primeiro.'); return false;">Arquivar Histórico</a>
                        <?php else: ?>
                            <a href="#" 
                               class="zerar-btn admin-action-btn"
                               data-url="<?php echo $config['base_path']; ?>api/admin/zerar_denuncias_usuario.php"
                               data-id="<?php echo $user_id_to_edit; ?>"
                               data-confirm-message="Isso irá arquivar (ocultar) o histórico de denúncias já resolvidas deste usuário. Deseja continuar?">
                               Arquivar Histórico
                            </a>
                            <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="stats-list-item"><i class="fas fa-file-alt stat-icon"></i><span class="stat-label">Postagens Criadas</span><span class="stat-value"><?php echo $total_posts; ?></span></div>
                <div class="stats-list-item"><i class="fas fa-comments stat-icon"></i><span class="stat-label">Comentários Feitos</span><span class="stat-value"><?php echo $total_comentarios; ?></span></div>
                <div class="stats-list-item"><i class="fas fa-thumbs-up stat-icon"></i><span class="stat-label">Curtidas Recebidas</span><span class="stat-value"><?php echo $total_curtidas_recebidas; ?></span></div>
                <div class="stats-list-item"><i class="fas fa-heart stat-icon"></i><span class="stat-label">Curtidas Feitas</span><span class="stat-value"><?php echo $total_curtidas_feitas; ?></span></div>
                <div class="stats-list-item"><i class="fas fa-bookmark stat-icon"></i><span class="stat-label">Posts Salvos por Outros</span><span class="stat-value"><?php echo $total_salvos_recebidos; ?></span></div>
                <div class="stats-list-item"><i class="fas fa-users stat-icon"></i><span class="stat-label">Amigos</span><span class="stat-value">N/D</span></div>
                <div class="stats-list-item"><i class="fas fa-user-friends stat-icon"></i><span class="stat-label">Grupos</span><span class="stat-value">N/D</span></div>
                <div class="stats-list-item"><i class="fas fa-building stat-icon"></i><span class="stat-label">Páginas Curtidas</span><span class="stat-value">N/D</span></div>
            </div>
        </div>

        <div class="admin-card">
            <h2><i class="fas fa-edit"></i> Gerenciar Informações</h2>
            <form class="admin-form" action="<?php echo $config['base_path']; ?>api/admin/atualizar_usuario.php" method="POST">
                
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

                <input type="hidden" name="id_usuario_a_editar" value="<?php echo $usuario['id']; ?>">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="nome">Nome</label>
                        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="sobrenome">Sobrenome</label>
                        <input type="text" id="sobrenome" name="sobrenome" value="<?php echo htmlspecialchars($usuario['sobrenome']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="nome_de_usuario">Nome de Usuário</label>
                        <input type="text" id="nome_de_usuario" name="nome_de_usuario" value="<?php echo htmlspecialchars($usuario['nome_de_usuario']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                    </div>
                    <div class="form-group full-width">
                        <label for="id_bairro">Bairro</label>
                        <select id="id_bairro" name="id_bairro" required>
                            <option value="">Selecione um bairro</option>
                            <?php if ($result_bairros && $result_bairros->num_rows > 0): mysqli_data_seek($result_bairros, 0); while($bairro = $result_bairros->fetch_assoc()): $selected = ($bairro['id'] == $usuario['id_bairro']) ? 'selected' : ''; echo '<option value="' . htmlspecialchars($bairro['id']) . '" ' . $selected . '>' . htmlspecialchars($bairro['nome']) . '</option>'; endwhile; endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="role">Função (Role)</label>
                        <select id="role" name="role" <?php echo ($usuario['id'] === $_SESSION['user_id']) ? 'disabled' : ''; ?>><option value="membro" <?php echo ($usuario['role'] === 'membro') ? 'selected' : ''; ?>>Membro</option><option value="admin" <?php echo ($usuario['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option></select>
                        <?php if ($usuario['id'] === $_SESSION['user_id']): ?><small>Você não pode alterar sua própria função.</small><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" <?php echo ($usuario['id'] === $_SESSION['user_id']) ? 'disabled' : ''; ?>><option value="ativo" <?php echo ($usuario['status'] === 'ativo') ? 'selected' : ''; ?>>Ativo</option><option value="suspenso" <?php echo ($usuario['status'] === 'suspenso') ? 'selected' : ''; ?>>Suspenso</option></select>
                        <?php if ($usuario['id'] === $_SESSION['user_id']): ?><small>Você não pode suspender sua própria conta.</small><?php endif; ?>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label for="nova_senha">Nova Senha (deixe em branco para não alterar)</label>
                    <input type="password" id="nova_senha" name="nova_senha" placeholder="Digite uma nova senha se quiser redefinir">
                </div>

                <button type="submit" class="filter-btn">Salvar Alterações</button>
            </form>
        </div>
    </main>

    <script>
        const BASE_PATH = '<?php echo $config['base_path']; ?>';
        const CSRF_TOKEN = '<?php echo get_csrf_token(); ?>';
    </script>
    

</body>
</html>
<?php $stmt->close(); $conn->close(); ?>