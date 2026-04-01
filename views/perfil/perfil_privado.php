<?php
/**
 * views/perfil/perfil_privado.php
 * Componente: Aviso de Perfil Privado.
 * PAPEL: Informar ao visitante que o conteúdo está restrito a amigos.
 * VERSÃO: V1.1 (Estilos encapsulados em tag STYLE)
 */

// Variáveis recebidas do orquestrador (perfil.php):
// $perfil_data (Dados do dono do perfil)
?>

<style>
    /* Estilos do Card de Perfil Privado (Baseados no monolito original) */
    .private-profile-card {
        text-align: center;
        padding: 60px 20px !important;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin-top: 10px;
    }

    .private-content-wrapper i.fa-lock {
        font-size: 3.5rem;
        color: #65676b;
        margin-bottom: 20px;
        display: block;
    }

    .private-content-wrapper h3 {
        font-size: 1.6rem;
        color: #050505;
        margin: 0 0 10px 0;
        font-weight: 700;
    }

    .private-content-wrapper p {
        color: #65676b;
        font-size: 1rem;
        max-width: 350px;
        margin: 0 auto;
        line-height: 1.5;
    }

    /* Suporte ao Modo Escuro (Baseado no _post.css original) */
    .dark-mode .private-profile-card {
        background-color: #242526;
        border-color: #3e4042;
    }
    
    .dark-mode .private-content-wrapper h3 {
        color: #e4e6eb;
    }
    
    .dark-mode .private-content-wrapper p,
    .dark-mode .private-content-wrapper i.fa-lock {
        color: #b0b3b8;
    }
</style>

<div class="post-card private-profile-card">
    <div class="private-content-wrapper">
        <i class="fas fa-lock"></i>
        <h3>Este perfil é privado</h3>
        <p>
            Adicione <strong><?php echo htmlspecialchars($perfil_data['nome']); ?></strong> como amigo 
            para ver as suas publicações e informações.
        </p>
    </div>
</div>