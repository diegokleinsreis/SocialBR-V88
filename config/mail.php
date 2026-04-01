<?php
/**
 * FICHEIRO: config/mail.php
 * PAPEL: Centralização de Credenciais do Servidor de E-mail (SMTP)
 * VERSÃO: 1.1 - Adição de Identidade do Administrador (socialbr.lol)
 */

return [
    // Configurações do Servidor Smarthost
    'smtp_host'     => 'mail.socialbr.lol',
    'smtp_user'     => 'suporte@socialbr.lol',
    'smtp_pass'     => 'Diego@56741634',
    'smtp_port'     => 465,
    'smtp_secure'   => 'ssl', // PHPMailer::ENCRYPTION_SMTPS utiliza SSL implícito

    // Identidade do Administrador (Destino de Alertas do Sentinela)
    'admin_email'   => 'suporte@socialbr.lol',

    // Identidade do Remetente Padrão
    'from_email'    => 'suporte@socialbr.lol',
    'from_name'     => 'Social BR',
    'support_name'  => 'Social BR Suporte',

    // Configurações de Performance/Segurança
    'char_set'      => 'UTF-8',
    'is_smtp'       => true,
    'smtp_auth'     => true,
    
    // Configuração de Depuração (0 = off, 2 = client/server messages)
    'smtp_debug'    => 0 
];