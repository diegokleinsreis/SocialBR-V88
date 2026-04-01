<?php
/**
 * FICHEIRO: componentes/super_debug.php
 * VERSÃO: 8.1 (The Final Clean Edition - Anti-Chaos)
 * PAPEL: Scanner de Integridade com Filtros e Blindagem de Loop.
 * OBJETIVO: Identificar vãos, divs vazias e elementos ocultos sem quebrar a UI.
 */
?>

<style>
    /* 1. INTERFACE DO BOTÃO PRINCIPAL */
    .btn-super-debug {
        color: #fff; background: rgba(255, 255, 255, 0.1);
        padding: 5px 12px; border-radius: 4px; font-size: 11px;
        font-weight: 600; cursor: pointer; border: 1px solid rgba(255, 255, 255, 0.2);
        display: flex; align-items: center; gap: 6px; transition: all 0.2s ease;
    }
    .btn-debug-on { background: #e74c3c !important; border-color: #c0392b; box-shadow: 0 0 10px rgba(231, 76, 60, 0.5); }

    /* 2. PAINEL DE CONTROLO DE FILTROS */
    .debug-filters-panel {
        display: none; position: fixed; top: 60px; left: 20px; 
        background: #1a1a1a; border: 1px solid #333; padding: 12px;
        border-radius: 8px; z-index: 2147483647; box-shadow: 0 10px 30px rgba(0,0,0,0.8);
        color: #fff; font-family: 'Segoe UI', Tahoma, sans-serif; min-width: 190px;
    }
    body.debug-master-mode .debug-filters-panel { display: block; }
    
    .debug-filter-item { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; font-size: 11px; cursor: pointer; user-select: none; }
    .debug-filter-item input { cursor: pointer; width: 14px; height: 14px; }

    /* ============================================================
       CAMADAS DE DIAGNÓSTICO (CSS SELETIVO)
       ============================================================ */

    /* Regra Base: Outline Suave em Containers */
    body.debug-master-mode div, body.debug-master-mode section, body.debug-master-mode aside { outline: 1px solid rgba(255, 0, 0, 0.1) !important; }

    /* FILTRO: Elementos Ocultos (Laranja) */
    body:not(.show-hidden) .tag-hidden, body:not(.show-hidden) .is-hidden-debug { display: none !important; }
    .is-hidden-debug {
        display: block !important; visibility: visible !important;
        outline: 2px dashed #e67e22 !important; position: absolute !important;
        z-index: 10005; background: rgba(230, 126, 34, 0.1) !important; pointer-events: none;
    }

    /* FILTRO: Divs Vazias (Vermelho) */
    body:not(.show-empty) .tag-empty, body:not(.show-empty) .debug-empty-div { display: none !important; }
    .debug-empty-div { outline: 2px dashed #c0392b !important; background: rgba(192, 57, 43, 0.1) !important; min-height: 20px; }

    /* FILTRO: Espaçamentos (Box Model) */
    body:not(.show-spacing) .tag-margin, body:not(.show-spacing) .tag-padding, body:not(.show-spacing) .tag-offset { display: none !important; }

    /* ETIQUETAS DINÂMICAS */
    .debug-label-tag {
        position: absolute; font-size: 9px; font-family: 'Courier New', monospace; font-weight: bold;
        color: #fff; padding: 2px 5px; z-index: 10006; pointer-events: none;
        border-radius: 3px; text-transform: uppercase; white-space: nowrap; box-shadow: 1px 1px 4px rgba(0,0,0,0.5);
    }
    .tag-empty   { background: #c0392b; border: 1px solid #fff; }
    .tag-hidden  { background: #e67e22; border: 1px solid #fff; }
    .tag-margin  { background: #e67e22; }
    .tag-padding { background: #2ecc71; }
    .tag-offset  { background: #9b59b6; }
    .tag-origin  { background: #34495e; color: #f1c40f; top: -14px !important; border: 1px solid #f1c40f; }
</style>

<div class="admin-super-debug-group">
    <button onclick="toggleSuperDebug()" id="btn-master-debug" class="btn-super-debug">
        <i class="fas fa-microscope"></i> Inspecionar UI
    </button>

    <div class="debug-filters-panel" id="debug-panel-root">
        <div style="font-size: 12px; font-weight: bold; margin-bottom: 12px; border-bottom: 1px solid #333; padding-bottom: 6px; color: #f1c40f;">
            Painel de Diagnóstico
        </div>
        <label class="debug-filter-item"><input type="checkbox" id="chk-hidden" checked onchange="updateDebugView()"> Divs Ocultas</label>
        <label class="debug-filter-item"><input type="checkbox" id="chk-empty" checked onchange="updateDebugView()"> Divs Vazias</label>
        <label class="debug-filter-item"><input type="checkbox" id="chk-spacing" checked onchange="updateDebugView()"> Margens/Paddings</label>
        <label class="debug-filter-item"><input type="checkbox" id="chk-origin" checked onchange="updateDebugView()"> Mostrar Origem CSS</label>
        <div style="margin-top: 10px; font-size: 9px; color: #888; border-top: 1px solid #333; padding-top: 6px;">
            Clique nos filtros para limpar a visão.
        </div>
    </div>
</div>

<script>
function toggleSuperDebug() {
    const body = document.body;
    const btn = document.getElementById('btn-master-debug');
    const isActivating = !body.classList.contains('debug-master-mode');

    if (isActivating) {
        body.classList.add('debug-master-mode');
        btn.classList.add('btn-debug-on');
        updateDebugView();
        runDeepDiagnostics();
    } else {
        body.classList.remove('debug-master-mode', 'show-hidden', 'show-empty', 'show-spacing', 'show-origin');
        btn.classList.remove('btn-debug-on');
        document.querySelectorAll('.debug-label-tag').forEach(el => el.remove());
        document.querySelectorAll('.debug-empty-div', '.is-hidden-debug').forEach(el => {
            el.classList.remove('debug-empty-div', 'is-hidden-debug');
        });
    }
}

function updateDebugView() {
    const body = document.body;
    body.classList.toggle('show-hidden', document.getElementById('chk-hidden').checked);
    body.classList.toggle('show-empty', document.getElementById('chk-empty').checked);
    body.classList.toggle('show-spacing', document.getElementById('chk-spacing').checked);
    body.classList.toggle('show-origin', document.getElementById('chk-origin').checked);
}

function runDeepDiagnostics() {
    // BLINDAGEM: Ignora os próprios elementos de debug para evitar loop
    const all = document.querySelectorAll('body.debug-master-mode *:not(.debug-label-tag):not(.debug-filters-panel):not(.btn-super-debug)');
    
    all.forEach(el => {
        const style = window.getComputedStyle(el);
        let stack = 0;

        // 1. OCULTOS (Laranja)
        const inline = el.getAttribute('style') || '';
        if (el.classList.contains('is-hidden') || style.display === 'none' || inline.includes('display:none')) {
            el.classList.add('is-hidden-debug');
            injectTag(el, "!! OCULTO !!", "tag-hidden", stack++);
        }

        // 2. VAZIOS (Vermelho)
        if (el.tagName === 'DIV' && el.children.length === 0 && el.textContent.trim() === '') {
            el.classList.add('debug-empty-div');
            injectTag(el, "DIV VAZIA", "tag-empty", stack++);
        }

        // 3. ESPAÇAMENTO (Box Model)
        const mt = parseInt(style.marginTop);
        const pt = parseInt(style.paddingTop);
        const topVal = parseInt(style.top);
        if (mt > 0 || pt > 0 || (style.position !== 'static' && topVal !== 0)) {
            const origin = (el.id ? '#' + el.id : '') + (el.className ? '.' + el.className.split(' ')[0] : el.tagName);
            injectTag(el, "ORIGEM: " + origin, "tag-origin", stack++);
            if (mt > 0) injectTag(el, "MT: " + mt + "px", "tag-margin", stack++);
            if (pt > 0) injectTag(el, "PT: " + pt + "px", "tag-padding", stack++);
            if (style.position !== 'static' && topVal !== 0) injectTag(el, "TOP: " + topVal + "px", "tag-offset", stack++);
        }
    });
}

function injectTag(parent, text, className, level) {
    if (parent.querySelector('.' + className.replace(' ', '.'))) return;
    const tag = document.createElement('span');
    tag.className = 'debug-label-tag ' + className;
    tag.innerText = text;
    if (window.getComputedStyle(parent).position === 'static') parent.style.position = 'relative';
    parent.appendChild(tag);
    tag.style.top = (level * 12) + "px";
    tag.style.right = "2px";
}
</script>