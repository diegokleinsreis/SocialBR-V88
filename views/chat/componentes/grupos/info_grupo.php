<?php
/**
 * views/chat/componentes/grupos/info_grupo.php
 * Sub-componente Especialista: Detalhes da Comunidade.
 * PAPEL: Exibir perfil do grupo ocupando toda a área de mensagens (Overlay).
 * VERSÃO: V66.2 (Compacto & Mobile-Friendly - socialbr.lol)
 */

if (session_status() == PHP_SESSION_NONE) { session_start(); }

// 1. Dependências de Sistema
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../src/ChatLogic.php';

$conversa_id = (int)($_GET['conversa_id'] ?? 0);
$user_id_logado = $_SESSION['user_id'] ?? 0;

if ($conversa_id <= 0) exit("Parâmetros inválidos.");

/**
 * 2. RECUPERAÇÃO DE DADOS V66.2
 */
$grupo = ChatLogic::getGroupDetails($conn, $conversa_id);

if (!$grupo) exit("Grupo não encontrado.");

$e_dono = ((int)$grupo['dono_id'] === (int)$user_id_logado);

// Busca Membros reais via Cérebro
$membros = ChatLogic::getGroupMembers($conn, $conversa_id);

// Lógica de Fallback de Imagem
$avatar_grupo = !empty($grupo['capa_url']) 
    ? $config['base_path'] . $grupo['capa_url'] 
    : $config['base_path'] . 'assets/images/default-group.png';
?>

<div class="sb-group-info-panel">
    <header class="sb-info-header">
        <button class="sb-btn-back-info" onclick="chatAcoes.toggleRightSidebar(false)" title="Voltar para a conversa">
            <i class="fas fa-arrow-left"></i>
        </button>
        <h3>Dados do Grupo</h3>
    </header>

    <div class="sb-info-body">
        <div class="sb-info-content-wrapper">
            
            <div class="sb-info-profile">
                <div class="sb-info-avatar-wrapper">
                    <div class="sb-info-avatar">
                        <img src="<?php echo $avatar_grupo; ?>" 
                             onerror="this.src='<?php echo $config['base_path']; ?>assets/images/default-group.png';">
                    </div>
                </div>
                <h2 class="sb-info-title"><?php echo htmlspecialchars($grupo['titulo']); ?></h2>
                <p class="sb-info-meta">Criada em <?php echo date('d/m/Y', strtotime($grupo['criado_em'])); ?></p>

                <?php if ($e_dono): ?>
                    <button class="sb-info-cta-btn" onclick="chatAcoes.openMemberManagement(<?php echo $conversa_id; ?>)">
                        <i class="fas fa-users-cog"></i> Gerir Comunidade
                    </button>
                <?php endif; ?>
            </div>

            <div class="sb-info-section">
                <div class="sb-section-header">
                    <h4>Participantes (<?php echo count($membros); ?>)</h4>
                </div>
                
                <ul class="sb-info-member-list">
                    <?php foreach ($membros as $m): 
                        $m_avatar = !empty($m['foto_perfil_url']) 
                            ? $config['base_path'] . $m['foto_perfil_url'] 
                            : $config['base_path'] . 'assets/images/default-avatar.png';
                    ?>
                        <li class="sb-info-member-item">
                            <div class="sb-info-m-left">
                                <div class="sb-info-m-avatar-box">
                                    <img src="<?php echo $m_avatar; ?>" class="sb-info-m-avatar"
                                         onerror="this.src='<?php echo $config['base_path']; ?>assets/images/default-avatar.png';">
                                </div>
                                <div class="sb-info-m-details">
                                    <span class="sb-info-m-name"><?php echo htmlspecialchars($m['nome'] . ' ' . $m['sobrenome']); ?></span>
                                    <?php if ($m['eh_dono']): ?>
                                        <span class="sb-info-m-badge"><i class="fas fa-crown"></i> Administrador</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="sb-info-m-actions">
                                <button class="sb-m-action-btn" onclick="window.location.href='<?php echo $config['base_path']; ?>perfil/<?php echo $m['id']; ?>'" title="Ver Perfil">
                                    <i class="fas fa-external-link-alt"></i>
                                </button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        </div> 
    </div>
</div>

<style>
/* Estabilização Estrutural e Motor de Scroll */
.sb-group-info-panel { 
    background: #f9fafb; 
    width: 100%; 
    height: 100%; 
    display: flex; 
    flex-direction: column; 
    overflow: hidden;
    position: relative;
}

