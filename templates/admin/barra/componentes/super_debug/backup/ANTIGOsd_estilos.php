<?php
/**
 * FICHEIRO: super_debug/sd_estilos.php
 * PAPEL: Sistema de Design do Super Debug (Glass UI)
 * VERSÃO: 19.7 (Correção: Visibilidade de Ocultos & Origem)
 * INTEGRIDADE: Completo e Integral - Blindagem HUD de Luxo.
 */
?>
<style>
/* 0. SISTEMA DE DESIGN UNIFICADO (V19.7 - Glass Moderation Edition) 
   Foco: Efeito Vidro Fosco, Scanner Reativo e Hub de Auditoria de Denúncias.
*/
:root {
    --sd-z-index: 1000000;
    --sd-bg-glass: rgba(15, 15, 15, 0.85);
    --sd-border: rgba(255, 255, 255, 0.15);
    --sd-accent: #f1c40f;
    --sd-success: #2ecc71;
    --sd-danger: #e74c3c;
    --sd-radius: 12px;
    --sd-shadow: 0 20px 60px rgba(0,0,0,0.9);
    --sd-transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

/* 1. ESTRUTURA DE GRUPOS (ALINHAMENTO HUD) */
.admin-atom-group {
    display: flex;
    align-items: center;
    gap: 8px;
    transition: var(--sd-transition);
}

/* 2. PADRONIZAÇÃO DE BOTÕES E ÍCONES (HUD PURE) */
.btn-super-debug, .btn-admin-atalho, .metrica-box, 
.tag-denuncia {
    height: 32px;
    width: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.07);
    border: 1px solid var(--sd-border);
    color: #fff;
    font-size: 14px;
    cursor: pointer;
    transition: var(--sd-transition);
    text-decoration: none;
    position: relative;
}

/* Esconde textos por padrão para manter o modo HUD sempre ativo */
.btn-label, .btn-super-debug span:not(.fas), .metrica-box span {
    display: none !important;
}

.btn-super-debug:hover, .btn-admin-atalho:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.3);
}

/* 3. PERFORMANCE HUB (GATILHO VERDE) */
.mobile-hub-trigger {
    display: flex !important;
    background: rgba(46, 204, 113, 0.15) !important;
    border-color: var(--sd-success) !important;
    color: var(--sd-success) !important;
}

.admin-perf-group .metrica-box:not(.mobile-hub-trigger) {
    display: none !important;
}

/* 4. SELETOR DE VISÃO (DROPDOWN ESCURO) */
.admin-visao-wrapper {
    height: 32px;
    display: flex;
    align-items: center;
    background: transparent;
    border: none;
    gap: 5px;
}

.admin-id-badge, .admin-label-visao { display: none !important; }

.admin-select-custom {
    background: #222 !important;
    color: #fff !important;
    border: 1px solid var(--sd-border);
    border-radius: 4px;
    font-size: 11px;
    height: 26px;
    padding: 0 5px;
    cursor: pointer;
    max-width: 120px;
    outline: none;
}

.admin-select-custom option { background: #1a1a1a !important; color: #fff !important; }

/* 5. MODAIS E FLYOUTS (GLASSMORPHISM) */
.metrics-hub-panel, .debug-filters-panel, .moderation-hub-panel {
    display: none;
    position: fixed;
    top: 60px;
    background: var(--sd-bg-glass);
    backdrop-filter: blur(25px);
    -webkit-backdrop-filter: blur(25px);
    border: 1px solid var(--sd-border);
    border-radius: var(--sd-radius);
    padding: 20px;
    z-index: var(--sd-z-index);
    box-shadow: var(--sd-shadow);
    color: #fff;
    animation: hubFadeIn 0.3s ease-out;
}

.metrics-hub-panel { right: 20px; width: 240px; }
.debug-filters-panel { left: 20px; width: 260px; }
.moderation-hub-panel { right: 20px; width: 300px; max-height: 80vh; overflow: hidden; display: flex; flex-direction: column; }

/* 6. ESTILOS ESPECÍFICOS DO HUB DE MODERAÇÃO */
.hub-status-alert {
    background: rgba(192, 57, 43, 0.2);
    border-left: 4px solid var(--sd-danger);
    padding: 10px;
    border-radius: 4px;
    font-size: 11px;
    margin-bottom: 15px;
}

.hub-list-scroll {
    overflow-y: auto;
    max-height: 300px;
    padding-right: 5px;
}

.hub-list-scroll::-webkit-scrollbar { width: 4px; }
.hub-list-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }

