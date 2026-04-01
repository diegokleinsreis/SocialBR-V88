<?php
/**
 * views/grupos/componentes/configuracoes_grupo.php
 * Componente: Painel de Gestão do Grupo.
 * PAPEL: Permitir edição de dados (Dono) ou gestão de participação (Membro).
 * VERSÃO: 1.1 (Integração Total AJAX - socialbr.lol)
 */

// Os dados do $grupo e as permissões ($is_dono, $is_admin) vêm do orquestrador ver.php
$nome_grupo  = htmlspecialchars($grupo['nome']);
$descricao   = htmlspecialchars($grupo['descricao'] ?? '');
$privacidade = $grupo['privacidade'];
?>

<style>
    /* Container Blindado: 1000px de largura total */
    .group-settings-wrapper {
        width: 100% !important;
        max-width: 1000px !important;
        margin: 0 auto !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 25px !important;
    }

    .settings-card-premium {
        background: #fff !important;
        border: 1px solid #e4e6eb !important;
        border-radius: 12px !important;
        padding: 30px !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05) !important;
    }

    .settings-section-title {
        font-size: 1.1rem !important; /* Tamanho refinado */
        font-weight: 800 !important;
        color: #0C2D54 !important; /* Sua cor oficial profunda */
        margin-bottom: 20px !important;
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        border-bottom: 1px solid #f0f2f5 !important;
        padding-bottom: 10px !important;
    }

    /* Formulários Internos */
    .group-form-row {
        margin-bottom: 20px !important;
    }

    .group-form-row label {
        display: block !important;
        font-weight: 700 !important;
        font-size: 0.9rem !important;
        color: #4b4f56 !important;
        margin-bottom: 8px !important;
    }

    .group-input-style {
        width: 100% !important;
        padding: 12px 15px !important;
        border-radius: 8px !important;
        border: 1px solid #dddfe2 !important;
        background-color: #f7f8fa !important;
        font-size: 1rem !important;
        color: #050505 !important;
        outline: none !important;
        transition: border-color 0.2s !important;
    }

    .group-input-style:focus {
        border-color: #1877f2 !important;
        background-color: #fff !important;
    }

    textarea.group-input-style {
        min-height: 120px !important;
        resize: vertical !important;
    }

    /* Botões de Ação */
    .settings-actions-footer {
        display: flex !important;
        justify-content: flex-end !important;
        gap: 12px !important;
        margin-top: 10px !important;
    }

    .btn-settings-save {
        background-color: #1877f2 !important;
        color: #fff !important;
        padding: 12px 25px !important;
        border-radius: 8px !important;
        font-weight: 700 !important;
        border: none !important;
        cursor: pointer !important;
        transition: filter 0.2s !important;
    }

    .btn-settings-danger {
        background-color: #fff !important;
        color: #fa3e3e !important;
        border: 1px solid #fa3e3e !important;
        padding: 10px 20px !important;
        border-radius: 8px !important;
        font-weight: 700 !important;
        cursor: pointer !important;
        transition: all 0.2s !important;
    }

    .btn-settings-danger:hover {
        background-color: #fa3e3e !important;
        color: #fff !important;
    }

    @media (max-width: 768px) {
        .settings-card-premium { padding: 20px !important; }
    }
</style>

<div class="group-settings-wrapper">

    <?php if ($is_dono || $is_admin): ?>
        <div class="settings-card-premium">
            <h3 class="settings-section-title">
                <i class="fas fa-edit"></i> Informações da Comunidade
            </h3>
            
            <form id="form-edit-grupo" onsubmit="atualizarGrupo(event)">
                <input type="hidden" name="id_grupo" value="<?php echo $id_grupo; ?>">

                <div class="group-form-row">
                    <label>Nome do Grupo</label>
                    <input type="text" name="nome" class="group-input-style" value="<?php echo $nome_grupo; ?>" required>
                </div>

                <div class="group-form-row">
                    <label>Descrição</label>
                    <textarea name="descricao" class="group-input-style"><?php echo $descricao; ?></textarea>
                </div>

                <div class="group-form-row">
                    <label>Privacidade</label>
                    <select name="privacidade" class="group-input-style">
                        <option value="publico" <?php echo ($privacidade === 'publico') ? 'selected' : ''; ?>>Público (Todos podem ver posts)</option>
                        <option value="privado" <?php echo ($privacidade === 'privado') ? 'selected' : ''; ?>>Privado (Apenas membros aprovados)</option>
                    </select>
                </div>

                <div class="settings-actions-footer">
                    <button type="submit" class="btn-settings-save">Salvar Alterações</button>
                </div>
            </form>
        </div>

        <div class="settings-card-premium" style="border-color: #fa3e3e !important;">
            <h3 class="settings-section-title" style="color: #fa3e3e !important;">
                <i class="fas fa-exclamation-triangle"></i> Zona de Perigo
            </h3>
            <p style="color: #65676b; font-size: 0.9rem; margin-bottom: 20px;">
                Ao apagar o grupo, todas as publicações, membros e fotos serão removidos permanentemente. Esta ação não pode ser desfeita.
            </p>
            <button class="btn-settings-danger" onclick="apagarGrupo(<?php echo $id_grupo; ?>)">
                <i class="fas fa-trash-alt"></i> Apagar Grupo Permanentemente
            </button>
        </div>

    <?php else: ?>
        <div class="settings-card-premium">
            <h3 class="settings-section-title">
                <i class="fas fa-user-cog"></i> Minha Participação
            </h3>
            <p style="color: #65676b; font-size: 0.95rem; margin-bottom: 25px;">
                Você é um membro ativo desta comunidade desde <?php echo date('d/m/Y', strtotime($grupo['data_criacao'] ?? 'now')); ?>.
            </p>
            
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <button class="btn-settings-danger" style="width: fit-content;" onclick="sairDoGrupo(<?php echo $id_grupo; ?>)">
                    <i class="fas fa-sign-out-alt"></i> Sair do Grupo
                </button>
            </div>
        </div>
    <?php endif; ?>

</div>

<script>
/**
 * Envia as atualizações do grupo via API Editar
 * Utiliza o objeto global GROUP_CONFIG definido no ver.php
 */
function atualizarGrupo(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('csrf_token', GROUP_CONFIG.csrfToken);

    fetch(`${GROUP_CONFIG.basePath}api/grupos/editar.php`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        alert(data.msg);
        if (data.status === 'sucesso') {
            location.reload(); // Recarrega para aplicar as mudanças de nome/privacidade
        }
    })
    .catch(error => {
        console.error('Erro ao atualizar grupo:', error);
        alert('Erro na comunicação com o servidor.');
    });
}

/**
 * Solicita a exclusão do grupo via API Excluir
 */
function apagarGrupo(id) {
    if (confirm("TEM CERTEZA? Esta ação apagará permanentemente a comunidade Social BR e todos os seus dados.")) {
        const formData = new FormData();
        formData.append('id_grupo', id);
        formData.append('csrf_token', GROUP_CONFIG.csrfToken);

        fetch(`${GROUP_CONFIG.basePath}api/grupos/excluir.php`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            alert(data.msg);
            if (data.status === 'sucesso') {
                window.location.href = `${GROUP_CONFIG.basePath}grupos`;
            }
        })
        .catch(error => {
            console.error('Erro ao excluir grupo:', error);
            alert('Erro na comunicação com o servidor.');
        });
    }
}
</script>