<?php
/**
 * FICHEIRO: templates/admin/barra_ver_como.php
 * OBJETIVO: Barra Multi-Admin com Diagnóstico de Layout e Monitor de Denúncias.
 * VERSÃO: 4.0 (Debug CSS & Contador de Denúncias)
 */

// 1. SEGURANÇA MULTI-ADMIN
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    return; 
}

// 2. CÁLCULO DE MÉTRICAS DE PERFORMANCE
$tempo_execucao = 0;
if (defined('PERF_INICIO')) {
    $tempo_execucao = number_format((microtime(true) - PERF_INICIO) * 1000, 2);
}

// Consumo de Memória RAM
$memoria_pico = number_format(memory_get_peak_usage() / 1024 / 1024, 2);

// Carga do Servidor (CPU Load)
$carga_cpu = "N/A";
if (function_exists('sys_getloadavg')) {
    $load = sys_getloadavg();
    $carga_cpu = number_format($load[0], 2);
}

// 3. CONFIGURAÇÕES DE INTERFACE E MODERAÇÃO
$modo_atual = $_SESSION['admin_modo_visao'] ?? 'Real';
$cor_fundo = ($modo_atual === 'Real') ? '#1a1a1a' : '#d35400'; 

// Identificação do Perfil Alvo
$id_alvo = (isset($id_do_perfil_a_exibir)) ? $id_do_perfil_a_exibir : 'N/A';

// Contador de Denúncias (Variável vinda do orquestrador perfil.php)
$num_denuncias = $perfil_denuncias_count ?? 0;
$alerta_denuncia = ($num_denuncias > 0) ? 'background: #c0392b; color: white;' : '';

$base_acao = "/~klscom/trocar_visao.php";
$link_admin_painel = "https://socialbr.lol/~klscom/admin/index.php";
?>

<style>
    /* RESET DE BOX-SIZING E LAYOUT */
    #barra-admin-simulacao {
        box-sizing: border-box;
        background-color: <?php echo $cor_fundo; ?>;
        color: #ffffff;
        width: 100%;
        max-width: 100vw;
        min-height: 45px;
        padding: 5px 20px;
        position: fixed; 
        top: 0;
        left: 0;
        z-index: 10000; 
        font-family: 'Segoe UI', Tahoma, sans-serif;
        box-shadow: 0 4px 12px rgba(0,0,0,0.5);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .admin-secao-flex { display: flex; align-items: center; gap: 10px; }

    /* ESTILO DAS MÉTRICAS E TAGS */
    .metrica-box {
        font-size: 10px;
        color: #ecf0f1;
        background: rgba(0,0,0,0.3);
        padding: 2px 7px;
        border-radius: 4px;
        border: 1px solid rgba(255,255,255,0.1);
        white-space: nowrap;
    }
    .metrica-box span { color: #2ecc71; font-weight: bold; }

    .tag-alerta-mod {
        font-size: 11px;
        font-weight: bold;
        background: rgba(255,255,255,0.1);
        padding: 3px 8px;
        border-radius: 3px;
        border: 1px solid rgba(255,255,255,0.1);
        <?php echo $alerta_denuncia; ?>
    }

    /* BOTÕES DE COMANDO E DIAGNÓSTICO */
    .btn-admin-tool {
        color: #fff;
        text-decoration: none;
        background: rgba(255,255,255,0.1);
        padding: 5px 10px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
        transition: 0.2s;
        border: 1px solid rgba(255,255,255,0.1);
        cursor: pointer;
    }
    .btn-admin-tool:hover { background: rgba(255,255,255,0.25); }
    .btn-debug-active { background: #2ecc71 !important; border-color: #27ae60; }
    .btn-admin-blue { background: #2980b9; border: none; }
    .btn-admin-red { background: #c0392b; border: none; }

    /* CLASSE DE DEBUG CSS */
    .debug-layout-mode * { 
        outline: 1px solid rgba(255, 0, 0, 0.3) !important; 
    }

    @media (max-width: 992px) {
        #barra-admin-simulacao { justify-content: center; padding: 10px; }
        .admin-secao-flex { width: 100%; justify-content: center; }
    }
</style>

<div id="barra-admin-simulacao">
    <div class="admin-secao-flex">
        <div class="metrica-box" title="Tempo de Resposta PHP">PHP: <span><?php echo $tempo_execucao; ?>ms</span></div>
        <div class="metrica-box" title="Consumo de RAM">RAM: <span><?php echo $memoria_pico; ?>MB</span></div>
        
        <div class="tag-alerta-mod" title="Total de denúncias contra este usuário">
            <i class="fas fa-exclamation-triangle"></i> Denúncias: <?php echo $num_denuncias; ?>
        </div>

        <button onclick="toggleDebugCSS()" id="btn-debug-layout" class="btn-admin-tool">
            <i class="fas fa-drafting-compass"></i> Debug CSS
        </button>
    </div>

    <div class="admin-secao-flex">
        <span class="metrica-box">ID #<?php echo $id_alvo; ?> | <strong><?php echo $modo_atual; ?></strong></span>
        
        <a href="<?php echo $base_acao; ?>?modo=dono" class="btn-admin-tool">Dono</a>
        <a href="<?php echo $base_acao; ?>?modo=amigo" class="btn-admin-tool">Amigo</a>
        <a href="<?php echo $base_acao; ?>?modo=visitante" class="btn-admin-tool">Visitante</a>
        <a href="<?php echo $base_acao; ?>?modo=bloqueado" class="btn-admin-tool">Bloqueado</a>
        
        <a href="<?php echo $link_admin_painel; ?>" target="_blank" class="btn-admin-tool btn-admin-blue">Painel</a>

        <?php if ($modo_atual !== 'Real'): ?>
            <a href="<?php echo $base_acao; ?>?modo=reset" class="btn-admin-tool btn-admin-red">Sair</a>
        <?php endif; ?>
    </div>
</div>

<script>
    /**
     * Função para alternar o contorno visual (outline) de todos os elementos
     * Ajuda a identificar problemas de alinhamento e transbordo lateral.
     */
    function toggleDebugCSS() {
        const body = document.body;
        const btn = document.getElementById('btn-debug-layout');
        body.classList.toggle('debug-layout-mode');
        
        if (body.classList.contains('debug-layout-mode')) {
            btn.classList.add('btn-debug-active');
        } else {
            btn.classList.remove('btn-debug-active');
        }
    }

    /**
     * Sincroniza o topo do site para não ficar por baixo da barra.
     */
    function sincronizarAltura() {
        const barra = document.getElementById('barra-admin-simulacao');
        if (barra) {
            document.body.style.paddingTop = barra.offsetHeight + 'px';
        }
    }
    window.addEventListener('resize', sincronizarAltura);
    document.addEventListener("DOMContentLoaded", sincronizarAltura);
</script>