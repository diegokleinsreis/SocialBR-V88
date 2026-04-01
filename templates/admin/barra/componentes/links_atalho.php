<?php
/**
 * FICHEIRO: componentes/links_atalho.php
 * VERSÃO: 14.0 (Compact Ready - Style Clean)
 * PAPEL: Componente Atómico de Atalhos de Sistema.
 * RESPONSABILIDADE: Providenciar acesso ao Painel e reset de simulação.
 * DADOS: Recebe o array $visao_data do orquestrador.
 */

// Extração segura de dados do payload
$modo_atual   = $visao_data['modo_atual']  ?? 'Real';
$link_painel  = $visao_data['link_painel'] ?? '#';
$base_url_act = $visao_data['base_acao']   ?? '#';
?>

<div class="admin-links-group">
    
    <a href="<?php echo $link_painel; ?>" 
       target="_blank" 
       class="btn-admin-atalho btn-painel-link"
       title="Abrir o Painel de Administração em nova aba">
        <i class="fas fa-shield-alt"></i> 
        <span class="btn-label">Painel Admin</span>
    </a>

    <?php if ($modo_atual !== 'Real'): ?>
        <a href="<?php echo $base_url_act; ?>?modo=reset" 
           class="btn-admin-atalho btn-sair-simulacao"
           title="Sair do modo <?php echo $modo_atual; ?> e voltar para a Visão Real">
            <i class="fas fa-times-circle"></i> 
            <span class="btn-label">Sair do Modo</span>
        </a>
    <?php endif; ?>

</div>