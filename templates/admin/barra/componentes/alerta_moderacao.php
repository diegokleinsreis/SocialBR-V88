<?php
/**
 * FICHEIRO: componentes/alerta_moderacao.php
 * PAPEL: Estação de Comando de Moderação (Visual HUD Luxo)
 * VERSÃO: 23.6 (Fixed dvh Bottom & Master Neon Pulse)
 * INTEGRIDADE: Completo e Integral.
 */

// Sincronia com a Omni-Query
$total_denuncias = $mod_data['num_denuncias'] ?? 0;
$motivos = $mod_data['lista_motivos'] ?? [];
$id_perfil = $mod_data['id_perfil'] ?? 0;

// Estado Crítico: Classe de Alarme Master
$classe_alerta = ($total_denuncias > 0) ? 'hud-master-pulse' : 'hud-btn-safe';
?>

<div class="admin-mod-container">
    
    <div class="tag-denuncia <?php echo $classe_alerta; ?>" 
         onclick="toggleModerationHub()"
         style="cursor: pointer; position: relative; z-index: 1000005 !important;"
         title="<?php echo ($total_denuncias > 0) ? '🚨 EMERGÊNCIA: ' . $total_denuncias . ' denúncias!' : 'Perfil Limpo'; ?>">
        
        <i class="fas fa-exclamation-triangle"></i> 
        <span class="btn-label" style="margin-left: 6px; font-weight: 900; display: inline !important;">
            <?php echo $total_denuncias; ?>
        </span>
    </div>

    <div class="moderation-hub-panel" id="moderation-hub-root" style="display: none; flex-direction: column;">
        
        <div class="hub-header" style="flex-shrink: 0;">
            <span><i class="fas fa-gavel"></i> Auditoria de Denúncias</span>
            <button type="button" onclick="toggleModerationHub()" class="btn-close-panel">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="hub-content-wrapper" style="display: flex; flex-direction: column; flex-grow: 1; overflow: hidden; min-height: 0;">
            <?php if ($total_denuncias > 0): ?>
                
                <div class="hub-status-alert" style="background: rgba(231, 76, 60, 0.2); border-left: 4px solid #ff3131; padding: 10px; border-radius: 6px; margin-bottom: 10px; flex-shrink: 0;">
                    <span style="font-size: 11px; color: #fff; font-weight: 700;">
                        <i class="fas fa-biohazard fa-beat" style="color: #ff3131; margin-right: 5px;"></i>
                        Fila de Moderador: <?php echo $total_denuncias; ?> itens.
                    </span>
                </div>
                
                <div class="hub-list-scroll" style="flex-grow: 1; overflow-y: auto; padding-right: 5px;">
                    <?php foreach ($motivos as $denuncia): 
                        $tipo = $denuncia['tipo_conteudo'] ?? 'usuario';
                        $mapa = [
                            'post' => ['label' => 'POSTAGEM', 'icon' => 'fa-file-alt', 'color' => '#3498db'],
                            'comentario' => ['label' => 'COMENTÁRIO', 'icon' => 'fa-comments', 'color' => '#9b59b6'],
                            'usuario' => ['label' => 'PERFIL', 'icon' => 'fa-user-tag', 'color' => '#e67e22']
                        ];
                        $meta = $mapa[$tipo] ?? $mapa['usuario'];
                    ?>
                        <div class="hub-row-moderation" style="display: flex; gap: 10px; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05); align-items: flex-start;">
                            <div style="background: <?php echo $meta['color']; ?>; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 0 10px <?php echo $meta['color']; ?>44;">
                                <i class="fas <?php echo $meta['icon']; ?>" style="color: #fff; font-size: 12px;"></i>
                            </div>

                            <div style="flex-grow: 1;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                                    <span style="font-size: 9px; font-weight: 900; color: <?php echo $meta['color']; ?>;"><?php echo $meta['label']; ?></span>
                                    <span style="font-size: 8px; color: rgba(255,255,255,0.3);"><?php echo date('d/m H:i', strtotime($denuncia['data_denuncia'])); ?></span>
                                </div>
                                <div style="font-size: 11px; color: #fff; line-height: 1.3;">"<?php echo htmlspecialchars($denuncia['motivo']); ?>"</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mod-action-footer" style="flex-shrink: 0; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.1);">
                    <a href="/~klscom/admin/admin_denuncias.php?tab=usuarios&busca_usuario=<?php echo $id_perfil; ?>" 
                       class="btn-autofix-trigger" style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; box-sizing: border-box; background: #2ecc71 !important; height: 40px !important; border-radius: 8px !important;">
                        <i class="fas fa-external-link-alt"></i> GESTÃO DE MODERAÇÃO
                    </a>
                </div>

            <?php else: ?>
                <div class="hub-empty-state" style="text-align: center; padding: 40px 10px; flex-grow: 1;">
                    <i class="fas fa-check-circle" style="color: #2ecc71; font-size: 40px; margin-bottom: 15px;"></i>
                    <strong style="display: block; color: #fff;">ESTADO: SEGURO</strong>
                    <p style="font-size: 10px; color: rgba(255,255,255,0.4);">Sem denúncias para este usuário.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    /* 1. MASTER NEON PULSE (A CORREÇÃO DO "PISCAR") */
    @keyframes masterNeonPulse {
        0% { background-color: #c0392b !important; box-shadow: 0 0 0px #ff3131; filter: brightness(1); }
        50% { background-color: #ff3131 !important; box-shadow: 0 0 20px #ff3131; filter: brightness(1.3); }
        100% { background-color: #c0392b !important; box-shadow: 0 0 0px #ff3131; filter: brightness(1); }
    }

    .hud-master-pulse {
        animation: masterNeonPulse 1s infinite alternate ease-in-out !important;
        border-color: #fff !important;
        color: #fff !important;
    }

    /* 2. LAYOUT DVH SEGURO PARA DISPOSITIVOS PEQUENOS */
    .moderation-hub-panel {
        width: 350px !important;
        max-width: calc(100vw - 40px) !important;
        top: 60px !important;
        /* MATEMÁTICA: Altura total menos o topo e margem de segurança */
        max-height: calc(100dvh - 80px) !important; 
        right: 20px !important;
        display: none;
        z-index: 1000010 !important;
    }

    .hub-list-scroll::-webkit-scrollbar { width: 3px; }
    .hub-list-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }

    /* RESET DE BOTÃO PARA GARANTIR VISIBILIDADE */
    .btn-autofix-trigger {
        color: #fff !important;
        font-weight: 800 !important;
        font-size: 11px !important;
        box-shadow: 0 4px 15px rgba(46, 204, 113, 0.4) !important;
        transition: transform 0.2s !important;
    }
    .btn-autofix-trigger:hover { transform: translateY(-2px); }
</style>