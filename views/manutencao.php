<?php
// 1. DEFINIÇÕES DA PÁGINA
// O index.php (roteador) já carregou o database.php.
// $conn, $config, e $config['base_path'] já estão disponíveis.
// $asset_version também já está disponível.

// Define o título que aparecerá no <head>
$page_title = 'Em Manutenção - ' . htmlspecialchars($config['site_nome']);

// 2. DEFINE O CÓDIGO DE RESPOSTA HTTP
// 503 (Serviço Indisponível) é o código correto para manutenção
http_response_code(503);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <?php 
    // 3. INCLUI O <HEAD> COMUM
    // (Carrega o CSS, a variável BASE_PATH para o JS, etc.)
    include '../templates/head_common.php'; 
    ?>
    <style>
        /* Reutiliza o mesmo estilo exato da página 404 para centralizar */
        html { height: 100%; }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            background-color: var(--cor-fundo-principal); 
            font-family: var(--fonte-principal);
        }
        .error-page-wrapper { /* (Usando a mesma classe do 404) */
            flex-grow: 1; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            text-align: center;
            padding: 20px;
        }
        .error-card { /* (Usando a mesma classe do 404) */
            background: var(--cor-fundo-card);
            padding: 40px;
            border-radius: 12px;
            box-shadow: var(--sombra-padrao);
            max-width: 500px;
            width: 90%; 
        }
        .error-card i {
            font-size: 5rem;
            color: var(--cor-primaria); /* Cor primária (azul) */
            margin-bottom: 20px;
        }
        .error-card h1 {
            font-size: 2rem;
            color: var(--cor-texto-primaria); 
            margin-bottom: 10px;
        }
        .error-card p {
            font-size: 1.1rem;
            color: var(--cor-texto-secundaria); 
            margin-bottom: 30px;
        }
        .error-card .primary-btn {
            text-decoration: none; 
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 1rem;
            display: inline-block;
            cursor: pointer;
            border: none; /* Garante que o <button> pareça um <a> */
            font-family: var(--fonte-principal); /* Garante a fonte correta no botão */
            font-weight: 600; /* Garante o peso correto no botão */
        }
    </style>
</head>
<body>

    <div class="error-page-wrapper">
        <div class="error-card">
            <i class="fas fa-tools"></i> <h1>Estamos em Manutenção</h1>
            <p>O <?php echo htmlspecialchars($config['site_nome']); ?> está a passar por uma atualização programada. Voltamos em breve!</p>
            
            <button type="button" class="primary-btn" onclick="location.reload();">
                Tentar Novamente
            </button>
        </div>
    </div>

    <?php 
    // 6. INCLUI O RODAPÉ (Para carregar os scripts JS globais)
    include '../templates/footer.php'; 
    ?>
</body>
</html>