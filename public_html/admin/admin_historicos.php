<?php
// 1. AUTENTICAÇÃO E CONEXÃO
require_once 'admin_auth.php'; // Garante que só o admin veja
// $conn, $config, e $asset_version (do database.php) já estão disponíveis aqui

// 2. VALIDAÇÃO DOS PARÂMETROS DA URL
$tipo = $_GET['tipo'] ?? ''; // Será 'post' ou 'comentario'
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die("ID de conteúdo inválido.");
}

// 3. INICIALIZA AS VARIÁVEIS DINÂMICAS
$page_title = 'Histórico de Edição';
$back_link = 'index.php'; // Link padrão para "Voltar"
$label_conteudo = 'Conteúdo';
$item_result = null;
$history_result = null;
$autor_nome = 'Desconhecido';
$conteudo_atual = '';

// 4. LÓGICA DINÂMICA BASEADA NO TIPO
if ($tipo === 'post') {
    $page_title = "Histórico de Edição da Postagem #{$id}";
    $back_link = "admin_postagens.php";
    $label_conteudo = "Postagem";

    // Busca dados da postagem original
    $sql_item = "SELECT p.conteudo_texto, u.nome, u.sobrenome 
                 FROM Postagens p 
                 JOIN Usuarios u ON p.id_usuario = u.id 
                 WHERE p.id = ?";
    $stmt_item = $conn->prepare($sql_item);
    $stmt_item->bind_param("i", $id);
    $stmt_item->execute();
    $item_result = $stmt_item->get_result()->fetch_assoc();
    $stmt_item->close();

    // Busca histórico de edições da postagem
    $sql_history = "SELECT conteudo_antigo, data_edicao FROM Postagens_Edicoes WHERE id_postagem = ? ORDER BY data_edicao DESC";
    $stmt_history = $conn->prepare($sql_history);
    $stmt_history->bind_param("i", $id);

} elseif ($tipo === 'comentario') {
    $page_title = "Histórico de Edição do Comentário #{$id}";
    $back_link = "admin_comentarios.php";
    $label_conteudo = "Comentário";

    // Busca dados do comentário original
    $sql_item = "SELECT c.conteudo_texto, u.nome, u.sobrenome 
                 FROM Comentarios c 
                 JOIN Usuarios u ON c.id_usuario = u.id 
                 WHERE c.id = ?";
    $stmt_item = $conn->prepare($sql_item);
    $stmt_item->bind_param("i", $id);
    $stmt_item->execute();
    $item_result = $stmt_item->get_result()->fetch_assoc();
    $stmt_item->close();

    // Busca histórico de edições do comentário
    $sql_history = "SELECT conteudo_antigo, data_edicao FROM Comentarios_Edicoes WHERE id_comentario = ? ORDER BY data_edicao DESC";
    $stmt_history = $conn->prepare($sql_history);
    $stmt_history->bind_param("i", $id);

} else {
    die("Tipo de histórico inválido. Deve ser 'post' ou 'comentario'.");
}

// 5. VERIFICA SE O ITEM FOI ENCONTRADO E EXECUTA A QUERY DE HISTÓRICO
if (!$item_result) {
    die("O item ($tipo) com ID $id não foi encontrado.");
}

// Prepara as variáveis para o HTML
$autor_nome = htmlspecialchars($item_result['nome'] . ' ' . $item_result['sobrenome']);
$conteudo_atual = nl2br(htmlspecialchars($item_result['conteudo_texto']));

// Executa a query de histórico que foi preparada
$stmt_history->execute();
$history_result = $stmt_history->get_result();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <link rel="stylesheet" href="assets/css/admin.css?v=<?php echo $asset_version; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <?php
    include 'templates/admin_header.php'; 
    include 'templates/admin_mobile_nav.php';
    ?>

    <main class="admin-main-content">
        <a href="<?php echo $back_link; ?>" class="admin-back-button"><i class="fas fa-arrow-left"></i> Voltar para <?php echo $label_conteudo; ?>s</a>
        
        <div class="admin-card">
            <h1><i class="fas fa-history"></i> Histórico de Edição do <?php echo $label_conteudo; ?> #<?php echo $id; ?></h1>
            <p><strong>Autor:</strong> <?php echo $autor_nome; ?></p>
            <p><strong>Conteúdo Atual:</strong> "<?php echo $conteudo_atual; ?>"</p>
        </div>

        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Versão Anterior do Conteúdo</th>
                        <th>Data da Edição</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($history_result && $history_result->num_rows > 0): ?>
                        <?php while($edicao = $history_result->fetch_assoc()): ?>
                            <tr>
                                <td class="comment-content-cell"><?php echo nl2br(htmlspecialchars($edicao['conteudo_antigo'])); ?></td>
                                <td><?php echo date("d/m/Y \à\s H:i:s", strtotime($edicao['data_edicao'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="2">Nenhuma edição anterior encontrada para este <?php echo strtolower($label_conteudo); ?>.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    
</body>
</html>
<?php
$stmt_history->close();
$conn->close();
?>