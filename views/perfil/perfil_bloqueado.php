<?php
/**
 * views/perfil/perfil_bloqueado.php
 * Componente: Aviso de Utilizador Bloqueado.
 * PAPEL: Informar que o utilizador está bloqueado e permitir o desbloqueio.
 * VERSÃO: V1.1 (Estilos encapsulados em tag STYLE)
 */

// Variáveis recebidas do orquestrador (perfil.php):
// $perfil_data (Dados do utilizador bloqueado)
// $id_do_perfil_a_exibir (ID para a ação de desbloqueio)
?>

<style>
    /* Estilos do Card de Bloqueio (Baseados no monolito original) */
    .blocked-profile-card {
        text-align: center;
        padding: 60px 20px !important;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border: 1px solid #d32f2f; /* Vermelho de alerta original */
        background-color: #fff;
        margin-bottom: 20px;
    }

    .blocked-icon {
        font-size: 3.5rem;
        color: #d32f2f;
        margin-bottom: 20px;
        display: block;
    }

    .blocked-profile-card h3 {
        color: #d32f2f;
        font-size: 1.6rem;
        margin: 0 0 10px 0;
        font-weight: 700;
    }

    .blocked-profile-card p {
        color: #65676b;
        font-size: 1rem;
        max-width: 450px;
        margin: 0 auto;
        line-height: 1.5;
    }

    /* Botão de Desbloqueio Customizado */
    .btn-unblock-action {
        background-color: #606770; /* Cor neutra original */
        color: #fff;
        border: none;
        padding: 10px 24px;
        border-radius: 6px;
        font-weight: bold;
        font-size: 0.9em;
        cursor: pointer;
        margin-top: 25px;
        transition: background-color 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-family: inherit;
    }

    .btn-unblock-action:hover {
        background-color: #4b4f56;
    }

    /* Suporte ao Modo Escuro */
    .dark-mode .blocked-profile-card {
        background-color: #242526;
        border-color: #d32f2f;
    }
    
    .dark-mode .blocked-profile-card p {
        color: #b0b3b8;
    }
</style>

<div class="post-card blocked-profile-card">
    <div class="blocked-content-wrapper">
        <i class="fas fa-ban blocked-icon"></i>
        <h3>Você bloqueou este usuário</h3>
        <p>
            Você não pode ver as publicações ou informações de 
            <strong><?php echo htmlspecialchars($perfil_data['nome']); ?></strong> 
            enquanto ele estiver bloqueado.
        </p>
        
        <button class="btn-unblock-action bloquear-usuario-btn" 
                data-usuario-id="<?php echo $id_do_perfil_a_exibir; ?>" 
                data-acao="desbloquear">
            <i class="fas fa-unlock"></i> Desbloquear Usuário
        </button>
    </div>
</div>