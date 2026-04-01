<?php
/**
 * CensuraLogic.php - O Escudo de Higiene Visual da socialbr.lol
 * PAPEL: Filtrar e mascarar termos ofensivos sem impedir a publicação.
 * VERSÃO: 1.0 (Arquitetura Atômica - Pronta para Moderadores)
 */

class CensuraLogic {
    private $db;
    private $config;
    private $palavrasCache = null;

    /**
     * O construtor recebe o banco e o array global $config carregado pelo database.php
     */
    public function __construct($db, $config = []) {
        $this->db = $db;
        $this->config = $config;
    }

    /**
     * MÉTODO PRINCIPAL: APLICAR MÁSCARA SOCIAL
     * Pega o texto bruto e devolve a versão "limpa" para o banco/timeline.
     */
    public function aplicarMascaraSocial($texto) {
        if (empty($texto)) return "";

        // 1. Verificação de Interruptor (Ativado/Desativado no Admin)
        $modoCensuraAtivo = isset($this->config['modo_censura']) ? (int)$this->config['modo_censura'] : 0;
        
        if ($modoCensuraAtivo === 0) {
            return $texto; // Liberdade total: retorna o texto original sem mexer em nada
        }

        // 2. Carregamento Inteligente da Blacklist
        $termosProibidos = $this->obterListaNegra();
        if (empty($termosProibidos)) return $texto;

        // 3. Processamento de Substituição
        foreach ($termosProibidos as $palavra) {
            // Gera a versão com símbolos (ex: puta -> p.t@)
            $versaoMascarada = $this->gerarMascara($palavra);
            
            // Substitui no texto original ignorando maiúsculas/minúsculas
            $texto = str_ireplace($palavra, $versaoMascarada, $texto);
        }

        return $texto;
    }

    /**
     * GERADOR DE CARACTERES ESPECIAIS
     * Implementa a lógica de troca solicitada usando os símbolos . ! ? @ 0
     */
    private function gerarMascara($palavra) {
        $mapa = [
            'a' => '@', 'A' => '@',
            'e' => '!', 'E' => '!',
            'i' => '!', 'I' => '!', // Pode usar ? aqui se preferir
            'o' => '0', 'O' => '0',
            'u' => '.', 'U' => '.',
            's' => '$', 'S' => '$'
        ];

        return strtr($palavra, $mapa);
    }

    /**
     * CACHE DE PALAVRAS
     * Evita múltiplas consultas ao banco de dados no mesmo ciclo de vida da página.
     */
    private function obterListaNegra() {
        if ($this->palavrasCache !== null) {
            return $this->palavrasCache;
        }

        $this->palavrasCache = [];
        $query = "SELECT termo FROM Palavras_Proibidas"; //
        $res = $this->db->query($query);

        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $this->palavrasCache[] = trim($row['termo']);
            }
        }

        return $this->palavrasCache;
    }

    /**
     * POSSIBILIDADE FUTURA: VERIFICAÇÃO SIMPLES
     * Verifica se existe ofensa sem alterar o texto (útil para logs de moderadores).
     */
    public function detectarOfensa($texto) {
        $termos = $this->obterListaNegra();
        foreach ($termos as $termo) {
            if (mb_stripos($texto, $termo) !== false) return true;
        }
        return false;
    }
}