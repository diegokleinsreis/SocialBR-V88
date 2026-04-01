<?php
/**
 * views/salvos/componentes/renderizador_lista.php
 * Componente Atómico: Renderizador de Resultados.
 * PAPEL: Executar a busca na Logic e iterar sobre os itens salvos.
 * VERSÃO: V70.4 (socialbr.lol)
 */

// 1. Preparação dos Filtros (Variáveis já higienizadas no home.php)
$filtros_busca = [
    'colecao_id' => $colecao_id,
    'tipo'       => $filtro_tipo,
    'busca'      => $busca_termo
];

// 2. Chamada ao Cérebro (Logic)
// O objeto $salvosLogic e $usuario_id são herdados do esqueleto home.php
$itens_salvos = $salvosLogic->getItensSalvos($usuario_id, $filtros_busca);

// 3. Renderização Condicional
if (empty($itens_salvos)): ?>
    
    <div class="saved-empty-state">
        <div class="empty-icon">
            <i class="fas fa-bookmark"></i>
        </div>
        <h3>Nenhum item encontrado</h3>
        <p>
            <?php if (!empty($busca_termo)): ?>
                Não encontrámos nada para "<strong><?php echo htmlspecialchars($busca_termo); ?></strong>" nesta categoria.
            <?php else: ?>
                Parece que ainda não guardou nada nesta coleção ou categoria.
            <?php endif; ?>
        </p>
        <a href="<?php echo $config['base_path']; ?>feed" class="secondary-btn">
            Explorar o Feed
        </a>
    </div>

<?php else: ?>

    <div class="saved-items-list">
        <?php 
        foreach ($itens_salvos as $item): 
            // Componente Atómico: O Card Individual (Estilo Facebook)
            // Passamos o array $item para dentro do componente
            include __DIR__ . '/card_item_salvo.php'; 
        endforeach; 
        ?>
    </div>

    <div class="saved-list-footer">
        <p><i class="fas fa-check-circle"></i> Todos os itens foram carregados.</p>
    </div>

<?php endif; ?>

<style>
.saved-empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border-radius: 12px;
    border: 2px dashed #e0e0e0;
    margin-top: 20px;
}
.saved-empty-state .empty-icon {
    font-size: 3.5rem;
    color: #0C2D54; /* Cor Oficial */
    opacity: 0.2;
    margin-bottom: 20px;
}
.saved-empty-state h3 {
    font-size: 1.4rem;
    color: #333;
    margin-bottom: 10px;
}
.saved-empty-state p {
    color: #666;
    margin-bottom: 25px;
}
.saved-list-footer {
    text-align: center;
    padding: 30px;
    color: #999;
    font-size: 0.9rem;
}
</style>