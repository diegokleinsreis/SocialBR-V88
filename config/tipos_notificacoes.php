<?php
/**
 * config/tipos_notificacoes.php
 * * PAPEL: Centralizar todos os tipos de notificações do sistema social.
 * OBJETIVO: Evitar nomes duplicados e garantir que APIs e Banco de Dados usem o mesmo padrão.
 * PADRÃO ADOTADO: acao_objeto (ex: curtida_post)
 */

// --- 1. INTERAÇÕES SOCIAIS (POSTS E COMENTÁRIOS) ---
define('NOTIF_CURTIDA_POST',          'curtida_post');          // Já codado (estava como 'curtida')
define('NOTIF_COMENTARIO_POST',       'comentario_post');       // Já codado (estava como 'comentario')
define('NOTIF_COMPARTILHAMENTO_POST', 'compartilhamento_post'); // Já codado (estava como 'compartilhar')
define('NOTIF_CURTIDA_COMENTARIO',    'curtida_comentario');    // Novo / No SQL

// --- 2. RELACIONAMENTOS (AMIZADE) ---
define('NOTIF_PEDIDO_AMIZADE',        'pedido_amizade');        // Já codado
define('NOTIF_AMIZADE_ACEITA',        'amizade_aceita');        // Já codado

// --- 3. GRUPOS DA COMUNIDADE ---
define('NOTIF_CONVITE_GRUPO',         'convite_grupo');         // Já codado
define('NOTIF_SOLICITACAO_GRUPO',      'solicitacao_grupo');      // Já codado (pedido para entrar)
define('NOTIF_ACEITE_SOLICITACAO',    'aceite_solicitacao_grupo'); // Já codado (aprovado pelo dono)
define('NOTIF_ACEITE_CONVITE_GRUPO',  'aceite_convite_grupo');  // Já codado (amigo aceitou convite)
define('NOTIF_PROMOCAO_MODERADOR',    'promocao_moderador');    // Já codado
define('NOTIF_REBAIXAMENTO_MEMBRO',   'rebaixamento_membro');   // Novo / Em breve
define('NOTIF_TRANSFERENCIA_DONO',    'transferencia_dono');    // Novo / Em breve
define('NOTIF_EXPULSAO_GRUPO',        'expulsao_grupo');        // Já codado

// --- 4. CHAT E MENSAGENS ---
define('NOTIF_MENSAGEM_PRIVADA',      'mensagem');              // Já codado
define('NOTIF_CONVITE_CHAT_GRUPO',    'convite_chat_grupo');    // Novo / Já codado

// --- 5. MARKETPLACE E ENQUETES ---
define('NOTIF_INTERESSE_MKT',         'interesse_mkt');         // Já codado
define('NOTIF_VOTO_ENQUETE',          'voto_enquete');          // Já codado

// --- 6. SISTEMA E BROADCAST ---
define('NOTIF_BROADCAST_GERAL',       'broadcast');             // No SQL / Alertas do sistema