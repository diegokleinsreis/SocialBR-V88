<?php
/**
 * views/marketplace/componentes/form_editar.php
 * Versão: 2.3 - FINAL (Fix URL Fotos, Sincronização SQL e Blindagem de Categoria)
 * Nota: Os dados vêm da variável $dados injetada pela view editar.php
 */

if (!isset($id_usuario_logado) || !isset($dados)) exit;
?>

<div class="mkt-form-container">
    
    <div class="form-section">
        <h3 class="form-title"><i class="fas fa-images"></i> Fotos do Anúncio</h3>
        
        <div class="current-photos-label" style="font-size: 0.85rem; font-weight: 700; color: #65676b; margin-bottom: 10px;">
            Fotos Publicadas:
        </div>
        
        <div class="miniatures-grid" style="margin-bottom: 20px;">
            <?php if (!empty($dados['fotos'])): ?>
                <?php foreach ($dados['fotos'] as $foto): ?>
                    <div class="mkt-miniature-item" id="photo-wrapper-<?php echo $foto['id']; ?>">
                        <?php 
                            // Blindagem de Caminho: Remove referências físicas do servidor
                            $url_exibicao = $foto['url_midia'];
                            if (strpos($url_exibicao, 'public_html/') !== false) {
                                $partes = explode('public_html/', $url_exibicao);
                                $url_exibicao = $partes[1];
                            }
                        ?>
                        <img src="<?php echo $config['base_path'] . $url_exibicao; ?>" class="miniature-img-form">
                        <button type="button" class="btn-remove-photo" onclick="marcarDelecao(<?php echo $foto['id']; ?>)" title="Remover esta foto">
                            &times;
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="font-size:0.85rem; color:#65676b;">Este anúncio não possui fotos no momento.</p>
            <?php endif; ?>
        </div>

        <div class="current-photos-label" style="font-size: 0.85rem; font-weight: 700; color: #65676b; margin-bottom: 10px; margin-top: 20px;">
            Adicionar Novas Fotos:
        </div>
        <div class="upload-area" id="drop-zone-edit" onclick="document.getElementById('inputFotosEdit').click()">
            <div class="upload-icon-wrapper">
                <i class="fas fa-plus-circle"></i>
            </div>
            <p style="font-weight:700; margin-bottom:5px; color:#1c1e21;">Clique ou arraste novas fotos</p>
            <p class="upload-hint" style="font-size:0.8rem; color:#65676b;">Suporta até 10 fotos no total.</p>
            <input type="file" name="fotos[]" id="inputFotosEdit" multiple accept="image/*" class="d-none">
        </div>
        <div id="preview-miniatures-new" class="miniatures-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 10px; margin-top: 15px;"></div>
    </div>

    <div class="form-section">
        <h3 class="form-title"><i class="fas fa-edit"></i> Informações do Item</h3>
        
        <div class="form-group">
            <label for="inputTitulo">Título do Anúncio</label>
            <input type="text" name="titulo" id="inputTitulo" class="form-control" 
                   value="<?php echo htmlspecialchars($dados['titulo_produto']); ?>" required maxlength="100">
        </div>

        <div class="form-group">
            <label for="inputPreco">Preço (R$)</label>
            <input type="text" name="preco" id="inputPreco" class="form-control" 
                   value="<?php echo $dados['preco_input']; ?>" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label for="inputCategoria">Categoria</label>
                <select name="categoria" id="inputCategoria" class="form-control" required>
                    <?php foreach ($configMkt['categorias'] as $slug => $item_cat): ?>
                        <option value="<?php echo $slug; ?>" <?php echo ($dados['categoria'] == $slug) ? 'selected' : ''; ?>>
                            <?php 
                            // Correção definitiva para "Array to string conversion"
                            echo is_array($item_cat) ? ($item_cat['label'] ?? $slug) : $item_cat; 
                            ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="inputCondicao">Condição</label>
                <select name="condicao" id="inputCondicao" class="form-control" required>
                    <option value="novo" <?php echo ($dados['condicao'] == 'novo') ? 'selected' : ''; ?>>Novo (Lacrado)</option>
                    <option value="usado_bom" <?php echo ($dados['condicao'] == 'usado_bom') ? 'selected' : ''; ?>>Usado (Bom estado)</option>
                    <option value="usado_marcas" <?php echo ($dados['condicao'] == 'usado_marcas') ? 'selected' : ''; ?>>Usado (Marcas de uso)</option>
                    <option value="defeito" <?php echo ($dados['condicao'] == 'defeito') ? 'selected' : ''; ?>>Com defeito / Peças</option>
                </select>
            </div>
        </div>

        <div class="form-group" style="margin-top: 15px;">
            <label for="inputDescricao">Descrição Detalhada</label>
            <textarea name="descricao" id="inputDescricao" class="form-control" 
                      placeholder="Detalhes sobre o estado, tempo de uso, etc..." required><?php echo htmlspecialchars($dados['descricao_completa']); ?></textarea>
        </div>
    </div>

    <div class="form-section">
        <h3 class="form-title"><i class="fas fa-map-marker-alt"></i> Localização do Produto</h3>
        <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 0 0 80px;">
                <label for="inputEstado">UF</label>
                <select name="estado" id=\"inputEstado\" class=\"form-control\" required>
                    <?php foreach ($configMkt['estados'] as $sigla => $nome): ?>
                        <option value="<?php echo $sigla; ?>" <?php echo ($dados['estado'] == $sigla) ? 'selected' : ''; ?>>
                            <?php echo $sigla; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="inputCidade">Cidade</label>
                <input type="text" name="cidade" id="inputCidade" class="form-control" 
                       value="<?php echo htmlspecialchars($dados['cidade']); ?>" required>
            </div>
        </div>
    </div>

    <input type="hidden" name="fotos_remover" id="inputFotosRemover" value="">

</div>

<script>
    /**
     * Lógica para marcar fotos existentes para deleção física na API
     */
    let fotosParaRemoverArray = [];
    
    function marcarDelecao(id) {
        if(!confirm('Deseja realmente remover esta foto? Ela será excluída permanentemente ao clicar em \"Salvar Alterações\".')) return;
        
        // Adiciona ao array de controle
        fotosParaRemoverArray.push(id);
        
        // Atualiza o input que será enviado via POST para a API
        document.getElementById('inputFotosRemover').value = fotosParaRemoverArray.join(',');
        
        // Feedback visual imediato: esconde o item
        const wrapper = document.getElementById('photo-wrapper-' + id);
        if(wrapper) {
            wrapper.style.transition = 'all 0.3s ease';
            wrapper.style.opacity = '0';
            wrapper.style.transform = 'scale(0.8)';
            wrapper.style.pointerEvents = 'none';
            setTimeout(() => {
                wrapper.style.display = 'none';
            }, 300);
        }
    }
</script>