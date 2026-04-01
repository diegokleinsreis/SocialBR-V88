<?php
/**
 * views/grupos/componentes/view_privada.php
 * Componente: Aviso de Grupo Privado.
 * PAPEL: Bloquear o feed para não-membros e incentivar a adesão.
 * VERSÃO: 1.0 (UX Premium - socialbr.lol)
 */
?>

<style>
    .group-private-lock-container {
        width: 100% !important;
        max-width: 1000px !important;
        margin: 0 auto !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 60px 20px !important;
        background: #fff !important;
        border-radius: 12px !important;
        border: 1px solid #e4e6eb !important;
        text-align: center !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
    }

    .lock-icon-wrapper {
        width: 100px !important;
        height: 100px !important;
        background-color: #f0f2f5 !important;
        border-radius: 50% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin-bottom: 25px !important;
    }

    .lock-icon-wrapper i {
        font-size: 3rem !important;
        color: #0C2D54 !important; /* Sua cor oficial do menu */
    }

    .group-private-lock-container h2 {
        font-size: 1.8rem !important;
        font-weight: 850 !important;
        color: #0C2D54 !important;
        margin-bottom: 15px !important;
    }

    .group-private-lock-container p {
        font-size: 1.1rem !important;
        color: #65676b !important;
        max-width: 500px !important;
        line-height: 1.6 !important;
        margin-bottom: 30px !important;
    }

    .btn-request-access {
        background-color: #1877f2 !important;
        color: #fff !important;
        padding: 14px 30px !important;
        border-radius: 8px !important;
        font-weight: 700 !important;
        font-size: 1rem !important;
        border: none !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        transition: transform 0.2s, background-color 0.2s !important;
        text-decoration: none !important;
    }

    .btn-request-access:hover {
        background-color: #166fe5 !important;
        transform: translateY(-2px) !important;
    }
</style>

<div class="group-private-lock-container">
    
    <div class="lock-icon-wrapper">
        <i class="fas fa-lock"></i>
    </div>

    <h2>Este grupo é privado</h2>
    <p>
        Apenas membros aprovados podem ver as publicações, participantes e interações desta comunidade. 
        Deseja fazer parte desta conversa?
    </p>

    <button class="btn-request-access" onclick="participarDoGrupo(<?php echo $id_grupo; ?>)">
        <i class="fas fa-user-plus"></i>
        <span>Pedir para Participar</span>
    </button>

</div>