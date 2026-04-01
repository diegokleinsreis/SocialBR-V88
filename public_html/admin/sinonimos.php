<?php
/**
 * admin/sinonimos.php - Dicionário de Inteligência
 * PAPEL: Mapear termos digitados para termos reais de busca.
 * VERSÃO: 2.3 - Correção de Botão Escondido e Overflow de Tabela
 */

if (!defined('ACESSO_ROTEADOR')) {
    header("Location: /admin/busca");
    exit;
}

$mensagem = "";

// 1. PROCESSAMENTO DE AÇÕES
if (isset($_POST['acao']) && $_POST['acao'] === 'add') {
    $digitado = mb_strtolower(trim($_POST['termo_digitado']));
    $real     = mb_strtolower(trim($_POST['termo_real']));

    if (!empty($digitado) && !empty($real)) {
        $stmt = $conn->prepare("INSERT INTO busca_sinonimos (termo_digitado, termo_real) VALUES (?, ?) ON DUPLICATE KEY UPDATE termo_real = ?");
        $stmt->bind_param("sss", $digitado, $real, $real);
        if ($stmt->execute()) {
            $mensagem = "Mapeamento definido: '$digitado' agora levará a '$real'.";
        }
        $stmt->close();
    }
}

if (isset($_GET['del'])) {
    $id_del = (int)$_GET['del'];
    $stmt = $conn->prepare("DELETE FROM busca_sinonimos WHERE id = ?");
    $stmt->bind_param("i", $id_del);
    if ($stmt->execute()) {
        $mensagem = "Mapeamento removido.";
    }
    $stmt->close();
}

// 2. BUSCA DA LISTA ATUAL
$sinonimos = $conn->query("SELECT * FROM busca_sinonimos ORDER BY data_criacao DESC")->fetch_all(MYSQLI_ASSOC);
$termo_pre_preenchido = isset($_GET['sugerir']) ? htmlspecialchars($_GET['sugerir']) : '';
$page_title = "Dicionário de Sinônimos";

// A. O Header abre o HTML, HEAD e BODY
include __DIR__ . '/templates/admin_header.php'; 
?>

<link rel="stylesheet" href="<?php echo $config['base_path']; ?>admin/assets/css/components/_admin_busca.css">
    
