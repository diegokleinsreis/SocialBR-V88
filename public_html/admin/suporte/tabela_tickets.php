<?php
/**
 * admin/suporte/tabela_tickets.php
 * COMPONENTE: Listagem Geral de Chamados (V1.5)
 * PAPEL: Fornecer visão macro com Scroll Interno e Cabeçalho Fixo.
 * AJUSTE: Otimização para preenchimento de 100% da moldura administrativa.
 * VERSÃO: 1.5 - socialbr.lol
 */

// 1. CAPTURA DE FILTROS E BUSCA
$filtro_status = $_GET['status'] ?? null;
$filtro_busca  = $_GET['q'] ?? null;

// 2. BUSCA OS DADOS VIA CÉREBRO
$tickets = SuporteLogic::getTodosChamadosAdmin($conn, $filtro_status, $filtro_busca);

// Função auxiliar para badges de status no Admin
if (!function_exists('getAdminStatusBadge')) {
    function getAdminStatusBadge($status) {
        $classes = [
            'aberto'       => 'status-aberto',
            'em_andamento' => 'status-andamento',
            'resolvido'    => 'status-resolvido'
        ];
        $labels = [
            'aberto'       => 'Novo',
            'em_andamento' => 'Em Atend.',
            'resolvido'    => 'OK'
        ];
        $classe = $classes[$status] ?? '';
        $label  = $labels[$status] ?? $status;
        
        return "<span class='status-badge $classe'>$label</span>";
    }
}
?>

<div class="suporte-admin-macro" style="display: flex; flex-direction: column; height: 100%; width: 100%; overflow: hidden;">
    
    <div class="admin-filters-bar" style="padding: 15px 20px; background: #fff; border-bottom: 1px solid #eee; flex-shrink: 0;">
        <form method="GET" action="<?php echo $config['base_path']; ?>admin/suporte" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <div class="filter-group" style="width: 140px;">
                <label style="display:block; font-size: 0.6rem; color: #999; margin-bottom: 5px; font-weight: 800; text-transform: uppercase;">Status</label>
                <select name="status" class="admin-input-select" onchange="this.form.submit()" style="width: 100%; height: 35px; border-radius: 6px; border: 1px solid #ddd; padding: 0 10px; font-size: 0.8rem; font-weight: 600; cursor: pointer;">
                    <option value="">Todos</option>
                    <option value="aberto" <?php echo $filtro_status == 'aberto' ? 'selected' : ''; ?>>Abertos</option>
                    <option value="em_andamento" <?php echo $filtro_status == 'em_andamento' ? 'selected' : ''; ?>>Andamento</option>
                    <option value="resolvido" <?php echo $filtro_status == 'resolvido' ? 'selected' : ''; ?>>Resolvidos</option>
                </select>
            </div>

            <div class="filter-group" style="flex: 1; min-width: 250px;">
                <label style="display:block; font-size: 0.6rem; color: #999; margin-bottom: 5px; font-weight: 800; text-transform: uppercase;">Pesquisa Rápida</label>
                <div style="display: flex; gap: 8px;">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($filtro_busca ?? ''); ?>" 
                           placeholder="Pesquisar por nome ou assunto..." 
                           style="flex: 1; height: 35px; border-radius: 6px; border: 1px solid #ddd; padding: 0 15px; font-size: 0.85rem; outline: none;" autocomplete="off">
                    <button type="submit" class="btn-admin-primary" style="height: 35px; width: 45px; display: flex; align-items: center; justify-content: center; background: #0C2D54; color: #fff; border: none; border-radius: 6px; cursor: pointer;">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="admin-table-responsive-container" style="flex: 1; overflow-y: auto; background: #fff;">
        <table class="admin-table" style="width: 100%; border-collapse: collapse; table-layout: fixed;">
            <thead style="position: sticky; top: 0; z-index: 20; background: #f8f9fa;">
                <tr>
                    <th style="width: 65px; text-align: center;">ID</th>
                    <th style="width: 180px;">Utilizador</th>
                    <th style="width: auto;">Assunto / Categoria</th>
                    <th style="width: 110px; text-align: center;">Status</th>
                    <th style="width: 90px; text-align: center;">Data</th>
                    <th style="width: 120px; text-align: right; padding-right: 25px;">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tickets)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 60px; color: #aaa;">
                            <i class="fas fa-folder-open" style="font-size: 2rem; display: block; margin-bottom: 10px; opacity: 0.3;"></i>
                            Nenhum chamado encontrado.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tickets as $t): ?>
                        <?php 
                            $foto_user = $t['foto_perfil_url'] ? $config['base_path'] . $t['foto_perfil_url'] : $config['base_path'] . 'assets/images/default-avatar.png';
                            $data_atualizacao = strtotime($t['data_atualizacao']);
                            $exibir_data = (date('Y-m-d', $data_atualizacao) == date('Y-m-d')) ? date('H:i', $data_atualizacao) : date('d/m', $data_atualizacao);
                            
                            $aguardando_admin = ($t['status'] !== 'resolvido' && (int)$t['ultima_msg_admin'] === 0);
                        ?>
                        <tr class="<?php echo $aguardando_admin ? 'row-pendente' : ''; ?>" style="transition: background 0.1s;">
                            <td style="text-align: center; font-size: 0.8rem; position: relative;">
                                <?php if ($aguardando_admin): ?>
                                    <span class="dot-alerta pulse-red" title="Aguardando sua resposta"></span>
                                <?php endif; ?>
                                <span style="color: #aaa; font-weight: 800;">#<?php echo $t['id']; ?></span>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <img src="<?php echo $foto_user; ?>" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 1px solid #eee;">
                                    <div style="font-weight: 700; font-size: 0.85rem; color: #1c1e21; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo htmlspecialchars($t['nome']); ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; overflow: hidden;">
                                    <div style="font-weight: 700; font-size: 0.85rem; color: #050505; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($t['assunto']); ?>">
                                        <?php echo htmlspecialchars($t['assunto']); ?>
                                    </div>
                                    <span style="font-size: 0.65rem; color: #0C2D54; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                                        <?php echo htmlspecialchars($t['categoria']); ?>
                                    </span>
                                </div>
                            </td>
                            <td style="text-align: center;"><?php echo getAdminStatusBadge($t['status']); ?></td>
                            <td style="text-align: center; font-size: 0.75rem; color: #666; font-weight: 700;"><?php echo $exibir_data; ?></td>
                            <td style="text-align: right; padding-right: 25px;">
                                <a href="<?php echo $config['base_path']; ?>admin/suporte/<?php echo $t['id']; ?>" class="btn-admin-action-premium">
                                    <i class="fas fa-comment-medical"></i> <span>Atender</span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
