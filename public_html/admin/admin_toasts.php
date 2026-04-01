<?php
/**
 * admin/admin_toasts.php
 * PAPEL: Central de Alertas OLED (Broadcast Command Center)
 * VERSÃO: 8.1 (UX Descriptions, Target Segmentation, Icons & CTA - socialbr.lol)
 */

// --- [PASSO 1: PROTEÇÃO E AUTENTICAÇÃO] ---
require_once __DIR__ . '/admin_auth.php'; 

$mensagem_sucesso = "";
$mensagem_erro = "";

// --- [PASSO 2: LÓGICA DE PROCESSAMENTO (POST)] ---

// 2.1 - Criar Novo Alerta (Broadcast)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'criar_alerta') {
    $titulo = trim($_POST['titulo']);
    $mensagem = trim($_POST['mensagem']);
    $cor_preset = $_POST['cor_preset']; 
    $duracao = intval($_POST['duracao']); 
    $criado_por = (int)$_SESSION['user_id']; 
    $is_sticky = isset($_POST['is_sticky']) ? 1 : 0;
    
    // Lógica de Agendamento
    $data_inicio = !empty($_POST['data_inicio']) ? str_replace('T', ' ', $_POST['data_inicio']) : date('Y-m-d H:i:s');

    // Campos CTA
    $cta_texto = !empty($_POST['cta_texto']) ? trim($_POST['cta_texto']) : null;
    $cta_link = !empty($_POST['cta_link']) ? trim($_POST['cta_link']) : null;

    // Campo de Ícone
    $icone = !empty($_POST['icone']) ? trim($_POST['icone']) : null;

    // --- CAPTURA DE ALVOS (V8.0) ---
    $alvos_raw = trim($_POST['alvos_ids'] ?? '');
    $alvos_array = array_filter(array_map('intval', explode(',', $alvos_raw)));

    if (empty($titulo) || empty($mensagem)) {
        $mensagem_erro = "O título e a mensagem são obrigatórios.";
    } else {
        $data_expiracao = date('Y-m-d H:i:s', strtotime($data_inicio . " +$duracao hours"));
        
        $sql = "INSERT INTO Avisos_Sistema (titulo, mensagem, cor_preset, is_sticky, data_inicio, data_expiracao, criado_por, cta_texto, cta_link, icone) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssississs", $titulo, $mensagem, $cor_preset, $is_sticky, $data_inicio, $data_expiracao, $criado_por, $cta_texto, $cta_link, $icone);
        
        if ($stmt->execute()) {
            $id_aviso = $stmt->insert_id;

            // --- VINCULAÇÃO DE DESTINATÁRIOS (SEGMENTAÇÃO) ---
            if (!empty($alvos_array)) {
                $stmt_target = $conn->prepare("INSERT INTO Avisos_Destinatarios (id_aviso, id_usuario) VALUES (?, ?)");
                foreach ($alvos_array as $uid) {
                    $stmt_target->bind_param("ii", $id_aviso, $uid);
                    $stmt_target->execute();
                }
                $stmt_target->close();
                $mensagem_sucesso = "Alerta segmentado enviado para " . count($alvos_array) . " usuários.";
            } else {
                $mensagem_sucesso = "Alerta global disparado com sucesso!";
            }
        } else {
            $mensagem_erro = "Erro ao processar alerta: " . $conn->error;
        }
    }
}

// 2.2 - Encerrar Alerta
if (isset($_GET['delete'])) {
    $id_alerta = intval($_GET['delete']);
    $conn->query("DELETE FROM Avisos_Sistema WHERE id = $id_alerta");
    $mensagem_sucesso = "Transmissão encerrada e dados removidos.";
}

// --- [PASSO 3: BUSCA DE DADOS COM CONTADOR E ALVOS] ---
$sql_ativos = "
    SELECT s.*, 
           COUNT(DISTINCT al.id) as total_vistas,
           (SELECT COUNT(*) FROM Avisos_Destinatarios WHERE id_aviso = s.id) as total_alvos
    FROM Avisos_Sistema s 
    LEFT JOIN Avisos_Lidos al ON s.id = al.id_aviso 
    GROUP BY s.id 
    ORDER BY s.data_inicio DESC
";
$alertas_ativos = $conn->query($sql_ativos);

// --- [PASSO 4: RENDERIZAÇÃO DO LAYOUT] ---
include __DIR__ . '/templates/admin_header.php'; 
?>

<link rel="stylesheet" href="/assets/css/components/_notificacao_toast.css">