<style>
    /* FIX CRÍTICO: Container de Conteúdo */
    .admin-content {
        padding: 20px;
        width: 100%;
        box-sizing: border-box;
    }

    .sinonimo-card { 
        background: #fff; 
        padding: clamp(15px, 5vw, 30px); 
        border-radius: 16px; 
        border: 1px solid #dddfe2; 
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        width: 100%;
        box-sizing: border-box;
    }

    /* FORMULÁRIO RESPONSIVO (Fix para o botão não sumir) */
    .form-sinonimo-wrapper {
        background: #f8f9fa; 
        padding: 20px; 
        border-radius: 12px; 
        margin-bottom: 30px;
        border: 1px solid #eaebed;
    }

    .form-sinonimo { 
        display: flex;
        flex-wrap: wrap; /* Permite que o botão caia para a linha de baixo se necessário */
        gap: 20px;
        align-items: flex-end; 
    }

    .input-group { 
        flex: 1; 
        min-width: 250px; /* Garante que os inputs tenham tamanho legível */
        display: flex; 
        flex-direction: column; 
        gap: 8px; 
    }

    .input-group label { font-size: 0.85rem; font-weight: 700; color: #0C2D54; }
    .input-group input { 
        padding: 12px; 
        border: 2px solid #dddfe2; 
        border-radius: 10px; 
        font-size: 1rem; 
        width: 100%;
        box-sizing: border-box;
    }

    /* BOTÃO FIXO */
    .btn-submit-sinonimo {
        white-space: nowrap;
        height: 48px;
        padding: 0 25px;
        flex-shrink: 0;
    }

    /* TABELA COM ROLAGEM INTERNA APENAS */
    .table-responsive { 
        width: 100%; 
        overflow-x: auto; 
        margin-top: 20px; 
        border-radius: 10px;
        border: 1px solid #f0f2f5;
    }

    .sinonimo-table { width: 100%; border-collapse: collapse; min-width: 500px; }
    .sinonimo-table th { 
        background: #0C2D54; 
        color: #fff; 
        text-align: left; 
        padding: 15px; 
        font-size: 0.75rem; 
        text-transform: uppercase; 
    }
    .sinonimo-table td { padding: 15px; border-bottom: 1px solid #f0f2f5; vertical-align: middle; }

    .btn-delete-action { 
        color: #dc3545; 
        background: #fff1f2; 
        padding: 6px 12px; 
        border-radius: 6px; 
        text-decoration: none; 
        font-weight: 600;
        font-size: 0.8rem;
    }

    @media (max-width: 768px) {
        .input-group { min-width: 100%; }
        .btn-submit-sinonimo { width: 100%; }
        .admin-header-page { flex-direction: column; align-items: flex-start; gap: 15px; }
    }
</style>

<?php 
// B. Carrega componente de navegação mobile (Botão Hambúrguer)
include __DIR__ . '/templates/admin_mobile_nav.php'; 
?>

<div class="main-layout">
    
    <?php include __DIR__ . '/templates/admin_sidebar.php'; ?>

    <main class="main-content">
        <div class="admin-content">
            
            <header class="admin-header-page" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div>
                    <h1 style="font-size: 1.8rem; color: #0C2D54;"><i class="fas fa-book"></i> Dicionário de Sinônimos</h1>
                    <p style="color: #65676b; margin-top: 5px;">Mapeie termos e melhore a precisão da descoberta.</p>
                </div>
                <a href="<?php echo $config['base_path']; ?>admin/busca" class="btn-premium" style="background: #65676b; height: fit-content;">
                    <i class="fas fa-arrow-left"></i> Voltar ao Painel
                </a>
            </header>

            <?php if ($mensagem): ?>
                <div class="admin-card" style="border-left: 5px solid #0064d2; margin-bottom: 20px; padding: 15px;">
                    <i class="fas fa-info-circle" style="color: #0064d2;"></i> <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <div class="sinonimo-card">
                <div class="form-sinonimo-wrapper">
                    <form method="POST" class="form-sinonimo">
                        <input type="hidden" name="acao" value="add">
                        <div class="input-group">
                            <label><i class="fas fa-keyboard"></i> Usuário digita:</label>
                            <input type="text" name="termo_digitado" value="<?php echo $termo_pre_preenchido; ?>" placeholder="Ex: trampo" required>
                        </div>
                        <div class="input-group">
                            <label><i class="fas fa-bullseye"></i> Sistema busca por:</label>
                            <input type="text" name="termo_real" placeholder="Ex: empregos" required>
                        </div>
                        <button type="submit" class="btn-premium btn-submit-sinonimo">
                            Criar Mapeamento
                        </button>
                    </form>
                </div>

                <h3 style="color: #0C2D54; margin-bottom: 20px;">Mapeamentos Ativos (<?php echo count($sinonimos); ?>)</h3>

                <div class="table-responsive">
                    <table class="sinonimo-table">
                        <thead>
                            <tr>
                                <th>Termo de Entrada</th>
                                <th style="width: 40px;"></th>
                                <th>Resultado da Busca</th>
                                <th style="text-align: right;">Gestão</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($sinonimos)): ?>
                                <tr>
                                    <td colspan="4" style="color:#65676b; padding:40px; text-align:center;">
                                        <i class="fas fa-folder-open" style="font-size: 2rem; display: block; margin-bottom: 10px; opacity: 0.5;"></i>
                                        Nenhum sinônimo mapeado ainda.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($sinonimos as $s): ?>
                                    <tr>
                                        <td><span style="font-weight:600; color: #1c1e21;">"<?php echo htmlspecialchars($s['termo_digitado']); ?>"</span></td>
                                        <td><i class="fas fa-arrow-right" style="color:#65676b; font-size: 0.8rem;"></i></td>
                                        <td><span style="color:#0064d2; font-weight:700;"><?php echo htmlspecialchars($s['termo_real']); ?></span></td>
                                        <td style="text-align: right;">
                                            <a href="<?php echo $config['base_path']; ?>admin/busca_sinonimos?del=<?php echo $s['id']; ?>" 
                                               class="btn-delete-action" 
                                               onclick="return confirm('Deseja realmente excluir este mapeamento?')">
                                                <i class="fas fa-trash-alt"></i> Remover
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>
</body>
</html>