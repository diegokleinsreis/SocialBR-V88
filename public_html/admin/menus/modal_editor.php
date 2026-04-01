<?php
/**
 * admin/menus/modal_editor.php
 * Componente: Modal Turbo de Configuração (Tabs + Real-time Icon Preview + File Validation + Agendamento).
 * VERSÃO: 2.4 (Event Mode Integration - socialbr.lol)
 */

// Busca menus que podem ser "Pais" (apenas itens de primeiro nível)
$sql_pais = "SELECT id, label FROM Menus_Sistema WHERE parent_id IS NULL ORDER BY label ASC";
$stmt_pais = $pdo->query($sql_pais);
$menus_pais = $stmt_pais->fetchAll();
?>

<style>
    /* Estilização das Abas */
    #modalEditorRota .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        border-bottom: 3px solid transparent;
        transition: 0.3s;
    }
    #modalEditorRota .nav-tabs .nav-link.active {
        color: #0C2D54;
        background: none;
        border-bottom-color: #0C2D54;
    }

    /* Preview do Ícone */
    #icon-preview-container {
        transition: all 0.3s ease;
        border: 2px solid #dee2e6 !important;
    }
    #icon-preview-container:hover {
        transform: scale(1.05);
        border-color: #0C2D54 !important;
        box-shadow: 0 5px 15px rgba(12, 45, 84, 0.1) !important;
    }

    /* Validação Visual (Dica de Ouro) */
    .is-invalid {
        border-color: #dc3545 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e") !important;
        background-repeat: no-repeat !important;
        background-position: right calc(0.375em + 0.1875rem) center !important;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem) !important;
    }
    .is-valid {
        border-color: #198754 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8' width='8' height='8' fill='%23198754'%3e%3cpath d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e") !important;
        background-repeat: no-repeat !important;
        background-position: right calc(0.375em + 0.1875rem) center !important;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem) !important;
    }

    /* Estilo do Agendamento (Modo Evento) */
    .scheduling-box {
        border-left: 4px solid #f1c40f;
        background-color: #fff9db;
    }

    /* Responsividade em telas pequenas */
    @media (max-width: 576px) {
        #modalEditorRota .modal-body { padding: 1.5rem !important; }
        #icon-preview-container { width: 80px !important; height: 80px !important; }
        #current_icon_display { font-size: 2rem !important; }
    }
</style>

