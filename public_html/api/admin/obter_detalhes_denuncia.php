<?php
/**
 * API Admin: Obter Detalhes da Denúncia (V96.2)
 * OBJETIVO: Gerar HTML compatível com o design do Feed para moderação.
 * SUPORTE: Múltiplas Mídias, Link Preview e Enquetes (Modo Resultados).
 */

require_once __DIR__ . '/../../admin/admin_auth.php'; // Garante segurança admin

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID da denúncia não fornecido.']);
    exit;
}

$denuncia_id = intval($_GET['id']);

/**
 * FUNÇÃO: Renderiza a estrutura de um Post para o Admin
 * Utiliza as classes .post-card e .post-content para ativar o CSS global.
 */
function renderPostForAdmin($conn, $post_id, $base_path) {
    
    // 1. Busca dados do post e autor
    $sql = "SELECT p.*, u.nome, u.sobrenome, u.foto_perfil_url 
            FROM Postagens p 
            JOIN Usuarios u ON p.id_usuario = u.id 
            WHERE p.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $post = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$post) return '<p class="admin-error-msg">A postagem original foi excluída ou não existe.</p>';

    // --- INÍCIO DO CARD (Classe post-card ativa o CSS do Feed) ---
    $html = '<div class="post-card" style="box-shadow:none; border:1px solid #ddd; margin-bottom:10px;">';
    
    $avatar = !empty($post['foto_perfil_url']) ? $base_path . htmlspecialchars($post['foto_perfil_url']) : $base_path . 'assets/images/default-avatar.png';
    
    $html .= '<div class="post-header">
                <div class="post-author-avatar"><img src="'.$avatar.'"></div>
                <div class="post-author-info">
                    <span class="post-author-name">'.htmlspecialchars($post['nome'].' '.$post['sobrenome']).'</span>
                    <span class="post-timestamp">Publicado em: '.date("d/m/Y H:i", strtotime($post['data_postagem'])).'</span>
                </div>
              </div>';

    $html .= '<div class="post-content"><p>'.nl2br(htmlspecialchars($post['conteudo_texto'])).'</p></div>';

    // --- A. LINKS (Post_Meta) ---
    $sql_meta = "SELECT meta_key, meta_value FROM Post_Meta WHERE post_id = ?";
    $stmt_m = $conn->prepare($sql_meta);
    $stmt_m->bind_param("i", $post['id']);
    $stmt_m->execute();
    $res_m = $stmt_m->get_result();
    $link = [];
    while($m = $res_m->fetch_assoc()) { $link[$m['meta_key']] = $m['meta_value']; }
    $stmt_m->close();

    if (!empty($link['link_url'])) {
        $html .= '<div class="post-link-preview" style="pointer-events: none;">
                    <div class="link-preview-wrapper">';
        if (!empty($link['link_image'])) $html .= '<div class="lp-image"><img src="'.$link['link_image'].'"></div>';
        $html .= '    <div class="lp-body">
                        <h4>'.htmlspecialchars($link['link_title']).'</h4>
                        <p>'.htmlspecialchars($link['link_desc']).'</p>
                        <small><i class="fas fa-link"></i> '.strtoupper(parse_url($link['link_url'], PHP_URL_HOST)).'</small>
                      </div>
                    </div>
                  </div>';
    }

    // --- B. ENQUETE (Sempre em Modo Resultados para o Admin) ---
    $sql_enq = "SELECT id, pergunta FROM Enquetes WHERE post_id = ?";
    $stmt_e = $conn->prepare($sql_enq);
    $stmt_e->bind_param("i", $post['id']);
    $stmt_e->execute();
    $enquete = $stmt_e->get_result()->fetch_assoc();
    $stmt_e->close();

    if ($enquete) {
        // Busca total de votos da enquete
        $sql_v = "SELECT COUNT(*) as total FROM Enquete_Votos ev JOIN Enquete_Opcoes eo ON ev.opcao_id = eo.id WHERE eo.enquete_id = ?";
        $st_v = $conn->prepare($sql_v); $st_v->bind_param("i", $enquete['id']); $st_v->execute();
        $total_geral = $st_v->get_result()->fetch_assoc()['total'] ?? 0;
        $st_v->close();

        $html .= '<div class="post-poll-container">
                    <div class="poll-question-title">'.htmlspecialchars($enquete['pergunta']).'</div>
                    <div class="poll-options-list">';
        
        // Busca cada opção e calcula percentagem
        $sql_o = "SELECT id, opcao_texto, (SELECT COUNT(*) FROM Enquete_Votos WHERE opcao_id = Enquete_Opcoes.id) as votos FROM Enquete_Opcoes WHERE enquete_id = ?";
        $st_o = $conn->prepare($sql_o); $st_o->bind_param("i", $enquete['id']); $st_o->execute();
        $res_o = $st_o->get_result();
        while($o = $res_o->fetch_assoc()) {
            $pct = ($total_geral > 0) ? round(($o['votos'] / $total_geral) * 100) : 0;
            $html .= '<div class="poll-option-item">
                        <div class="poll-bar-bg"><div class="poll-bar-fill" style="width: '.$pct.'%"></div></div>
                        <div class="poll-option-info"><span>'.htmlspecialchars($o['opcao_texto']).'</span><strong>'.$pct.'%</strong></div>
                      </div>';
        }
        $st_o->close();
        $html .= '  </div><div class="poll-footer"><small>'.$total_geral.' votos computados nesta enquete.</small></div></div>';
    }

    // --- C. MÍDIAS (Imagens/Vídeos) ---
    $sql_midia = "SELECT url_midia, tipo_midia FROM Postagens_Midia WHERE id_postagem = ?";
    $st_mid = $conn->prepare($sql_midia);
    $st_mid->bind_param("i", $post['id']);
    $st_mid->execute();
    $midias = $st_mid->get_all_rows ?? []; // Fallback se fetch_all não estiver ativo
    $res_midia = $st_mid->get_result();
    
    if ($res_midia->num_rows > 0) {
        $html .= '<div class="post-media-grid grid-'.$res_midia->num_rows.'" style="margin-top:10px;">';
        while($m = $res_midia->fetch_assoc()) {
            $src = $base_path . htmlspecialchars($m['url_midia']);
            $html .= '<div class="media-item">';
            if($m['tipo_midia'] == 'imagem') $html .= '<img src="'.$src.'">';
            else $html .= '<video src="'.$src.'"></video>';
            $html .= '</div>';
        }
        $html .= '</div>';
    }
    $st_mid->close();

    $html .= '</div>'; // Fecha post-card
    return $html;
}

