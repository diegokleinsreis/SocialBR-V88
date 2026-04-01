<?php
/**
 * views/perfil/capa_perfil.php
 * Componente: Seção da Foto de Capa.
 * PAPEL: Exibir a imagem de cobertura e prover controles de edição.
 * VERSÃO: V2.2 (Reversion: Estabilização de Rede & Path Fix - socialbr.lol)
 */

// Preparação da URL da capa com fallback seguro
$capa_url = !empty($perfil_data['foto_capa_url']) 
    ? $config['base_path'] . htmlspecialchars($perfil_data['foto_capa_url']) 
    : $config['base_path'] . 'assets/images/default-cover.jpg';
?>

<style>
    /* Estrutura da Seção de Capa */
    .profile-cover-section {
        background-image: url('<?php echo $capa_url; ?>');
        width: 100%;
        height: 250px; 
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center center;
        position: relative;
        border-bottom: 4px solid #fff;
        box-shadow: inset 0 -60px 100px -20px rgba(0,0,0,0.5);
        border-radius: 12px 12px 0 0;
        overflow: hidden;
        transition: background-image 0.5s ease;
    }

    .cover-overlay-top {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 40%;
        background: linear-gradient(to bottom, rgba(0,0,0,0.4), transparent);
        pointer-events: none;
    }

    /* Posicionamento dos botões (Topo Direito) */
    .cover-upload-wrapper {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 15;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .change-cover-btn {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        color: #fff;
        padding: 10px 18px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        height: 40px;
    }

    .change-cover-btn:hover {
        background: rgba(255, 255, 255, 0.35);
        transform: translateY(-2px);
    }

    .remove-cover-btn {
        background: rgba(220, 53, 69, 0.25) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(220, 53, 69, 0.5) !important;
        color: #ffcccc !important;
        width: 40px;
        height: 40px;
        border-radius: 10px !important;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .remove-cover-btn:hover {
        background: rgba(220, 53, 69, 0.8) !important;
        color: #fff !important;
    }

    @media (max-width: 768px) {
        .profile-cover-section { height: 180px; }
        .btn-text { display: none; }
        .change-cover-btn { width: 40px; padding: 0; justify-content: center; }
    }
</style>

<div class="profile-cover-section" id="profile-cover-display">
    <div class="cover-overlay-top"></div>

    <?php if ($is_own_profile): ?>
        <div class="cover-upload-wrapper">
            <button type="button" class="change-cover-btn" id="btn-trigger-upload">
                <i class="fas fa-camera"></i> 
                <span class="btn-text">Alterar Capa</span>
            </button>
            
            <?php if (!empty($perfil_data['foto_capa_url'])): ?>
                <button type="button" class="remove-cover-btn" title="Remover Capa" id="btn-remover-capa-ajax">
                    <i class="fas fa-trash"></i>
                </button>
            <?php endif; ?>
        </div>

        <input type="file" id="hub-cover-input" style="display: none;" accept="image/*">

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const fileInput = document.getElementById('hub-cover-input');
                const btnUpload = document.getElementById('btn-trigger-upload');
                const btnRemover = document.getElementById('btn-remover-capa-ajax');
                const coverDisplay = document.getElementById('profile-cover-display');

                // 1. GATILHO DO UPLOAD
                if (btnUpload) btnUpload.addEventListener('click', () => fileInput.click());

                // 2. LÓGICA DE UPLOAD
                fileInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (!file) return;

                    // Filtro de Segurança BMP (Local)
                    const forbiddenTypes = ['image/bmp', 'image/x-ms-bmp'];
                    if (forbiddenTypes.includes(file.type) || file.name.toLowerCase().endsWith('.bmp')) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Formato não suportado',
                            text: 'Arquivos .BMP são muito pesados. Use JPG, PNG ou WebP.',
                            confirmButtonColor: '#0C2D54'
                        });
                        this.value = '';
                        return;
                    }

                    // UI Loading
                    btnUpload.disabled = true;
                    btnUpload.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    if (typeof NProgress !== 'undefined') NProgress.start();

                    const formData = new FormData();
                    formData.append('foto_capa', file);

                    fetch('<?php echo $config['base_path']; ?>api/usuarios/upload_capa.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) throw new Error(`Falha no servidor (${response.status})`);
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Sucesso!', 'Capa atualizada com sucesso.', 'success')
                            .then(() => window.location.reload());
                        } else {
                            throw new Error(data.erro || 'Erro ao processar imagem.');
                        }
                    })
                    .catch(err => {
                        console.error('Erro Upload:', err.message);
                        Swal.fire('Erro', err.message, 'error');
                    })
                    .finally(() => {
                        btnUpload.disabled = false;
                        btnUpload.innerHTML = '<i class="fas fa-camera"></i> <span class="btn-text">Alterar Capa</span>';
                        if (typeof NProgress !== 'undefined') NProgress.done();
                    });
                });

                // 3. LÓGICA DE REMOÇÃO
                if (btnRemover) {
                    btnRemover.addEventListener('click', function() {
                        Swal.fire({
                            title: 'Remover Capa?',
                            text: "Sua foto de capa voltará ao padrão do sistema.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#0C2D54',
                            confirmButtonText: 'Sim, remover!',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Feedback de carregamento no botão
                                btnRemover.disabled = true;
                                btnRemover.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                                fetch('<?php echo $config['base_path']; ?>api/usuarios/remover_capa.php', { 
                                    method: 'POST' 
                                })
                                .then(res => {
                                    if (!res.ok) throw new Error(`Erro de rede (${res.status})`);
                                    return res.json();
                                })
                                .then(data => {
                                    if (data.success) {
                                        coverDisplay.style.backgroundImage = "url('<?php echo $config['base_path']; ?>assets/images/default-cover.jpg')";
                                        btnRemover.remove();
                                        Swal.fire('Removido!', 'Sua capa foi removida.', 'success');
                                    } else {
                                        throw new Error(data.erro || 'Falha ao remover capa.');
                                    }
                                })
                                .catch(err => {
                                    console.error('Erro Remoção:', err.message);
                                    Swal.fire('Erro', err.message, 'error');
                                    btnRemover.disabled = false;
                                    btnRemover.innerHTML = '<i class="fas fa-trash"></i>';
                                });
                            }
                        });
                    });
                }
            });
        </script>
    <?php endif; ?>
</div>