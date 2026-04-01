<?php
/**
 * views/suporte/lista_historico.php
 * COMPONENTE: Lista de Histórico de Chamados (V3.0)
 * PAPEL: Exibir tickets em formato App com scroll interno e Alerta Vermelho.
 * AJUSTE: Layout Flexbox para preenchimento total e rolagem independente.
 * VERSÃO: 3.0 - socialbr.lol
 */

// 1. BUSCA OS DADOS (O $conn e $user_id já vêm do orquestrador suporte.php)
$meus_chamados = SuporteLogic::getChamadosPorUsuario($conn, $user_id);

// Função auxiliar para estilizar os badges de status
if (!function_exists('getStatusBadge')) {
    function getStatusBadge($status) {
        $cores = [
            'aberto' => ['bg' => '#e1f5fe', 'text' => '#01579b', 'label' => 'Aberto'],
            'em_andamento' => ['bg' => '#fff3e0', 'text' => '#e65100', 'label' => 'Em Atendimento'],
            'resolvido' => ['bg' => '#e8f5e9', 'text' => '#1b5e20', 'label' => 'Resolvido']
        ];
        
        $estilo = $cores[$status] ?? ['bg' => '#f5f5f5', 'text' => '#616161', 'label' => 'Desconhecido'];
        
        return "<span style='padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 800; background: {$estilo['bg']}; color: {$estilo['text']}; text-transform: uppercase;'>
                    {$estilo['label']}
                </span>";
    }
}
?>

<div class="suporte-historico-wrapper" style="display: flex; flex-direction: column; height: 100%; overflow: hidden;">
    
    <div style="padding: 20px 25px; border-bottom: 1px solid #eee; flex-shrink: 0; background: #fcfcfc;">
        <h3 style="margin: 0; color: #1c1e21; font-weight: 800; font-size: 1.1rem;">Meus Chamados</h3>
        <p style="margin: 5px 0 0 0; font-size: 0.75rem; color: #606770;">Acompanhe o progresso das suas solicitações abaixo.</p>
    </div>

    <div style="flex: 1; overflow-y: auto; background: #fff;">
        <?php if (empty($meus_chamados)): ?>
            <div style="text-align: center; padding: 60px 20px;">
                <i class="fas fa-ticket-alt" style="font-size: 3rem; color: #ddd; margin-bottom: 15px;"></i>
                <p style="color: #888; font-size: 0.95rem;">Você ainda não tem pedidos de suporte ativos.</p>
                <a href="<?php echo $config['base_path']; ?>suporte/abrir" class="primary-btn-small" style="margin-top: 15px; display: inline-block; background: #0C2D54; border-radius: 6px;">
                    Abrir Novo Chamado
                </a>
            </div>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                <thead style="position: sticky; top: 0; background: #fff; z-index: 10; box-shadow: 0 1px 0 #eee;">
                    <tr style="text-align: left;">
                        <th style="padding: 15px 25px; color: #90949c; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px;">ID</th>
                        <th style="padding: 15px 12px; color: #90949c; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px;">Assunto</th>
                        <th style="padding: 15px 12px; color: #90949c; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px;">Status</th>
                        <th style="padding: 15px 12px; color: #90949c; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px;">Atividade</th>
                        <th style="padding: 15px 25px; text-align: right;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($meus_chamados as $chamado): ?>
                        <?php 
                            $tem_novidade = ($chamado['status'] !== 'resolvido' && isset($chamado['ultima_msg_admin']) && (int)$chamado['ultima_msg_admin'] === 1);
                        ?>
                        <tr style="border-bottom: 1px solid #f8f9fa; transition: background 0.2s; <?php echo $tem_novidade ? 'background: #fff8f8;' : ''; ?>" 
                            onmouseover="this.style.background='<?php echo $tem_novidade ? '#fef2f2' : '#f9fafb'; ?>'" 
                            onmouseout="this.style.background='<?php echo $tem_novidade ? '#fff8f8' : 'transparent'; ?>'">
                            
                            <td style="padding: 18px 25px; font-weight: bold; color: #0C2D54; position: relative;">
                                <?php if ($tem_novidade): ?>
                                    <span class="dot-user-alerta pulse-red" title="Nova resposta do suporte"></span>
                                <?php endif; ?>
                                #<?php echo $chamado['id']; ?>
                            </td>
                            
                            <td style="padding: 18px 12px;">
                                <div style="font-weight: <?php echo $tem_novidade ? '800' : '600'; ?>; color: #1c1e21; font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($chamado['assunto']); ?>
                                </div>
                                <span style="font-size: 0.7rem; color: #90949c;"><?php echo htmlspecialchars($chamado['categoria']); ?></span>
                            </td>
                            
                            <td style="padding: 18px 12px;">
                                <?php echo getStatusBadge($chamado['status']); ?>
                            </td>
                            
                            <td style="padding: 18px 12px; color: #606770; font-size: 0.8rem;">
                                <?php 
                                    $data = !empty($chamado['ultima_atividade']) ? $chamado['ultima_atividade'] : $chamado['data_criacao'];
                                    echo date('d/m/Y', strtotime($data)) . '<br><small>' . date('H:i', strtotime($data)) . '</small>'; 
                                ?>
                            </td>
                            
                            <td style="padding: 18px 25px; text-align: right;">
                                <a href="<?php echo $config['base_path']; ?>suporte/ver/<?php echo $chamado['id']; ?>" 
                                   class="primary-btn-small" 
                                   style="padding: 8px 16px; font-size: 0.75rem; font-weight: bold; background: #0C2D54; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
                                    <?php echo $tem_novidade ? '<i class="fas fa-comment-dots"></i> LER RESPOSTA' : 'VER'; ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
/* Estilo do Indicador de Novidade em Vermelho */
.dot-user-alerta {
    position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    width: 8px;
    height: 8px;
    background-color: #ef4444;
    border-radius: 50%;
    display: inline-block;
}
.pulse-red {
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
    animation: pulse-red-effect 1.5s infinite;
}
@keyframes pulse-red-effect {
    0% { transform: translateY(-50%) scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
    70% { transform: translateY(-50%) scale(1.1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
    100% { transform: translateY(-50%) scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}

/* Scrollbar fina para a lista */
.suporte-historico-wrapper div::-webkit-scrollbar { width: 6px; }
.suporte-historico-wrapper div::-webkit-scrollbar-track { background: transparent; }
.suporte-historico-wrapper div::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }
</style>