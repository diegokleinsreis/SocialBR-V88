<?php
/**
 * views/grupos/criar.php
 * Orquestrador da Página de Criação de Grupos.
 * PAPEL: Validar sessão, CSRF e incluir o componente de formulário.
 * VERSÃO: 1.0 (Arquitetura Atômica - SOOC)
 */

// 1. SEGURANÇA: Garantia de Sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $config['base_path'] . "login");
    exit();
}

// 2. CONFIGURAÇÕES E COMPONENTES
$page_title = "Criar Novo Grupo - " . ($config['site_nome'] ?? 'Social BR');
$component_path = __DIR__ . '/componentes/';

// Token CSRF para segurança do formulário (gerado no header.php)
$csrf_token = $_SESSION['csrf_token'] ?? '';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <?php include __DIR__ . '/../../templates/head_common.php'; ?>
    <style>
        .create-group-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header-simple {
            margin-bottom: 25px;
        }

        .page-header-simple h1 {
            font-size: 1.8rem;
            font-weight: 800;
            color: #050505;
        }

        .page-header-simple p {
            color: #65676b;
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .create-group-container { padding: 10px; }
        }
    </style>
</head>
<body class="bg-light">

    <?php include __DIR__ . '/../../templates/header.php'; ?>
    <?php include __DIR__ . '/../../templates/mobile_nav.php'; ?>

    <div class="main-content-area">
        <?php include __DIR__ . '/../../templates/sidebar.php'; ?>

        <main class="feed-container">
            <div class="create-group-container">
                
                <header class="page-header-simple">
                    <h1>Criar Grupo</h1>
                    <p>Defina a identidade e a privacidade da sua nova comunidade.</p>
                </header>

                <?php 
                $form_file = $component_path . 'form_criar.php';
                if (file_exists($form_file)) {
                    include $form_file;
                } else {
                    echo "<div class='alert alert-error'>Erro: Componente <code>form_criar.php</code> não encontrado.</div>";
                }
                ?>

            </div>
        </main>
    </div>

    <?php include __DIR__ . '/../../templates/footer.php'; ?>

</body>
</html>