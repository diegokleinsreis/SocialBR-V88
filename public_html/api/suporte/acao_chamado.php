<?php
/**
 * ARQUIVO: api/suporte/acao_chamado.php
 * PAPEL: Controlador de tickets de suporte e chat de atendimento.
 * VERSÃO: 3.5 - Estrutura /midias/ e Nomenclatura Humanizada (socialbr.lol)
 */

header('Content-Type: application/json; charset=utf-8');

// 1. --- [INICIALIZAÇÃO E SEGURANÇA] ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sessão expirada.']);
    exit;
}

// Dependências de Lógica e Configuração
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/SuporteLogic.php';
require_once __DIR__ . '/../../../src/UserLogic.php';
require_once __DIR__ . '/../../../src/EmailLogic.php';

$base_path = $config['base_path'] ?? '/'; 
$user_id = $_SESSION['user_id'];
$acao = $_GET['acao'] ?? '';
$response = ['success' => false, 'message' => 'Ação inválida.'];

/**
 * FUNÇÃO AUXILIAR: Sanitizar nomes para ficheiros
 */
function sanitizarParaFicheiro($string) {
    $string = mb_strtolower($string, 'UTF-8');
    $acentos = [
        'a' => ['á','à','â','ã','ä'], 'e' => ['é','è','ê','ë'],
        'i' => ['í','ì','î','ï'], 'o' => ['ó','ò','ô','õ','ö'],
        'u' => ['ú','ù','û','ü'], 'c' => ['ç'], 'n' => ['ñ']
    ];
    foreach ($acentos as $letra => $padrao) {
        $string = str_replace($padrao, $letra, $string);
    }
    $string = preg_replace('/[^a-z0-9\-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * FUNÇÃO AUXILIAR: Processar Upload de Suporte (Alta Qualidade)
 * Nota: Mantém extensão original conforme solicitado.
 */
function processarUploadSuporte($file, $uid, $nome_limpo) {
    $diretorio = __DIR__ . '/../../midias/suporte/fotos/';
    if (!file_exists($diretorio)) {
        mkdir($diretorio, 0755, true);
    }
    
    $extensao = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $data_hora = date('Y-m-d_H-i-s');
    $tipo = "suporte";

    // PADRÃO: ID_Nome_Data_Hora_Tipo.ext
    $novo_nome = "{$uid}_{$nome_limpo}_{$data_hora}_{$tipo}.{$extensao}";
    $caminho_final = $diretorio . $novo_nome;

    if (move_uploaded_file($file['tmp_name'], $caminho_final)) {
        return 'midias/suporte/fotos/' . $novo_nome;
    }
    return null;
}

// 2. --- [PROCESSAMENTO DE AÇÕES] ---
switch ($acao) {
    
    case 'get_mensagens':
        $chamado_id = (int)($_GET['chamado_id'] ?? 0);
        if ($chamado_id <= 0) break;

        $chamado = SuporteLogic::getDetalhesChamado($conn, $chamado_id);
        if (!$chamado || ($chamado['user_id'] != $user_id && $_SESSION['user_role'] !== 'admin')) {
            $response['message'] = 'Acesso negado.';
            break;
        }

        $mensagens = SuporteLogic::getMensagensChamado($conn, $chamado_id);
        
        ob_start();
        foreach ($mensagens as $msg) {
            $sou_eu = ($msg['remetente_tipo'] === 'admin' && $_SESSION['user_role'] === 'admin') || 
                      ($msg['remetente_tipo'] === 'usuario' && $_SESSION['user_role'] !== 'admin');
            
            $alinhamento = $sou_eu ? 'flex-end' : 'flex-start';
            $cor_fundo = $sou_eu ? '#0C2D54' : '#ffffff';
            $cor_texto = $sou_eu ? '#ffffff' : '#1c1e21';
            $radius = $sou_eu ? '14px 14px 2px 14px' : '14px 14px 14px 2px';
            $borda = $sou_eu ? 'none' : '1px solid #dddfe2';
            ?>
            <div style="display: flex; flex-direction: column; align-items: <?php echo $alinhamento; ?>; max-width: 85%; align-self: <?php echo $alinhamento; ?>; margin-bottom: 10px;">
                <div style="background: <?php echo $cor_fundo; ?>; color: <?php echo $cor_texto; ?>; padding: 10px 14px; border-radius: <?php echo $radius; ?>; box-shadow: 0 1px 2px rgba(0,0,0,0.08); border: <?php echo $borda; ?>;">
                    <p style="margin: 0; white-space: pre-wrap; font-size: 0.9rem; line-height: 1.4;"><?php echo htmlspecialchars($msg['mensagem']); ?></p>
                    <?php if (!empty($msg['foto_url'])): ?>
                        <div style="margin-top: 8px;">
                            <img src="<?php echo $base_path . $msg['foto_url']; ?>" style="max-width: 100%; border-radius: 6px; cursor: pointer;">
                        </div>
                    <?php endif; ?>
                </div>
                <span style="font-size: 0.65rem; color: #8e949e; margin-top: 4px;">
                    <?php echo $sou_eu ? 'Enviado' : ($msg['remetente_tipo'] === 'admin' ? 'Suporte' : 'Utilizador'); ?> • <?php echo date('H:i', strtotime($msg['data_envio'])); ?>
                </span>
            </div>
            <?php
        }
        $response = ['success' => true, 'html' => ob_get_clean()];
        break;

    case 'criar':
        $assunto = trim($_POST['assunto'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $mensagem = trim($_POST['mensagem'] ?? '');
        $diagnostico = ['url' => $_POST['diag_url'] ?? 'N/A', 'browser' => $_POST['diag_browser'] ?? 'N/A', 'res' => $_POST['diag_res'] ?? 'N/A'];

        if (empty($assunto) || empty($mensagem)) {
            $response['message'] = 'Campos obrigatórios ausentes.';
            break;
        }

        // Obtém nome para nomenclatura do arquivo
        $userData = UserLogic::getUserDataForSettings($conn, $user_id);
        $nome_limpo = sanitizarParaFicheiro($userData['nome'] ?? 'utilizador');

        $foto_url = null;
        if (isset($_FILES['foto_suporte']) && $_FILES['foto_suporte']['error'] === UPLOAD_ERR_OK) {
            $foto_url = processarUploadSuporte($_FILES['foto_suporte'], $user_id, $nome_limpo);
        }

        $chamado_id = SuporteLogic::criarChamado($conn, $user_id, $assunto, $categoria, $mensagem, $foto_url, $diagnostico);

        if ($chamado_id) {
            try {
                $emailService = new EmailLogic($pdo);
                $emailService->enviarAlertaNovoChamado($userData['nome'], $categoria, $assunto, $mensagem);
            } catch (Exception $e) { error_log("E-mail suporte falhou: " . $e->getMessage()); }

            $response = ['success' => true, 'message' => 'Chamado aberto!', 'chamado_id' => $chamado_id];
        }
        break;

    case 'responder':
        $chamado_id = (int)($_POST['chamado_id'] ?? 0);
        $mensagem = trim($_POST['mensagem'] ?? '');
        $tipo = ($_SESSION['user_role'] === 'admin') ? 'admin' : 'usuario';

        if ($chamado_id <= 0 || empty($mensagem)) break;

        $check = SuporteLogic::getDetalhesChamado($conn, $chamado_id);
        if (!$check || ($check['user_id'] != $user_id && $_SESSION['user_role'] !== 'admin')) {
            $response['message'] = 'Acesso negado.';
            break;
        }

        $userData = UserLogic::getUserDataForSettings($conn, $user_id);
        $nome_limpo = sanitizarParaFicheiro($userData['nome'] ?? 'utilizador');

        $foto_url = null;
        if (isset($_FILES['foto_suporte']) && $_FILES['foto_suporte']['error'] === UPLOAD_ERR_OK) {
            $foto_url = processarUploadSuporte($_FILES['foto_suporte'], $user_id, $nome_limpo);
        }

        if (SuporteLogic::adicionarResposta($conn, $chamado_id, $tipo, $mensagem, $foto_url)) {
            $response = ['success' => true, 'message' => 'Resposta enviada!'];
        }
        break;

    case 'mudar_status':
        if ($_SESSION['user_role'] !== 'admin') break;
        $chamado_id = (int)($_POST['chamado_id'] ?? 0);
        $novo_status = $_POST['status'] ?? '';

        if ($chamado_id > 0 && SuporteLogic::atualizarStatus($conn, $chamado_id, $novo_status)) {
            $response = ['success' => true, 'message' => 'Status atualizado.'];
        }
        break;
}

echo json_encode($response);