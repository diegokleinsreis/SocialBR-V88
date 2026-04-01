<?php
/**
 * views/chat/home.php
 * Esqueleto Orquestrador do Sistema de Chat.
 * PAPEL: Centralizar o chat em um Card Premium (Estilo CheckYou).
 * VERSÃO: V64.7 (Fix: Sincronização de Tokens CSRF Globais - socialbr.lol)
 */

// 1. Verificações de Segurança e Contexto
if (session_status() == PHP_SESSION_NONE) { 
    session_start(); 
}

/**
 * BLINDAGEM DE SEGURANÇA V64.7:
 * Sincronizamos o token do chat com o token global do site (csrf_token).
 * Isso permite que o chat utilize APIs externas (como bloqueio) sem erros de segurança.
 */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// Unificamos para que componentes antigos que usam 'token' continuem a funcionar
$_SESSION['token'] = $_SESSION['csrf_token'];

// Caminhos absolutos conforme a Constituição V2.1
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/ChatLogic.php';

// Proteção de Acesso
if (!isset($_SESSION['user_id'])) {
    header("Location: " . $config['base_path'] . "login");
    exit;
}

$user_id_logado = $_SESSION['user_id'];

/**
 * ROTEAMENTO VIRTUAL:
 * Captura o ID da rota para abertura direta ou via AJAX.
 */
$target_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Lógica de Visibilidade Mobile (Transição Zero-Reload)
$sidebar_class = $target_id ? 'mobile-hidden' : '';
$window_class = !$target_id ? 'mobile-hidden' : '';

$page_title = "Mensagens | socialbr.lol";

// 2. Inclusão dos Componentes de Topo Oficiais do Site
include __DIR__ . '/../../templates/header.php';
include __DIR__ . '/../../templates/mobile_nav.php';
?>

<style>
    /* Trava a página inteira para modo App-Like */
    body.page-chat, body.page-chat html {
        height: 100% !important;
        overflow: hidden !important;
        margin: 0 !important; 
        padding: 0 !important;
        position: fixed;
        width: 100%;
        background-color: #f0f2f5; /* Cor de fundo "chão" para destacar o card */
    }

    body.dark-mode.page-chat { background-color: #0f172a; }

    /**
     * ESTRUTURA V64.6:
     * O container mestre agora centraliza o card no meio da tela com compensação de 85px.
     */
    .main-content-area {
        display: flex;
        justify-content: center; /* Centraliza o card horizontalmente */
        align-items: center;     /* Centraliza o card verticalmente */
        /* Sincronizado com --site-header-height do CSS para evitar sobreposição */
        height: calc(100dvh - var(--site-header-height, 85px)); 
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 20px !important; /* Margem de respiro para o efeito flutuante */
        overflow: hidden;
    }

    /**
     * MENU GLOBAL: Esconde o menu lateral apenas no Desktop para foco no Chat.
     */
    @media (min-width: 769px) {
        .main-content-area > aside:first-child {
            display: none !important;
        }
    }

    /* Container que sustenta a estrutura flexível */
    .chat-main-container { 
        width: 100%;
        max-width: 1400px; 
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .chat-app-wrapper { 
        flex: 1;
        display: flex; 
        width: 100% !important; 
        height: 100%;
    }

    /**
     * O CARD PREMIUM (DNA CheckYou)
     */
    .chat-app-card { 
        height: 100% !important; 
        width: 100% !important; 
        background: #ffffff;
        border-radius: 16px !important; 
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.12) !important; 
        display: grid;
        grid-template-columns: 380px 1fr; 
        position: relative;
        min-height: 0; 
    }

    body.dark-mode .chat-app-card {
        background: var(--chat-bg-card);
        border-color: var(--chat-border) !important;
        box-shadow: 0 10px 40px rgba(0,0,0,0.4) !important;
    }

    /* Sidebar interna configurada para forçar scroll */
    .chat-sidebar-list {
        display: flex !important;
        flex-direction: column !important;
        height: 100% !important;
        min-height: 0 !important;
        border-right: 1px solid var(--chat-border);
        background-color: var(--chat-bg-sidebar);
        z-index: 5;
    }

    /* Janela principal configurada para manter o campo de input visível */
    .chat-main-window {
        display: flex !important;
        flex-direction: column !important;
        height: 100% !important;
        min-height: 0 !important;
        position: relative;
        overflow: hidden;
        background: #fff;
    }

    /* RESPONSIVIDADE ADAPTATIVA V64.6 */
    @media (max-width: 768px) {
        /* Trava de visibilidade mobile para evitar sobreposição fantasma */
        .mobile-hidden {
            display: none !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        .main-content-area { 
            padding: 0 !important; 
            height: calc(100dvh - var(--site-header-height, 85px)) !important; 
            justify-content: flex-start !important;
            align-items: flex-start !important;
        } 
        
        .chat-app-card { 
            grid-template-columns: 1fr; 
            border-radius: 0 !important; 
            box-shadow: none !important;
            border: none !important;
            min-height: 0 !important;
        }

        .chat-sidebar-list, .chat-main-window {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10;
        }

        /* Se a janela de chat estiver ativa, ela ganha prioridade de camada */
        .chat-main-window:not(.mobile-hidden) {
            z-index: 20;
        }
    }
</style>

<div class="main-content-area">
    
    <?php include __DIR__ . '/../../templates/sidebar.php'; ?>

    <main class="chat-main-container">
        <section class="chat-app-wrapper">
            <div class="chat-app-card" id="chat-master-card">
                
                <aside class="chat-sidebar-list <?php echo $sidebar_class; ?>" id="chat-sidebar-container">
                    <?php include __DIR__ . '/componentes/lista_conversas.php'; ?>
                </aside>

                <section class="chat-main-window <?php echo $window_class; ?>" id="chat-active-window">
                    <?php 
                        if ($target_id) {
                            include __DIR__ . '/componentes/janela_conversa.php';
                        } else {
                            include __DIR__ . '/componentes/estado_vazio.php';
                        }
                    ?>
                </section>

            </div>
        </section>
    </main>
</div>

<div id="chat-media-modal" class="modal-base is-hidden"></div>

<script>
    /**
     * CONFIGURAÇÃO GLOBAL DO MOTOR V64.7:
     * Utilizamos o token CSRF global do sistema para garantir compatibilidade
     * total com as APIs de usuários (bloqueio, denúncia, etc).
     */
    window.CHAT_CONFIG = {
        baseUrl: "<?php echo $config['base_path']; ?>",
        myId: <?php echo $user_id_logado; ?>,
        token: "<?php echo $_SESSION['csrf_token']; ?>"
    };

    /**
     * REGRA DE OURO: Body-Freeze Imediato
     */
    (function() {
        const body = document.body;
        body.classList.add('page-chat');
        console.log("💎 V64.7: Sincronização CSRF Global concluída.");
    })();
</script>

<?php 
// Inclusão do Footer Global
include __DIR__ . '/../../templates/footer.php'; 
?>