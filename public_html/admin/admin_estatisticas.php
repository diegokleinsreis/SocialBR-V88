<?php
// 1. AUTENTICAÇÃO E CONEXÃO
require_once 'admin_auth.php';
require_once __DIR__ . '/../../config/database.php';

// Define o fuso horário para o PHP (para garantir que 'NOW()' e 'CURDATE()' funcionem bem)
// E define a localidade para Português, para os nomes dos meses
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');

// ---
// 2. FUNÇÃO HELPER PARA PROCESSAR DADOS DOS GRÁFICOS
// ---

/**
 * Prepara os dados de 12 meses para um gráfico de linha.
 * Pega os resultados do SQL (que podem ter buracos) e preenche os meses
 * em falta com o valor 0, para que o gráfico não desenhe linhas erradas.
 */
function preparar_dados_grafico_12_meses($sql_result) {
    $dados_map = [];
    foreach ($sql_result as $row) {
        $dados_map[$row['mes']] = $row['total']; // Ex: '2025-11' => 15
    }

    $labels = []; // Nomes dos meses (ex: "Nov/25")
    $data_points = []; // Os valores (ex: 15)

    // Itera pelos últimos 12 meses, de trás para a frente
    for ($i = 0; $i <= 11; $i++) {
        $data = new DateTime("first day of this month - $i months");
        $mes_key = $data->format('Y-m'); // '2025-11'
        $mes_label = strftime('%b/%y', $data->getTimestamp()); // 'Nov/25'

        // Adiciona ao início dos arrays (para ficar em ordem cronológica)
        array_unshift($labels, $mes_label);
        array_unshift($data_points, $dados_map[$mes_key] ?? 0); // Usa 0 se o mês não existir nos resultados
    }

    return ['labels' => $labels, 'data' => $data_points];
}


// ---
// 3. BUSCAR OS DADOS (QUERIES SQL)
// ---

// GRÁFICO 1: Novos Cadastros (Últimos 12 meses)
$sql_cadastros = "SELECT 
                        DATE_FORMAT(data_cadastro, '%Y-%m') as mes, 
                        COUNT(id) as total 
                  FROM Usuarios 
                  WHERE data_cadastro >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) 
                  GROUP BY mes 
                  ORDER BY mes ASC";
$result_cadastros = $conn->query($sql_cadastros)->fetch_all(MYSQLI_ASSOC);
$dados_grafico_cadastros = preparar_dados_grafico_12_meses($result_cadastros);


// GRÁFICO 2: Total de Logins (Últimos 12 meses)
$sql_logins = "SELECT 
                    DATE_FORMAT(data_login, '%Y-%m') as mes, 
                    COUNT(id) as total 
               FROM Logs_Login 
               WHERE data_login >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) 
               GROUP BY mes 
               ORDER BY mes ASC";
$result_logins = $conn->query($sql_logins)->fetch_all(MYSQLI_ASSOC);
$dados_grafico_logins = preparar_dados_grafico_12_meses($result_logins);


// GRÁFICO 3: Novas Publicações (Últimos 12 meses)
$sql_postagens = "SELECT 
                        DATE_FORMAT(data_postagem, '%Y-%m') as mes, 
                        COUNT(id) as total 
                  FROM Postagens 
                  WHERE data_postagem >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) 
                  GROUP BY mes 
                  ORDER BY mes ASC";
$result_postagens = $conn->query($sql_postagens)->fetch_all(MYSQLI_ASSOC);
$dados_grafico_postagens = preparar_dados_grafico_12_meses($result_postagens);


// GRÁFICO 4: Top 10 Bairros
$sql_bairros = "SELECT 
                    b.nome, 
                    COUNT(u.id) as total 
                FROM Usuarios u
                JOIN Bairros b ON u.id_bairro = b.id
                GROUP BY u.id_bairro 
                ORDER BY total DESC 
                LIMIT 10";
$result_bairros = $conn->query($sql_bairros)->fetch_all(MYSQLI_ASSOC);

$labels_grafico_bairros = [];
$data_grafico_bairros = [];
foreach ($result_bairros as $bairro) {
    $labels_grafico_bairros[] = $bairro['nome'];
    $data_grafico_bairros[] = $bairro['total'];
}


// --- [INÍCIO DAS NOVAS QUERIES SQL] ---

