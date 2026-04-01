<?php
/**
 * BuscaLogic.php - O Cérebro de Inteligência da socialbr.lol
 * VERSÃO: 3.0 (Conversão Integral para PDO - socialbr.lol)
 * PAPEL: Centralizar queries complexas, relevância e métricas administrativas.
 */

class BuscaLogic {
    private $db; // Agora tratado como instância PDO
    private $userId;

    public function __construct($db, $userId = null) {
        $this->db = $db;
        $this->userId = (int)$userId;
    }

    /**
     * BUSCA GLOBAL (Orquestrador Balanceado)
     */
    public function buscarGlobal($termo, $limite = 8) {
        $termo = $this->preProcessarTermo($termo);
        if (empty($termo)) return [];

        $resultados = [];

        // 1. Busca Pessoas
        $usuarios = $this->buscarUsuarios($termo, $limite);
        foreach ($usuarios as $u) {
            $u['tipo_resultado'] = 'perfil';
            $resultados[] = $u;
        }

        // 2. Busca Grupos
        $grupos = $this->buscarGrupos($termo, $limite);
        foreach ($grupos as $g) {
            $g['tipo_resultado'] = 'grupo';
            $resultados[] = $g;
        }

        // 3. Busca Postagens
        if (count($resultados) < $limite) {
            $posts = $this->buscarPostagens($termo, ($limite - count($resultados)));
            foreach ($posts as $p) {
                $p['tipo_resultado'] = 'post';
                $resultados[] = $p;
            }
        }

        return array_slice($resultados, 0, $limite);
    }

