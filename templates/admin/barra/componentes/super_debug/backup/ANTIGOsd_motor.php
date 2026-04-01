/**
 * FICHEIRO: super_debug/sd_motor.php
 * PAPEL: Motor de Inteligência e Comportamento do HUD Admin.
 * VERSÃO: 22.5 (Precision Scale & Matrix Rotation Edition)
 * INTEGRIDADE: Completo e Integral.
 */

/**
 * 1. GESTÃO DE ESTADOS E ATIVAÇÃO (SUPER-DEBUG)
 */
function toggleSuperDebug() {
    const body = document.body;
    const btn = document.getElementById('btn-master-debug');
    const panel = document.getElementById('debug-panel-root');
    const isActivating = !body.classList.contains('debug-master-mode');

    if (isActivating) {
        body.classList.add('debug-master-mode');
        btn.classList.add('btn-debug-on');
        if(panel) panel.style.display = 'block';
        updateDebugView();
        console.log("Super Debug V22.5: Scanner Ativado.");
        runDeepDiagnostics();
    } else {
        body.classList.remove('debug-master-mode', 'show-hidden', 'show-empty', 'show-spacing', 'show-origin');
        btn.classList.remove('btn-debug-on');
        if(panel) panel.style.display = 'none';
        document.querySelectorAll('.debug-label-tag').forEach(el => el.remove());
        document.querySelectorAll('.debug-is-empty, .debug-is-hidden, .debug-gap-causer, .debug-is-fixed').forEach(el => {
            el.classList.remove('debug-is-empty', 'debug-is-hidden', 'debug-gap-causer', 'debug-is-fixed');
        });
        console.log("Super Debug V22.5: Scanner Desativado.");
    }
}

/**
 * 2. SQL HUB UNIFICADO
 */
function switchSQLTab(tabName) {
    document.querySelectorAll('.sql-tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.sql-tab-content').forEach(content => content.classList.remove('active'));
    if (tabName === 'live') {
        document.getElementById('btn-tab-live').classList.add('active');
        document.getElementById('sql-tab-live').classList.add('active');
    } else {
        document.getElementById('btn-tab-audit').classList.add('active');
        document.getElementById('sql-tab-audit').classList.add('active');
    }
}

async function clearSQLAuditLog() {
    const btn = document.querySelector('.btn-clear-log');
    if (!confirm("Deseja realmente apagar o histórico de auditoria SQL?")) return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;

    try {
        const response = await fetch('/~klscom/api/admin/limpar_sql_audit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        });
        const result = await response.json();
        if (result.success) {
            btn.style.background = "#2ecc71";
            setTimeout(() => { location.reload(); }, 600);
        } else {
            alert("Erro: " + (result.error || "Ação negada"));
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    } catch (error) {
        console.error("Erro SQL API:", error);
        btn.disabled = false;
    }
}

/**
 * 3. DEVICE SIMULATOR (MATEMÁTICA DE ESCALA DE PRECISÃO)
 */

function calculateDeviceScale() {
    const overlay = document.getElementById('device-sim-overlay');
    const frame = document.getElementById('device-hardware-frame');
    if (!overlay || !frame || !document.body.classList.contains('sim-active')) return;

    // 1. ESPAÇO DISPONÍVEL (Janela menos folga de UI)
    const paddingUI = 160; // Espaço para controles e margens
    const availableW = window.innerWidth - 40;
    const availableH = window.innerHeight - paddingUI;

    // 2. DIMENSÕES REAIS DO HARDWARE (Sem escala aplicada)
    // Forçamos temporariamente a escala 1 para medir o tamanho real do frame CSS
    const oldScale = overlay.style.getPropertyValue('--sim-scale') || "1";
    overlay.style.setProperty('--sim-scale', "1");
    
    // Medimos o retalho real (Bounding Box) que o frame ocupa
    const rect = frame.getBoundingClientRect();
    const isLandscape = overlay.classList.contains('device-landscape-mode');
    
    // Se estiver em landscape, as dimensões de medição se invertem no cálculo
    const targetW = isLandscape ? rect.height : rect.width;
    const targetH = isLandscape ? rect.width : rect.height;

    // 3. CÁLCULO DA ESCALA (LaTeX: $scale = \min(\frac{W_{av}}{W_{tg}}, \frac{H_{av}}{H_{tg}})$)
    let scale = Math.min(availableW / targetW, availableH / targetH, 1);
    
    // Proteção para não ficar microscópico em telas muito pequenas
    if (scale < 0.25) scale = 0.25;

    // 4. APLICAÇÃO ATÓMICA
    overlay.style.setProperty('--sim-scale', scale.toFixed(3));
    
    console.log(`Motor Sim: Escala ajustada para ${scale.toFixed(3)} | Modo: ${isLandscape ? 'Landscape' : 'Portrait'}`);
}

function toggleDeviceSim() {
    const body = document.body;
    const isActivating = !body.classList.contains('sim-active');

    if (isActivating) {
        body.classList.add('sim-active');
        localStorage.setItem('admin_device_sim', 'active');
        
        // Esconde outros hubs para limpar a visão
        ['sql-hub-root', 'metrics-hub-root', 'moderation-hub-root'].forEach(id => {
            const el = document.getElementById(id);
            if(el) el.style.display = 'none';
        });

        const savedModel = localStorage.getItem('admin_device_model') || 'iphone';
        const savedRotation = localStorage.getItem('admin_device_rotation') || 'portrait';
        
        switchDeviceModel(savedModel);
        if(savedRotation === 'landscape') {
            document.getElementById('device-sim-overlay').classList.add('device-landscape-mode');
            document.getElementById('device-sim-overlay').style.setProperty('--sim-rotation', '90deg');
        }

        setTimeout(calculateDeviceScale, 100);
    } else {
        body.classList.remove('sim-active');
        localStorage.setItem('admin_device_sim', 'inactive');
    }
}

function switchDeviceModel(model) {
    const overlay = document.getElementById('device-sim-overlay');
    if (!overlay) return;

    overlay.classList.remove('sim-iphone', 'sim-android', 'sim-tablet');
    overlay.classList.add('sim-' + model);
    
    // Atualiza botões
    document.querySelectorAll('.btn-sim-opt').forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-model') === model) btn.classList.add('active');
    });

    localStorage.setItem('admin_device_model', model);
    
    // Reset de variáveis de hardware baseadas no modelo (conforme CSS)
    const dims = { 'iphone': [393, 852], 'android': [360, 800], 'tablet': [1024, 768] };
    overlay.style.setProperty('--current-w', dims[model][0] + 'px');
    overlay.style.setProperty('--current-h', dims[model][1] + 'px');

    setTimeout(calculateDeviceScale, 50);
}

