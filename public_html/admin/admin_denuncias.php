<?php
require_once 'admin_auth.php'; // CORRIGIDO (agora inclui o database.php)
// $conn e $config (com $config['base_path']) já estão disponíveis aqui

// --- NOVA LÓGICA DE ABAS ---
$active_tab = $_GET['tab'] ?? 'conteudo';
// --- FIM DA NOVA LÓGICA ---

// --- [INÍCIO DA ATUALIZAÇÃO] Lógica de Filtros ---
$busca_motivo = $_GET['busca_motivo'] ?? ''; // Para o Motivo
$busca_usuario = $_GET['busca_usuario'] ?? ''; // Para o Usuário
$tipo_filtro = $_GET['tipo'] ?? ''; // Para post/comentario
// --- [FIM DA ATUALIZAÇÃO] ---
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Denúncias - Painel Admin</title>
    
    <link rel="stylesheet" href="assets/css/admin.css?v=<?php echo $asset_version; ?>">
    <link rel="stylesheet" href="assets/css/components/_admin_modal.css?v=<?php echo $asset_version; ?>">
    
    <?php // --- ESTILOS DO FEED PARA O MODAL DE DENÚNCIA --- ?>
    <link rel="stylesheet" href="../assets/css/components/_post.css?v=<?php echo $asset_version; ?>">
    <link rel="stylesheet" href="../assets/css/components/_post_interactive.css?v=<?php echo $asset_version; ?>">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script>
        const BASE_PATH = '<?php echo $config['base_path']; ?>';
        const CSRF_TOKEN = '<?php echo get_csrf_token(); ?>';
    </script>
    
    <style>
        /* Define os estilos dos botões de ação que estão fora do modal */
        .admin-table .action-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
            color: white; 
        }
        .admin-table .action-btn.ignore-btn {
            background-color: #ffc107; 
            color: #212529; 
        }
        .admin-table .action-btn.hide-btn {
            background-color: #dc3545; 
            color: white;
        }
        .admin-table .action-btn.view-btn {
            background-color: #0d6efd; 
            color: white;
        }

        /* --- [CORREÇÃO V96.4: POSICIONAMENTO E VISIBILIDADE DOS BOTÕES] --- */
        
        .admin-modal {
            align-items: flex-start; /* Alinha o modal ao topo da tela */
            padding-top: 20px;
        }

        .admin-modal-content {
            display: flex;
            flex-direction: column;
            max-height: 96vh; /* Quase o ecrã todo, mas com margem de segurança */
            width: 95%;
            max-width: 700px;
            margin: 0 auto; /* Removido o margin-top daqui para usar o padding do pai */
            position: relative;
            overflow: hidden; /* IMPORTANTE: Impede que o modal cresça para fora da tela */
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.3);
        }

        .admin-modal-header {
            flex-shrink: 0;
            padding: 15px 25px;
            background: #fff;
            border-bottom: 1px solid #eee;
            z-index: 10;
        }

        .admin-modal-body {
            flex: 1; /* Ocupa todo o espaço disponível entre header e footer */
            overflow-y: auto; /* Barra de rolagem apenas aqui */
            padding: 20px 25px;
            background: #fdfdfd;
        }

        .admin-modal-actions {
            flex-shrink: 0; /* Garante que os botões nunca encolham ou sumam */
            padding: 15px 25px;
            background: #fff;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            z-index: 10;
        }

        /* Ajustes de Imagem dentro do Modal */
        #denunciaConteudo img, 
        #denunciaConteudo video {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            display: block;
            margin: 10px 0;
        }

        #denunciaConteudo .post-card {
            box-shadow: none;
            border: 1px solid var(--cor-borda, #dddfe2);
            margin-bottom: 0;
            padding: 15px;
        }

        /* Estilo para garantir que o 'X' de fechar fique visível */
        .admin-modal-close {
            font-size: 28px;
            color: #aaa;
            cursor: pointer;
            line-height: 1;
        }

        /* [NOVO] Estilo para a descrição detalhada na tabela e no modal */
        .desc-resumo {
            font-style: italic;
            color: #666;
            font-size: 0.85rem;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
        }
        
        .denuncia-detalhes-box {
            background: #f8f9fa;
            border-left: 4px solid #0C2D54;
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 0 8px 8px 0;
        }
        
        .denuncia-detalhes-label {
            display: block;
            font-weight: 800;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #0C2D54;
            margin-bottom: 5px;
        }
        
        .denuncia-detalhes-texto {
            font-size: 0.95rem;
            color: #333;
            line-height: 1.4;
            white-space: pre-wrap;
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
            <h1><i class="fas fa-gavel"></i> Gerenciar Denúncias</h1>
            <p>Revise o conteúdo e os usuários que foram denunciados pela comunidade.</p>
        </div>

        <div class="admin-tabs">
            <a href="?tab=conteudo" class="admin-tab-link <?php echo ($active_tab === 'conteudo') ? 'active' : ''; ?>">
                Denúncias de Conteúdo (Posts/Comentários)
            </a>
            <a href="?tab=usuarios" class="admin-tab-link <?php echo ($active_tab === 'usuarios') ? 'active' : ''; ?>">
                Denúncias de Usuários
            </a>
        </div>

        <div class="admin-card">
            <h2><i class="fas fa-filter"></i> Filtros de Busca</h2>
            <form class="admin-filter-form" action="admin_denuncias.php" method="GET">
                <input type="hidden" name="tab" value="<?php echo htmlspecialchars($active_tab); ?>">

                <div class="form-group">
                    <label for="busca_motivo">Buscar (Motivo)</label>
                    <input type="text" id="busca_motivo" name="busca_motivo" value="<?php echo htmlspecialchars($busca_motivo); ?>" placeholder="Digite um motivo...">
                </div>

                <div class="form-group">
                    <label for="busca_usuario">Buscar Usuário (Autor ou Denunciado)</label>
                    <input type="text" id="busca_usuario" name="busca_usuario" value="<?php echo htmlspecialchars($busca_usuario); ?>" placeholder="Nome, sobrenome ou e-mail...">
                </div>

                <?php if ($active_tab === 'conteudo'): ?>
                    <div class="form-group">
                        <label for="tipo">Tipo de Conteúdo</label>
                        <select id="tipo" name="tipo">
                            <option value="">Todos</option>
                            <option value="post" <?php echo ($tipo_filtro === 'post') ? 'selected' : ''; ?>>Post</option>
                            <option value="comentario" <?php echo ($tipo_filtro === 'comentario') ? 'selected' : ''; ?>>Comentário</option>
                        </select>
                    </div>
                <?php endif; ?>

                <button type="submit" class="filter-btn">Filtrar</button>
                <a href="admin_denuncias.php?tab=<?php echo htmlspecialchars($active_tab); ?>" class="filter-btn clear-btn">Limpar Filtros</a>
            </form>
        </div>

        <?php if ($active_tab === 'usuarios'): ?>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Denunciado</th>
                            <th>Autor</th>
                            <th>Motivo</th>
                            <th>Descrição Adicional</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // [ATUALIZADO] Incluída a coluna 'descricao'
                        $sql = "SELECT 
                                    d.id, d.id_conteudo AS id_usuario_denunciado, d.motivo, d.descricao, d.data_denuncia,
                                    u_denunciado.nome AS nome_denunciado, u_denunciado.sobrenome AS sobrenome_denunciado, u_denunciado.email AS email_denunciado,
                                    u_autor.nome AS nome_autor, u_autor.sobrenome AS sobrenome_autor, u_autor.email AS email_autor
                                FROM Denuncias AS d
                                JOIN Usuarios AS u_denunciado ON d.id_conteudo = u_denunciado.id
                                JOIN Usuarios AS u_autor ON d.id_usuario_denunciou = u_autor.id
                                WHERE d.tipo_conteudo = 'usuario' AND d.status = 'pendente'";
                        
                        $params = [];
                        $types = '';
                        
                        if (!empty($busca_motivo)) {
                            $sql .= " AND d.motivo LIKE ?";
                            $busca_param = "%" . $busca_motivo . "%";
                            array_push($params, $busca_param);
                            $types .= 's';
                        }
                        
                        if (!empty($busca_usuario)) {
                            $sql .= " AND (
                                        (u_autor.nome LIKE ?) OR
                                        (u_autor.sobrenome LIKE ?) OR
                                        (u_autor.email = ?) OR
                                        (u_denunciado.nome LIKE ?) OR
                                        (u_denunciado.sobrenome LIKE ?) OR
                                        (u_denunciado.email = ?)
                                    )";
                            $usuario_param_like = "%" . $busca_usuario . "%";
                            array_push($params, $usuario_param_like, $usuario_param_like, $busca_usuario, $usuario_param_like, $usuario_param_like, $busca_usuario);
                            $types .= 'ssssss';
                        }

                        $sql .= " ORDER BY d.data_denuncia DESC";
                        $stmt = $conn->prepare($sql);
                        if (!empty($params)) $stmt->bind_param($types, ...$params);
                        $stmt->execute();
                        $result_denuncias_user = $stmt->get_result();
                        ?>
                        <?php if ($result_denuncias_user && $result_denuncias_user->num_rows > 0): ?>
                            <?php while($denuncia = $result_denuncias_user->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $denuncia['id']; ?></td>
                                    <td>
                                        <a href="admin_editar_usuario.php?id=<?php echo $denuncia['id_usuario_denunciado']; ?>" target="_blank">
                                            <?php echo htmlspecialchars($denuncia['nome_denunciado'] . ' ' . $denuncia['sobrenome_denunciado']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($denuncia['nome_autor'] . ' ' . $denuncia['sobrenome_autor']); ?></td>
                                    <td><?php echo htmlspecialchars($denuncia['motivo']); ?></td>
                                    <td>
                                        <span class="desc-resumo" title="<?php echo htmlspecialchars($denuncia['descricao']); ?>">
                                            <?php echo !empty($denuncia['descricao']) ? htmlspecialchars(mb_strimwidth($denuncia['descricao'], 0, 40, "...")) : '---'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date("d/m/Y H:i", strtotime($denuncia['data_denuncia'])); ?></td>
                                    <td class="actions-cell">
                                        <?php
                                        $link_toggle_user = $config['base_path'] . 'api/admin/toggle_user_status.php';
                                        $link_update_denuncia = $config['base_path'] . 'api/admin/atualizar_status_denuncia.php';
                                        ?>
                                        <button 
                                            class="action-btn ignore-btn admin-action-btn"
                                            data-url="<?php echo $link_update_denuncia; ?>"
                                            data-id="<?php echo $denuncia['id']; ?>"
                                            data-status="ignorado"
                                            data-confirm-message="Tem a certeza de que deseja IGNORAR esta denúncia?">
                                            Ignorar
                                        </button>
                                        <button 
                                            class="action-btn hide-btn admin-action-btn"
                                            data-url="<?php echo $link_toggle_user; ?>"
                                            data-id="<?php echo $denuncia['id_usuario_denunciado']; ?>"
                                            data-denuncia-id="<?php echo $denuncia['id']; ?>"
                                            data-confirm-message="Atenção: Isto irá SUSPENDER o usuário. Deseja continuar?">
                                            Suspender
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">Nenhuma denúncia de usuário pendente.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php $stmt->close(); ?>

        <?php else: ?>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Autor</th>
                            <th>Motivo</th>
                            <th>Descrição Adicional</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // [ATUALIZADO] Incluída a coluna 'descricao'
                        $sql = "SELECT 
                                    d.id, d.tipo_conteudo, d.id_conteudo, d.motivo, d.descricao, d.data_denuncia,
                                    u_autor.nome AS nome_autor, u_autor.sobrenome AS sobrenome_autor
                                FROM Denuncias AS d
                                JOIN Usuarios AS u_autor ON d.id_usuario_denunciou = u_autor.id
                                WHERE d.tipo_conteudo IN ('post', 'comentario') AND d.status = 'pendente'";
                        
                        $params = []; $types = '';
                        if (!empty($busca_motivo)) {
                            $sql .= " AND d.motivo LIKE ?";
                            $busca_param = "%" . $busca_motivo . "%";
                            array_push($params, $busca_param);
                            $types .= 's';
                        }
                        if (!empty($tipo_filtro)) {
                            $sql .= " AND d.tipo_conteudo = ?";
                            array_push($params, $tipo_filtro);
                            $types .= 's';
                        }

                        $sql .= " ORDER BY d.data_denuncia DESC";
                        $stmt = $conn->prepare($sql);
                        if (!empty($params)) $stmt->bind_param($types, ...$params);
                        $stmt->execute();
                        $result_denuncias = $stmt->get_result();
                        ?>
                        <?php if ($result_denuncias && $result_denuncias->num_rows > 0): ?>
                            <?php while($denuncia = $result_denuncias->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $denuncia['id']; ?></td>
                                    <td>
                                        <span class="status-tag <?php echo ($denuncia['tipo_conteudo'] === 'post') ? 'role-admin' : 'role-membro'; ?>">
                                            <?php echo htmlspecialchars(ucfirst($denuncia['tipo_conteudo'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($denuncia['nome_autor'] . ' ' . $denuncia['sobrenome_autor']); ?></td>
                                    <td><?php echo htmlspecialchars($denuncia['motivo']); ?></td>
                                    <td>
                                        <span class="desc-resumo" title="<?php echo htmlspecialchars($denuncia['descricao']); ?>">
                                            <?php echo !empty($denuncia['descricao']) ? htmlspecialchars(mb_strimwidth($denuncia['descricao'], 0, 40, "...")) : '---'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date("d/m/Y H:i", strtotime($denuncia['data_denuncia'])); ?></td>
                                    <td class="actions-cell">
                                        <button class="action-btn view-btn" 
                                                data-denuncia-id="<?php echo $denuncia['id']; ?>"
                                                data-descricao-completa="<?php echo htmlspecialchars($denuncia['descricao']); ?>">
                                            <i class="fas fa-eye"></i> Ver Conteúdo
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">Nenhuma denúncia pendente encontrada.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php $stmt->close(); ?>

        <?php endif; ?>

    </main>

    <div id="denunciaModal" class="admin-modal">
        <div class="admin-modal-content">
            <span class="admin-modal-close">&times;</span>
            
            <div class="admin-modal-header">
                <h2>Detalhes da Denúncia</h2>
                <div id="admin-modal-header-actions"></div>
            </div>

            <div class="admin-modal-body">
                <div id="denunciaDescricaoDetalhada"></div>

                <div id="denunciaConteudo">
                    <p>Carregando...</p>
                </div>
            </div>
            
            <div id="denunciaAcoes" class="admin-modal-actions">
                </div>
        </div>
    </div>

</body>
</html>
<?php 
$conn->close();
?>