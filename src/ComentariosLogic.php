<?php
/**
 * src/ComentariosLogic.php
 * Lógica de Negócio para Comentários - Versão: V102.1 (Sincronização de Atributos - socialbr.lol)
 * Responsável por: Árvore de Respostas, Preview com Foto e Estatísticas AJAX.
 */

class ComentariosLogic {

    /**
     * Busca a lista completa de comentários de um post para o Modal.
     * Organiza os dados em formato de árvore (Pai -> Filhos -> Netos).
     * @param mysqli $conn Conexão ativa com o banco.
     * @param int $id_postagem ID do post alvo.
     * @param int $user_id ID do usuário logado (para checar curtidas individuais).
     * @return array Estrutura hierárquica de comentários.
     */
    public static function getComentariosCompletos($conn, $id_postagem, $user_id) {
        
        // 1. QUERY ÚNICA: Busca todos os dados de uma vez para performance máxima.
        $sql = "SELECT 
                    c.id, 
                    c.conteudo_texto, 
                    c.data_comentario, 
                    c.id_comentario_pai,
                    u.id AS autor_id, 
                    u.nome, 
                    u.sobrenome, 
                    u.foto_perfil_url,
                    (SELECT COUNT(*) FROM Curtidas_Comentarios WHERE id_comentario = c.id) AS total_curtidas,
                    (SELECT COUNT(*) FROM Curtidas_Comentarios WHERE id_comentario = c.id AND id_usuario = ?) AS usuario_curtiu
                FROM Comentarios c
                JOIN Usuarios u ON c.id_usuario = u.id
                WHERE c.id_postagem = ? AND c.status = 'ativo'
                ORDER BY c.data_comentario ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $id_postagem);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $lista_plana = [];
        while ($row = $result->fetch_assoc()) {
            // Garante que o caminho da foto de perfil seja tratado corretamente
            $row['autor_foto_url'] = !empty($row['foto_perfil_url']) ? $row['foto_perfil_url'] : 'assets/images/default-avatar.png';
            $lista_plana[] = $row;
        }
        $stmt->close();

        // 2. PROCESSAMENTO: Transforma a lista plana do SQL na árvore de threads.
        return self::buildCommentTree($lista_plana);
    }

    /**
     * Função Recursiva para Montagem da Árvore (Threads).
     */
    private static function buildCommentTree(array &$elementos, $parentId = null) {
        $ramo = [];

        foreach ($elementos as $elemento) {
            if ($elemento['id_comentario_pai'] == $parentId) {
                // Busca recursiva: Procura respostas para este comentário específico
                $filhos = self::buildCommentTree($elementos, $elemento['id']);
                
                // Se encontrar respostas, anexa ao array 'respostas'
                $elemento['respostas'] = $filhos ? $filhos : [];
                
                $ramo[] = $elemento;
            }
        }

        return $ramo;
    }

    /**
     * Busca os 2 comentários mais recentes para o "Preview" do Feed.
     * CORREÇÃO PASSO 78: id_usuario adicionado para compatibilidade com lista_comentarios.php.
     */
    public static function getPreviewComentarios($conn, $id_postagem) {
        $sql = "SELECT 
                    c.id, 
                    c.conteudo_texto, 
                    u.id AS id_usuario,
                    u.id AS autor_id,
                    u.nome AS autor_nome, 
                    u.sobrenome AS autor_sobrenome,
                    u.foto_perfil_url AS autor_foto
                FROM Comentarios c
                JOIN Usuarios u ON c.id_usuario = u.id
                WHERE c.id_postagem = ? AND c.status = 'ativo'
                ORDER BY c.data_comentario DESC 
                LIMIT 2";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_postagem);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Reverte para mostrar em ordem cronológica (mais antigo primeiro no preview)
        return array_reverse($res);
    }

    /**
     * Função auxiliar para formatar a data do comentário de forma "humana".
     */
    public static function formatarDataHumana($data) {
        $tempo = strtotime($data);
        $diferenca = time() - $tempo;

        if ($diferenca < 60) return "agora há pouco";
        if ($diferenca < 3600) return "há " . round($diferenca / 60) . " min";
        if ($diferenca < 86400) return "há " . round($diferenca / 3600) . " h";
        if ($diferenca < 604800) return "há " . round($diferenca / 86400) . " dias";
        
        return date("d/m/Y", $tempo);
    }
}