<div class="modal fade" id="modalEditorRota" tabindex="-1" aria-labelledby="modalEditorRotaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            
            <div class="modal-header text-white border-0" style="background-color: #0C2D54;">
                <h5 class="modal-title" id="modalEditorRotaLabel">
                    <i class="fas fa-magic me-2"></i><span id="tituloModal">Configurar Módulo</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <ul class="nav nav-tabs nav-fill bg-light border-bottom" id="modalTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold py-3" id="basico-tab" data-bs-toggle="tab" data-bs-target="#tab-basico" type="button" role="tab">
                        <i class="fas fa-paint-brush me-1"></i> 1. Identidade Visual
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-3" id="tecnico-tab" data-bs-toggle="tab" data-bs-target="#tab-tecnico" type="button" role="tab">
                        <i class="fas fa-code me-1"></i> 2. Regras e Caminhos
                    </button>
                </li>
            </ul>

            <form id="formGerenciarRota">
                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="rota_id">
                    
                    <div class="tab-content" id="modalTabsContent">
                        
                        <div class="tab-pane fade show active" id="tab-basico" role="tabpanel">
                            <div class="row g-4 align-items-center">
                                
                                <div class="col-md-4 text-center border-end">
                                    <label class="form-label d-block fw-bold mb-3 text-primary">Preview do Ícone</label>
                                    <div id="icon-preview-container" class="mx-auto shadow-sm rounded-circle d-flex align-items-center justify-content-center border" 
                                         style="width: 110px; height: 110px; background: #ffffff;">
                                        <i id="current_icon_display" class="fas fa-icons fa-3x" style="color: #0C2D54;"></i>
                                    </div>
                                    <div class="mt-3 px-2">
                                        <label class="small fw-bold text-muted mb-1">Classe FontAwesome</label>
                                        <input type="text" name="icone" id="rota_icone" class="form-control form-control-sm text-center font-monospace" 
                                               placeholder="Ex: fas fa-rocket" value="fas fa-icons" required>
                                        <small class="text-primary d-block mt-2" style="font-size: 0.7rem;">
                                            <i class="fas fa-external-link-alt me-1"></i>Veja em 
                                            <a href="https://fontawesome.com/v6/search" target="_blank" class="fw-bold text-decoration-none">fontawesome.com</a>
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Nome no Menu</label>
                                        <input type="text" name="label" id="rota_label" class="form-control form-control-lg" placeholder="Ex: Central de Vendas" required>
                                        <small class="text-muted">Este é o texto que os usuários verão na barra lateral.</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Slug da URL</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">/</span>
                                            <input type="text" name="slug" id="rota_slug" class="form-control" placeholder="exemplo-pagina" required>
                                        </div>
                                        <small class="text-muted">O endereço amigável (ex: socialbr.lol/marketplace).</small>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="p-3 rounded mb-3" style="background-color: #f0f4f8;">
                                        <label class="form-label fw-bold"><i class="fas fa-level-down-alt me-2"></i>Hierarquia (Menu Pai)</label>
                                        <select name="parent_id" id="rota_parent_id" class="form-select">
                                            <option value="">Item Principal (Nível 0)</option>
                                            <?php foreach ($menus_pais as $pai): ?>
                                                <option value="<?php echo $pai['id']; ?>"><?php echo htmlspecialchars($pai['label']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="p-3 rounded scheduling-box shadow-sm border">
                                        <label class="form-label fw-bold text-dark"><i class="fas fa-clock me-2"></i>Modo Evento (Lançamento Agendado)</label>
                                        <input type="datetime-local" name="liberacao_em" id="rota_liberacao_em" class="form-control">
                                        <small class="text-muted d-block mt-1">
                                            Se preenchido, o módulo aparecerá bloqueado (cinza) com contagem regressiva. 
                                            <strong>Deixe vazio para liberação imediata.</strong>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-tecnico" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Destino Físico do Arquivo</label>
                                    <input type="text" name="arquivo_destino" id="rota_arquivo_destino" class="form-control font-monospace" placeholder="/views/modulo/index.php" required>
                                    <div class="invalid-feedback">Atenção Arquiteto: Este arquivo não foi localizado no servidor.</div>
                                    <div class="valid-feedback">Excelente: Arquivo localizado e pronto para conexão!</div>
                                    <small class="text-muted d-block mt-1">Caminho do arquivo PHP no servidor a partir da raiz.</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Privacidade / Permissão</label>
                                    <select name="permissao" id="rota_permissao" class="form-select">
                                        <option value="todos">Público (Livre)</option>
                                        <option value="logado">Logados (Membros)</option>
                                        <option value="admin">Restrito (Administração)</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Ordem Numérica</label>
                                    <input type="number" name="ordem" id="rota_ordem" class="form-control" value="0">
                                </div>

                                <div class="col-12 mt-4">
                                    <div class="card bg-light border-0">
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-3"><i class="fas fa-sliders-h me-2"></i>Interruptores de Controle</h6>
                                            <div class="row">
                                                <div class="col-md-6 border-end">
                                                    <div class="form-check form-switch mb-3">
                                                        <input class="form-check-input" type="checkbox" name="exibir_no_menu" id="rota_exibir_no_menu" value="1" checked>
                                                        <label class="form-check-label fw-bold">Visível no Menu</label>
                                                        <small class="d-block text-muted">Aparece na Sidebar lateral.</small>
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="permite_parametros" id="rota_permite_parametros" value="1">
                                                        <label class="form-check-label fw-bold">Aceita Variáveis (IDs)</label>
                                                        <small class="d-block text-muted">Permite URLs como /perfil/123.</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 ps-md-4">
                                                    <div class="form-check form-switch mb-3 text-danger">
                                                        <input class="form-check-input" type="checkbox" name="manutencao_modulo" id="rota_manutencao_modulo" value="1">
                                                        <label class="form-check-label fw-bold">Trancado (Manutenção)</label>
                                                        <small class="d-block text-muted">Apenas admins acessam agora.</small>
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="status" id="rota_status" value="1" checked>
                                                        <label class="form-check-label fw-bold">Status: Ativo</label>
                                                        <small class="d-block text-muted">Se desmarcado, a rota é ignorada.</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link text-muted text-decoration-none px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white px-5 shadow-sm fw-bold" style="background-color: #0C2D54;" id="btnSalvarRota">
                        <i class="fas fa-save me-1"></i> Gravar Módulo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>