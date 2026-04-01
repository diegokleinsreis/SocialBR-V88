<?php
// 1. GUARITA DE SEGURANÇA E CONEXÃO
require_once 'admin_auth.php'; // Garante que só o admin veja
// $conn e $config (com $config['base_path']) já estão disponíveis aqui

// 2. BUSCAR DADOS ATUALIZADOS
$sql = "SELECT chave, valor FROM Configuracoes";
$result = $conn->query($sql);
$configs_db = [];
while ($row = $result->fetch_assoc()) {
    $configs_db[$row['chave']] = $row['valor'];
}

// Helper para buscar o valor de forma segura
function getConfigValue($key, $configs_array) {
    return isset($configs_array[$key]) ? htmlspecialchars($configs_array[$key]) : '';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações Gerais - Painel Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css?v=2.91"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Ajustes Finos de Espaçamento para Melhor UI */
        .admin-form .form-group { margin-bottom: 25px; }
        .form-group-description { 
            margin-bottom: 12px !important; 
            line-height: 1.5; 
            color: #65676b;
            font-size: 0.85rem;
        }
        .switch-group { 
            padding: 15px 0;
            border-bottom: 1px solid #f0f2f5;
        }
        .switch-group:last-of-type { border-bottom: none; }
        
        .label-with-link {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }
        .manage-list-link {
            font-size: 0.75rem;
            text-decoration: none;
            color: #1877f2;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: opacity 0.2s;
        }
        .manage-list-link:hover { opacity: 0.8; text-decoration: underline; }
        
        hr { border: 0; border-top: 1px solid #e4e6eb; margin: 30px 0; }
    </style>
</head>
<body>

    <?php include 'templates/admin_header.php'; ?>
    <?php include 'templates/admin_mobile_nav.php'; ?>

    <main class="admin-main-content">
        <a href="index.php" class="admin-back-button"><i class="fas fa-arrow-left"></i> Voltar ao Dashboard</a>
        
        <div class="admin-card">
            <h1><i class="fas fa-cogs"></i> Configurações Gerais</h1>
            <p>Controle os pilares centrais da inteligência da socialbr.lol.</p>
        </div>

        <div class="admin-card">
            <h2><i class="fas fa-edit"></i> Editar Parâmetros</h2>
            
            <form class="admin-form" action="<?php echo $config['base_path']; ?>api/admin/atualizar_configuracoes.php" method="POST">
                
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                
                <div class="form-group">
                    <label for="site_nome">Nome da Rede Social</label>
                    <input type="text" id="site_nome" name="site_nome" value="<?php echo getConfigValue('site_nome', $configs_db); ?>" required>
                </div>

                <div class="form-group">
                    <label for="site_descricao">Slogan / Descrição Curta</label>
                    <input type="text" id="site_descricao" name="site_descricao" value="<?php echo getConfigValue('site_descricao', $configs_db); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email_contato">E-mail Administrativo</label>
                    <input type="email" id="email_contato" name="email_contato" value="<?php echo getConfigValue('email_contato', $configs_db); ?>" required>
                </div>
                
                <hr>
                
                <div class="form-group switch-group">
                    <label for="modo_manutencao">Modo Manutenção</label>
                    <p class="form-group-description">Bloqueia o acesso ao site para usuários comuns. Útil para atualizações críticas no banco de dados.</p>
                    <label class="switch">
                        <input type="checkbox" id="modo_manutencao" name="modo_manutencao" value="1" <?php echo (getConfigValue('modo_manutencao', $configs_db) == '1') ? 'checked' : ''; ?>>
                        <span class="slider round"></span>
                    </label>
                </div>

                <div class="form-group switch-group">
                    <label for="permite_cadastro">Portas Abertas (Novos Cadastros)</label>
                    <p class="form-group-description">Se desativado, nenhum novo usuário poderá se registrar, mantendo a rede em modo "convite" ou fechada.</p>
                    <label class="switch">
                        <input type="checkbox" id="permite_cadastro" name="permite_cadastro" value="1" <?php echo (getConfigValue('permite_cadastro', $configs_db) == '1') ? 'checked' : ''; ?>>
                        <span class="slider round"></span>
                    </label>
                </div>

                <div class="form-group switch-group">
                    <label for="modo_dev">Modo Desenvolvedor</label>
                    <p class="form-group-description">Força a limpeza de cache de CSS/JS e ativa a exibição de erros detalhados (Sentinela) a cada clique. Mantenha desligado em produção para máxima velocidade e segurança.</p>
                    <label class="switch">
                        <input type="checkbox" id="modo_dev" name="modo_dev" value="1" <?php echo (getConfigValue('modo_dev', $configs_db) == '1') ? 'checked' : ''; ?>>
                        <span class="slider round"></span>
                    </label>
                </div>

                <div class="form-group switch-group">
                    <div class="label-with-link">
                        <label for="modo_censura">Modo Censura (Máscara Social)</label>
                        <a href="<?php echo $config['base_path']; ?>admin/Palavras_Proibidas" class="manage-list-link">
                            <i class="fas fa-list-ul"></i> GERENCIAR PALAVRAS
                        </a>
                    </div>
                    <p class="form-group-description">Substitui automaticamente termos da lista negra por símbolos (ex: p.t@) na timeline e comentários.</p>
                    <label class="switch">
                        <input type="checkbox" id="modo_censura" name="modo_censura" value="1" <?php echo (getConfigValue('modo_censura', $configs_db) == '1') ? 'checked' : ''; ?>>
                        <span class="slider round"></span>
                    </label>
                </div>

                <hr>

                <div class="form-group">
                    <label for="versao_assets">Número da Versão (Cache Control)</label>
                    <p class="form-group-description">Altere este número (ex: 1.0.1 para 1.0.2) para forçar todos os navegadores a baixarem o novo CSS após uma mudança visual.</p>
                    <input type="text" id="versao_assets" name="versao_assets" value="<?php echo getConfigValue('versao_assets', $configs_db); ?>" required>
                </div>
                
                <div style="margin-top: 40px;">
                    <button type="submit" class="filter-btn" style="width: 100%; padding: 15px; font-size: 1rem;">
                        <i class="fas fa-save"></i> Gravar Todas as Alterações
                    </button>
                </div>

            </form>
        </div>
    </main>

    <script src="assets/js/admin.js"></script>

</body>
</html>
<?php $conn->close(); ?>