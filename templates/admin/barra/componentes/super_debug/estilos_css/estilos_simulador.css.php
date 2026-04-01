<?php
/**
 * FICHEIRO: estilos_css/estilos_simulador.css.php
 * PAPEL: Engenharia Visual de Hardware & Glass Controls
 * VERSÃO: 2.2 (Glass Edition - Sincronia de Escala)
 * RESPONSABILIDADE: Definir a moldura dos dispositivos e o visual dos botões de controlo.
 * INTEGRIDADE: Completo e Integral.
 */
?>
<style>
/* 1. OVERLAY DO UNIVERSO DE SIMULAÇÃO (Matemática DVH) */
#device-sim-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100dvh; /* Uso de DVH para evitar cortes em mobile */
    background: radial-gradient(circle at center, rgba(15, 15, 15, 0.98) 0%, rgba(5, 5, 5, 1) 100%);
    z-index: 10000005 !important; /* Prioridade máxima sobre o site Marketplace */
    justify-content: center;
    align-items: center;
    flex-direction: column;
    padding: 0;
    margin: 0;
    overflow: hidden;
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
}

/* Ativação do estado via classe Master */
body.sim-active #device-sim-overlay {
    display: flex !important;
}

/* 2. MOLDURA DE HARDWARE (FRAME) */
.device-frame {
    all: initial; 
    position: relative;
    background: #111;
    border-radius: 55px;
    padding: 12px;
    box-shadow: 
        0 0 0 2px #222, 
        0 30px 60px rgba(0,0,0,0.9),
        inset 0 0 20px rgba(255,255,255,0.05);
    
    /* Motor de Transformação Atómico (Sincronizado com motor_simulador.js.php) */
    transform: scale(var(--sim-scale, 1)) rotate(var(--sim-rotation, 0deg));
    transform-origin: center center;
    transition: transform 0.6s cubic-bezier(0.23, 1, 0.32, 1);
    
    display: flex;
    justify-content: center;
    align-items: center;
    flex-shrink: 0 !important;
    pointer-events: auto;
}

/* 3. ECRÃ E VIEWPORT */
.device-screen {
    position: relative;
    background: #fff;
    width: var(--current-w, 393px);
    height: var(--current-h, 852px);
    border-radius: 42px;
    overflow: hidden;
    border: 4px solid #000;
    flex-shrink: 0;
}

.device-screen iframe {
    width: 100% !important;
    height: 100% !important;
    border: none;
    display: block;
    background: #fff;
}

/* 4. NOTCH INTERATIVO (iPhone Style) */
.device-notch {
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 150px;
    height: 35px;
    background: #000;
    border-bottom-left-radius: 20px;
    border-bottom-right-radius: 20px;
    z-index: 100;
}

/* 5. PAINEL DE CONTROLO (VISUAL GLASS LUXO) */
.sim-controls {
    all: initial;
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    
    /* Efeito Glass Morphism Integrado */
    background: var(--hud-glass-bg) !important;
    border-radius: 100px !important;
    box-shadow: var(--hud-glass-shadow) !important;
    backdrop-filter: var(--hud-glass-blur) saturate(180%) !important;
    -webkit-backdrop-filter: var(--hud-glass-blur) saturate(180%) !important;
    border: 1px solid var(--hud-glass-border) !important;
    
    padding: 12px 30px;
    display: flex;
    gap: 25px;
    z-index: 10000000;
    font-family: 'Inter', sans-serif;
}

.btn-sim-opt {
    background: none;
    border: none;
    color: rgba(255,255,255,0.4);
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

.btn-sim-opt i { font-size: 18px; }
.btn-sim-opt:hover { color: #fff; transform: translateY(-2px); }

.btn-sim-opt.active { 
    color: var(--hud-accent) !important; 
    text-shadow: 0 0 10px rgba(241, 196, 15, 0.4); 
}

/* 6. VARIAÇÕES DE HARDWARE */
#device-sim-overlay.sim-android .device-frame { border-radius: 40px; }
#device-sim-overlay.sim-android .device-screen { border-radius: 32px; }
#device-sim-overlay.sim-android .device-notch { width: 50px; height: 10px; top: 15px; border-radius: 10px; }

#device-sim-overlay.sim-tablet .device-frame { border-radius: 25px; padding: 25px; }
#device-sim-overlay.sim-tablet .device-screen { border-radius: 8px; }
#device-sim-overlay.sim-tablet .device-notch { display: none; }

/* 7. RESPONSIVIDADE PARA CONTROLOS */
@media (max-width: 600px) {
    .sim-controls { 
        padding: 10px 15px; 
        gap: 15px; 
        bottom: 20px;
        width: 90%;
        justify-content: center;
    }
    .btn-sim-opt span { display: none; } /* Mantém apenas ícones em ecrãs minúsculos */
}
</style>