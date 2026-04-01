<?php
/**
 * views/grupos/componentes/lista_membros.php
 * Componente: Galeria de Membros com Moderação Integrada.
 * PAPEL: Listar participantes e prover menu de ações para Donos/Admins.
 * VERSÃO: 1.2 (Moderação em Tempo Real - socialbr.lol)
 */

// 1. BUSCA DE DADOS (Vindo do GruposLogic via ver.php)
$membros = GruposLogic::getGroupMembers($conn, $id_grupo);

// 2. SEPARAÇÃO POR CARGOS PARA ORGANIZAÇÃO VISUAL
$admins = array_filter($membros, function($m) { 
    return $m['nivel_permissao'] === 'dono' || $m['nivel_permissao'] === 'moderador'; 
});
$comuns = array_filter($membros, function($m) { 
    return $m['nivel_permissao'] === 'membro'; 
});
?>

<style>
    /* Container Blindado */
    .members-list-wrapper {
        width: 100% !important;
        max-width: 1000px !important;
        margin: 0 auto !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 25px !important;
    }

    .members-section-title {
        font-size: 1.05rem !important;
        font-weight: 800 !important;
        color: #0C2D54 !important;
        border-bottom: 2px solid #e4e6eb !important;
        padding-bottom: 8px !important;
        margin-bottom: 12px !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
    }

    .members-grid {
        display: grid !important;
        grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)) !important;
        gap: 12px !important;
    }

    .member-item-card {
        background: #fff !important;
        border: 1px solid #e4e6eb !important;
        border-radius: 10px !important;
        padding: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        position: relative !important; /* Essencial para o dropdown */
    }

    .member-user-info { display: flex !important; align-items: center !important; gap: 12px !important; }

    .member-avatar {
        width: 50px !important;
        height: 50px !important;
        border-radius: 50% !important;
        object-fit: cover !important;
    }

    .member-text h4 { margin: 0 !important; font-size: 0.95rem !important; font-weight: 700 !important; color: #050505 !important; }

    .role-badge {
        font-size: 0.68rem !important;
        font-weight: 700 !important;
        padding: 3px 8px !important;
        border-radius: 15px !important;
        text-transform: uppercase !important;
        display: inline-block !important;
        margin-top: 4px !important;
    }
    .role-dono { background: #fff0f0 !important; color: #d70000 !important; }
    .role-mod { background: #e7f3ff !important; color: #1877f2 !important; }
    .role-user { background: #f0f2f5 !important; color: #65676b !important; }

    .btn-member-manage {
        background: #f0f2f5 !important;
        color: #65676b !important;
        border: none !important;
        width: 32px !important;
        height: 32px !important;
        border-radius: 50% !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    /* ESTILO DO DROPDOWN DE MODERAÇÃO */
    .mod-dropdown {
        position: absolute !important;
        top: 50px !important;
        right: 12px !important;
        background: #fff !important;
        border: 1px solid #e4e6eb !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        z-index: 100 !important;
        width: 200px !important;
        display: none; /* Escondido por padrão */
    }

    .mod-dropdown.active { display: block !important; }

    .mod-opt {
        padding: 10px 15px !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        color: #050505 !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        transition: background 0.2s !important;
    }

    .mod-opt:hover { background-color: #f2f2f2 !important; }
    .mod-opt-danger { color: #fa3e3e !important; }
    .mod-opt-danger:hover { background-color: #fff0f0 !important; }

    @media (max-width: 768px) { .members-grid { grid-template-columns: 1fr !important; } }
</style>

<div class="members-list-wrapper">

    <section>
        <h3 class="members-section-title">
            <i class="fas fa-user-shield"></i> Administração
        </h3>
        <div class="members-grid">
            <?php foreach ($admins as $m): ?>
                <div class="member-item-card" id="member-card-<?php echo $m['id']; ?>">
                    <div class="member-user-info">
                        <?php $foto = !empty($m['foto_perfil_url']) ? $m['foto_perfil_url'] : 'assets/images/default-avatar.png'; ?>
                        <img src="<?php echo $config['base_path'] . $foto; ?>" class="member-avatar">
                        <div class="member-text">
                            <h4><?php echo htmlspecialchars($m['nome'] . ' ' . $m['sobrenome']); ?></h4>
                            <span class="role-badge <?php echo ($m['nivel_permissao'] === 'dono') ? 'role-dono' : 'role-mod'; ?>">
                                <?php echo ($m['nivel_permissao'] === 'dono') ? 'Proprietário' : 'Moderador'; ?>
                            </span>
                        </div>
                    </div>

                    <?php if (($is_dono || $is_admin) && $m['nivel_permissao'] !== 'dono'): ?>
                        <button class="btn-member-manage" onclick="toggleModMenu(<?php echo $m['id']; ?>)">
                            <i class="fas fa-cog"></i>
                        </button>
                        <div class="mod-dropdown" id="dropdown-<?php echo $m['id']; ?>">
                            <div class="mod-opt" onclick="executarAcaoMembro(<?php echo $m['id']; ?>, 'rebaixar_membro')">
                                <i class="fas fa-user-minus"></i> Rebaixar a Membro
                            </div>
                            <div class="mod-opt mod-opt-danger" onclick="executarAcaoMembro(<?php echo $m['id']; ?>, 'remover')">
                                <i class="fas fa-user-times"></i> Remover do Grupo
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section>
        <h3 class="members-section-title">
            <i class="fas fa-users"></i> Membros (<?php echo count($comuns); ?>)
        </h3>
        <div class="members-grid">
            <?php foreach ($comuns as $m): ?>
                <div class="member-item-card" id="member-card-<?php echo $m['id']; ?>">
                    <div class="member-user-info">
                        <?php $foto = !empty($m['foto_perfil_url']) ? $m['foto_perfil_url'] : 'assets/images/default-avatar.png'; ?>
                        <img src="<?php echo $config['base_path'] . $foto; ?>" class="member-avatar">
                        <div class="member-text">
                            <h4><?php echo htmlspecialchars($m['nome'] . ' ' . $m['sobrenome']); ?></h4>
                            <span class="role-badge role-user">Membro</span>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 6px;">
                        <a href="<?php echo $config['base_path'] . 'perfil.php?id=' . $m['id']; ?>" class="btn-member-manage" title="Ver Perfil">
                            <i class="fas fa-external-link-alt" style="font-size: 0.75rem;"></i>
                        </a>
                        <?php if ($is_dono || $is_admin): ?>
                            <button class="btn-member-manage" onclick="toggleModMenu(<?php echo $m['id']; ?>)">
                                <i class="fas fa-cog"></i>
                            </button>
                            <div class="mod-dropdown" id="dropdown-<?php echo $m['id']; ?>">
                                <div class="mod-opt" onclick="executarAcaoMembro(<?php echo $m['id']; ?>, 'promover_mod')">
                                    <i class="fas fa-user-shield"></i> Tornar Moderador
                                </div>
                                <div class="mod-opt" onclick="executarAcaoMembro(<?php echo $m['id']; ?>, 'tornar_dono')">
                                    <i class="fas fa-crown"></i> Transferir Posse
                                </div>
                                <div class="mod-opt mod-opt-danger" onclick="executarAcaoMembro(<?php echo $m['id']; ?>, 'remover')">
                                    <i class="fas fa-user-times"></i> Remover do Grupo
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

</div>

<script>
/**
 * Abre/Fecha o menu de moderação de um usuário específico
 */
function toggleModMenu(idUsuario) {
    const dropdown = document.getElementById(`dropdown-${idUsuario}`);
    // Fecha outros dropdowns abertos antes de abrir este
    document.querySelectorAll('.mod-dropdown').forEach(d => {
        if(d.id !== `dropdown-${idUsuario}`) d.classList.remove('active');
    });
    dropdown.classList.toggle('active');
}

/**
 * Chama a API de gerenciamento de membros
 * Utiliza o objeto GROUP_CONFIG definido no ver.php
 */
function executarAcaoMembro(idAlvo, acao) {
    if(!confirm("Tem certeza que deseja realizar esta ação de moderação?")) return;

    const formData = new FormData();
    formData.append('id_grupo', GROUP_CONFIG.idGrupo);
    formData.append('id_usuario_alvo', idAlvo);
    formData.append('acao', acao);
    formData.append('csrf_token', GROUP_CONFIG.csrfToken);

    fetch(`${GROUP_CONFIG.basePath}api/grupos/gerenciar_membro.php`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        alert(data.msg);
        if(data.status === 'sucesso') location.reload();
    })
    .catch(err => alert("Erro ao processar moderação."));
}

// Fecha dropdowns se clicar fora deles
window.addEventListener('click', function(e) {
    if (!e.target.closest('.btn-member-manage') && !e.target.closest('.mod-dropdown')) {
        document.querySelectorAll('.mod-dropdown').forEach(d => d.classList.remove('active'));
    }
});
</script>