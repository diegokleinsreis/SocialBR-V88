<?php
/**
 * admin/suporte/atendimento_chat.php
 * COMPONENTE: Interface de Resposta e Gestão (V1.6)
 * PAPEL: Chat de suporte administrativo com Header Unificado e Botão Voltar Minimalista.
 * AJUSTE: Fusão de controles para ganho de espaço vertical (Morte ao Double Header).
 * VERSÃO: 1.6 - socialbr.lol
 */

// 1. BUSCA DADOS DO CHAMADO E MENSAGENS
$chamado = SuporteLogic::getDetalhesChamado($conn, $chamado_id);
$mensagens = SuporteLogic::getMensagensChamado($conn, $chamado_id);

if (!$chamado) {
    echo "<div class='alert alert-danger'>Chamado não encontrado ou ID inválido.</div>";
    return;
}

$diagnostico = !empty($chamado['diagnostico_json']) ? json_decode($chamado['diagnostico_json'], true) : null;
$foto_user = $chamado['foto_perfil_url'] ? $config['base_path'] . $chamado['foto_perfil_url'] : $config['base_path'] . 'assets/images/default-avatar.png';
?>

<div class="atendimento-chat-wrapper" style="display: flex; flex-direction: column; height: 100%; width: 100%; overflow: hidden; background: #fff;">

    <div class="atendimento-header-tools" style="background: #ffffff; border-bottom: 1px solid #eee; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; gap: 15px; flex-shrink: 0; z-index: 10; min-height: 65px;">
        
        <div style="display: flex; gap: 15px; align-items: center; flex: 1; min-width: 200px;">
            <a href="<?php echo $config['base_path']; ?>admin/suporte" class="btn-back-minimal" title="Voltar para a lista">
                <i class="fas fa-arrow-left"></i>
            </a>

            <img src="<?php echo $foto_user; ?>" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid #eee;">
            
            <div style="overflow: hidden;">
                <h4 style="margin: 0; color: #1c1e21; font-size: 0.9rem; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <?php echo htmlspecialchars($chamado['nome'] . ' ' . $chamado['sobrenome']); ?>
                </h4>
                <div style="display: flex; gap: 6px; align-items: center; margin-top: 1px;">
                    <span style="font-size: 0.6rem; color: #0C2D54; background: #eef3ff; padding: 1px 5px; border-radius: 3px; font-weight: 900;">
                        #<?php echo $chamado['id']; ?>
                    </span>
                    <span style="font-size: 0.7rem; color: #888; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                        <?php echo htmlspecialchars($chamado['categoria']); ?>
                    </span>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 8px; align-items: center;">
            <button type="button" id="btn-refresh-chat" title="Sincronizar" class="btn-tool-circle">
                <i class="fas fa-sync-alt" id="icon-refresh"></i>
            </button>

            <button type="button" id="btn-toggle-diagnostico" class="btn-diag-trigger-minimal">
                <i class="fas fa-microchip"></i> <span>DADOS</span>
            </button>

            <div style="min-width: 130px;">
                <select id="admin-status-select" class="admin-input-select-compact">
                    <option value="aberto" <?php echo $chamado['status'] == 'aberto' ? 'selected' : ''; ?>>ABERTO</option>
                    <option value="em_andamento" <?php echo $chamado['status'] == 'em_andamento' ? 'selected' : ''; ?>>EM ATEND.</option>
                    <option value="resolvido" <?php echo $chamado['status'] == 'resolvido' ? 'selected' : ''; ?>>RESOLVIDO</option>
                </select>
            </div>
        </div>
    </div>

    <div id="gaveta-diagnostico" style="display: none; background: #fafbfc; border-bottom: 1px solid #eee; padding: 12px 20px; flex-shrink: 0;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div class="diag-item">
                <label style="display:block; font-size: 0.55rem; color: #aaa; font-weight: 900; text-transform: uppercase; margin-bottom: 2px;">Origem</label>
                <a href="<?php echo htmlspecialchars($diagnostico['url'] ?? '#'); ?>" target="_blank" style="font-size: 0.75rem; color: #0C2D54; text-decoration: none; word-break: break-all; font-weight: 600;">
                    <?php echo htmlspecialchars($diagnostico['url'] ?? 'N/A'); ?> <i class="fas fa-external-link-alt" style="font-size: 0.6rem;"></i>
                </a>
            </div>
            <div class="diag-item">
                <label style="display:block; font-size: 0.55rem; color: #aaa; font-weight: 900; text-transform: uppercase; margin-bottom: 2px;">Ambiente</label>
                <span style="font-size: 0.75rem; font-weight: 700; color: #555;"><?php echo htmlspecialchars(($diagnostico['res'] ?? 'N/A') . ' • ' . ($diagnostico['browser'] ?? 'N/A')); ?></span>
            </div>
        </div>
    </div>

    <div id="admin-chat-box" style="flex: 1; overflow-y: auto; background: #f0f2f5; padding: 20px; display: flex; flex-direction: column; gap: 12px;">
        <?php foreach ($mensagens as $msg): ?>
            <?php 
                $sou_eu = ($msg['remetente_tipo'] === 'admin');
                $alinhamento = $sou_eu ? 'flex-end' : 'flex-start';
                $cor_fundo = $sou_eu ? '#0C2D54' : '#ffffff';
                $cor_texto = $sou_eu ? '#ffffff' : '#1c1e21';
                $radius = $sou_eu ? '14px 14px 2px 14px' : '14px 14px 14px 2px';
            ?>
            <div style="display: flex; flex-direction: column; align-items: <?php echo $alinhamento; ?>; max-width: 85%; align-self: <?php echo $alinhamento; ?>;">
                <div style="background: <?php echo $cor_fundo; ?>; color: <?php echo $cor_texto; ?>; padding: 10px 14px; border-radius: <?php echo $radius; ?>; box-shadow: 0 1px 2px rgba(0,0,0,0.08); border: <?php echo $sou_eu ? 'none' : '1px solid #dddfe2'; ?>;">
                    <p style="margin: 0; white-space: pre-wrap; font-size: 0.88rem; line-height: 1.4;"><?php echo htmlspecialchars($msg['mensagem']); ?></p>
                    
                    <?php if (!empty($msg['foto_url'])): ?>
                        <div style="margin-top: 8px;" class="msg-foto">
                            <img src="<?php echo $config['base_path'] . $msg['foto_url']; ?>" 
                                 class="suporte-msg-foto" 
                                 style="max-width: 100%; max-height: 180px; width: auto; border-radius: 6px; display: block; cursor: pointer; transition: opacity 0.2s;"
                                 onmouseover="this.style.opacity='0.9'" 
                                 onmouseout="this.style.opacity='1'">
                        </div>
                    <?php endif; ?>
                </div>
                <span style="font-size: 0.6rem; color: #90949c; margin-top: 4px; font-weight: 600; padding: 0 4px;">
                    <?php echo $sou_eu ? 'Você' : 'Utilizador'; ?> • <?php echo date('H:i', strtotime($msg['data_envio'])); ?>
                </span>
            </div>
        <?php endforeach; ?>
        <div id="chat-anchor"></div>
    </div>

    <div class="admin-reply-form" style="background: #fff; border-top: 1px solid #eee; padding: 12px 20px; flex-shrink: 0;">
        <form id="admin-form-resposta" enctype="multipart/form-data">
            <input type="hidden" name="chamado_id" value="<?php echo $chamado['id']; ?>">
            
            <div style="position: relative; margin-bottom: 10px;">
                <textarea name="mensagem" id="admin_resposta_mensagem" rows="2" 
                          placeholder="Digite a resposta... (Ctrl+V para prints)" 
                          style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 0.85rem; resize: none; font-family: inherit; outline: none; transition: border 0.2s;" required></textarea>
                
                <div id="thumb-container-admin" style="display: none; position: absolute; bottom: 100%; left: 0; margin-bottom: 10px; align-items: center; gap: 8px; background: #fff; padding: 6px; border-radius: 8px; border: 1px solid #ddd; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <img id="img-preview-admin" src="#" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                    <span style="font-size: 0.65rem; color: #2ecc71; font-weight: 800;">✓ ANEXADA</span>
                </div>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="position: relative;">
                    <button type="button" class="btn-admin-secondary-compact">
                        <i class="fas fa-paperclip"></i> Anexar
                    </button>
                    <input type="file" name="foto_suporte" id="foto_suporte_admin" accept="image/*" style="position: absolute; left: 0; top: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;">
                </div>

                <button type="submit" class="btn-admin-send-compact" id="btn-admin-enviar">
                    ENVIAR <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* 1. COMPONENTES MINIMALISTAS */
