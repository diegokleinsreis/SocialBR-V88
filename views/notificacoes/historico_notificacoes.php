<?php
/**
 * views/notificacoes/historico_notificacoes.php
 * VERSÃO: 11.9 (Full Padronização - socialbr.lol)
 * PAPEL: Visualização do Histórico sincronizada com tipos_notificacoes.php.
 */

if (session_status() == PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/tipos_notificacoes.php'; // IMPORTANTE: Nosso dicionário
require_once __DIR__ . '/../../src/NotificationLogic.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    $base = isset($config['base_path']) ? $config['base_path'] : '/';
    header("Location: " . $base . "login");
    exit();
}
$user_id = (int)$_SESSION['user_id'];

if (!isset($conn)) {
    die("Erro Crítico: Conexão não encontrada.");
}

$notificacoes = NotificationLogic::getNotificationsForUser($conn, $user_id);
$site_nome = isset($config['site_nome']) ? $config['site_nome'] : 'Social BR';
$page_title = 'Notificações - ' . htmlspecialchars($site_nome);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <?php include __DIR__ . '/../../templates/head_common.php'; ?>
    <style>
        .notifications-header {
            display: flex;
            justify-content: space-between; 
            align-items: center;
            padding: 20px; 
            border-bottom: 1px solid #f0f2f5;
            background: #fff;
        }
        .notifications-header h3 { margin: 0; font-size: 1.25rem; font-weight: 700; color: #050505; }

        .btn-marcar-lidas {
            background-color: #e4e6eb;
            color: #050505;
            padding: 8px 14px;
            border-radius: 6px;
            border: none;
            font-size: 0.9em;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s;
        }
        .btn-marcar-lidas:hover { background-color: #d8dadf; }
        
        .full-notifications-page-list .notification-item.unread { background-color: #f0f7ff; }
    </style>
</head>
<body>

    <?php include __DIR__ . '/../../templates/header.php'; ?>
    <?php include __DIR__ . '/../../templates/mobile_nav.php'; ?>

    <div class="main-content-area">
        <?php include __DIR__ . '/../../templates/sidebar.php'; ?>

        <main class="feed-container">
            <div class="post-card" style="padding: 0; background: #fff; border-radius: 12px; border: 1px solid #dddfe2; overflow: hidden; margin-top: 20px;"> 
                <div class="notifications-header">
                    <h3>Notificações</h3>
                    <button class="btn-marcar-lidas" id="marcar-todas-lidas-btn">
                        <i class="fas fa-check-double"></i> Marcar todas como lidas
                    </button>
                </div>

                <div class="full-notifications-page-list">
                    <?php if (empty($notificacoes)): ?>
                        <div class="no-notifications-message" style="padding: 60px 40px; text-align: center; color: #65676b;">
                            <i class="far fa-bell" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.3;"></i>
                            <p>Você não tem notificações no momento.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notificacoes as $notif): 
                            $isUnread = ($notif['lida'] == 0);
                            $isUnreadClass = $isUnread ? 'unread' : '';
                            $tipo = trim($notif['tipo']);
                            $text = 'interagiu com você.'; // Fallback
                            $idRef = $notif['id_referencia'];
                            
                            $avatar_url = !empty($notif['remetente_foto']) 
                                ? $config['base_path'] . htmlspecialchars($notif['remetente_foto']) 
                                : $config['base_path'] . 'assets/images/default-avatar.png';

                            $grupoNome = !empty($notif['grupo_nome']) ? $notif['grupo_nome'] : 'um grupo';

                            // --- MAPEAMENTO SINCRONIZADO COM tipos_notificacoes.php ---
                            switch ($tipo) {
                                case 'mensagem':
                                    $text = 'enviou uma nova mensagem.';
                                    $link = $config['base_path'] . 'chat?id=' . $idRef;
                                    break;

                                case 'curtida': case 'curtida_post':
                                    $text = 'curtiu a sua publicação.';
                                    $link = $config['base_path'] . 'postagem/' . $idRef; 
                                    break;

                                case 'comentario': case 'comentario_post':
                                    $text = 'comentou na sua publicação.';
                                    $link = $config['base_path'] . 'postagem/' . $idRef; 
                                    break;

                                case 'compartilhar': case 'compartilhamento_post':
                                    $text = 'compartilhou a sua publicação.';
                                    $link = $config['base_path'] . 'postagem/' . $idRef; 
                                    break;

                                case 'curtida_comentario':
                                    $text = 'curtiu o seu comentário.';
                                    $link = $config['base_path'] . 'postagem/' . $idRef; 
                                    break;

                                case 'pedido_amizade':
                                    $text = 'enviou um pedido de amizade.';
                                    $link = $config['base_path'] . 'perfil/' . $notif['remetente_id']; 
                                    break;

                                case 'amizade_aceita': case 'aceite_amizade':
                                    $text = 'aceitou o seu pedido de amizade.';
                                    $link = $config['base_path'] . 'perfil/' . $notif['remetente_id']; 
                                    break;

                                case 'convite_grupo':
                                    $text = 'convidou você para o grupo <strong>' . htmlspecialchars($grupoNome) . '</strong>.';
                                    $link = $config['base_path'] . 'grupos/ver/' . $idRef;
                                    break;

                                case 'solicitacao_grupo':
                                    $text = 'quer entrar no seu grupo <strong>' . htmlspecialchars($grupoNome) . '</strong>.';
                                    $link = $config['base_path'] . 'grupos/ver/' . $idRef;
                                    break;

                                case 'aceite_solicitacao_grupo':
                                    $text = 'aceitou sua entrada no grupo <strong>' . htmlspecialchars($grupoNome) . '</strong>.';
                                    $link = $config['base_path'] . 'grupos/ver/' . $idRef;
                                    break;

                                case 'aceite_convite_grupo':
                                    $text = 'aceitou seu convite para o grupo <strong>' . htmlspecialchars($grupoNome) . '</strong>.';
                                    $link = $config['base_path'] . 'grupos/ver/' . $idRef;
                                    break;

                                case 'promocao_moderador':
                                    $text = 'te promoveu a moderador no grupo <strong>' . htmlspecialchars($grupoNome) . '</strong>.';
                                    $link = $config['base_path'] . 'grupos/ver/' . $idRef;
                                    break;

                                case 'rebaixamento_membro':
                                    $text = 'alterou seu cargo para membro no grupo <strong>' . htmlspecialchars($grupoNome) . '</strong>.';
                                    $link = $config['base_path'] . 'grupos/ver/' . $idRef;
                                    break;

                                case 'transferencia_dono':
                                    $text = 'transferiu a posse do grupo <strong>' . htmlspecialchars($grupoNome) . '</strong> para você.';
                                    $link = $config['base_path'] . 'grupos/ver/' . $idRef;
                                    break;

                                case 'expulsao_grupo':
                                    $text = 'removeu você do grupo <strong>' . htmlspecialchars($grupoNome) . '</strong>.';
                                    $link = $config['base_path'] . 'grupos';
                                    break;

                                case 'convite_chat_grupo':
                                    $text = 'convidou você para o chat em grupo <strong>' . htmlspecialchars($grupoNome) . '</strong>.';
                                    $link = $config['base_path'] . 'chat?id=' . $idRef;
                                    break;

                                case 'voto_enquete':
                                    $text = 'votou na sua enquete.';
                                    $link = $config['base_path'] . 'postagem/' . $idRef;
                                    break;

                                case 'interesse_mkt':
                                    $text = 'demonstrou interesse num item seu no Marketplace.';
                                    $link = $config['base_path'] . 'marketplace/item/' . $idRef;
                                    break;

                                case 'broadcast':
                                    $text = '<strong>' . htmlspecialchars($notif['remetente_nome']) . '</strong>: ' . htmlspecialchars($notif['grupo_nome']);
                                    $link = '#';
                                    $avatar_url = $config['base_path'] . 'assets/images/favicon.png';
                                    break;

                                default:
                                    $link = $config['base_path'] . 'historico_notificacoes';
                            }
                        ?>
                            <a href="<?php echo $link; ?>" class="notification-item <?php echo $isUnreadClass; ?>" 
                               style="display: flex; align-items: center; padding: 16px; border-bottom: 1px solid #f0f2f5; text-decoration: none; color: inherit; transition: background 0.2s;">
                                
                                <div class="notification-avatar" style="margin-right: 15px;">
                                    <img src="<?php echo $avatar_url; ?>" alt="Avatar" style="width: 56px; height: 56px; border-radius: 50%; object-fit: cover; border: 1px solid #dddfe2;">
                                </div>

                                <div class="notification-text" style="flex: 1;">
                                    <p style="margin: 0; font-size: 0.95rem; line-height: 1.4; color: #050505;">
                                        <?php if ($tipo !== 'broadcast'): ?>
                                            <strong style="font-weight: 700;"><?php echo htmlspecialchars($notif['remetente_nome'] . ' ' . $notif['remetente_sobrenome']); ?></strong> 
                                        <?php endif; ?>
                                        <?php echo $text; ?>
                                    </p>
                                    <span class="notification-time" style="font-size:0.85rem; color:#65676b; display: block; margin-top: 4px;">
                                        <?php echo date("d/m/Y - H:i", strtotime($notif['data_criacao'])); ?>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

<?php include __DIR__ . '/../../templates/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const markAllReadBtn = document.getElementById('marcar-todas-lidas-btn');
        const appPath = document.body.getAttribute('data-base-path') || "<?php echo $config['base_path']; ?>";

        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function() {
                const originalContent = markAllReadBtn.innerHTML;
                markAllReadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Aguarde...';
                markAllReadBtn.disabled = true;

                fetch(appPath + 'api/notificacoes/marcar_como_lida.php', { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.error || 'Erro ao processar requisição.');
                        markAllReadBtn.innerHTML = originalContent;
                        markAllReadBtn.disabled = false;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Erro de conexão com o servidor.');
                    markAllReadBtn.innerHTML = originalContent;
                    markAllReadBtn.disabled = false;
                });
            });
        }
    });
</script>
</body>
</html>