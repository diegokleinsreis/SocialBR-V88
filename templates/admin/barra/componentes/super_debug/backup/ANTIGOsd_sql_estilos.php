/* 0. SISTEMA DE DESIGN SQL LOGGER (V1.5 - Unified Hub Edition) 
   PAPEL: Estilização do HUD de Banco de Dados e Auditoria AJAX.
   VERSÃO: 1.5 (Tabbed Interface & Neon Highlights)
*/

/* 1. O ÍCONE GATILHO NA BARRA */
.btn-sql-debug {
    position: relative;
    transition: var(--sd-transition);
}

.btn-sql-debug .sql-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: var(--sd-accent);
    color: #000;
    font-size: 8px;
    font-weight: 900;
    padding: 1px 4px;
    border-radius: 10px;
    border: 1px solid #000;
}

/* Estado de Alerta no Ícone */
.btn-sql-alert {
    border-color: var(--sd-danger) !important;
    color: var(--sd-danger) !important;
    box-shadow: 0 0 10px rgba(231, 76, 60, 0.4);
    animation: sqlPulse 2s infinite;
}

/* 2. O PAINEL HUB (UNIFICADO) */
.sql-hub-panel {
    display: none;
    position: fixed;
    top: 60px;
    left: 20px;
    width: 550px; /* Aumentado para acomodar a auditoria */
    max-height: 85vh;
    background: var(--sd-bg-glass);
    backdrop-filter: blur(25px);
    -webkit-backdrop-filter: blur(25px);
    border: 1px solid var(--sd-border);
    border-radius: var(--sd-radius);
    padding: 0; /* Removido padding para controle das abas */
    z-index: var(--sd-z-index);
    box-shadow: var(--sd-shadow);
    color: #fff;
    flex-direction: column;
    animation: hubFadeIn 0.3s ease-out;
    overflow: hidden;
}

/* 3. SISTEMA DE ABAS (TABS) */
.sql-nav-tabs {
    display: flex;
    background: rgba(0,0,0,0.3);
    border-bottom: 1px solid var(--sd-border);
    padding: 0 10px;
}

.sql-tab-btn {
    background: transparent;
    border: none;
    color: #888;
    padding: 12px 15px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.2s;
    border-bottom: 2px solid transparent;
}

.sql-tab-btn:hover { color: #fff; }

.sql-tab-btn.active {
    color: var(--sd-accent);
    border-bottom-color: var(--sd-accent);
}

/* 4. CONTEÚDO E SCROLL */
.sql-tab-content {
    display: none;
    padding: 15px;
    flex-direction: column;
}

.sql-tab-content.active { display: flex; }

.sql-list-scroll {
    overflow-y: auto;
    overflow-x: hidden;
    max-height: 60vh;
    padding-right: 5px;
}

.sql-list-scroll::-webkit-scrollbar { width: 4px; }
.sql-list-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }

/* 5. TABELA DE QUERIES E AUDITORIA */
.sql-debug-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 10px;
}

.sql-debug-table th {
    text-align: left;
    color: #666;
    padding: 8px;
    border-bottom: 1px solid var(--sd-border);
    text-transform: uppercase;
    font-size: 9px;
}

.sql-debug-table td {
    padding: 8px;
    vertical-align: top;
    border-bottom: 1px solid rgba(255,255,255,0.03);
}

.sql-time {
    font-weight: 800;
    color: var(--sd-accent);
    white-space: nowrap;
}

.sql-code code {
    display: block;
    font-family: 'Fira Code', monospace;
    color: #ccc;
    line-height: 1.4;
    word-break: break-all;
    background: rgba(0,0,0,0.2);
    padding: 5px;
    border-radius: 4px;
}

/* 6. DESTAQUES NEON (Dica de Ouro) */
.log-critical {
    background: rgba(231, 76, 60, 0.1) !important;
}

.log-critical .status-tag {
    color: #ff3131;
    text-shadow: 0 0 8px rgba(255, 49, 49, 0.6);
    font-weight: 900;
}

.sql-alert-tag {
    display: inline-block;
    margin-top: 5px;
    background: rgba(231, 76, 60, 0.1);
    color: var(--sd-danger);
    padding: 2px 6px;
    border-radius: 3px;
    border: 1px solid var(--sd-danger);
    font-size: 8px;
    font-weight: 800;
}

/* 7. BARRA DE FERRAMENTAS DO LOG */
.sql-log-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    background: rgba(255,255,255,0.03);
    padding: 8px;
    border-radius: 4px;
}

.btn-clear-log {
    background: rgba(231, 76, 60, 0.2);
    color: #ff7675;
    border: 1px solid rgba(231, 76, 60, 0.3);
    padding: 4px 8px;
    font-size: 9px;
    border-radius: 3px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-clear-log:hover {
    background: var(--sd-danger);
    color: #fff;
}

/* ANIMAÇÕES */
@keyframes sqlPulse {
    0% { box-shadow: 0 0 5px rgba(231, 76, 60, 0.2); }
    50% { box-shadow: 0 0 15px rgba(231, 76, 60, 0.5); }
    100% { box-shadow: 0 0 5px rgba(231, 76, 60, 0.2); }
}

@keyframes hubFadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}