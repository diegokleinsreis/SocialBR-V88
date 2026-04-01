<?php
/**
 * FICHEIRO: motores_js/motor_sql.js.php
 * PAPEL: Inteligência de Auditoria SQL (Database Engine)
 * VERSÃO: 3.0 (Edição Modular em Português)
 * RESPONSABILIDADE: Gerir abas de log, limpeza de histórico e monitorização de queries.
 */
?>
<script>
/**
 * 1. ALTERNAR ABAS DO SQL HUB
 * Controla a exibição entre "Live Queries" e "Auditoria de Logs".
 */
function switchSQLTab(tabName) {
    // Seletores de botões e conteúdos
    const botoes = document.querySelectorAll('.sql-tab-btn');
    const conteudos = document.querySelectorAll('.sql-tab-content');

    // Remove estado ativo de todos
    botoes.forEach(btn => btn.classList.remove('active'));
    conteudos.forEach(content => content.classList.remove('active'));

    // Ativa o alvo selecionado
    if (tabName === 'live') {
        const btnLive = document.getElementById('btn-tab-live');
        const contentLive = document.getElementById('sql-tab-live');
        
        if (btnLive) btnLive.classList.add('active');
        if (contentLive) contentLive.classList.add('active');
        
        console.log("SQL Hub: Mudado para Visualização em Tempo Real.");
    } else {
        const btnAudit = document.getElementById('btn-tab-audit');
        const contentAudit = document.getElementById('sql-tab-audit');
        
        if (btnAudit) btnAudit.classList.add('active');
        if (contentAudit) contentAudit.classList.add('active');
        
        console.log("SQL Hub: Mudado para Auditoria de Histórico.");
    }
}

/**
 * 2. LIMPEZA DE LOGS DE AUDITORIA
 * Comunica com a API para apagar o ficheiro sql_audit.log de forma segura.
 */
async function clearSQLAuditLog() {
    const btn = document.querySelector('.btn-clear-log');
    
    // Confirmação de segurança
    if (!confirm("Atenção Arquiteto: Deseja realmente apagar todo o histórico de auditoria SQL? Esta ação é irreversível.")) {
        return;
    }

    // Captura o Token CSRF para proteção contra ataques
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const originalText = btn ? btn.innerHTML : 'Limpar';

    // Feedback visual de carregamento
    if (btn) {
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> A processar...';
        btn.disabled = true;
    }

    try {
        // Chamada à API (Usando o caminho robusto conforme a Constituição)
        const response = await fetch('/~klscom/api/admin/limpar_sql_audit.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken 
            }
        });

        const result = await response.json();

        if (result.success) {
            console.log("SQL Hub: Histórico de auditoria limpo com sucesso.");
            if (btn) {
                btn.style.background = "#2ecc71";
                btn.innerHTML = '<i class="fas fa-check"></i> Limpo!';
            }
            
            // Recarrega para atualizar a lista de logs (agora vazia)
            setTimeout(() => { 
                location.reload(); 
            }, 800);
        } else {
            console.error("SQL Hub Erro:", result.error || "Erro desconhecido");
            alert("Bloqueio de Segurança: " + (result.error || "Token inválido ou sem permissão."));
            
            if (btn) {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }
    } catch (error) {
        console.error("Erro crítico na API de segurança SQL:", error);
        alert("Erro de conexão: Não foi possível comunicar com a API de auditoria.");
        
        if (btn) {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
}

/**
 * 3. AUTO-UPDATE (OPCIONAL)
 * Futura implementação para atualizar o log live via AJAX sem refresh.
 */
function refreshSQLLive() {
    // Lógica para carregar novas queries sem recarregar a página
}
</script>