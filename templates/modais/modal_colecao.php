<?php
/**
 * templates/modais/modal_colecao.php
 * Componente: Modal de Gestão de Coleções (Criar/Editar).
 * PAPEL: Fornecer interface para nomear coleções e definir privacidade.
 * VERSÃO: V71.8 (socialbr.lol)
 */
?>

<div id="modal-colecao-salvos" class="modal-container is-hidden">
    <div class="modal-overlay" onclick="fecharModalColecao()"></div>
    
    <div class="modal-content modal-small anim-scale-up">
        <header class="modal-header">
            <h3 id="modal-colecao-titulo" style="color: #0C2D54; display: flex; align-items: center; gap: 10px; padding: 20px;">
                <i class="fas fa-folder-plus"></i> Nova Coleção
            </h3>
            <button class="btn-close-modal" onclick="fecharModalColecao()" style="position: absolute; right: 20px; top: 20px; background: none; border: none; cursor: pointer; font-size: 1.2rem; color: #65676b;">
                <i class="fas fa-times"></i>
            </button>
        </header>

        <form id="form-gestao-colecao" onsubmit="processarFormColecao(event)">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            
            <input type="hidden" id="colecao-id-edicao" value="">
            <input type="hidden" id="modal-acao-tipo" value="criar">

            <div class="modal-body" style="padding: 0 20px 20px 20px;">
                <div class="form-group-premium">
                    <label for="colecao-nome">Nome da Coleção</label>
                    <input type="text" 
                           id="colecao-nome" 
                           name="nome" 
                           placeholder="Ex: Viagens, Ideias, Marketplace..." 
                           required 
                           maxlength="100"
                           autocomplete="off">
                </div>

                <div class="form-group-premium">
                    <label for="colecao-privacidade">Privacidade</label>
                    <div class="select-wrapper">
                        <select id="colecao-privacidade" name="privacidade">
                            <option value="privada" selected>Privada (Apenas eu)</option>
                            <option value="publica">Pública (Qualquer um com o link)</option>
                        </select>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <small class="form-help" style="color: #65676b; font-size: 0.8rem; display: block; margin-top: 5px;">
                        Você poderá alterar isso a qualquer momento.
                    </small>
                </div>

                <div id="alerta-exclusao-colecao" class="danger-zone-alert is-hidden">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p><strong>Atenção:</strong> Ao excluir esta coleção, todos os itens salvos dentro dela serão movidos para a pasta Geral.</p>
                </div>
            </div>

            <footer class="modal-footer" style="padding: 20px; background: #f8f9fa; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-cancelar" onclick="fecharModalColecao()" style="padding: 10px 20px; border: none; background: #e4e6eb; color: #4b4f56; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Cancelar
                </button>
                <button type="submit" id="btn-submit-colecao" class="primary-btn" style="padding: 10px 20px; border: none; background: #0C2D54; color: #fff; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Criar Coleção
                </button>
            </footer>
        </form>
    </div>
</div>

<style>
/* Estilos Específicos do Formulário de Coleções */
.form-group-premium {
    margin-bottom: 20px;
}
.form-group-premium label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #1c1e21;
    font-size: 0.9rem;
}
.form-group-premium input {
    width: 100%;
    padding: 12px;
    border: 1px solid #dddfe2;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.2s;
    box-sizing: border-box;
}
.form-group-premium input:focus {
    border-color: #0C2D54;
    outline: none;
    box-shadow: 0 0 0 2px rgba(12, 45, 84, 0.1);
}
.select-wrapper {
    position: relative;
}
.select-wrapper select {
    width: 100%;
    padding: 12px;
    border: 1px solid #dddfe2;
    border-radius: 8px;
    appearance: none;
    background: #fff;
    cursor: pointer;
    font-size: 1rem;
    box-sizing: border-box;
}
.select-wrapper i {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #0C2D54;
}
.danger-zone-alert {
    background: #fff5f5;
    border: 1px solid #feb2b2;
    padding: 12px;
    border-radius: 8px;
    display: flex;
    gap: 10px;
    align-items: flex-start;
    margin-top: 15px;
}
.danger-zone-alert i { color: #f56565; margin-top: 3px; }
.danger-zone-alert p { font-size: 0.85rem; color: #c53030; margin: 0; }
</style>