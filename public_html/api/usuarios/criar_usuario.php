<?php
/**
 * ARQUIVO: api/usuarios/criar_usuario.php
 * PAPEL: Processamento de Registro com Redirecionamento para Rota Amigável Correcta.
 * VERSÃO: 4.6 - socialbr.lol (Correção de Rota /cadastro)
 */

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/UserLogic.php';

// Importação PHPMailer para o envio do primeiro link de verificação
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../../libs/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../../../libs/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../../libs/PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = trim($_POST['nome']);
    $sobrenome = trim($_POST['sobrenome']);
    $data_nascimento = $_POST['data_nascimento'];
    $nome_de_usuario = trim($_POST['nome_de_usuario']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    $confirmar_senha = $_POST['confirmar_senha'];
    $id_bairro = isset($_POST['id_bairro']) ? (int)$_POST['id_bairro'] : 0;

    // 1. --- [VALIDAÇÃO DE CAMPOS OBRIGATÓRIOS] ---
    if (empty($nome) || empty($sobrenome) || empty($data_nascimento) || empty($nome_de_usuario) || empty($email) || empty($senha) || empty($confirmar_senha) || $id_bairro <= 0) {
        header("Location: " . $config['base_path'] . "cadastro?erro=campos_incompletos");
        exit();
    }

    // 2. --- [SEGURANÇA: BLINDAGEM DE E-MAIL (DNS/MX & BLACKLIST)] ---
    // Verifica se o domínio é temporário ou inexistente.
    if (!UserLogic::validarEmailReal($email)) {
        header("Location: " . $config['base_path'] . "cadastro?erro=email_falso");
        exit();
    }

    if ($senha !== $confirmar_senha) {
        header("Location: " . $config['base_path'] . "cadastro?erro=senha_diferente");
        exit();
    }

    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // 3. --- [GERAÇÃO DE TOKEN DE VERIFICAÇÃO] ---
    $token_verificacao = UserLogic::gerarTokenVerificacao(); 

    // 4. --- [PERSISTÊNCIA NO BANCO DE DADOS] ---
    $sql = "INSERT INTO Usuarios (nome, sobrenome, data_nascimento, nome_de_usuario, email, senha_hash, id_bairro, token_verificacao, email_verificado) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssis", $nome, $sobrenome, $data_nascimento, $nome_de_usuario, $email, $senha_hash, $id_bairro, $token_verificacao);

    if ($stmt->execute()) {
        $user_id = $conn->insert_id;

        // 5. --- [DISPARO DO E-MAIL DE BOAS-VINDAS] ---
        enviarEmailBoasVindas($email, $nome, $token_verificacao, $config);

        header("Location: " . $config['base_path'] . "login?cadastro=sucesso");
        exit();
    } else {
        if ($conn->errno == 1062) {
            header("Location: " . $config['base_path'] . "cadastro?erro=duplicado");
        } else {
            header("Location: " . $config['base_path'] . "cadastro?erro=fatal");
        }
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Acesso inválido.";
}

/**
 * Função Auxiliar para envio de e-mail inicial (PHPMailer)
 */
function enviarEmailBoasVindas($destinatario, $nome, $token, $config) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'mail.socialbr.lol';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'suporte@socialbr.lol';
        $mail->Password   = 'Diego@56741634'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom('suporte@socialbr.lol', 'Social BR');
        $mail->addAddress($destinatario, $nome);
        
        $link = $config['base_url'] . "verificar-email?token=" . $token;

        $mail->isHTML(true);
        $mail->Subject = 'Bem-vindo à Social BR - Confirme seu e-mail';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; color: #333;'>
                <h1 style='color: #0C2D54;'>Olá, {$nome}!</h1>
                <p>Confirme o seu e-mail no link abaixo para ativar sua conta:</p>
                <p><a href='{$link}' style='background:#0C2D54; color:#fff; padding:10px 20px; text-decoration:none; border-radius:5px; display:inline-block;'>Verificar Conta</a></p>
            </div>";

        $mail->send();
    } catch (Exception $e) {
        error_log("Falha ao enviar e-mail: " . $mail->ErrorInfo);
    }
}