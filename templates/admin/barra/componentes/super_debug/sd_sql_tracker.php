<?php
/**
 * FICHEIRO: templates/admin/barra/componentes/super_debug/sd_sql_tracker.php
 * PAPEL: Motor de Captura e Auditoria Persistente de SQL.
 * VERSÃO: 1.2 (Persistent Audit Edition)
 * RESPONSABILIDADE: Rastrear queries, analisar performance e gravar logs físicos para auditoria AJAX.
 */

class SQLPerfTracker {
    private static $instance = null;
    private $queries = [];
    private $conn = null;
    private $requestId = 'N/A';
    private $logFile;

    private function __construct($connection, $requestId = 'N/A') {
        $this->conn = $connection;
        $this->requestId = $requestId;
        
        // Caminho robusto para o log dentro da pasta config
        $this->logFile = __DIR__ . '/../../../../../config/sql_audit.log';
    }

    /**
     * Singleton Evoluído: Inicializa o motor com ID de Requisição.
     */
    public static function init($connection, $requestId = 'N/A') {
        if (self::$instance == null) {
            self::$instance = new SQLPerfTracker($connection, $requestId);
        }
        return self::$instance;
    }

    /**
     * Captura uma query, cronometra, analisa índices e escreve no disco.
     */
    public function logQuery($sql, $executionTime) {
        $analysis = $this->checkIndexUsage($sql);
        
        // CORRELACIONADOR DE EVENTOS (Dica de Ouro)
        // Identifica qual arquivo e linha disparou a query
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $caller = "Desconhecido";
        foreach ($backtrace as $step) {
            if (isset($step['file']) && !str_contains($step['file'], 'database.php') && !str_contains($step['file'], 'sd_sql_tracker.php')) {
                $caller = basename($step['file']) . ":" . $step['line'];
                break;
            }
        }

        $queryData = [
            'sql'           => $sql,
            'time'          => number_format($executionTime * 1000, 3), // ms
            'no_index'      => $analysis['no_index'],
            'rows_examined' => $analysis['rows'],
            'error'         => $this->conn->error,
            'caller'        => $caller
        ];

        // 1. Guarda na memória (para o HUD visual)
        $this->queries[] = $queryData;

        // 2. Persiste no disco (para auditoria AJAX)
        $this->writeToDisk($queryData);
    }

    /**
     * Escreve os dados no arquivo físico de auditoria.
     */
    private function writeToDisk($data) {
        // Trava de segurança: Não grava se o arquivo passar de 10MB
        if (file_exists($this->logFile) && filesize($this->logFile) > 10 * 1024 * 1024) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $status = $data['no_index'] ? '[ALERTA-INDICE]' : '[OK]';
        if ($data['error']) $status = '[ERRO-SQL]';

        $logEntry = sprintf(
            "[%s] [%s] [%s] %s %sms | Origem: %s | Query: %s%s",
            $timestamp,
            $this->requestId,
            $status,
            $_SERVER['REQUEST_URI'] ?? 'CLI',
            $data['time'],
            $data['caller'],
            trim(preg_replace('/\s+/', ' ', $data['sql'])),
            PHP_EOL
        );

        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Executa EXPLAIN silencioso para detectar Full Table Scans.
     */
    private function checkIndexUsage($sql) {
        $result = ['no_index' => false, 'rows' => 0];
        
        $trimmed_sql = trim($sql);
        if (stripos($trimmed_sql, 'SELECT') === 0) {
            // Previne que o tracker analise a si mesmo
            $explain = $this->conn->query("EXPLAIN " . $sql);
            
            if ($explain instanceof mysqli_result) {
                while ($row = $explain->fetch_assoc()) {
                    // Tipo 'ALL' em tabelas com mais de 10 registros acende o alerta
                    if (isset($row['type']) && $row['type'] == 'ALL' && (isset($row['rows']) && $row['rows'] > 10)) {
                        $result['no_index'] = true;
                    }
                    $result['rows'] += $row['rows'] ?? 0;
                }
                $explain->free();
            }
        }
        return $result;
    }

    public function getHistory() {
        return $this->queries;
    }

    public function getCount() {
        return count($this->queries);
    }
}