<?php
// 1. VERIFICAÇÃO DE SESSÃO
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. LÓGICA DE REDIRECIONAMENTO
if (isset($_SESSION['user_id'])) {
    header("Location: " . $config['base_path'] . "feed");
    exit();
}

// 3. DEFINE O TÍTULO DA PÁGINA
$page_title = 'Login - ' . htmlspecialchars($config['site_nome']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <?php include '../templates/head_common.php'; ?>
    
    <style>
        /* --- ESTILOS DO MODAL DE RECUPERAÇÃO --- */
        .modal-recuperacao {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.6);
            backdrop-filter: blur(5px);
            align-items: center;
            justify-content: center;
        }

        .modal-content-rec {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            position: relative;
            text-align: center;
        }

        .modal-content-rec h3 {
            margin-top: 0;
            color: #0C2D54;
            font-size: 1.5rem;
        }

        .modal-content-rec p {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 20px;
        }

        .modal-content-rec input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .close-modal-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: #aaa;
        }

        .step-2 { display: none; } /* Esconde a parte de validar código inicialmente */
        
        /* Ajuste do link original */
        .link-secondary { cursor: pointer; }
    </style>
</head>
<body class="login-page-body">

    <div class="container">
        
        <div class="form-header">
            <div class="header-text">
                <h1><?php echo htmlspecialchars($config['site_nome']); ?></h1>
                <h2>Faça login para continuar</h2>
            </div>
        </div>

        <?php
        if (isset($_GET['cadastro']) && $_GET['cadastro'] === 'sucesso') {
            echo '<div class="success-message">Cadastro realizado com sucesso! Faça o login para continuar.</div>';
        }

        if (isset($_SESSION['login_error'])) {
            echo '<div class="error-message">' . htmlspecialchars($_SESSION['login_error']) . '</div>';
            unset($_SESSION['login_error']);
        }
        ?>

        <form action="api/usuarios/processa_login.php" method="POST">
            <input type="text" name="email_ou_usuario" placeholder="E-mail ou nome de usuário" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit" class="primary-btn">Entrar</button>
        </form>

        <div class="form-actions">
            <a onclick="abrirModalRecuperacao()" class="link-secondary">Esqueceu sua senha?</a>
            
            <?php if (isset($config['permite_cadastro']) && $config['permite_cadastro'] == '1'): ?>
                <button type="button" class="secondary-btn" onclick="location.href='<?php echo $config['base_path']; ?>cadastro'">Criar nova conta</button>
            <?php endif; ?>
        </div>

    </div>

    <div id="modalRecuperacao" class="modal-recuperacao">
        <div class="modal-content-rec">
            <button class="close-modal-btn" onclick="fecharModalRecuperacao()">&times;</button>
            
            <div id="step1">
                <h3>Recuperar Senha</h3>
                <p>Introduza o seu e-mail para receber um código de segurança.</p>
                <input type="email" id="rec_email" placeholder="Seu e-mail cadastrado">
                <button type="button" class="primary-btn" id="btnEnviarEmail" onclick="solicitarCodigo()">Enviar E-mail</button>
            </div>

            <div id="step2" class="step-2">
                <h3>Validar Código</h3>
                <p>Insira o código enviado para o seu e-mail e escolha uma nova senha.</p>
                <input type="text" id="rec_codigo" placeholder="Código de 6 dígitos" maxlength="6">
                <input type="password" id="rec_nova_senha" placeholder="Nova senha (min. 6 caracteres)">
                <button type="button" class="primary-btn" onclick="resetarSenha()">Alterar Senha</button>
            </div>
        </div>
    </div>

    <footer class="site-footer">
        <div class="footer-links">
            <a href="#">Sobre</a> <a href="#">Termos</a> <a href="#">Políticas de Privacidade</a> <a href="#">Desenvolvedores</a> <a href="#">Ajuda</a>
        </div>
        <div class="footer-copyright">
            &copy; 2025 <?php echo htmlspecialchars($config['site_nome']); ?>
        </div>
    </footer>

    <script>
        const modal = document.getElementById('modalRecuperacao');
        const step1 = document.getElementById('step1');
        const step2 = document.getElementById('step2');

        function abrirModalRecuperacao() {
            modal.style.display = 'flex';
        }

        function fecharModalRecuperacao() {
            modal.style.display = 'none';
            // Reseta para o passo 1 ao fechar
            step1.style.display = 'block';
            step2.style.display = 'none';
        }

        // FASE 1: Solicitar o envio do e-mail
        async function solicitarCodigo() {
            const email = document.getElementById('rec_email').value;
            const btn = document.getElementById('btnEnviarEmail');

            if (!email) return Swal.fire('Erro', 'Por favor, insira o seu e-mail.', 'error');

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> A enviar...';

            try {
                const response = await fetch(window.base_path + 'api/usuarios/solicitar_recuperacao.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: email })
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire('E-mail enviado!', data.message, 'success');
                    // Troca para o formulário de validação
                    step1.style.display = 'none';
                    step2.style.display = 'block';
                } else {
                    Swal.fire('Falha', data.message, 'error');
                }
            } catch (error) {
                Swal.fire('Erro', 'Ocorreu um problema ao conectar com o servidor.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Enviar E-mail';
            }
        }

        // FASE 2: Validar o código e trocar a senha
        async function resetarSenha() {
            const email = document.getElementById('rec_email').value;
            const codigo = document.getElementById('rec_codigo').value;
            const nova_senha = document.getElementById('rec_nova_senha').value;

            if (!codigo || !nova_senha) return Swal.fire('Erro', 'Preencha todos os campos.', 'error');

            try {
                const response = await fetch(window.base_path + 'api/usuarios/validar_recuperacao.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: email, codigo: codigo, nova_senha: nova_senha })
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire('Sucesso!', data.message, 'success').then(() => {
                        fecharModalRecuperacao();
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Erro', data.message, 'error');
                }
            } catch (error) {
                Swal.fire('Erro', 'Falha ao processar a nova senha.', 'error');
            }
        }

        // Fechar modal se clicar fora dele
        window.onclick = function(event) {
            if (event.target == modal) fecharModalRecuperacao();
        }
    </script>
</body>
</html>