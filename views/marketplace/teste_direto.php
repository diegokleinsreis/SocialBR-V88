<?php
// marketplace/teste_direto.php
require_once __DIR__ . '/../../config/database.php';
session_start();

$id_tentado = $_GET['id'] ?? 3; // Pega o ID da URL ou usa 3 como padrão

echo "<h1>🔍 Diagnóstico de Emergência</h1>";
echo "Tentando buscar o ID: <strong>$id_tentado</strong><br><hr>";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // 1. Verifica na tabela de ANÚNCIOS
    $stmt1 = $pdo->prepare("SELECT * FROM Marketplace_Anuncios WHERE id = ?");
    $stmt1->execute([$id_tentado]);
    $anuncio = $stmt1->fetch(PDO::FETCH_ASSOC);

    if ($anuncio) {
        echo "<p style='color:green'>✅ SUCESSO: O ID $id_tentado existe na tabela Marketplace_Anuncios!</p>";
        echo "<pre>Dados do Anúncio: " . print_r($anuncio, true) . "</pre>";
        
        // 2. Verifica se a POSTAGEM existe
        $stmt2 = $pdo->prepare("SELECT * FROM Postagens WHERE id = ?");
        $stmt2->execute([$anuncio['id_postagem']]);
        if ($stmt2->fetch()) {
            echo "<p style='color:green'>✅ SUCESSO: A Postagem vinculada (" . $anuncio['id_postagem'] . ") existe!</p>";
        } else {
            echo "<p style='color:red'>❌ ERRO: O anúncio existe, mas a POSTAGEM vinculada sumiu do banco!</p>";
        }
    } else {
        echo "<p style='color:red'>❌ ERRO: O ID $id_tentado NÃO EXISTE na tabela Marketplace_Anuncios.</p>";
        
        // 3. Tenta ver se esse ID é na verdade um ID de Postagem
        $stmt3 = $pdo->prepare("SELECT * FROM Marketplace_Anuncios WHERE id_postagem = ?");
        $stmt3->execute([$id_tentado]);
        if ($res = $stmt3->fetch()) {
            echo "<p style='color:blue'>ℹ️ INFO: Achei! O ID $id_tentado não é o ID do anúncio, é o ID da POSTAGEM deste item.</p>";
        }
    }

} catch (Exception $e) {
    echo "Erro de conexão: " . $e->getMessage();
}