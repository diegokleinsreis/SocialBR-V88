<?php
/**
 * src/ToastLogic.php
 * Provedor de dados para o sistema de alertas em tempo real (Toasts).
 * VERSÃO: 10.0 - Limpeza de Verificação (Foco em Notificações & Broadcast)
 * FOCADO EM: Baixa latência e conformidade com a arquitetura socialbr.lol.
 */

require_once __DIR__ . '/UserLogic.php';

class ToastLogic {

    /**
     * Obtém notificações recentes e Alertas Administrativos.
     * @param mysqli $conn Conexão MySQLi.
     * @param int $user_id ID do usuário logado.
     * @param int $last_toast_id O ID do último toast exibido (evita repetição).
     * @return array Lista de notificações prontas para o MotorToast.js.
     */
    public static function getRecentToasts($conn, int $user_id, int $last_toast_id = 0): array {
        global $config;
        $base = $config['base_path'] ?? '/';
        $formatted_toasts = [];

        try {
            // NOTA: A verificação de e-mail foi movida para o banner estático no header.php 
            // para garantir visibilidade permanente sem obstruir a tela com popups.

            // --- 1. BUSCA DE ALERTAS GLOBAIS OU SEGMENTADOS (BROADCAST) ---
            $sqlAviso = "SELECT s.id, s.titulo, s.mensagem, s.cor_preset, s.is_sticky, s.cta_texto, s.cta_link, s.icone 
                         FROM Avisos_Sistema s
                         LEFT JOIN Avisos_Lidos l ON s.id = l.id_aviso AND l.id_usuario = ?
                         WHERE (s.data_expiracao IS NULL OR s.data_expiracao > NOW())
                           AND s.data_inicio <= NOW()
                           AND l.id IS NULL
                           AND (
                               NOT EXISTS (SELECT 1 FROM Avisos_Destinatarios WHERE id_aviso = s.id)
                               OR 
                               EXISTS (SELECT 1 FROM Avisos_Destinatarios WHERE id_aviso = s.id AND id_usuario = ?)
                           )
                         ORDER BY s.data_criacao DESC
                         LIMIT 1";
            
            $stmtA = $conn->prepare($sqlAviso);
            if ($stmtA) {
                $stmtA->bind_param("ii", $user_id, $user_id);
                $stmtA->execute();
                $resAviso = $stmtA->get_result()->fetch_assoc();
                $stmtA->close();

                if ($resAviso) {
                    $formatted_toasts[] = [
                        'id' => (int)$resAviso['id'],
                        'tipo' => 'broadcast', 
                        'titulo' => htmlspecialchars($resAviso['titulo']),
                        'mensagem' => htmlspecialchars($resAviso['mensagem']),
                        'cor_preset' => $resAviso['cor_preset'], 
                        'is_sticky' => (bool)$resAviso['is_sticky'], 
                        'cta_texto' => $resAviso['cta_texto'],
                        'cta_link' => $resAviso['cta_link'],
                        'icone' => $resAviso['icone'],
                        'link' => '#',
                        'is_admin_alert' => true
                    ];
                }
            }

            // --- 2. BUSCA DE NOTIFICAÇÕES COMUNS ---
            $sql = "SELECT 
                        MAX(n.id) as id, 
                        n.tipo, 
                        n.id_referencia, 
                        MAX(n.data_criacao) as data_criacao,
                        u.nome, 
                        u.sobrenome, 
                        u.foto_perfil_url,
                        COALESCE(g.nome, cc.titulo) AS grupo_nome,
                        COUNT(*) as total_agrupado
                    FROM notificacoes n
                    JOIN Usuarios u ON n.remetente_id = u.id
                    LEFT JOIN Grupos g ON (n.id_referencia = g.id AND n.tipo IN (
                        'convite_grupo', 'solicitacao_grupo', 'aceite_solicitacao_grupo', 
                        'promocao_moderador', 'rebaixamento_membro', 'transferencia_dono', 
                        'expulsao_grupo', 'aceite_convite_grupo'
                    ))
                    LEFT JOIN chat_conversas cc ON (n.id_referencia = cc.id AND n.tipo = 'convite_chat_grupo')
                    WHERE n.usuario_id = ? 
                      AND n.id > ?
                      AND n.data_criacao >= (NOW() - INTERVAL 1 MINUTE)
                    GROUP BY n.remetente_id, n.tipo, n.id_referencia
                    ORDER BY id ASC 
                    LIMIT 5";

            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ii", $user_id, $last_toast_id);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    $foto_path = $row['foto_perfil_url'] ?: 'assets/images/default-avatar.png';
                    if ($foto_path && strpos($foto_path, 'http') !== 0) {
                        $foto_path = $base . $foto_path;
                    }

                    $formatted_toasts[] = [
                        'id' => (int)$row['id'],
                        'tipo' => $row['tipo'],
                        'titulo' => null, 
                        'mensagem' => self::formatToastMessage($row),
                        'foto' => $foto_path,
                        'link' => self::generateToastLink($row),
                        'is_admin_alert' => false,
                        'qtd' => (int)$row['total_agrupado']
                    ];
                }
                $stmt->close();
            }

