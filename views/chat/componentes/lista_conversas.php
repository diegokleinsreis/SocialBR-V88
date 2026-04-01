<?php
/**
 * views/chat/componentes/lista_conversas.php
 * Componente Atómico: Barra lateral de listagem de conversas.
 * PAPEL: Orquestrador da Lista (Delegador de Itens Privado/Grupo).
 * VERSÃO: V63.0 (Harmonização Premium Card - socialbr.lol)
 */

$user_id_logado = $_SESSION['user_id'] ?? 0;
$filtro_pesquisa = $_GET['search'] ?? null;

/**
 * BUSCA DE CONVERSAS V63.0:
 * Recupera as conversas via ChatLogic com suporte a preview de remetentes.
 */
$conversas = ChatLogic::getConversations($conn, $user_id_logado, $filtro_pesquisa);
?>

<div class="chat-sidebar-header">
    <div class="chat-sidebar-top-row">
        <div class="chat-sidebar-title">
            <h2 style="color: var(--chat-primary) !important;">Mensagens</h2>
        </div>
        <button class="chat-action-circle-btn" id="btn-open-new-chat" title="Iniciar Nova Conversa">
            <i class="fas fa-plus"></i>
        </button>
    </div>
    
    <div class="chat-search-wrapper">
        <div class="chat-search-container">
            <input type="text" id="chat-search-input" placeholder="Pesquisar conversas ou pessoas..." 
                   value="<?php echo htmlspecialchars($filtro_pesquisa ?? ''); ?>" autocomplete="off">
            <button type="button" id="chat-search-submit" class="chat-search-btn-trigger">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </div>
</div>

<div class="chat-list-scrollable" id="chat-list-container">
    
    <div id="chat-local-list">
        <?php if (empty($conversas)): ?>
            <div class="chat-list-empty-premium" id="local-empty-state">
                <div class="empty-list-icon">
                    <i class="fas fa-comment-dots"></i>
                </div>
                <p>Nenhuma conversa encontrada.</p>
                <button class="chat-new-start-btn" onclick="document.getElementById('chat-search-input').focus()">
                    Procurar amigos
                </button>
            </div>
        <?php else: ?>
            <?php foreach ($conversas as $conv): 
                /**
                 * DELEGAÇÃO MODULAR V61.0:
                 * Encaminha a renderização conforme o tipo de conversa.
                 */
                if ($conv['tipo'] === 'grupo') {
                    // Carrega o visual especializado em comunidades
                    include __DIR__ . '/grupos/item_grupo.php';
                } else {
                    // Carrega o visual especializado em chats 1x1
                    include __DIR__ . '/privado/item_privado.php';
                }
            endforeach; ?>
        <?php endif; ?>
    </div>

    <div id="chat-global-search-results" class="is-hidden">
        <div class="chat-search-divider">
            <span>Encontrar novas pessoas</span>
        </div>
        <div id="global-results-list"></div>
    </div>
</div>

<style>
/**
 * REFINAMENTO DE COMPONENTE V63.0
 */
.chat-sidebar-header {
    padding: 20px;
    background: var(--chat-bg-sidebar);
    border-bottom: 1px solid var(--chat-border);
}

.chat-sidebar-top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.chat-sidebar-title h2 {
    font-size: 1.5rem;
    font-weight: 800;
    margin: 0;
    letter-spacing: -0.5px;
}

/* Botão de Ação Premium */
.chat-action-circle-btn {
    width: 38px;
    height: 38px;
    background: rgba(12, 45, 84, 0.08);
    border: none;
    border-radius: 50%;
    color: var(--chat-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.chat-action-circle-btn:hover {
    background: var(--chat-primary);
    color: #fff;
    transform: rotate(90deg);
}

/* Container de Pesquisa V63.0 */
.chat-search-container {
    display: flex;
    background: #fff;
    border: 1px solid var(--chat-border);
    border-radius: 25px; /* Formato Pílula Premium */
    padding: 4px 15px;
    transition: all 0.3s;
}

body.dark-mode .chat-search-container { background: rgba(255,255,255,0.05); }

.chat-search-container:focus-within {
    border-color: var(--chat-primary);
    box-shadow: 0 0 0 3px rgba(12, 45, 84, 0.1);
}

.chat-search-container input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 8px 0;
    font-size: 0.9rem;
    outline: none;
    color: var(--chat-text);
}

.chat-search-btn-trigger {
    background: none;
    border: none;
    color: var(--chat-text-sub);
    cursor: pointer;
    padding-left: 10px;
}

.chat-search-btn-trigger:hover { color: var(--chat-primary); }

.chat-list-scrollable {
    flex: 1;
    overflow-y: auto;
    background: var(--chat-bg-sidebar);
}
</style>