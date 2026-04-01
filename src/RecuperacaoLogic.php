<?php
/**
 * ARQUIVO: src/RecuperacaoLogic.php
 * PAPEL: Lógica de negócio para recuperação de senha e códigos temporários.
 * VERSÃO: 1.0 - Alinhado com a tabela Usuarios_Recuperacao (socialbr.lol)
 */

class RecuperacaoLogic {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Verifica se o e-mail pertence a um utilizador ativo.
     */
    public function buscarUsuarioPorEmail($email) {
        $sql = "SELECT id, nome, status FROM Usuarios WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Gera um código de 6 dígitos, salva no banco e define expiração.
     * Invalida pedidos anteriores do mesmo utilizador para evitar spam.
     */
    public function gerarCodigoRecuperacao($usuario_id, $minutos_validos = 15) {
        // 1. Invalida qualquer código anterior não usado deste utilizador
        $this->invalidarCodigosAnteriores($usuario_id);

        // 2. Gera código numérico de 6 dígitos
        $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // 3. Define data de expiração (Data atual + X minutos)
        $data_expiracao = date('Y-m-d H:i:s', strtotime("+$minutos_validos minutes"));

        // 4. Persiste na tabela Usuarios_Recuperacao
        $sql = "INSERT INTO Usuarios_Recuperacao (usuario_id, codigo, data_expiracao) 
                VALUES (:uid, :cod, :exp)";
        
        $stmt = $this->pdo->prepare($sql);
        $sucesso = $stmt->execute([
            'uid' => $usuario_id,
            'cod' => $codigo,
            'exp' => $data_expiracao
        ]);

        return $sucesso ? $codigo : false;
    }

    /**
     * Valida se um código é legítimo, pertence ao utilizador e não expirou.
     */
    public function validarCodigo($usuario_id, $codigo) {
        $sql = "SELECT id FROM Usuarios_Recuperacao 
                WHERE usuario_id = :uid 
                AND codigo = :cod 
                AND usado = 0 
                AND data_expiracao > NOW() 
                LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'uid' => $usuario_id,
            'cod' => $codigo
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Marca o código como utilizado após o sucesso do reset.
     */
    public function marcarComoUsado($codigo_id) {
        $sql = "UPDATE Usuarios_Recuperacao SET usado = 1 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $codigo_id]);
    }

    /**
     * Executa a troca final da senha na tabela Usuarios.
     */
    public function atualizarSenhaUsuario($usuario_id, $nova_senha) {
        $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        
        $sql = "UPDATE Usuarios SET senha_hash = :hash WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'hash' => $hash,
            'id'   => $usuario_id
        ]);
    }

    /**
     * Limpeza interna: Garante que apenas o código mais recente seja válido.
     */
    private function invalidarCodigosAnteriores($usuario_id) {
        $sql = "UPDATE Usuarios_Recuperacao SET usado = 1 
                WHERE usuario_id = :uid AND usado = 0";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['uid' => $usuario_id]);
    }
}