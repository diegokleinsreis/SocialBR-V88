<?php
/**
 * Template para o cabeçalho das páginas de perfil.
 * VERSÃO INTEGRAL CORRIGIDA (V64.7 - Fix Syntax & Config Redirect).
 */

// Garante que $config (com $config['base_path']) esteja disponível.
if (!isset($config)) {
    require_once __DIR__ . '/../config/database.php';
}

// Lógica de Status Online (Mantida Integralmente)
if (!function_exists('formatar_status_online')) {
    function formatar_status_online($ultimo_acesso_timestamp) {
        if ($ultimo_acesso_timestamp === null) return null;
        try {
            $fuso_horario = new DateTimeZone('America/Sao_Paulo');
            $agora = new DateTime("now", $fuso_horario);
            $ultimo_acesso = new DateTime($ultimo_acesso_timestamp, $fuso_horario);
            $diferenca_em_minutos = floor(($agora->getTimestamp() - $ultimo_acesso->getTimestamp()) / 60);
            if ($diferenca_em_minutos < 5) {
                return '<span class="status-dot status-online" title="Online"></span>';
            }
            return null; 
        } catch (Exception $e) {
            error_log("Erro ao formatar data ultimo_acesso: " . $e->getMessage());
            return null;
        }
    }
}

// Lógica de Capa Padrão
$foto_capa = !empty($perfil_data['foto_capa_url']) 
    ? $config['base_path'] . htmlspecialchars($perfil_data['foto_capa_url']) 
    : $config['base_path'] . 'assets/images/default-cover.jpg';

$posicao_y = isset($perfil_data['capa_posicao_y']) ? (int)$perfil_data['capa_posicao_y'] : 50;
?>

