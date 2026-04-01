<?php
/**
 * src/MarketplaceLogic.php
 * Lógica de Negócio do Marketplace (Backend)
 * Versão: 12.8 - COMPLETO (Correção de Edição + Sincronização SQL V67)
 */

require_once __DIR__ . '/../config/database.php';

class MarketplaceLogic {
    private $pdo;
    private $config;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $configFile = __DIR__ . '/../config/marketplace.php';
        if (file_exists($configFile)) {
            $this->config = require $configFile;
        } else {
            $this->config = ['seguranca' => ['exigir_cpf' => false]];
        }
    }

    /**
     * 1. FEED PÚBLICO
     * Sincronizado com colunas: url_midia, id_postagem, nome/sobrenome
     */
    public function listarAnuncios($filtros = [], $pagina = 1, $limite = 20, $visualizador_id = 0) {
        $offset = ($pagina - 1) * $limite;
        $params = [$visualizador_id ?? 0]; 
        
        $sql = "SELECT 
                    ma.*, 
                    CONCAT(u.nome, ' ', u.sobrenome) as vendedor_nome,
                    u.foto_perfil_url as vendedor_avatar,
                    u.cpf as vendedor_cpf,
                    (SELECT pm.url_midia FROM Postagens_Midia pm WHERE pm.id_postagem = ma.id_postagem ORDER BY pm.id ASC LIMIT 1) as capa_anuncio,
                    (SELECT COUNT(*) FROM Curtidas c WHERE c.id_postagem = ma.id_postagem) as total_likes,
                    (SELECT COUNT(*) FROM Curtidas c WHERE c.id_postagem = ma.id_postagem AND c.id_usuario = ?) as eu_curti
                FROM Marketplace_Anuncios ma
                INNER JOIN Postagens p ON ma.id_postagem = p.id
                INNER JOIN Usuarios u ON p.id_usuario = u.id
                WHERE ma.status_venda IN ('disponivel', 'vendido') 
                AND p.status = 'ativo'";

        if (!empty($filtros['categoria'])) {
            $sql .= " AND ma.categoria = ?";
            $params[] = $filtros['categoria'];
        }

        if (!empty($filtros['busca'])) {
            $termo = '%' . $filtros['busca'] . '%';
            $sql .= " AND (ma.titulo_produto LIKE ? OR ma.cidade LIKE ?)";
            $params[] = $termo; $params[] = $termo;
        }

        $sql .= " ORDER BY FIELD(ma.status_venda, 'disponivel', 'vendido'), ma.criado_em DESC LIMIT $limite OFFSET $offset";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($resultados as &$item) {
                $item['preco_formatado'] = 'R$ ' . number_format($item['preco'], 2, ',', '.');
                if (empty($item['capa_anuncio'])) $item['capa_anuncio'] = 'assets/images/placeholder-image.png';
            }
            return $resultados;
        } catch (PDOException $e) {
            error_log("Erro listarAnuncios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 2. DETALHES DO PRODUTO
     * Usa views_count conforme o SQL real
     */
    public function obterDetalhesAnuncio($id, $visualizador_id = null) {
        if (!$id || $id <= 0) return false;

        try {
            $this->pdo->prepare("UPDATE Marketplace_Anuncios SET views_count = views_count + 1 WHERE id = ?")->execute([$id]);

            $sql = "SELECT 
                        ma.*,
                        p.id as post_id,
                        p.id_usuario as vendedor_id,
                        p.data_postagem,
                        CONCAT(u.nome, ' ', u.sobrenome) as vendedor_nome_completo,
                        u.foto_perfil_url as vendedor_avatar,
                        u.data_cadastro as vendedor_desde,
                        u.cpf as vendedor_cpf,
                        (SELECT COUNT(*) FROM Curtidas c WHERE c.id_postagem = p.id) as total_likes,
                        (SELECT COUNT(*) FROM Curtidas c WHERE c.id_postagem = p.id AND c.id_usuario = ?) as eu_curti,
                        (SELECT COUNT(*) FROM Marketplace_Interesses mi WHERE mi.id_anuncio = ma.id) as total_interessados,
                        (SELECT COUNT(*) FROM Marketplace_Interesses mi WHERE mi.id_anuncio = ma.id AND mi.id_usuario = ?) as tenho_interesse
                    FROM Marketplace_Anuncios ma
                    INNER JOIN Postagens p ON ma.id_postagem = p.id
                    INNER JOIN Usuarios u ON p.id_usuario = u.id
                    WHERE ma.id = ?";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$visualizador_id ?? 0, $visualizador_id ?? 0, $id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) return false;

            $stmtFotos = $this->pdo->prepare("SELECT url_midia FROM Postagens_Midia WHERE id_postagem = ? ORDER BY id ASC");
            $stmtFotos->execute([$item['post_id']]);
            $item['fotos'] = $stmtFotos->fetchAll(PDO::FETCH_COLUMN);

            if (empty($item['fotos'])) $item['fotos'] = ['assets/images/placeholder-image.png'];
            
            $item['preco_formatado'] = 'R$ ' . number_format($item['preco'], 2, ',', '.');
            $item['is_owner'] = ($visualizador_id && $visualizador_id == $item['vendedor_id']);
            $item['descricao_completa'] = $item['descricao_produto']; 

            return $item;
        } catch (PDOException $e) { return false; }
    }

    /**
     * 3. MEUS ANÚNCIOS (Painel de Gestão)
     */
    public function listarAnunciosDoUsuario($usuario_id) {
        try {
            $sql = "SELECT 
                        ma.id, 
                        ma.titulo_produto, 
                        ma.preco, 
                        ma.status_venda, 
                        ma.views_count as visualizacoes, 
                        p.data_postagem as data_criacao,
                        (SELECT pm.url_midia FROM Postagens_Midia pm WHERE pm.id_postagem = p.id ORDER BY pm.id ASC LIMIT 1) as capa,
                        (SELECT COUNT(*) FROM Curtidas c WHERE c.id_postagem = p.id) as total_likes,
                        (SELECT COUNT(*) FROM Marketplace_Interesses mi WHERE mi.id_anuncio = ma.id) as total_interessados
                    FROM Marketplace_Anuncios ma
                    INNER JOIN Postagens p ON ma.id_postagem = p.id
                    WHERE p.id_usuario = ? AND p.status != 'excluido_pelo_usuario'
                    ORDER BY p.data_postagem DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$usuario_id]);
            $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($itens as &$item) {
                $item['preco_formatado'] = 'R$ ' . number_format($item['preco'], 2, ',', '.');
                $item['data_formatada'] = date('d/m/Y', strtotime($item['data_criacao']));
                if (empty($item['capa'])) $item['capa'] = 'assets/images/placeholder-image.png';
            }
            return $itens;
        } catch (PDOException $e) { return []; }
    }

    /**
     * 4. BUSCA PARA EDIÇÃO (Correção do Fatal Error)
     * Recupera dados do anúncio e fotos para popular o formulário de edição.
     */
    public function obterAnuncioParaEdicao($anuncio_id, $usuario_id) {
        try {
            $sql = "SELECT ma.*, p.id as post_id, 
                           u.nome as vendedor_nome, u.foto_perfil_url as vendedor_avatar
                    FROM Marketplace_Anuncios ma 
                    INNER JOIN Postagens p ON ma.id_postagem = p.id 
                    INNER JOIN Usuarios u ON p.id_usuario = u.id
                    WHERE ma.id = ? AND p.id_usuario = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$anuncio_id, (int)$usuario_id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($item) {
                // Fotos usando url_midia e id_postagem
                $stmtFotos = $this->pdo->prepare("SELECT id, url_midia FROM Postagens_Midia WHERE id_postagem = ? ORDER BY id ASC");
                $stmtFotos->execute([$item['post_id']]);
                $item['fotos'] = $stmtFotos->fetchAll(PDO::FETCH_ASSOC);
                
                $item['preco_input'] = number_format($item['preco'], 2, ',', '.');
                $item['descricao_completa'] = $item['descricao_produto'];
            }
            return $item;
        } catch (PDOException $e) { 
            error_log("Erro em obterAnuncioParaEdicao: " . $e->getMessage());
            return false; 
        }
    }

    /**
     * 5. DASHBOARD (Estatísticas)
     */
    public function obterEstatisticasVendedor($usuario_id) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_geral,
                        SUM(CASE WHEN ma.status_venda = 'disponivel' THEN 1 ELSE 0 END) as ativos,
                        SUM(CASE WHEN ma.status_venda = 'vendido' THEN 1 ELSE 0 END) as vendidos,
                        COALESCE(SUM(ma.views_count), 0) as total_views
                    FROM Marketplace_Anuncios ma
                    INNER JOIN Postagens p ON ma.id_postagem = p.id
                    WHERE p.id_usuario = ? AND p.status != 'excluido_pelo_usuario'";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$usuario_id]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $stats ?: ['ativos' => 0, 'vendidos' => 0, 'total_views' => 0];
        } catch (PDOException $e) {
            return ['ativos' => 0, 'vendidos' => 0, 'total_views' => 0];
        }
    }

    /**
     * 6. MÉTODOS DE AÇÃO (Status e Exclusão)
     */
    public function atualizarStatus($anuncio_id, $usuario_id, $novo_status) {
        try {
            $sql = "UPDATE Marketplace_Anuncios ma 
                    INNER JOIN Postagens p ON ma.id_postagem = p.id 
                    SET ma.status_venda = ? 
                    WHERE ma.id = ? AND p.id_usuario = ?";
            return $this->pdo->prepare($sql)->execute([$novo_status, $anuncio_id, $usuario_id]);
        } catch (PDOException $e) { return false; }
    }

    public function excluirAnuncio($anuncio_id, $usuario_id) {
        try {
            $sql = "UPDATE Postagens p 
                    INNER JOIN Marketplace_Anuncios ma ON ma.id_postagem = p.id 
                    SET p.status = 'excluido_pelo_usuario' 
                    WHERE ma.id = ? AND p.id_usuario = ?";
            return $this->pdo->prepare($sql)->execute([$anuncio_id, $usuario_id]);
        } catch (PDOException $e) { return false; }
    }

    private function formatarPrecoParaBanco($preco) {
        return str_replace(',', '.', str_replace(['R$', ' ', '.'], '', $preco));
    }
}