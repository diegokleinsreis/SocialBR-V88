<?php
/**
 * FICHEIRO: super_debug/sd_motor.php
 * PAPEL: Orquestrador Master de Motores e Estilos (O Coração)
 * VERSÃO: 4.0 (Edição Modular & Glass Morphism Total)
 * INTEGRIDADE: Completo e Integral - Sem remoções.
 */

// 1. CARREGAMENTO DA ESTÉTICA MODULAR (CSS)
$pasta_estilos = __DIR__ . '/estilos_css/';

$arquivos_estilos = [
    'estilos_base.css.php',        // Definições Glass e Botões
    'estilos_sql.css.php',         // Estilo do Hub de Base de Dados
    'estilos_metricas.css.php',    // Estilo da Telemetria
    'estilos_diagnostico.css.php', // Estilo do Scanner de UI
    'estilos_simulador.css.php'    // Estilo do Hardware Simulator
];

foreach ($arquivos_estilos as $estilo) {
    if (file_exists($pasta_estilos . $estilo)) {
        include_once $pasta_estilos . $estilo;
    }
}

// 2. NAMESPACE E ESTADOS GLOBAIS
echo '<script>window.SocialBR_HUD = { versao: "4.0", camadaZ: 1000000 };</script>';

// 3. CARREGAMENTO DOS MOTORES LÓGICOS (JS)
$pasta_motores = __DIR__ . '/motores_js/';

$arquivos_motores = [
    'motor_principal.js.php',
    'motor_sql.js.php',
    'motor_performance.js.php',
    'motor_simulador.js.php',
    'motor_diagnostico.js.php'
];

foreach ($arquivos_motores as $motor) {
    if (file_exists($pasta_motores . $motor)) {
        include_once $pasta_motores . $motor;
    }
}

// 4. CARREGAMENTO DO AUTO-FIX (O Cirurgião)
$autofix = __DIR__ . '/sd_autofix.php';
if (file_exists($autofix)) {
    echo '<script>';
    include_once $autofix;
    echo '</script>';
}
?>

<script>
/**
 * INICIALIZAÇÃO E PERSISTÊNCIA DO HUD 4.0
 */
document.addEventListener("DOMContentLoaded", function() {
    console.group("SocialBR HUD 4.0: Inicialização");
    console.log("Status: Todos os módulos e estilos Glass carregados.");
    
    // Verificação de Persistência do Simulador
    if (localStorage.getItem('admin_device_sim') === 'active') {
        if (typeof toggleDeviceSim === "function") {
            console.log("Ação: Reativando Simulador de Dispositivos...");
            toggleDeviceSim();
        }
    }
    
    console.groupEnd();
});
</script>