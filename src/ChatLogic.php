<?php
/**
 * src/ChatLogic.php
 * "Cérebro" do Sistema de Chat em Tempo Real.
 * Centraliza a lógica de conversas, mensagens, bloqueios, mídias e grupos.
 * VERSÃO: V67.7 (Fix: Identificação de Autor de Bloqueio - socialbr.lol)
 */

class ChatLogic {

    /**
     * Lista todas as conversas do usuário (Privadas e Grupos).
     * Inclui o nome do remetente da última mensagem para Preview Dinâmico.
     */
    public static function getConversations($conn, $userId, $filter = null) {
        $params = [$userId];
        $types = "i";
        
        $sql = "SELECT 
                    c.id AS conversa_id,
                    c.tipo,
                    c.titulo AS grupo_titulo,
                    c.capa_url AS grupo_capa,
                    c.dono_id AS grupo_dono_id, 
                    c.status AS conversa_status,
                    p.fixada,
                    p.silenciada,
                    p.ultima_leitura_at,
                    m.mensagem AS ultima_msg_texto,
                    m.tipo_midia AS ultima_msg_tipo,
                    m.criado_em AS ultima_msg_data,
                    m.remetente_id AS ultima_msg_remetente,
                    u_msg.nome AS ultima_msg_remetente_nome,
                    u.id AS outro_usuario_id,
                    u.nome AS outro_usuario_nome,
                    u.sobrenome AS outro_usuario_sobrenome,
                    u.foto_perfil_url AS outro_usuario_avatar,
                    u.nome_de_usuario AS outro_usuario_slug,
                    (u.ultimo_acesso > DATE_SUB(NOW(), INTERVAL 5 MINUTE)) AS is_online,
                    (m.criado_em <= p2.ultima_leitura_at) AS ultima_msg_lida,
                    (SELECT COUNT(*) FROM chat_mensagens 
                     WHERE conversa_id = c.id 
                     AND remetente_id != ? 
                     AND criado_em > p.ultima_leitura_at) AS unread_count
                FROM chat_participantes p
                JOIN chat_conversas c ON p.conversa_id = c.id
                LEFT JOIN chat_mensagens m ON c.id = m.conversa_id AND m.criado_em = c.ultima_mensagem_at
                LEFT JOIN Usuarios u_msg ON m.remetente_id = u_msg.id
                LEFT JOIN chat_participantes p2 ON c.id = p2.conversa_id AND p2.usuario_id != p.usuario_id AND c.tipo = 'privada'
                LEFT JOIN Usuarios u ON p2.usuario_id = u.id
                WHERE p.usuario_id = ?";
        
        $params[] = $userId;
        $types .= "i";

        if (!empty($filter)) {
            $sql .= " AND (c.titulo LIKE ? OR u.nome LIKE ? OR u.sobrenome LIKE ? OR u.nome_de_usuario LIKE ?)";
            $f = "%$filter%";
            array_push($params, $f, $f, $f, $f);
            $types .= "ssss";
        }

        $sql .= " ORDER BY p.fixada DESC, c.ultima_mensagem_at DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtém o histórico de mensagens de uma conversa específica.
     */
    public static function getMessages($conn, $conversaId, $userId) {
        $sql = "SELECT m.*, 
                        u.nome AS remetente_nome, 
                        u.sobrenome AS remetente_sobrenome, 
                        u.foto_perfil_url AS remetente_avatar,
                        (m.criado_em <= p2.ultima_leitura_at) AS lida
                FROM chat_mensagens m
                JOIN Usuarios u ON m.remetente_id = u.id
                JOIN chat_conversas c ON m.conversa_id = c.id
                JOIN chat_participantes p1 ON m.conversa_id = p1.conversa_id AND p1.usuario_id = ?
                LEFT JOIN chat_participantes p2 ON m.conversa_id = p2.conversa_id AND p2.usuario_id != ? AND c.tipo = 'privada'
                WHERE m.conversa_id = ?
                ORDER BY m.criado_em ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $userId, $userId, $conversaId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Cria uma conversa de grupo e insere todos os participantes.
     */
    public static function createGroupConversation($conn, $creatorId, $title, $participants) {
        $conn->begin_transaction();
        try {
            $sqlChat = "INSERT INTO chat_conversas (tipo, titulo, dono_id) VALUES ('grupo', ?, ?)";
            $stmtChat = $conn->prepare($sqlChat);
            $stmtChat->bind_param("si", $title, $creatorId);
            $stmtChat->execute();
            $conversaId = $conn->insert_id;

            $sqlPart = "INSERT INTO chat_participantes (conversa_id, usuario_id) VALUES (?, ?)";
            $stmtPart = $conn->prepare($sqlPart);
            
            $stmtPart->bind_param("ii", $conversaId, $creatorId);
            $stmtPart->execute();

            foreach ($participants as $pId) {
                $pId = (int)$pId;
                $stmtPart->bind_param("ii", $conversaId, $pId);
                $stmtPart->execute();
            }

            $conn->commit();
            return $conversaId;
        } catch (Exception $e) {
            $conn->rollback();
            return false;
        }
    }

    /**
     * Obtém metadados de um grupo específico.
     */
    public static function getGroupDetails($conn, $conversaId) {
        $sql = "SELECT id, titulo, capa_url, dono_id, criado_em, status 
                FROM chat_conversas 
                WHERE id = ? AND tipo = 'grupo' LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $conversaId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Retorna a lista de membros de um grupo com status de dono.
     */
    public static function getGroupMembers($conn, $conversaId) {
        $sql = "SELECT u.id, u.nome, u.sobrenome, u.foto_perfil_url, u.nome_de_usuario,
                        (u.id = c.dono_id) as eh_dono
                FROM Usuarios u
                JOIN chat_participantes p ON u.id = p.usuario_id
                JOIN chat_conversas c ON p.conversa_id = c.id
                WHERE p.conversa_id = ?
                ORDER BY eh_dono DESC, u.nome ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $conversaId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Verifica se o utilizador é o dono da conversa (grupo).
     */
    public static function isGroupOwner($conn, $conversaId, $userId) {
        $sql = "SELECT id FROM chat_conversas WHERE id = ? AND dono_id = ? AND tipo = 'grupo' LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $conversaId, $userId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Remove um participante da conversa.
     */
    public static function removeParticipant($conn, $conversaId, $userId) {
        $sql = "DELETE FROM chat_participantes WHERE conversa_id = ? AND usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $conversaId, $userId);
        return $stmt->execute();
    }

    /**
     * Expulsa um membro da comunidade.
     */
    public static function kickMember($conn, $conversaId, $targetUserId) {
        $group = self::getGroupDetails($conn, $conversaId);
        if (!$group || (int)$group['dono_id'] === (int)$targetUserId) {
            return false;
        }
        return self::removeParticipant($conn, $conversaId, $targetUserId);
    }

    /**
     * Transfere a coroa da comunidade.
     */
    public static function transferOwnership($conn, $conversaId, $newOwnerId) {
        if (!self::isParticipant($conn, $conversaId, $newOwnerId)) {
            return false;
        }
        $sql = "UPDATE chat_conversas SET dono_id = ? WHERE id = ? AND tipo = 'grupo'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $newOwnerId, $conversaId);
        return $stmt->execute();
    }

    /**
     * Verifica se um usuário pertence a uma conversa.
     */
    public static function isParticipant($conn, $conversaId, $userId) {
        $sql = "SELECT 1 FROM chat_participantes WHERE conversa_id = ? AND usuario_id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $conversaId, $userId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Envia uma mensagem e atualiza o estado global.
     */
    public static function sendMessage($conn, $senderId, $conversaId, $msgText, $type = 'texto', $mediaUrl = null) {
        $token = bin2hex(random_bytes(16));
        $sql = "INSERT INTO chat_mensagens (conversa_id, remetente_id, mensagem, midia_url, tipo_midia, token_seguranca) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissss", $conversaId, $senderId, $msgText, $mediaUrl, $type, $token);
        
        if ($stmt->execute()) {
            $stmtUpdate = $conn->prepare("UPDATE chat_conversas SET ultima_mensagem_at = NOW() WHERE id = ?");
            $stmtUpdate->bind_param("i", $conversaId);
            $stmtUpdate->execute();
            return true;
        }
        return false;
    }

    /**
     * Verifica bloqueios mútuos.
     */
    public static function isUserBlocked($conn, $userId, $targetId) {
        $sql = "SELECT id FROM Bloqueios WHERE (bloqueador_id = ? AND bloqueado_id = ?) OR (bloqueador_id = ? AND bloqueado_id = ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $userId, $targetId, $targetId, $userId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Verifica se o utilizador logado bloqueou especificamente o alvo.
     * Útil para exibir botões de "Desbloquear" no menu de ações.
     */
    public static function didIBlockThisUser($conn, $userId, $targetId) {
        $sql = "SELECT id FROM Bloqueios WHERE bloqueador_id = ? AND bloqueado_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $targetId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Busca ou Cria uma conversa privada entre dois usuários.
     */
    public static function getOrCreatePrivateConversation($conn, $user1, $user2) {
        $sql = "SELECT p1.conversa_id FROM chat_participantes p1
                JOIN chat_participantes p2 ON p1.conversa_id = p2.conversa_id
                JOIN chat_conversas c ON p1.conversa_id = c.id
                WHERE p1.usuario_id = ? AND p2.usuario_id = ? AND c.tipo = 'privada' LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user1, $user2);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if ($res) return (int)$res['conversa_id'];

        return self::createPrivateChat($conn, $user1, $user2);
    }

    private static function createPrivateChat($conn, $u1, $u2) {
        $conn->begin_transaction();
        try {
            $conn->query("INSERT INTO chat_conversas (tipo) VALUES ('privada')");
            $cId = $conn->insert_id;
            $stmt = $conn->prepare("INSERT INTO chat_participantes (conversa_id, usuario_id) VALUES (?, ?), (?, ?)");
            $stmt->bind_param("iiii", $cId, $u1, $cId, $u2);
            $stmt->execute();
            $conn->commit();
            return $cId;
        } catch (Exception $e) {
            $conn->rollback();
            return false;
        }
    }

    /**
     * Busca amigos disponíveis para iniciar uma nova conversa.
     * UPGRADE V67.6: Fix de Bind Param (Removido espaço na string de tipos).
     */
    public static function searchFriendsForNewChat($conn, $userId, $term = '', $forGroup = false) {
        $termParam = "%$term%";
        
        $sql = "SELECT u.id, u.nome, u.sobrenome, u.foto_perfil_url, u.nome_de_usuario,
                       (SELECT p1.conversa_id 
                        FROM chat_participantes p1
                        JOIN chat_participantes p2 ON p1.conversa_id = p2.conversa_id
                        JOIN chat_conversas c ON p1.conversa_id = c.id
                        WHERE p1.usuario_id = ? AND p2.usuario_id = u.id AND c.tipo = 'privada' 
                        LIMIT 1) as conversa_existente_id
                FROM Usuarios u
                INNER JOIN Amizades a ON (u.id = a.usuario_um_id OR u.id = a.usuario_dois_id)
                WHERE (a.usuario_um_id = ? OR a.usuario_dois_id = ?)
                AND a.status = 'aceite'
                AND u.id != ? ";

        $sql .= " AND u.id NOT IN (
                    SELECT bloqueado_id FROM Bloqueios WHERE bloqueador_id = ?
                    UNION
                    SELECT bloqueador_id FROM Bloqueios WHERE bloqueado_id = ?
                )
                AND (u.nome LIKE ? OR u.sobrenome LIKE ? OR u.nome_de_usuario LIKE ?)
                AND u.status = 'ativo'
                LIMIT 20";

        $stmt = $conn->prepare($sql);
        
        // FIX V67.6: String de tipos corrigida para "iiiiii sss" -> "iiiiiisss" (9 parâmetros exatos)
        $stmt->bind_param("iiiiiisss", $userId, $userId, $userId, $userId, $userId, $userId, $termParam, $termParam, $termParam);

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Busca as mídias (fotos/vídeos/áudios) compartilhadas em uma conversa.
     */
    public static function getConversationMedia($conn, $conversaId) {
        $sql = "SELECT m.id, m.midia_url, m.tipo_midia, m.criado_em, m.remetente_id, u.nome AS remetente_nome
                FROM chat_mensagens m
                JOIN Usuarios u ON m.remetente_id = u.id
                WHERE m.conversa_id = ? 
                AND m.tipo_midia IN ('foto', 'video', 'audio')
                AND m.midia_url IS NOT NULL
                ORDER BY m.criado_em DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $conversaId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Busca grupos em comum entre dois utilizadores.
     */
    public static function getMutualGroups($conn, $userId, $targetId) {
        $sql = "SELECT c.id, c.titulo, c.capa_url 
                FROM chat_conversas c
                JOIN chat_participantes p1 ON c.id = p1.conversa_id
                JOIN chat_participantes p2 ON c.id = p2.conversa_id
                WHERE c.tipo = 'grupo' 
                AND p1.usuario_id = ? 
                AND p2.usuario_id = ?
                LIMIT 10";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $targetId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function toggleMute($conn, $userId, $conversaId) {
        $sql = "UPDATE chat_participantes SET silenciada = NOT silenciada 
                WHERE usuario_id = ? AND conversa_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $conversaId);
        return $stmt->execute();
    }

    public static function togglePin($conn, $userId, $conversaId) {
        $sql = "UPDATE chat_participantes SET fixada = NOT fixada 
                WHERE usuario_id = ? AND conversa_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $conversaId);
        return $stmt->execute();
    }

    public static function markAsRead($conn, $userId, $conversaId) {
        $sql = "UPDATE chat_participantes SET ultima_leitura_at = NOW() 
                WHERE usuario_id = ? AND conversa_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $conversaId);
        return $stmt->execute();
    }
}