.hub-row-moderation {
    padding: 12px 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.mod-date {
    font-size: 9px;
    color: #888;
    margin-bottom: 4px;
}

.mod-reason {
    font-size: 11px;
    line-height: 1.4;
    color: #eee;
    font-style: italic;
}

.hub-row-empty {
    color: #666;
    font-size: 11px;
    font-style: italic;
    text-align: center;
}

/* 7. COMPONENTES DE PAINEL (CABEÇALHOS E FILTROS) */
.debug-control-group label {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    font-size: 11px;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    transition: color 0.2s;
    user-select: none;
}

.debug-control-group label:hover { color: var(--sd-accent); }
.debug-control-group input { accent-color: var(--sd-accent); cursor: pointer; }

.hub-header {
    border-bottom: 1px solid var(--sd-border);
    padding-bottom: 10px;
    margin-bottom: 15px;
    font-size: 10px;
    text-transform: uppercase;
    color: var(--sd-accent);
    font-weight: 800;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* 8. BOTÃO EXECUTAR AUTO-FIX */
.btn-autofix-trigger {
    width: 100% !important;
    background: var(--sd-success) !important;
    color: #fff !important;
    padding: 12px !important;
    border: none !important;
    border-radius: 8px !important;
    font-weight: 800 !important;
    font-size: 10px !important;
    cursor: pointer !important;
    margin-top: 15px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
}

/* 9. ETIQUETAS DO SCANNER */
.debug-label-tag {
    display: none;
    position: absolute;
    font-size: 9px;
    padding: 2px 6px;
    border-radius: 4px;
    z-index: 999999;
    color: #fff;
    pointer-events: none;
    font-family: 'Fira Code', monospace;
    white-space: nowrap;
}

/* REATIVIDADE VISUAL DOS FILTROS */
body.show-hidden .tag-hidden { display: block !important; background: #e67e22; }
body.show-empty .tag-empty { display: block !important; background: var(--sd-danger); }
body.show-spacing .tag-margin { display: block !important; background: #9b59b6; }
body.show-spacing .tag-padding { display: block !important; background: #3498db; }

/* 🔵 CORREÇÃO: MOSTRAR ORIGEM */
body.show-origin .tag-origin { 
    display: block !important; 
    background: #444 !important; 
    border: 1px solid rgba(255,255,255,0.2) !important; 
}

/* BORDAS DE DIAGNÓSTICO */
body.show-hidden .debug-is-hidden { outline: 2px solid #e67e22 !important; outline-offset: -2px; }
body.show-empty .debug-is-empty { outline: 2px solid var(--sd-danger) !important; outline-offset: -2px; }
body.show-spacing .debug-gap-causer { outline: 1px dashed var(--sd-success) !important; }

/* 🔴 FIX CRÍTICO: REVELAR ELEMENTOS COM DISPLAY:NONE */
body.debug-master-mode.show-hidden .debug-is-hidden { 
    display: block !important; 
    visibility: visible !important; 
    opacity: 0.5 !important; 
    outline: 2px dashed #e67e22 !important;
    min-height: 20px; 
    min-width: 20px;
}

/* 10. ANIMAÇÕES E ESTADOS */
@keyframes hubFadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.btn-debug-on { background: var(--sd-danger) !important; border-color: #fff; box-shadow: 0 0 15px var(--sd-danger); }
.admin-mod-danger { background: var(--sd-danger) !important; animation: pulse 2s infinite; }
.btn-close-panel { cursor: pointer; color: #888; transition: color 0.2s; background: none; border: none; }
.btn-close-panel:hover { color: #fff; }

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.6; }
    100% { opacity: 1; }
}
</style>