/* Cabeçalho Fixo do Componente */
.sb-info-header { 
    padding: 0 30px; 
    border-bottom: 1px solid var(--chat-border, #e5e7eb); 
    display: flex; 
    align-items: center; 
    gap: 20px; 
    background: #ffffff; 
    height: 75px; 
    flex-shrink: 0;
    z-index: 100;
}
.sb-info-header h3 { margin: 0; font-size: 1.1rem; color: #0C2D54; font-weight: 800; }

.sb-btn-back-info { 
    background: rgba(12, 45, 84, 0.05); border: none; width: 40px; height: 40px; 
    border-radius: 50%; color: #0C2D54; cursor: pointer;
    display: flex; align-items: center; justify-content: center; transition: all 0.2s;
}
.sb-btn-back-info:hover { background: #0C2D54; color: #ffffff; transform: translateX(-3px); }

/* Área de Conteúdo Rolável */
.sb-info-body { 
    flex: 1; 
    overflow-y: auto !important; 
    padding-bottom: 40px; 
    -webkit-overflow-scrolling: touch;
}

.sb-info-content-wrapper {
    max-width: 800px; 
    margin: 0 auto 40px; 
    background: #ffffff; 
    border-radius: 0 0 16px 16px; 
    border: 1px solid var(--chat-border, #e5e7eb); 
    border-top: none;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    overflow: hidden;
}

/* Compactação da Identidade do Grupo */
.sb-info-profile { 
    padding: 30px 20px; /* Reduzido de 50px */
    text-align: center; 
    border-bottom: 1px solid var(--chat-border, #e5e7eb); 
}

.sb-info-avatar-wrapper { 
    width: 100px; /* Reduzido de 140px */
    height: 100px; 
    margin: 0 auto 15px; 
}
.sb-info-avatar { 
    width: 100%; height: 100%; border-radius: 50%; overflow: hidden; 
    border: 4px solid #ffffff; box-shadow: 0 6px 15px rgba(0,0,0,0.1); 
}
.sb-info-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }

.sb-info-title { margin: 0; font-size: 1.4rem; color: var(--chat-text, #1f2937); font-weight: 900; }
.sb-info-meta { margin: 5px 0 15px; font-size: 0.8rem; color: var(--chat-text-sub, #6b7280); font-weight: 500; }

.sb-info-cta-btn {
    display: inline-flex; align-items: center; gap: 8px;
    background: #0C2D54; color: #ffffff; border: none;
    padding: 10px 22px; border-radius: 30px; font-weight: 700; font-size: 0.85rem;
    cursor: pointer; transition: all 0.3s ease;
}
.sb-info-cta-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(12, 45, 84, 0.2); }

/* Otimização da Lista de Participantes */
.sb-info-section { padding: 25px 30px; }
.sb-section-header h4 { 
    margin: 0 0 20px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1.2px; 
    color: var(--chat-text-sub, #6b7280); font-weight: 800; border-left: 4px solid #0C2D54; padding-left: 12px;
}

.sb-info-member-list { list-style: none; padding: 0; margin: 0; }
.sb-info-member-item { 
    display: flex; align-items: center; justify-content: space-between; 
    padding: 10px 15px; /* Reduzido de 15px */
    border-radius: 12px; transition: background 0.2s;
    margin-bottom: 4px;
}
.sb-info-member-item:hover { background: #f8fafc; }

.sb-info-m-left { display: flex; align-items: center; gap: 12px; overflow: hidden; }

.sb-info-m-avatar-box {
    width: 38px; /* Reduzido de 45px */
    height: 38px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    background: #eee;
}
.sb-info-m-avatar { width: 100%; height: 100%; object-fit: cover; display: block; }

.sb-info-m-details { display: flex; flex-direction: column; overflow: hidden; }
.sb-info-m-name { font-weight: 700; font-size: 0.9rem; color: var(--chat-text, #1f2937); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sb-info-m-badge { font-size: 0.6rem; color: #f6ad55; font-weight: 800; display: flex; align-items: center; gap: 3px; margin-top: 1px; }

.sb-m-action-btn {
    background: transparent; border: 1px solid var(--chat-border, #e5e7eb);
    width: 32px; height: 32px; border-radius: 8px; color: var(--chat-text-sub, #6b7280);
    cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center;
}
.sb-m-action-btn:hover { background: #0C2D54; color: #ffffff; border-color: #0C2D54; }

/* Correção de Visibilidade Mobile */
@media (max-width: 768px) {
    .sb-group-info-panel { 
        /* Safe space para o cabeçalho fixo do site */
        padding-top: var(--site-header-height, 85px) !important; 
    }
    .sb-info-content-wrapper { margin: 0; border-radius: 0; border: none; }
    .sb-info-header { padding: 0 15px; position: sticky; top: 0; }
    .sb-info-section { padding: 20px 15px; }
}

/* Scrollbar Custom */
.sb-info-body::-webkit-scrollbar { width: 5px; }
.sb-info-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>