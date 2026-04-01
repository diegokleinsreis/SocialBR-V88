<?php
/**
 * views/chat/componentes/sub_iniciador_privado.php
 * Sub-Componente: Seleção para Conversa Privada.
 * PAPEL: Interface de busca e listagem de amigos para chat 1x1.
 * VERSÃO: V60.0 (socialbr.lol)
 */
?>

<div class="sb-selection-view-content" id="sb-privado-ui">
    
    <div class="sb-search-wrapper">
        <i class="fas fa-search"></i>
        <input type="text" 
               class="sb-search-input" 
               id="sb-friend-search-privado" 
               placeholder="Pesquisar amigo para conversar...">
    </div>

    <div class="sb-friends-list" id="sb-friends-container-privado">
        <div style="text-align: center; padding: 30px; color: #65676b;">
            <i class="fas fa-circle-notch fa-spin" style="font-size: 1.5rem; margin-bottom: 10px; display: block;"></i>
            <span>A carregar os seus amigos...</span>
        </div>
    </div>

</div>