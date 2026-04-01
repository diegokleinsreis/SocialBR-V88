<?php
/**
 * FICHEIRO: estilos_css/estilos_metricas.css.php
 * PAPEL: Estética Glass para o Monitor de Performance (Telemetry UI)
 * VERSÃO: 2.2 (Glass Edition - Blindagem de Alinhamento)
 * RESPONSABILIDADE: Estilizar velocímetros, painel de telemetria e garantir alinhamento horizontal.
 * INTEGRIDADE: Completo e Integral.
 */
?>
<style>
/* 1. GRUPO DE PERFORMANCE (ALINHAMENTO BLINDADO) */
.admin-perf-group {
    display: flex !important;          /* Força alinhamento flexível */
    flex-direction: row !important;    /* Garante que fiquem lado a lado */
    align-items: center !important;
    gap: 6px !important;
    padding-right: 10px;
    border-right: 1px solid rgba(255, 255, 255, 0.1);
}

/* 2. CAIXAS DE MÉTRICAS (VISUAL HUD) */
.admin-perf-group .metrica-box {
    background: rgba(255, 255, 255, 0.08) !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    padding: 0 10px !important;
    height: 32px !important;
    display: flex !important;
    align-items: center !important;
    font-size: 10px !important;
    font-weight: 700 !important;
    color: rgba(255, 255, 255, 0.7) !important;
    border-radius: 6px !important;
    white-space: nowrap !important;
    flex-shrink: 0 !important; /* Impede que a caixa encolha */
}

.admin-perf-group .metrica-box span {
    display: inline !important; 
    color: var(--hud-success) !important;
    margin-left: 4px !important;
}

/* 3. ESTADOS DE ALERTA (EX: PHP LENTO) */
.admin-perf-group .perf-alerta {
    border-color: var(--hud-danger) !important;
    background: rgba(231, 76, 60, 0.15) !important;
    animation: pulsePerformance 2s infinite;
}

.admin-perf-group .perf-alerta span {
    color: var(--hud-danger) !important;
}

/* 4. O PAINEL DE TELEMETRIA (GLASS MORPHISM ALTO CONTRASTE) */
.metrics-hub-panel {
    display: none; 
    position: fixed;
    top: 60px;
    right: 20px;
    width: 280px;
    
    /* Identidade Glass Negro conforme estilos_base.css.php */
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

/* 5. ELEMENTOS INTERNOS DO HUB */
.metrics-hub-panel .hub-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    padding-bottom: 12px;
    margin-bottom: 15px;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    color: var(--hud-success);
}

.metrics-hub-panel .hub-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    font-size: 12px;
}

.metrics-hub-panel .hub-row strong {
    color: rgba(255, 255, 255, 0.5);
    font-weight: 400;
}

.metrics-hub-panel .hub-row span {
    font-family: 'Fira Code', monospace;
    font-weight: 700;
}

/* 6. ANIMAÇÕES ESPECÍFICAS */
@keyframes pulsePerformance {
    0% { box-shadow: 0 0 0px rgba(231, 76, 60, 0); }
    50% { box-shadow: 0 0 12px rgba(231, 76, 60, 0.4); }
    100% { box-shadow: 0 0 0px rgba(231, 76, 60, 0); }
}
</style>