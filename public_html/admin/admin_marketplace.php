<?php
/**
 * admin/admin_marketplace.php
 * Painel de Marketplace
 * Versão: 13.0 - Fix Mobile Nav & Links Absolutos
 */

require_once 'admin_auth.php'; 

// Garante que $config['base_path'] esteja disponível para links e CSS
if (!isset($config) || !isset($config['base_path'])) {
    // Tenta carregar do banco se o admin_auth não tiver feito
    $paths = [__DIR__ . '/../../config/database.php', __DIR__ . '/../config/database.php'];
    foreach($paths as $p) { if(file_exists($p)) { include $p; break; } }
}

// Se ainda assim falhar, define fallback
if (!isset($config['base_path'])) {
    $config['base_path'] = '/';
}

$anuncios = [];
$erro_db = '';
$busca = $_GET['busca'] ?? '';
$filtro_status = $_GET['status'] ?? '';

// LÓGICA DE DADOS (MySQLi)
if (isset($conn) && $conn instanceof mysqli) {
    try {
        $sql = "SELECT ma.*, 
                       u.nome as vendedor_nome, 
                       u.email as vendedor_email,
                       p.status as status_post,
                       (SELECT pm.url_midia FROM Postagens_Midia pm WHERE pm.id_postagem = ma.id_postagem LIMIT 1) as capa
                FROM Marketplace_Anuncios ma
                JOIN Postagens p ON ma.id_postagem = p.id
                JOIN Usuarios u ON p.id_usuario = u.id
                WHERE 1=1";

        $types = "";
        $params = [];

        if (!empty($busca)) {
            $sql .= " AND (ma.titulo_produto LIKE ? OR u.nome LIKE ?)";
            $busca_wildcard = "%$busca%";
            $params[] = $busca_wildcard;
            $params[] = $busca_wildcard;
            $types .= "ss";
        }

        // Esconde itens excluídos definitivamente pelo usuário
        $sql .= " AND p.status != 'excluido_pelo_usuario'";

        if (!empty($filtro_status)) {
            if ($filtro_status == 'banido') {
                $sql .= " AND p.status != 'ativo'";
            } else {
                $sql .= " AND ma.status_venda = ?";
                $params[] = $filtro_status;
                $types .= "s";
            }
        }

        $sql .= " ORDER BY ma.id DESC LIMIT 50";

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            if (!empty($params)) $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) $anuncios[] = $row;
            $stmt->close();
        }
    } catch (Exception $e) { $erro_db = $e->getMessage(); }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Marketplace - Painel Admin</title>
    
    <link rel="stylesheet" href="assets/css/admin.css?v=3.8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* LAYOUT FLUIDO */
        body { overflow-x: hidden; background-color: #f4f6f9; }
        .admin-container { display: block; width: 100%; padding: 0; margin: 0; }
        .admin-content { width: 100%; padding: 20px; box-sizing: border-box; max-width: 1400px; margin: 0 auto; }

        /* HEADER E STATS */
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
        .page-header h1 { margin: 0; font-size: 1.5rem; color: #333; }
        .badge-stats { background: #007bff; color: white; padding: 6px 12px; border-radius: 20px; font-size: 0.9em; font-weight: bold; }

        /* FILTROS */
        .filters-bar { background: #fff; padding: 15px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; gap: 10px; flex-wrap: wrap; }
        .search-form { display: flex; gap: 10px; width: 100%; flex-wrap: wrap; }
        .search-form input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px; min-width: 200px; }
        .search-form select { padding: 10px; border: 1px solid #ddd; border-radius: 4px; }

        /* TABELA COM SCROLL RESPONSIVO */
        .data-table-container {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch; /* Suavidade no mobile */
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #eee;
        }

        .admin-table { width: 100%; min-width: 900px; border-collapse: collapse; }
        .admin-table th, .admin-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; vertical-align: middle; white-space: nowrap; }
        .admin-table td.col-produto { white-space: normal; min-width: 200px; max-width: 350px; }
        .admin-table th { background: #f8f9fa; font-weight: 600; color: #555; text-transform: uppercase; font-size: 0.85em; }
        .table-thumb { width: 45px; height: 45px; object-fit: cover; border-radius: 4px; border: 1px solid #eee; }
        
        /* BADGES */
        .status-badge { padding: 5px 10px; border-radius: 4px; font-size: 0.8em; font-weight: bold; text-transform: uppercase; }
        .status-disponivel { background: #e8f5e9; color: #2e7d32; }
        .status-vendido { background: #e3f2fd; color: #1565c0; }
        .status-banido { background: #ffebee; color: #c62828; }

        /* BOTÕES */
        .btn-action { border: none; background: #f8f9fa; width: 34px; height: 34px; border-radius: 50%; cursor: pointer; margin-right: 5px; transition: 0.2s; display: inline-flex; align-items: center; justify-content: center; }
        .btn-action:hover { transform: scale(1.1); filter: brightness(0.95); background: #e2e6ea; }
    </style>
</head>
<body>
    
    <?php include 'templates/admin_header.php'; ?>
    
    <?php include 'templates/admin_mobile_nav.php'; ?>
    
    <div class="admin-container">
        <main class="admin-content">
            
            <div class="page-header">
                <div>
                    <h1>Gestão de Marketplace</h1>
                    <p style="color: #6c757d; margin:5px 0;">Moderação de anúncios e vendas.</p>
                </div>
                <div>
                    <span class="badge-stats"><?php echo count($anuncios); ?> Itens</span>
                </div>
            </div>

            <?php if(!empty($erro_db)): ?>
                <div style="background:#ffebee; color:#c62828; padding:15px; margin-bottom:20px; border-radius:4px; border:1px solid #f5c6cb;">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($erro_db); ?>
                </div>
            <?php endif; ?>

            <div class="filters-bar">
                <form action="" method="GET" class="search-form">
                    <input type="text" name="busca" placeholder="Buscar produto, ID ou vendedor..." value="<?php echo htmlspecialchars($busca); ?>">
                    <select name="status" onchange="this.form.submit()">
                        <option value="">Todos os Status</option>
                        <option value="disponivel" <?php echo $filtro_status=='disponivel'?'selected':'';?>>Disponíveis</option>
                        <option value="vendido" <?php echo $filtro_status=='vendido'?'selected':'';?>>Vendidos</option>
                        <option value="banido" <?php echo $filtro_status=='banido'?'selected':'';?>>Banidos</option>
                    </select>
                </form>
            </div>

            <div class="data-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th width="50">ID</th>
                            <th width="70">Capa</th>
                            <th>Produto</th>
                            <th>Vendedor</th>
                            <th>Preço</th>
                            <th>Status</th>
                            <th width="160">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($anuncios)): ?>
                            <tr><td colspan="7" style="text-align: center; padding: 40px; color: #888;">Nenhum anúncio encontrado.</td></tr>
                        <?php else: ?>
                            <?php foreach ($anuncios as $item): ?>
                                <?php 
                                    $capa = $item['capa'] ?? '';
                                    // URL da Imagem
                                    $imgUrl = (strpos($capa, 'http') === 0) ? $capa : $config['base_path'] . $capa;
                                    
                                    // URL DE VISUALIZAÇÃO CORRETA: marketplace/item/{id}
                                    $linkVer = $config['base_path'] . 'marketplace/item/' . $item['id'];
                                ?>
                                <tr>
                                    <td>#<?php echo $item['id']; ?></td>
                                    <td><img src="<?php echo htmlspecialchars($imgUrl); ?>" class="table-thumb" onerror="this.src='<?php echo $config['base_path']; ?>assets/images/placeholder-image.png'"></td>
                                    <td class="col-produto">
                                        <strong style="display:block; color: #333; font-size:1.05em;"><?php echo htmlspecialchars($item['titulo_produto']); ?></strong>
                                        <small style="color: #888;"><?php echo htmlspecialchars($item['categoria']); ?></small>
                                    </td>
                                    <td>
                                        <div style="font-weight:500; color:#444;"><?php echo htmlspecialchars($item['vendedor_nome']); ?></div>
                                    </td>
                                    <td style="font-weight: bold; color: #2e7d32;">
                                        R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?>
                                    </td>
                                    <td>
                                        <?php if ($item['status_post'] !== 'ativo'): ?>
                                            <span class="status-badge status-banido">Banido</span>
                                        <?php else: ?>
                                            <span class="status-badge status-<?php echo $item['status_venda']; ?>">
                                                <?php echo ucfirst($item['status_venda']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo $linkVer; ?>" target="_blank" class="btn-action" style="color:#007bff;" title="Ver no Site">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <?php if ($item['status_post'] === 'ativo'): ?>
                                            <?php if ($item['status_venda'] === 'vendido'): ?>
                                                <button onclick="adminMktAction(<?php echo $item['id']; ?>, 'marcar_disponivel')" class="btn-action" style="color:#ffc107;" title="Marcar Disponível">
                                                    <i class="fas fa-box-open"></i>
                                                </button>
                                            <?php else: ?>
                                                <button onclick="adminMktAction(<?php echo $item['id']; ?>, 'marcar_vendido')" class="btn-action" style="color:#28a745;" title="Marcar Vendido">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <button onclick="adminMktAction(<?php echo $item['id']; ?>, 'banir')" class="btn-action" style="color:#dc3545;" title="Banir">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        <?php else: ?>
                                            <button onclick="adminMktAction(<?php echo $item['id']; ?>, 'reativar')" class="btn-action" style="color:#28a745;" title="Reativar">
                                                <i class="fas fa-undo-alt"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    

    <script>
    // Define a base URL absoluta vinda do PHP para a API não falhar
    const BASE_PATH = '<?php echo $config['base_path']; ?>';

    async function adminMktAction(id, action) {
        let msg = 'Confirmar ação?';
        if(action === 'banir') msg = 'Deseja excluir/banir este anúncio?';
        if(action === 'marcar_vendido') msg = 'Marcar como VENDIDO?';
        if(action === 'marcar_disponivel') msg = 'Marcar como DISPONÍVEL?';

        if (!confirm(msg)) return;

        try {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('action', action);

            // Monta URL absoluta da API para evitar erro de pasta
            // Ex: https://socialbr.lol/~klscom/api/admin/marketplace_admin_acoes.php
            const apiUrl = BASE_PATH + 'api/admin/marketplace_admin_acoes.php';

            const response = await fetch(apiUrl, { method: 'POST', body: formData });
            
            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch(e) {
                console.error('Resposta inválida:', text);
                alert('Erro no servidor (ver console).');
                return;
            }

            if (data.success) {
                location.reload();
            } else {
                alert('Erro: ' + (data.error || 'Falha desconhecida.'));
            }

        } catch (error) {
            console.error(error);
            alert('Erro de conexão.');
        }
    }
    </script>
</body>
</html>