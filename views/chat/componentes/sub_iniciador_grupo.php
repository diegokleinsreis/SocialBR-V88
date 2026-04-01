<?php
/**
 * views/chat/componentes/sub_iniciador_grupo.php
 * Sub-Componente: Seleção e Configuração de Grupo.
 * PAPEL: Interface para nomear o grupo e selecionar múltiplos participantes.
 * VERSÃO: V60.1 (Sincronizado com Layout V60.5 - socialbr.lol)
 */
?>

<div class="sb-selection-view-content" id="sb-grupo-ui">
    
    <div class="sb-group-config" id="sb-group-form-area" style="display: block;">
        <input type="text" 
               id="sb-group-name-input" 
               class="sb-input-group-name" 
               placeholder="Como se vai chamar o grupo?">
        
        <p style="font-size: 0.85rem; color: #65676b; margin: 15px 0 10px 0; font-weight: 600;">
            Selecione os participantes (mínimo 1):
        </p>
    </div>

    <div class="sb-search-wrapper">
        <i class="fas fa-search"></i>
        <input type="text" 
               class="sb-search-input" 
               id="sb-friend-search-grupo" 
               placeholder="Pesquisar amigos para convidar...">
    </div>

    <div class="sb-friends-list" id="sb-friends-container-grupo">
        <div style="text-align: center; padding: 40px; color: #65676b;">
            <i class="fas fa-users" style="font-size: 2rem; margin-bottom: 12px; display: block; opacity: 0.3;"></i>
            <span>A carregar a sua lista de amigos...</span>
        </div>
    </div>

</div>