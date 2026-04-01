<?php
// 1. VERIFICAÇÕES INICIAIS
// O index.php (roteador) já carregou o database.php
// $config['base_path'] já está disponível.

// 2. VERIFICA SE OS CADASTROS ESTÃO PERMITIDOS (Corrigido)
// Se o admin desligou os cadastros, redireciona para a ROTA /login.
if (!isset($config['permite_cadastro']) || $config['permite_cadastro'] == '0') {
    header("Location: " . $config['base_path'] . "login"); 
    exit();
}

// 3. BUSCA BAIRROS (Lógica existente)
// A variável $conn vem do database.php, carregado pelo index.php
// --- CORREÇÃO: Usar CIDADE_PADRAO_ID da configuração (com fallback para 129) ---
$id_cidade_padrao = (int)($config['CIDADE_PADRAO_ID'] ?? 129);
$sql_bairros = "SELECT id, nome FROM Bairros WHERE id_cidade = " . $id_cidade_padrao . " ORDER BY nome ASC";
// --- FIM DA CORREÇÃO ---
$result_bairros = $conn->query($sql_bairros);

// 4. DEFINE O TÍTULO DA PÁGINA PARA O TEMPLATE
$page_title = 'Crie sua Conta - ' . htmlspecialchars($config['site_nome']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <?php 
    // 5. INCLUI O NOSSO <HEAD> CENTRALIZADO
    // Caminho relativo ao index.php (public_html/)
    include '../templates/head_common.php'; 
    ?>
</head>
<body class="register-page-body">

    <a href="<?php echo $config['base_path']; ?>login" class="back-button"><i class="fas fa-arrow-left"></i> Voltar</a> 
    
    <div class="container">

        <div class="form-header">
            <div class="header-text">
                <h1>Crie sua conta em<br><?php echo htmlspecialchars($config['site_nome']); ?></h1>
                <h2>É rápido e fácil.</h2>
            </div>
        </div>

        <form action="api/usuarios/criar_usuario.php" method="POST">
            
            <div class="input-container">
                <input type="text" name="nome" placeholder="Nome" required>
            </div>
            <div class="input-container">
                <input type="text" name="sobrenome" placeholder="Sobrenome" required>
            </div>
            <div class="input-container">
                <input type="text" name="nome_de_usuario" placeholder="Nome de Usuário (@exemplo)" required>
            </div>
            <div class="input-container">
                <label for="data_nasc">Data de Nascimento</label>
                <input type="date" id="data_nasc" name="data_nascimento" required>
            </div>
            <div class="input-container">
                <label for="bairro">Seu Bairro</label>
                <select name="id_bairro" id="bairro" required>
                    <option value="" disabled selected>Selecione seu bairro</option>
                    <?php
                    // Lógica dos bairros
                    if ($result_bairros && $result_bairros->num_rows > 0) {
                        while($bairro = $result_bairros->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($bairro['id']) . '">' . htmlspecialchars($bairro['nome']) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="input-container">
                <input type="email" name="email" placeholder="Seu melhor e-mail" required>
            </div>
            <div class="input-container">
                <input type="password" name="senha" placeholder="Crie uma senha" required>
            </div>
            <div class="input-container">
                <input type="password" name="confirmar_senha" placeholder="Confirme sua senha" required>
            </div>
            
            <button type="submit" class="primary-btn">Cadastrar</button>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const erro = urlParams.get('erro');

        if (erro) {
            let configAlerta = {
                confirmButtonColor: '#0C2D54', // Cor oficial do site
                confirmButtonText: 'Entendido'
            };

            switch (erro) {
                case 'email_falso':
                    configAlerta.title = 'E-mail não permitido';
                    configAlerta.text = 'Por favor, utilize um endereço de e-mail real. Não aceitamos domínios temporários ou inexistentes.';
                    configAlerta.icon = 'warning';
                    break;
                case 'senha_diferente':
                    configAlerta.title = 'Erro nas senhas';
                    configAlerta.text = 'A confirmação de senha não coincide com a senha digitada.';
                    configAlerta.icon = 'error';
                    break;
                case 'campos_incompletos':
                    configAlerta.title = 'Campos vazios';
                    configAlerta.text = 'Por favor, preencha todos os campos do formulário para continuar.';
                    configAlerta.icon = 'info';
                    break;
                case 'duplicado':
                    configAlerta.title = 'Conta já existe';
                    configAlerta.text = 'Este e-mail ou nome de usuário já está em uso na nossa rede.';
                    configAlerta.icon = 'warning';
                    break;
                case 'fatal':
                    configAlerta.title = 'Erro interno';
                    configAlerta.text = 'Ocorreu um erro ao processar seu cadastro. Tente novamente em instantes.';
                    configAlerta.icon = 'error';
                    break;
            }

            if (configAlerta.title && typeof Swal !== 'undefined') {
                Swal.fire(configAlerta);
            }
        }
    });
    </script>

</body>
</html>