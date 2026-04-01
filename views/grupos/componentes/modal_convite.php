<?php
/**
 * views/grupos/componentes/modal_convite.php
 * Componente: Modal de Convite de Amigos.
 * PAPEL: Listar amigos e permitir o disparo de convites via notificação.
 * VERSÃO: 1.0 (Blindagem de ID e CSS - socialbr.lol)
 */
?>

<style>
    /* 1. OVERLAY E CONTAINER */
    .invite-modal-overlay {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background: rgba(0, 0, 0, 0.6) !important;
        z-index: 9999 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        backdrop-filter: blur(4px) !important;
    }

    .invite-modal-container {
        background: #fff !important;
        width: 100% !important;
        max-width: 450px !important;
        border-radius: 12px !important;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.2) !important;
        display: flex !important;
        flex-direction: column !important;
        max-height: 90vh !important;
        overflow: hidden !important;
    }

    /* 2. CABEÇALHO */
    .invite-modal-header {
        padding: 15px 20px !important;
        border-bottom: 1px solid #e4e6eb !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
    }

    .invite-modal-header h3 {
        margin: 0 !important;
        font-size: 1.2rem !important;
        font-weight: 800 !important;
        color: #0C2D54 !important; /* Cor Oficial */
    }

    .invite-modal-close {
        background: #f0f2f5 !important;
        border: none !important;
        width: 36px !important;
        height: 36px !important;
        border-radius: 50% !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: background 0.2s !important;
    }

    /* 3. BARRA DE BUSCA */
    .invite-modal-search {
        padding: 12px 20px !important;
        background: #fff !important;
        border-bottom: 1px solid #f0f2f5 !important;
    }

    .invite-search-input {
        width: 100% !important;
        padding: 10px 15px !important;
        background: #f0f2f5 !important;
        border: none !important;
        border-radius: 20px !important;
        font-size: 0.9rem !important;
        outline: none !important;
    }

    /* 4. LISTA DE AMIGOS */
    .invite-modal-body {
        flex: 1 !important;
        overflow-y: auto !important;
        padding: 10px !important;
        min-height: 300px !important;
    }

    .invite-friend-item {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        padding: 8px 10px !important;
        border-radius: 8px !important;
        transition: background 0.2s !important;
    }

    .invite-friend-item:hover {
        background: #f0f2f5 !important;
    }

    .invite-friend-avatar {
        width: 45px !important;
        height: 45px !important;
        border-radius: 50% !important;
        object-fit: cover !important;
        border: 1px solid #e4e6eb !important;
    }

    .invite-friend-info {
        flex: 1 !important;
    }

    .invite-friend-info strong {
        display: block !important;
        font-size: 0.95rem !important;
        color: #050505 !important;
    }

    .invite-btn-send {
        background: #1877f2 !important;
        color: #fff !important;
        border: none !important;
        padding: 7px 15px !important;
        border-radius: 6px !important;
        font-weight: 600 !important;
        font-size: 0.85rem !important;
        cursor: pointer !important;
    }

    .invite-btn-send.is-sent {
        background: #e4e6eb !important;
        color: #65676b !important;
        cursor: default !important;
    }

    /* Estados auxiliares */
    .is-hidden { display: none !important; }
</style>

<div id="group-invite-modal" class="invite-modal-overlay is-hidden">
    <div class="invite-modal-container">
        
        <div class="invite-modal-header">
            <h3>Convidar Amigos</h3>
            <button type="button" class="invite-modal-close" onclick="fecharModalConvite()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="invite-modal-search">
            <input type="text" id="invite-search-field" class="invite-search-input" 
                   placeholder="Procurar amigos..." onkeyup="filtrarAmigosConvite()">
        </div>

        <div id="invite-modal-list" class="invite-modal-body">
            <div class="text-center py-4" style="color: #65676b;">
                <i class="fas fa-spinner fa-spin"></i> A carregar amigos...
            </div>
        </div>

    </div>
</div>

<script>
/**
 * Lógica do Modal de Convites
 */
function fecharModalConvite() {
    document.getElementById('group-invite-modal').classList.add('is-hidden');
    document.body.style.overflow = ''; // Destrava o scroll do site
}

function abrirModalConvite() {
    const modal = document.getElementById('group-invite-modal');
    modal.classList.remove('is-hidden');
    document.body.style.overflow = 'hidden'; // Trava o scroll por trás
    
    const listContainer = document.getElementById('invite-modal-list');
    listContainer.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> A carregar amigos...</div>';

    // Carregamento via AJAX (Passo 57)
    fetch(`${GROUP_CONFIG.basePath}api/grupos/amigos_convite.php?id_grupo=${GROUP_CONFIG.idGrupo}`)
    .then(res => res.json())
    .then(data => {
        if(data.success && data.amigos.length > 0) {
            renderizarListaAmigos(data.amigos);
        } else {
            listContainer.innerHTML = '<div class="text-center py-5"><p style="color:#65676b;">Nenhum amigo disponível para convidar.</p></div>';
        }
    })
    .catch(err => {
        console.error("Erro ao carregar amigos:", err);
        listContainer.innerHTML = '<div class="text-center py-5 text-danger">Erro de comunicação.</div>';
    });
}

function renderizarListaAmigos(amigos) {
    const container = document.getElementById('invite-modal-list');
    container.innerHTML = '';
    
    amigos.forEach(amigo => {
        const foto = amigo.foto_perfil_url ? GROUP_CONFIG.basePath + amigo.foto_perfil_url : GROUP_CONFIG.basePath + 'assets/images/default-avatar.png';
        const item = `
            <div class="invite-friend-item" data-nome="${amigo.nome.toLowerCase()} ${amigo.sobrenome.toLowerCase()}">
                <img src="${foto}" class="invite-friend-avatar">
                <div class="invite-friend-info">
                    <strong>${amigo.nome} ${amigo.sobrenome}</strong>
                </div>
                <button class="invite-btn-send" onclick="dispararConvite(${amigo.id}, this)">
                    Convidar
                </button>
            </div>
        `;
        container.innerHTML += item;
    });
}

function filtrarAmigosConvite() {
    const termo = document.getElementById('invite-search-field').value.toLowerCase();
    const itens = document.querySelectorAll('.invite-friend-item');
    
    itens.forEach(item => {
        const nome = item.getAttribute('data-nome');
        item.style.display = nome.includes(termo) ? 'flex' : 'none';
    });
}

function dispararConvite(idAmigo, btn) {
    if(btn.classList.contains('is-sent')) return;

    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;

    const formData = new FormData();
    formData.append('id_grupo', GROUP_CONFIG.idGrupo);
    formData.append('id_amigo', idAmigo);
    formData.append('csrf_token', GROUP_CONFIG.csrfToken);

    fetch(`${GROUP_CONFIG.basePath}api/grupos/convidar.php`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'sucesso') {
            btn.innerHTML = 'Enviado';
            btn.classList.add('is-sent');
        } else {
            alert("Erro: " + data.msg);
            btn.innerHTML = 'Convidar';
            btn.disabled = false;
        }
    });
}
</script>