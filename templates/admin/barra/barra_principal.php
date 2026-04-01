<?php
/**
 * FICHEIRO: templates/admin/barra/barra_principal.php
 * PAPEL: Orquestrador Master (Esqueleto HUD Pure)
 * VERSÃO: 23.3 (SQL Sync & Tabela Postagens Edition)
 * INTEGRIDADE: Completo e Integral - Nomes de tabelas sincronizados com klscom_social.sql.
 */

// 1. SEGURANÇA E SESSÃO
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') { return; }

// 1.5 DETECÇÃO DE AMBIENTE
if (isset($_GET['is_simulated']) || (isset($_SERVER['HTTP_SEC_FETCH_DEST']) && $_SERVER['HTTP_SEC_FETCH_DEST'] === 'iframe')) {
    return; 
}

// 2. IDENTIFICAÇÃO DO ALVO (ID DO PERFIL)
$id_alvo_perfil = $id_do_perfil_a_exibir ?? $perfil_id ?? $user['id'] ?? 0;

if ($id_alvo_perfil <= 0) {
    $path_parts = explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    foreach ($path_parts as $part) {
        if (is_numeric($part)) { $id_alvo_perfil = (int)$part; break; }
    }
}

// 3. BUSCA DE DENÚNCIAS REAIS (SINCRONIZADO COM O DB REAL)
$perfil_denuncias_count = 0;
$perfil_denuncias_lista = [];

if ($id_alvo_perfil > 0 && isset($conn)) {
    /**
     * AJUSTE DE NOMENCLATURA:
     * - Tabela: Postagens (em vez de Posts)
     * - Tabela: Comentarios (em vez de Comentarios)
     * - Status: 'pendente'
     */
    $sql_denuncias = "SELECT d.motivo, d.data_denuncia, d.tipo_conteudo 
                      FROM Denuncias d
                      LEFT JOIN Postagens p ON (d.tipo_conteudo = 'post' AND d.id_conteudo = p.id)
                      LEFT JOIN Comentarios c ON (d.tipo_conteudo = 'comentario' AND d.id_conteudo = c.id)
                      WHERE d.status = 'pendente'
                      AND (
                          (d.tipo_conteudo = 'usuario' AND d.id_conteudo = ?) OR
                          (d.tipo_conteudo = 'post' AND p.id_usuario = ?) OR
                          (d.tipo_conteudo = 'comentario' AND c.id_usuario = ?)
                      )
                      ORDER BY d.data_denuncia DESC LIMIT 20";
    
    // Tentativa segura de execução
    try {
        $stmt_denuncias = $conn->prepare($sql_denuncias);
        if ($stmt_denuncias) {
            $stmt_denuncias->bind_param("iii", $id_alvo_perfil, $id_alvo_perfil, $id_alvo_perfil);
            $stmt_denuncias->execute();
            $res_denuncias = $stmt_denuncias->get_result();
            
            if ($res_denuncias) {
                while ($row = $res_denuncias->fetch_assoc()) {
                    $perfil_denuncias_lista[] = $row;
                }
                $perfil_denuncias_count = count($perfil_denuncias_lista);
            }
            $stmt_denuncias->close();
        }
    } catch (Exception $e) {
        error_log("HUD SQL Error: " . $e->getMessage());
    }
}

// 4. PREPARAÇÃO DO PAYLOAD
$perf_data = [
    'tempo'   => (defined('PERF_INICIO')) ? number_format((microtime(true) - PERF_INICIO) * 1000, 2) : "0",
    'memoria' => number_format(memory_get_peak_usage() / 1024 / 1024, 2),
    'cpu'     => (function_exists('sys_getloadavg')) ? number_format(sys_getloadavg()[0], 2) : "N/A"
];

$visao_data = [
    'modo_atual' => $_SESSION['admin_modo_visao'] ?? 'Real',
    'id_alvo'    => $id_alvo_perfil,
    'base_acao'  => ($config['base_path'] ?? '') . "trocar_visao.php",
    'link_painel'=> ($config['base_url'] ?? '') . "admin/index.php"
];

$mod_data = [
    'num_denuncias' => $perfil_denuncias_count,
    'id_perfil'     => $id_alvo_perfil,
    'lista_motivos' => $perfil_denuncias_lista
];

// 5. ESTÉTICA DINÂMICA
$cor_barra = ($visao_data['modo_atual'] === 'Real') ? '#111111' : '#d35400';

// CARREGAMENTO DO MOTOR
$sd_motor = __DIR__ . '/componentes/super_debug/sd_motor.php';
if(file_exists($sd_motor)) { include_once $sd_motor; }
?>

<style>
    #barra-admin-master {
        box-sizing: border-box; background-color: <?php echo $cor_barra; ?>;
        color: #ffffff; width: 100%; max-width: 100vw; height: 46px;
        padding: 0 15px; position: fixed; top: 0; left: 0; z-index: 1000000;
        font-family: 'Inter', sans-serif; display: flex;
        justify-content: space-between; align-items: center;
        box-shadow: 0 4px 25px rgba(0,0,0,0.7); 
        border-bottom: 1px solid rgba(255,255,255,0.08);
        backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
    }
    body { transition: padding-top 0.3s ease; }
</style>

<div id="barra-admin-master">
    <div class="admin-atom-group">
        <?php 
            include __DIR__ . '/componentes/monitor_desempenho.php'; 
            include __DIR__ . '/componentes/super_debug.php';
            $sim_trigger = __DIR__ . '/componentes/super_debug/sd_device_sim.php';
            if(file_exists($sim_trigger)) { include $sim_trigger; }
            $sql_painel = __DIR__ . '/componentes/super_debug/sd_sql_painel.php';
            if(file_exists($sql_painel)) { include $sql_painel; }
        ?>
    </div>
    <div class="admin-atom-group">
        <?php 
            include __DIR__ . '/componentes/alerta_moderacao.php'; 
            include __DIR__ . '/componentes/seletor_visao.php'; 
            include __DIR__ . '/componentes/links_atalho.php'; 
        ?>
    </div>
</div>

<?php 
    $sim_painel = __DIR__ . '/componentes/super_debug/sd_device_sim_painel.php';
    if(file_exists($sim_painel)) { include $sim_painel; }
?>

<script>
    function sincronizarAlturaAdmin() {
        const barra = document.getElementById('barra-admin-master');
        if (barra) { document.body.style.setProperty('padding-top', barra.offsetHeight + 'px', 'important'); }
    }
    window.addEventListener('resize', sincronizarAlturaAdmin);
    document.addEventListener("DOMContentLoaded", sincronizarAlturaAdmin);
</script>