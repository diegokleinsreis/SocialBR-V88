/**
 * assets/js/controle_menu.js
 * COMPONENTE: Motor de Controle da Sidebar Retrátil.
 * PAPEL: Gerir a expansão/recolhimento do menu e persistir a escolha do utilizador.
 * VERSÃO: 1.1 - Blindagem de LocalStorage (socialbr.lol)
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. SELETORES PRINCIPAIS ---
    const sidebar = document.getElementById('sidebar-principal');
    const botaoToggle = document.getElementById('botao-controle-sidebar');
    
    // Se a sidebar não existir nesta página (ex: telas de login), encerra o script.
    if (!sidebar || !botaoToggle) return;

    /**
     * --- 2. INICIALIZAÇÃO DE ESTADO ---
     * Verifica no 'armazenamento local' do navegador se o utilizador 
     * já tinha deixado o menu recolhido anteriormente.
     * BLINDAGEM: try/catch para evitar erro Fatal em modo anónimo ou restrições de storage.
     */
    let estadoMenu = null;
    try {
        // Verifica se o objeto localStorage está acessível
        if (typeof(Storage) !== "undefined" && window.localStorage) {
            estadoMenu = localStorage.getItem('sidebar_estado');
        }
    } catch (e) {
        console.warn("📌 Social BR: Acesso ao LocalStorage impedido pelo navegador. Usando estado padrão.");
    }

    if (estadoMenu === 'recolhido') {
        // Aplica a classe imediatamente sem animação para evitar o "pulo" visual
        sidebar.style.transition = 'none';
        sidebar.classList.add('recolhida');
        
        // Restaura a transição após o carregamento inicial
        setTimeout(() => {
            sidebar.style.transition = '';
        }, 100);
    }

    /**
     * --- 3. FUNÇÃO DE ALTERNÂNCIA (TOGGLE) ---
     * Liga/Desliga a classe 'recolhida' e salva a preferência.
     */
    function alternarMenu() {
        sidebar.classList.toggle('recolhida');

        // Verifica se o menu está recolhido agora e salva com segurança (Blindagem)
        try {
            if (typeof(Storage) !== "undefined" && window.localStorage) {
                if (sidebar.classList.contains('recolhida')) {
                    localStorage.setItem('sidebar_estado', 'recolhido');
                    console.log("📌 Menu Recolhido: Preferência salva.");
                } else {
                    localStorage.setItem('sidebar_estado', 'expandido');
                    console.log("📌 Menu Expandido: Preferência salva.");
                }
            }
        } catch (e) {
            console.error("📌 Social BR: Não foi possível gravar a preferência no navegador.");
        }
    }

    // --- 4. EVENT LISTENER ---
    botaoToggle.addEventListener('click', function(e) {
        e.preventDefault();
        alternarMenu();
    });

    /**
     * --- 5. AJUSTE PARA DISPOSITIVOS MÓVEIS ---
     * Garante que o menu retrátil não interfira no comportamento 
     * do menu lateral nativo do Mobile (mobile_nav.php).
     */
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 992) {
            sidebar.classList.remove('recolhida');
        }
    });

});