<?php
/**
 * ARQUIVO: src/RotasLogic.php
 * VERSÃO: 1.8 (Admin Privilege & Deep Bypass - socialbr.lol)
 * PAPEL: O motor de inteligência por trás do roteamento e menus.
 * EVOLUÇÃO: Implementado bypass total para administradores em rotas bloqueadas/manutenção.
 */

class RotasLogic {
    private $pdo;
    private $user_role;
    private $is_logged;
    private $slug_atual;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->is_logged = isset($_SESSION['user_id']);
        $this->user_role = $_SESSION['user_role'] ?? 'visitante';
    }

    /**
     * Tenta encontrar uma rota válida com suporte a profundidade (ex: marketplace/item/10)
     */
    public function buscarRota(string $slug_solicitado) {
        $this->slug_atual = trim($slug_solicitado, '/');
        $partes = explode('/', $this->slug_atual);
        
        // 1. Tenta a busca exata primeiro
        $stmt = $this->pdo->prepare("SELECT * FROM Menus_Sistema WHERE slug = ? AND status = 1 LIMIT 1");
        $stmt->execute([$this->slug_atual]);
        $rota = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($rota) return $rota;

        // 2. BUSCA RECURSIVA (Deep Routing)
        $contagem = count($partes);
        for ($i = $contagem - 1; $i > 0; $i--) {
            $slug_base = implode('/', array_slice($partes, 0, $i));
            $parametro = implode('/', array_slice($partes, $i));

            $sql = "SELECT * FROM Menus_Sistema 
                    WHERE slug = ? AND permite_parametros = 1 AND status = 1 LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$slug_base]);
            $rota = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($rota) {
                // Sucesso! Achamos a base. Agora injetamos o ID no $_GET
                $segmentos_extras = explode('/', $parametro);
                $_GET['id'] = is_numeric($segmentos_extras[0]) ? (int)$segmentos_extras[0] : $segmentos_extras[0];
                
                // Guardamos o contexto completo para módulos complexos
                $_GET['slug_completo'] = $this->slug_atual;
                $_GET['params_url'] = $parametro; 
                
                return $rota;
            }
        }
        return null;
    }

    /**
     * Valida autorização, manutenção e trava de tempo para execução.
     * CORREÇÃO: Administradores agora possuem bypass total.
     */
    public function validarAcesso($rota): array {
        if (!$rota) {
            $this->registrarAcessoNegado($this->slug_atual ?? 'desconhecido', 404);
            return ['autorizado' => false, 'erro' => 404];
        }

        // Definimos a autoridade do usuário
        $is_admin = ($this->user_role === 'admin');

        // --- MODO EVENTO (Trava de Tempo) ---
        if (!empty($rota['liberacao_em'])) {
            $data_liberacao = strtotime($rota['liberacao_em']);
            // Se ainda não liberou e NÃO é admin, barramos.
            if (time() < $data_liberacao && !$is_admin) {
                $this->registrarAcessoNegado($rota['slug'], 403); 
                return [
                    'autorizado' => false, 
                    'erro' => 'em_breve', 
                    'liberacao' => $rota['liberacao_em']
                ];
            }
        }

        // --- MODO MANUTENÇÃO ---
        $em_manutencao = (isset($rota['manutencao_modulo']) && $rota['manutencao_modulo'] == 1);
        // Se está em manutenção e NÃO é admin, barramos.
        if ($em_manutencao && !$is_admin) {
            $this->registrarAcessoNegado($rota['slug'], 503);
            return ['autorizado' => false, 'erro' => 503];
        }

        // --- REGRAS DE PERMISSÃO ---
        switch ($rota['permissao']) {
            case 'admin':
                if (!$is_admin) {
                    $this->registrarAcessoNegado($rota['slug'], 403);
                    return ['autorizado' => false, 'erro' => 403];
                }
                break;
            case 'logado':
                if (!$this->is_logged) return ['autorizado' => false, 'erro' => 'login_required'];
                break;
        }

        // Validação Física do Arquivo
        if (!$this->arquivoExiste($rota['arquivo_destino'])) {
            error_log("ERRO CRÍTICO: Arquivo não existe: " . $rota['arquivo_destino']);
            $this->registrarAcessoNegado($rota['slug'], 404);
            return ['autorizado' => false, 'erro' => 404];
        }

        $caminho_absoluto = dirname($_SERVER['DOCUMENT_ROOT'], 1) . $rota['arquivo_destino'];
        return ['autorizado' => true, 'arquivo' => $caminho_absoluto];
    }

    /**
     * LÓGICA DE BLOQUEIO VISUAL (DICA DE OURO)
     * Determina se o item deve aparecer como bloqueado no menu.
     */
    private function verificarSeBloqueado(array $item): bool {
        // Administradores nunca veem bloqueios no menu para facilitar testes
        if ($this->user_role === 'admin') return false;

        // Se estiver em manutenção manual
        if (isset($item['manutencao_modulo']) && $item['manutencao_modulo'] == 1) return true;

        // Se houver data de liberação e for futura
        if (!empty($item['liberacao_em'])) {
            if (strtotime($item['liberacao_em']) > time()) return true;
        }

        return false;
    }

    public function obterLinksMenu(): array {
        $sql = "SELECT * FROM Menus_Sistema 
                WHERE status = 1 AND exibir_no_menu = 1 AND parent_id IS NULL 
                ORDER BY ordem ASC";
        $stmt = $this->pdo->query($sql);
        $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $menu_final = [];

        foreach ($itens as $item) {
            // Filtragem básica de permissão para visualização no menu
            if ($item['permissao'] === 'admin' && $this->user_role !== 'admin') continue;
            if ($item['permissao'] === 'logado' && !$this->is_logged) continue;
            
            // Injeta o estado de bloqueio visual (leva em conta se o user é admin)
            $item['is_bloqueado'] = $this->verificarSeBloqueado($item);
            
            $item['submenus'] = $this->obterSubmenus($item['id']);
            $menu_final[] = $item;
        }
        return $menu_final;
    }

    private function obterSubmenus(int $parent_id): array {
        $sql = "SELECT * FROM Menus_Sistema WHERE parent_id = ? AND status = 1 ORDER BY ordem ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$parent_id]);
        $submenus = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($submenus as &$sub) {
            $sub['is_bloqueado'] = $this->verificarSeBloqueado($sub);
        }
        
        return $submenus;
    }

    public function arquivoExiste(string $caminho_relativo): bool {
        if (empty($caminho_relativo)) return false;
        $caminho_absoluto = dirname($_SERVER['DOCUMENT_ROOT'], 1) . $caminho_relativo;
        return file_exists($caminho_absoluto);
    }

    public function limparLogsNegados(): bool {
        try {
            $this->pdo->exec("TRUNCATE TABLE Logs_Acessos_Negados");
            return true;
        } catch (Exception $e) { return false; }
    }

    private function registrarAcessoNegado(string $slug, int $erro) {
        try {
            $sql = "INSERT INTO Logs_Acessos_Negados (usuario_id, slug_tentado, erro_codigo, ip_endereco, user_agent) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $_SESSION['user_id'] ?? null,
                $slug, $erro,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (Exception $e) {}
    }
}