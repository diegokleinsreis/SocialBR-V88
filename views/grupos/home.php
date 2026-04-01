<?php
/**
 * views/grupos/home.php
 * Orquestrador da Página Inicial de Grupos.
 * PAPEL: Centralizar a lógica de exibição de "Meus Grupos", "Recomendações", "Busca" e "Explorar".
 * VERSÃO: 2.2 (Botão Explorar Tudo Funcional - socialbr.lol)
 */

// 1. PROTEÇÃO DE ACESSO E DADOS SOCIAIS
if (!isset($_SESSION['user_id'])) {
    header("Location: " . $config['base_path'] . "login");
    exit();
}

$id_usuario_logado = (int)$_SESSION['user_id'];

// Carrega o "Cérebro" do módulo (Versão 2.2 com Catálogo Completo)
require_once __DIR__ . '/../../src/GruposLogic.php';

// 2. LÓGICA DE NAVEGAÇÃO (Busca vs Abas vs Dashboard)
$termo_busca = trim($_GET['q'] ?? '');
$view        = $_GET['view'] ?? ''; 
$is_busca    = !empty($termo_busca);

if ($is_busca) {
    // Modo Busca: Foca nos resultados do termo pesquisado
    $resultados_busca = GruposLogic::buscarGrupos($conn, $termo_busca);
} elseif ($view === 'meus') {
    // Modo Ver Todos (Participação): Lista completa de onde o usuário já é membro
    $meus_grupos = GruposLogic::getMeusGrupos($conn, $id_usuario_logado, 0);
} elseif ($view === 'explorar') {
    // MODO NOVO: Explorar Tudo (Descoberta): Lista todos os grupos do site que o usuário ainda não participa
    $resultados_explorar = GruposLogic::getExplorarTudo($conn, $id_usuario_logado);
} else {
    // Modo Dashboard (Padrão): Resumo limitado a 6 itens por seção
    $meus_grupos   = GruposLogic::getMeusGrupos($conn, $id_usuario_logado, 6);
    $recomendacoes = GruposLogic::getRecomendacoes($conn, $id_usuario_logado, 6);
}

// Definições de página
$page_title = $is_busca ? "Busca: $termo_busca - Grupos" : "Grupos - " . ($config['site_nome'] ?? 'Social BR');
$component_path = __DIR__ . '/componentes/';

// Blindagem: Se a pasta de componentes não existir, avisamos o desenvolvedor
if (!is_dir($component_path)) {
    die("<strong>Erro de Arquitetura:</strong> A pasta <code>views/grupos/componentes/</code> não foi encontrada.");
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <?php include __DIR__ . '/../../templates/head_common.php'; ?>
    <style>
        .groups-home-wrapper {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .groups-section {
            margin-bottom: 40px;
        }

        .groups-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e4e6eb;
        }

        .groups-section-header h2 {
            font-size: 1.3rem;
            font-weight: 800;
            color: #0C2D54; /* Sua cor oficial */
        }

        .groups-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .empty-section-msg {
            background: #fff;
            padding: 40px 20px;
            border-radius: 12px;
            text-align: center;
            color: #65676b;
            border: 1px solid #e4e6eb;
        }

        .empty-section-msg i {
            font-size: 3rem;
            color: #ccd0d5;
            margin-bottom: 15px;
            display: block;
        }

        /* Links de Ação estilizados */
        .link-view-all {
            color: #1877f2;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: opacity 0.2s;
            cursor: pointer;
        }

        .link-view-all:hover {
            text-decoration: underline;
            opacity: 0.8;
        }

        @media (max-width: 768px) {
            .groups-home-wrapper { padding: 10px; }
            .groups-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="bg-light">

    <?php include __DIR__ . '/../../templates/header.php'; ?>
    <?php include __DIR__ . '/../../templates/mobile_nav.php'; ?>

    <div class="main-content-area">
        <?php include __DIR__ . '/../../templates/sidebar.php'; ?>

        <main class="feed-container">
            <div class="groups-home-wrapper">

                <?php include $component_path . 'barra_topo.php'; ?>

                <?php if ($is_busca): ?>
                    <section class="groups-section">
                        <div class="groups-section-header">
                            <h2>Resultados para "<?php echo htmlspecialchars($termo_busca); ?>"</h2>
                            <a href="<?php echo $config['base_path']; ?>grupos" class="link-view-all">Limpar busca</a>
                        </div>
                        
                        <div class="groups-grid">
                            <?php if (!empty($resultados_busca)): ?>
                                <?php foreach ($resultados_busca as $item_sugestao): ?>
                                    <?php include $component_path . 'card_sugestao.php'; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-section-msg">
                                    <i class="fas fa-search"></i>
                                    <p>Nenhum grupo encontrado com este nome.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>

                <?php elseif ($view === 'meus'): ?>
                    <section class="groups-section">
                        <div class="groups-section-header">
                            <h2>Todos os Seus Grupos</h2>
                            <a href="<?php echo $config['base_path']; ?>grupos" class="link-view-all">Voltar ao início</a>
                        </div>
                        
                        <div class="groups-grid">
                            <?php if (!empty($meus_grupos)): ?>
                                <?php foreach ($meus_grupos as $grupo): ?>
                                    <?php include $component_path . 'card_meu_grupo.php'; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-section-msg">
                                    <p>Você ainda não participa de nenhum grupo.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>

                <?php elseif ($view === 'explorar'): ?>
                    <section class="groups-section">
                        <div class="groups-section-header">
                            <h2>Explorar Todas as Comunidades</h2>
                            <a href="<?php echo $config['base_path']; ?>grupos" class="link-view-all">Voltar ao início</a>
                        </div>
                        
                        <div class="groups-grid">
                            <?php if (!empty($resultados_explorar)): ?>
                                <?php foreach ($resultados_explorar as $item_sugestao): ?>
                                    <?php include $component_path . 'card_sugestao.php'; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-section-msg">
                                    <i class="fas fa-globe-americas"></i>
                                    <p>Não há novos grupos para explorar no momento.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>

                <?php else: ?>
                    <section class="groups-section">
                        <div class="groups-section-header">
                            <h2>Seus Grupos</h2>
                            <a href="<?php echo $config['base_path']; ?>grupos?view=meus" class="link-view-all">Ver todos</a>
                        </div>
                        
                        <div class="groups-grid">
                            <?php if (!empty($meus_grupos)): ?>
                                <?php foreach ($meus_grupos as $grupo): ?>
                                    <?php include $component_path . 'card_meu_grupo.php'; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-section-msg">
                                    <p>Você ainda não participa de nenhum grupo.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>

                    <section class="groups-section">
                        <div class="groups-section-header">
                            <h2>Sugestões para você</h2>
                            <a href="<?php echo $config['base_path']; ?>grupos?view=explorar" class="link-view-all">Explorar todas</a>
                        </div>

                        <div class="groups-grid">
                            <?php if (!empty($recomendacoes)): ?>
                                <?php foreach ($recomendacoes as $item_sugestao): ?>
                                    <?php include $component_path . 'card_sugestao.php'; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-section-msg">
                                    <p>Não encontramos novas sugestões no momento.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php endif; ?>

            </div>
        </main>
    </div>

    <?php include __DIR__ . '/../../templates/footer.php'; ?>

</body>
</html>