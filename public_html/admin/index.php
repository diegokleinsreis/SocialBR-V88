<?php
/**
 * admin/index.php
 * Dashboard Administrativo.
 * PAPEL: Exibir estatísticas vitais e mapa de acessos geolocalizado.
 * VERSÃO: 110.9 (Layout Sync Fix - socialbr.lol)
 */

// --- [PASSO DE ENGENHARIA: BLOQUEIO DE ACESSO DIRETO] ---
if (!defined('ACESSO_ROTEADOR')) {
    $config_file = __DIR__ . '/../../config/database.php';
    if (file_exists($config_file)) {
        require_once $config_file;
        $destino = (isset($config['base_path']) ? $config['base_path'] : '/') . "admin";
        header("Location: " . $destino);
    } else {
        header("Location: ../admin");
    }
    exit;
}

// CHAMA A GUARITA DE SEGURANÇA!
require_once __DIR__ . '/admin_auth.php'; 
// $conn e $config já estão disponíveis aqui

// --- [INÍCIO DAS QUERIES DE ESTATÍSTICAS] ---

// Query para contar denúncias de CONTEÚDO
$sql_content_count = "SELECT COUNT(id) AS pending_count FROM Denuncias WHERE status = 'pendente' AND tipo_conteudo IN ('post', 'comentario')";
$result_content_count = $conn->query($sql_content_count);
$pending_content_count = $result_content_count ? $result_content_count->fetch_assoc()['pending_count'] : 0;

// Query para contar denúncias de USUÁRIOS
$sql_user_count = "SELECT COUNT(id) AS pending_count FROM Denuncias WHERE status = 'pendente' AND tipo_conteudo = 'usuario'";
$result_user_count = $conn->query($sql_user_count);
$pending_user_count = $result_user_count ? $result_user_count->fetch_assoc()['pending_count'] : 0;

// Query para total de usuários
$sql_total_users = "SELECT COUNT(id) AS total_users FROM Usuarios";
$result_total_users = $conn->query($sql_total_users);
$total_users = $result_total_users ? $result_total_users->fetch_assoc()['total_users'] : 0;

// Query para usuários online
$sql_online_users = "SELECT COUNT(id) AS online_count FROM Usuarios WHERE ultimo_acesso >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
$result_online_users = $conn->query($sql_online_users);
$online_users_count = $result_online_users ? $result_online_users->fetch_assoc()['online_count'] : 0;

// Query para total de logins
$sql_total_logins = "SELECT COUNT(id) AS total_logins FROM Logs_Login";
$result_total_logins = $conn->query($sql_total_logins);
$total_logins = $result_total_logins ? $result_total_logins->fetch_assoc()['total_logins'] : 0;

// Query para logins de hoje
$sql_logins_hoje = "SELECT COUNT(id) AS logins_hoje FROM Logs_Login WHERE data_login >= CURDATE()";
$result_logins_hoje = $conn->query($sql_logins_hoje);
$logins_hoje = $result_logins_hoje ? $result_logins_hoje->fetch_assoc()['logins_hoje'] : 0;

// --- [LÓGICA DO POST MAIS VISTO] ---
$sql_top_post = "SELECT 
                    p.id, 
                    p.conteudo_texto, 
                    COUNT(l.id) as total_visualizacoes
                 FROM Logs_Visualizacao_Post AS l
                 JOIN Postagens AS p ON l.id_postagem = p.id
                 WHERE p.status = 'ativo'
                 GROUP BY l.id_postagem
                 ORDER BY total_visualizacoes DESC
                 LIMIT 1";
$result_top_post = $conn->query($sql_top_post);
$top_post = $result_top_post ? $result_top_post->fetch_assoc() : null; 

// --- [ESTATÍSTICAS DE CLIQUES EM LINKS] ---
$total_cliques = 0;
$cliques_hoje = 0;
try {
    $res_total = $conn->query("SELECT COUNT(id) AS total FROM Links_Cliques");
    if ($res_total) {
        $total_cliques = $res_total->fetch_assoc()['total'];
        $res_hoje = $conn->query("SELECT COUNT(id) AS total FROM Links_Cliques WHERE data_clique >= CURDATE()");
        $cliques_hoje = $res_hoje ? $res_hoje->fetch_assoc()['total'] : 0;
    }
} catch (Exception $e) { }

// --- [MAPA BASEADO EM LOGINS] ---
$ips_para_mapa = [];
$debug_msg = ""; 

try {
    $sql_ips = "SELECT DISTINCT ip_usuario FROM Logs_Login ORDER BY data_login DESC LIMIT 50";
    $res_ips = $conn->query($sql_ips);
    if ($res_ips) {
        while($row = $res_ips->fetch_assoc()) {
            $ip = $row['ip_usuario'];
            if (!empty($ip) && $ip != '::1' && $ip != '127.0.0.1') {
                $ips_para_mapa[] = $ip;
            }
        }
    } else {
        $debug_msg = "Erro SQL: " . $conn->error;
    }
} catch (Exception $e) {
    $debug_msg = "Exceção: " . $e->getMessage();
}
$json_ips = json_encode($ips_para_mapa);

