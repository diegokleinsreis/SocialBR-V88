<?php
/**
 * views/chat/componentes/privado/info_contato.php
 * Sub-componente Especialista: Centro de Comando 1x1.
 * PAPEL: Exibir perfil detalhado, galeria de mídias, grupos mútuos e ações de segurança.
 * VERSÃO: V65.3 (Ajuste de Espaçamento e Cor Oficial - socialbr.lol)
 */

if (session_status() == PHP_SESSION_NONE) { session_start(); }

// 1. Dependências de Sistema
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../src/ChatLogic.php';

$usuario_id = (int)($_GET['usuario_id'] ?? 0);
$conversa_id = (int)($_GET['conversa_id'] ?? 0);
$user_id_logado = $_SESSION['user_id'] ?? 0;

if ($usuario_id <= 0) exit("Parâmetros inválidos.");

/**
 * 2. RECUPERAÇÃO DE DADOS (DNA CheckYou)
 */
$sql = "SELECT nome, sobrenome, foto_perfil_url, nome_de_usuario, ultimo_acesso, data_cadastro 
        FROM Usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

if (!$usuario) exit("Utilizador não encontrado.");

$nome_completo = $usuario['nome'] . ' ' . $usuario['sobrenome'];
$avatar_contato = !empty($usuario['foto_perfil_url']) 
    ? $config['base_path'] . $usuario['foto_perfil_url'] 
    : $config['base_path'] . 'assets/images/default-avatar.png';

$midias = ChatLogic::getConversationMedia($conn, $conversa_id);
$grupos_mutuos = ChatLogic::getMutualGroups($conn, $user_id_logado, $usuario_id);
?>