<style>
    .form-grid-toast {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: 25px;
        align-items: start;
    }
    .stat-badge {
        background: #f0f2f5; padding: 4px 10px; border-radius: 20px;
        font-size: 0.85em; font-weight: 700; color: #0C2D54;
        display: inline-flex; align-items: center; gap: 5px;
    }
    .sticky-badge {
        background: #0C2D54; color: #ffffff; font-size: 0.65em; padding: 2px 6px;
        border-radius: 4px; text-transform: uppercase; font-weight: 800;
        vertical-align: middle; margin-left: 5px;
    }
    .target-badge {
        background: #6f42c1; color: #fff; font-size: 0.65em; padding: 2px 6px;
        border-radius: 4px; font-weight: 800; margin-left: 5px;
    }
    .scheduled-badge {
        background: #DAA520; color: #fff; font-size: 0.65em; padding: 2px 6px;
        border-radius: 4px; font-weight: 800;
    }
    .switch-container {
        display: flex; align-items: center; justify-content: space-between;
        background: #f8f9fa; padding: 12px; border-radius: 8px;
        border: 1px solid #dee2e6; margin-top: 10px;
    }
    .switch { position: relative; display: inline-block; width: 46px; height: 24px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider {
        position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
        background-color: #ccc; transition: .4s; border-radius: 34px;
    }
    .slider:before {
        position: absolute; content: ""; height: 18px; width: 18px;
        left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%;
    }
    input:checked + .slider { background-color: #0C2D54; }
    input:checked + .slider:before { transform: translateX(22px); }
    .input-desc { display: block; color: #666; font-size: 0.75em; margin-top: 4px; line-height: 1.2; }

    @media (max-width: 992px) {
        .form-grid-toast { grid-template-columns: 1fr; }
        .admin-main-content { padding: 15px; }
    }
</style>

<main class="admin-main-content">
    <a href="dashboard" class="admin-back-button">
        <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
    </a>

    <div class="admin-card">
        <h1><i class="fas fa-bullhorn" style="color: #DAA520;"></i> Gestão de Alertas OLED</h1>
        <p>Centro de Comando: Segmentação de Alvos e Controle de Transmissão.</p>
    </div>

    <?php if ($mensagem_sucesso): ?>
        <div class="status-tag status-revisado" style="display: block; margin-bottom: 20px; padding: 15px; border-radius: 8px;">
            <i class="fas fa-check-circle"></i> <?php echo $mensagem_sucesso; ?>
        </div>
    <?php endif; ?>

    <?php if ($mensagem_erro): ?>
        <div class="status-tag status-ativo" style="display: block; margin-bottom: 20px; padding: 15px; background-color: #dc3545; color: #fff;">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $mensagem_erro; ?>
        </div>
    <?php endif; ?>

    <div class="form-grid-toast">
        <div class="admin-card">
            <form action="" method="POST" class="admin-form">
                <input type="hidden" name="action" value="criar_alerta">
                
                <h2><i class="fas fa-plus-circle"></i> Novo Alerta</h2>
                
                <div class="form-group">
                    <label for="titulo">Título Principal</label>
                    <input type="text" id="titulo" name="titulo" required>
                    <small class="input-desc">Texto curto em negrito que aparece no topo do aviso.</small>
                </div>

                <div class="form-group">
                    <label for="mensagem">Mensagem</label>
                    <textarea name="mensagem" id="mensagem" style="width: 100%; border-radius: 8px; border: 1px solid #ced4da; height: 60px;" required></textarea>
                    <small class="input-desc">O conteúdo principal que o usuário irá ler na notificação.</small>
                </div>

                <div class="form-group" style="background: #f4f0fa; padding: 12px; border-radius: 8px; border: 1px solid #e9ecef;">
                    <label for="alvos_ids">Segmentar Público (IDs de Usuários)</label>
                    <input type="text" id="alvos_ids" name="alvos_ids" placeholder="Ex: 1, 45, 102">
                    <small style="color: #6f42c1; font-weight: 700; font-size: 0.75em; display: block; margin-top: 5px;">
                        Deixe vazio para enviar a TODOS (Global).
                    </small>
                </div>

                <div class="form-grid" style="grid-template-columns: 1fr 1.5fr; gap: 15px; margin-bottom: 15px; background: #f0f7ff; padding: 12px; border-radius: 8px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="cta_texto">Texto Botão</label>
                        <input type="text" id="cta_texto" name="cta_texto" placeholder="Ex: Ver Agora">
                        <small class="input-desc">Rótulo do botão (opcional).</small>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="cta_link">Link Botão</label>
                        <input type="url" id="cta_link" name="cta_link" placeholder="https://...">
                        <small class="input-desc">URL de destino ao clicar no botão.</small>
                    </div>
                </div>

                <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="icone">Símbolo</label>
                        <select name="icone" id="icone">
                            <option value="">Automático</option>
                            <option value="fa-bullhorn">📢 Megafone</option>
                            <option value="fa-exclamation-triangle">⚠️ Atenção</option>
                            <option value="fa-gift">🎁 Presente</option>
                            <option value="fa-tools">🛠️ Ferramentas</option>
                            <option value="fa-dollar-sign">💰 Dinheiro</option>
                            <option value="fa-rocket">🚀 Foguete</option>
                        </select>
                        <small class="input-desc">Ícone visual do aviso.</small>
                    </div>
                    <div class="form-group">
                        <label for="data_inicio">Agendar Início</label>
                        <input type="datetime-local" id="data_inicio" name="data_inicio">
                        <small class="input-desc">Data/hora para o aviso começar.</small>
                    </div>
                </div>

                <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 0;">
                    <div class="form-group">
                        <label for="cor_preset">Estética OLED</label>
                        <select name="cor_preset" id="cor_preset">
                            <option value="info" selected>Site Blue</option>
                            <option value="gold">Gold Premium</option>
                            <option value="emergency">Emergency Red</option>
                            <option value="success">Safe Green</option>
                        </select>
                        <small class="input-desc">Esquema de cores do card.</small>
                    </div>
                    <div class="form-group">
                        <label for="duracao">Expiração</label>
                        <select name="duracao" id="duracao">
                            <option value="1">1 Hora</option>
                            <option value="24" selected>24 Horas</option>
                            <option value="168">7 Dias</option>
                        </select>
                        <small class="input-desc">Quanto tempo o aviso durará.</small>
                    </div>
                </div>

                <div class="switch-container">
                    <div style="flex: 1;">
                        <span style="font-size: 0.9em; font-weight: 600; color: #444;">Prioridade Hard (Sticky)</span>
                        <small class="input-desc" style="margin-top: 0;">O alerta não some sozinho por tempo; exige clique no "X".</small>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="is_sticky" id="is_sticky_toggle">
                        <span class="slider"></span>
                    </label>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" onclick="previewToast()" class="filter-btn" style="background-color: #6c757d; flex: 1; height: 45px; border-radius: 8px;">
                        <i class="fas fa-eye"></i> PRÉVIA
                    </button>
                    <button type="submit" class="filter-btn" style="background-color: #0C2D54; flex: 1.5; height: 45px; border-radius: 8px;">
                        <i class="fas fa-paper-plane"></i> DISPARAR
                    </button>
                </div>
            </form>
        </div>

        <div class="admin-card">
            <h2><i class="fas fa-chart-line"></i> Histórico de Transmissões</h2>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Alerta / Público</th>
                            <th style="text-align: center;">Alcance</th>
                            <th style="text-align: center;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($alertas_ativos && $alertas_ativos->num_rows > 0): ?>
                            <?php while($row = $alertas_ativos->fetch_assoc()): 
                                $agora = time();
                                $inicio = strtotime($row['data_inicio']);
                                $is_futuro = $inicio > $agora;
                            ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; color: #0C2D54;">
                                            <i class="fas <?php echo $row['icone'] ?: 'fa-bullhorn'; ?> mr-1"></i>
                                            <?php echo htmlspecialchars($row['titulo']); ?>
                                            <?php if($row['is_sticky']): ?><span class="sticky-badge">Sticky</span><?php endif; ?>
                                            <?php if($row['total_alvos'] > 0): ?><span class="target-badge">Segmentado</span><?php endif; ?>
                                        </div>
                                        <div style="font-size: 0.75em; color: #6c757d; margin-top: 3px;">
                                            <?php echo $row['total_alvos'] > 0 ? "Público: " . $row['total_alvos'] . " IDs" : "Público: Global"; ?>
                                            <?php if($is_futuro): ?> | <span style="color:#DAA520">Aguarda: <?php echo date('d/m H:i', $inicio); ?></span><?php endif; ?>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="stat-badge"><i class="fas fa-eye"></i> <?php echo (int)$row['total_vistas']; ?></span>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Encerrar alerta?')">
                                            <i class="fas fa-trash-alt" style="color: #dc3545;"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" style="text-align: center; padding: 30px; color: #999;">Nenhuma transmissão.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script src="/assets/js/componentes/ui/MotorToast.js"></script>

<script>
function previewToast() {
    const titulo = document.getElementById('titulo').value;
    const mensagem = document.getElementById('mensagem').value;
    const cor_preset = document.getElementById('cor_preset').value;
    const is_sticky = document.getElementById('is_sticky_toggle').checked;
    const cta_texto = document.getElementById('cta_texto').value;
    const cta_link = document.getElementById('cta_link').value;
    const icone = document.getElementById('icone').value;

    if (!titulo || !mensagem) {
        alert('Preencha os campos básicos para a prévia.');
        return;
    }

    if (typeof MotorToast !== 'undefined') {
        MotorToast.renderToast({
            id: 0, tipo: 'broadcast', titulo: titulo, mensagem: mensagem,
            cor_preset: cor_preset, is_sticky: is_sticky, 
            cta_texto: cta_texto, cta_link: cta_link, icone_custom: icone,
            is_admin_alert: true
        });
    }
}
</script>

<?php include __DIR__ . '/templates/admin_mobile_nav.php'; ?>
</body>
</html>