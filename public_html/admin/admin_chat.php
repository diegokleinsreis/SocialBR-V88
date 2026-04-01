<?php
/**
 * admin/admin_chat.php
 * PAPEL: Centro de Comando de Chat (Monitoramento & Moderação)
 * VERSÃO: 2.0 (Final: User Link Inspector & Privacy Tracker - socialbr.lol)
 */

// --- [PASSO 1: PROTEÇÃO E SEGURANÇA] ---
if (!defined('ACESSO_ROTEADOR')) {
    die('Acesso direto negado.');
}

require_once __DIR__ . '/admin_auth.php'; // Garante $conn e sessão admin

/**
 * CORREÇÃO DE CAMINHO:
 * ChatLogic está em /src/ChatLogic.php (fora da public_html).
 */
$caminho_logic = __DIR__ . '/../../src/ChatLogic.php';
if (file_exists($caminho_logic)) {
    require_once $caminho_logic;
}

$mensagem_sucesso = "";
$mensagem_erro = "";

// --- [PASSO 2: COLETA DE ESTATÍSTICAS (CARGA INICIAL)] ---
$stats = [
    'total_mensagens' => $conn->query("SELECT COUNT(*) FROM chat_mensagens")->fetch_row()[0] ?? 0,
    'total_grupos'    => $conn->query("SELECT COUNT(*) FROM chat_conversas WHERE tipo = 'grupo'")->fetch_row()[0] ?? 0,
    'mensagens_hoje'  => $conn->query("SELECT COUNT(*) FROM chat_mensagens WHERE DATE(criado_em) = CURDATE()")->fetch_row()[0] ?? 0,
    'usuarios_ativos' => $conn->query("SELECT COUNT(DISTINCT remetente_id) FROM chat_mensagens WHERE criado_em >= (NOW() - INTERVAL 24 HOUR)")->fetch_row()[0] ?? 0
];

// --- [PASSO 3: BUSCA DE GRUPOS E CONVERSAS] ---
$sql_grupos = "SELECT c.*, u.nome AS dono_nome, 
                (SELECT COUNT(*) FROM chat_participantes WHERE conversa_id = c.id) as total_membros,
                (SELECT COUNT(*) FROM chat_mensagens WHERE conversa_id = c.id) as total_msgs
               FROM chat_conversas c
               LEFT JOIN Usuarios u ON c.dono_id = u.id
               WHERE c.tipo = 'grupo'
               ORDER BY c.ultima_mensagem_at DESC";
$lista_grupos = $conn->query($sql_grupos);

// --- [PASSO 4: RENDERIZAÇÃO DO LAYOUT] ---
include __DIR__ . '/templates/admin_header.php'; 
?>

