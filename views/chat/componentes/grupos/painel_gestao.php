<?php
/**
 * views/chat/componentes/grupos/painel_gestao.php
 * Sub-componente Especialista: O Trono do Administrador.
 * PAPEL: Gerenciar membros, expulsar, promover e gerir a sucessão.
 * VERSÃO: V66.2 (Correção de Visibilidade Mobile e Scroll - socialbr.lol)
 */

if (session_status() == PHP_SESSION_NONE) { session_start(); }

// 1. Dependências de Sistema
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../src/ChatLogic.php';

$conversa_id = (int)($_GET['conversa_id'] ?? 0);
$user_id_logado = $_SESSION['user_id'] ?? 0;

if ($conversa_id <= 0 || !$user_id_logado) exit("Acesso negado.");

/**
 * 2. VALIDAÇÃO DE AUTORIDADE (DNA CheckYou)
 */
if (!ChatLogic::isGroupOwner($conn, $conversa_id, $user_id_logado)) {
    exit("<p style='padding:20px; color:red;'>Acesso restrito ao administrador do grupo.</p>");
}

$grupo = ChatLogic::getGroupDetails($conn, $conversa_id);
$membros = ChatLogic::getGroupMembers($conn, $conversa_id);

$avatar_grupo = !empty($grupo['capa_url']) 
    ? $config['base_path'] . $grupo['capa_url'] 
    : $config['base_path'] . 'assets/images/default-group.png';
?>

<div class="sb-admin-panel">
    <header class="sb-info-header">
        <button class="sb-btn-back-info" onclick="chatAcoes.openGroupInfo(<?php echo $conversa_id; ?>)" title="Voltar para Detalhes">
            <i class="fas fa-arrow-left"></i>
        </button>
        <h3>Gestão da Comunidade</h3>
    </header>

    <div class="sb-info-body">
        <div class="sb-info-content-wrapper">
            
            <div class="sb-admin-summary">
                <div class="sb-admin-badge">
                    <i class="fas fa-crown"></i> Painel do Proprietário
                </div>
                <h2 class="sb-info-title"><?php echo htmlspecialchars($grupo['titulo']); ?></h2>
                <p class="sb-info-meta">Você tem controle total sobre esta comunidade.</p>
            </div>

            <div class="sb-info-section">
                <div class="sb-section-header">
                    <h4>Membros Atuais (<?php echo count($membros); ?>)</h4>
                </div>
                
                <ul class="sb-admin-member-list">
                    <?php foreach ($membros as $m): 
                        $m_avatar = !empty($m['foto_perfil_url']) 
                            ? $config['base_path'] . $m['foto_perfil_url'] 
                            : $config['base_path'] . 'assets/images/default-avatar.png';
                        $is_self = ((int)$m['id'] === (int)$user_id_logado);
                    ?>
                        <li class="sb-admin-member-item <?php echo $is_self ? 'is-me' : ''; ?>">
                            <div class="sb-info-m-left">
                                <div class="sb-info-m-avatar">
                                    <img src="<?php echo $m_avatar; ?>" 
                                         onerror="this.src='<?php echo $config['base_path']; ?>assets/images/default-avatar.png';">
                                </div>
                                <div class="sb-info-m-details">
                                    <span class="sb-info-m-name"><?php echo htmlspecialchars($m['nome'] . ' ' . $m['sobrenome']); ?></span>
                                    <?php if ($m['eh_dono']): ?>
                                        <span class="sb-info-m-badge"><i class="fas fa-crown"></i> Dono do Grupo</span>
                                    <?php else: ?>
                                        <span class="sb-info-m-status">Membro</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="sb-admin-m-actions">
                                <?php if (!$m['eh_dono']): ?>
                                    <button class="sb-admin-btn btn-promote" 
                                            onclick="chatAcoes.gerenciarMembro(<?php echo $conversa_id; ?>, <?php echo $m['id']; ?>, 'promover')" 
                                            title="Transferir Propriedade">
                                        <i class="fas fa-angle-double-up"></i>
                                    </button>
                                    <button class="sb-admin-btn btn-kick" 
                                            onclick="chatAcoes.gerenciarMembro(<?php echo $conversa_id; ?>, <?php echo $m['id']; ?>, 'remover')" 
                                            title="Expulsar do Grupo">
                                        <i class="fas fa-user-times"></i>
                                    </button>
                                <?php else: ?>
                                    <span class="sb-admin-self-tag">Você</span>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="sb-admin-danger-zone">
                <div class="sb-section-header">
                    <h4 class="text-danger">Zona de Perigo</h4>
                </div>
                <p class="sb-danger-text">Para sair do grupo, você deve transferir a propriedade para outro membro primeiro.</p>
                <button class="sb-btn-exit-group disabled" title="Ação bloqueada para o dono">
                    <i class="fas fa-sign-out-alt"></i> Sair do Grupo
                </button>
            </div>

        </div>
    </div>
</div>

<style>
/* 1. ESTABILIZAÇÃO ESTRUTURAL (Fix para Imagens e Scroll) */
.sb-admin-panel { 
    background: #f9fafb; 
    width: 100%; 
    height: 100%; /* Essencial para o scroll interno funcionar */
    display: flex; 
    flex-direction: column; 
    overflow: hidden; 
    position: relative;
}

