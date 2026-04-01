<?php
/**
 * views/marketplace/componentes/form_criar.php
 * Componente de Formulário (V4.4 - Sincronização de ENUM com Banco de Dados)
 * Nota: As tags <form> e o botão de submit foram movidos para o arquivo pai (criar.php)
 */

if (!isset($id_usuario_logado)) exit;
?>

<div class="mkt-form-container">
    
    <div class="form-section">
        <h3 class="form-title"><i class="fas fa-camera"></i> Fotos do Produto</h3>
        <div class="upload-area" id="drop-zone">
            <div class="upload-icon-wrapper">
                <i class="fas fa-images"></i>
            </div>
            <p style="font-weight:700; margin-bottom:5px; color:#1c1e21;">Adicionar fotos</p>
            <p class="upload-hint" style="font-size:0.8rem; color:#65676b;">Pode arrastar ou clicar para selecionar até 10 fotos.</p>
            <input type="file" name="fotos[]" id="inputFotos" multiple accept="image/*" class="d-none">
        </div>
        <div id="preview-miniatures" class="miniatures-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 10px; margin-top: 15px;"></div>
    </div>

    <div class="form-section">
        <h3 class="form-title"><i class="fas fa-tag"></i> Sobre o Item</h3>
        
        <div class="form-group">
            <label for="inputTitulo">Título do Anúncio</label>
            <input type="text" name="titulo" id="inputTitulo" class="form-control" 
                   placeholder="Ex: iPhone 13 Pro Max 256GB" required maxlength="100">
        </div>

        <div class="form-group">
            <label for="inputPreco">Preço (R$)</label>
            <input type="text" name="preco" id="inputPreco" class="form-control" 
                   placeholder="0,00" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label for="inputCategoria">Categoria</label>
                <select name="categoria" id="inputCategoria" class="form-control" required>
                    <option value="">Selecionar...</option>
                    <?php foreach ($configMkt['categorias'] as $slug => $item_cat): ?>
                        <option value="<?php echo $slug; ?>">
                            <?php 
                            // FIX: Verifica se é array (com ícone) ou string (legado) para evitar erro de conversão
                            echo is_array($item_cat) ? ($item_cat['label'] ?? $slug) : $item_cat; 
                            ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="inputCondicao">Condição</label>
                <select name="condicao" id="inputCondicao" class="form-control" required>
                    <option value="">Selecionar...</option>
                    <option value="novo">Novo</option>
                    <option value="usado_bom">Usado (Bom estado)</option>
                    <option value="usado_marcas">Usado (Marcas de uso)</option>
                    <option value="defeito">Com defeito / Peças</option>
                </select>
            </div>
        </div>

        <div class="form-group" style="margin-top: 15px;">
            <label for="inputDescricao">Descrição Detalhada</label>
            <textarea name="descricao" id="inputDescricao" class="form-control" 
                      placeholder="Descreva detalhes como tempo de uso, defeitos ou acessórios inclusos..." required></textarea>
        </div>
    </div>

    <div class="form-section">
        <h3 class="form-title"><i class="fas fa-map-marker-alt"></i> Onde o item está?</h3>
        <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 0 0 80px;">
                <label for="inputEstado">UF</label>
                <select name="estado" id="inputEstado" class="form-control" required>
                    <option value="">--</option>
                    <?php foreach ($configMkt['estados'] as $sigla => $nome): ?>
                        <option value="<?php echo $sigla; ?>"><?php echo $sigla; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="inputCidade">Cidade</label>
                <input type="text" name="cidade" id="inputCidade" class="form-control" 
                       placeholder="Ex: São Paulo" required>
            </div>
        </div>
    </div>

    <?php if ($precisa_cpf): ?>
    <div class="security-alert-box" style="background: #fff9db; border: 1px solid #ffec99; padding: 15px; border-radius: 8px; margin-top: 10px;">
        <div style="font-weight: 700; color: #856404; margin-bottom: 5px; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-shield-alt"></i> Verificação de Segurança
        </div>
        <p style="font-size: 0.85rem; color: #664d03; margin-bottom: 10px;">
            Para garantir a segurança das transações, precisamos do seu CPF. Esta informação é encriptada e não será exibida publicamente.
        </p>
        <div class="form-group" style="margin-bottom: 0;">
            <input type="text" name="cpf" id="inputCpf" class="form-control" 
                   placeholder="000.000.000-00" required maxlength="14" 
                   style="border-color: #ffe58f; background: #fff;">
        </div>
    </div>
    <?php endif; ?>

</div>