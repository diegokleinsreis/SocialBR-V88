<?php
// 1. DEFINIÇÕES DA PÁGINA
// O index.php (roteador) já carregou o database.php.
// $conn, $config, $config['base_path'], $asset_version já estão disponíveis.
$page_title = 'Página Não Encontrada - ' . htmlspecialchars($config['site_nome']);
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <script>
        (function() {
            const theme = localStorage.getItem('theme');
            if (theme === 'dark') {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/base/_base.css?v=<?php echo $asset_version; ?>">
    <link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_forms.css?v=<?php echo $asset_version; ?>">


    <style>
        /* Este CSS agora não tem NENHUM conflito */
        html {
            height: 100%;
        }
        body {
            display: flex; 
            flex-direction: column;
            min-height: 100vh; 
            margin: 0; 
            padding: 0; 
            background-color: var(--cor-fundo-principal); 
            font-family: var(--fonte-principal);
            /* Centralização Vertical e Horizontal */
            align-items: center; 
            justify-content: center; 
            text-align: center;
            padding: 20px;
            box-sizing: border-box; /* Garante que o padding não quebre o 100vh */
        }
        .error-card {
            background: var(--cor-fundo-card);
            padding: 40px;
            border-radius: 12px;
            box-shadow: var(--sombra-padrao);
            max-width: 500px;
            width: 100%; 
            box-sizing: border-box;
        }
        .error-card i {
            font-size: 5rem;
            color: var(--cor-primaria); /* Usa a cor do tema (azul) */
            margin-bottom: 20px;
        }
        .error-card h1 {
            font-size: 2rem;
            color: var(--cor-texto-primaria); /* Usa a cor do tema */
            margin-bottom: 10px;
        }
        .error-card p {
            font-size: 1.1rem;
            color: var(--cor-texto-secundaria); /* Usa a cor do tema */
            margin-bottom: 30px;
        }
        .error-card .primary-btn {
            text-decoration: none !important; /* Remove sublinhado */
        }
    </style>
</head>
<body>

    <div class="error-card">
        <i class="fas fa-exclamation-triangle"></i>
        <h1>Erro 404</h1>
        <p>Oops! A página que você está a tentar aceder não foi encontrada ou não existe.</p>
        <a href="<?php echo $config['base_path']; ?>feed" class="primary-btn">Voltar ao Início</a>
    </div>
    
    </body>
</html>