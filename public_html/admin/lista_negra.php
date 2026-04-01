<?php
/**
 * admin/lista_negra.php - Gestão de Termos Proibidos
 * PAPEL: Adicionar e remover palavras da blacklist de busca.
 * VERSÃO: 1.2 - Sincronização de Layout e Responsividade Mobile (socialbr.lol)
 */

if (!defined('ACESSO_ROTEADOR')) {
    header("Location: /admin/busca");
    exit;
}

// 1. PROCESSAMENTO DE AÇÕES (Adicionar/Remover)
$mensagem = "";

// Adicionar Termo
if (isset($_POST['acao']) && $_POST['acao'] === 'add') {
    $novo_termo = mb_strtolower(trim($_POST['termo']));
    if (!empty($novo_termo)) {
        $stmt = $conn->prepare("INSERT IGNORE INTO Palavras_Proibidas (termo) VALUES (?)");
        $stmt->bind_param("s", $novo_termo);
        if ($stmt->execute()) {
            $mensagem = "Termo '$novo_termo' bloqueado com sucesso!";
        }
        $stmt->close();
    }
}

// Remover Termo
if (isset($_GET['del'])) {
    $id_del = (int)$_GET['del'];
    $stmt = $conn->prepare("DELETE FROM Palavras_Proibidas WHERE id = ?");
    $stmt->bind_param("i", $id_del);
    if ($stmt->execute()) {
        $mensagem = "Termo removido da blacklist.";
    }
    $stmt->close();
}

// 2. BUSCA DA LISTA ATUAL
$blacklist = $conn->query("SELECT * FROM Palavras_Proibidas ORDER BY termo ASC")->fetch_all(MYSQLI_ASSOC);

$termo_pre_preenchido = isset($_GET['adicionar']) ? htmlspecialchars($_GET['adicionar']) : '';
$page_title = "Gerenciar Blacklist";

// A. O Header abre o HTML, HEAD e BODY
include __DIR__ . '/templates/admin_header.php'; 
?>

<link rel="stylesheet" href="<?php echo $config['base_path']; ?>admin/assets/css/components/_admin_busca.css">
    
<style>
    /* FIX DE CONTENÇÃO */
    .admin-content {
        padding: 20px;
        width: 100%;
        box-sizing: border-box;
    }

    .blacklist-container { 
        background: #fff; 
        padding: clamp(15px, 5vw, 30px); 
        border-radius: 16px; 
        border: 1px solid #dddfe2; 
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        width: 100%;
        box-sizing: border-box;
    }

    /* FORMULÁRIO FLEXÍVEL (Anti-quebra) */
    .form-add-blacklist { 
        display: flex;
        flex-wrap: wrap; 
        gap: 15px; 
        margin-bottom: 30px; 
        background: #f0f2f5; 
        padding: 20px; 
        border-radius: 12px; 
        align-items: center;
    }

    .form-add-blacklist input { 
        flex: 1; 
        min-width: 250px;
        padding: 14px; 
        border: 2px solid #dddfe2; 
        border-radius: 10px; 
        font-size: 1rem; 
        box-sizing: border-box;
    }

    .btn-block-action {
        height: 52px;
        padding: 0 25px;
        white-space: nowrap;
        flex-shrink: 0;
    }

    /* SISTEMA DE BADGES */
    .term-badges-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 20px;
    }

    .term-badge { 
        display: inline-flex; 
        align-items: center; 
        background: #f0f2f5; 
        padding: 8px 15px; 
        border-radius: 20px; 
        border: 1px solid #dddfe2; 
        font-weight: 600; 
        color: #0C2D54; 
        transition: all 0.2s;
    }

    .term-badge:hover { background: #e4e6eb; }

    .term-badge a { 
        margin-left: 10px; 
        color: #dc3545; 
        text-decoration: none; 
        font-size: 1.2rem; 
        line-height: 1;
    }

    .term-badge a:hover { color: #a71d2a; }

    @media (max-width: 768px) {
        .form-add-blacklist input { min-width: 100%; }
        .btn-block-action { width: 100%; }
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
            
            <header class="admin-header-page" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <div>
                    <h1 style="font-size: 1.8rem; color: #0C2D54;"><i class="fas fa-user-slash"></i> Blacklist de Termos</h1>
                    <p style="color: #65676b; margin-top: 5px;">Palavras que nunca aparecerão nas sugestões de busca.</p>
                </div>
                <a href="<?php echo $config['base_path']; ?>admin/busca" class="btn-premium" style="background: #65676b; height: fit-content;">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </header>

            <?php if ($mensagem): ?>
                <div class="admin-card" style="border-left: 5px solid #28a745; margin-bottom: 20px; padding: 15px;">
                    <i class="fas fa-check-circle" style="color: #28a745;"></i> <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <div class="blacklist-container">
                <form method="POST" class="form-add-blacklist">
                    <input type="hidden" name="acao" value="add">
                    <input type="text" name="termo" value="<?php echo $termo_pre_preenchido; ?>" placeholder="Ex: palavrão, termo ofensivo..." required>
                    <button type="submit" class="btn-premium btn-block-action">Bloquear Termo</button>
                </form>

                <h3 style="color: #0C2D54; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px;">
                    Termos Bloqueados Atualmente (<?php echo count($blacklist); ?>)
                </h3>
                
                <div class="term-badges-grid">
                    <?php if (empty($blacklist)): ?>
                        <p style="color: #65676b; padding: 20px;">Nenhum termo bloqueado ainda.</p>
                    <?php else: ?>
                        <?php foreach ($blacklist as $item): ?>
                            <span class="term-badge">
                                <?php echo htmlspecialchars($item['termo']); ?>
                                <a href="<?php echo $config['base_path']; ?>admin/Palavras_Proibidas?del=<?php echo $item['id']; ?>" 
                                   title="Remover" onclick="return confirm('Deseja desbloquear este termo?')">&times;</a>
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>
</div>
</body>
</html>