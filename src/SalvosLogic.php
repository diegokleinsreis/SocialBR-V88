<?php
/**
 * SalvosLogic.php
 * Classe responsável pela inteligência de negócio do Módulo de Salvos.
 * Gerencia coleções, itens salvos e filtros globais com alta performance.
 * VERSÃO: V75.2 - Visibilidade Pública de Coleções (socialbr.lol)
 */

class SalvosLogic {
    private PDO $db;
    private const NOME_COLECAO_PADRAO = 'Geral';

    /**
     * Construtor da Classe
     * @param PDO $pdo Instância de conexão PDO
     */
    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    /**
     * INTELIGÊNCIA DE AUTO-GÊNESE
     * Busca o ID da coleção "Geral" do usuário. Caso não exista, cria automaticamente.
     */
    public function getOrCreateGeral(int $usuario_id): int {
        try {
            $sql = "SELECT id FROM Salvos_Colecoes 
                    WHERE usuario_id = :uid AND nome = :nome 
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':uid'  => $usuario_id,
                ':nome' => self::NOME_COLECAO_PADRAO
            ]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                return (int)$resultado['id'];
            }

            $sqlInsert = "INSERT INTO Salvos_Colecoes (usuario_id, nome, privacidade) 
                         VALUES (:uid, :nome, 'privada')";
            $stmtInsert = $this->db->prepare($sqlInsert);
            $stmtInsert->execute([
                ':uid'  => $usuario_id,
                ':nome' => self::NOME_COLECAO_PADRAO
            ]);

