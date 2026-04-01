<?php
/**
 * views/chat/componentes/ver_midia_conversa.php
 * Sub-componente Atómico: Galeria de Mídia Local da Conversa.
 * PAPEL: Exibir histórico de fotos, vídeos e áudios do chat atual.
 * VERSÃO: V1.2 (Fix: Integração com chatLightbox Specialist - socialbr.lol)
 */

// Proteção de acesso direto
if (!isset($conversa_id)) {
    exit("Erro: Contexto de conversa não identificado.");
}

// 1. Obtenção dos Dados via Cérebro (ChatLogic)
$midias = ChatLogic::getConversationMedia($conn, $conversa_id);

// 2. Agrupamento Lógico por Tipo
$fotos = [];
$videos = [];
$audios = [];

foreach ($midias as $item) {
    if ($item['tipo_midia'] === 'foto') $fotos[] = $item;
    elseif ($item['tipo_midia'] === 'video') $videos[] = $item;
    elseif ($item['tipo_midia'] === 'audio') $audios[] = $item;
}

// Título dinâmico baseado no contexto
$titulo_contexto = isset($grupo_titulo) ? $grupo_titulo : ($outro_usuario_nome ?? 'Conversa');
?>

<div class="chat-media-gallery-wrapper">
    <header class="media-gallery-header">
        <button type="button" class="back-to-chat" onclick="chatAcoes.toggleRightSidebar(false)" title="Voltar para a conversa">
            <i class="fas fa-chevron-left"></i>
        </button>
        <div class="header-info">
            <h2>Mídias da Conversa</h2>
            <span><?= htmlspecialchars($titulo_contexto) ?></span>
        </div>
    </header>

    <nav class="media-tabs">
        <button class="media-tab-btn active" data-target="tab-fotos">
            <i class="fas fa-image"></i> Fotos (<?= count($fotos) ?>)
        </button>
        <button class="media-tab-btn" data-target="tab-videos">
            <i class="fas fa-video"></i> Vídeos (<?= count($videos) ?>)
        </button>
        <button class="media-tab-btn" data-target="tab-audios">
            <i class="fas fa-microphone"></i> Áudios (<?= count($audios) ?>)
        </button>
    </nav>

    <div class="media-content-area">
        
        <section id="tab-fotos" class="media-pane active">
            <?php if (empty($fotos)): ?>
                <div class="media-empty">Nenhuma foto compartilhada.</div>
            <?php else: ?>
                <div class="media-grid-photos">
                    <?php foreach ($fotos as $foto): ?>
                        <?php $full_image_url = $config['base_path'] . $foto['midia_url']; ?>
                        <div class="media-item photo-item" onclick="if(window.chatLightbox) chatLightbox.open('<?= $full_image_url ?>')">
                            <img src="<?= $full_image_url ?>" alt="Mídia" loading="lazy">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section id="tab-videos" class="media-pane">
            <?php if (empty($videos)): ?>
                <div class="media-empty">Nenhum vídeo compartilhado.</div>
            <?php else: ?>
                <div class="media-grid-videos">
                    <?php foreach ($videos as $video): ?>
                        <div class="media-item video-item">
                            <video src="<?= $config['base_path'] . $video['midia_url'] ?>"></video>
                            <div class="video-overlay" onclick="chatAcoes.playGalleryVideo('<?= $config['base_path'] . $video['midia_url'] ?>')">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section id="tab-audios" class="media-pane">
            <?php if (empty($audios)): ?>
                <div class="media-empty">Nenhum áudio compartilhado.</div>
            <?php else: ?>
                <div class="media-list-audios">
                    <?php foreach ($audios as $audio): ?>
                        <div class="audio-card">
                            <div class="audio-info">
                                <i class="fas fa-music"></i>
                                <div>
                                    <p>Enviado por <?= htmlspecialchars($audio['remetente_nome']) ?></p>
                                    <span><?= date('d/m/Y - H:i', strtotime($audio['criado_em'])) ?></span>
                                </div>
                            </div>
                            <audio controls src="<?= $config['base_path'] . $audio['midia_url'] ?>"></audio>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </div>
</div>