// GRÁFICO 5: Novos Comentários (Últimos 12 meses)
$sql_comentarios = "SELECT 
                        DATE_FORMAT(data_comentario, '%Y-%m') as mes, 
                        COUNT(id) as total 
                    FROM Comentarios 
                    WHERE data_comentario >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) 
                    GROUP BY mes 
                    ORDER BY mes ASC";
$result_comentarios = $conn->query($sql_comentarios)->fetch_all(MYSQLI_ASSOC);
$dados_grafico_comentarios = preparar_dados_grafico_12_meses($result_comentarios);

// GRÁFICO 6: Novas Curtidas (Posts + Comentários) (Últimos 12 meses)
$sql_curtidas = "(SELECT data_curtida FROM Curtidas WHERE data_curtida >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH))
                 UNION ALL
                 (SELECT data_curtida FROM Curtidas_Comentarios WHERE data_curtida >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH))";

$sql_curtidas_agrupadas = "SELECT 
                                DATE_FORMAT(data_curtida, '%Y-%m') as mes, 
                                COUNT(*) as total 
                           FROM ($sql_curtidas) as todas_curtidas
                           GROUP BY mes 
                           ORDER BY mes ASC";
$result_curtidas = $conn->query($sql_curtidas_agrupadas)->fetch_all(MYSQLI_ASSOC);
$dados_grafico_curtidas = preparar_dados_grafico_12_meses($result_curtidas);


// GRÁFICO 7: Novas Amizades (Últimos 12 meses)
$sql_amizades = "SELECT 
                        DATE_FORMAT(data_atualizacao, '%Y-%m') as mes, 
                        COUNT(id) as total 
                 FROM Amizades 
                 WHERE status = 'aceite' AND data_atualizacao >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) 
                 GROUP BY mes 
                 ORDER BY mes ASC";
$result_amizades = $conn->query($sql_amizades)->fetch_all(MYSQLI_ASSOC);
$dados_grafico_amizades = preparar_dados_grafico_12_meses($result_amizades);


// GRÁFICO 8: Denúncias Recebidas (Últimos 12 meses)
$sql_denuncias = "SELECT 
                        DATE_FORMAT(data_denuncia, '%Y-%m') as mes, 
                        COUNT(id) as total 
                  FROM Denuncias 
                  WHERE data_denuncia >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) 
                  GROUP BY mes 
                  ORDER BY mes ASC";
$result_denuncias = $conn->query($sql_denuncias)->fetch_all(MYSQLI_ASSOC);
$dados_grafico_denuncias = preparar_dados_grafico_12_meses($result_denuncias);

// --- [FIM DAS NOVAS QUERIES SQL] ---


// Passa os dados do PHP para o JavaScript
$json_cadastros_labels = json_encode($dados_grafico_cadastros['labels']);
$json_cadastros_data = json_encode($dados_grafico_cadastros['data']);

$json_logins_labels = json_encode($dados_grafico_logins['labels']);
$json_logins_data = json_encode($dados_grafico_logins['data']);

$json_postagens_labels = json_encode($dados_grafico_postagens['labels']);
$json_postagens_data = json_encode($dados_grafico_postagens['data']);

$json_bairros_labels = json_encode($labels_grafico_bairros);
$json_bairros_data = json_encode($data_grafico_bairros);

// --- [PASSANDO NOVOS DADOS PARA O JS] ---
$json_comentarios_labels = json_encode($dados_grafico_comentarios['labels']);
$json_comentarios_data = json_encode($dados_grafico_comentarios['data']);

$json_curtidas_labels = json_encode($dados_grafico_curtidas['labels']);
$json_curtidas_data = json_encode($dados_grafico_curtidas['data']);

$json_amizades_labels = json_encode($dados_grafico_amizades['labels']);
$json_amizades_data = json_encode($dados_grafico_amizades['data']);

$json_denuncias_labels = json_encode($dados_grafico_denuncias['labels']);
$json_denuncias_data = json_encode($dados_grafico_denuncias['data']);
// --- [FIM DOS NOVOS DADOS PARA O JS] ---


