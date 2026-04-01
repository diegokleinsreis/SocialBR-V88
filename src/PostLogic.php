<?php
/**
 * src/PostLogic.php
 * "Cérebro" para busca de posts (Perfil, Salvos, Single).
 * VERSÃO V104.0 - SINCRONIZAÇÃO DE DADOS SOCIAIS (socialbr.lol)
 */

class PostLogic {

    /**
     * Busca os posts para a aba "Posts" de um perfil específico.
     * Atualizado para suportar Título e Descrição do Marketplace.
     */
    public static function getPostsForProfile($conn, $id_usuario_logado, $id_do_perfil_a_exibir) {
        
        $sql_posts = "SELECT
                        p.id, p.conteudo_texto, p.data_postagem,
                        p.privacidade, p.post_original_id, p.tipo_post,
                        u.id AS autor_id, u.nome, u.sobrenome, u.foto_perfil_url,
                        po.id AS original_post_id, po.conteudo_texto AS original_conteudo_texto,
                        po.data_postagem AS original_data_postagem,
                        uo.id AS original_autor_id, uo.nome AS original_autor_nome, 
                        uo.sobrenome AS original_autor_sobrenome, uo.foto_perfil_url AS original_autor_foto,
                        -- Integração Marketplace: Dados vitais para o post_template
                        ma.id AS anuncio_id, 
                        ma.titulo_produto, 
                        ma.descricao_produto, 
                        ma.preco, 
                        ma.categoria, 
                        ma.status_venda, 
                        ma.estado, 
                        ma.cidade,
                        (SELECT COUNT(*) FROM Curtidas WHERE id_postagem = p.id) AS total_curtidas,
                        (SELECT COUNT(*) FROM Curtidas WHERE id_postagem = p.id AND id_usuario = ?) AS usuario_curtiu,
                        (SELECT COUNT(*) FROM Comentarios WHERE id_postagem = p.id AND status = 'ativo') AS total_comentarios,
                        (SELECT COUNT(*) FROM Postagens_Salvas WHERE id_postagem = p.id AND id_usuario = ?) AS usuario_salvou,
                        IFNULL(po.contador_compartilhamentos, p.contador_compartilhamentos) AS total_compartilhamentos
                    FROM Postagens AS p
                    JOIN Usuarios AS u ON p.id_usuario = u.id
                    LEFT JOIN Amizades AS a ON 
                        ((a.usuario_um_id = p.id_usuario AND a.usuario_dois_id = ?) OR (a.usuario_um_id = ? AND a.usuario_dois_id = p.id_usuario))
                        AND a.status = 'aceite'
                    LEFT JOIN Postagens AS po ON p.post_original_id = po.id
                    LEFT JOIN Usuarios AS uo ON po.id_usuario = uo.id
                    -- JOIN com Marketplace para sincronizar dados no perfil
                    LEFT JOIN Marketplace_Anuncios ma ON p.id = ma.id_postagem
                    WHERE p.id_usuario = ? AND p.status = 'ativo' AND u.status = 'ativo'
                    AND (p.post_original_id IS NULL OR (po.status = 'ativo' AND uo.status = 'ativo'))
                    AND (p.id_usuario = ? OR p.privacidade = 'publico' OR (p.privacidade = 'amigos' AND a.id IS NOT NULL))
                    GROUP BY p.id ORDER BY p.data_postagem DESC";
        
        $stmt_posts = $conn->prepare($sql_posts);
        $stmt_posts->bind_param("iiiiii", $id_usuario_logado, $id_usuario_logado, $id_usuario_logado, $id_usuario_logado, $id_do_perfil_a_exibir, $id_usuario_logado);
        $stmt_posts->execute();
        $result_posts = $stmt_posts->get_result();
        $posts = [];

        while ($post = $result_posts->fetch_assoc()) {
            // Formatação de Preço para posts de venda
            if ($post['tipo_post'] === 'venda' && !empty($post['preco'])) {
                $post['preco_formatado'] = 'R$ ' . number_format($post['preco'], 2, ',', '.');
            }
            
            $id_ref = !empty($post['post_original_id']) ? $post['post_original_id'] : $post['id'];
            $post = self::attachPostAddons($conn, $post, $id_ref, $id_usuario_logado);
            $posts[] = $post;
        }
        $stmt_posts->close();
        return $posts;
    }

