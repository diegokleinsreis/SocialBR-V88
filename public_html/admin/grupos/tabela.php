<?php
/**
 * admin/grupos/tabela.php
 * PAPEL: Listagem mestre de grupos com ações de moderação.
 * LOCALIZAÇÃO: Deve estar dentro da pasta 'admin/grupos/'
 */
?>
<style>
    /* Contentor e Tabela */
    .admin-table-container { 
        background: #fff; 
        border-radius: 10px; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
        overflow-x: auto; 
    }
    
    .admin-table { 
        width: 100%; 
        border-collapse: collapse; 
        min-width: 900px;
    }
    
    .admin-table th { 
        background: #f8f9fa; 
        text-align: left; 
        padding: 15px; 
        font-size: 0.8rem; 
        color: #666; 
        text-transform: uppercase;
        border-bottom: 2px solid #eee; 
    }
    
    .admin-table td { 
        padding: 15px; 
        border-bottom: 1px solid #eee; 
        font-size: 0.9rem; 
        vertical-align: middle;
    }

    /* Badges de Status e Privacidade */
    .badge { 
        padding: 4px 10px; 
        border-radius: 20px; 
        font-size: 0.75rem; 
        font-weight: 700; 
        display: inline-block;
    }
    .badge-publico { background: #e7f3ff; color: #1877f2; }
    .badge-privado { background: #f0f2f5; color: #65676b; }
    .badge-ativo { background: #e6ffed; color: #28a745; }
    .badge-suspenso { background: #fff3cd; color: #856404; }

    /* Elementos Visuais */
    .group-info-cell { display: flex; align-items: center; gap: 12px; }
    .group-img-sm { 
        width: 40px; 
        height: 40px; 
        border-radius: 8px; 
        object-fit: cover; 
        background: #eee;
    }
    
    .owner-info small { color: #888; display: block; margin-bottom: 2px; }
    
    /* Botões de Ação */
    .actions-flex { display: flex; gap: 6px; }
    
    .btn-group-action {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        color: #fff;
    }
    
    .btn-privacidade { background: #0C2D54; }
    .btn-status-toggle { background: #ffc107; } /* Amarelo para suspender */
    .btn-status-activate { background: #28a745; } /* Verde para ativar */
    .btn-transfer { background: #17a2b8; }
    
    .btn-group-action:hover { opacity: 0.85; transform: translateY(-1px); }
</style>

<div class="admin-table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th width="80">ID</th>
                <th>Grupo</th>
                <th>Privacidade</th>
                <th>Dono Atual</th>
                <th>Membros</th>
                <th>Status</th>
                <th width="150">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($lista_grupos)): ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:40px; color:#888;">
                        <i class="fas fa-search" style="font-size:2rem; display:block; margin-bottom:10px; opacity:0.3;"></i>
                        Nenhum grupo encontrado com os critérios de busca.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($lista_grupos as $g): ?>
                <tr>
                    <td><strong>#<?php echo $g['id']; ?></strong></td>
                    <td>
                        <div class="group-info-cell">
                            <img src="<?php echo $config['base_path'] . $g['foto_capa_url']; ?>" class="group-img-sm" onerror="this.src='assets/img/default_group.png'">
                            <div>
                                <div style="font-weight:700; color:#333;"><?php echo htmlspecialchars($g['nome']); ?></div>
                                <small style="color:#888;">Criado em: <?php echo date('d/m/Y', strtotime($g['data_criacao'])); ?></small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo $g['privacidade']; ?>">
                            <i class="fas <?php echo $g['privacidade'] == 'publico' ? 'fa-globe-americas' : 'fa-lock'; ?>"></i> 
                            <?php echo strtoupper($g['privacidade']); ?>
                        </span>
                    </td>
                    <td class="owner-info">
                        <small>UID: <?php echo $g['id_dono']; ?></small>
                        <strong><?php echo htmlspecialchars($g['dono_nome'] . ' ' . $g['dono_sobrenome']); ?></strong>
                    </td>
                    <td>
                        <div style="font-weight:700; color:#0C2D54;">
                            <i class="fas fa-users" style="opacity:0.5;"></i> <?php echo number_format($g['membros_count']); ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo $g['status']; ?>">
                            <?php echo strtoupper($g['status']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="actions-flex">
                            <button title="Alternar Privacidade" class="btn-group-action btn-privacidade admin-action-btn" 
                                    data-url="<?php echo $config['base_path']; ?>api/admin/grupos_acoes.php" 
                                    data-id="<?php echo $g['id']; ?>" 
                                    data-acao="toggle_privacidade" 
                                    data-confirm-message="Deseja mudar a privacidade deste grupo para <?php echo $g['privacidade'] == 'publico' ? 'Privado' : 'Público'; ?>?">
                                <i class="fas fa-shield-alt"></i>
                            </button>

                            <?php if ($g['status'] == 'ativo'): ?>
                                <button title="Suspender Grupo" class="btn-group-action btn-status-toggle admin-action-btn" 
                                        data-url="<?php echo $config['base_path']; ?>api/admin/grupos_acoes.php" 
                                        data-id="<?php echo $g['id']; ?>" 
                                        data-acao="toggle_status" 
                                        data-confirm-message="Tem a certeza que deseja SUSPENDER este grupo? Ele ficará oculto para os membros.">
                                    <i class="fas fa-pause"></i>
                                </button>
                            <?php else: ?>
                                <button title="Ativar Grupo" class="btn-group-action btn-status-activate admin-action-btn" 
                                        data-url="<?php echo $config['base_path']; ?>api/admin/grupos_acoes.php" 
                                        data-id="<?php echo $g['id']; ?>" 
                                        data-acao="toggle_status" 
                                        data-confirm-message="Deseja REATIVAR este grupo agora?">
                                    <i class="fas fa-play"></i>
                                </button>
                            <?php endif; ?>

                            <button title="Transferir Propriedade" class="btn-group-action btn-transfer" 
                                    onclick="window.promptTrocaDono(<?php echo $g['id']; ?>, '<?php echo addslashes($g['nome']); ?>')">
                                <i class="fas fa-user-edit"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
/**
 * Lógica para Troca de Dono via Painel Admin
 */
window.promptTrocaDono = function(grupoId, nomeGrupo) {
    const novoDonoId = prompt(`Transferir o grupo "${nomeGrupo}"\n\nIntroduza o ID (UID) do novo proprietário:`);
    
    if (novoDonoId && !isNaN(novoDonoId)) {
        if(confirm(`Confirmar a transferência do grupo para o utilizador #${novoDonoId}?`)) {
            const formData = new FormData();
            formData.append('id', grupoId);
            formData.append('id_alvo', novoDonoId);
            formData.append('acao', 'trocar_dono');
            formData.append('csrf_token', '<?php echo get_csrf_token(); ?>');

            fetch('<?php echo $config['base_path']; ?>api/admin/grupos_acoes.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Propriedade transferida com sucesso!');
                    location.reload();
                } else {
                    alert('Erro: ' + (data.error || 'Não foi possível transferir o grupo. Verifique se o ID do utilizador existe e se ele já é membro.'));
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                alert('Erro de ligação com o servidor.');
            });
        }
    } else if (novoDonoId !== null) {
        alert('Por favor, insira um ID numérico válido.');
    }
};
</script>