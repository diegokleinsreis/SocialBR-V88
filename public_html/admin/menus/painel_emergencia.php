<?php
/**
 * admin/menus/painel_emergencia.php
 * Componente: Gestor do Sistema de Redundância (Para-quedas).
 * VERSÃO: 1.0 (Segurança Dinâmica - socialbr.lol)
 */
?>

<button class="btn btn-outline-warning shadow-sm fw-bold" 
        id="btnRegenerarBackup" 
        title="Atualizar ficheiro de emergência JSON">
    <i class="fas fa-sync-alt me-1"></i> 
    <span class="d-none d-md-inline">Sincronizar Para-quedas</span>
    <span class="d-inline d-md-none">Backup</span>
</button>

<div class="d-none">
    <div id="info-paraquedas-content">
        <div class="p-2 text-center">
            <i class="fas fa-shield-alt fa-3x mb-3 text-warning"></i>
            <h6 class="fw-bold">Sistema de Redundância</h6>
            <p class="small text-muted">
                Ao clicar em sincronizar, o sistema lê todas as rotas ativas do banco e 
                reescreve o ficheiro <code>config/emergencia.json</code>.
            </p>
            <hr>
            <p class="mb-0 small fw-bold text-primary">
                <i class="fas fa-info-circle me-1"></i> Use isto sempre que criar uma rota vital.
            </p>
        </div>
    </div>
</div>