<?php
/**
 * FICHEIRO: motores_js/motor_simulador.js.php
 * PAPEL: Motor de Simulação de Hardware (Physics & Scale Engine)
 * VERSÃO: 3.2 (Hard Reload & Productivity Edition)
 * RESPONSABILIDADE: Gestão de modelos e recarregamento isolado de viewport.
 * INTEGRIDADE: Completo e Integral.
 */
?>
<script>
/**
 * 1. CÁLCULO DE ESCALA DINÂMICA PREDITIVA
 * Mantém o dispositivo sempre visível e centralizado sem cortes.
 */
function calcularEscalaDispositivo() {
    const overlay = document.getElementById('device-sim-overlay');
    if (!overlay || !document.body.classList.contains('sim-active')) return;

    // A. Captura do Modelo via Persistência
    const modelo = localStorage.getItem('admin_device_model') || 'iphone';
    
    // Dimensões nominais dos Hardwares (Sempre em Modo Retrato)
    const dimensoesFisicas = { 
        'iphone': [393, 852], 
        'android': [360, 800], 
        'tablet': [1024, 768] 
    };

    // Buffer de Hardware (Molduras e Sombras)
    const targetW = dimensoesFisicas[modelo][0] + 40;
    const targetH = dimensoesFisicas[modelo][1] + 110;

    // B. Definição do Espaço Disponível Real (Viewport HUD)
    const larguraDisponivel = window.innerWidth - 40;
    const alturaDisponivel = window.innerHeight - 150; 

    // C. Algoritmo de Encaixe (Scale-to-Fit)
    let escalaIdeal = Math.min(
        larguraDisponivel / targetW, 
        alturaDisponivel / targetH, 
        1 
    );

    if (escalaIdeal < 0.2) escalaIdeal = 0.2;

    // D. Aplicação da Variável de Transformação
    overlay.style.setProperty('--sim-scale', escalaIdeal.toFixed(4));
    
    // Reset de rotação por segurança (Garante que o site nunca fique deitado)
    overlay.style.setProperty('--sim-rotation', '0deg');
}

/**
 * 2. RECARREGAMENTO DO DISPOSITIVO (NOVA FUNÇÃO)
 * Atualiza apenas o Iframe, disparando o loader visual.
 */
function reloadDevice() {
    const iframe = document.getElementById('sim-viewport');
    const loader = document.getElementById('sim-loader');
    
    if (iframe) {
        console.log("Simulador: Realizando Hard Reload da Viewport...");
        
        // Ativa o feedback visual de carregamento
        if (loader) {
            loader.style.display = "flex";
            loader.style.opacity = "1";
        }
        
        // Força o recarregamento mantendo a flag de simulação
        const currentUrl = new URL(iframe.src);
        currentUrl.searchParams.set('reload_ts', Date.now()); // Evita cache do navegador
        iframe.src = currentUrl.href;
    }
}

/**
 * 3. ATIVAÇÃO E PERSISTÊNCIA
 */
function toggleDeviceSim() {
    const body = document.body;
    const estaAtivando = !body.classList.contains('sim-active');

    if (estaAtivando) {
        body.classList.add('sim-active');
        localStorage.setItem('admin_device_sim', 'active');
        
        const modeloSalvo = localStorage.getItem('admin_device_model') || 'iphone';
        switchDeviceModel(modeloSalvo);
        
        // Sincronia de escala
        calcularEscalaDispositivo(); 
        setTimeout(calcularEscalaDispositivo, 150);
        setTimeout(calcularEscalaDispositivo, 450); 
    } else {
        body.classList.remove('sim-active');
        localStorage.setItem('admin_device_sim', 'inactive');
        
        const iframe = document.getElementById('sim-viewport');
        if (iframe) iframe.src = "about:blank";
    }
}

/**
 * 4. TROCA DE MODELO
 */
function switchDeviceModel(modelo) {
    const overlay = document.getElementById('device-sim-overlay');
    if (!overlay) return;

    overlay.classList.remove('sim-iphone', 'sim-android', 'sim-tablet');
    overlay.classList.add('sim-' + modelo);
    
    document.querySelectorAll('.btn-sim-opt').forEach(btn => {
        btn.classList.toggle('active', btn.getAttribute('data-model') === modelo);
    });

    localStorage.setItem('admin_device_model', modelo);
    
    const dimensoes = { 'iphone': [393, 852], 'android': [360, 800], 'tablet': [1024, 768] };
    overlay.style.setProperty('--current-w', dimensoes[modelo][0] + 'px');
    overlay.style.setProperty('--current-h', dimensoes[modelo][1] + 'px');

    calcularEscalaDispositivo();
    setTimeout(calcularEscalaDispositivo, 50);
}

/**
 * 5. LISTENERS DINÂMICOS
 */
window.addEventListener('resize', calcularEscalaDispositivo);
</script>