$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estatísticas - Painel Admin</title>
    
    <link rel="stylesheet" href="assets/css/admin.css?v=2.3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* Estilo para garantir que os gráficos sejam responsivos */
        .chart-container {
            position: relative;
            height: 350px;
            width: 100%;
        }
        
        /* Grid para os gráficos */
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        /* Em telas menores, 1 coluna */
        @media (max-width: 1200px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <?php include 'templates/admin_header.php'; ?>
    <?php include 'templates/admin_mobile_nav.php'; ?>

    <main class="admin-main-content">
    
        <a href="index.php" class="admin-back-button"><i class="fas fa-arrow-left"></i> Voltar ao Dashboard</a>
        
        <div class="admin-card">
            <h1><i class="fas fa-chart-line"></i> Estatísticas e Gráficos</h1>
            <p>Análise de crescimento e engajamento da plataforma.</p>
        </div>

        <div class="charts-grid">
            
            <div class="admin-card">
                <h2><i class="fas fa-user-plus"></i> Novos Cadastros (Últimos 12 Meses)</h2>
                <div class="chart-container">
                    <canvas id="graficoNovosCadastros"></canvas>
                </div>
            </div>

            <div class="admin-card">
                <h2><i class="fas fa-sign-in-alt"></i> Total de Logins (Últimos 12 Meses)</h2>
                <div class="chart-container">
                    <canvas id="graficoLogins"></canvas>
                </div>
            </div>

            <div class="admin-card">
                <h2><i class="fas fa-file-signature"></i> Novas Publicações (Últimos 12 Meses)</h2>
                <div class="chart-container">
                    <canvas id="graficoNovasPublicacoes"></canvas>
                </div>
            </div>

            <div class="admin-card">
                <h2><i class="fas fa-map-marker-alt"></i> Top 10 Bairros por Usuários</h2>
                <div class="chart-container">
                    <canvas id="graficoTopBairros"></canvas>
                </div>
            </div>

            <?php // --- [INÍCIO DO NOVO HTML DOS GRÁFICOS] --- ?>
            
            <div class="admin-card">
                <h2><i class="fas fa-comments"></i> Novos Comentários (Últimos 12 Meses)</h2>
                <div class="chart-container">
                    <canvas id="graficoNovosComentarios"></canvas>
                </div>
            </div>

            <div class="admin-card">
                <h2><i class="fas fa-thumbs-up"></i> Novas Curtidas (Últimos 12 Meses)</h2>
                <div class="chart-container">
                    <canvas id="graficoNovasCurtidas"></canvas>
                </div>
            </div>

            <div class="admin-card">
                <h2><i class="fas fa-user-friends"></i> Novas Amizades (Últimos 12 Meses)</h2>
                <div class="chart-container">
                    <canvas id="graficoNovasAmizades"></canvas>
                </div>
            </div>

            <div class="admin-card">
                <h2><i class="fas fa-flag"></i> Denúncias Recebidas (Últimos 12 Meses)</h2>
                <div class="chart-container">
                    <canvas id="graficoDenuncias"></canvas>
                </div>
            </div>

            <?php // --- [FIM DO NOVO HTML DOS GRÁFICOS] --- ?>

        </div>

    </main>
    
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // Configuração Padrão para os Gráficos de Linha
        const configGraficoLinha = {
            type: 'line',
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { // Garante que só tenhamos números inteiros na escala Y
                            stepSize: 1
                        }
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                }
            }
        };

        // --- Gráfico 1: Novos Cadastros ---
        const ctxCadastros = document.getElementById('graficoNovosCadastros').getContext('2d');
        new Chart(ctxCadastros, {
            ...configGraficoLinha, // Usa a configuração padrão
            data: {
                labels: <?php echo $json_cadastros_labels; ?>,
                datasets: [{
                    label: 'Novos Cadastros',
                    data: <?php echo $json_cadastros_data; ?>,
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    fill: true,
                    tension: 0.1
                }]
            }
        });

        // --- Gráfico 2: Logins ---
        const ctxLogins = document.getElementById('graficoLogins').getContext('2d');
        new Chart(ctxLogins, {
            ...configGraficoLinha, // Usa a configuração padrão
            data: {
                labels: <?php echo $json_logins_labels; ?>,
                datasets: [{
                    label: 'Total de Logins',
                    data: <?php echo $json_logins_data; ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: true,
                    tension: 0.1
                }]
            }
        });

        // --- Gráfico 3: Novas Publicações ---
        const ctxPostagens = document.getElementById('graficoNovasPublicacoes').getContext('2d');
        new Chart(ctxPostagens, {
            ...configGraficoLinha, // Usa a configuração padrão
            data: {
                labels: <?php echo $json_postagens_labels; ?>,
                datasets: [{
                    label: 'Novas Publicações',
                    data: <?php echo $json_postagens_data; ?>,
                    borderColor: 'rgb(255, 159, 64)',
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    fill: true,
                    tension: 0.1
                }]
            }
        });

        // --- Gráfico 4: Top Bairros (Gráfico de Barras) ---
        const ctxBairros = document.getElementById('graficoTopBairros').getContext('2d');
        new Chart(ctxBairros, {
            type: 'bar', // Tipo de gráfico diferente
            data: {
                labels: <?php echo $json_bairros_labels; ?>,
                datasets: [{
                    label: 'Total de Usuários',
                    data: <?php echo $json_bairros_data; ?>,
                    backgroundColor: [ // Array de cores para as barras
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(153, 102, 255, 0.5)',
                        'rgba(255, 159, 64, 0.5)',
                        'rgba(199, 199, 199, 0.5)',
                        'rgba(83, 109, 254, 0.5)',
                        'rgba(40, 220, 158, 0.5)',
                        'rgba(240, 98, 146, 0.5)'
                    ],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y', // <-- Faz o gráfico ser de barras horizontais (melhor para listas)
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1 // Garante números inteiros
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false // Esconde a legenda, pois só há 1 dataset
                    }
                }
            }
        });

        <?php // --- [INÍCIO DO NOVO JAVASCRIPT DOS GRÁFICOS] --- ?>

        // --- Gráfico 5: Novos Comentários ---
        const ctxComentarios = document.getElementById('graficoNovosComentarios').getContext('2d');
        new Chart(ctxComentarios, {
            ...configGraficoLinha,
            data: {
                labels: <?php echo $json_comentarios_labels; ?>,
                datasets: [{
                    label: 'Novos Comentários',
                    data: <?php echo $json_comentarios_data; ?>,
                    borderColor: 'rgb(153, 102, 255)',
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    fill: true,
                    tension: 0.1
                }]
            }
        });

        // --- Gráfico 6: Novas Curtidas ---
        const ctxCurtidas = document.getElementById('graficoNovasCurtidas').getContext('2d');
        new Chart(ctxCurtidas, {
            ...configGraficoLinha,
            data: {
                labels: <?php echo $json_curtidas_labels; ?>,
                datasets: [{
                    label: 'Novas Curtidas (Posts + Comentários)',
                    data: <?php echo $json_curtidas_data; ?>,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    fill: true,
                    tension: 0.1
                }]
            }
        });

        // --- Gráfico 7: Novas Amizades ---
        const ctxAmizades = document.getElementById('graficoNovasAmizades').getContext('2d');
        new Chart(ctxAmizades, {
            ...configGraficoLinha,
            data: {
                labels: <?php echo $json_amizades_labels; ?>,
                datasets: [{
                    label: 'Novas Amizades (Aceitas)',
                    data: <?php echo $json_amizades_data; ?>,
                    borderColor: 'rgb(40, 220, 158)',
                    backgroundColor: 'rgba(40, 220, 158, 0.2)',
                    fill: true,
                    tension: 0.1
                }]
            }
        });

        // --- Gráfico 8: Denúncias ---
        const ctxDenuncias = document.getElementById('graficoDenuncias').getContext('2d');
        new Chart(ctxDenuncias, {
            ...configGraficoLinha,
            data: {
                labels: <?php echo $json_denuncias_labels; ?>,
                datasets: [{
                    label: 'Denúncias Recebidas',
                    data: <?php echo $json_denuncias_data; ?>,
                    borderColor: 'rgb(240, 98, 146)',
                    backgroundColor: 'rgba(240, 98, 146, 0.2)',
                    fill: true,
                    tension: 0.1
                }]
            }
        });
        
        <?php // --- [FIM DO NOVO JAVASCRIPT DOS GRÁFICOS] --- ?>

    });
    </script>
    
    <script>
        const BASE_PATH = '<?php echo $config['base_path']; ?>';
        const CSRF_TOKEN = '<?php echo get_csrf_token(); ?>';
    </script>
    
</body>
</html>