<?php
/**
 * views/grupos/ver.php
 * Orquestrador Master do Grupo.
 * PAPEL: Processar lógica de adesão, prover dados do feed e gerenciar interações AJAX.
 * VERSÃO: 5.5 (Correção de Fluxo de Upload e Compartilhamento - socialbr.lol)
 */

// 1. GARANTIA DE CONEXÃO E LÓGICA
if (!isset($conn)) {
    require_once __DIR__ . '/../../config/database.php';
}

// Carrega o motor v2.8 (Ajuste de Persistência de Convite)
require_once __DIR__ . '/../../src/GruposLogic.php';

// 2. IDENTIFICAÇÃO DO UTILIZADOR E DO GRUPO
$is_logged_in = isset($_SESSION['user_id']);
$user_id_logado = $is_logged_in ? (int)$_SESSION['user_id'] : 0;
$id_grupo = (int)($_GET['id'] ?? 0);

if ($id_grupo <= 0) {
    header("Location: " . $config['base_path'] . "404");
    exit();
}

// 3. BUSCA DE DADOS MESTRE
$grupo = GruposLogic::getGroupData($conn, $id_grupo, $user_id_logado);

if (!$grupo) {
    header("Location: " . $config['base_path'] . "404");
    exit();
}

// 4. DETERMINAÇÃO DE PERMISSÕES E ESTADOS
$is_membro   = !empty($grupo['membro_id']);
$is_dono     = ($grupo['nivel_permissao'] === 'dono');
$is_admin    = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');

// Verifica se existe um convite oficial para este usuário (Sincronizado com Motor V2.8)
$tem_convite = GruposLogic::verificarConvitePendente($conn, $id_grupo, $user_id_logado);

/**
 * BLINDAGEM DE PRIVACIDADE:
 * Só pode ver o conteúdo se for membro, se for admin ou se o grupo for público.
 */
$pode_ver = GruposLogic::podeVerConteudo($grupo) || $is_admin;

$active_tab = $_GET['tab'] ?? 'feed';
$page_title = htmlspecialchars($grupo['nome']) . " - " . ($config['site_nome'] ?? 'Social BR');
$comp_path = __DIR__ . '/componentes/';

