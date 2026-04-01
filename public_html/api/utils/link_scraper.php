<?php
/**
 * API Utilitária: Link Scraper (V80.7)
 * Responsável por capturar metadados de URLs para pré-visualização.
 */

session_start();
header('Content-Type: application/json');

// 1. Verificação de Segurança
// Apenas utilizadores logados podem usar o scraper para evitar abusos no servidor
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acesso negado.']);
    exit;
}

// 2. Captura e Validação da URL
$url = $_GET['url'] ?? '';

if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
    echo json_encode(['success' => false, 'error' => 'URL inválida ou ausente.']);
    exit;
}

/**
 * Função Principal de Captura via cURL
 */
function get_site_metadata($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Segue redirecionamentos (ex: bit.ly)
    curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8); // Não trava o servidor se o site for lento
    
    // Simula um navegador real (Mozilla) para contornar bloqueios básicos de bots
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    
    $html = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!$html || $http_code !== 200) {
        return null;
    }

    // Usamos o DOMDocument para processar o HTML de forma robusta
    $doc = new DOMDocument();
    // O @ suprime avisos de HTML malformado, comum em muitos sites
    @$doc->loadHTML('<?xml encoding="UTF-8">' . $html);
    $xpath = new DOMXPath($doc);

    // Função auxiliar para buscar conteúdo de meta tags
    $find_meta = function($property) use ($xpath) {
        // Tenta buscar por 'property' (Open Graph) ou 'name' (Meta padrão)
        $nodes = $xpath->query("//meta[@property='$property']/@content | //meta[@name='$property']/@content");
        return ($nodes->length > 0) ? $nodes->item(0)->nodeValue : null;
    };

    // Extração com Hierarquia de Prioridade
    $title = $find_meta('og:title') 
             ?? $find_meta('twitter:title') 
             ?? ($xpath->query("//title")->length > 0 ? $xpath->query("//title")->item(0)->nodeValue : "Link Externo");

    $description = $find_meta('og:description') 
                   ?? $find_meta('description') 
                   ?? $find_meta('twitter:description') 
                   ?? "";

    $image = $find_meta('og:image') 
             ?? $find_meta('twitter:image') 
             ?? "";

    // Limpeza de caracteres e codificação
    return [
        'title'       => trim(mb_convert_encoding($title, 'UTF-8', 'auto')),
        'description' => trim(mb_convert_encoding($description, 'UTF-8', 'auto')),
        'image'       => $image
    ];
}

// 3. Execução e Resposta
$meta = get_site_metadata($url);

if ($meta) {
    echo json_encode(array_merge(['success' => true], $meta));
} else {
    echo json_encode(['success' => false, 'error' => 'Não foi possível ler os dados deste site.']);
}