            return (int)$this->db->lastInsertId();

        } catch (PDOException $e) {
            error_log("Erro em SalvosLogic::getOrCreateGeral: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * LISTAGEM DE COLEÇÕES
     * Retorna todas as pastas do usuário, garantindo que "Geral" venha primeiro.
     */
    public function listarColecoes(int $usuario_id): array {
        try {
            $sql = "SELECT *, 
                    (SELECT COUNT(*) FROM Postagens_Salvas WHERE colecao_id = Salvos_Colecoes.id) as total_itens
                    FROM Salvos_Colecoes 
                    WHERE usuario_id = :uid 
                    ORDER BY (nome = :nome_padrao) DESC, nome ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':uid' => $usuario_id,
                ':nome_padrao' => self::NOME_COLECAO_PADRAO
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * CRIAÇÃO DE NOVA COLEÇÃO
     */
    public function criarColecao(int $usuario_id, string $nome, string $privacidade = 'privada'): bool {
        $nome = htmlspecialchars(trim($nome));
        if (strtolower($nome) === strtolower(self::NOME_COLECAO_PADRAO)) {
            return false;
        }

        try {
            $sql = "INSERT INTO Salvos_Colecoes (usuario_id, nome, privacidade) 
                    VALUES (:uid, :nome, :priv)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':uid'  => $usuario_id,
                ':nome' => $nome,
                ':priv' => $privacidade
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * EDIÇÃO DE COLEÇÃO (TRAVA DE IMUTABILIDADE)
     */
    public function editarColecao(int $colecao_id, int $usuario_id, string $novo_nome, string $nova_priv): bool {
        try {
            if ($this->isGeral($colecao_id)) {
                return false; 
            }

            $sql = "UPDATE Salvos_Colecoes 
                    SET nome = :nome, privacidade = :priv 
                    WHERE id = :cid AND usuario_id = :uid";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':nome' => htmlspecialchars(trim($novo_nome)),
                ':priv' => $nova_priv,
                ':cid'  => $colecao_id,
                ':uid'  => $usuario_id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * EXCLUSÃO DE COLEÇÃO
     */
    public function excluirColecao(int $colecao_id, int $usuario_id): bool {
        try {
            if ($this->isGeral($colecao_id)) {
                return false;
            }

            $sql = "DELETE FROM Salvos_Colecoes WHERE id = :cid AND usuario_id = :uid";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':cid' => $colecao_id, ':uid' => $usuario_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * SALVAR ITEM (VINCULADOR)
     */
    public function salvarItem(int $usuario_id, int $post_id, ?int $colecao_id = null): bool {
        try {
            if (!$colecao_id) {
                $colecao_id = $this->getOrCreateGeral($usuario_id);
            }

            $sqlCheck = "SELECT id FROM Postagens_Salvas 
                         WHERE id_usuario = :uid AND id_postagem = :pid LIMIT 1";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([':uid' => $usuario_id, ':pid' => $post_id]);
            
            if ($stmtCheck->fetch()) {
                return $this->moverItem($post_id, $usuario_id, $colecao_id);
            }

            $sql = "INSERT INTO Postagens_Salvas (id_usuario, id_postagem, colecao_id) 
                    VALUES (:uid, :pid, :cid)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':uid' => $usuario_id,
                ':pid' => $post_id,
                ':cid' => $colecao_id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * MOVER ITEM ENTRE COLEÇÕES
     */
    public function moverItem(int $post_id, int $usuario_id, int $nova_colecao_id): bool {
        try {
            $sql = "UPDATE Postagens_Salvas 
                    SET colecao_id = :cid 
                    WHERE id_postagem = :pid AND id_usuario = :uid";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':cid' => $nova_colecao_id,
                ':pid' => $post_id,
                ':uid' => $usuario_id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * REMOVER ITEM DOS SALVOS
     */
    public function removerItem(int $post_id, int $usuario_id): bool {
        try {
            $sql = "DELETE FROM Postagens_Salvas 
                    WHERE id_postagem = :pid AND id_usuario = :uid";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':pid' => $post_id, ':uid' => $usuario_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * MOTOR DE BUSCA E FILTRAGEM (O CORAÇÃO DA VIEW)
     * ATUALIZAÇÃO V75.2: Injeção de dados de Marketplace e Fotos.
     * FIX: Correção de parâmetros duplicados no LIKE.
     * EVOLUÇÃO: Suporte a visualização pública de coleções de terceiros.
     */
    public function getItensSalvos(int $usuario_id, array $filtros = []): array {
        $colecao_id = $filtros['colecao_id'] ?? null;
        $tipo       = $filtros['tipo'] ?? null; 
        $busca      = $filtros['busca'] ?? null;

        try {
            $sql = "SELECT ps.id AS salvo_id, ps.id_postagem, ps.colecao_id,
                           p.conteudo_texto, p.tipo_media AS post_tipo_media, 
                           p.tipo_post AS post_tipo,
                           p.data_postagem AS data_criacao,
                           (SELECT url_midia FROM Postagens_Midia WHERE id_postagem = p.id LIMIT 1) AS url_media,
                           u.nome AS autor_nome, u.foto_perfil_url AS autor_avatar, 
                           u.nome_de_usuario AS autor_slug,
                           sc.nome AS colecao_nome,
                           sc.privacidade AS colecao_privacidade,
                           e.pergunta AS enquete_pergunta,
                           IF(e.id IS NOT NULL, 1, 0) AS is_enquete,
                           ma.id AS anuncio_id,
                           ma.titulo_produto AS mkt_titulo,
                           ma.preco AS mkt_preco,
                           ma.status_venda AS mkt_status
                    FROM Postagens_Salvas ps
                    INNER JOIN Postagens p ON ps.id_postagem = p.id
                    INNER JOIN Usuarios u ON p.id_usuario = u.id
                    LEFT JOIN Salvos_Colecoes sc ON ps.colecao_id = sc.id
                    LEFT JOIN Enquetes e ON p.id = e.post_id
                    LEFT JOIN Marketplace_Anuncios ma ON p.id = ma.id_postagem";

            $params = [':uid' => $usuario_id];

            // --- LÓGICA DE ACESSO (Público vs Privado) ---
            if ($colecao_id) {
                // Se o usuário solicitou uma pasta específica:
                // Ele vê os itens se for o dono DA PASTA OU se a pasta for PÚBLICA.
                $sql .= " WHERE ps.colecao_id = :cid AND (sc.usuario_id = :uid OR sc.privacidade = 'publica')";
                $params[':cid'] = $colecao_id;
            } else {
                // Se o usuário está na visão geral ("Tudo"):
                // Mostra apenas os itens salvos PELO PRÓPRIO usuário (privacidade absoluta).
                $sql .= " WHERE ps.id_usuario = :uid";
            }

            // Filtros Inteligentes por Categoria
            if ($tipo && $tipo !== 'todos') {
                if ($tipo === 'enquete') {
                    $sql .= " AND e.id IS NOT NULL";
                } elseif ($tipo === 'marketplace' || $tipo === 'venda') {
                    $sql .= " AND p.tipo_post = 'venda'";
                } elseif ($tipo === 'publicacoes' || $tipo === 'publicacao') {
                    $sql .= " AND p.tipo_post = 'padrao' AND e.id IS NULL";
                }
            }

            if ($busca) {
                $sql .= " AND (p.conteudo_texto LIKE :b1 OR u.nome LIKE :b2 OR e.pergunta LIKE :b3 OR ma.titulo_produto LIKE :b4)";
                $termo_busca = "%$busca%";
                $params[':b1'] = $termo_busca;
                $params[':b2'] = $termo_busca;
                $params[':b3'] = $termo_busca;
                $params[':b4'] = $termo_busca;
            }

            $sql .= " ORDER BY ps.id DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Erro em SalvosLogic::getItensSalvos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * HELPER: VERIFICA SE É A COLEÇÃO GERAL
     */
    private function isGeral(int $colecao_id): bool {
        $sql = "SELECT nome FROM Salvos_Colecoes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $colecao_id]);
        $nome = $stmt->fetchColumn();
        return ($nome === self::NOME_COLECAO_PADRAO);
    }
}