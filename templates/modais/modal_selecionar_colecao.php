<?php
/**
 * templates/modais/modal_selecionar_colecao.php
 * Componente: Seletor de Destino para Itens Salvos.
 * PAPEL: Interface para o usuário escolher em qual pasta salvar um post.
 * VERSÃO: V71.9 (socialbr.lol)
 */

// 1. Dependências de Lógica (Garante acesso às coleções)
require_once __DIR__ . '/../../src/SalvosLogic.php';
$salvosLogicModal = new SalvosLogic($pdo); 
$userIdModal = $_SESSION['user_id'] ?? 0;
$colecoesModal = ($userIdModal > 0) ? $salvosLogicModal->listarColecoes($userIdModal) : [];
?>

<div id="modal-selecionar-colecao" class="modal-container is-hidden">
    <div class="modal-overlay" onclick="fecharModalSelecao()"></div>
    
    <div class="modal-content modal-small anim-scale-up">
        <header class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid #f0f2f5;">
            <h3 style="color: #0C2D54; margin: 0; font-size: 1.1rem; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-folder-plus"></i> Salvar em...
            </h3>
            <button class="btn-close-modal" onclick="fecharModalSelecao()" style="background: none; border: none; cursor: pointer; color: #65676b; font-size: 1.1rem;">
                <i class="fas fa-times"></i>
            </button>
        </header>

        <div class="modal-body p-0">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            
            <input type="hidden" id="post-id-para-salvar" value="">

            <div class="selection-list-container">
                <ul class="selection-collection-list">
                    <?php foreach ($colecoesModal as $col): ?>
                        <li class="selection-item" onclick="executarSalvamento(<?php echo $col['id']; ?>)">
                            <div class="selection-item-info">
                                <i class="fas fa-folder"></i>
                                <span class="selection-name"><?php echo htmlspecialchars($col['nome']); ?></span>
                            </div>
                            <span class="selection-count"><?php echo $col['total_itens']; ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="selection-footer-action">
                <button type="button" class="btn-new-folder-shortcut" onclick="abrirModalCriarColecaoRapida()">
                    <i class="fas fa-plus-circle"></i> Criar nova coleção
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilização do Seletor de Coleções (Design de Luxo) */
.selection-list-container {
    max-height: 300px;
    overflow-y: auto;
}

.selection-collection-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.selection-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #f0f2f5;
    cursor: pointer;
    transition: background 0.2s;
}

.selection-item:hover {
    background: #f8f9fa;
}

.selection-item:last-child {
    border-bottom: none;
}

.selection-item-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.selection-item-info i {
    color: #0C2D54;
    font-size: 1.1rem;
    opacity: 0.7;
}

.selection-name {
    font-weight: 600;
    color: #1c1e21;
    font-size: 0.95rem;
}

.selection-count {
    font-size: 0.75rem;
    background: #e4e6eb;
    color: #65676b;
    padding: 2px 8px;
    border-radius: 10px;
}

.selection-footer-action {
    padding: 15px;
    border-top: 1px solid #f0f2f5;
    background: #fdfdfd;
    text-align: center;
}

.btn-new-folder-shortcut {
    background: none;
    border: none;
    color: #0C2D54;
    font-weight: 700;
    font-size: 0.9rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 10px;
    border-radius: 8px;
    transition: background 0.2s;
}

.btn-new-folder-shortcut:hover {
    background: rgba(12, 45, 84, 0.05);
}

.p-0 { padding: 0 !important; }
</style>