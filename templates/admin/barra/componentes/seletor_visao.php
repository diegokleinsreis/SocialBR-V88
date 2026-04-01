<?php
/**
 * FICHEIRO: componentes/seletor_visao.php
 * VERSÃO: 15.0 (Compact Glass & Mobile-First)
 * PAPEL: Componente Atómico de Simulação de Identidade.
 * RESPONSABILIDADE: Gerir o dropdown de troca de visão (Ver Como) com economia de espaço.
 * INTEGRIDADE: Completo e Integral.
 */

// Extração segura de dados do payload vindo da barra_principal.php
$modo_atual = $visao_data['modo_atual'] ?? 'Real';
$id_alvo    = $visao_data['id_alvo']    ?? '0';
$base_url   = $visao_data['base_acao']  ?? '#';

/**
 * Mapeamento de Modos para exibição amigável
 */
$opcoes_visao = [
    'reset'     => ['label' => 'Visão Real',  'icon' => 'fa-user-shield'],
    'dono'      => ['label' => 'Dono',        'icon' => 'fa-user-edit'],
    'amigo'     => ['label' => 'Amigo',       'icon' => 'fa-user-friends'],
    'visitante' => ['label' => 'Visitante',   'icon' => 'fa-eye'],
    'bloqueado' => ['label' => 'Bloqueado',   'icon' => 'fa-user-slash']
];
?>

<div class="admin-visao-wrapper">
    
    <span class="admin-id-badge" title="ID do Alvo: <?php echo $id_alvo; ?>">
        #<?php echo $id_alvo; ?>
    </span>

    <span class="admin-label-visao">Ver como:</span>

    <select class="admin-select-custom hud-glass-select" 
            onchange="handleVisionChange(this)"
            title="Mudar perspetiva de visualização">
        
        <?php foreach ($opcoes_visao as $slug => $info): ?>
            <?php 
                $link_final = $base_url . "?modo=" . $slug;
                $selected = (strtolower($modo_atual) === strtolower($slug) || ($slug === 'reset' && $modo_atual === 'Real')) ? 'selected' : '';
            ?>
            <option value="<?php echo $link_final; ?>" <?php echo $selected; ?>>
                <?php echo $info['label']; ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<script>
/**
 * Lógica de Redirecionamento Atómica
 */
function handleVisionChange(selectElement) {
    const targetUrl = selectElement.value;
    if (targetUrl && targetUrl !== '#') {
        // Feedback visual antes de recarregar
        selectElement.style.opacity = '0.5';
        window.location.href = targetUrl;
    }
}
</script>