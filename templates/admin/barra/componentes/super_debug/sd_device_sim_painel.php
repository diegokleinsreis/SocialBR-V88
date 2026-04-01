<?php
/**
 * FICHEIRO: super_debug/sd_device_sim_painel.php
 * PAPEL: Interface de Simulação (The Frame & Controls)
 * VERSÃO: 2.2 (Integração de Recarregamento de Iframe)
 * INTEGRIDADE: Completo e Integral - Blindagem Dupla contra Inception.
 */
?>

<div id="device-sim-overlay" class="sim-iphone">

    <div class="sim-controls">
        <button type="button" class="btn-sim-opt active" data-model="iphone" onclick="switchDeviceModel('iphone')" title="iPhone 15 Pro">
            <i class="fas fa-mobile-alt"></i> <span>iPhone</span>
        </button>
        
        <button type="button" class="btn-sim-opt" data-model="android" onclick="switchDeviceModel('android')" title="Samsung S23">
            <i class="fab fa-android"></i> <span>Android</span>
        </button>
        
        <button type="button" class="btn-sim-opt" data-model="tablet" onclick="switchDeviceModel('tablet')" title="iPad Air">
            <i class="fas fa-tablet-alt"></i> <span>Tablet</span>
        </button>
        
        <div style="width: 1px; background: rgba(255,255,255,0.15); margin: 0 10px;"></div>

        <button type="button" class="btn-sim-opt" onclick="reloadDevice()" title="Recarregar apenas o Iframe (Reset de Viewport)">
            <i class="fas fa-sync"></i> <span>Recarregar</span>
        </button>
        
        <button type="button" class="btn-sim-opt" onclick="toggleDeviceSim()" title="Fechar Simulador" style="color: #ff7675;">
            <i class="fas fa-times-circle"></i> <span>Fechar</span>
        </button>
    </div>

    <div class="device-frame" id="device-hardware-frame">
        <div class="device-notch"></div>
        
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
                    background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, transparent 50%); 
                    pointer-events: none; z-index: 105; border-radius: inherit;"></div>
        
        <div class="device-screen">
            <div id="sim-loader" style="position:absolute; inset:0; background:#111; display:flex; align-items:center; justify-content:center; z-index:200; transition: opacity 0.5s;">
                 <i class="fas fa-circle-notch fa-spin" style="color:#00d2ff; font-size:30px;"></i>
            </div>

            <iframe id="sim-viewport" 
                    name="sim-viewport"
                    src="about:blank" 
                    frameborder="0"
                    loading="lazy"></iframe>
        </div>
    </div>

</div>

<script>
/**
 * MOTOR DE SINCRONIZAÇÃO E BLINDAGEM (CORRIGIDO)
 */
(function() {
    const iframe = document.getElementById('sim-viewport');
    const overlay = document.getElementById('device-sim-overlay');
    const loader = document.getElementById('sim-loader');
    
    // 1. OBSERVER DE ATIVAÇÃO
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === "class") {
                const isActive = document.body.classList.contains('sim-active');
                
                if (isActive) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('is_simulated', '1');
                    
                    if (iframe.src === "about:blank" || iframe.src === "") {
                        console.log("Device Sim: Iniciando Viewport Seguro...");
                        loader.style.opacity = "1";
                        loader.style.display = "flex";
                        iframe.src = url.href;
                    }
                    
                    /**
                     * Sincronia com o motor de escala
                     */
                    if (typeof calcularEscalaDispositivo === "function") {
                        setTimeout(calcularEscalaDispositivo, 50); 
                    }
                }
            }
        });
    });

    observer.observe(document.body, { attributes: true });

    // 2. LIMPEZA E UX NO LOAD
    iframe.onload = function() {
        loader.style.opacity = "0";
        setTimeout(() => { loader.style.display = "none"; }, 500);

        try {
            const innerWin = iframe.contentWindow;
            const innerDoc = innerWin.document;

            const bar = innerDoc.getElementById('barra-admin-master');
            if (bar) {
                bar.style.display = 'none';
                innerDoc.body.style.paddingTop = '0px';
            }
            
            innerWin.scrollTo(0, 0);

        } catch (e) {
            console.info("Device Sim: Ambiente isolado com sucesso (Trava PHP Ativa).");
        }
    };
})();
</script>