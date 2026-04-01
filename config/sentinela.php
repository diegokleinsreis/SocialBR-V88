<?php
/**
 * ARQUIVO: config/sentinela.php
 * VERSÃO: 1.0 (socialbr.lol)
 * PAPEL: Capturar globalmente erros e exceções e enviá-los ao ErrorLogic.
 * STACK: PHP 8.x + SOOC (Separação Atômica)
 */

// 1. --- [BLINDAGEM DE EXIBIÇÃO] ---
// Se o modo de desenvolvimento estiver desligado, escondemos tudo do usuário.
if (!isset($config['modo_dev']) || $config['modo_dev'] != '1') {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
} else {
    // Em desenvolvimento, mantemos visível para agilizar o debug local
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// 2. --- [INICIALIZAÇÃO DO MOTOR] ---
require_once __DIR__ . '/../src/ErrorLogic.php';
$sentinela = new ErrorLogic($pdo);

/**
 * CAPTURADOR 1: Erros Padrão do PHP (Warnings, Notices, etc.)
 */
set_error_handler(function($nivel, $mensagem, $arquivo, $linha) use ($sentinela) {
    // Mapeamento de gravidade para o banco
    $tipos = [
        E_ERROR => 'Fatal Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice'
    ];
    
    $tipo_str = $tipos[$nivel] ?? 'PHP Error (' . $nivel . ')';
    
    // Grava no banco de dados através da lógica centralizada
    $sentinela->registrarErro($tipo_str, $mensagem, $arquivo, $linha);
    
    // Se for um erro do tipo "User Error" (gatilhado manualmente), paramos a execução
    if ($nivel === E_USER_ERROR) {
        exibirTelaErroCustomizada();
        exit;
    }
    
    // Retornar false permite que o PHP siga o fluxo normal (importante para Warnings)
    return false; 
});

/**
 * CAPTURADOR 2: Exceções Não Tratadas (Try/Catch Ausentes)
 */
set_exception_handler(function($e) use ($sentinela) {
    $sentinela->registrarErro(
        'Exception: ' . get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString() // Captura o rastro completo do erro
    );
    
    exibirTelaErroCustomizada();
    exit;
});

/**
 * CAPTURADOR 3: Erros Fatais (Shutdown Function)
 * Captura erros que normalmente "matam" o script antes dos outros handlers.
 */
register_shutdown_function(function() use ($sentinela) {
    $erro_final = error_get_last();
    
    // Filtramos apenas erros críticos (Fatais, Parse, etc.)
    if ($erro_final && in_array($erro_final['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $sentinela->registrarErro(
            'Fatal Error (Sentinel)',
            $erro_final['message'],
            $erro_final['file'],
            $erro_final['line']
        );
        
        // Limpamos o buffer de saída para não mostrar lixo ao usuário
        if (ob_get_length()) ob_clean();
        exibirTelaErroCustomizada();
    }
});

/**
 * FUNÇÃO AUXILIAR: UX Premium de Erro
 * Exibe uma mensagem amigável com a cor oficial #0C2D54.
 */
function exibirTelaErroCustomizada() {
    global $config;
    
    // Se estiver em modo DEV, não exibimos a tela amigável para não atrapalhar o programador
    if (isset($config['modo_dev']) && $config['modo_dev'] == '1') return;

    http_response_code(500);
    
    // Estilos inline para garantir resiliência absoluta (mesmo sem carregar o CSS do site)
    echo "
    <!DOCTYPE html>
    <html lang='pt-br'>
    <head>
        <meta charset='UTF-8'>
        <title>Erro Interno - socialbr.lol</title>
        <style>
            body { background: #f8fafc; font-family: 'Segoe UI', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
            .container { text-align: center; padding: 40px; background: #fff; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border-top: 6px solid #0C2D54; max-width: 450px; }
            h1 { color: #0C2D54; font-size: 24px; }
            p { color: #64748b; line-height: 1.6; }
            .btn { display: inline-block; margin-top: 25px; padding: 12px 30px; background: #0C2D54; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; transition: opacity 0.2s; }
            .btn:hover { opacity: 0.9; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>Ops! Algo deu errado.</h1>
            <p>Tivemos um problema técnico, mas nossa equipe já foi notificada e está trabalhando nisso.</p>
            <p>Por favor, tente novamente em alguns minutos.</p>
            <a href='/' class='btn'>Voltar ao Início</a>
        </div>
    </body>
    </html>";
}