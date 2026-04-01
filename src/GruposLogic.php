<?php
/**
 * src/GruposLogic.php
 * "Cérebro" para o Módulo de Grupos.
 * PAPEL: Gerenciar permissões, visibilidade, feed rico e sistema social de convites/respostas.
 * VERSÃO: 3.0 (Sync: Correção de Método Ausente e Retorno de Ação - socialbr.lol)
 */

class GruposLogic {

    /* =========================================================================
       1. MÉTODOS DE LEITURA (INTEGRAIS)
       ========================================================================= */

    /**
     * BUSCA DADOS MESTRE DO GRUPO
     */
    public static function getGroupData($conn, $id_grupo, $id_usuario_logado = 0) {
        $sql = "SELECT g.*, 
                (SELECT COUNT(*) FROM Grupos_Membros WHERE id_grupo = g.id) as total_membros,
                gm.nivel_permissao, gm.id as membro_id
                FROM Grupos g
                LEFT JOIN Grupos_Membros gm ON gm.id_grupo = g.id AND gm.id_usuario = ?
                WHERE g.id = ? AND g.status = 'ativo'";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_usuario_logado, $id_grupo);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $result;
    }

    /**
     * VERIFICA PERMISSÃO DE VISUALIZAÇÃO
     */
    public static function podeVerConteudo($grupo_data) {
        if (!$grupo_data) return false;
        // Blindagem: Se for público qualquer um vê. Se for privado, apenas se membro_id existir.
        if ($grupo_data['privacidade'] === 'publico') return true;
        return !empty($grupo_data['membro_id']);
    }

    /**
     * VERIFICA SE O USUÁRIO TEM UM CONVITE PENDENTE PARA ESTE GRUPO
     */
    public static function verificarConvitePendente($conn, $id_grupo, $user_id) {
        if ($user_id <= 0) return false;

        $sql = "SELECT id FROM notificacoes 
                WHERE usuario_id = ? 
                  AND tipo = 'convite_grupo' 
                  AND id_referencia = ? 
                LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $id_grupo);
        $stmt->execute();
        $res = $stmt->get_result();
        $tem_convite = ($res->num_rows > 0);
        $stmt->close();
        
