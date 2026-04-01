/**
 * assets/js/mkt_criar.js
 * Versão 4.5: Envio Seguro com CSRF Token + Integração SweetAlert2 (Premium)
 * PAPEL: Gestão de criação de anúncios e feedback visual de luxo.
 * socialbr.lol
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // ==========================================
    // 1. MAPEAMENTO DE ELEMENTOS (DOM CACHE)
    // ==========================================
    const els = {
        form: document.getElementById('form-criar-anuncio'),
        inputs: {
            titulo: document.getElementById('inputTitulo'),
            preco: document.getElementById('inputPreco'),
            descricao: document.getElementById('inputDescricao'),
            condicao: document.getElementById('inputCondicao'),
            categoria: document.getElementById('inputCategoria'),
            estado: document.getElementById('inputEstado'),
            cidade: document.getElementById('inputCidade'),
            cpf: document.getElementById('inputCpf')
        },
        dropZone: document.getElementById('drop-zone'),
        inputFotos: document.getElementById('inputFotos'),
        miniaturesGrid: document.getElementById('preview-miniatures'),
        
        // Outputs (Preview no Painel Lateral)
        card: {
            img: document.getElementById('preview-img-main'),
            placeholder: document.getElementById('preview-placeholder-container'),
            titulo: document.getElementById('preview-text-titulo'),
            preco: document.getElementById('preview-text-preco'),
            cidade: document.getElementById('preview-text-cidade'),
            badgeLocal: document.getElementById('preview-badge-local'),
            badgeStatus: document.getElementById('preview-badge-status'),
            // Detalhes extras
            detCategoria: document.getElementById('preview-detail-categoria'),
            detCondicao: document.getElementById('preview-detail-condicao'),
            detDesc: document.getElementById('preview-detail-desc'),
            btnPrev: document.getElementById('preview-btn-prev'),
            btnNext: document.getElementById('preview-btn-next')
        }
    };

    let uploadedImages = []; // Armazena os arquivos reais (File Objects)
    let currentPreviewIndex = 0;

    // ==========================================
    // 2. LÓGICA DE UPLOAD E MINIATURAS
    // ==========================================

    // FIX: Abre o explorador apenas uma vez (Trata o clique na zona)
    if (els.dropZone && els.inputFotos) {
        els.dropZone.addEventListener('click', (e) => {
            if (e.target !== els.inputFotos) {
                els.inputFotos.click();
            }
        });
    }

    if (els.inputFotos) {
        els.inputFotos.addEventListener('change', function(e) {
            handleFiles(Array.from(e.target.files));
            els.inputFotos.value = ''; 
        });
    }

    function handleFiles(files) {
        files.forEach(file => {
            if (uploadedImages.length < 10) {
                uploadedImages.push(file);
                renderMiniatures();
            }
        });
        updatePreviewImages();
    }

    function renderMiniatures() {
        if (!els.miniaturesGrid) return;
        els.miniaturesGrid.innerHTML = '';

        uploadedImages.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'mkt-miniature-item';
                
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'miniature-img-form';

                const btnRemove = document.createElement('button');
                btnRemove.innerHTML = '&times;';
                btnRemove.className = 'btn-remove-photo';
                btnRemove.onclick = (event) => {
                    event.stopPropagation();
                    removeFile(index);
                };

                wrapper.appendChild(img);
                wrapper.appendChild(btnRemove);
                els.miniaturesGrid.appendChild(wrapper);
            };
            reader.readAsDataURL(file);
        });
    }

    function removeFile(index) {
        uploadedImages.splice(index, 1);
        renderMiniatures();
        updatePreviewImages();
    }

    // ==========================================
    // 3. ATUALIZAÇÃO DO PREVIEW (LIVE)
    // ==========================================

    function updatePreviewImages() {
        if (uploadedImages.length > 0) {
            els.card.placeholder.style.display = 'none';
            els.card.img.style.display = 'block';
            
            const reader = new FileReader();
            reader.onload = (e) => {
                els.card.img.src = e.target.result;
            };
            reader.readAsDataURL(uploadedImages[currentPreviewIndex]);

            const showNav = uploadedImages.length > 1;
            els.card.btnPrev.style.display = showNav ? 'flex' : 'none';
            els.card.btnNext.style.display = showNav ? 'flex' : 'none';
        } else {
            els.card.placeholder.style.display = 'flex';
            els.card.img.style.display = 'none';
            els.card.btnPrev.style.display = 'none';
            els.card.btnNext.style.display = 'none';
        }
    }

    els.card.btnNext?.addEventListener('click', () => {
        currentPreviewIndex = (currentPreviewIndex + 1) % uploadedImages.length;
        updatePreviewImages();
    });

    els.card.btnPrev?.addEventListener('click', () => {
        currentPreviewIndex = (currentPreviewIndex - 1 + uploadedImages.length) % uploadedImages.length;
        updatePreviewImages();
    });

    const syncText = (input, output, fallback) => {
        if (!input || !output) return;
        input.addEventListener('input', () => {
            output.innerText = input.value.trim() || fallback;
        });
    };

    syncText(els.inputs.titulo, els.card.titulo, 'Título do anúncio');
    syncText(els.inputs.descricao, els.card.detDesc, 'A descrição que você digitar aparecerá aqui...');
    
    els.inputs.preco?.addEventListener('input', (e) => {
        let val = e.target.value.replace(/\D/g, '');
        if (val) {
            val = (parseFloat(val) / 100).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            els.card.preco.innerText = val;
        } else {
            els.card.preco.innerText = 'R$ 0,00';
        }
    });

    const updateLocation = () => {
        const cidade = els.inputs.cidade.value.trim();
        const estado = els.inputs.estado.value;
        if (cidade || estado) {
            els.card.cidade.innerText = `${cidade}${cidade && estado ? ', ' : ''}${estado}`;
            els.card.badgeLocal.style.display = 'block';
        } else {
            els.card.badgeLocal.style.display = 'none';
        }
    };
    els.inputs.cidade?.addEventListener('input', updateLocation);
    els.inputs.estado?.addEventListener('change', updateLocation);

    els.inputs.categoria?.addEventListener('change', (e) => {
        const text = e.target.options[e.target.selectedIndex].text;
        els.card.detCategoria.innerText = e.target.value ? text : '--';
    });

    els.inputs.condicao?.addEventListener('change', (e) => {
        const text = e.target.options[e.target.selectedIndex].text;
        els.card.detCondicao.innerText = e.target.value ? text : '--';
    });

    // ==========================================
    // 4. ENVIO DO FORMULÁRIO (AJAX + SweetAlert2)
    // ==========================================
    els.form?.addEventListener('submit', async function(e) {
        e.preventDefault();

        const btn = els.form.querySelector('.btn-submit-mkt-final');
        if (!btn) return;

        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = 'Publicando...';

        try {
            const formData = new FormData(els.form);
            
            formData.delete('fotos[]');
            uploadedImages.forEach(file => {
                formData.append('fotos[]', file);
            });

            // 🔒 BLINDAGEM CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                formData.append('csrf_token', csrfToken);
            } else {
                throw new Error("Erro de Segurança: Token CSRF não encontrado. Atualize a página.");
            }

            const apiUrl = (typeof BASE_PATH !== 'undefined') 
                ? BASE_PATH + 'api/marketplace/criar_anuncio.php'
                : '../api/marketplace/criar_anuncio.php';

            const response = await fetch(apiUrl, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Alerta de Sucesso Premium
                Swal.fire({
                    icon: 'success',
                    title: 'Publicado!',
                    text: 'Anúncio publicado com sucesso no Marketplace.',
                    confirmButtonColor: '#0C2D54'
                }).then(() => {
                    const redirectUrl = (typeof BASE_PATH !== 'undefined') 
                        ? BASE_PATH + 'marketplace' 
                        : (result.redirect || 'marketplace');
                    window.location.href = redirectUrl;
                });
            } else {
                // Alerta de Erro de Servidor Premium
                Swal.fire({
                    icon: 'error',
                    title: 'Falha na Publicação',
                    text: result.message || 'Erro ao publicar seu anúncio.',
                    confirmButtonColor: '#0C2D54'
                });
            }

        } catch (err) {
            console.error(err);
            // Alerta de Erro Técnico/Rede Premium
            Swal.fire({
                icon: 'error',
                title: 'Erro de Ligação',
                text: err.message || 'Ocorreu um erro ao conectar ao servidor.',
                confirmButtonColor: '#d33'
            });
        } finally {
            btn.disabled = false;
            btn.innerText = originalText;
        }
    });
});