/* ALERTA VISUAL */
.dot-alerta {
    position: absolute; left: 6px; top: 50%; transform: translateY(-50%);
    width: 9px; height: 9px; background-color: #ef4444; border-radius: 50%;
}
.pulse-red {
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
    animation: pulse-red-effect 2s infinite;
}
@keyframes pulse-red-effect {
    0% { transform: translateY(-50%) scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
    70% { transform: translateY(-50%) scale(1); box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
    100% { transform: translateY(-50%) scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}

.row-pendente { background-color: #fff9f9; }

/* ESTILIZAÇÃO DA TABELA */
.admin-table th { 
    padding: 12px 10px; font-size: 0.65rem; border-bottom: 1px solid #eee; 
    color: #888; text-transform: uppercase; font-weight: 900; letter-spacing: 1px;
}
.admin-table td { padding: 12px 10px; border-bottom: 1px solid #f5f5f5; vertical-align: middle; }
.admin-table tr:hover { background-color: #f8fbff; }

/* Botão de Ação Estilizado */
.btn-admin-action-premium {
    background: #0C2D54; color: #fff; padding: 6px 12px; border-radius: 6px;
    text-decoration: none; font-size: 0.75rem; font-weight: 700;
    display: inline-flex; align-items: center; gap: 8px; transition: 0.2s;
}
.btn-admin-action-premium:hover { background: #08213d; transform: translateY(-1px); box-shadow: 0 4px 8px rgba(12, 45, 84, 0.2); }

.status-badge { padding: 3px 8px; border-radius: 4px; font-size: 0.65rem; font-weight: 900; display: inline-block; min-width: 85px; }
.status-aberto { background: #fee2e2; color: #b91c1c; }
.status-andamento { background: #fef3c7; color: #92400e; }
.status-resolvido { background: #dcfce7; color: #15803d; }

/* Saneamento de Scrollbar */
.admin-table-responsive-container::-webkit-scrollbar { width: 6px; }
.admin-table-responsive-container::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }

@media (max-width: 900px) {
    .btn-admin-action-premium span { display: none; }
    .btn-admin-action-premium { padding: 8px; }
}
</style>