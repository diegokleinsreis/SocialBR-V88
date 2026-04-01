<?php
/**
 * FICHEIRO: motores_js/motor_diagnostico.js.php
 * PAPEL: Motor de Inspeção e Diagnóstico (Scanner Cirúrgico)
 * VERSÃO: 3.2 (Correção de Tags Fantasmas & Performance)
 * INTEGRIDADE: Completo e Integral.
 */
?>
<script>
/**
 * 1. ATIVAÇÃO DO MODO MASTER DEBUG
 */
function toggleSuperDebug() {
    const corpo = document.body;
    const btn = document.getElementById('btn-master-debug');
    const painel = document.getElementById('debug-panel-root');
    const estaAtivando = !corpo.classList.contains('debug-master-mode');

    if (estaAtivando) {
        corpo.classList.add('debug-master-mode');
        if(btn) btn.classList.add('btn-debug-on');
        if(painel) painel.style.display = 'block';
        
        atualizarVisualizacaoDebug();
        console.log("Diagnóstico: Scanner Cirúrgico Ativado.");
        executarDiagnosticoProfundo();
    } else {
        corpo.classList.remove('debug-master-mode', 'show-hidden', 'show-empty', 'show-spacing', 'show-origin');
        if(btn) btn.classList.remove('btn-debug-on');
        if(painel) painel.style.display = 'none';
        
        document.querySelectorAll('.debug-label-tag').forEach(etiqueta => etiqueta.remove());
        document.querySelectorAll('.debug-is-empty, .debug-is-hidden, .debug-gap-causer').forEach(el => {
            el.classList.remove('debug-is-empty', 'debug-is-hidden', 'debug-gap-causer');
            if(el.dataset.oldPos) el.style.position = el.dataset.oldPos;
        });
        
        console.log("Diagnóstico: Scanner Desativado.");
    }
}

/**
 * 2. SCANNER DE INTERFACE (VERSÃO FILTRADA)
 */
function executarDiagnosticoProfundo() {
    // LISTA NEGRA: Impedir que o scanner injete em elementos de sistema ou texto
    const tagsIgnoradas = ['OPTION', 'OPTGROUP', 'BR', 'SCRIPT', 'STYLE', 'I', 'SPAN', 'HEAD', 'META', 'LINK'];
    
    const seletor = 'body.debug-master-mode *:not(.debug-label-tag):not(.debug-filters-panel):not(.btn-super-debug)';
    const elementos = document.querySelectorAll(seletor);

    elementos.forEach(el => {
        // A. FILTROS DE SEGURANÇA
        if (el.closest('#barra-admin-master') || el.closest('#device-sim-overlay')) return;
        if (tagsIgnoradas.includes(el.tagName)) return; // Ignora as tags que causaram o erro

        const estilo = window.getComputedStyle(el);
        let nivelPilha = 0;

        // B. ORIGEM DO ELEMENTO
        const idTexto = el.id ? '#' + el.id : '';
        const classeTexto = el.className && typeof el.className === 'string' ? '.' + el.className.split(' ')[0] : '';
        const tagTexto = el.tagName.toLowerCase();
        const infoOrigem = `🏷️ ${tagTexto}${idTexto}${classeTexto}`;
        injetarEtiquetaDebug(el, infoOrigem, "tag-origin", nivelPilha++);

        // C. ELEMENTOS OCULTOS
        const estaOculto = (estilo.display === 'none' || estilo.visibility === 'hidden');
        if (estaOculto) {
            el.classList.add('debug-is-hidden');
            injetarEtiquetaDebug(el, "!! OCULTO !!", "tag-hidden", nivelPilha++);
        }

        // D. DIVS VAZIAS
        if (el.tagName === 'DIV' && el.children.length === 0 && el.textContent.trim() === '') {
            if (!estaOculto) {
                el.classList.add('debug-is-empty');
                injetarEtiquetaDebug(el, "DIV VAZIA", "tag-empty", nivelPilha++);
            }
        }

        // E. ESPAÇAMENTOS
        const mt = parseInt(estilo.marginTop);
        const pt = parseInt(estilo.paddingTop);
        if (mt > 10 || pt > 10) { // Só reporta espaçamentos significativos
            el.classList.add('debug-gap-causer');
            if (mt > 10) injetarEtiquetaDebug(el, `M: ${mt}px`, "tag-margin", nivelPilha++);
            if (pt > 10) injetarEtiquetaDebug(el, `P: ${pt}px`, "tag-padding", nivelPilha++);
        }
    });
}

/**
 * 3. INJETOR DE ETIQUETAS
 */
function injetarEtiquetaDebug(pai, texto, classeCSS, nivel) {
    if (pai.querySelector('.' + classeCSS)) return;

    const etiqueta = document.createElement('span');
    etiqueta.className = 'debug-label-tag ' + classeCSS;
    etiqueta.innerText = texto;

    const estiloPai = window.getComputedStyle(pai);
    if (estiloPai.position === 'static') {
        pai.dataset.oldPos = 'static';
        pai.style.setProperty('position', 'relative', 'important');
    }

    pai.appendChild(etiqueta);
    etiqueta.style.top = (nivel * 14) + "px";
}

/**
 * 4. GESTÃO DE FILTROS
 */
function atualizarVisualizacaoDebug() {
    const corpo = document.body;
    const camadas = {
        'show-hidden': 'chk-hidden', 
        'show-empty': 'chk-empty', 
        'show-spacing': 'chk-spacing', 
        'show-origin': 'chk-origin'
    };

    for (const [classeCorpo, idCheckbox] of Object.entries(camadas)) {
        const checkbox = document.getElementById(idCheckbox);
        if (checkbox && checkbox.checked) {
            corpo.classList.add(classeCorpo);
        } else {
            corpo.classList.remove(classeCorpo);
        }
    }
}
</script>