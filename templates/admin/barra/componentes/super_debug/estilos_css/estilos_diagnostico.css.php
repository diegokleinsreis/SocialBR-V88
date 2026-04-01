<?php
/**
 * FICHEIRO: estilos_css/estilos_diagnostico.css.php
 * PAPEL: Estética Glass para o Scanner de UI (Inspector UI)
 * VERSÃO: 2.4 (Ghost Vision Edition - Sem quebra de layout)
 * RESPONSABILIDADE: Revelar o oculto sem destruir a estrutura do site.
 * INTEGRIDADE: Completo e Integral.
 */
?>
<style>
/* 1. GRUPO DO SCANNER (ALINHAMENTO BLINDADO) */
.admin-super-debug-group {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    gap: 8px !important;
}

/* 2. PAINEL DE FILTROS (DIAGNÓSTICO) - GLASS FUMÊ */
.debug-filters-panel {
    display: none;
    position: fixed;
    top: 60px;
    left: 20px;
    width: 280px;
    background: var(--hud-glass-bg) !important;
    border-radius: 16px !important;
    box-shadow: var(--hud-glass-shadow) !important;
    backdrop-filter: var(--hud-glass-blur) !important;
    -webkit-backdrop-filter: var(--hud-glass-blur) !important;
    border: 1px solid var(--hud-glass-border) !important;
    padding: 20px !important;
    z-index: 1000000;
    color: var(--hud-text) !important;
    animation: hudSlideDown 0.3s ease-out;
}

/* 3. BOTÃO EXECUTAR AUTO-FIX */
.btn-autofix-trigger {
    width: 100% !important;
    background: var(--hud-success) !important;
    color: #fff !important;
    padding: 12px !important;
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
    border-radius: 8px !important;
    font-weight: 800 !important;
    font-size: 10px !important;
    text-transform: uppercase !important;
    letter-spacing: 1px !important;
    cursor: pointer !important;
    margin-top: 15px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3) !important;
    transition: var(--hud-transition) !important;
}

/* 4. ETIQUETAS DO SCANNER (TAGS) - GHOST STYLE */
.debug-label-tag {
    display: none;
    position: absolute;
    font-size: 8px !important;
    padding: 1px 4px !important;
    border-radius: 3px !important;
    z-index: 1000001 !important;
    color: #fff !important;
    pointer-events: none !important;
    font-family: 'Fira Code', monospace !important;
    white-space: nowrap !important;
    box-shadow: 0 2px 5px rgba(0,0,0,0.5) !important;
    opacity: 0.85;
}

/* REATIVIDADE VISUAL DOS FILTROS */
body.show-hidden .tag-hidden { display: block !important; background: #e67e22 !important; }
body.show-empty .tag-empty { display: block !important; background: var(--hud-danger) !important; }
body.show-spacing .tag-margin { display: block !important; background: #9b59b6 !important; }
body.show-spacing .tag-padding { display: block !important; background: #3498db !important; }
body.show-origin .tag-origin { 
    display: block !important; 
    background: rgba(0,0,0,0.8) !important; 
    border: 1px solid rgba(255,255,255,0.2) !important; 
}

/* 5. BORDAS DE DIAGNÓSTICO */
body.show-hidden .debug-is-hidden { outline: 1px solid #e67e22 !important; outline-offset: -1px !important; }
body.show-empty .debug-is-empty { outline: 2px solid var(--hud-danger) !important; }
body.show-spacing .debug-gap-causer { outline: 1px dashed var(--hud-success) !important; }

/* 6. REVELAÇÃO "GHOST" (A GRANDE MUDANÇA) */
/* Agora os elementos ocultos aparecem sem volume, sem empurrar o site */
body.debug-master-mode.show-hidden .debug-is-hidden { 
    display: inline-block !important; /* Não quebra a linha do site */
    visibility: visible !important; 
    opacity: 0.2 !important; /* Muito sutil, como um fantasma */
    outline: 1px dashed #e67e22 !important;
    min-height: 2px !important; /* O mínimo possível para ser detectado */
    min-width: 2px !important;
    pointer-events: none !important; /* Você clica "através" dele nos botões reais */
}

/* 7. ETIQUETA DE AUTO-FIX */
.tag-fixed { background: var(--hud-success) !important; border: 1px solid #fff !important; display: block !important; }

/* 8. BOTÃO DE FECHAR PAINEL */
.btn-close-panel {
    position: absolute;
    top: 10px;
    right: 10px;
    background: none;
    border: none;
    color: rgba(255,255,255,0.5);
    cursor: pointer;
    transition: var(--hud-transition);
}
.btn-close-panel:hover { color: #fff; transform: rotate(90deg); }
</style>