            return $formatted_toasts;

        } catch (Exception $e) {
            error_log("Erro Crítico em ToastLogic: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Formata a mensagem visual baseada no tipo de interação.
     */
    private static function formatToastMessage(array $data): string {
        $nome = htmlspecialchars($data['nome'] . ' ' . $data['sobrenome']);
        $total = (int)($data['total_agrupado'] ?? 1);
        $grupo = htmlspecialchars($data['grupo_nome'] ?? '');
        
        return match ($data['tipo']) {
            'mensagem' => ($total > 1) 
                ? "<strong>{$nome}</strong> enviou {$total} novas mensagens." 
                : "<strong>{$nome}</strong> enviou uma nova mensagem.",

            'aceite_convite_grupo' => !empty($grupo)
                ? "<strong>{$nome}</strong> aceitou seu convite para o grupo <strong>{$grupo}</strong>."
                : "<strong>{$nome}</strong> aceitou seu convite para um grupo.",

            'promocao_moderador' => !empty($grupo)
                ? "Você foi promovido a <strong>moderador</strong> no grupo <strong>{$grupo}</strong>."
                : "Você foi promovido a moderador de um grupo.",

            'rebaixamento_membro' => !empty($grupo)
                ? "Seu cargo no grupo <strong>{$grupo}</strong> foi alterado para membro comum."
                : "Seu cargo em um grupo foi alterado.",

            'transferencia_dono' => !empty($grupo)
                ? "A propriedade do grupo <strong>{$grupo}</strong> foi transferida para você."
                : "Você agora é o proprietário de um grupo.",

            'expulsao_grupo' => !empty($grupo)
                ? "Você foi removido do grupo <strong>{$grupo}</strong>."
                : "Você foi removido de um grupo.",

            'aceite_solicitacao_grupo' => !empty($grupo)
                ? "<strong>{$nome}</strong> aprovou sua entrada no grupo <strong>{$grupo}</strong>."
                : "<strong>{$nome}</strong> aprovou sua entrada no grupo.",

            'solicitacao_grupo' => !empty($grupo)
                ? "<strong>{$nome}</strong> quer entrar no grupo <strong>{$grupo}</strong>."
                : "<strong>{$nome}</strong> solicitou entrar no seu grupo.",

            'convite_chat_grupo' => !empty($grupo)
                ? "<strong>{$nome}</strong> adicionou você ao grupo <strong>{$grupo}</strong>."
                : "<strong>{$nome}</strong> adicionou você a um novo grupo de chat.",

            'convite_grupo' => !empty($grupo)
                ? "<strong>{$nome}</strong> te convidou para o grupo <strong>{$grupo}</strong>."
                : "<strong>{$nome}</strong> te convidou para um grupo.",

            'curtida', 'curtida_post'         => "<strong>{$nome}</strong> curtiu sua publicação.",
            'curtida_comentario'              => "<strong>{$nome}</strong> curtiu seu comentário.",
            'comentario', 'comentario_post'   => "<strong>{$nome}</strong> comentou no seu post.",
            'pedido_amizade'                  => "<strong>{$nome}</strong> enviou um pedido de amizade.",
            'aceite_amizade', 'amizade_aceita' => "<strong>{$nome}</strong> aceitou seu pedido.",
            'mencao'                          => "<strong>{$nome}</strong> mencionou você em um post.",
            'compartilhar'                    => "<strong>{$nome}</strong> compartilhou seu post.",
            'interesse_mkt'                   => "<strong>{$nome}</strong> tem interesse no seu produto.",
            default                           => "Nova interação de <strong>{$nome}</strong>."
        };
    }

    /**
     * Gera o link de redirecionamento do Toast baseado na referência.
     */
    private static function generateToastLink(array $data): string {
        global $config;
        $base = $config['base_path'] ?? '/';

        return match ($data['tipo']) {
            'mensagem', 'convite_chat_grupo' => "{$base}chat?id={$data['id_referencia']}",
            
            'convite_grupo', 'solicitacao_grupo', 'aceite_solicitacao_grupo', 
            'promocao_moderador', 'rebaixamento_membro', 'transferencia_dono',
            'aceite_convite_grupo' 
                => "{$base}grupos/ver/{$data['id_referencia']}",

            'curtida', 'curtida_post', 'curtida_comentario', 'comentario', 'comentario_post', 'mencao', 'compartilhar' 
                => "{$base}postagem/{$data['id_referencia']}",

            'pedido_amizade', 'aceite_amizade', 'amizade_aceita' 
                => "{$base}perfil/{$data['id_referencia']}",

            'interesse_mkt' => "{$base}marketplace/item/{$data['id_referencia']}",

            default => "{$base}historico_notificacoes"
        };
    }
}