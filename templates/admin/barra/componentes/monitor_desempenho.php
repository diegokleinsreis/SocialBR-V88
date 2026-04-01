<?php
/**
 * FICHEIRO: componentes/monitor_desempenho.php
 * PAPEL: Componente Atómico de Telemetria (Versão Compacta Mobile-First)
 * VERSÃO: 15.0 (Otimização de Espaço & Sinalizador Inteligente)
 * RESPONSABILIDADE: Prover acesso à telemetria via ícone único e painel Glass.
 * INTEGRIDADE: Completo e Integral.
 */

// Captura de dados do orquestrador
$tempo   = $perf_data['tempo']   ?? '0.00';
$memoria = $perf_data['memoria'] ?? '0.00';
$cpu     = $perf_data['cpu']     ?? 'N/A';

/**
 * LÓGICA DE ALERTA: O ícone na barra piscará se o PHP estiver lento.
 * A animação 'perf-alerta' está definida em estilos_metricas.css.php
 */
$classe_alerta = ($tempo > 500) ? 'perf-alerta' : '';
?>

<div class="admin-perf-group">
    
    <button type="button" 
            onclick="toggleMetricsHub()" 
            class="metrica-box mobile-hub-trigger <?php echo $classe_alerta; ?>" 
            title="Telemetria: <?php echo $tempo; ?>ms | <?php echo $memoria; ?>MB | CPU: <?php echo $cpu; ?>"
            style="cursor: pointer;">
        <i class="fas fa-tachometer-alt"></i>
    </button>

    <div class="metrics-hub-panel" id="metrics-hub-root" style="display: none;">
        <div class="hub-header">
            <span><i class="fas fa-chart-line"></i> Telemetria do Sistema</span>
            <i class="fas fa-times" onclick="toggleMetricsHub()" style="cursor:pointer" title="Fechar Hub"></i>
        </div>
        
        <div class="hub-row" style="border-bottom: 1px solid rgba(255,255,255,0.1); padding: 10px 0;">
            <strong>Resposta PHP:</strong> 
            <span style="color: <?php echo ($tempo > 500) ? 'var(--hud-danger)' : 'var(--hud-success)'; ?>; font-weight: 800;">
                <?php echo $tempo; ?>ms
            </span>
        </div>
        
        <div class="hub-row" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding: 10px 0;">
            <strong>Memória RAM:</strong> 
            <span style="color: #fff;"><?php echo $memoria; ?>MB</span>
        </div>
        
        <div class="hub-row" style="padding: 10px 0;">
            <strong>Carga CPU:</strong> 
            <span style="color: #fff;"><?php echo $cpu; ?></span>
        </div>

        <div style="font-size: 8px; color: rgba(255,255,255,0.3); margin-top: 15px; text-align: center; font-style: italic; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px;">
            * Métricas capturadas durante o processamento desta página.
        </div>
    </div>
</div>