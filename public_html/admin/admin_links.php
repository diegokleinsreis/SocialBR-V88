<?php
// 1. AUTENTICAÇÃO E CONEXÃO
require_once 'admin_auth.php'; // Garante que só o admin veja e carrega o database.php
// $conn e $config (com $config['base_path']) já estão disponíveis aqui

// 2. FILTROS DA URL
$busca = $_GET['busca'] ?? '';
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';

// --- [NOVO V109] LÓGICA DO TOP 10 MAIS ACESSADOS ---
$sql_top10 = "SELECT url_destino, COUNT(id) as total 
              FROM Links_Cliques 
              GROUP BY url_destino 
              ORDER BY total DESC 
              LIMIT 10";
$result_top10 = false;
try {
    $result_top10 = $conn->query($sql_top10);
} catch (Exception $e) {
    // Silencia caso a tabela não exista (fail-safe)
}
// ---------------------------------------------------

// 3. CONSTRUÇÃO DA QUERY PRINCIPAL (LOG DETALHADO)
// Adicionei uma subquery (total_cliques_link) para contar o histórico daquele link específico
$sql = "SELECT 
            lc.id, 
            lc.url_destino, 
            lc.data_clique, 
            lc.ip_address, 
            lc.user_agent, 
            lc.post_id,
            u.id AS usuario_id, 
            u.nome, 
            u.sobrenome,
            p.conteudo_texto,
            (SELECT COUNT(sub.id) FROM Links_Cliques sub WHERE sub.url_destino = lc.url_destino) as total_cliques_link
        FROM Links_Cliques AS lc
        LEFT JOIN Usuarios AS u ON lc.usuario_id = u.id
        LEFT JOIN Postagens AS p ON lc.post_id = p.id";

$where_clauses = [];
$params = [];
$types = '';

// Filtro por Texto (URL ou Nome do Usuário)
if (!empty($busca)) {
    $where_clauses[] = "(lc.url_destino LIKE ? OR u.nome LIKE ? OR u.sobrenome LIKE ?)";
    $busca_param = "%" . $busca . "%";
    array_push($params, $busca_param, $busca_param, $busca_param);
    $types .= 'sss';
}

// Filtro por Data Início
if (!empty($data_inicio)) {
    $where_clauses[] = "lc.data_clique >= ?";
    array_push($params, $data_inicio . ' 00:00:00');
    $types .= 's';
}

// Filtro por Data Fim
if (!empty($data_fim)) {
    $where_clauses[] = "lc.data_clique <= ?";
    array_push($params, $data_fim . ' 23:59:59');
    $types .= 's';
}

// Aplica os filtros
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

// Ordenação e Limite
$sql .= " ORDER BY lc.data_clique DESC LIMIT 100";

