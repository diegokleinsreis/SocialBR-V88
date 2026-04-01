<?php
/**
 * FICHEIRO: super_debug/sd_device_sim_estilos.php
 * PAPEL: Engenharia Visual de Dispositivos (Isolamento Atómico & Escala Dinâmica)
 * VERSÃO: 2.0 (Pro Scale & Matrix Rotation)
 * INTEGRIDADE: Completo e Integral.
 */
?>
<style>
/* 1. VARIÁVEIS DE HARDWARE E MOTOR DE ESCALA */
:root {
    /* Dimensões Base (Referência) */
    --sim-iphone-w: 393px;
    --sim-iphone-h: 852px;
    --sim-android-w: 360px;
    --sim-android-h: 800px;
    --sim-tablet-w: 1024px;
    --sim-tablet-h: 768px;
    
    --sim-border-color: #1a1a1a;
    --sim-notch-color: #000;
    
    /* VARIÁVEIS DE ESTADO (Controladas pelo JS) */
    --current-w: 393px;
    --current-h: 852px;
    --sim-scale: 1; 
    --sim-rotation: 0deg;
}

/* 2. O OVERLAY (O Universo Isolado) */
#device-sim-overlay {
    display: none;
    position: fixed;
    top: 0; /* Ocupa tudo, a barra HUD fica por cima ou integrada */
    left: 0;
    width: 100vw;
    height: 100vh;
    background: radial-gradient(circle at center, rgba(30, 30, 30, 0.98) 0%, rgba(10, 10, 10, 1) 100%);
    z-index: 9999999; 
    justify-content: center;
    align-items: center;
    flex-direction: column;
    padding: 0;
    margin: 0;
    transition: background 0.5s ease;
    overflow: hidden;
    box-sizing: border-box;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

/* Ativação via Body */
body.sim-active #device-sim-overlay { display: flex; }
body.sim-active { overflow: hidden !important; }

/* 3. A MOLDURA (Hardware de Luxo) */
.device-frame {
    all: initial; /* Reset total de herança */
    position: relative;
    background: var(--sim-border-color);
    border-radius: 55px;
    padding: 12px; /* Moldura física do aparelho */
    box-shadow: 
        0 0 0 2px #333, 
        0 30px 60px rgba(0,0,0,0.8),
        inset 0 0 20px rgba(255,255,255,0.1);
    
    /* MOTOR DE TRANSFORMAÇÃO: Escala e Rotação combinadas */
    transform: scale(var(--sim-scale)) rotate(var(--sim-rotation));
    transform-origin: center center;
    
    transition: transform 0.6s cubic-bezier(0.23, 1, 0.32, 1), 
                width 0.6s cubic-bezier(0.23, 1, 0.32, 1), 
                height 0.6s cubic-bezier(0.23, 1, 0.32, 1);
    
    display: flex;
    justify-content: center;
    align-items: center;
    flex-shrink: 0 !important;
    pointer-events: auto;
    will-change: transform;
}

/* 4. O ECRÃ (O Viewport do Site) */
.device-screen {
    position: relative;
    background: #fff;
    width: var(--current-w);
    height: var(--current-h);
    border-radius: 42px;
    overflow: hidden;
    border: 4px solid #000;
    box-shadow: inset 0 0 10px rgba(0,0,0,0.2);
    flex-shrink: 0;
}

.device-screen iframe {
    width: 100% !important;
    height: 100% !important;
    border: none;
    display: block;
    background: #fff;
    /* Evita que o iframe capture scrolls do pai */
    pointer-events: auto;
}

/* 5. O NOTCH (Interativo) */
.device-notch {
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 150px;
    height: 35px;
    background: var(--sim-notch-color);
    border-bottom-left-radius: 20px;
    border-bottom-right-radius: 20px;
    z-index: 100;
    transition: all 0.3s ease;
}

/* Sensor da Câmera no Notch */
.device-notch::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 70%;
    transform: translate(-50%, -50%);
    width: 8px;
    height: 8px;
    background: #1a1a3a;
    border-radius: 50%;
    box-shadow: 0 0 2px rgba(255,255,255,0.2);
}

/* 6. MODOS ESPECÍFICOS (Variações Atômicas) */
#device-sim-overlay.sim-android .device-frame { border-radius: 40px; }
#device-sim-overlay.sim-android .device-screen { border-radius: 32px; }
#device-sim-overlay.sim-android .device-notch { 
    width: 50px; height: 10px; top: 15px; border-radius: 10px;
}

#device-sim-overlay.sim-tablet .device-frame { border-radius: 25px; padding: 25px; }
#device-sim-overlay.sim-tablet .device-screen { border-radius: 8px; }
#device-sim-overlay.sim-tablet .device-notch { display: none; }

/* 7. PAINEL DE CONTROLO (Glassmorphism Luxo) */
.sim-controls {
    all: initial;
    position: absolute;
    bottom: 40px; /* Colocado em baixo para não conflitar com a barra principal */
    left: 50%;
    transform: translateX(-50%);
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(25px) saturate(180%);
    -webkit-backdrop-filter: blur(25px) saturate(180%);
    padding: 15px 35px;
    border-radius: 100px;
    display: flex;
    gap: 30px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    box-shadow: 0 20px 50px rgba(0,0,0,0.6);
    z-index: 10000000;
    font-family: 'Inter', sans-serif;
}

.btn-sim-opt {
    background: none;
    border: none;
    color: rgba(255,255,255,0.5);
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.btn-sim-opt i { font-size: 20px; margin-bottom: 2px; }
.btn-sim-opt:hover { color: #fff; transform: translateY(-3px); }
.btn-sim-opt.active { color: #00d2ff; text-shadow: 0 0 15px rgba(0,210,255,0.5); }

/* 8. CORREÇÃO DE ROTAÇÃO (CSS-SIDE) */
/* Quando rotacionado, o notch deve se ajustar lateralmente se necessário */
.device-landscape-mode .device-notch {
    /* O JS aplicará a classe .device-landscape-mode ao overlay */
}

/* 9. ANIMAÇÃO DE ENTRADA CINEMATOGRÁFICA */
@keyframes deviceShow {
    0% { opacity: 0; transform: scale(0.2) rotate(-5deg); filter: blur(20px); }
    100% { opacity: 1; transform: scale(var(--sim-scale)) rotate(0deg); filter: blur(0); }
}
body.sim-active .device-frame { animation: deviceShow 0.8s cubic-bezier(0.19, 1, 0.22, 1); }
</style>