// 5. CARREGAMENTO DE CONTEÚDO DINÂMICO
$posts = [];
if ($active_tab === 'feed' && $pode_ver) {
    $posts = GruposLogic::getPostsDoGrupo($conn, $id_grupo, $user_id_logado);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <?php include __DIR__ . '/../../templates/head_common.php'; ?>
    <style>
        .group-main-wrapper {
            max-width: 1050px !important; 
            margin: 0 auto !important;
            background: #fff !important;
            border-radius: 0 0 12px 12px !important;
            border: 1px solid #e4e6eb !important;
            border-top: none !important;
            overflow: hidden !important;
        }
        
        .group-content-area {
            padding: 30px 0 !important; 
            background-color: #f0f2f5 !important;
            min-height: 400px !important;
            width: 100% !important;
            display: flex !important;
            justify-content: center !important;
        }

        .group-header-inner {
            padding: 0 30px 20px 30px !important;
        }

        @media (max-width: 768px) {
            .group-main-wrapper { border-radius: 0 !important; border: none !important; }
            .group-header-inner { padding: 0 15px 15px 15px !important; }
        }
    </style>
</head>
<body class="bg-light">

    <?php include __DIR__ . '/../../templates/header.php'; ?>
    <?php include __DIR__ . '/../../templates/mobile_nav.php'; ?>

    <div class="main-content-area">
        <?php include __DIR__ . '/../../templates/sidebar.php'; ?>

        <main class="feed-container">
            <div class="group-main-wrapper">
                
                <?php include $comp_path . 'capa_grupo.php'; ?>
                
                <div class="group-header-inner">
                    <?php 
                    /** * A identidade do grupo usará as variáveis $is_membro e $tem_convite
                     * para decidir se mostra Participar, Membro ou Aceitar/Recusar.
                     */
                    include $comp_path . 'identidade_grupo.php'; 
                    ?>
                    <?php include $comp_path . 'menu_interno.php'; ?>
                </div>

            </div>

            <div class="group-content-area">
                <?php 
                if (!$pode_ver):
                    // Blindagem ativa: Exibe bloqueio visual se não houver permissão
                    include $comp_path . 'view_privada.php';
                else:
                    switch ($active_tab) {
                        case 'membros':
                            include $comp_path . 'lista_membros.php';
                            break;
                        case 'config':
                            include $comp_path . 'configuracoes_grupo.php';
                            break;
                        case 'solicitacoes':
                            include $comp_path . 'solicitacoes_grupo.php';
                            break;
                        case 'feed':
                        default:
                            include $comp_path . 'feed_grupo.php';
                            break;
                    }
                endif;
                ?>
            </div>
        </main>
    </div>

    <?php include $comp_path . 'modal_convite.php'; ?>

    <?php include __DIR__ . '/../../templates/footer.php'; ?>

    <script>
    /**
     * CONFIGURAÇÕES GLOBAIS PARA GRUPOS
     */
    const GROUP_CONFIG = {
        basePath: '<?php echo $config["base_path"]; ?>',
        csrfToken: '<?php echo $_SESSION["csrf_token"]; ?>',
        idGrupo: <?php echo $id_grupo; ?>
    };

    /**
     * Passo 69: Função para Copiar Link do Grupo
     */
    function compartilharGrupo(id) {
        const urlGrupo = window.location.href;
        navigator.clipboard.writeText(urlGrupo).then(() => {
            alert("Link do grupo copiado para a área de transferência!");
        }).catch(err => {
            console.error('Erro ao copiar: ', err);
            alert("Não foi possível copiar o link automaticamente.");
        });
    }

    /**
     * Passo 70: Função para Alterar Capa do Grupo
     * CORREÇÃO: Utiliza Form Dinâmico para funcionar com redirecionamento header() do PHP.
     */
    function alterarCapaGrupo() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        
        input.onchange = e => {
            const file = e.target.files[0];
            
            // Criamos um formulário real para disparar o POST e permitir o redirecionamento
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `${GROUP_CONFIG.basePath}api/grupos/upload_capa.php`;
            form.enctype = 'multipart/form-data';

            const inputId = document.createElement('input');
            inputId.type = 'hidden';
            inputId.name = 'id_grupo';
            inputId.value = GROUP_CONFIG.idGrupo;

            const inputCsrf = document.createElement('input');
            inputCsrf.type = 'hidden';
            inputCsrf.name = 'csrf_token';
            inputCsrf.value = GROUP_CONFIG.csrfToken;

            const inputFile = document.createElement('input');
            inputFile.type = 'file';
            inputFile.name = 'foto_capa';

            // Transfere o ficheiro selecionado
            const container = new DataTransfer();
            container.items.add(file);
            inputFile.files = container.files;

            form.appendChild(inputId);
            form.appendChild(inputCsrf);
            form.appendChild(inputFile);
            document.body.appendChild(form);
            
            form.submit(); // Envia e deixa a API redirecionar (Resolve erro de JSON/404)
        };
        
        input.click();
    }

    /**
     * Responde a um Convite Pendente (Aceitar ou Recusar)
     */
    function responderAoConvite(acao) {
        const formData = new FormData();
        formData.append('id_grupo', GROUP_CONFIG.idGrupo);
        formData.append('acao', acao);
        formData.append('csrf_token', GROUP_CONFIG.csrfToken);

        fetch(`${GROUP_CONFIG.basePath}api/grupos/responder_convite.php`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'sucesso') {
                if(acao === 'aceitar') {
                    alert("Bem-vindo à comunidade!");
                    location.reload();
                } else {
                    alert("Convite recusado.");
                    window.location.href = `${GROUP_CONFIG.basePath}grupos`;
                }
            } else {
                alert('Erro: ' + data.msg);
            }
        })
        .catch(err => alert("Erro na comunicação com o servidor."));
    }

    function convidarAmigos(id) {
        if (typeof abrirModalConvite === 'function') {
            abrirModalConvite();
        } else {
            console.error("Erro: O componente modal_convite.php não foi carregado corretamente.");
        }
    }

    function participarDoGrupo(id) {
        const formData = new FormData();
        formData.append('id_grupo', id);
        formData.append('csrf_token', GROUP_CONFIG.csrfToken);

        fetch(`${GROUP_CONFIG.basePath}api/grupos/participar.php`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'sucesso') {
                alert(data.acao === 'entrou' ? 'Bem-vindo ao grupo!' : 'Solicitação enviada!');
                location.reload();
            } else {
                alert('Erro: ' + data.msg);
            }
        });
    }

    function sairDoGrupo(id) {
        if (!confirm("Tem certeza que deseja sair desta comunidade?")) return;

        const formData = new FormData();
        formData.append('id_grupo', id);
        formData.append('csrf_token', GROUP_CONFIG.csrfToken);

        fetch(`${GROUP_CONFIG.basePath}api/grupos/sair.php`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'sucesso') {
                alert(data.msg);
                window.location.href = `${GROUP_CONFIG.basePath}grupos`;
            } else {
                alert('Erro: ' + data.msg);
            }
        });
    }

    function decidirSolicitacao(idSolicitacao, acao) {
        const formData = new FormData();
        formData.append('id_solicitacao', idSolicitacao);
        formData.append('id_grupo', GROUP_CONFIG.idGrupo);
        formData.append('acao', acao);
        formData.append('csrf_token', GROUP_CONFIG.csrfToken);

        fetch(`${GROUP_CONFIG.basePath}api/grupos/decidir_solicitacao.php`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'sucesso') {
                const element = document.getElementById(`pedido-${idSolicitacao}`);
                if (element) element.remove();
            } else {
                alert('Erro ao processar pedido.');
            }
        });
    }
    </script>

</body>
</html>