function rotateDevice() {
    const overlay = document.getElementById('device-sim-overlay');
    if (!overlay) return;
    
    const isLandscape = overlay.classList.toggle('device-landscape-mode');
    const rotation = isLandscape ? '90deg' : '0deg';
    
    overlay.style.setProperty('--sim-rotation', rotation);
    localStorage.setItem('admin_device_rotation', isLandscape ? 'landscape' : 'portrait');
    
    // Disparar recálculo imediato
    calculateDeviceScale();
}

/**
 * 4. UTILITÁRIOS E DIAGNÓSTICOS
 */
function handleVisionChange(select) {
    if(!select.value) return;
    select.style.opacity = '0.5';
    select.disabled = true;
    window.location.href = select.value;
}

function runDeepDiagnostics() {
    const selector = 'body.debug-master-mode *:not(.debug-label-tag):not(.debug-filters-panel):not(.btn-super-debug):not(script):not(style)';
    const allElements = document.querySelectorAll(selector);

    allElements.forEach(el => {
        if (el.closest('#barra-admin-master') || el.closest('#device-sim-overlay')) return;

        const style = window.getComputedStyle(el);
        const isHidden = (style.display === 'none' || style.visibility === 'hidden');
        
        if (isHidden) {
            el.classList.add('debug-is-hidden');
            injectDebugTag(el, "OCULTO", "tag-hidden", 0);
        }

        if (el.tagName === 'DIV' && el.children.length === 0 && el.textContent.trim() === '') {
            el.classList.add('debug-is-empty');
            injectDebugTag(el, "VAZIO", "tag-empty", 1);
        }
    });
}

function injectDebugTag(parent, text, className, level) {
    if (parent.querySelector('.' + className)) return;
    const tag = document.createElement('span');
    tag.className = 'debug-label-tag ' + className;
    tag.innerText = text;
    parent.appendChild(tag);
}

// Inicialização e Listeners
window.addEventListener('resize', calculateDeviceScale);

// Observer para mudanças de visibilidade no DOM que afetem o layout
if (window.ResizeObserver) {
    const ro = new ResizeObserver(() => {
        if(document.body.classList.contains('sim-active')) calculateDeviceScale();
    });
    ro.observe(document.body);
}

document.addEventListener("DOMContentLoaded", () => {
    if (localStorage.getItem('admin_device_sim') === 'active') {
        toggleDeviceSim();
    }
});