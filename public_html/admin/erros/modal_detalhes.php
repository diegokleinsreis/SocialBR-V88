<?php
/**
 * admin/erros/modal_detalhes.php
 * Componente: Modal de Detalhes Técnicos do Sentinela.
 * VERSÃO: 1.4 (Reversion: Estabilização de Rede - socialbr.lol)
 * PAPEL: Exibir detalhes de falhas de código (JS/Promises) capturadas.
 */
?>

<style>
    /* Estilos de compactação específicos para o modal de erros */
    #modalDetalhesErro .modal-body {
        font-size: 0.85rem;
    }
    #modalDetalhesErro .list-group-item {
        padding-top: 4px !important;
        padding-bottom: 4px !important;
    }
    #modalDetalhesErro .alert {
        padding: 0.75rem !important;
        margin-bottom: 1rem !important;
    }
    #modalDetalhesErro label {
        margin-bottom: 2px !important;
        font-size: 0.7rem !important;
    }
    #modalDetalhesErro pre {
        font-size: 0.7rem !important;
        max-height: 150px !important;
        white-space: pre-wrap;
        font-family: 'Consolas', 'Monaco', monospace;
        line-height: 1.1;
    }
</style>

<div class="modal fade" id="modalDetalhesErro" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            
            <div class="modal-header text-white py-2 px-3" style="background: #0C2D54;">
                <h6 class="modal-title d-flex align-items-center">
                    <i class="fas fa-bug me-2"></i>
                    <span>Monitor Sentinela: Detalhes Técnicos</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-2 p-md-3">
                
                <div class="alert border-0 bg-light d-flex align-items-center mb-2 shadow-sm py-2">
                    <i class="fas fa-exclamation-triangle fa-lg text-danger me-2"></i>
                    <div class="overflow-hidden">
                        <div class="fw-bold text-dark text-break" id="detalhe_mensagem" style="line-height: 1.2;">A carregar mensagem...</div>
                        <small class="text-muted d-block text-truncate" id="detalhe_localizacao" style="font-size: 0.7rem;">Arquivo: - na linha -</small>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-12 col-md-6">
                        <label class="text-muted fw-bold text-uppercase d-block">Contexto de Acesso</label>
                        <ul class="list-group list-group-flush border rounded">
                            <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                                <small class="text-muted">URL:</small>
                                <code class="text-primary small text-break ms-2" id="detalhe_url" style="max-width: 160px; text-align: right;">-</code>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                                <small class="text-muted">Utilizador:</small>
                                <span class="fw-bold" id="detalhe_utilizador">-</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                                <small class="text-muted">IP:</small>
                                <span class="badge bg-light text-dark border p-1" id="detalhe_ip" style="font-size: 0.7rem;">-</span>
                            </li>
                        </ul>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="text-muted fw-bold text-uppercase d-block">Informação de Sistema</label>
                        <ul class="list-group list-group-flush border rounded">
                            <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                                <small class="text-muted">Primeira vez:</small>
                                <small id="detalhe_criado" class="fw-medium text-dark">-</small>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                                <small class="text-muted">Última vez:</small>
                                <small id="detalhe_atualizado" class="fw-medium text-dark">-</small>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                                <small class="text-muted">Recorrência:</small>
                                <span class="badge bg-danger rounded-pill px-2" id="detalhe_ocorrencias">1x</span>
                            </li>
                        </ul>
                    </div>

                    <div class="col-12 mt-2">
                        <label class="text-muted fw-bold text-uppercase d-block">Stack Trace / Rastro de Execução</label>
                        <div class="bg-dark rounded p-2">
                            <pre class="text-info mb-0" id="detalhe_stack">Nenhum rastro disponível.</pre>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="text-muted fw-bold text-uppercase d-block">User Agent (Dispositivo)</label>
                        <div class="p-2 border rounded bg-light">
                            <small class="text-muted d-block text-break" id="detalhe_ua" style="font-size: 0.65rem; line-height: 1.1;">-</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light border-top py-2 px-3 flex-column flex-sm-row gap-2 justify-content-between">
                <div class="d-flex align-items-center w-100 w-sm-auto gap-2">
                    <select class="form-select form-select-sm" id="input_status_erro" style="min-width: 145px; font-size: 0.8rem;">
                        <option value="pendente">🔴 Pendente</option>
                        <option value="em_analise">🔵 Em Análise</option>
                        <option value="corrigido">✅ Corrigido</option>
                        <option value="ignorado">⚪ Ignorado</option>
                    </select>
                    <button type="button" class="btn btn-sm btn-primary flex-shrink-0 px-3" id="btnSalvarStatusErro">
                        Salvar
                    </button>
                </div>
                <button type="button" class="btn btn-sm btn-secondary w-100 w-sm-auto px-3" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>