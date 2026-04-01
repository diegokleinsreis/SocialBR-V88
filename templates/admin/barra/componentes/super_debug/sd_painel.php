<?php
/**
 * FICHEIRO: super_debug/sd_painel.php
 * PAPEL: Painel de Controlo de Filtros e Gatilhos (Visual HUD Luxo)
 * VERSÃO: 17.0 (Organização Modular & Glass UI)
 * INTEGRIDADE: Completo e Integral - Sem remoções.
 */
?>

<div class="admin-super-debug-group" id="sd-group-container">
    
    <button type="button" 
            onclick="toggleSuperDebug()" 
            id="btn-master-debug" 
            class="btn-super-debug"
            title="Ativar/Desativar Inspetor de Interface (Scanner)">
        <i class="fas fa-microscope"></i> 
        <span class="btn-label">Inspecionar UI</span>
    </button>

    <div class="debug-filters-panel" id="debug-panel-root">
        
        <button type="button" onclick="closeDebugPanel()" class="btn-close-panel" title="Fechar Painel">
            <i class="fas fa-times"></i>
        </button>

        <div class="hub-header">
            <span><i class="fas fa-filter"></i> Filtros de Visão</span>
        </div>
        
        <div class="debug-control-group" style="display: flex; flex-direction: column; gap: 4px; margin-bottom: 20px;">
            
            <label class="hud-filter-label" style="display: flex; align-items: center; gap: 12px; cursor: pointer; font-size: 11px; padding: 6px 8px; border-radius: 6px; transition: background 0.2s;">
                <input type="checkbox" id="chk-hidden" onchange="atualizarVisualizacaoDebug()" style="accent-color: var(--hud-accent);"> 
                <span>🔎 Elementos Ocultos</span>
            </label>
            
            <label class="hud-filter-label" style="display: flex; align-items: center; gap: 12px; cursor: pointer; font-size: 11px; padding: 6px 8px; border-radius: 6px; transition: background 0.2s;">
                <input type="checkbox" id="chk-empty" onchange="atualizarVisualizacaoDebug()" style="accent-color: var(--hud-accent);"> 
                <span>🗑️ Divs Vazias</span>
            </label>
            
            <label class="hud-filter-label" style="display: flex; align-items: center; gap: 12px; cursor: pointer; font-size: 11px; padding: 6px 8px; border-radius: 6px; transition: background 0.2s;">
                <input type="checkbox" id="chk-spacing" onchange="atualizarVisualizacaoDebug()" style="accent-color: var(--hud-accent);"> 
                <span>📏 Margens/Paddings</span>
            </label>
            
            <label class="hud-filter-label" style="display: flex; align-items: center; gap: 12px; cursor: pointer; font-size: 11px; padding: 6px 8px; border-radius: 6px; transition: background 0.2s;">
                <input type="checkbox" id="chk-origin" onchange="atualizarVisualizacaoDebug()" style="accent-color: var(--hud-accent);"> 
                <span>🏷️ Mostrar Origem</span>
            </label>
        </div>

        <div style="font-size: 10px; font-weight: 800; margin-bottom: 12px; color: var(--hud-success); border-top: 1px solid rgba(255,255,255,0.1); padding-top: 18px; display: flex; align-items: center; gap: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
            <i class="fas fa-magic"></i> Inteligência Ativa
        </div>

        <div class="autofix-module" style="background: rgba(46, 204, 113, 0.05); padding: 10px; border-radius: 10px; border: 1px solid rgba(46, 204, 113, 0.1);">
            <button type="button" 
                    onclick="applyAutoFix()" 
                    class="btn-autofix-trigger">
                <i class="fas fa-wrench"></i> EXECUTAR AUTO-FIX
            </button>
            
            <div style="margin-top: 12px; font-size: 9px; color: rgba(255,255,255,0.4); text-align: center; font-style: italic; line-height: 1.4;">
                * O Auto-Fix aplicará correções baseadas nos filtros ativos acima.
            </div>
        </div>
        
    </div>
</div>