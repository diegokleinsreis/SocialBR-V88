<?php
/**
 * FICHEIRO: estilos_css/estilos_base.css.php
 * PAPEL: Fundação Visual Glass & Blindagem de Layout HUD
 * VERSÃO: 2.3 (Correção do Seletor de Visão & Mobile-First)
 * INTEGRIDADE: Completo e Integral.
 */
?>
<style>
:root {
    /* 1. PALETA DE CORES HUD (Fiel à Constituição) */
    --hud-glass-bg: rgba(10, 10, 10, 0.85); 
    --hud-glass-border: rgba(255, 255, 255, 0.25); 
    --hud-glass-shadow: 0 15px 45px rgba(0, 0, 0, 0.7);
    --hud-glass-blur: blur(12px) saturate(180%); 
    
    --hud-accent: #f1c40f;   /* Amarelo (Debug/Simulador) */
    --hud-success: #2ecc71;  /* Verde (Performance/Auto-fix) */
    --hud-danger: #e74c3c;   /* Vermelho (SQL Alerta/Master Mode) */
    --hud-text: #ffffff;
    
    --hud-radius: 12px;
    --hud-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* 2. CLASSE MESTRE: ALINHAMENTO HORIZONTAL (Blindagem contra empilhamento) */
.admin-atom-group {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    gap: 12px !important;
    transition: var(--hud-transition);
    background: transparent !important;
}

/* 3. CLASSE MESTRE: EFEITO VIDRO (GLASS MORPHISM) */
.hud-glass-effect {
    background: var(--hud-glass-bg) !important;
    border-radius: var(--hud-radius) !important;
    box-shadow: var(--hud-glass-shadow) !important;
    backdrop-filter: var(--hud-glass-blur) !important;
    -webkit-backdrop-filter: var(--hud-glass-blur) !important;
    border: 1px solid var(--hud-glass-border) !important;
}

/* 4. PADRONIZAÇÃO UNIVERSAL DE BOTÕES DA BARRA */
.admin-atom-group .btn-super-debug,
.admin-atom-group .btn-admin-atalho,
.admin-atom-group .metrica-box,
.admin-atom-group .btn-sql-debug,
.admin-atom-group .tag-denuncia {
    height: 32px !important;
    width: 38px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 8px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    color: var(--hud-text) !important;
    font-size: 14px !important;
    cursor: pointer !important;
    transition: var(--hud-transition) !important;
    text-decoration: none !important;
    position: relative !important;
    flex-shrink: 0 !important;
}

/* Feedback visual de Hover */
.admin-atom-group button:hover, 
.admin-atom-group a:hover {
    background: rgba(255, 255, 255, 0.2) !important;
    border-color: rgba(255, 255, 255, 0.5) !important;
    transform: translateY(-2px);
}

/* 5. BASE PARA PAINÉIS FLUTUANTES (FLYOUTS) */
.metrics-hub-panel, 
.debug-filters-panel, 
.moderation-hub-panel, 
.sql-hub-panel {
    display: none;
    position: fixed;
    top: 60px;
    padding: 20px;
    z-index: 1000000;
    color: var(--hud-text) !important;
    background: var(--hud-glass-bg) !important;
    border-radius: 16px !important;
    box-shadow: var(--hud-glass-shadow) !important;
    backdrop-filter: var(--hud-glass-blur) !important;
    -webkit-backdrop-filter: var(--hud-glass-blur) !important;
    border: 1px solid var(--hud-glass-border) !important;
    animation: hudSlideDown 0.3s ease-out;
}

/* 6. ESTILOS DO SELETOR DE VISÃO (Correção de Vazamento) */
.admin-visao-wrapper {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    padding: 0 10px !important;
    border-radius: 8px !important;
    height: 32px !important; /* Mesma altura dos botões */
    transition: var(--hud-transition);
}

.admin-label-visao {
    font-size: 10px !important;
    color: rgba(255, 255, 255, 0.5) !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
    white-space: nowrap !important;
}

.admin-id-badge {
    background: var(--hud-accent) !important;
    color: #000 !important;
    font-size: 9px !important;
    font-weight: 900 !important;
    padding: 2px 6px !important;
    border-radius: 4px !important;
    line-height: 1 !important;
}

.hud-glass-select {
    background: transparent !important;
    border: none !important;
    color: #fff !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    outline: none !important;
    padding: 0 5px !important;
}

/* Ajuste para remover estilos nativos do navegador no select */
.hud-glass-select option {
    background: #111 !important;
    color: #fff !important;
}

/* 7. RESPONSIVIDADE ATÓMICA */
@media (max-width: 768px) {
    .admin-label-visao { display: none !important; } /* Esconde o texto no telemóvel */
    .admin-atom-group { gap: 8px !important; }
    .admin-visao-wrapper { padding: 0 5px !important; gap: 5px !important; }
}

@keyframes hudSlideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Esconder labels por padrão */
.btn-label, .btn-super-debug span:not(.fas), .metrica-box span {
    display: none !important;
}
</style>