// --- [FIM DA LÓGICA / INÍCIO DA RENDERIZAÇÃO] ---

// 1. O Header abre o HTML, HEAD e BODY e carrega o CSS oficial
include __DIR__ . '/templates/admin_header.php'; 
?>

<style>
    /* Estilos Específicos do Widget do Mapa */
    .geo-dashboard-container { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 15px; }
    .map-area { flex: 2; min-width: 300px; height: 400px; border: 1px solid #e9ecef; border-radius: 8px; overflow: hidden; background: #fff; }
    .foreign-list-area { flex: 1; min-width: 250px; background: #fff; border: 1px solid #e9ecef; border-radius: 8px; padding: 15px; max-height: 400px; overflow-y: auto; }
    .foreign-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f0f2f5; font-size: 0.9em; }
    .foreign-item:last-child { border-bottom: none; }
    .map-loading { display: flex; justify-content: center; align-items: center; height: 100%; color: #6c757d; font-weight: 500; flex-direction: column; text-align: center; padding: 20px; }
    .debug-error { color: #dc3545; font-size: 0.8em; margin-top: 10px; background: #ffe6e6; padding: 5px; border-radius: 4px; }
    
    /* FIX: Garante que os cards do dashboard sigam o padrão visual do painel */
    .admin-card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px; }
    .admin-card h1, .admin-card h2 { color: #0C2D54; font-weight: 700; margin-bottom: 10px; }
    .actions-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
    .action-card { display: flex; align-items: center; padding: 15px; border: 1px solid #eee; border-radius: 8px; text-decoration: none; color: #333; transition: 0.2s; }
    .action-card:hover { background: #f8f9fa; border-color: #0C2D54; }
    .action-card i { font-size: 1.5rem; margin-right: 15px; color: #0C2D54; }
    .stats-list-item { display: flex; align-items: center; padding: 12px 0; border-bottom: 1px solid #eee; }
    .stat-icon { width: 40px; font-size: 1.2rem; color: #0C2D54; }
    .stat-label { flex: 1; font-weight: 500; }
    .stat-value { font-weight: 700; font-size: 1.1rem; }
</style>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<?php 
// 2. Carrega componentes de navegação mobile
include __DIR__ . '/templates/admin_mobile_nav.php'; 

// 3. Sidebar fixa (posicionada pelo Header)
include __DIR__ . '/templates/admin_sidebar.php'; 
?>

<div class="admin-main-wrapper">
    <main class="container-fluid py-4">
        
        <div class="admin-card">
            <h1>Bem-vindo, Administrador!</h1>
            <p>Dashboard operando via Roteador Central (socialbr.lol).</p>
        </div>

        <div class="admin-card">
            <h2>Ações Rápidas</h2>
            <div class="actions-grid">
                <a href="denuncias?tab=conteudo" class="action-card">
                    <i class="fas fa-flag"></i>
                    <span><strong>Denúncias de Conteúdo</strong><br>(<?php echo $pending_content_count; ?> pendentes)</span>
                </a>
                <a href="denuncias?tab=usuarios" class="action-card">
                    <i class="fas fa-user-shield"></i>
                    <span><strong>Denúncias de Usuários</strong><br>(<?php echo $pending_user_count; ?> pendentes)</span>
                </a>
                <a href="postagens" class="action-card">
                    <i class="fas fa-file-alt"></i>
                    <span><strong>Gerenciar Postagens</strong><br>Ver todas as postagens</span>
                </a>
                <a href="comentarios" class="action-card">
                    <i class="fas fa-comments"></i>
                    <span><strong>Gerenciar Comentários</strong><br>Ver todos os comentários</span>
                </a>
            </div>
        </div>

        <div class="admin-card">
            <h2><i class="fas fa-globe-americas"></i> Mapa de Acessos ao Site (Logins)</h2>
            <p style="font-size: 0.9em; color: #666; margin-bottom: 15px;">
                Geolocalização baseada nos últimos 50 logins efetuados por usuários na plataforma.
            </p>
            <div class="geo-dashboard-container">
                <div class="map-area" id="map_div">
                    <div class="map-loading">
                        <i class="fas fa-circle-notch fa-spin"></i> &nbsp; Carregando dados...
                        <?php if (!empty($debug_msg)): ?>
                            <div class="debug-error">
                                <strong>Diagnóstico:</strong> <?php echo htmlspecialchars($debug_msg); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="foreign-list-area">
                    <h4 style="margin-top: 0; color: #0c2d54;">Acessos Internacionais</h4>
                    <div id="foreign_list_div">
                        <p style="color: #999; font-size: 0.9em;">Aguardando dados...</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <h2><i class="fas fa-chart-bar"></i> Relatórios e Estatísticas</h2>
            <div class="stats-list">
                <div class="stats-list-item">
                    <i class="fas fa-users stat-icon"></i>
                    <span class="stat-label">Total de Usuários Cadastrados</span>
                    <span class="stat-value"><?php echo $total_users; ?></span>
                </div>
                <div class="stats-list-item">
                    <i class="fas fa-signal stat-icon" style="color: #28a745;"></i>
                    <span class="stat-label">Usuários Online Agora</span>
                    <span class="stat-value" style="color: #28a745; font-weight: bold;"><?php echo $online_users_count; ?></span>
                </div>
                <div class="stats-list-item">
                    <i class="fas fa-eye stat-icon"></i>
                    <span class="stat-label">Visitas Totais (Logins)</span>
                    <span class="stat-value"><?php echo $total_logins; ?></span>
                </div>
                <div class="stats-list-item">
                    <i class="fas fa-mouse-pointer stat-icon" style="color: #007bff;"></i>
                    <span class="stat-label">Cliques em Links Externos (Hoje / Total)</span>
                    <span class="stat-value">
                        <a href="links" title="Ver Detalhes" style="color: inherit; text-decoration: none;">
                            <?php echo $cliques_hoje; ?> <small style="color: #6c757d;">/ <?php echo $total_cliques; ?></small>
                        </a>
                    </span>
                </div>
                <div class="stats-list-item">
                    <i class="fas fa-fire stat-icon" style="color: #dc3545;"></i>
                    <span class="stat-label">Post Mais Acessado</span>
                    <span class="stat-value" style="font-size: 0.9em; text-align: right; line-height: 1.2;">
                        <?php if ($top_post): ?>
                            <a href="<?php echo $config['base_path']; ?>postagem/<?php echo $top_post['id']; ?>" target="_blank" title="Ver postagem">
                                <strong><?php echo htmlspecialchars(mb_strimwidth($top_post['conteudo_texto'], 0, 30, "...")); ?></strong>
                            </a>
                            <small style="display: block; color: #6c757d;"><?php echo $top_post['total_visualizacoes']; ?> acessos</small>
                        <?php else: ?>
                            <span class="badge bg-secondary">Nenhum</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="stats-list-item">
                    <i class="fas fa-clipboard-list stat-icon"></i>
                    <span class="stat-label">Logins Efetuados Hoje</span>
                    <span class="stat-value"><?php echo $logins_hoje; ?></span>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
(function() {
    const ips = <?php echo $json_ips; ?>;
    if (ips.length === 0) {
        document.getElementById('map_div').innerHTML = '<div class="map-loading">Sem dados de login recentes.</div>';
        document.getElementById('foreign_list_div').innerHTML = '<p style="color: #999;">Nenhum acesso externo registado.</p>';
        return;
    }

    google.charts.load('current', { 'packages': ['geochart'] });
    google.charts.setOnLoadCallback(processGeoData);

    function processGeoData() {
        const brazilStates = {}; 
        const otherCountries = {}; 
        const fetchPromises = ips.map(ip => {
            return fetch(`https://ip-api.com/json/${ip}?fields=status,country,region,regionName`)
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        if (data.country === 'Brazil') {
                            const stateCode = 'BR-' + data.region; 
                            brazilStates[stateCode] = (brazilStates[stateCode] || 0) + 1;
                        } else {
                            otherCountries[data.country] = (otherCountries[data.country] || 0) + 1;
                        }
                    }
                })
                .catch(err => console.error('Erro GeoIP:', err));
        });

        Promise.all(fetchPromises).then(() => {
            drawRegionsMap(brazilStates);
            renderForeignList(otherCountries);
        });
    }

    function drawRegionsMap(statesData) {
        const dataArray = [['Estado', 'Logins']];
        if (Object.keys(statesData).length === 0) {
            dataArray.push(['Brazil', 0]);
        } else {
            for (const [state, count] of Object.entries(statesData)) {
                dataArray.push([state, count]);
            }
        }
        const data = google.visualization.arrayToDataTable(dataArray);
        const options = {
            region: 'BR', displayMode: 'regions', resolution: 'provinces', 
            colorAxis: {colors: ['#e3f2fd', '#0d47a1']},
            backgroundColor: '#fff', datalessRegionColor: '#f5f5f5', defaultColor: '#f5f5f5',
        };
        const chart = new google.visualization.GeoChart(document.getElementById('map_div'));
        chart.draw(data, options);
    }

    function renderForeignList(countriesData) {
        const container = document.getElementById('foreign_list_div');
        container.innerHTML = '';
        if (Object.keys(countriesData).length === 0) {
            container.innerHTML = '<p style="color: #999; font-size: 0.9em;">Apenas acessos do Brasil.</p>';
            return;
        }
        for (const [country, count] of Object.entries(countriesData)) {
            const item = document.createElement('div');
            item.className = 'foreign-item';
            item.innerHTML = `<span><i class="fas fa-globe"></i> ${country}</span><strong>${count}</strong>`;
            container.appendChild(item);
        }
    }
})();
</script>

</body>
</html>
<?php 
$conn->close(); 
?>