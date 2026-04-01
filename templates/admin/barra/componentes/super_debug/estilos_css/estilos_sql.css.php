<?php
/**
 * FICHEIRO: estilos_css/estilos_sql.css.php
 * PAPEL: Estética Glass para o SQL Hub (Database UI)
 * VERSÃO: 2.0 (Glass Edition)
 * RESPONSABILIDADE: Estilizar o log de queries, abas e auditoria com efeito de transparência.
 * INTEGRIDADE: Completo e Integral.
 */
?>
<style>
/* 1. O PAINEL SQL (Efeito Glass Reforçado) */
.sql-hub-panel {
    display: none;
    position: fixed;
    top: 60px;
    left: 20px;
    width: 600px; /* Largura otimizada para leitura de código SQL */
    max-height: 80vh;
    
    /* Aplicação da Identidade Visual Glass */
    background: rgba(10, 10, 10, 0.85) !important;
    backdrop-filter: blur(15px) saturate(180%) !important;
    -webkit-backdrop-filter: blur(15px) saturate(180%) !important;
    border: 1px solid var(--hud-glass-border) !important;
    border-radius: 16px !important;
    box-shadow: var(--hud-glass-shadow) !important;
    
    color: var(--hud-text);
    z-index: 1000000;
    flex-direction: column;
    overflow: hidden;
    animation: hudSlideDown 0.3s ease-out;
}

/* 2. SISTEMA DE ABAS SQL */
.sql-nav-tabs {
    display: flex;
    background: rgba(255, 255, 255, 0.05);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 0 10px;
}

.sql-tab-btn {
    background: transparent;
    border: none;
    color: rgba(255, 255, 255, 0.5);
    padding: 12px 20px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    cursor: pointer;
    transition: var(--hud-transition);
    border-bottom: 3px solid transparent;
}

.sql-tab-btn:hover { color: #fff; background: rgba(255,255,255,0.05); }

.sql-tab-btn.active {
    color: var(--hud-accent);
    border-bottom-color: var(--hud-accent);
}

/* 3. LISTA DE QUERIES E SCROLL */
.sql-tab-content {
    display: none;
    padding: 20px;
    flex-direction: column;
}

.sql-tab-content.active { display: flex; }

.sql-list-scroll {
    overflow-y: auto;
    max-height: 55vh;
    padding-right: 10px;
}

.sql-list-scroll::-webkit-scrollbar { width: 4px; }
.sql-list-scroll::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); border-radius: 10px; }

/* 4. TABELA DE AUDITORIA E CÓDIGO */
.sql-debug-table {
    width: 100%;
    border-collapse: collapse;
}

.sql-debug-table th {
    text-align: left;
    color: rgba(255, 255, 255, 0.4);
    font-size: 9px;
    text-transform: uppercase;
    padding: 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sql-debug-table td {
    padding: 12px 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.sql-time {
    color: var(--hud-accent);
    font-weight: 800;
    font-family: 'Fira Code', monospace;
    font-size: 11px;
}

.sql-code code {
    display: block;
    background: rgba(0, 0, 0, 0.4);
    padding: 10px;
    border-radius: 8px;
    color: #81ecec; /* Ciano para destaque do SQL */
    font-family: 'Fira Code', monospace;
    font-size: 11px;
    line-height: 1.5;
    word-break: break-all;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

/* 5. BARRA DE FERRAMENTAS (LIMPEZA) */
.sql-log-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(0, 0, 0, 0.3);
    padding: 12px 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-clear-log {
    background: rgba(231, 76, 60, 0.2);
    color: var(--hud-danger);
    border: 1px solid var(--hud-danger);
    padding: 6px 12px;
    font-size: 10px;
    font-weight: 800;
    border-radius: 6px;
    cursor: pointer;
    transition: var(--hud-transition);
}

.btn-clear-log:hover {
    background: var(--hud-danger);
    color: #fff;
    box-shadow: 0 0 15px rgba(231, 76, 60, 0.4);
}
</style>