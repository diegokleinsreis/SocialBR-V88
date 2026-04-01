<?php
/**
 * FICHEIRO: super_debug/sd_autofix.php
 * PAPEL: Motor de Correção Automática (O Cirurgião)
 * VERSÃO: 20.1 (Sincronia Atómica & Auditoria Visual)
 * INTEGRIDADE: Completo e Integral - Sem remoções.
 */
?>

/**
 * FUNÇÃO MESTRE: APPLY AUTO-FIX
 * Esta função varre o que o scanner encontrou e aplica correções cirúrgicas,
 * deixando um rastro visual (borda verde) nos elementos corrigidos.
 */
function applyAutoFix() {
    console.group("🚀 Super Debug: Iniciando Auto-Fix com Rastro");
    
    let fixCount = 0;

    // 1. LIMPEZA DE DIVS VAZIAS
    // Estas são removidas fisicamente para limpar o DOM de elementos "fantasmas".
    const emptyDivs = document.querySelectorAll('.debug-is-empty');
    if (emptyDivs.length > 0) {
        console.log(`Sweep: Removendo ${emptyDivs.length} divs inúteis.`);
        emptyDivs.forEach(div => {
            console.warn("Auto-Fix: Removendo elemento:", div);
            div.remove();
        });
        fixCount += emptyDivs.length;
    }

    // 2. CORREÇÃO DE OFFSET E MARGENS (Vãos)
    // Aqui aplicamos o rastro visual para que o Arquiteto saiba quem foi movido.
    const gapTargets = document.querySelectorAll('.debug-gap-causer');
    gapTargets.forEach(el => {
        const style = window.getComputedStyle(el);
        const topVal = parseInt(style.top);
        const mtVal = parseInt(style.marginTop);
        
        // Verifica se há algo para corrigir (evita redundância)
        if ( (style.position !== 'static' && topVal !== 0) || mtVal !== 0 ) {
            console.log("Auto-Fix: Corrigindo posição de:", el);
            
            // APLICAÇÃO DA CORREÇÃO
            el.style.setProperty('top', '0px', 'important');
            el.style.setProperty('margin-top', '0px', 'important');
            
            // INJEÇÃO DO RASTRO (AUDITORIA)
            el.classList.add('debug-is-fixed');
            injectFixedTag(el);
            
            fixCount++;
        }
    });

    // 3. ESTABILIZAÇÃO DE ELEMENTOS OCULTOS
    // Apenas para dar visibilidade ao que estava escondido durante o debug.
    const hiddenElements = document.querySelectorAll('.debug-is-hidden');
    hiddenElements.forEach(el => {
        el.style.opacity = "0.3";
    });

    console.log(`✅ Auto-Fix concluído. ${fixCount} correções aplicadas.`);
    console.groupEnd();

    // Feedback visual para o Arquiteto
    if (fixCount > 0) {
        alert(`Auto-Fix Concluído!\n\nForam aplicadas ${fixCount} correções.\nOs elementos destacados em VERDE no código/inspetor são os que foram movidos/ajustados.`);
    } else {
        alert("Auto-Fix: Nenhum erro passível de correção automática foi encontrado neste ecrã.");
    }
}

/**
 * FUNÇÃO AUXILIAR: INJECT FIXED TAG
 * Injeta a etiqueta [ FIXADO ] no elemento corrigido respeitando a hierarquia do scanner.
 */
function injectFixedTag(parent) {
    // Evita duplicar a etiqueta de fixação
    if (parent.querySelector('.tag-fixed')) return;

    const tag = document.createElement('span');
    tag.className = 'debug-label-tag tag-fixed';
    tag.innerText = '[ FIXADO ]';
    
    // Garante que o pai tem posição para segurar a tag
    const pStyle = window.getComputedStyle(parent);
    if (pStyle.position === 'static') {
        parent.style.setProperty('position', 'relative', 'important');
    }
    
    parent.appendChild(tag);
    
    // Posicionamento padrão para não sobrepor outras tags do scanner
    tag.style.top = "-18px";
    tag.style.left = "2px";
    tag.style.background = "#2ecc71"; // Verde Sucesso
    tag.style.border = "1px solid #fff";
    tag.style.color = "#fff";
    tag.style.fontSize = "9px";
    tag.style.padding = "2px 4px";
    tag.style.borderRadius = "3px";
    tag.style.position = "absolute";
    tag.style.zIndex = "1000001";
}