// Execução Segura
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$result_links = false;
try {
    $stmt->execute();
    $result_links = $stmt->get_result();
} catch (Exception $e) {
    $erro_banco = "A tabela de rastreamento parece não existir ou ocorreu um erro.";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoramento de Links - Painel Admin</title>
    
    <link rel="stylesheet" href="assets/css/admin.css?v=<?php echo $asset_version ?? '1.0'; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* Estilos específicos para esta página */
        .url-cell {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
            color: #007bff;
            font-weight: 500;
        }
        .url-cell:hover {
            text-decoration: underline;
        }
        .meta-info {
            font-size: 0.8em;
            color: #6c757d;
            display: block;
        }
        .browser-icon {
            margin-right: 4px;
        }
        
        /* Estilos do Top 10 */
        .top-10-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .top-link-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .top-link-url {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 80%;
            font-size: 0.9em;
            color: #333;
            text-decoration: none;
        }
        .top-link-url:hover { text-decoration: underline; color: #0c2d54; }
        .top-link-count {
            background: #0c2d54;
            color: #fff;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        /* Badge de Contagem na Tabela Principal */
        .total-clicks-badge {
            display: inline-block;
            background-color: #e9ecef;
            color: #495057;
            font-size: 0.75em;
            padding: 2px 6px;
            border-radius: 4px;
            margin-top: 4px;
            border: 1px solid #ced4da;
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
            <h1><i class="fas fa-link"></i> Monitoramento de Links Externos</h1>
            <p>Acompanhe em tempo real os cliques em links compartilhados na sua rede.</p>
            <?php if (isset($erro_banco)): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-top: 10px;">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $erro_banco; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="admin-card">
            <h2><i class="fas fa-trophy"></i> Top 10 Links Mais Acessados</h2>
            <div class="top-10-grid">
                <?php if ($result_top10 && $result_top10->num_rows > 0): ?>
                    <?php $rank = 1; while($top = $result_top10->fetch_assoc()): ?>
                        <div class="top-link-item">
                            <div style="display: flex; align-items: center; width: 85%;">
                                <span style="font-weight: bold; color: #ccc; margin-right: 8px;">#<?php echo $rank++; ?></span>
                                <a href="<?php echo htmlspecialchars($top['url_destino']); ?>" target="_blank" class="top-link-url" title="<?php echo htmlspecialchars($top['url_destino']); ?>">
                                    <?php echo htmlspecialchars($top['url_destino']); ?>
                                </a>
                            </div>
                            <span class="top-link-count" title="Total de cliques"><?php echo $top['total']; ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #6c757d; font-style: italic;">Nenhum clique registado ainda.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="admin-card">
            <h2><i class="fas fa-filter"></i> Log Detalhado & Filtros</h2>
            <form class="admin-filter-form" action="admin_links.php" method="GET">
                
                <div class="form-group">
                    <label for="busca">Buscar (URL ou Usuário)</label>
                    <input type="text" id="busca" name="busca" value="<?php echo htmlspecialchars($busca); ?>" placeholder="Ex: google.com ou João...">
                </div>
                
                <div class="form-group">
                    <label for="data_inicio">De:</label>
                    <input type="date" id="data_inicio" name="data_inicio" value="<?php echo htmlspecialchars($data_inicio); ?>">
                </div>
                
                <div class="form-group">
                    <label for="data_fim">Até:</label>
                    <input type="date" id="data_fim" name="data_fim" value="<?php echo htmlspecialchars($data_fim); ?>">
                </div>

                <button type="submit" class="filter-btn">Filtrar</button>
                <a href="admin_links.php" class="filter-btn clear-btn">Limpar</a>
            </form>
        </div>

        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Quem Clicou?</th>
                        <th>Origem (Post)</th>
                        <th>Destino (Link)</th>
                        <th>Info Técnica</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_links && $result_links->num_rows > 0): ?>
                        <?php while($row = $result_links->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php echo date("d/m/Y", strtotime($row['data_clique'])); ?><br>
                                    <small><?php echo date("H:i:s", strtotime($row['data_clique'])); ?></small>
                                </td>
                                
                                <td>
                                    <?php if ($row['usuario_id']): ?>
                                        <a href="admin_editar_usuario.php?id=<?php echo $row['usuario_id']; ?>" target="_blank" style="font-weight: bold;">
                                            <?php echo htmlspecialchars($row['nome'] . ' ' . $row['sobrenome']); ?>
                                        </a>
                                        <br><span class="status-tag status-ativo" style="font-size: 0.7em;">Membro</span>
                                    <?php else: ?>
                                        <span style="color: #6c757d;">Visitante (Não logado)</span>
                                        <br><span class="status-tag status-inativo" style="font-size: 0.7em;">Anônimo</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if ($row['post_id']): ?>
                                        <a href="<?php echo $config['base_path']; ?>postagem/<?php echo $row['post_id']; ?>" target="_blank">
                                            Post #<?php echo $row['post_id']; ?>
                                        </a>
                                        <br>
                                        <small style="color: #999;">
                                            "<?php echo htmlspecialchars(mb_strimwidth($row['conteudo_texto'] ?? '', 0, 30, "...")); ?>"
                                        </small>
                                    <?php else: ?>
                                        <span style="color: #ccc;">N/A</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <a href="<?php echo htmlspecialchars($row['url_destino']); ?>" target="_blank" class="url-cell" title="<?php echo htmlspecialchars($row['url_destino']); ?>">
                                        <i class="fas fa-external-link-alt"></i> <?php echo htmlspecialchars($row['url_destino']); ?>
                                    </a>
                                    <span class="total-clicks-badge" title="Este link já foi clicado <?php echo $row['total_cliques_link']; ?> vezes no total">
                                        Total: <?php echo $row['total_cliques_link']; ?> cliques
                                    </span>
                                </td>

                                <td>
                                    <span class="meta-info"><strong>IP:</strong> <?php echo htmlspecialchars($row['ip_address']); ?></span>
                                    <span class="meta-info" title="<?php echo htmlspecialchars($row['user_agent']); ?>">
                                        <strong>Disp:</strong> 
                                        <?php 
                                            // Detecção simples para ícone
                                            $ua = strtolower($row['user_agent']);
                                            if (strpos($ua, 'mobile') !== false || strpos($ua, 'android') !== false || strpos($ua, 'iphone') !== false) {
                                                echo '<i class="fas fa-mobile-alt browser-icon"></i> Mobile';
                                            } else {
                                                echo '<i class="fas fa-desktop browser-icon"></i> Desktop';
                                            }
                                        ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px;">
                                <?php if (isset($erro_banco)): ?>
                                    <span style="color: #dc3545;"><?php echo $erro_banco; ?></span>
                                <?php else: ?>
                                    Nenhum clique registado neste período.
                                <?php endif; ?>
                            </td>
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
if (isset($stmt) && $stmt) $stmt->close();
$conn->close(); 
?>