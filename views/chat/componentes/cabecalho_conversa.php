<?php
/**
 * views/chat/componentes/cabecalho_conversa.php
 * Sub-componente Atómico: Delegador de Cabeçalho.
 * PAPEL: Orquestrar a exibição do cabeçalho correto (Privado vs Grupo).
 * VERSÃO: V61.1 (Estabilidade de Variáveis Globais - socialbr.lol)
 */

/**
 * 1. PROTEÇÃO E CONTEXTO:
 * O componente depende da variável $tipo_conversa definida no orquestrador principal.
 */
if (!isset($tipo_conversa)) {
    // Fallback de segurança para garantir o funcionamento em caso de carregamento isolado
    $tipo_conversa = $conversa['tipo'] ?? 'privada';
}

/**
 * 2. GUARDA DE ESTABILIDADE (V61.1):
 * Garante que a identidade visual oficial esteja disponível para os sub-componentes.
 * Isso elimina os erros de PHP Warning que interrompem o fluxo do chat.
 */
if (!isset($cor_padrao)) {
    $cor_padrao = "#0C2D54"; 
}

/**
 * 3. ROTEAMENTO MODULAR:
 * Encaminha o carregamento para os componentes especializados nas subpastas.
 *
 */
if ($tipo_conversa === 'grupo') {
    // Inclui a interface de gestão e identidade do grupo
    include __DIR__ . '/grupos/cabecalho_grupo.php';
} else {
    // Inclui a interface 1x1 com lógica de status real e bloqueios
    include __DIR__ . '/privado/cabecalho_privado.php';
}