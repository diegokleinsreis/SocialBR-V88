<?php
/**
 * V85/src/UserLogic.php
 * "Cérebro" para gestão de utilizadores e segurança de acesso.
 * Versão: 2.1 - Módulo de Verificação de E-mail & Blindagem DNS.
 */

class UserLogic {

    /**
     * BLINDAGEM: Valida se o e-mail tem um formato correto e se o domínio existe (MX).
     * @param string $email
     * @return bool
     */
    public static function validarEmailReal(string $email): bool {
        // 1. Validação de sintaxe básica
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // 2. Extração do domínio
        $dominio = substr(strrchr($email, "@"), 1);

        // 3. Verificação de Registos MX (DNS)
        // Isso impede e-mails de domínios que não existem.
        if (!checkdnsrr($dominio, "MX")) {
            return false;
        }

        return true;
    }

    /**
     * Gera um token seguro para verificação de conta.
     */
    public static function gerarTokenVerificacao(): string {
        return bin2hex(random_bytes(32));
    }

    /**
     * Verifica se o utilizador precisa de ver o aviso (Toast) de verificação.
     * Regra: Não verificado E (Nunca avisado OU Último aviso > 24h).
     */
    public static function precisaMostrarAvisoVerificacao(array $userData): bool {
        if ((int)$userData['email_verificado'] === 1) {
            return false;
        }

        if (empty($userData['data_ultimo_aviso_verificacao'])) {
            return true;
        }

        $agora = new DateTime();
        $ultimoAviso = new DateTime($userData['data_ultimo_aviso_verificacao']);
        $intervalo = $agora->diff($ultimoAviso);

        // Retorna true se a diferença for maior ou igual a 1 dia (24h)
        return $intervalo->days >= 1;
    }

