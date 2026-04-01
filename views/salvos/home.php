<?php
/**
 * views/salvos/home.php
 * Esqueleto Orquestrador do Módulo de Salvos Premium.
 * PAPEL: Gerenciar a estrutura principal, filtros e incluir componentes atômicos.
 * VERSÃO: V81.0 - Botão Compartilhar p/ Coleções Públicas (socialbr.lol)
 */

// 1. Inicialização e Segurança
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $config['base_path'] . "login");
    exit;
}

// 2. Dependências de Lógica
require_once __DIR__ . '/../../src/SalvosLogic.php';
$salvosLogic = new SalvosLogic($pdo); 

// 3. Captura de Parâmetros (Filtros e Busca)
$usuario_id  = (int)$_SESSION['user_id'];
$colecao_id  = isset($_GET['colecao_id']) ? (int)$_GET['colecao_id'] : null;
$filtro_tipo = isset($_GET['tipo']) ? htmlspecialchars($_GET['tipo']) : 'todos';
$busca_termo = isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : '';

// 4. Inteligência de Inicialização
$id_geral = $salvosLogic->getOrCreateGeral($usuario_id);

// --- LÓGICA DE DETECÇÃO DE PRIVACIDADE PARA COMPARTILHAMENTO ---
$is_public_view = false;
if ($colecao_id) {
    // Busca a privacidade da coleção atual para decidir se mostra o botão de compartilhar
    $stmt_priv = $pdo->prepare("SELECT privacidade FROM Salvos_Colecoes WHERE id = ?");
    $stmt_priv->execute([$colecao_id]);
    $priv_status = $stmt_priv->fetchColumn();
    if ($priv_status === 'publica') {
        $is_public_view = true;
    }
}

// 5. Inclusão dos Componentes de Cabeçalho Global
require_once __DIR__ . '/../../templates/header.php';
include __DIR__ . '/../../templates/mobile_nav.php'; 
?>

<style>
    /* CALIBRAÇÃO DE COMANDOS - FORCE STYLE */
    .saved-page-header {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        margin-bottom: 25px !important;
        width: 100% !important;
    }

    .header-actions {
        display: flex !important;
        gap: 10px !important;
        align-items: center !important;
    }

    /* Botão Gerenciar (Padrão e Ativo) */
    .btn-manage-collections {
        background-color: #f0f2f5 !important;
        color: #1c1e21 !important;
        border: none !important;
        padding: 10px 18px !important;
        border-radius: 20px !important;
        font-size: 0.85rem !important;
        font-weight: 700 !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        transition: all 0.2s ease !important;
    }

    .btn-manage-collections.is-active {
        background-color: #dc3545 !important;
        color: #ffffff !important;
    }

    /* Botão Nova Coleção */
    .btn-create-collection-minimal {
        background-color: rgba(12, 45, 84, 0.05) !important;
        color: #0C2D54 !important;
        border: none !important;
        padding: 10px 18px !important;
        border-radius: 20px !important;
        font-size: 0.85rem !important;
        font-weight: 700 !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        transition: all 0.2s ease !important;
    }

    /* BOTÃO COMPARTILHAR (NOVO V81.0) */
    .btn-share-collection {
        background-color: #e7f3ff !important;
        color: #1877f2 !important;
        border: none !important;
        padding: 10px 18px !important;
        border-radius: 20px !important;
        font-size: 0.85rem !important;
        font-weight: 700 !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        transition: all 0.2s ease !important;
    }

    .btn-share-collection:hover {
        background-color: #d8eaff !important;
    }

    .btn-create-collection-minimal:hover {
        background-color: #0C2D54 !important;
        color: #ffffff !important;
    }

    .premium-title {
        font-size: 1.6rem !important;
        font-weight: 800 !important;
        color: #0C2D54 !important;
        margin: 0 !important;
    }
</style>

<div class="main-content-area">
    
    <?php include __DIR__ . '/../../templates/sidebar.php'; ?>

    <main class="feed-container">
        <div class="saved-module-wrapper-minimal">
            
            <section class="saved-content-main">
                
                <div class="saved-page-header">
                    <h2 class="premium-title"><i class="fas fa-bookmark"></i> Itens Salvos</h2>
                    
                    <div class="header-actions">
                        <?php if ($is_public_view): ?>
                            <button class="btn-share-collection" onclick="copyCollectionLink(<?php echo $colecao_id; ?>)">
                                <i class="fas fa-link"></i> Compartilhar
                            </button>
                        <?php endif; ?>

                        <button class="btn-manage-collections" onclick="toggleGerenciamentoColecoes()">
                            <i class="fas fa-cog"></i> Gerenciar
                        </button>
                        
                        <button class="btn-create-collection-minimal" onclick="abrirModalCriarColecao()">
                            <i class="fas fa-plus"></i> Nova Coleção
                        </button>
                    </div>
                </div>

                <nav class="saved-collections-nav-container">
                    <?php 
                    include __DIR__ . '/componentes/navegacao_colecoes.php'; 
                    ?>
                </nav>

                <div class="saved-search-filter-bar">
                    <?php 
                    include __DIR__ . '/componentes/barra_filtros.php'; 
                    ?>
                </div>

                <div id="saved-items-container" class="saved-items-list-wrapper">
                    <?php 
                    include __DIR__ . '/componentes/renderizador_lista.php'; 
                    ?>
                </div>

                <div id="saved-loader" class="is-hidden">
                    <div class="spinner"></div>
                </div>

            </section>

        </div>
    </main>
</div>

<script>
/**
 * Copia o link da coleção pública para a área de transferência.
 */
function copyCollectionLink(id) {
    const url = "<?php echo $config['base_url']; ?>salvos/" + id;
    
    navigator.clipboard.writeText(url).then(() => {
        // Feedback visual (Toasts ou Alertas já existentes no seu sistema)
        if (typeof showToast === "function") {
            showToast("Link da coleção copiado!", "success");
        } else {
            alert("Link da coleção copiado para a área de transferência!");
        }
    }).catch(err => {
        console.error('Erro ao copiar link: ', err);
    });
}
</script>

<?php 
// 6. Inclusão do Footer Comum (Central de Motores e Modais)
require_once __DIR__ . '/../../templates/footer.php'; 
?>