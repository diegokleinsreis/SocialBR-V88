<?php
/**
 * src/NotificationLogic.php
 * "Cérebro" para tudo relacionado com a busca de notificações.
 * PAPEL: Centralizar a lógica de busca para o histórico de alertas.
 * VERSÃO: 51.5 (Padronização com tipos_notificacoes.php)
 */

// Importamos o dicionário de tipos para usar as constantes na Query SQL
require_once __DIR__ . '/../config/tipos_notificacoes.php';

class NotificationLogic {

    /**
     * Busca todo o histórico de notificações de um usuário.
     * Atualizado para usar as constantes padronizadas na query.
     */
    public static function getNotificationsForUser($conn, $user_id) {
        
        // Preparamos os tipos de grupo para a query SQL usando as constantes
        $tipos_grupo = [
            NOTIF_CONVITE_GRUPO,
            NOTIF_SOLICITACAO_GRUPO,
            NOTIF_ACEITE_SOLICITACAO,
            NOTIF_PROMOCAO_MODERADOR,
            NOTIF_REBAIXAMENTO_MEMBRO,
            NOTIF_TRANSFERENCIA_DONO,
            NOTIF_EXPULSAO_GRUPO,
            NOTIF_ACEITE_CONVITE_GRUPO
        ];
        
        // Transformamos o array em uma string para o SQL (ex: 'tipo1','tipo2')
        $tipos_grupo_sql = "'" . implode("','", $tipos_grupo) . "'";
        $tipo_chat_grupo = "'" . NOTIF_CONVITE_CHAT_GRUPO . "'";

        $sql_notificacoes = "SELECT 
                                 n.id, 
                                 n.tipo, 
                                 n.id_referencia, 
                                 n.remetente_id,
                                 n.lida, 
                                 n.data_criacao,
                                 u.nome AS remetente_nome,
                                 u.sobrenome AS remetente_sobrenome,
                                 u.foto_perfil_url AS remetente_foto,
                                 COALESCE(g.nome, cc.titulo) AS grupo_nome
                               FROM 
                                 notificacoes AS n
                               JOIN 
                                 Usuarios AS u ON n.remetente_id = u.id
                               LEFT JOIN 
                                 Grupos AS g ON (n.id_referencia = g.id AND n.tipo IN ($tipos_grupo_sql))
                               LEFT JOIN 
                                 chat_conversas AS cc ON (n.id_referencia = cc.id AND n.tipo = $tipo_chat_grupo)
                               WHERE 
                                 n.usuario_id = ?
                               ORDER BY 
                                 n.data_criacao DESC";

        $stmt = $conn->prepare($sql_notificacoes);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notificacoes = $result->fetch_all(MYSQLI_ASSOC); 
        
        $stmt->close();
        
        return $notificacoes;
    }
}
?>