    /**
     * BUSCA DE USUÁRIOS
     */
    public function buscarUsuarios($termo, $limite = 20, $offset = 0) {
        $termo = $this->preProcessarTermo($termo);
        if (empty($termo)) return [];

        $query = "
            SELECT 
                u.id, u.nome, u.sobrenome, u.nome_de_usuario, u.foto_perfil_url, u.perfil_privado,
                (SELECT COUNT(*) FROM Amizades WHERE 
                    ((usuario_um_id = u.id AND usuario_dois_id = ?) OR (usuario_um_id = ? AND usuario_dois_id = u.id))
                    AND status = 'aceite'
                ) as eh_amigo
            FROM Usuarios u
            WHERE u.id != ? 
            AND u.status = 'ativo'
            AND (
                MATCH(u.nome, u.sobrenome, u.nome_de_usuario) AGAINST(? IN BOOLEAN MODE)
                OR u.nome_de_usuario LIKE ?
            )
            AND NOT EXISTS (
                SELECT 1 FROM Amizades 
                WHERE ((usuario_um_id = ? AND usuario_dois_id = u.id) OR (usuario_um_id = u.id AND usuario_dois_id = ?))
                AND status = 'bloqueado'
            )
            ORDER BY eh_amigo DESC, u.nome ASC
            LIMIT " . (int)$limite . " OFFSET " . (int)$offset;

        try {
            $stmt = $this->db->prepare($query);
            $t_full = $termo . '*';
            $t_like = $termo . '%';

            $stmt->execute([
                $this->userId, $this->userId,
                $this->userId,
                $t_full, $t_like,
                $this->userId, $this->userId
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro buscarUsuarios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * BUSCA DE GRUPOS
     */
    public function buscarGrupos($termo, $limite = 20, $offset = 0) {
        $termo = $this->preProcessarTermo($termo);
        if (empty($termo)) return [];

        $query = "
            SELECT id, nome, descricao, foto_capa_url, privacidade
            FROM Grupos
            WHERE status = 'ativo'
            AND (nome LIKE ? OR descricao LIKE ? OR MATCH(nome, descricao) AGAINST(? IN BOOLEAN MODE))
            ORDER BY nome ASC
            LIMIT " . (int)$limite . " OFFSET " . (int)$offset;

        try {
            $stmt = $this->db->prepare($query);
            $t_like = '%' . $termo . '%';
            $t_full = $termo . '*';

            $stmt->execute([$t_like, $t_like, $t_full]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * BUSCA DE POSTAGENS
     */
    public function buscarPostagens($termo, $limite = 20, $offset = 0) {
        $termo = $this->preProcessarTermo($termo);
        if (empty($termo)) return [];

        $query = "
            SELECT p.*, u.nome, u.nome_de_usuario, u.foto_perfil_url
            FROM Postagens p
            JOIN Usuarios u ON p.id_usuario = u.id
            WHERE p.status = 'ativo'
            AND MATCH(p.conteudo_texto) AGAINST(? IN BOOLEAN MODE)
            AND (
                p.privacidade = 'publico'
                OR (
                    p.privacidade = 'amigos' 
                    AND EXISTS (
                        SELECT 1 FROM Amizades 
                        WHERE ((usuario_um_id = ? AND usuario_dois_id = p.id_usuario) OR (usuario_um_id = p.id_usuario AND usuario_dois_id = ?))
                        AND status = 'aceite'
                    )
                )
                OR p.id_usuario = ?
            )
            ORDER BY p.data_postagem DESC
            LIMIT " . (int)$limite . " OFFSET " . (int)$offset;

        try {
            $stmt = $this->db->prepare($query);
            $t_full = $termo . '*';

            $stmt->execute([$t_full, $this->userId, $this->userId, $this->userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * SISTEMA DE RASTREIO (Registro de Interação)
     */
    public function registrarInteracao($termo, $tipo = 'geral', $idAlvo = null, $totalResultados = 0) {
        if (empty($termo)) return false;

        $query = "INSERT INTO busca_interacoes (id_usuario, termo, tipo_clicado, id_alvo, total_resultados) VALUES (?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->db->prepare($query);
            $t = mb_strtolower(trim($termo));
            $id_a = $idAlvo !== null ? (int)$idAlvo : null;
            $total = (int)$totalResultados;

            return $stmt->execute([$this->userId, $t, $tipo, $id_a, $total]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * INTELIGÊNCIA ADMINISTRATIVA
     */

    public function getTopTermos($limite = 10) {
        $query = "SELECT termo, COUNT(*) as total FROM busca_interacoes GROUP BY termo ORDER BY total DESC LIMIT " . (int)$limite;
        try {
            $stmt = $this->db->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getBuscasSemSucesso($limite = 20) {
        $query = "SELECT termo, COUNT(*) as total FROM busca_interacoes WHERE total_resultados = 0 GROUP BY termo ORDER BY total DESC LIMIT " . (int)$limite;
        try {
            $stmt = $this->db->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getEstatisticasCliques() {
        $query = "SELECT tipo_clicado, COUNT(*) as total FROM busca_interacoes WHERE id_alvo IS NOT NULL GROUP BY tipo_clicado";
        try {
            $stmt = $this->db->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getVolumeBuscasPorPeriodo($dias = 30) {
        $query = "SELECT DATE(data_interacao) as data, COUNT(*) as total FROM busca_interacoes GROUP BY DATE(data_interacao) ORDER BY data DESC LIMIT " . (int)$dias;
        try {
            $stmt = $this->db->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * DICIONÁRIO E SEGURANÇA
     */

    public function verificarBlacklist($termo) {
        $query = "SELECT 1 FROM Palavras_Proibidas WHERE termo = ? LIMIT 1";
        try {
            $stmt = $this->db->prepare($query);
            $t = mb_strtolower(trim($termo));
            $stmt->execute([$t]);
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }

    private function aplicarSinonimos($termo) {
        $query = "SELECT termo_real FROM busca_sinonimos WHERE termo_digitado = ? LIMIT 1";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([$termo]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ? $res['termo_real'] : $termo;
        } catch (PDOException $e) {
            return $termo;
        }
    }

    private function preProcessarTermo($termo) {
        $t = $this->sanitizarTermo($termo);
        if (empty($t)) return "";
        if ($this->verificarBlacklist($t)) return "";
        return $this->aplicarSinonimos(mb_strtolower($t));
    }

    /**
     * OBTER HISTÓRICO ENRIQUECIDO
     */
    public function obterHistoricoSugestoes($limite = 5) {
        $query = "
            SELECT termo, tipo_clicado, id_alvo
            FROM busca_interacoes 
            WHERE id_usuario = ? 
            ORDER BY data_interacao DESC 
            LIMIT " . (int)$limite;
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([$this->userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    private function sanitizarTermo($termo) {
        return htmlspecialchars(strip_tags(trim($termo)));
    }
}