<style>
    /* Estilo OLED Admin Chat */
    .chat-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: #fff; padding: 20px; border-radius: 12px; border-left: 5px solid #0C2D54; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    .stat-card h3 { color: #666; font-size: 0.85em; text-transform: uppercase; margin-bottom: 10px; }
    .stat-card .value { color: #0C2D54; font-size: 1.8em; font-weight: 800; }
    .stat-card.gold { border-left-color: #DAA520; }

    .admin-grid-chat { display: grid; grid-template-columns: 1.5fr 1fr; gap: 25px; align-items: start; }
    .input-desc { display: block; color: #666; font-size: 0.75em; margin-top: 4px; line-height: 1.2; }
    
    /* Badges de Status e Origem */
    .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.7em; font-weight: 800; text-transform: uppercase; }
    .status-ativa { background: #e1f5fe; color: #0288d1; }
    .status-bloqueada { background: #ffebee; color: #d32f2f; }

    .badge-privada { background: #e3f2fd !important; color: #1976d2 !important; }
    .badge-grupo { background: #fff3e0 !important; color: #ef6c00 !important; }

    /* Botões de Ação na Tabela */
    .actions-cell { 
        display: flex; 
        gap: 10px; 
        justify-content: center; 
        align-items: center;
        flex-wrap: nowrap;
        min-width: 150px;
    }
    .actions-cell .filter-btn {
        margin: 0 !important;
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }

    /* Modais */
    .modal-ghost { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(5px); }
    .modal-content { background: #f0f2f5; width: 90%; max-width: 600px; margin: 50px auto; border-radius: 15px; height: 80vh; display: flex; flex-direction: column; overflow: hidden; }
    .modal-header { padding: 20px; background: #0C2D54; color: #fff; display: flex; justify-content: space-between; align-items: center; }
    #chat-history-body, #member-list-body { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 12px; }
    
    .bubble { max-width: 85%; padding: 12px; border-radius: 12px; font-size: 0.92em; position: relative; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .bubble.admin-view { background: #fff; align-self: flex-start; border: 1px solid #e0e0e0; }
    .bubble .sender { font-size: 0.72em; font-weight: 800; color: #DAA520; display: block; margin-bottom: 5px; text-transform: uppercase; }

    /* Estilos Membros e Investigação */
    .member-item { background: #fff; padding: 12px; border-radius: 10px; display: flex; align-items: center; justify-content: space-between; border: 1px solid #eee; transition: transform 0.2s; }
    .member-item:hover { transform: translateX(5px); border-color: #DAA520; }
    .member-info { display: flex; align-items: center; gap: 12px; flex: 1; }
    .member-avatar { 
        width: 42px; 
        height: 42px; 
        border-radius: 50%; 
        object-fit: cover; 
        border: 2px solid #f0f0f0; 
        flex-shrink: 0; 
        background: #eee;
    }
    .member-name { font-weight: 700; font-size: 0.95em; color: #0C2D54; }

    /* Estilos Investigador (v9.4) */
    .inspect-item { background: #fff; padding: 10px; border-radius: 8px; border: 1px solid #eee; display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
    .inspect-meta { font-size: 0.85em; color: #333; font-weight: 600; }

    @media (max-width: 992px) { .admin-grid-chat { grid-template-columns: 1fr; } }
</style>

<main class="admin-main-content">
    <a href="dashboard" class="admin-back-button">
        <i class="fas fa-arrow-left"></i> Voltar ao Painel
    </a>

    <div class="admin-card">
        <h1><i class="fas fa-comments" style="color: #0C2D54;"></i> Centro de Comando de Chat</h1>
        <p>Monitoramento dinâmico, fiscalização de conversas e rastreamento de usuários.</p>
    </div>

    <div class="chat-stats-grid">
        <div class="stat-card">
            <h3>Mensagens Totais</h3>
            <div class="value" id="stat-total-msgs"><?= number_format($stats['total_mensagens'], 0, ',', '.') ?></div>
            <small class="input-desc">Volume histórico plataforma.</small>
        </div>
        <div class="stat-card gold">
            <h3>Grupos Ativos</h3>
            <div class="value" id="stat-total-grupos"><?= $stats['total_grupos'] ?></div>
            <small class="input-desc">Comunidades gerenciadas.</small>
        </div>
        <div class="stat-card">
            <h3>Hoje</h3>
            <div class="value" id="stat-msgs-hoje"><?= $stats['mensagens_hoje'] ?></div>
            <small class="input-desc">Atividade 24h.</small>
        </div>
        <div class="stat-card gold">
            <h3>Usuários Online</h3>
            <div class="value" id="stat-users-ativos"><?= $stats['usuarios_ativos'] ?></div>
            <small class="input-desc">Engajamento recente.</small>
        </div>
    </div>

    <div class="admin-card" style="margin-bottom: 25px; border-left: 5px solid #DAA520;">
        <h2><i class="fas fa-user-shield"></i> Inspeção de Vínculos</h2>
        <p>Identifique com quem um usuário conversa e quais grupos ele participa.</p>
        <div class="form-group" style="display: flex; gap: 15px; align-items: flex-end; margin-top: 15px;">
            <div style="flex: 1;">
                <label for="inspect-user-search">Utilizador (Nome ou ID)</label>
                <input type="text" id="inspect-user-search" placeholder="Ex: Diego ou 5" onkeyup="ChatManager.inspectUser()">
                <small class="input-desc">Digite ao menos 3 caracteres para iniciar o rastreamento.</small>
            </div>
        </div>
        <div id="inspect-results" style="margin-top: 15px; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px;">
            </div>
    </div>

    <div class="admin-grid-chat">
        <div class="admin-card">
            <h2><i class="fas fa-users"></i> Gestão de Grupos</h2>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Grupo / Proprietário</th>
                            <th style="text-align:center;">Saúde</th>
                            <th style="text-align:center;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($lista_grupos && $lista_grupos->num_rows > 0): ?>
                            <?php while($grp = $lista_grupos->fetch_assoc()): ?>
                                <tr id="grp-row-<?= $grp['id'] ?>">
                                    <td>
                                        <div style="font-weight: 700; color: #0C2D54;"><?= htmlspecialchars($grp['titulo']) ?></div>
                                        <div style="font-size: 0.75em; color: #666;">
                                            Dono: <?= htmlspecialchars($grp['dono_nome'] ?? 'Sistema') ?> | 
                                            Status: <span class="status-badge status-<?= $grp['status'] ?>"><?= $grp['status'] ?></span>
                                        </div>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="stat-badge"><?= $grp['total_membros'] ?> Membros</span>
                                        <div style="font-size: 0.65em; margin-top: 3px;"><?= $grp['total_msgs'] ?> mensagens</div>
                                    </td>
                                    <td class="actions-cell">
                                        <button onclick="ChatManager.openGhostMode(<?= $grp['id'] ?>, '<?= addslashes(htmlspecialchars($grp['titulo'])) ?>')" title="Modo Espectador" class="filter-btn" style="background:#6c757d;">
                                            <i class="fas fa-ghost"></i>
                                        </button>
                                        <button onclick="ChatManager.manageMembers(<?= $grp['id'] ?>, '<?= addslashes(htmlspecialchars($grp['titulo'])) ?>')" title="Gerir Membros" class="filter-btn" style="background:#DAA520;">
                                            <i class="fas fa-user-cog"></i>
                                        </button>
                                        <button onclick="ChatManager.toggleGroupStatus(<?= $grp['id'] ?>, '<?= $grp['status'] ?>')" title="Bloquear/Ativar" class="filter-btn" style="background:#0C2D54;">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" style="text-align:center; padding:20px; color:#999;">Nenhum grupo encontrado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-card">
            <h2><i class="fas fa-search"></i> Auditoria de Segurança</h2>
            <div class="form-group">
                <label for="audit-search">Termo de Busca</label>
                <input type="text" id="audit-search" placeholder="Ex: link, ofensas, spans..." onkeyup="ChatManager.auditMessages()">
                <small class="input-desc">Varredura global em grupos e chats privados.</small>
            </div>
            <div id="audit-results" style="margin-top: 15px; max-height: 400px; overflow-y: auto; background: #f8f9fa; border-radius: 8px; padding: 10px; border: 1px solid #ddd;">
                <p style="text-align:center; color:#999; padding: 20px;">Aguardando termo para auditoria...</p>
            </div>
        </div>
    </div>
</main>

<div id="modalGhost" class="modal-ghost">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="ghost-title">Auditoria de Conversa</h3>
            <span style="cursor:pointer; font-size: 1.5em;" onclick="ChatManager.closeGhostMode()">&times;</span>
        </div>
        <div id="chat-history-body"></div>
        <div style="padding: 15px; background: #fff; border-top: 1px solid #ddd; text-align: center;">
            <small style="color:#d32f2f; font-weight:700;">GHOST MODE: Invisível para todos os participantes.</small>
        </div>
    </div>
</div>

<div id="modalMembers" class="modal-ghost">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="members-title">Gestão de Participantes</h3>
            <span style="cursor:pointer; font-size: 1.5em;" onclick="ChatManager.closeMembersMode()">&times;</span>
        </div>
        <div id="member-list-body"></div>
        <div style="padding: 15px; background: #fff; border-top: 1px solid #ddd; text-align: center;">
            <small class="input-desc">Administradores possuem controle total sobre a hierarquia.</small>
        </div>
    </div>
</div>

<script>
    const ADMIN_BASE = "<?= $config['base_path'] ?>";
</script>

<script src="<?= $config['base_path'] ?>admin/assets/js/chat_manager.js?v=<?= time() ?>"></script>

<?php include __DIR__ . '/templates/admin_mobile_nav.php'; ?>
</body>
</html>