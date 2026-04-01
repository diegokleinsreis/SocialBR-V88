<?php
/**
 * Sub-view: Aba de Privacidade e Segurança
 * Este ficheiro é carregado dinamicamente por configurar_perfil.php
 */
?>

<div class="settings-card">
    <h2><i class="fas fa-user-secret"></i> Privacidade e Segurança</h2>
    
    <form id="form-privacidade" action="<?php echo $config['base_path']; ?>api/usuarios/atualizar_privacidade.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
        
        <div class="form-group switch-group">
            <label for="perfil_privado_switch">Perfil Privado</label>
            <p class="form-group-description">Se ativado, apenas os seus amigos poderão ver as suas publicações e informações detalhadas.</p>
            <label class="switch">
                <input type="checkbox" id="perfil_privado_switch" name="perfil_privado" value="1" <?php echo ($user_data['perfil_privado'] == 1) ? 'checked' : ''; ?>>
                <span class="slider round"></span>
            </label>
        </div>

        <div class="form-group">
            <label for="privacidade_amigos">Quem pode ver a sua lista de amigos?</label>
            <p class="form-group-description">Escolha quem terá permissão para ver a lista completa dos seus amigos no seu perfil.</p>
            <select id="privacidade_amigos" name="privacidade_amigos">
                <option value="todos" <?php echo ($user_data['privacidade_amigos'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
                <option value="amigos" <?php echo ($user_data['privacidade_amigos'] == 'amigos') ? 'selected' : ''; ?>>Apenas amigos</option>
                <option value="ninguem" <?php echo ($user_data['privacidade_amigos'] == 'ninguem') ? 'selected' : ''; ?>>Ninguém</option>
            </select>
        </div>

        <div class="form-group">
            <label for="privacidade_posts_padrao_select">Privacidade padrão para futuras publicações</label>
            <p class="form-group-description">Esta será a opção pré-selecionada no formulário ao criar um novo post.</p>
            <select id="privacidade_posts_padrao_select" name="privacidade_posts_padrao">
                <option value="publico" <?php echo ($user_data['privacidade_posts_padrao'] == 'publico') ? 'selected' : ''; ?>>Público</option>
                <option value="amigos" <?php echo ($user_data['privacidade_posts_padrao'] == 'amigos') ? 'selected' : ''; ?>>Apenas amigos</option>
            </select>
        </div>

        <hr>

        <div class="form-group">
            <label><i class="fas fa-user-slash"></i> Gerenciar Bloqueios</label>
            <p class="form-group-description">Visualize e faça a gestão dos utilizadores que bloqueou anteriormente.</p>
            <a href="<?php echo $config['base_path']; ?>gerenciar_bloqueios" class="secondary-btn-small" style="text-decoration: none; display: inline-block;">
                Ver lista de bloqueados
            </a>
        </div>

        <div class="form-actions-right">
            <button type="submit" class="primary-btn-small">Salvar Configuração de Privacidade</button>
        </div>
    </form>
</div>