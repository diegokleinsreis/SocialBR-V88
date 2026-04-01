<?php
/**
 * Sub-view: Aba de Perfil (Dados Pessoais e Mídia)
 * Este ficheiro é carregado dinamicamente por configurar_perfil.php
 */
?>

<div class="settings-card">
    <?php include __DIR__ . '/../settings_media_hub_template.php'; ?>
</div>

<div class="settings-card">
    <h2><i class="fas fa-user-edit"></i> Configuração do Perfil</h2>
    
    <form id="form-perfil" action="<?php echo $config['base_path']; ?>api/usuarios/atualizar_perfil.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

        <div class="form-row">
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($user_data['nome']); ?>" required>
            </div>
            <div class="form-group">
                <label for="sobrenome">Sobrenome</label>
                <input type="text" id="sobrenome" name="sobrenome" value="<?php echo htmlspecialchars($user_data['sobrenome']); ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="biografia">Biografia</label>
            <textarea id="biografia" name="biografia" rows="3" placeholder="Escreva um pouco sobre você..."><?php echo htmlspecialchars($user_data['biografia']); ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="data_nascimento">Data de Nascimento</label>
                <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($user_data['data_nascimento']); ?>" required>
            </div>
            <div class="form-group">
                <label for="relacionamento">Relacionamento</label>
                <select id="relacionamento" name="relacionamento">
                    <?php 
                    $relacionamentos = ['Não especificado', 'Solteiro(a)', 'Em um relacionamento sério', 'Casado(a)', 'Divorciado(a)'];
                    foreach ($relacionamentos as $r) {
                        $selected = ($user_data['relacionamento'] == $r) ? 'selected' : '';
                        echo "<option value=\"$r\" $selected>" . htmlspecialchars($r) . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="id_bairro">Bairro</label>
            <select id="id_bairro" name="id_bairro" required>
                <?php 
                if ($result_bairros && $result_bairros->num_rows > 0): 
                    mysqli_data_seek($result_bairros, 0); 
                    while($bairro = $result_bairros->fetch_assoc()): 
                        $selected = ($bairro['id'] == $user_data['id_bairro']) ? 'selected' : ''; 
                        echo '<option value="' . htmlspecialchars($bairro['id']) . '" ' . $selected . '>' . htmlspecialchars($bairro['nome']) . '</option>'; 
                    endwhile; 
                endif; 
                ?>
            </select>
        </div>

        <div class="form-actions-right">
            <button type="submit" class="primary-btn-small">Salvar Alterações do Perfil</button>
        </div>
    </form>
</div>