<div class="sb-contact-info-panel">
    <header class="sb-info-header">
        <button class="sb-btn-back-info" onclick="chatAcoes.toggleRightSidebar(false)" title="Voltar para a conversa">
            <i class="fas fa-arrow-left"></i>
        </button>
        <h3>Informações do Contato</h3>
    </header>

    <div class="sb-info-body">
        <div class="sb-info-content-wrapper">
            
            <div class="sb-info-profile">
                <div class="sb-info-avatar-wrapper">
                    <div class="sb-info-avatar">
                        <img src="<?php echo $avatar_contato; ?>" 
                             onerror="this.src='<?php echo $config['base_path']; ?>assets/images/default-avatar.png';">
                    </div>
                </div>
                <h2 class="sb-info-title"><?php echo htmlspecialchars($nome_completo); ?></h2>
                <p class="sb-info-meta">@<?php echo htmlspecialchars($usuario['nome_de_usuario']); ?></p>
                
                <a href="<?php echo $config['base_path']; ?>perfil/<?php echo $usuario_id; ?>" class="sb-info-cta-btn">
                    <i class="fas fa-user"></i> Ver Perfil Completo
                </a>
            </div>

            <div class="sb-info-section">
                <div class="sb-section-header">
                    <h4>Mídias Recentes</h4>
                    <button class="sb-view-all-link" onclick="chatAcoes.openMediaHub(<?php echo $conversa_id; ?>)">Ver tudo</button>
                </div>
                
                <?php if (!empty($midias)): ?>
                    <div class="sb-media-horizontal-scroll">
                        <?php foreach (array_slice($midias, 0, 10) as $m): ?>
                            <div class="sb-media-item" onclick="chatAcoes.openMediaHub(<?php echo $conversa_id; ?>)">
                                <?php if ($m['tipo_midia'] === 'photo' || $m['tipo_midia'] === 'foto'): ?>
                                    <img src="<?php echo $config['base_path'] . $m['midia_url']; ?>" alt="Mídia">
                                <?php else: ?>
                                    <div class="sb-media-video-thumb">
                                        <i class="fas fa-play"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="sb-empty-info">Nenhuma foto ou vídeo compartilhado ainda.</p>
                <?php endif; ?>
            </div>

            <div class="sb-info-section">
                <div class="sb-section-header">
                    <h4>Grupos em Comum (<?php echo count($grupos_mutuos); ?>)</h4>
                </div>
                
                <?php if (!empty($grupos_mutuos)): ?>
                    <div class="sb-mutual-groups-list">
                        <?php foreach ($grupos_mutuos as $g): 
                            $capa = !empty($g['capa_url']) ? $config['base_path'] . $g['capa_url'] : $config['base_path'] . 'assets/images/default-group.png';
                        ?>
                            <div class="sb-mutual-group-item" onclick="chatMotor.trocarConversa(<?php echo $g['id']; ?>)">
                                <img src="<?php echo $capa; ?>" class="sb-mutual-g-img">
                                <span class="sb-mutual-g-name"><?php echo htmlspecialchars($g['titulo']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="sb-empty-info">Vocês ainda não participam de grupos juntos.</p>
                <?php endif; ?>
            </div>

            <div class="sb-info-section">
                <div class="sb-section-header">
                    <h4>Privacidade e Segurança</h4>
                </div>
                <div class="sb-security-grid">
                    <button class="sb-security-card" onclick="chatAcoes.toggleMute(<?php echo $conversa_id; ?>)">
                        <i class="fas fa-bell-slash"></i>
                        <span>Silenciar</span>
                    </button>
                    <button class="sb-security-card" onclick="chatAcoes.openReport(<?php echo $usuario_id; ?>)">
                        <i class="fas fa-flag"></i>
                        <span>Denunciar</span>
                    </button>
                    <button class="sb-security-card text-danger" onclick="chatAcoes.toggleBlock(<?php echo $usuario_id; ?>)">
                        <i class="fas fa-ban"></i>
                        <span>Bloquear</span>
                    </button>
                </div>
            </div>

            <div class="sb-info-footer-data">
                <p><i class="fas fa-calendar-alt"></i> No Social BR desde <?php echo date('F \d\e Y', strtotime($usuario['data_cadastro'])); ?></p>
            </div>

        </div>
    </div>
</div>

<style>
.sb-contact-info-panel { 
    background: var(--chat-bg-card); 
    width: 100%; height: 100%; 
    display: flex; flex-direction: column; 
    overflow: hidden;
}

.sb-info-header { 
    padding: 0 30px; border-bottom: 1px solid var(--chat-border); 
    display: flex; align-items: center; gap: 20px; 
    background: #fff; height: 75px; flex-shrink: 0;
}

.sb-info-header h3 { margin: 0; font-size: 1.1rem; color: #0C2D54; font-weight: 800; }

.sb-btn-back-info { 
    background: rgba(12, 45, 84, 0.05); border: none; width: 40px; height: 40px; 
    border-radius: 50%; color: #0C2D54; cursor: pointer;
    display: flex; align-items: center; justify-content: center; transition: all 0.2s;
}
.sb-btn-back-info:hover { background: #0C2D54; color: #fff; transform: translateX(-3px); }

.sb-info-body { flex: 1; overflow-y: auto; background: #f9fafb; padding-bottom: 40px; }

.sb-info-content-wrapper {
    max-width: 800px; 
    /* Ajuste: margin-top zero para encostar no cabeçalho */
    margin: 0 auto 40px; 
    background: #fff; 
    border-radius: 16px; border: 1px solid var(--chat-border); overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.sb-info-profile { 
    /* Ajuste: padding-top reduzido de 50px para 30px */
    padding: 30px 30px 50px; 
    text-align: center; 
    border-bottom: 1px solid var(--chat-border); 
}

.sb-info-avatar-wrapper { width: 140px; height: 140px; margin: 0 auto 20px; }
.sb-info-avatar { 
    width: 100%; height: 100%; border-radius: 50%; overflow: hidden; 
    border: 5px solid #fff; box-shadow: 0 8px 25px rgba(0,0,0,0.1); 
}
.sb-info-avatar img { width: 100%; height: 100%; object-fit: cover; }
.sb-info-title { font-size: 1.6rem; font-weight: 900; color: var(--chat-text); margin: 0; }
.sb-info-meta { color: var(--chat-text-sub); margin: 5px 0 20px; font-weight: 500; }

.sb-info-cta-btn {
    display: inline-flex; align-items: center; gap: 10px;
    background: #0C2D54; color: #fff; text-decoration: none;
    padding: 12px 25px; border-radius: 30px; font-weight: 700; font-size: 0.9rem;
    transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(12, 45, 84, 0.2);
}
.sb-info-cta-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(12, 45, 84, 0.3); }

.sb-info-section { padding: 30px; border-bottom: 1px solid var(--chat-border); }
.sb-section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.sb-section-header h4 { 
    font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1.2px; 
    color: var(--chat-text-sub); font-weight: 800; border-left: 4px solid #0C2D54; padding-left: 12px; margin: 0;
}
.sb-view-all-link { background: none; border: none; color: #0C2D54; font-weight: 700; cursor: pointer; font-size: 0.85rem; }

.sb-media-horizontal-scroll {
    display: flex; gap: 12px; overflow-x: auto; padding-bottom: 10px;
}
.sb-media-item {
    min-width: 110px; width: 110px; height: 110px; border-radius: 12px;
    overflow: hidden; background: #eee; cursor: pointer; transition: transform 0.2s;
}
.sb-media-item:hover { transform: scale(1.05); }
.sb-media-item img { width: 100%; height: 100%; object-fit: cover; }

.sb-mutual-groups-list { display: flex; flex-direction: column; gap: 10px; }
.sb-mutual-group-item {
    display: flex; align-items: center; gap: 15px; padding: 12px;
    background: #f8fafc; border-radius: 12px; cursor: pointer; transition: all 0.2s;
}
.sb-mutual-group-item:hover { background: #eff6ff; border-color: #0C2D54; }
.sb-mutual-g-img { width: 40px; height: 40px; border-radius: 8px; object-fit: cover; }
.sb-mutual-g-name { font-weight: 700; font-size: 0.95rem; color: var(--chat-text); }

.sb-security-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
.sb-security-card {
    display: flex; flex-direction: column; align-items: center; gap: 10px;
    padding: 20px; background: #f8fafc; border: 1px solid var(--chat-border);
    border-radius: 12px; cursor: pointer; transition: all 0.2s;
}
.sb-security-card:hover { background: #fff; border-color: #0C2D54; transform: translateY(-3px); }
.sb-security-card i { font-size: 1.2rem; color: var(--chat-text-sub); }
.sb-security-card span { font-size: 0.8rem; font-weight: 700; color: var(--chat-text); }

.sb-empty-info { font-size: 0.85rem; color: var(--chat-text-sub); font-style: italic; }
.sb-info-footer-data { padding: 20px; text-align: center; color: var(--chat-text-sub); font-size: 0.8rem; }

.sb-media-horizontal-scroll::-webkit-scrollbar { height: 6px; }
.sb-media-horizontal-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

@media (max-width: 768px) {
    .sb-info-content-wrapper { margin: 0; border-radius: 0; border: none; }
    .sb-security-grid { grid-template-columns: 1fr; }
}
</style>