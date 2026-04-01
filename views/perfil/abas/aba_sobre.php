<?php
/**
 * views/perfil/abas/aba_sobre.php
 * Componente: Aba de Informações Detalhadas (Sobre).
 * PAPEL: Exibir dados biográficos, localização e metadados do utilizador.
 * VERSÃO: V1.1 (Estilos encapsulados em tag STYLE)
 */

// Variáveis recebidas do orquestrador (perfil.php):
// $perfil_data, $id_do_perfil_a_exibir, $id_usuario_logado, $config
?>

<style>
    /* Estilos da Aba Sobre (Baseados no _profile.css e perfil.php) */
    .profile-details-wrapper {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .profile-details-card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        padding: 20px;
        box-sizing: border-box;
    }

    .profile-details-card h3 {
        margin: 0 0 15px 0;
        padding-bottom: 15px;
        border-bottom: 1px solid #e4e6eb;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.2rem;
        color: #050505;
    }

    .profile-details-card h3 i {
        color: #0c2d54;
    }

    /* Itens de Informação */
    .info-item {
        display: flex;
        align-items: center;
        padding: 15px 5px;
        border-bottom: 1px solid #f0f2f5;
        font-size: 0.95em;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-item i {
        color: #8a96a3;
        width: 30px;
        margin-right: 15px;
        text-align: center;
        font-size: 1.2em;
    }

    .info-item label {
        font-weight: 600;
        color: #606770;
        flex-basis: 180px;
        flex-shrink: 0;
    }

    .info-item span {
        color: #050505;
        word-break: break-word;
    }

    /* Biografia na Aba Sobre */
    .aba-bio-text {
        font-size: 1em;
        color: #333;
        line-height: 1.6;
        padding: 10px 5px;
    }

    /* Botão Editar (Apenas para o dono) */
    .edit-profile-action {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 10px;
    }

    .btn-edit-inline {
        background-color: #e4e6eb;
        color: #050505;
        padding: 8px 16px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85em;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background 0.2s;
    }

    .btn-edit-inline:hover {
        background-color: #d8dbde;
    }

    /* Ajustes Mobile */
    @media (max-width: 768px) {
        .info-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }
        .info-item label {
            flex-basis: auto;
        }
        .info-item i {
            display: none; /* Simplifica no mobile */
        }
    }
</style>

<div class="profile-details-wrapper">

    <?php if ($id_do_perfil_a_exibir == $id_usuario_logado): ?>
        <div class="edit-profile-action">
            <a href="<?php echo $config['base_path']; ?>configurar_perfil" class="btn-edit-inline">
                <i class="fas fa-edit"></i> Editar Informações
            </a>
        </div>
    <?php endif; ?>

    <?php if (!empty($perfil_data['biografia'])): ?>
        <div class="profile-details-card">
            <h3><i class="fas fa-info-circle"></i> Biografia</h3>
            <p class="aba-bio-text">
                <?php echo nl2br(htmlspecialchars($perfil_data['biografia'])); ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="profile-details-card">
        <h3><i class="fas fa-id-card"></i> Informações de <?php echo htmlspecialchars($perfil_data['nome']); ?></h3>
        
        <div class="info-item">
            <i class="fas fa-user"></i>
            <label>Nome Completo</label>
            <span><?php echo htmlspecialchars($perfil_data['nome'] . ' ' . $perfil_data['sobrenome']); ?></span>
        </div>

        <div class="info-item">
            <i class="fas fa-at"></i>
            <label>Nome de Usuário</label>
            <span>@<?php echo htmlspecialchars($perfil_data['nome_de_usuario']); ?></span>
        </div>

        <?php if ($id_do_perfil_a_exibir == $id_usuario_logado || $sao_amigos): ?>
            <div class="info-item">
                <i class="fas fa-envelope"></i>
                <label>E-mail</label>
                <span><?php echo htmlspecialchars($perfil_data['email']); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($perfil_data['nome_bairro'])): ?>
            <div class="info-item">
                <i class="fas fa-map-marker-alt"></i>
                <label>Localização</label>
                <span>
                    <?php echo htmlspecialchars($perfil_data['nome_bairro'] . ', ' . $perfil_data['nome_cidade'] . ' - ' . $perfil_data['sigla_estado']); ?>
                </span>
            </div>
        <?php endif; ?>

        <div class="info-item">
            <i class="fas fa-birthday-cake"></i>
            <label>Data de Nascimento</label>
            <span><?php echo date("d/m/Y", strtotime($perfil_data['data_nascimento'])); ?></span>
        </div>

        <div class="info-item">
            <i class="fas fa-calendar-alt"></i>
            <label>Membro desde</label>
            <span><?php echo date("d/m/Y", strtotime($perfil_data['data_cadastro'])); ?></span>
        </div>
    </div>
</div>