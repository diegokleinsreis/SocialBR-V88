<?php
/**
 * views/perfil/informacoes_topo.php
 * Componente: Informações de Identidade (Texto).
 * PAPEL: Exibir o Nome Completo, @username, Bio e Status de Atividade.
 * VERSÃO: V60.7 - Purificação Estrutural (socialbr.lol)
 */

// Preparação de dados seguros vindo do orquestrador perfil.php
$nome_completo = htmlspecialchars(($perfil_data['nome'] ?? '') . ' ' . ($perfil_data['sobrenome'] ?? ''));
$nome_de_usuario = htmlspecialchars($perfil_data['nome_de_usuario'] ?? '');
$biografia = $perfil_data['biografia'] ?? '';
$is_online = isset($perfil_data['is_online']) && $perfil_data['is_online'] == 1;
?>

<div class="profile-header-info">
    <h1 title="<?php echo $nome_completo; ?>">
        <?php echo $nome_completo; ?>
        
        <?php if ($is_online): ?>
            <span class="status-dot-info" title="Online agora"></span>
        <?php endif; ?>

        <?php if (!empty($perfil_data['verificado'])): ?>
            <i class="fas fa-check-circle profile-verified-badge" title="Conta Verificada"></i>
        <?php endif; ?>
    </h1>

    <span class="profile-username">@<?php echo $nome_de_usuario; ?></span>

    <?php if (!empty($biografia)): ?>
        <div class="profile-bio-container">
            <p class="profile-bio-text">
                <?php echo nl2br(htmlspecialchars($biografia)); ?>
            </p>
        </div>
    <?php endif; ?>
</div>