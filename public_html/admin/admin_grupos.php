<?php
/**
 * admin/admin_grupos.php
 * PAPEL: Orquestrador do Painel de Gestão de Grupos.
 * VERSÃO: 1.7 (Fix: SQL Ambiguity, Correct Path & No-Cut Mobile - socialbr.lol)
 */

// 1. SEGURANÇA E DEPENDÊNCIAS
require_once 'admin_auth.php'; 
// CORREÇÃO: Caminho de apenas um nível acima para acessar a raiz e depois a pasta src
require_once __DIR__ . '/../../src/GruposLogic.php'; 

// 2. PARÂMETROS DE FILTRO E PAGINAÇÃO
$pagina_atual = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$limite = 15;
$offset = ($pagina_atual - 1) * $limite;
$busca = isset($_GET['q']) ? trim($_GET['q']) : '';

// 3. LÓGICA DE BUSCA E CONTAGEM (FIX: AMBIGUIDADE TOTAL)
// Definimos o filtro base usando obrigatoriamente o alias 'g.'
$where_sql = " WHERE g.status != 'excluido' "; 

if (!empty($busca)) {
    // Adicionado 'g.' também nos campos de busca para evitar conflitos
    $where_sql .= " AND (g.nome LIKE ? OR g.id = ?) ";
    $termo_like = "%$busca%";
    $params[] = $termo_like;
    $params[] = (int)$busca;
}

// A. Busca do Total para Paginação
$sql_total = "SELECT COUNT(*) as total FROM Grupos g $where_sql";
$stmt_t = $conn->prepare($sql_total);
if (!empty($params)) {
    $types = (count($params) == 2) ? "si" : "";
    if($types) $stmt_t->bind_param($types, ...$params);
}
$stmt_t->execute();
$total_grupos = $stmt_t->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_grupos / $limite);

// B. Busca da Lista de Grupos (Sincronizada com o alias g e u)
$sql_list = "SELECT g.*, u.nome as dono_nome, u.sobrenome as dono_sobrenome, 
            (SELECT COUNT(*) FROM Grupos_Membros WHERE id_grupo = g.id) as membros_count 
            FROM Grupos g
            JOIN Usuarios u ON g.id_dono = u.id
            $where_sql
            ORDER BY g.data_criacao DESC
            LIMIT ? OFFSET ?";

$stmt_l = $conn->prepare($sql_list);
$offset_param = (int)$offset;
$limite_param = (int)$limite;

if (!empty($params)) {
    $stmt_l->bind_param("siii", $params[0], $params[1], $limite_param, $offset_param);
} else {
    $stmt_l->bind_param("ii", $limite_param, $offset_param);
}
$stmt_l->execute();
$lista_grupos = $stmt_l->get_result()->fetch_all(MYSQLI_ASSOC);

// 4. ESTATÍSTICAS RÁPIDAS (Sem JOIN, aqui o status não é ambíguo)
$stats = [
    'total' => $conn->query("SELECT COUNT(*) FROM Grupos WHERE status != 'excluido'")->fetch_row()[0],
    'ativos' => $conn->query("SELECT COUNT(*) FROM Grupos WHERE status = 'ativo'")->fetch_row()[0],
    'suspensos' => $conn->query("SELECT COUNT(*) FROM Grupos WHERE status = 'suspenso'")->fetch_row()[0],
    'novos_hoje' => $conn->query("SELECT COUNT(*) FROM Grupos WHERE DATE(data_criacao) = CURDATE()")->fetch_row()[0]
];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Gestão de Grupos - Painel Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css?v=<?php echo $config['versao_assets']; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root { --admin-primary: #0C2D54; }
        
        * { box-sizing: border-box; }

        /* Ajuste do Contentor para evitar quebras e cortes */
        .admin-main-content { 
            padding: 15px !important; 
            width: 100%;
            max-width: 100vw; 
            overflow-x: hidden; /* Impede que a página inteira balance */
        }

        .groups-dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
        }

        /* Container da Tabela com Scroll Suave */
        .table-responsive-wrapper {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            -webkit-overflow-scrolling: touch;
            margin-bottom: 20px;
        }

        @media (max-width: 1100px) {
            .groups-dashboard-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 600px) {
            .admin-card { padding: 12px; }
            .admin-card h1 { font-size: 1.2rem; }
            .admin-main-content { padding: 8px !important; }
        }

        /* Estilização da Paginação */
        .admin-pagination { 
            display: flex; 
            gap: 5px; 
            margin: 20px 0; 
            justify-content: center; 
            flex-wrap: wrap; 
        }
        .admin-pagination a { 
            padding: 8px 14px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            text-decoration: none; 
            color: var(--admin-primary); 
            background: #fff;
            font-size: 0.85rem;
            font-weight: bold;
        }
        .admin-pagination a.active { 
            background: var(--admin-primary); 
            color: #fff; 
            border-color: var(--admin-primary); 
        }
    </style>
</head>
<body>

    <?php include 'templates/admin_header.php'; ?>
    <?php include 'templates/admin_mobile_nav.php'; ?>

    <main class="admin-main-content">
        <a href="index.php" class="admin-back-button"><i class="fas fa-arrow-left"></i> Voltar</a>
        
        <div class="admin-card" style="margin-bottom: 20px;">
            <h1><i class="fas fa-users"></i> Gestão de Grupos</h1>
            <p>Controle administrativo das comunidades da rede.</p>
        </div>

        <?php include 'grupos/estatisticas.php'; ?>

        <div class="groups-dashboard-grid">
            
            <div class="groups-main-column">
                <?php include 'grupos/busca.php'; ?>

                <div class="table-responsive-wrapper">
                    <?php include 'grupos/tabela.php'; ?>
                </div>

                <?php if ($total_paginas > 1): ?>
                <div class="admin-pagination">
                    <?php if($pagina_atual > 1): ?>
                        <a href="?sub_route=grupos&p=<?php echo $pagina_atual-1; ?>&q=<?php echo urlencode($busca); ?>">&laquo;</a>
                    <?php endif; ?>

                    <?php for($i=1; $i<=$total_paginas; $i++): ?>
                        <?php if($i == 1 || $i == $total_paginas || ($i >= $pagina_atual - 1 && $i <= $pagina_atual + 1)): ?>
                            <a href="?sub_route=grupos&p=<?php echo $i; ?>&q=<?php echo urlencode($busca); ?>" 
                               class="<?php echo $pagina_atual == $i ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if($pagina_atual < $total_paginas): ?>
                        <a href="?sub_route=grupos&p=<?php echo $pagina_atual+1; ?>&q=<?php echo urlencode($busca); ?>">&raquo;</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <aside class="groups-sidebar">
                <?php include 'grupos/ranking.php'; ?>
            </aside>

        </div>
    </main>

    
</body>
</html>
<?php $conn->close(); ?>