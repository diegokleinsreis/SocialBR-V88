<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site em Construção - Black</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #000000;
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            width: 100%;
            animation: fadeIn 1.5s ease-in-out;
        }

        .icon {
            font-size: 4rem;
            margin-bottom: 2rem;
            animation: pulse 2s infinite;
        }

        h1 {
            font-family: 'Orbitron', monospace;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #ffffff;
        }

        .subtitle {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: #cccccc;
            line-height: 1.6;
        }

        .loading-bar {
            width: 100%;
            height: 4px;
            background-color: #333333;
            border-radius: 2px;
            overflow: hidden;
            margin: 2rem 0;
        }

        .loading-progress {
            width: 0%;
            height: 100%;
            background-color: #ffffff;
            border-radius: 2px;
            animation: loading 3s ease-in-out infinite;
        }

        .message {
            font-size: 1rem;
            color: #999999;
            margin-bottom: 3rem;
        }

        .credits {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.9rem;
            color: #666666;
            text-align: center;
        }

        .credits a {
            color: #ffffff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .credits a:hover {
            color: #cccccc;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }

        @keyframes loading {
            0% {
                width: 0%;
            }
            50% {
                width: 70%;
            }
            100% {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 2rem;
            }
            
            .subtitle {
                font-size: 1rem;
            }
            
            .icon {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🚧</div>
        <h1>Site em Construção</h1>
        <p class="subtitle">Estamos trabalhando duro para trazer algo incrível para você.</p>
        
        <div class="loading-bar">
            <div class="loading-progress"></div>
        </div>
        
        <p class="message">Aguarde o lançamento oficial!</p>
    </div>
    
    <div class="credits">
        Desenvolvido por <a href="#">Diego Kleins</a>
    </div>
</body>
</html>

