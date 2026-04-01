<?php
/**
 * FICHEIRO: componentes/super_debug.php
 * VERSÃO: 12.0 (Clean Modular - Anti-Syntax Error)
 * PAPEL: Maestro do Diagnóstico.
 * RESPONSABILIDADE: Abrir as tags de sistema e incluir o código puro dos átomos.
 */

// 1. Definição do caminho absoluto para a pasta dos sub-componentes
$sd_path = __DIR__ . '/super_debug/';
?>

<style id="super-debug-styles">
    <?php 
    if (file_exists($sd_path . 'sd_estilos.php')) {
        include_once $sd_path . 'sd_estilos.php'; 
    }
    ?>
</style>

<?php 
if (file_exists($sd_path . 'sd_painel.php')) {
    include_once $sd_path . 'sd_painel.php'; 
}
?>

<script id="super-debug-engine">
    /** * O PHP despeja aqui o JavaScript dos motores. 
     * Como os ficheiros sd_motor e sd_autofix agora serão puros 
     * (sem as tags <script> internas), o erro de consola desaparecerá.
     */
    <?php 
    if (file_exists($sd_path . 'sd_motor.php')) {
        include_once $sd_path . 'sd_motor.php'; 
    }
    
    if (file_exists($sd_path . 'sd_autofix.php')) {
        include_once $sd_path . 'sd_autofix.php'; 
    }
    ?>
</script>