    /**
     * Busca os posts salvos do usuário logado.
     */
    public static function getSavedPosts($conn, $id_usuario_logado) {
        $sql_posts = "SELECT p.id, p.conteudo_texto, p.data_postagem, p.privacidade, p.post_original_id, p.tipo_post,
                        u.id AS autor_id, u.nome, u.sobrenome, u.foto_perfil_url, 
                        po.id AS original_post_id, po.conteudo_texto AS original_conteudo_texto,
                        po.data_postagem AS original_data_postagem,
                        uo.id AS original_autor_id, uo.nome AS original_autor_nome, 
                        uo.sobrenome AS original_autor_sobrenome, uo.foto_perfil_url AS original_autor_foto,
                        -- Marketplace em Itens Salvos
                        ma.id AS anuncio_id, ma.titulo_produto, ma.descricao_produto, ma.preco, 
                        ma.categoria, ma.status_venda, ma.estado, ma.cidade,
                        (SELECT COUNT(*) FROM Curtidas WHERE id_postagem = p.id) AS total_curtidas, 
                        (SELECT COUNT(*) FROM Curtidas WHERE id_postagem = p.id AND id_usuario = ?) AS usuario_curtiu, 
                        (SELECT COUNT(*) FROM Comentarios WHERE id_postagem = p.id AND status = 'ativo') AS total_comentarios, 
                        1 AS usuario_salvou,
                        IFNULL(po.contador_compartilhamentos, p.contador_compartilhamentos) AS total_compartilhamentos
                      FROM Postagens_Salvas AS ps 
                      JOIN Postagens AS p ON ps.id_postagem = p.id 
                      JOIN Usuarios AS u ON p.id_usuario = u.id
                      LEFT JOIN Amizades AS a ON 
                        ((a.usuario_um_id = p.id_usuario AND a.usuario_dois_id = ?) OR (a.usuario_um_id = ? AND a.usuario_dois_id = p.id_usuario))
                        AND a.status = 'aceite'
                      LEFT JOIN Postagens AS po ON p.post_original_id = po.id
                      LEFT JOIN Usuarios AS uo ON po.id_usuario = uo.id
                      LEFT JOIN Marketplace_Anuncios ma ON p.id = ma.id_postagem
                      WHERE ps.id_usuario = ? AND p.status = 'ativo'
                      AND (p.post_original_id IS NULL OR (po.status = 'ativo' AND uo.status = 'ativo'))
                      AND (p.id_usuario = ? OR p.privacidade = 'publico' OR (p.privacidade = 'amigos' AND a.id IS NOT NULL))
                      GROUP BY p.id ORDER BY ps.data_salvo DESC";
        
        $stmt_posts = $conn->prepare($sql_posts);
        $stmt_posts->bind_param("iiiii", $id_usuario_logado, $id_usuario_logado, $id_usuario_logado, $id_usuario_logado, $id_usuario_logado);
        $stmt_posts->execute();
        $result_posts = $stmt_posts->get_result();
        $posts = [];

        while ($post = $result_posts->fetch_assoc()) {
            if ($post['tipo_post'] === 'venda' && !empty($post['preco'])) {
                $post['preco_formatado'] = 'R$ ' . number_format($post['preco'], 2, ',', '.');
            }
            $id_ref = !empty($post['post_original_id']) ? $post['post_original_id'] : $post['id'];
            $post = self::attachPostAddons($conn, $post, $id_ref, $id_usuario_logado);
            $posts[] = $post;
        }
        $stmt_posts->close();
        return $posts;
    }

    /**
     * Busca um post individual completo (Página de Postagem Única).
     */
    public static function getSinglePostWithComments($conn, $post_id, $user_id) {
        $sql_post = "SELECT p.*, u.id AS autor_id, u.nome, u.sobrenome, u.foto_perfil_url, u.status as autor_status,
                         po.id AS original_post_id, po.conteudo_texto AS original_conteudo_texto, 
                         po.data_postagem AS original_data_postagem, po.status as original_post_status,
                         uo.id AS original_autor_id, uo.nome AS original_autor_nome, 
                         uo.sobrenome AS original_autor_sobrenome, uo.foto_perfil_url AS original_autor_foto, uo.status as original_autor_status,
                         -- Marketplace Single View
                         ma.id AS anuncio_id, ma.titulo_produto, ma.descricao_produto, ma.preco, 
                         ma.categoria, ma.status_venda, ma.estado, ma.cidade,
                         (SELECT COUNT(*) FROM Curtidas WHERE id_postagem = p.id) AS total_curtidas,
                         (SELECT COUNT(*) FROM Curtidas WHERE id_postagem = p.id AND id_usuario = ?) AS usuario_curtiu,
                         (SELECT COUNT(*) FROM Comentarios WHERE id_postagem = p.id AND status = 'ativo') AS total_comentarios,
                         (SELECT COUNT(*) FROM Postagens_Salvas WHERE id_postagem = p.id AND id_usuario = ?) AS usuario_salvou
                       FROM Postagens AS p
                       JOIN Usuarios AS u ON p.id_usuario = u.id
                       LEFT JOIN Postagens AS po ON p.post_original_id = po.id
                       LEFT JOIN Usuarios AS uo ON po.id_usuario = uo.id
                       LEFT JOIN Marketplace_Anuncios ma ON p.id = ma.id_postagem
                       WHERE p.id = ?"; 

        $stmt_post = $conn->prepare($sql_post);
        $stmt_post->bind_param("iii", $user_id, $user_id, $post_id);
        $stmt_post->execute();
        $post = $stmt_post->get_result()->fetch_assoc();
        $stmt_post->close();

        if (!$post) return null;
        
        if ($post['tipo_post'] === 'venda' && !empty($post['preco'])) {
            $post['preco_formatado'] = 'R$ ' . number_format($post['preco'], 2, ',', '.');
        }

        $id_ref = !empty($post['post_original_id']) ? $post['post_original_id'] : $post['id'];
        $post = self::attachPostAddons($conn, $post, $id_ref, $user_id);

        // Busca Comentários
        $sql_comments = "SELECT c.*, u.nome, u.sobrenome, u.foto_perfil_url,
                             (SELECT COUNT(*) FROM Curtidas_Comentarios WHERE id_comentario = c.id) AS total_curtidas_comentario,
                             (SELECT COUNT(*) FROM Curtidas_Comentarios WHERE id_comentario = c.id AND id_usuario = ?) AS usuario_curtiu_comentario
                           FROM Comentarios AS c JOIN Usuarios AS u ON c.id_usuario = u.id
                           WHERE c.id_postagem = ? AND c.status = 'ativo' ORDER BY c.data_comentario ASC";
        $stmt_c = $conn->prepare($sql_comments);
        $stmt_c->bind_param("ii", $user_id, $post_id);
        $stmt_c->execute();
        $res_c = $stmt_c->get_result();
        $comentarios = []; $respostas = [];
        while ($row = $res_c->fetch_assoc()) {
            if ($row['id_comentario_pai'] === null) $comentarios[$row['id']] = $row;
            else $respostas[$row['id_comentario_pai']][] = $row;
        }
        $stmt_c->close();

        return ['post' => $post, 'comentarios' => $comentarios, 'respostas' => $respostas];
    }

