<?php
/**
 * admin/roteador.php
 * Roteador Administrativo Centralizado.
 * PAPEL: Validar segurança e carregar os módulos de gestão.
 * VERSÃO: 3.2 (Integração do Monitor de Erros Sentinela - socialbr.lol)
 */

if (!defined('ACESSO_ROTEADOR')) {
    define('ACESSO_ROTEADOR', true);
}

// 1. GUARITA DE SEGURANÇA (MiddleWare)
require_once __DIR__ . '/admin_auth.php';

// 2. EXTRAÇÃO DA SUB-ROTA
$sub_route = $parts[1] ?? 'dashboard';

// 3. MAPEAMENTO DE MÓDULOS ADMIN
switch ($sub_route) {
    
    // --- [NOVA CENTRAL DE MENUS E ROTAS] ---
    case 'menus-rotas':
        $arquivo_admin = __DIR__ . '/admin_menus_rotas.php';
        break;

    case 'notificacoes':
    case 'admin_toasts':
        $arquivo_admin = __DIR__ . '/admin_toasts.php';
        break;

    case 'usuarios':
        $arquivo_admin = __DIR__ . '/admin_usuarios.php';
        break;

    case 'editar_usuario':
        if (isset($parts[2])) $_GET['id'] = (int)$parts[2];
        $arquivo_admin = __DIR__ . '/admin_editar_usuario.php';
        break;

    case 'postagens':
        $arquivo_admin = __DIR__ . '/admin_postagens.php';
        break;

    case 'comentarios':
        $arquivo_admin = __DIR__ . '/admin_comentarios.php';
        break;

    case 'denuncias':
        $arquivo_admin = __DIR__ . '/admin_denuncias.php';
        break;

    case 'chat':
        $arquivo_admin = __DIR__ . '/admin_chat.php';
        break;

    case 'marketplace':
        $arquivo_admin = __DIR__ . '/admin_marketplace.php';
        break;

    case 'configuracoes':
        $arquivo_admin = __DIR__ . '/admin_configuracoes.php';
        break;

    case 'estatisticas':
        $arquivo_admin = __DIR__ . '/admin_estatisticas.php';
        break;

    case 'anotacoes':
        // Nova rota para o sistema de anotações administrativas
        $arquivo_admin = __DIR__ . '/admin_anotacoes.php';
        break;

    case 'grupos':
        // Módulo de Gestão de Grupos
        $arquivo_admin = __DIR__ . '/admin_grupos.php';
        break;

    case 'historicos':
        $arquivo_admin = __DIR__ . '/admin_historicos.php';
        break;

    case 'links':
        $arquivo_admin = __DIR__ . '/admin_links.php';
        break;

    case 'logs':
        // Módulo de Auditoria e Logs Administrativos
        $arquivo_admin = __DIR__ . '/admin_logs.php';
        break;

    case 'erros-sistema':
        // NOVO: Monitoramento de Erros Globais (Sentinela)
        $arquivo_admin = __DIR__ . '/admin_erros.php';
        break;

    case 'suporte':
        // NOVO: Módulo de Gestão de Chamados e Atendimento (V1.0)
        if (isset($parts[2])) $_GET['id'] = (int)$parts[2];
        $arquivo_admin = __DIR__ . '/admin_suporte.php';
        break;

    // --- MÓDULO DE INTELIGÊNCIA DE BUSCA ---
    case 'busca':
        $arquivo_admin = __DIR__ . '/admin_busca.php';
        break;

    case 'Palavras_Proibidas':
        // CORREÇÃO: Rota atualizada para coincidir com o novo nome da tabela e links
        $arquivo_admin = __DIR__ . '/lista_negra.php';
        break;

    case 'busca_sinonimos':
        // Nova rota para o dicionário de sinônimos
        $arquivo_admin = __DIR__ . '/sinonimos.php';
        break;

    case 'dashboard':
    default:
        $arquivo_admin = __DIR__ . '/index.php';
        break;
}

// 4. CARREGAMENTO DO MÓDULO FINAL
if (file_exists($arquivo_admin)) {
    require_once $arquivo_admin;
} else {
    http_response_code(404);
    echo "<div style='padding:40px; font-family:sans-serif; text-align:center;'>
            <h1 style='color:#0C2D54;'>Módulo Não Localizado</h1>
            <p>O componente <strong>" . basename($arquivo_admin) . "</strong> ainda não foi implementado.</p>
            <a href='" . (isset($config['base_path']) ? $config['base_path'] : '/') . "admin' style='color:#0C2D54; font-weight:bold;'>Voltar ao Dashboard</a>
          </div>";
}