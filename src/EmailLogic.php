<?php
/**
 * FICHEIRO: src/EmailLogic.php
 * PAPEL: Classe Mestre de Mensageria (PHPMailer Wrapper)
 * VERSÃO: 2.3 - Desacoplamento de E-mail Administrativo (socialbr.lol)
 */

// 1. --- [IMPORTAÇÃO FÍSICA DAS LIBS] ---
// Caminhos robustos partindo da pasta /src/ para a /libs/
require_once __DIR__ . '/../libs/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/src/SMTP.php';

// 2. --- [NAMESPACES] ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailLogic {
    private $config;
    private $pdo;

    /**
     * Construtor: Carrega as configurações de e-mail e a conexão PDO para logs.
     */
    public function __construct($pdo = null) {
        $this->pdo = $pdo;
        // Localiza o config/mail.php de forma dinâmica
        $pathConfig = dirname(__DIR__) . '/config/mail.php';
        if (!file_exists($pathConfig)) {
            error_log("Erro Crítico: Ficheiro config/mail.php não encontrado.");
            $this->config = [];
        } else {
            $this->config = require $pathConfig;
        }
    }

    /**
     * Método Privado: Configura uma nova instância do PHPMailer.
     * Centraliza o "setup" para evitar repetição de código (DRY).
     */
    private function criarInstancia(): PHPMailer {
        $mail = new PHPMailer(true);

        // Configurações vindas do config/mail.php
        if (isset($this->config['is_smtp']) && $this->config['is_smtp']) {
            $mail->isSMTP();
            $mail->Host       = $this->config['smtp_host'];
            $mail->SMTPAuth   = $this->config['smtp_auth'];
            $mail->Username   = $this->config['smtp_user'];
            $mail->Password   = $this->config['smtp_pass'];
            $mail->SMTPSecure = $this->config['smtp_secure'];
            $mail->Port       = $this->config['smtp_port'];
        }

        $mail->CharSet = $this->config['char_set'] ?? 'UTF-8';
        $mail->setFrom($this->config['from_email'], $this->config['from_name']);
        $mail->isHTML(true);
        $mail->SMTPDebug = $this->config['smtp_debug'] ?? 0;

        return $mail;
    }

    /**
     * MÉDODO: Enviar Alerta de Novo Chamado para o Administrador.
     * Utiliza a chave 'admin_email' definida no config/mail.php.
     */
    public function enviarAlertaNovoChamado(string $nomeUser, string $categoria, string $assunto, string $mensagem): bool {
        try {
            $mail = $this->criarInstancia();
            
            // BUSCA O DESTINATÁRIO NA NOVA CHAVE DE CONFIGURAÇÃO
            // Se não existir no config, usa o e-mail de suporte como backup seguro.
            $destinatario = $this->config['admin_email'] ?? 'suporte@socialbr.lol';
            
            $mail->addAddress($destinatario, 'Admin Social BR');
            $mail->Subject = "[ALERTA] Novo Chamado: {$assunto}";

            $mail->Body = "
                <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 12px; overflow: hidden; background-color: #ffffff;'>
                    <div style='background-color: #0C2D54; padding: 20px; text-align: center; color: #fff;'>
                        <h2 style='margin: 0;'>Novo Chamado de Suporte</h2>
                    </div>
                    <div style='padding: 25px; color: #333;'>
                        <p style='margin-bottom: 10px;'><strong>Utilizador:</strong> {$nomeUser}</p>
                        <p style='margin-bottom: 10px;'><strong>Categoria:</strong> {$categoria}</p>
                        <p style='margin-bottom: 10px;'><strong>Assunto:</strong> {$assunto}</p>
                        <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                        <p style='font-weight: bold; margin-bottom: 10px;'>Mensagem do Utilizador:</p>
                        <div style='background: #f9f9f9; padding: 15px; border-radius: 8px; border-left: 4px solid #0C2D54; line-height: 1.5;'>
                            " . nl2br(htmlspecialchars($mensagem)) . "
                        </div>
                    </div>
                    <div style='background: #f1f1f1; padding: 15px; text-align: center; font-size: 11px; color: #888;'>
                        Enviado automaticamente pelo Sistema de Sentinela - socialbr.lol
                    </div>
                </div>";

            $enviou = $mail->send();
            $this->logEmail($destinatario, 'alerta_suporte', $enviou);
            return $enviou;

        } catch (Exception $e) {
            $this->registrarErro("Erro Alerta Suporte: " . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * MÉDODO ATÓMICO: Enviar Código de Recuperação.
     */
    public function enviarCodigoRecuperacao(string $email, string $nome, string $codigo): bool {
        try {
            $mail = $this->criarInstancia();
            $mail->addAddress($email, $nome);
            $mail->Subject = 'Seu Código de Segurança - Social BR';

            $mail->Body = "
                <div style='font-family: sans-serif; max-width: 500px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; border-radius: 12px;'>
                    <h2 style='color: #0C2D54; text-align: center;'>Social BR</h2>
                    <p>Olá, <strong>{$nome}</strong>!</p>
                    <p>Recebemos um pedido para redefinir a sua senha. Utilize o código abaixo:</p>
                    <div style='background: #f8f9fa; border: 2px dashed #0C2D54; padding: 20px; text-align: center; font-size: 32px; font-weight: bold; color: #0C2D54; margin: 20px 0;'>
                        {$codigo}
                    </div>
                    <p style='font-size: 13px; color: #666;'>Válido por 15 minutos. Se não solicitou, ignore este e-mail.</p>
                </div>";

            $enviou = $mail->send();
            $this->logEmail($email, 'recuperacao_senha', $enviou);
            return $enviou;

        } catch (Exception $e) {
            $this->registrarErro("Erro Recuperação ({$email}): " . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * MÉDODO ATÓMICO: Enviar Link de Verificação de Conta.
     */
    public function enviarLinkVerificacao(string $email, string $nome, string $link): bool {
        try {
            $mail = $this->criarInstancia();
            $mail->addAddress($email, $nome);
            $mail->Subject = 'Confirme o seu e-mail na Social BR';

            $mail->Body = "
                <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 12px; overflow: hidden;'>
                    <div style='background-color: #0C2D54; padding: 30px; text-align: center; color: #fff;'>
                        <h1 style='margin: 0;'>Social BR</h1>
                    </div>
                    <div style='padding: 30px;'>
                        <h2>Bem-vindo, {$nome}!</h2>
                        <p>Para libertar todos os recursos da sua conta (Chat e Marketplace), confirme o seu e-mail:</p>
                        <div style='text-align: center; margin: 30px;'>
                            <a href='{$link}' style='background: #0C2D54; color: #fff; padding: 15px 25px; text-decoration: none; border-radius: 8px; font-weight: bold;'>Verificar E-mail Agora</a>
                        </div>
                    </div>
                </div>";

            $enviou = $mail->send();
            $this->logEmail($email, 'verificacao_conta', $enviou);
            return $enviou;

        } catch (Exception $e) {
            $this->registrarErro("Erro Verificação ({$email}): " . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Log de Auditoria: Regista se o e-mail saiu ou falhou.
     */
    private function logEmail($email, $tipo, $status) {
        if (!$this->pdo) return;
        try {
            $sql = "INSERT INTO Logs_Emails (destinatario, tipo, status, data_envio) VALUES (?, ?, ?, NOW())";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$email, $tipo, $status ? 'sucesso' : 'falha']);
        } catch (Exception $e) { /* Falha silenciosa no log */ }
    }

    /**
     * Central de Erros: Regista falhas no error_log do servidor.
     */
    private function registrarErro($mensagem) {
        error_log("[EmailLogic] " . $mensagem);
    }
}