// --- 2. EXECUÇÃO PRINCIPAL DA API ---

$sql_d = "SELECT * FROM Denuncias WHERE id = ?";
$stmt_d = $conn->prepare($sql_d);
$stmt_d->bind_param("i", $denuncia_id);
$stmt_d->execute();
$denuncia = $stmt_d->get_result()->fetch_assoc();
$stmt_d->close();

if (!$denuncia) {
    echo json_encode(['success' => false, 'message' => 'Denúncia não encontrada.']);
    exit;
}

$html_content = '';
$post_id_ref = null;
$base_path = $config['base_path'];

if ($denuncia['tipo_conteudo'] === 'post') {
    $html_content .= renderPostForAdmin($conn, $denuncia['id_conteudo'], $base_path);
    $post_id_ref = $denuncia['id_conteudo'];
} 
elseif ($denuncia['tipo_conteudo'] === 'comentario') {
    // Busca o comentário e o post de contexto
    $sql_c = "SELECT c.*, u.nome, u.sobrenome FROM Comentarios c JOIN Usuarios u ON c.id_usuario = u.id WHERE c.id = ?";
    $st_c = $conn->prepare($sql_c);
    $st_c->bind_param("i", $denuncia['id_conteudo']);
    $st_c->execute();
    $com = $st_c->get_result()->fetch_assoc();
    $st_c->close();

    if ($com) {
        $post_id_ref = $com['id_postagem'];
        $html_content .= '<h5 style="margin-bottom:8px; color:#555; font-size:14px;">Contexto da Publicação Original:</h5>';
        $html_content .= renderPostForAdmin($conn, $com['id_postagem'], $base_path);
        
        $html_content .= '<div class="denuncia-comment-box" style="margin-top:15px; padding:12px; background:#fff1f1; border:1px solid #ffcccc; border-radius:8px;">';
        $html_content .= '  <strong style="color:#d32f2f;"><i class="fas fa-comment"></i> Comentário Denunciado:</strong>';
        $html_content .= '  <p style="margin-top:8px; font-style:italic;">"'.nl2br(htmlspecialchars($com['conteudo_texto'])).'"</p>';
        $html_content .= '  <small style="color:#666;">Autor: '.$com['nome'].' '.$com['sobrenome'].'</small>';
        $html_content .= '</div>';
    }
}

echo json_encode([
    'success' => true, 
    'html' => $html_content, 
    'denuncia' => $denuncia, 
    'post_id_referencia' => $post_id_ref
]);

$conn->close();