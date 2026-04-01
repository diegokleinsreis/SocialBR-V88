<?php
/**
 * pesquisa.php - Orquestrador de Resultados Premium
 * VERSÃO: 3.2 - Registro de Inteligência de Resultados (socialbr.lol)
 * PAPEL: Centralizar busca, filtros e registrar métricas de sucesso confirmadas.
 */

// 1. Inicialização e Segurança
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Dependências do Core
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/BuscaLogic.php';

/**
 * 3. BLINDAGEM DE INFRAESTRUTURA
 */
$config = $config ?? ['base_path' => '/']; 
$base   = $config['base_path'];

if (!isset($db)) {
    $db = $pdo ?? $conn ?? $conexao ?? null;
}

// Redirecionamento se não logado
if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base . "login");
    exit;
}

// 4. Parâmetros
$termo_busca = isset($_GET['q']) ? trim($_GET['q']) : '';
$filtro      = isset($_GET['filtro']) ? $_GET['filtro'] : 'tudo';
$userId      = (int)$_SESSION['user_id'];

$busca = new BuscaLogic($db, $userId);
$resultados = [];

// 5. EXECUÇÃO DA BUSCA E REGISTRO DE MÉTRICAS
if (!empty($termo_busca)) {
    // Busca os dados conforme o filtro selecionado
    switch ($filtro) {
        case 'usuarios': $resultados = $busca->buscarUsuarios($termo_busca, 40); break;
        case 'grupos':   $resultados = $busca->buscarGrupos($termo_busca, 40); break;
        case 'posts':    $resultados = $busca->buscarPostagens($termo_busca, 40); break;
        default:         $resultados = $busca->buscarGlobal($termo_busca, 20); break;
    }

    /**
     * REGISTRO DE INTELIGÊNCIA [NOVO]
     * Como o usuário confirmou a busca (Enter/Botão), registramos o termo
     * com o total real de resultados encontrados para alimentar o Admin.
     */
    $busca->registrarInteracao($termo_busca, 'geral', null, count($resultados));
}

$page_title = "Resultados para: " . htmlspecialchars($termo_busca);
$ultimo_tipo = null;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <?php include __DIR__ . '/../../templates/head_common.php'; ?>
    <title><?php echo $page_title; ?> | Social BR</title>
    
    <style>
        /* 1. AJUSTES DO PALCO CENTRALIZADO */
        .container-principal-pesquisa {
            max-width: 100%;
            padding: 20px; 
            box-sizing: border-box;
            min-height: 85vh;
            flex: 1;
        }

        /* 2. ESTRUTURA DE COLUNA ÚNICA */
        .layout-busca {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 1150px;
            margin: 0 auto;
        }

        .busca-filtros-topo {
            width: 100%;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-bottom: 25px;
            overflow: hidden;
            border: 1px solid #dddfe2;
        }

        .busca-conteudo {
            width: 100%;
        }

        /* 3. GRID DE RESULTADOS OTIMIZADO */
        .resultados-grid {
            display: grid;
            gap: 15px; 
            align-items: stretch;
        }

        .layout-cards {
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        }

        .layout-lista { 
            grid-template-columns: 1fr; 
        }

        /* 4. DIVISORES DE SEÇÃO */
        .divisor-secao {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            margin: 10px 0 15px 0;
            padding: 10px 0;
            border-bottom: 2px solid #eaebed;
        }
        .divisor-secao i { margin-right: 12px; color: #0C2D54; font-size: 1.1rem; }
        .divisor-secao h2 { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1.2px; color: #65676b; font-weight: 800; margin: 0; }

        /* Classe de captura para MotorBusca.js */
        .search-result-choice {
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
        }

        @media (max-width: 992px) {
            .container-principal-pesquisa { padding: 10px; }
            .layout-cards {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            }
        }
    </style>
</head>
<body class="bg-light">

    <?php include __DIR__ . '/../../templates/header.php'; ?>
    <?php include __DIR__ . '/../../templates/mobile_nav.php'; ?>

    <div class="main-content-area">
        <?php include __DIR__ . '/../../templates/sidebar.php'; ?>

        <main class="container-principal-pesquisa">
            <div class="layout-busca">
                
                <div class="busca-filtros-topo">
                    <?php include __DIR__ . '/componentes/menu_filtros.php'; ?>
                </div>

                <section class="busca-conteudo">
                    
                    <div class="resultados-header-card" style="margin-bottom: 0px;">
                        <h1 class="termo-titulo" style="font-size: 1.5rem; color: #0C2D54;">Resultados para "<span><?php echo htmlspecialchars($termo_busca); ?></span>"</h1>
                        <p class="contador-votos" style="color: #65676b;">
                            Encontramos <strong><?php echo count($resultados); ?></strong> 
                            <?php echo (count($resultados) == 1) ? 'item relevante' : 'itens relevantes'; ?>.
                        </p>
                    </div>

                    <div class="resultados-wrapper">
                        <?php if (empty($resultados)): ?>
                            <div class="busca-vazia-container">
                                <h2>Nenhum resultado encontrado</h2>
                                <a href="<?php echo $base; ?>feed" class="btn-voltar">Voltar para o Feed</a>
                            </div>
                        <?php else: ?>
                            <div class="resultados-grid <?php echo ($filtro === 'posts') ? 'layout-lista' : 'layout-cards'; ?>">
                                <?php foreach ($resultados as $item): ?>
                                    <?php 
                                        $tipo_atual = $item['tipo_resultado'] ?? $filtro;
                                        if ($tipo_atual !== $ultimo_tipo) {
                                            $label = ''; $icon = '';
                                            switch ($tipo_atual) {
                                                case 'perfil':  case 'usuarios': $label = 'Pessoas';   $icon = 'fa-users'; break;
                                                case 'grupo':   case 'grupos':   $label = 'Grupos';    $icon = 'fa-layer-group'; break;
                                                case 'post':    case 'posts':    $label = 'Postagens'; $icon = 'fa-comment-alt'; break;
                                            }
                                            if ($label) echo "<div class='divisor-secao'><i class='fas {$icon}'></i><h2>{$label}</h2></div>";
                                            $ultimo_tipo = $tipo_atual;
                                        }

                                        // Wrapper de monitoramento integrado com MotorBusca.js
                                        echo "<div class='search-result-choice' 
                                                    data-id='{$item['id']}' 
                                                    data-tipo='{$tipo_atual}' 
                                                    data-termo='".htmlspecialchars($termo_busca)."'>";

                                            if ($tipo_atual === 'perfil' || $filtro === 'usuarios') {
                                                $u = $item; include __DIR__ . '/componentes/cartao_usuario.php';
                                            } elseif ($tipo_atual === 'grupo' || $filtro === 'grupos') {
                                                $g = $item; include __DIR__ . '/componentes/cartao_grupo.php';
                                            } elseif ($tipo_atual === 'post' || $filtro === 'posts') {
                                                $p = $item; include __DIR__ . '/componentes/cartao_postagem.php';
                                            }

                                        echo "</div>";
                                    ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                </section>
            </div>
        </main>
    </div>

    <?php include __DIR__ . '/../../templates/footer.php'; ?>
    
    <script>window.BASE_PATH = "<?php echo $base; ?>";</script>
</body>
</html>