<?php
/**
 * ARQUIVO: src/ErrorLogic.php
 * VERSÃO: 1.0 (Sentinela - socialbr.lol)
 * PAPEL: Motor de persistência de erros e exceções.
 * STACK: PHP 8.x + PDO
 */

class ErrorLogic {
    private $pdo;

    /**
     * Construtor: Inicializa a conexão com o banco de dados.
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Registra um erro ou exceção no banco de dados.
     * Implementa a Dica de Ouro: Deduplicação por Hash.
     */
    public function registrarErro(
        string $tipo, 
        string $mensagem, 
        string $arquivo, 
        int $linha, 
        ?string $stack_trace = null
    ): bool {
        try {
            // 1. Coleta de Metadados de Ambiente
            $usuario_id   = $_SESSION['user_id'] ?? null;
            $url_acessada = $_SERVER['REQUEST_URI'] ?? 'CLI/Unknown';
            $ip_endereco  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $user_agent   = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconhecido';

            // 2. Geração do Hash de Identidade (Deduplicação)
            // Erros idênticos no mesmo local geram o mesmo hash.
            $hash_erro = md5($tipo . $mensagem . $arquivo . $linha);

            // 3. Persistência Inteligente (INSERT ... ON DUPLICATE KEY UPDATE)
            $sql = "INSERT INTO Logs_Erros_Sistema (
                        usuario_id, tipo, mensagem, arquivo, linha, 
                        url_acessada, ip_endereco, user_agent, 
                        stack_trace, hash_erro, ocorrencias
                    ) VALUES (
                        :uid, :tipo, :msg, :arq, :lin, 
                        :url, :ip, :ua, :stack, :hash, 1
                    ) 
                    ON DUPLICATE KEY UPDATE 
                        ocorrencias = ocorrencias + 1,
                        data_atualizacao = CURRENT_TIMESTAMP,
                        usuario_id = VALUES(usuario_id),
                        url_acessada = VALUES(url_acessada)";

            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute([
                ':uid'   => $usuario_id,
                ':tipo'  => $tipo,
                ':msg'   => $mensagem,
                ':arq'   => $arquivo,
                ':lin'   => $linha,
                ':url'   => $url_acessada,
                ':ip'    => $ip_endereco,
                ':ua'    => $user_agent,
                ':stack' => $stack_trace,
                ':hash'  => $hash_erro
            ]);

        } catch (Exception $e) {
            // Se o próprio sistema de log falhar, gravamos no log de erro nativo do servidor
            // para evitar que o site entre em loop infinito.
            error_log("FALHA CRÍTICA NO SENTINELA: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca os erros mais recentes para o Painel Administrativo.
     */
    public function obterLogsRecentes(int $limite = 50): array {
        $sql = "SELECT * FROM Logs_Erros_Sistema ORDER BY data_atualizacao DESC LIMIT :limite";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}