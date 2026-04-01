<?php
// 1. VERIFICA SE O UTILIZADOR ESTÁ LOGADO
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: " . $config['base_path'] . "login");
    exit();
}
$user_id = $_SESSION['user_id'];

// 2. INCLUI O "CÉREBRO" DO UTILIZADOR
require_once __DIR__ . '/../src/UserLogic.php';

// --- LÓGICA DO FILTRO ---
$filtro = $_GET['filtro'] ?? null;
$filtro_limpo = $filtro ? htmlspecialchars(trim($filtro), ENT_QUOTES, 'UTF-8') : null;

// 3. BUSCA OS DADOS (Agora com o filtro)
$lista_bloqueados = UserLogic::getBlockedUsersList($conn, $user_id, $filtro_limpo);

// 4. DEFINE O TÍTULO DA PÁGINA
$page_title = 'Gerenciar Bloqueios - ' . htmlspecialchars($config['site_nome']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <?php include '../templates/head_common.php'; ?>
    <link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_settings.css?v=<?php echo $asset_version; ?>">
    
    <?php // Estilo extra para o formulário de filtro ?>
    <style>
        .search-filter-form {
            display: flex;
            gap: 10px;
            /* [MODIFICAÇÃO] A margem de baixo agora controla o espaço para a lista */
            margin-bottom: 25px; 
            align-items: center; 
        }
        .search-filter-form input[type="text"] {
            flex-grow: 1;
        }
        
        .search-filter-form .primary-btn-small {
            padding: 8px 12px;
            font-size: 0.9em;
        }

        .search-filter-form .primary-btn-small,
        .search-filter-form .secondary-btn-small {
            flex-shrink: 0; 
        }
    </style>
</head>
<body>
    <?php include '../templates/header.php'; ?>
    <?php include '../templates/mobile_nav.php'; ?>

    <div class="main-content-area">
        <?php include '../templates/sidebar.php'; ?>

        <main class="profile-main-content">
            <div class="page-section-header">
                <h1>Gerenciar Bloqueios</h1>
                <p>Aqui você pode ver todos os usuários que bloqueou e desbloqueá-los.</p>
            </div>

            <div class="settings-card">
                
                <h2>
                    <i class="fas fa-user-lock"></i> Usuários Bloqueados 
                    (<?php echo count($lista_bloqueados); ?> <?php echo !empty($filtro) ? 'encontrados' : ''; ?>)
                </h2>

                <form action="<?php echo $config['base_path']; ?>gerenciar_bloqueios" method="GET" class="search-filter-form">
                    <input type="text" name="filtro" placeholder="Buscar por nome ou @username..." value="<?php echo htmlspecialchars($filtro ?? ''); ?>">
                    <button type="submit" class="primary-btn-small">Buscar</button>
                    <?php if (!empty($filtro)): ?>
                        <a href="<?php echo $config['base_path']; ?>gerenciar_bloqueios" class="secondary-btn-small" style="text-decoration:none;">Limpar</a>
                    <?php endif; ?>
                </form>
                <div class="blocked-user-list">
                    <?php if (empty($lista_bloqueados)): ?>
                        
                        <?php if (!empty($filtro)): ?>
                            <p style="padding: 10px 0;">Nenhum usuário bloqueado encontrado com o termo "<?php echo $filtro_limpo; ?>".</p>
                        <?php else: ?>
                            <p style="padding: 10px 0;">Você não bloqueou nenhum usuário.</p>
                        <?php endif; ?>

                    <?php else: ?>
                        <?php foreach ($lista_bloqueados as $usuario_bloqueado): ?>
                            <div class="blocked-user-item">
                                <div class="user-info">
                                    <?php
                                    $avatar_bloqueado = !empty($usuario_bloqueado['foto_perfil_url'])
                                        ? $config['base_path'] . htmlspecialchars($usuario_bloqueado['foto_perfil_url'])
                                        : $config['base_path'] . 'assets/images/default-avatar.png.png';
                                    ?>
                                    <img src="<?php echo $avatar_bloqueado; ?>" alt="Avatar" class="avatar-small">
                                    <div>
                                        <strong><?php echo htmlspecialchars($usuario_bloqueado['nome'] . ' ' . $usuario_bloqueado['sobrenome']); ?></strong>
                                        <span>@<?php echo htmlspecialchars($usuario_bloqueado['nome_de_usuario']); ?></span>
                                    </div>
                                </div>
                                <button class="secondary-btn-small bloquear-usuario-btn" 
                                        data-usuario-id="<?php echo $usuario_bloqueado['id']; ?>" 
                                        data-acao="desbloquear">
                                    Desbloquear
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>

    <?php include '../templates/footer.php'; ?>
</body>
</html>