    /**
     * Atualiza o timestamp do último aviso de verificação enviado/exibido.
     */
    public static function atualizarDataAviso($conn, int $userId): void {
        $sql = "UPDATE Usuarios SET data_ultimo_aviso_verificacao = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Busca todos os dados essenciais para o cabeçalho do perfil.
     */
    public static function getProfileHeaderData($conn, $id_perfil_a_exibir, $id_usuario_logado) {
        
        // BUSCA OS DADOS DO PERFIL (Incluindo novos campos de verificação)
        $sql_perfil = "SELECT u.*, u.ultimo_acesso, b.nome AS nome_bairro, c.nome AS nome_cidade, e.sigla AS sigla_estado 
                         FROM Usuarios AS u 
                         LEFT JOIN Bairros AS b ON u.id_bairro = b.id 
                         LEFT JOIN Cidades AS c ON b.id_cidade = c.id 
                         LEFT JOIN Estados AS e ON c.id_estado = e.id 
                         WHERE u.id = ?";
        
        $stmt_perfil = $conn->prepare($sql_perfil);
        $stmt_perfil->bind_param("i", $id_perfil_a_exibir);
        $stmt_perfil->execute();
        $perfil_data = $stmt_perfil->get_result()->fetch_assoc();
        $stmt_perfil->close();

        if (!$perfil_data) {
            return null;
        }

        // DETALHES DA AMIZADE
        $amizade_details = [
            'status_amizade' => null,
            'amizade_id' => null,
            'id_remetente_pedido' => null,
            'sao_amigos' => false
        ];

        if ($id_usuario_logado > 0 && $id_usuario_logado != $id_perfil_a_exibir) {
            $sql_amizade = "SELECT id, status, usuario_um_id FROM Amizades 
                             WHERE (usuario_um_id = ? AND usuario_dois_id = ?) 
                                OR (usuario_um_id = ? AND usuario_dois_id = ?)";
            
            $stmt_amizade = $conn->prepare($sql_amizade);
            $stmt_amizade->bind_param("iiii", $id_usuario_logado, $id_perfil_a_exibir, $id_perfil_a_exibir, $id_usuario_logado);
            $stmt_amizade->execute();
            $resultado_amizade = $stmt_amizade->get_result()->fetch_assoc();
            
            if ($resultado_amizade) {
                $amizade_details['status_amizade'] = $resultado_amizade['status'];
                $amizade_details['amizade_id'] = $resultado_amizade['id'];
                $amizade_details['id_remetente_pedido'] = $resultado_amizade['usuario_um_id'];
                if ($resultado_amizade['status'] === 'aceite') {
                    $amizade_details['sao_amigos'] = true;
                }
            }
            $stmt_amizade->close();
        }

        // PRIVACIDADE
        $pode_ver_conteudo = false;
        if ($id_usuario_logado == $id_perfil_a_exibir || (int)$perfil_data['perfil_privado'] === 0 || $amizade_details['sao_amigos']) {
            $pode_ver_conteudo = true;
        }

        return [
            'perfil_data' => $perfil_data,
            'amizade_details' => $amizade_details,
            'pode_ver_conteudo' => $pode_ver_conteudo
        ];
    }

    /**
     * Busca os dados para a aba "Amigos" do perfil.
     */
    public static function getFriendsPageData($conn, $id_perfil_a_exibir, $id_usuario_logado, $perfil_data, $sao_amigos) {
        
        $pode_ver_lista_amigos = false;
        if ($id_usuario_logado === (int)$id_perfil_a_exibir) {
            $pode_ver_lista_amigos = true;
        } else {
            $privacidade = $perfil_data['privacidade_amigos'];
            if ($privacidade === 'todos') {
                $pode_ver_lista_amigos = true;
            } elseif ($privacidade === 'amigos' && $sao_amigos) {
                $pode_ver_lista_amigos = true;
            }
        }

        $lista_amigos = [];
        if ($pode_ver_lista_amigos) {
            $sql_amigos = "SELECT u.id, u.nome, u.sobrenome, u.nome_de_usuario, u.foto_perfil_url
                             FROM Amizades a
                             JOIN Usuarios u ON u.id = IF(a.usuario_um_id = ?, a.usuario_dois_id, a.usuario_um_id)
                             WHERE (a.usuario_um_id = ? OR a.usuario_dois_id = ?) AND a.status = 'aceite'";

            $stmt_amigos = $conn->prepare($sql_amigos);
            $stmt_amigos->bind_param("iii", $id_perfil_a_exibir, $id_perfil_a_exibir, $id_perfil_a_exibir);
            $stmt_amigos->execute();
            $result_amigos = $stmt_amigos->get_result();
            while ($amigo = $result_amigos->fetch_assoc()) {
                $lista_amigos[] = $amigo;
            }
            $stmt_amigos->close();
        }
        
        return [
            'pode_ver_lista_amigos' => $pode_ver_lista_amigos,
            'lista_amigos' => $lista_amigos
        ];
    }

    /**
     * Busca dados para Configurações (Agora com campos de verificação).
     */
    public static function getUserDataForSettings($conn, $user_id) {
        $sql_user = "SELECT u.*, b.nome AS nome_bairro 
                       FROM Usuarios u 
                       LEFT JOIN Bairros b ON u.id_bairro = b.id
                       WHERE u.id = ?";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $user_data = $stmt_user->get_result()->fetch_assoc();
        $stmt_user->close();
        return $user_data;
    }

    /**
     * Lista de bairros para dropdown.
     */
    public static function getBairrosList($conn) {
        global $config; 
        $id_cidade_padrao = (int)($config['CIDADE_PADRAO_ID'] ?? 129);
        
        $sql_bairros = "SELECT id, nome FROM Bairros WHERE id_cidade = ? ORDER BY nome ASC";
        $stmt_bairros = $conn->prepare($sql_bairros);
        $stmt_bairros->bind_param("i", $id_cidade_padrao);
        $stmt_bairros->execute();
        return $stmt_bairros->get_result();
    }

    /**
     * Lista de utilizadores bloqueados.
     */
    public static function getBlockedUsersList($conn, $id_usuario_logado, $filtro = null) {
        $lista_bloqueados = [];
        $params = [$id_usuario_logado];
        $types = "i";
        
        $sql = "SELECT u.id, u.nome, u.sobrenome, u.nome_de_usuario, u.foto_perfil_url, b.data_bloqueio
                FROM Bloqueios AS b
                JOIN Usuarios AS u ON b.bloqueado_id = u.id
                WHERE b.bloqueador_id = ?";
        
        if (!empty($filtro)) {
            $sql .= " AND (u.nome_de_usuario LIKE ? OR CONCAT(u.nome, ' ', u.sobrenome) LIKE ?)";
            $filtro_like = "%" . $filtro . "%";
            $params[] = $filtro_like;
            $params[] = $filtro_like;
            $types .= "ss";
        }
                
        $sql .= " ORDER BY b.data_bloqueio DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($usuario = $result->fetch_assoc()) {
            $lista_bloqueados[] = $usuario;
        }
        $stmt->close();
        return $lista_bloqueados;
    }
}