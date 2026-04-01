<?php
/**
 * FICHEIRO: super_debug/sd_device_sim.php
 * PAPEL: Gatilho (Ícone) para o Simulador de Dispositivos na Barra Admin.
 * VERSÃO: 1.0 (Mobile First Edition)
 * RESPONSABILIDADE: Prover o ponto de entrada visual para o simulador de hardware.
 */

// 1. SEGURANÇA E SESSÃO
// Garante que o gatilho só exista no contexto de um administrador autenticado
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') { return; }
?>

<div class="admin-atom-group">
    <button type="button" 
            onclick="toggleDeviceSim()" 
            class="btn-super-debug" 
            id="btn-device-simulator"
            title="Device Simulator: Testar interface em iPhone, Android ou Tablet.">
        <i class="fas fa-mobile-alt"></i>
    </button>
</div>