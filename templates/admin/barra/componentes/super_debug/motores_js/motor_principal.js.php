<?php
/**
 * FICHEIRO: motores_js/motor_principal.js.php
 * PAPEL: Núcleo de Gestão de UI e Camadas (Core Engine)
 * VERSÃO: 3.0 (Edição Modular & Resiliente)
 * RESPONSABILIDADE: Controlar a visibilidade dos hubs, gerir Z-Index e estados globais.
 */
?>
<script>
/**
 * 1. GESTÃO DE CAMADAS (Z-INDEX)
 * Garante que o painel clicado apareça sempre à frente.
 */
function focarPainel(idPainel) {
    const painel = document.getElementById(idPainel);
    if (painel) {
        window.SocialBR_HUD.camadaZ++;
        painel.style.zIndex = window.SocialBR_HUD.camadaZ;
    }
}

/**
 * 2. TOGGLES DE INTERFACE (AS FUNÇÕES RECUPERADAS)
 */

// Alternar o SQL Hub (Log de Consultas)
function toggleSQLHub() {
    const hub = document.getElementById('sql-hub-root');
    const metricas = document.getElementById('metrics-hub-root');
    const moderacao = document.getElementById('moderation-hub-root');
    
    if (!hub) {
        console.error("Erro: SQL Hub não encontrado no DOM (#sql-hub-root).");
        return;
    }

    const estaVisivel = hub.style.display === 'flex' || hub.style.display === 'block';
    
    if (estaVisivel) {
        hub.style.display = 'none';
    } else {
        // Esconde outros para foco total (opcional, mas recomendado para UX)
        if(metricas) metricas.style.display = 'none';
        if(moderacao) moderacao.style.display = 'none';
        
        hub.style.display = 'flex';
        focarPainel('sql-hub-root');
        console.log("HUD: SQL Hub Ativado.");
    }
}

// Alternar o Monitor de Performance (Métricas de CPU/RAM)
function toggleMetricsHub() {
    const hub = document.getElementById('metrics-hub-root');
    const sql = document.getElementById('sql-hub-root');
    const moderacao = document.getElementById('moderation-hub-root');

    if (!hub) {
        console.error("Erro: Metrics Hub não encontrado (#metrics-hub-root).");
        return;
    }

    const estaVisivel = hub.style.display === 'block';

    if (estaVisivel) {
        hub.style.display = 'none';
    } else {
        if(sql) sql.style.display = 'none';
        if(moderacao) moderacao.style.display = 'none';
        
        hub.style.display = 'block';
        focarPainel('metrics-hub-root');
        console.log("HUD: Monitor de Performance Ativado.");
    }
}

// Alternar o Painel de Moderação (Denúncias)
function toggleModerationHub() {
    const hub = document.getElementById('moderation-hub-root');
    const sql = document.getElementById('sql-hub-root');
    const metricas = document.getElementById('metrics-hub-root');

    if (!hub) {
        console.error("Erro: Moderation Hub não encontrado (#moderation-hub-root).");
        return;
    }

    const estaVisivel = hub.style.display === 'flex' || hub.style.display === 'block';

    if (estaVisivel) {
        hub.style.display = 'none';
    } else {
        if(sql) sql.style.display = 'none';
        if(metricas) metricas.style.display = 'none';
        
        hub.style.display = 'flex';
        focarPainel('moderation-hub-root');
        console.log("HUD: Painel de Moderação Ativado.");
    }
}

/**
 * 3. UTILITÁRIOS DE NAVEGAÇÃO
 */

// Fechar qualquer painel de debug
function closeDebugPanel() {
    const painel = document.getElementById('debug-panel-root');
    if (painel) painel.style.display = 'none';
}

// Gestão de Mudança de Visão (Admin Ver Como)
function handleVisionChange(select) {
    if(!select.value) return;
    select.style.opacity = '0.5';
    select.disabled = true;
    console.log("HUD: Alterando identidade de visão...");
    window.location.href = select.value;
}

/**
 * 4. EVENT DELEGATION (RESILIÊNCIA)
 * Garante que cliques no corpo da página fechem painéis se necessário, 
 * ou tratem cliques em elementos dinâmicos.
 */
document.addEventListener('keydown', function(e) {
    // Tecla ESC fecha todos os painéis abertos
    if (e.key === "Escape") {
        ['sql-hub-root', 'metrics-hub-root', 'moderation-hub-root', 'debug-panel-root'].forEach(id => {
            const el = document.getElementById(id);
            if(el) el.style.display = 'none';
        });
        console.log("HUD: Todos os painéis fechados via ESC.");
    }
});

</script>