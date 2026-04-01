<?php
/**
 * admin/grupos/estatisticas.php
 * PAPEL: Exibir os cards de resumo e crescimento do módulo de grupos.
 * LOCALIZAÇÃO: Deve estar dentro da pasta 'admin/grupos/'
 */
?>
<style>
    /* Grid de estatísticas com design responsivo */
    .group-stats-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
        gap: 20px; 
        margin-bottom: 30px; 
    }

    /* Estilização dos Cards baseada no padrão do painel admin */
    .stat-card { 
        background: #fff; 
        padding: 20px; 
        border-radius: 10px; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
        border-left: 5px solid #0C2D54; /* Cor oficial: SocialBR Blue */
        display: flex; 
        align-items: center; 
        gap: 15px; 
        transition: transform 0.2s ease-in-out;
    }

    .stat-card:hover {
        transform: translateY(-3px);
    }

    .stat-card i { 
        font-size: 2rem; 
        color: #0C2D54; 
        opacity: 0.8; 
    }

    .stat-info h3 { 
        margin: 0; 
        font-size: 1.5rem; 
        color: #333; 
    }

    .stat-info p { 
        margin: 0; 
        font-size: 0.85rem; 
        color: #666; 
        font-weight: 600; 
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>

<div class="group-stats-grid">
    <div class="stat-card">
        <i class="fas fa-users"></i>
        <div class="stat-info">
            <h3><?php echo number_format($stats['total']); ?></h3>
            <p>Total de Grupos</p>
        </div>
    </div>

    <div class="stat-card" style="border-left-color: #28a745;">
        <i class="fas fa-check-circle" style="color: #28a745;"></i>
        <div class="stat-info">
            <h3><?php echo number_format($stats['ativos']); ?></h3>
            <p>Grupos Ativos</p>
        </div>
    </div>

    <div class="stat-card" style="border-left-color: #ffc107;">
        <i class="fas fa-pause-circle" style="color: #ffc107;"></i>
        <div class="stat-info">
            <h3><?php echo number_format($stats['suspensos']); ?></h3>
            <p>Suspensos</p>
        </div>
    </div>

    <div class="stat-card" style="border-left-color: #17a2b8;">
        <i class="fas fa-plus-square" style="color: #17a2b8;"></i>
        <div class="stat-info">
            <h3><?php echo number_format($stats['novos_hoje']); ?></h3>
            <p>Criados Hoje</p>
        </div>
    </div>
</div>