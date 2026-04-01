<?php
/**
 * src/SuporteLogic.php
 * "Cérebro" do Módulo de Suporte (V2.1)
 * PAPEL: Gerenciar o ciclo de vida dos chamados e inteligência de resposta.
 * AJUSTE: Ativação de alertas para utilizadores comuns (ultima_msg_admin).
 * VERSÃO: 2.1 - socialbr.lol
 */

class SuporteLogic {

    /* =========================================================================
       1. ÁREA DO UTILIZADOR (CLIENTE)
       ========================================================================= */

    /**
     * CRIA UM NOVO CHAMADO E A PRIMEIRA MENSAGEM
     */
    public static function criarChamado($conn, $userId, $assunto, $categoria, $mensagem, $fotoUrl = null, $diagnostico = null) {
        $conn->begin_transaction();
        try {
            // A. Insere o cabeçalho do chamado
            $sql_chamado = "INSERT INTO Suporte_Chamados (usuario_id, assunto, categoria, status, diagnostico_json) 
                            VALUES (?, ?, ?, 'aberto', ?)";
            $stmt_c = $conn->prepare($sql_chamado);
            $diag_json = !empty($diagnostico) ? json_encode($diagnostico) : null;
            $stmt_c->bind_param("isss", $userId, $assunto, $categoria, $diag_json);
            $stmt_c->execute();
            $chamadoId = $conn->insert_id;

            // B. Insere a primeira mensagem (do utilizador)
            $sql_msg = "INSERT INTO Suporte_Mensagens (chamado_id, remetente_tipo, mensagem, foto_url) 
                        VALUES (?, 'usuario', ?, ?)";
            $stmt_m = $conn->prepare($sql_msg);
            $stmt_m->bind_param("iss", $chamadoId, $mensagem, $fotoUrl);
            $stmt_m->execute();

            $conn->commit();
            return $chamadoId;
        } catch (Exception $e) {
            $conn->rollback();
            return false;
        }
    }

    /**
     * BUSCA A LISTA DE CHAMADOS DE UM UTILIZADOR ESPECÍFICO
     * ATUALIZAÇÃO V2.1: Agora detecta se a última resposta foi do Admin para alertas.
     */
    public static function getChamadosPorUsuario($conn, $userId) {
        $sql = "SELECT *, 
                (SELECT data_envio FROM Suporte_Mensagens WHERE chamado_id = Suporte_Chamados.id ORDER BY data_envio DESC LIMIT 1) as ultima_atividade,
                (SELECT CASE WHEN remetente_tipo = 'admin' THEN 1 ELSE 0 END 
                 FROM Suporte_Mensagens 
                 WHERE chamado_id = Suporte_Chamados.id 
                 ORDER BY data_envio DESC LIMIT 1) as ultima_msg_admin
                FROM Suporte_Chamados 
                WHERE usuario_id = ? 
                ORDER BY data_atualizacao DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /* =========================================================================
       2. ÁREA ADMINISTRATIVA (PAINEL ADMIN)
       ========================================================================= */

    /**
     * LISTAGEM GERAL PARA O ADMIN
     */
    public static function getTodosChamadosAdmin($conn, $status = null, $busca = null) {
        $params = [];
        $types = "";
        
        $sql = "SELECT c.*, u.nome, u.sobrenome, u.foto_perfil_url,
                (SELECT CASE WHEN remetente_tipo = 'admin' THEN 1 ELSE 0 END 
                 FROM Suporte_Mensagens 
                 WHERE chamado_id = c.id 
                 ORDER BY data_envio DESC LIMIT 1) as ultima_msg_admin
                FROM Suporte_Chamados c
                JOIN Usuarios u ON c.usuario_id = u.id
                WHERE 1=1";

        if ($status) {
            $sql .= " AND c.status = ?";
            $params[] = $status;
            $types .= "s";
        }

        if ($busca) {
            $sql .= " AND (c.assunto LIKE ? OR u.nome LIKE ? OR u.sobrenome LIKE ?)";
            $b = "%$busca%";
            array_push($params, $b, $b, $b);
            $types .= "sss";
        }

        $sql .= " ORDER BY c.data_atualizacao DESC";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * ATUALIZA O STATUS DO CHAMADO
     */
    public static function atualizarStatus($conn, $chamadoId, $novoStatus) {
        $sql = "UPDATE Suporte_Chamados SET status = ?, data_atualizacao = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $novoStatus, $chamadoId);
        return $stmt->execute();
    }

    /* =========================================================================
       3. LÓGICA COMPARTILHADA (HISTÓRICO E MENSAGENS)
       ========================================================================= */

    /**
     * BUSCA O CABEÇALHO DE UM CHAMADO ÚNICO
     */
    public static function getDetalhesChamado($conn, $chamadoId) {
        $sql = "SELECT c.*, u.nome, u.sobrenome, u.foto_perfil_url, u.id as user_id
                FROM Suporte_Chamados c
                JOIN Usuarios u ON c.usuario_id = u.id
                WHERE c.id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $chamadoId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * BUSCA TODAS AS MENSAGENS DE UM CHAMADO
     */
    public static function getMensagensChamado($conn, $chamadoId) {
        $sql = "SELECT * FROM Suporte_Mensagens 
                WHERE chamado_id = ? 
                ORDER BY data_envio ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $chamadoId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * ADICIONA UMA RESPOSTA
     */
    public static function adicionarResposta($conn, $chamadoId, $remetenteTipo, $mensagem, $fotoUrl = null) {
        $conn->begin_transaction();
        try {
            // 1. Insere a mensagem
            $sql = "INSERT INTO Suporte_Mensagens (chamado_id, remetente_tipo, mensagem, foto_url) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", $chamadoId, $remetenteTipo, $mensagem, $fotoUrl);
            $stmt->execute();

            // 2. Atualiza o status automaticamente
            if ($remetenteTipo === 'admin') {
                $statusUpdate = "UPDATE Suporte_Chamados SET status = 'em_andamento', data_atualizacao = NOW() WHERE id = ?";
                $stmtU = $conn->prepare($statusUpdate);
                $stmtU->bind_param("i", $chamadoId);
                $stmtU->execute();
            } else {
                $dataUpdate = "UPDATE Suporte_Chamados SET data_atualizacao = NOW() WHERE id = ?";
                $stmtD = $conn->prepare($dataUpdate);
                $stmtD->bind_param("i", $chamadoId);
                $stmtD->execute();
            }

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            return false;
        }
    }

    /**
     * ESTATÍSTICAS PARA O DASHBOARD ADMIN
     */
    public static function getStatsAdmin($conn) {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'aberto' THEN 1 ELSE 0 END) as abertos,
                SUM(CASE WHEN status = 'em_andamento' THEN 1 ELSE 0 END) as em_andamento,
                SUM(CASE WHEN status = 'resolvido' THEN 1 ELSE 0 END) as resolvidos
                FROM Suporte_Chamados";
        $res = $conn->query($sql);
        return $res->fetch_assoc();
    }
}