        return $tem_convite;
    }

    /**
     * BUSCA PEDIDOS DE ENTRADA PENDENTES (V3.0 - Resolve Erro no Componente)
     */
    public static function getSolicitacoesPendentes($conn, $id_grupo) {
        $sql = "SELECT s.id, s.id_usuario, s.data_pedido, u.nome, u.sobrenome, u.foto_perfil_url
                FROM Grupos_Solicitacoes s
                JOIN Usuarios u ON s.id_usuario = u.id
                WHERE s.id_grupo = ? AND s.status = 'pendente'
                ORDER BY s.data_pedido DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_grupo);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $res;
    }

    /**
     * LISTAR GRUPOS QUE O USUÁRIO PARTICIPA
     */
    public static function getMeusGrupos($conn, $id_usuario, $limit = 0) {
        $sql = "SELECT g.id, g.nome, g.foto_capa_url, gm.nivel_permissao 
                FROM Grupos_Membros gm
                JOIN Grupos g ON gm.id_grupo = g.id
                WHERE gm.id_usuario = ? AND g.status = 'ativo'
                ORDER BY g.nome ASC";
        
        if ($limit > 0) $sql .= " LIMIT ?";

        $stmt = $conn->prepare($sql);
        if ($limit > 0) $stmt->bind_param("ii", $id_usuario, $limit);
        else $stmt->bind_param("i", $id_usuario);
        
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    /**
     * RECOMENDAÇÕES (RESUMO)
     */
    public static function getRecomendacoes($conn, $id_usuario, $limit = 6) {
        $sql = "SELECT g.*, 
                (SELECT COUNT(*) FROM Grupos_Membros WHERE id_grupo = g.id) as total_membros
                FROM Grupos g
                WHERE g.status = 'ativo'
                AND g.id NOT IN (SELECT id_grupo FROM Grupos_Membros WHERE id_usuario = ?)
                ORDER BY total_membros DESC, g.data_criacao DESC
                LIMIT ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_usuario, $limit);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    /**
     * EXPLORAR TUDO
     */
    public static function getExplorarTudo($conn, $id_usuario) {
        $sql = "SELECT g.*, 
                (SELECT COUNT(*) FROM Grupos_Membros WHERE id_grupo = g.id) as total_membros
                FROM Grupos g
                WHERE g.status = 'ativo'
                AND g.id NOT IN (SELECT id_grupo FROM Grupos_Membros WHERE id_usuario = ?)
                ORDER BY total_membros DESC, g.data_criacao DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    /**
     * BUSCA DE GRUPOS
     */
    public static function buscarGrupos($conn, $termo) {
        $termo_like = "%" . $termo . "%";
        $sql = "SELECT g.*, (SELECT COUNT(*) FROM Grupos_Membros WHERE id_grupo = g.id) as total_membros
                FROM Grupos g
                WHERE g.nome LIKE ? AND g.status = 'ativo'
                ORDER BY total_membros DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $termo_like);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    /**
     * BUSCA POSTAGENS DO GRUPO
     */
    public static function getPostsDoGrupo($conn, $id_grupo, $id_usuario_logado) {
        $sql = "SELECT p.*, u.nome, u.sobrenome, u.nome_de_usuario, u.foto_perfil_url, u.id as autor_id,
                (SELECT COUNT(*) FROM Curtidas WHERE id_postagem = p.id) AS total_curtidas,
                (SELECT COUNT(*) FROM Curtidas WHERE id_postagem = p.id AND id_usuario = ?) AS usuario_curtiu,
                (SELECT COUNT(*) FROM Comentarios WHERE id_postagem = p.id AND status = 'ativo') AS total_comentarios
                FROM Postagens p
                JOIN Usuarios u ON p.id_usuario = u.id
                WHERE p.id_grupo = ? AND p.status = 'ativo'
                ORDER BY p.data_postagem DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_usuario_logado, $id_grupo);
        $stmt->execute();
        $posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Carrega a lógica de comentários para buscar as prévias
        require_once __DIR__ . '/ComentariosLogic.php';

        foreach ($posts as &$post) {
            $pid = $post['id'];
            
            // A. Mídias
            $stmt_m = $conn->prepare("SELECT url_midia, tipo_midia FROM Postagens_Midia WHERE id_postagem = ?");
            $stmt_m->bind_param("i", $pid); $stmt_m->execute();
            $post['midias'] = $stmt_m->get_result()->fetch_all(MYSQLI_ASSOC); $stmt_m->close();

            // B. Enquete
            $stmt_e = $conn->prepare("SELECT id, pergunta FROM Enquetes WHERE post_id = ?");
            $stmt_e->bind_param("i", $pid); $stmt_e->execute();
            $enquete_res = $stmt_e->get_result();
            if ($enquete_res->num_rows > 0) {
                $enquete = $enquete_res->fetch_assoc();
                $stmt_o = $conn->prepare("SELECT eo.id, eo.opcao_texto, 
                         (SELECT COUNT(*) FROM Enquete_Votos WHERE opcao_id = eo.id) as total_votos,
                         (SELECT COUNT(*) FROM Enquete_Votos WHERE opcao_id = eo.id AND usuario_id = ?) as usuario_votou
                         FROM Enquete_Opcoes eo WHERE eo.enquete_id = ?");
                $stmt_o->bind_param("ii", $id_usuario_logado, $enquete['id']); $stmt_o->execute();
                $enquete['opcoes'] = $stmt_o->get_result()->fetch_all(MYSQLI_ASSOC);
                $post['enquete'] = $enquete; $stmt_o->close();
            }
            $stmt_e->close();

            // C. Link Preview
            $stmt_l = $conn->prepare("SELECT meta_key, meta_value FROM Post_Meta WHERE post_id = ?");
            $stmt_l->bind_param("i", $pid); $stmt_l->execute();
            $meta_res = $stmt_l->get_result(); $link_data = [];
            while ($m = $meta_res->fetch_assoc()) { $link_data[$m['meta_key']] = $m['meta_value']; }
            $post['link_data'] = $link_data; $stmt_l->close();

            // D. Injeção de Prévia de Comentários
            if ($post['total_comentarios'] > 0) {
                $post['ultimos_comentarios'] = ComentariosLogic::getPreviewComentarios($conn, $pid);
            } else {
                $post['ultimos_comentarios'] = [];
            }
        }
        return $posts;
    }

    /**
     * BUSCA LISTA DE MEMBROS
     */
    public static function getGroupMembers($conn, $id_grupo) {
        $sql = "SELECT u.id, u.nome, u.sobrenome, u.nome_de_usuario, u.foto_perfil_url, gm.nivel_permissao 
                FROM Grupos_Membros gm
                JOIN Usuarios u ON gm.id_usuario = u.id
                WHERE gm.id_grupo = ?
                ORDER BY CASE gm.nivel_permissao 
                    WHEN 'dono' THEN 1 
                    WHEN 'moderador' THEN 2 
                    ELSE 3 END, u.nome ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_grupo);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $res;
    }

    /**
     * LISTA AMIGOS DISPONÍVEIS PARA CONVITE
     */
    public static function getAmigosParaConvidar($conn, $id_grupo, $user_id) {
        $sql = "SELECT u.id, u.nome, u.sobrenome, u.foto_perfil_url 
                FROM Amizades a
                JOIN Usuarios u ON (a.usuario_um_id = u.id OR a.usuario_dois_id = u.id)
                WHERE (a.usuario_um_id = ? OR a.usuario_dois_id = ?)
                  AND a.status = 'aceite'
                  AND u.id != ?
                  AND u.id NOT IN (SELECT id_usuario FROM Grupos_Membros WHERE id_grupo = ?)
                ORDER BY u.nome ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $user_id, $user_id, $user_id, $id_grupo);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    /* =========================================================================
       2. MÉTODOS DE ESCRITA (INTEGRAIS)
       ========================================================================= */

    /**
     * RESPONDE A UM CONVITE DE GRUPO
     */
    public static function responderConvite($conn, $id_grupo, $user_id, $acao) {
        if ($acao === 'aceitar') {
            $conn->begin_transaction();
            try {
                $stmt1 = $conn->prepare("INSERT INTO Grupos_Membros (id_grupo, id_usuario, nivel_permissao) VALUES (?, ?, 'membro')");
                $stmt1->bind_param("ii", $id_grupo, $user_id);
                $stmt1->execute();

                $stmt2 = $conn->prepare("DELETE FROM notificacoes WHERE usuario_id = ? AND tipo = 'convite_grupo' AND id_referencia = ?");
                $stmt2->bind_param("ii", $user_id, $id_grupo);
                $stmt2->execute();

                $conn->commit();
                return true;
            } catch (Exception $e) {
                $conn->rollback();
                return false;
            }
        } else {
            $stmt = $conn->prepare("DELETE FROM notificacoes WHERE usuario_id = ? AND tipo = 'convite_grupo' AND id_referencia = ?");
            $stmt->bind_param("ii", $user_id, $id_grupo);
            $sucesso = $stmt->execute();
            $stmt->close();
            return $sucesso;
        }
    }

    /**
     * DISPARA UM CONVITE PARA O AMIGO
     */
    public static function enviarConvite($conn, $id_grupo, $remetente_id, $destinatario_id) {
        $sql = "INSERT INTO notificacoes (usuario_id, remetente_id, tipo, id_referencia, lida) 
                VALUES (?, ?, 'convite_grupo', ?, 0)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $destinatario_id, $remetente_id, $id_grupo);
        $sucesso = $stmt->execute();
        $stmt->close();

        return $sucesso;
    }

    /**
     * PARTICIPAÇÃO: Processa entrada ou solicitação
     * V3.0: Retorno de ação 'solicitacao_enviada' sincronizado com API participar.php
     */
    public static function participar($conn, $id_grupo, $id_usuario) {
        $grupo = self::getGroupData($conn, $id_grupo, $id_usuario);
        if (!$grupo) return ['status' => 'erro', 'msg' => 'Grupo não encontrado.'];
        if ($grupo['membro_id']) return ['status' => 'erro', 'msg' => 'Já é membro.'];

        if ($grupo['privacidade'] === 'publico') {
            $sql = "INSERT INTO Grupos_Membros (id_grupo, id_usuario, nivel_permissao) VALUES (?, ?, 'membro')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $id_grupo, $id_usuario);
            if ($stmt->execute()) {
                $stmt->close();
                return ['status' => 'sucesso', 'acao' => 'entrou'];
            }
        } else {
            $sql = "INSERT INTO Grupos_Solicitacoes (id_grupo, id_usuario, status, data_pedido) VALUES (?, ?, 'pendente', NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $id_grupo, $id_usuario);
            if ($stmt->execute()) {
                $stmt->close();
                return ['status' => 'sucesso', 'acao' => 'solicitacao_enviada'];
            }
        }
        return ['status' => 'erro', 'msg' => 'Falha ao processar ação.'];
    }

    public static function sair($conn, $id_grupo, $id_usuario) {
        $sql = "DELETE FROM Grupos_Membros WHERE id_grupo = ? AND id_usuario = ? AND nivel_permissao != 'dono'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_grupo, $id_usuario);
        $sucesso = $stmt->execute(); $stmt->close();
        return $sucesso;
    }

    public static function decidirSolicitacao($conn, $id_solicitacao, $id_grupo, $acao) {
        if ($acao === 'aprovar') {
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("SELECT id_usuario FROM Grupos_Solicitacoes WHERE id = ? AND id_grupo = ?");
                $stmt->bind_param("ii", $id_solicitacao, $id_grupo);
                $stmt->execute();
                $resultado = $stmt->get_result()->fetch_assoc();
                if (!$resultado) throw new Exception("Solicitação inválida.");
                $uid = $resultado['id_usuario'];

                $stmt2 = $conn->prepare("INSERT INTO Grupos_Membros (id_grupo, id_usuario, nivel_permissao) VALUES (?, ?, 'membro')");
                $stmt2->bind_param("ii", $id_grupo, $uid); $stmt2->execute();

                $stmt3 = $conn->prepare("DELETE FROM Grupos_Solicitacoes WHERE id = ?");
                $stmt3->bind_param("i", $id_solicitacao); $stmt3->execute();

                $conn->commit(); return true;
            } catch (Exception $e) { $conn->rollback(); return false; }
        } else {
            $stmt = $conn->prepare("DELETE FROM Grupos_Solicitacoes WHERE id = ? AND id_grupo = ?");
            $stmt->bind_param("ii", $id_solicitacao, $id_grupo);
            $sucesso = $stmt->execute(); $stmt->close();
            return $sucesso;
        }
    }

    public static function atualizarGrupo($conn, $id_grupo, $dados) {
        $sql = "UPDATE Grupos SET nome = ?, descricao = ?, privacidade = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $dados['nome'], $dados['descricao'], $dados['privacidade'], $id_grupo);
        $sucesso = $stmt->execute(); $stmt->close();
        return $sucesso;
    }

    public static function excluirGrupo($conn, $id_grupo) {
        $sql = "UPDATE Grupos SET status = 'excluido' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_grupo);
        $sucesso = $stmt->execute(); $stmt->close();
        return $sucesso;
    }

    public static function alterarPapelMembro($conn, $id_grupo, $id_usuario_alvo, $novo_papel) {
        if (!in_array($novo_papel, ['membro', 'moderador', 'dono'])) return false;
        $conn->begin_transaction();
        try {
            if ($novo_papel === 'dono') {
                $stmt1 = $conn->prepare("UPDATE Grupos_Membros SET nivel_permissao = 'moderador' WHERE id_grupo = ? AND nivel_permissao = 'dono'");
                $stmt1->bind_param("i", $id_grupo); $stmt1->execute();
                $stmt2 = $conn->prepare("UPDATE Grupos SET id_dono = ? WHERE id = ?");
                $stmt2->bind_param("ii", $id_usuario_alvo, $id_grupo); $stmt2->execute();
            }
            $stmt3 = $conn->prepare("UPDATE Grupos_Membros SET nivel_permissao = ? WHERE id_grupo = ? AND id_usuario = ?");
            $stmt3->bind_param("sii", $novo_papel, $id_grupo, $id_usuario_alvo); $stmt3->execute();
            $conn->commit(); return true;
        } catch (Exception $e) { $conn->rollback(); return false; }
    }

    public static function removerMembro($conn, $id_grupo, $id_usuario_alvo) {
        $sql = "DELETE FROM Grupos_Membros WHERE id_grupo = ? AND id_usuario = ? AND nivel_permissao != 'dono'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_grupo, $id_usuario_alvo);
        $sucesso = $stmt->execute(); $stmt->close();
        return $sucesso;
    }
}