.btn-back-minimal {
    width: 32px; height: 32px; background: #f0f2f5; color: #0C2D54;
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    text-decoration: none; transition: all 0.2s; font-size: 0.85rem;
}
.btn-back-minimal:hover { background: #0C2D54; color: #fff; transform: translateX(-3px); }

.btn-tool-circle {
    background: #fff; border: 1px solid #ddd; color: #0C2D54; width: 32px; height: 32px;
    border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;
}
.btn-tool-circle:hover { background: #f0f2f5; border-color: #0C2D54; }

.btn-diag-trigger-minimal {
    background: #f0f2f5; border: 1px solid #ddd; padding: 6px 12px; border-radius: 6px;
    cursor: pointer; font-size: 0.65rem; font-weight: 800; color: #4b4f56; display: flex; align-items: center; gap: 6px;
}
.btn-diag-trigger-minimal:hover { background: #e4e6eb; color: #0C2D54; }

.admin-input-select-compact {
    background: #0C2D54; color: #fff; border: none; font-weight: 800; width: 100%; height: 32px;
    border-radius: 6px; cursor: pointer; font-size: 0.65rem; padding: 0 8px;
}

/* 2. BOTÕES DE FORMULÁRIO COMPACTOS */
.btn-admin-secondary-compact {
    background: #f0f2f5; border: 1px solid #ddd; padding: 6px 15px; border-radius: 6px;
    font-size: 0.7rem; font-weight: 800; color: #1c1e21; cursor: pointer;
}

.btn-admin-send-compact {
    background: #0C2D54; color: #fff; border: none; padding: 8px 25px; border-radius: 6px;
    font-weight: 800; font-size: 0.8rem; cursor: pointer; display: flex; align-items: center; gap: 8px;
}

/* 3. MOTOR DE SCROLL */
#admin-chat-box::-webkit-scrollbar { width: 5px; }
#admin-chat-box::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
</style>