<?php
// raiox.php
require_once __DIR__ . '/config/database.php'; // Ajuste o caminho se necessário

echo "<h2>🔍 Diagnóstico de Banco de Dados - Marketplace</h2>";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // 1. O ID 3 existe?
    $id_busca = 3;
    $stmt = $pdo->prepare("SELECT * FROM Marketplace_Anuncios WHERE id = ?");
    $stmt->execute([$id_busca]);
    $anuncio = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$anuncio) {
        echo "<p style='color:red;'>❌ ERRO: O ID <strong>$id_busca</strong> não existe na tabela <code>Marketplace_Anuncios</code>.</p>";
        
        // Sugestão de IDs existentes
        $ids = $pdo->query("SELECT id FROM Marketplace_Anuncios LIMIT 5")->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>IDs que realmente existem no seu banco: " . implode(", ", $ids) . "</p>";
    } else {
        echo "<p style='color:green;'>✅ SUCESSO: O anúncio '$id_busca' foi encontrado.</p>";
        
        // 2. Verificar a Postagem
        $post_id = $anuncio['id_postagem'];
        $stmtPost = $pdo->prepare("SELECT id, id_usuario, status FROM Postagens WHERE id = ?");
        $stmtPost->execute([$post_id]);
        $post = $stmtPost->fetch(PDO::FETCH_ASSOC);

        if (!$post) {
            echo "<p style='color:red;'>❌ ERRO CRÍTICO: O anúncio existe, mas a Postagem ID $post_id vinculada a ele NÃO existe!</p>";
        } else {
            echo "<p style='color:green;'>✅ Postagem vinculada encontrada (Status: {$post['status']}).</p>";
            
            // 3. Verificar o Usuário
            $user_id = $post['id_usuario'];
            $stmtUser = $pdo->prepare("SELECT id, nome FROM Usuarios WHERE id = ?");
            $stmtUser->execute([$user_id]);
            if (!$stmtUser->fetch()) {
                echo "<p style='color:red;'>❌ ERRO: O usuário dono do anúncio (ID $user_id) não existe no banco.</p>";
            } else {
                echo "<p style='color:green;'>✅ Usuário dono encontrado.</p>";
                echo "<h3>🎉 Tudo parece OK no banco. O erro pode ser o caminho da URL no Feed.</h3>";
            }
        }
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}