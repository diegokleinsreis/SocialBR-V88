<?php
/**
 * src/LogsLogic.php
 * PAPEL: "Cérebro" de Auditoria e Logs do Painel Administrativo.
 * VERSÃO: 1.1 (Correção de Tipagem de Alvo - socialbr.lol)
 */

class LogsLogic {

    /**
     * 1. REGISTRAR UMA AÇÃO (O coração do sistema)
     * Salva quem fez, o que fez, em qual objeto e os detalhes.
     */
    public static function registrar($conn, $admin_id, $acao, $tipo_objeto, $id_objeto, $detalhes = '') {
        $sql = "INSERT INTO Logs_Admin (admin_id, acao, tipo_objeto, id_objeto, detalhes, data_log) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        
        /**
         * CORREÇÃO DE ENGENHARIA:
         * i = admin_id (int)
         * s = acao (string)
         * s = tipo_objeto (string)
         * i = id_objeto (int)
         * s = detalhes (string)
         */
        $stmt->bind_param("issis", $admin_id, $acao, $tipo_objeto, $id_objeto, $detalhes);
        
        $sucesso = $stmt->execute();
        $stmt->close();
        
        return $sucesso;
    }

    /**
     * 2. LISTAR LOGS COM PAGINAÇÃO E JOIN
     * Busca os logs trazendo o nome do administrador que realizou a ação.
     */
    public static function listarLogs($conn, $limite = 20, $offset = 0, $filtro_tipo = '') {
        $where = "";
        $params = [];
        $types = "";

        if (!empty($filtro_tipo)) {
            $where = " WHERE l.tipo_objeto = ? ";
            $params[] = $filtro_tipo;
            $types .= "s";
        }

        $sql = "SELECT l.*, u.nome as admin_nome, u.sobrenome as admin_sobrenome, u.foto_perfil_url
                FROM Logs_Admin l
                JOIN Usuarios u ON l.admin_id = u.id
                $where
                ORDER BY l.data_log DESC
                LIMIT ? OFFSET ?";

        $params[] = (int)$limite;
        $params[] = (int)$offset;
        $types .= "ii";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    /**
     * 3. CONTAR TOTAL DE LOGS (Para paginação)
     */
    public static function contarTotal($conn, $filtro_tipo = '') {
        $sql = "SELECT COUNT(*) as total FROM Logs_Admin";
        if (!empty($filtro_tipo)) {
            $sql .= " WHERE tipo_objeto = '" . $conn->real_escape_string($filtro_tipo) . "'";
        }
        
        $res = $conn->query($sql);
        return $res->fetch_assoc()['total'];
    }

    /**
     * 4. BUSCAR LOGS DE UM OBJETO ESPECÍFICO
     * Útil para ver o histórico de um único grupo ou usuário.
     */
    public static function getHistoricoObjeto($conn, $tipo_objeto, $id_objeto) {
        $sql = "SELECT l.*, u.nome as admin_nome 
                FROM Logs_Admin l
                JOIN Usuarios u ON l.admin_id = u.id
                WHERE l.tipo_objeto = ? AND l.id_objeto = ?
                ORDER BY l.data_log DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $tipo_objeto, $id_objeto);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $res;
    }

    /**
     * 5. ESTATÍSTICAS RÁPIDAS
     * Mostra quantas ações foram feitas hoje e o admin mais ativo.
     */
    public static function getStatsHoje($conn) {
        $stats = [];
        
        // Ações hoje
        $res = $conn->query("SELECT COUNT(*) FROM Logs_Admin WHERE DATE(data_log) = CURDATE()");
        $stats['total_hoje'] = $res->fetch_row()[0];

        // Admin mais ativo
        $res = $conn->query("SELECT u.nome, COUNT(l.id) as total 
                             FROM Logs_Admin l 
                             JOIN Usuarios u ON l.admin_id = u.id 
                             GROUP BY l.admin_id ORDER BY total DESC LIMIT 1");
        $stats['top_admin'] = $res->fetch_assoc();

        return $stats;
    }

    /**
     * 6. MANUTENÇÃO: LIMPAR LOGS ANTIGOS
     * Remove logs com mais de X dias para economizar espaço em disco.
     */
    public static function limparLogsAntigos($conn, $dias = 90) {
        $sql = "DELETE FROM Logs_Admin WHERE data_log < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $dias);
        $stmt->execute();
        return $stmt->affected_rows;
    }
}