<div class="profile-header-container">

    <div class="profile-cover-section" style="background-image: url('<?php echo $foto_capa; ?>'); background-position: center <?php echo $posicao_y; ?>%;">
        
        <div class="cover-overlay"></div>

        <?php if ($id_usuario_logado == $id_do_perfil_a_exibir): ?>
            <div class="cover-upload-wrapper">
                <a href="<?php echo $config['base_path']; ?>configurar_perfil#visual-hub" class="change-cover-btn" style="text-decoration: none; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-camera"></i> <span>Alterar Capa</span>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="profile-page-header">
        
        <?php
        $avatar_url = !empty($perfil_data['foto_perfil_url'])
            ? $config['base_path'] . htmlspecialchars($perfil_data['foto_perfil_url'])
            : null;

        $trigger_class = $avatar_url ? 'post-image-clickable' : '';
        ?>

        <div class="profile-avatar-large <?php echo $trigger_class; ?>" 
             <?php if ($avatar_url): ?>
                data-media-url="<?php echo $avatar_url; ?>" 
                data-postid="0" 
                data-media-index="0"
                style="cursor: pointer; position: relative; z-index: 20;"
             <?php endif; ?>>
            
            <?php if ($avatar_url): ?>
                <img src="<?php echo $avatar_url; ?>" alt="Foto de Perfil">
            <?php else: ?>
                <i class="fas fa-user"></i>
            <?php endif; ?>
        </div>
        
        <div class="profile-header-info">
            
            <h1>
                <?php echo htmlspecialchars($perfil_data['nome'] . ' ' . $perfil_data['sobrenome']); ?>
                <?php
                if ( (!isset($eu_bloqueie) || !$eu_bloqueie) && (($id_usuario_logado == $id_do_perfil_a_exibir) || $sao_amigos) ) {
                    echo formatar_status_online($perfil_data['ultimo_acesso'] ?? null);
                }
                ?>
            </h1>
            
            <p>@<?php echo htmlspecialchars($perfil_data['nome_de_usuario']); ?></p>

            <?php
            if ( (!isset($eu_bloqueie) || !$eu_bloqueie) && !empty($perfil_data['biografia'])): 
                $limite_caracteres = 100;
                $biografia_completa = $perfil_data['biografia'];
                $biografia_curta = $biografia_completa;
                $precisa_ver_mais = false;

                if (mb_strlen($biografia_completa) > $limite_caracteres) {
                    $biografia_curta = mb_strimwidth($biografia_completa, 0, $limite_caracteres, "...");
                    $precisa_ver_mais = true;
                }
            ?>
                <p class="profile-bio">
                    <?php echo nl2br(htmlspecialchars($biografia_curta)); ?>
                    <?php if ($precisa_ver_mais): ?>
                        <a href="<?php echo $config['base_path']; ?>perfil/<?php echo $id_do_perfil_a_exibir; ?>?tab=sobre">ver mais</a>
                    <?php endif; ?>
                </p>
            <?php endif; ?>

        </div>
        
        <div class="profile-header-actions">
            <?php if ($id_do_perfil_a_exibir != $id_usuario_logado): ?>
            
                <?php
                switch ($status_amizade) {
                    case 'bloqueado':
                        break;
                    
                    case 'aceite':
                        echo '<a href="#" class="action-btn-friends cancelar-amizade-btn" data-amizade-id="' . $amizade_id . '">';
                        echo '<i class="fas fa-user-check"></i> Amigos';
                        echo '</a>';
                        break;

                    case 'pendente':
                        if ($id_remetente_pedido == $id_usuario_logado) {
                            echo '<div class="friend-actions-dropdown">';
                            echo '<button class="action-btn-pending friend-dropdown-toggle"><i class="fas fa-user-clock"></i> Pedido Pendente</button>';
                            echo '<div class="dropdown-content">'; 
                            echo '<a href="#" class="cancelar-pedido-btn" data-amizade-id="' . $amizade_id . '"><i class="fas fa-times-circle"></i> Cancelar Pedido</a>';
                            echo '</div>';
                            echo '</div>';
                        } else {
                            echo '<div class="friend-actions-dropdown">';
                            echo '<button class="action-btn-respond friend-dropdown-toggle"><i class="fas fa-user-plus"></i> Responder Pedido</button>';
                            echo '<div class="dropdown-content">';
                            echo '<a href="#" class="aceitar-pedido-btn" data-amizade-id="' . $amizade_id . '">Aceitar</a>';
                            echo '<a href="#" class="recusar-pedido-btn" data-amizade-id="' . $amizade_id . '">Recusar</a>';
                            echo '</div>';
                            echo '</div>';
                        }
                        break;
                    
                    default:
                        echo '<button class="action-btn-add" id="add-friend-btn" data-destinatario-id="' . $id_do_perfil_a_exibir . '"><i class="fas fa-user-plus"></i> Adicionar Amigo</button>';
                        break;
                }
                ?>

                <div class="profile-header-options post-options">
                    <button class="post-options-btn"><i class="fas fa-ellipsis-h"></i></button>
                    <div class="post-options-menu is-hidden">
                        <?php if (isset($eu_bloqueie) && $eu_bloqueie): ?>
                            <a href="#" class="bloquear-usuario-btn" data-usuario-id="<?php echo $id_do_perfil_a_exibir; ?>" data-acao="desbloquear">
                                <i class="fas fa-check-circle"></i> Desbloquear Usuário
                            </a>
                        <?php else: ?>
                            <a href="#" class="report-btn" data-content-type="usuario" data-content-id="<?php echo $id_do_perfil_a_exibir; ?>"><i class="fas fa-flag"></i> Denunciar Perfil</a>
                            <a href="#" class="bloquear-usuario-btn" data-usuario-id="<?php echo $id_do_perfil_a_exibir; ?>" data-acao="bloquear">
                                <i class="fas fa-ban"></i> Bloquear Usuário
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

            <?php else: ?>
                <a href="<?php echo $config['base_path']; ?>configurar_perfil" class="action-btn-edit">
                    <i class="fas fa-edit"></i> Editar Perfil
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>