<?php
/**
 * src/FeedLogic.php
 * "Cérebro" do Feed - Versão: V111.5 (Sincronização Total de Parâmetros)
 * LÓGICA: Suporte a Compartilhamento, Marketplace, Link Preview e Filtro Anti-Bloqueio.
 * VERSÃO: V111.5 (socialbr.lol)
 * PAPEL: Garantir que bloqueados NUNCA apareçam, corrigindo o erro de bind_param.
 */

class FeedLogic {

    /**
     * Busca os posts para o feed principal com limites de paginação.
     * AJUSTE V111.5: Sincronização rigorosa de 9 parâmetros de bind.
     */
    public static function getFeedPosts($conn, $user_id, $limit = 10, $offset = 0) {
        
        // 1. QUERY PRINCIPAL: O WHERE garante a exclusão absoluta de bloqueados.
        $sql_posts = "SELECT
                        p.id, p.conteudo_texto, p.data_postagem,
                        p.privacidade, p.post_original_id, p.tipo_post,
                        u.id AS autor_id, u.nome, u.sobrenome, u.foto_perfil_url,
                        po.id AS original_post_id, po.conteudo_texto AS original_conteudo_texto,
                        po.data_postagem AS original_data_postagem,
                        uo.id AS original_autor_id, uo.nome AS original_autor_nome, 
                        uo.sobrenome AS original_autor_sobrenome, uo.foto_perfil_url AS original_autor_foto,
                        ma.id AS anuncio_id, 
                        ma.titulo_produto, 
                        ma.preco, 
                        ma.status_venda,
                        ma.categoria,
                        ma.cidade,
                        ma.estado,
                        mao.id AS original_anuncio_id,
                        mao.titulo_produto AS original_titulo_produto,
                        mao.preco AS original_preco,
                        mao.status_venda AS original_status_venda,
                        mao.categoria AS original_categoria,
                        mao.cidade AS original_cidade,
                        mao.estado AS original_estado,
                        (SELECT COUNT(*) FROM Curtidas WHERE id_postagem = p.id) AS total_curtidas,
                        (SELECT COUNT(*) FROM Curtidas WHERE id_postagem = p.id AND id_usuario = ?) AS usuario_curtiu,
                        (SELECT COUNT(*) FROM Postagens_Salvas WHERE id_postagem = p.id AND id_usuario = ?) AS usuario_salvou,
                        (SELECT COUNT(*) FROM Comentarios WHERE id_postagem = p.id AND status = 'ativo') AS total_comentarios,
                        IFNULL(po.contador_compartilhamentos, p.contador_compartilhamentos) AS total_compartilhamentos
                        FROM Postagens p
                        JOIN Usuarios u ON p.id_usuario = u.id
                        LEFT JOIN Postagens po ON p.post_original_id = po.id
                        LEFT JOIN Usuarios uo ON po.id_usuario = uo.id
                        LEFT JOIN Marketplace_Anuncios ma ON p.id = ma.id_postagem
                        LEFT JOIN Marketplace_Anuncios mao ON po.id = mao.id_postagem
                        WHERE p.status = 'ativo'
                        AND p.id_grupo IS NULL 
                        -- ESCUDO DE BLOQUEIO: Se houver bloqueio em qualquer direção, o post some.
                        AND p.id_usuario NOT IN (
                            SELECT usuario_dois_id FROM Amizades WHERE usuario_um_id = ? AND status = 'bloqueado'
                            UNION
                            SELECT usuario_um_id FROM Amizades WHERE usuario_dois_id = ? AND status = 'bloqueado'
                        )
                        -- REGRAS DE VISIBILIDADE
                        AND (p.id_usuario = ? 
                             OR p.id_usuario IN (SELECT usuario_dois_id FROM Amizades WHERE usuario_um_id = ? AND status = 'aceite')
                             OR p.id_usuario IN (SELECT usuario_um_id FROM Amizades WHERE usuario_dois_id = ? AND status = 'aceite')
                             OR p.privacidade = 'publico')
                        ORDER BY p.data_postagem DESC
                        LIMIT ? OFFSET ?";

        $stmt = $conn->prepare($sql_posts);
        
        /**
         * MAPEAMENTO CORRIGIDO (9 Parâmetros):
         * 1. $user_id (curtidas) | 2. $user_id (salvos)
         * 3. $user_id (bloqueio sub 1) | 4. $user_id (bloqueio sub 2)
         * 5. $user_id (check eu mesmo)
         * 6. $user_id (amigos sub 1) | 7. $user_id (amigos sub 2)
         * 8. $limit | 9. $offset
         */
        $stmt->bind_param("iiiiiiiii", $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $limit, $offset);
        
        $stmt->execute();
        $posts_res = $stmt->get_result();
        $posts = $posts_res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        require_once __DIR__ . '/ComentariosLogic.php'; 

        foreach ($posts as &$post) {
            $post_id = $post['id'];

            // Mídias
            $stmt_media = $conn->prepare("SELECT url_midia, tipo_midia FROM Postagens_Midia WHERE id_postagem = ?");
            $stmt_media->bind_param("i", $post_id);
            $stmt_media->execute();
            $post['midias'] = $stmt_media->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt_media->close();

            // Link Preview
            $stmt_meta = $conn->prepare("SELECT meta_key, meta_value FROM Post_Meta WHERE post_id = ?");
            $stmt_meta->bind_param("i", $post_id);
            $stmt_meta->execute();
            $meta_res = $stmt_meta->get_result();
            $link_data = [];
            while ($m = $meta_res->fetch_assoc()) { $link_data[$m['meta_key']] = $m['meta_value']; }
            $post['link_data'] = $link_data;
            $stmt_meta->close();

            // Enquetes
            $stmt_enquete = $conn->prepare("SELECT id, pergunta FROM Enquetes WHERE post_id = ?");
            $stmt_enquete->bind_param("i", $post_id);
            $stmt_enquete->execute();
            $enquete_res = $stmt_enquete->get_result();

            if ($enquete_res->num_rows > 0) {
                $enquete = $enquete_res->fetch_assoc();
                $stmt_opts = $conn->prepare("SELECT eo.id, eo.opcao_texto, 
                             (SELECT COUNT(*) FROM Enquete_Votos WHERE opcao_id = eo.id) AS total_votos,
                             (SELECT COUNT(*) FROM Enquete_Votos WHERE opcao_id = eo.id AND usuario_id = ?) AS usuario_votou
                             FROM Enquete_Opcoes eo WHERE eo.enquete_id = ?");
                $stmt_opts->bind_param("ii", $user_id, $enquete['id']);
                $stmt_opts->execute();
                $enquete['opcoes'] = $stmt_opts->get_result()->fetch_all(MYSQLI_ASSOC);
                
                $total_v = 0; $votou = false;
                foreach($enquete['opcoes'] as $opt) {
                    $total_v += $opt['total_votos'];
                    if($opt['usuario_votou'] > 0) $votou = true;
                }
                $enquete['total_geral_votos'] = $total_v;
                $enquete['usuario_ja_votou'] = $votou;
                $post['enquete'] = $enquete;
                $stmt_opts->close();
            }
            $stmt_enquete->close();

            // Prévia de Comentários
            if ($post['total_comentarios'] > 0) {
                $post['ultimos_comentarios'] = ComentariosLogic::getPreviewComentarios($conn, $post_id);
            } else {
                $post['ultimos_comentarios'] = [];
            }
        }

        return $posts;
    }
}