    /**
     * FUNÇÃO AUXILIAR: Anexa Mídias, Links, Enquetes e PRÉVIA DE COMENTÁRIOS ao objeto Post.
     * VERSÃO 104.0: Inclui injeção de prévia de comentários para Feed e Perfil.
     */
    private static function attachPostAddons($conn, $post, $id_ref, $user_id) {
        $sql_m = "SELECT id, url_midia, tipo_midia FROM Postagens_Midia WHERE id_postagem = ?";
        $stmt_m = $conn->prepare($sql_m);
        $stmt_m->bind_param("i", $id_ref); $stmt_m->execute();
        $post['midias'] = $stmt_m->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_m->close();

        $sql_l = "SELECT meta_key, meta_value FROM Post_Meta WHERE post_id = ?";
        $stmt_l = $conn->prepare($sql_l);
        $stmt_l->bind_param("i", $id_ref); $stmt_l->execute();
        $res_l = $stmt_l->get_result();
        $post['link_data'] = [];
        while($m = $res_l->fetch_assoc()) $post['link_data'][$m['meta_key']] = $m['meta_value'];
        $stmt_l->close();

        $sql_e = "SELECT id, pergunta FROM Enquetes WHERE post_id = ?";
        $stmt_e = $conn->prepare($sql_e);
        $stmt_e->bind_param("i", $id_ref); $stmt_e->execute();
        $enquete = $stmt_e->get_result()->fetch_assoc();
        if ($enquete) {
            $sql_o = "SELECT id, opcao_texto, 
                      (SELECT COUNT(*) FROM Enquete_Votos WHERE opcao_id = Enquete_Opcoes.id) AS total_votos,
                      (SELECT COUNT(*) FROM Enquete_Votos WHERE opcao_id = Enquete_Opcoes.id AND usuario_id = ?) AS usuario_votou
                      FROM Enquete_Opcoes WHERE enquete_id = ?";
            $stmt_o = $conn->prepare($sql_o);
            $stmt_o->bind_param("ii", $user_id, $enquete['id']); $stmt_o->execute();
            $enquete['opcoes'] = $stmt_o->get_result()->fetch_all(MYSQLI_ASSOC);
            $total_geral = 0; $ja_votou = false;
            foreach($enquete['opcoes'] as $opt) {
                $total_geral += $opt['total_votos'];
                if($opt['usuario_votou'] > 0) $ja_votou = true;
            }
            $enquete['total_geral_votos'] = $total_geral;
            $enquete['usuario_ja_votou'] = $ja_votou;
            $post['enquete'] = $enquete;
            $stmt_o->close();
        }
        $stmt_e->close();

        // NOVO (PASSO 77): Injeção de Prévia de Comentários em todos os contextos de post
        require_once __DIR__ . '/ComentariosLogic.php';
        if (isset($post['total_comentarios']) && (int)$post['total_comentarios'] > 0) {
            $post['ultimos_comentarios'] = ComentariosLogic::getPreviewComentarios($conn, $post['id']);
        } else {
            $post['ultimos_comentarios'] = [];
        }

        return $post;
    }

    public static function getGalleryMedia($conn, $id_usuario) {
        $sql = "SELECT pm.id, pm.url_midia, pm.tipo_midia, pm.id_postagem FROM Postagens_Midia pm
                JOIN Postagens p ON pm.id_postagem = p.id WHERE p.id_usuario = ? 
                AND p.status = 'ativo' AND pm.salvo_na_galeria = 1 ORDER BY p.data_postagem DESC";
        $stmt = $conn->prepare($sql); $stmt->bind_param("i", $id_usuario);
        $stmt->execute(); $res = $stmt->get_result();
        $midias = []; while ($row = $res->fetch_assoc()) $midias[] = $row;
        $stmt->close(); return $midias;
    }
}