/* 2. CABEÇALHO DO COMPONENTE */
.sb-info-header { 
    padding: 0 30px; 
    border-bottom: 1px solid var(--chat-border, #e5e7eb); 
    display: flex; 
    align-items: center; 
    gap: 20px; 
    background: #ffffff; 
    height: 75px; 
    flex-shrink: 0; /* Impede que o cabeçalho seja esmagado */
    z-index: 10;
}
.sb-info-header h3 { margin: 0; font-size: 1.1rem; color: #0C2D54; font-weight: 800; }

.sb-btn-back-info { 
    background: rgba(12, 45, 84, 0.05); 
    border: none; 
    width: 40px; 
    height: 40px; 
    min-width: 40px;
    border-radius: 50%; 
    color: #0C2D54; 
    cursor: pointer;
    display: flex; 
    align-items: center; 
    justify-content: center; 
    transition: all 0.2s;
}
.sb-btn-back-info:hover { background: #0C2D54; color: #fff; transform: translateX(-3px); }

/* 3. CORPO E MOTOR DE SCROLL */
.sb-info-body { 
    flex: 1; 
    overflow-y: auto !important; /* Habilita o scroll */
    padding-bottom: 40px; 
    -webkit-overflow-scrolling: touch; /* Suavidade no iOS */
}

.sb-info-content-wrapper {
    max-width: 800px; 
    margin: 0 auto 40px; 
    background: #ffffff; 
    border-radius: 0 0 16px 16px; 
    border: 1px solid var(--chat-border, #e5e7eb); 
    border-top: none;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

/* 4. RESUMO ADMINISTRATIVO */
.sb-admin-summary { padding: 40px 30px; text-align: center; border-bottom: 1px solid var(--chat-border, #e5e7eb); background: #fff; }
.sb-admin-badge { 
    display: inline-flex; align-items: center; gap: 8px; 
    background: #0C2D54; color: #fff; padding: 6px 15px; 
    border-radius: 20px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; margin-bottom: 15px;
}
.sb-info-title { margin: 0; font-size: 1.6rem; color: var(--chat-text, #1f2937); font-weight: 900; }
.sb-info-meta { margin: 8px 0 0; font-size: 0.85rem; color: var(--chat-text-sub, #6b7280); }

/* 5. LISTA DE MEMBROS E AVATARES (FIX IMAGENS GIGANTES) */
.sb-info-section { padding: 30px; }
.sb-section-header h4 { 
    margin: 0 0 20px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1.2px; 
    color: var(--chat-text-sub, #6b7280); font-weight: 800; border-left: 4px solid #0C2D54; padding-left: 12px;
}

.sb-admin-member-list { list-style: none; padding: 0; margin: 0; }
.sb-admin-member-item { 
    display: flex; align-items: center; justify-content: space-between; 
    padding: 15px 20px; border-bottom: 1px solid #f1f5f9; transition: all 0.2s;
}
.sb-admin-member-item:hover { background: #f8fafc; }

.sb-info-m-left { display: flex; align-items: center; gap: 15px; overflow: hidden; }

/* Contenção Rígida Atômica para os Avatares */
.sb-info-m-avatar { 
    width: 48px; 
    height: 48px; 
    min-width: 48px; /* Impede o achatamento */
    min-height: 48px; 
    border-radius: 50%; 
    overflow: hidden; 
    flex-shrink: 0; /* Vital para manter o tamanho no flexbox */
    border: 2px solid #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.sb-info-m-avatar img { 
    width: 100% !important; 
    height: 100% !important; 
    object-fit: cover !important; 
    display: block;
}

.sb-info-m-details { display: flex; flex-direction: column; overflow: hidden; }
.sb-info-m-name { font-weight: 700; font-size: 0.95rem; color: var(--chat-text, #1f2937); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sb-info-m-badge { font-size: 0.65rem; color: #f6ad55; font-weight: 800; display: flex; align-items: center; gap: 4px; margin-top: 2px; }
.sb-info-m-status { font-size: 0.7rem; color: #94a3b8; font-weight: 600; }

/* 6. AÇÕES ADMINISTRATIVAS */
.sb-admin-m-actions { display: flex; gap: 8px; flex-shrink: 0; }
.sb-admin-btn {
    width: 34px; height: 34px; border-radius: 8px; border: 1px solid #e2e8f0;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all 0.2s; color: #64748b; background: #ffffff;
}
.btn-promote:hover { background: #0C2D54; color: #ffffff; border-color: #0C2D54; }
.btn-kick:hover { background: #ef4444; color: #ffffff; border-color: #ef4444; }
.sb-admin-self-tag { font-size: 0.65rem; font-weight: 800; color: #94a3b8; background: #f1f5f9; padding: 3px 8px; border-radius: 4px; }

/* 7. ZONA DE PERIGO */
.sb-admin-danger-zone { padding: 30px; border-top: 4px solid #f1f5f9; background: #fffafb; }
.sb-danger-text { font-size: 0.8rem; color: #64748b; margin-bottom: 15px; }
.sb-btn-exit-group {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 20px; border-radius: 8px; border: 1px solid #e2e8f0;
    font-weight: 700; color: #94a3b8; background: #f8fafc; cursor: not-allowed; font-size: 0.85rem;
}

/* 8. RESPONSIVIDADE MOBILE (FIX BOTÃO VOLTAR SUMINDO) */
@media (max-width: 768px) {
    .sb-admin-panel { 
        /* Empurra o painel para baixo do cabeçalho fixo do site */
        padding-top: var(--site-header-height, 85px) !important; 
        height: 100vh;
    }
    .sb-info-content-wrapper { margin: 0; border-radius: 0; border: none; }
    .sb-info-header { 
        padding: 0 15px; 
        position: sticky; /* Mantém o cabeçalho visível ao rolar a lista */
        top: 0;
    }
    .sb-admin-member-item { padding: 15px; }
}

/* Custom Scrollbar */
.sb-info-body::-webkit-scrollbar { width: 6px; }
.sb-info-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>