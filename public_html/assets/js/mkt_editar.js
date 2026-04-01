/**
 * assets/js/mkt_editar.js
 * Versão 3.2: Preview Multi-Fotos + BLINDAGEM CSRF + Integração SweetAlert2
 * PAPEL: Gestão de edição de anúncios e feedback visual premium.
 * socialbr.lol
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // 1. MAPEAMENTO DE ELEMENTOS
    const els = {
        form: document.getElementById('form-editar-anuncio'),
        inputs: {
            titulo: document.getElementById('inputTitulo'),
            preco: document.getElementById('inputPreco'),
            descricao: document.getElementById('inputDescricao'),
            condicao: document.getElementById('inputCondicao'),
            categoria: document.getElementById('inputCategoria'),
            estado: document.getElementById('inputEstado'),
            cidade: document.getElementById('inputCidade')
        },
        inputFotosNew: document.getElementById('inputFotosEdit'),
        miniaturesNewGrid: document.getElementById('preview-miniatures-new'),
        // Preview Card
        card: {
            imgMain: document.getElementById('preview-img-main'),
            placeholder: document.getElementById('preview-placeholder-container'),
            titulo: document.getElementById('preview-text-titulo'),
            preco: document.getElementById('preview-text-preco'),
            local: document.getElementById('preview-text-cidade'),
            badgeLocal: document.getElementById('preview-badge-local'),
            estadoBadge: document.getElementById('preview-text-estado'),
            btnPrev: document.getElementById('preview-btn-prev'),
            btnNext: document.getElementById('preview-btn-next')
        },
        detail: {
            categoria: document.getElementById('preview-detail-categoria'),
            condicao: document.getElementById('preview-detail-condicao'),
            desc: document.getElementById('preview-detail-desc')
        }
    };

    // ESTADO DO CARROSSEL
    let newImages = []; // Ficheiros File objects
    let gallerySources = []; // Array de strings (URLs ou DataURLs)
    let currentPhotoIndex = 0;

    // 2. INICIALIZAÇÃO
    function init() {
        if (!els.form) return;
        setupListeners();
        updateLivePreview();
        refreshGallery(); // Reconstrói galeria inicial com fotos do banco
    }

    // 3. LÓGICA DO CARROSSEL
    function refreshGallery() {
        gallerySources = [];
        
        // A. Pega fotos atuais do banco que não foram removidas visualmente
        const existingImgs = document.querySelectorAll('.miniature-img-form:not(#preview-miniatures-new img)');
        existingImgs.forEach(img => {
            if (img.parentElement.style.display !== 'none') {
                gallerySources.push(img.src);
            }
        });

        // B. Adiciona Blobs das novas fotos
        const promises = newImages.map(file => {
            return new Promise(resolve => {
                const reader = new FileReader();
                reader.onload = (e) => resolve(e.target.result);
                reader.readAsDataURL(file);
            });
        });

        Promise.all(promises).then(newSrcs => {
            gallerySources = [...gallerySources, ...newSrcs];
            renderCarousel();
        });
    }

    function renderCarousel() {
        if (gallerySources.length > 0) {
            // Garante que o índice está dentro dos limites
            if (currentPhotoIndex >= gallerySources.length) currentPhotoIndex = 0;
            if (currentPhotoIndex < 0) currentPhotoIndex = gallerySources.length - 1;

            els.card.imgMain.src = gallerySources[currentPhotoIndex];
            els.card.imgMain.style.display = 'block';
            els.card.placeholder.style.display = 'none';

            // Controla visibilidade dos botões
            const showNav = gallerySources.length > 1;
            els.card.btnPrev.style.display = showNav ? 'flex' : 'none';
            els.card.btnNext.style.display = showNav ? 'flex' : 'none';
        } else {
            els.card.imgMain.style.display = 'none';
            els.card.placeholder.style.display = 'flex';
            els.card.btnPrev.style.display = 'none';
            els.card.btnNext.style.display = 'none';
        }
    }

    // 4. LOGICA DE SINCRONIZAÇÃO DE CAMPOS
    function setupListeners() {
        // Navegação do Carrossel
        if (els.card.btnPrev) els.card.btnPrev.onclick = () => { currentPhotoIndex--; renderCarousel(); };
        if (els.card.btnNext) els.card.btnNext.onclick = () => { currentPhotoIndex++; renderCarousel(); };

        // Inputs de Texto
        Object.values(els.inputs).forEach(input => {
            if (input) {
                input.addEventListener('input', updateLivePreview);
                if (input.tagName === 'SELECT') input.addEventListener('change', updateLivePreview);
            }
        });

        // Máscara de Preço (FIX 0,001)
        if (els.inputs.preco) {
            els.inputs.preco.addEventListener('input', function(e) {
                let v = e.target.value.replace(/\D/g, "");
                if (v === "") v = "0";
                v = (parseInt(v) / 100).toFixed(2) + "";
                v = v.replace(".", ",");
                v = v.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
                e.target.value = v;
                updateLivePreview();
            });
        }

        // Novas Fotos
        if (els.inputFotosNew) {
            els.inputFotosNew.addEventListener('change', (e) => handleNewFiles(Array.from(e.target.files)));
        }

        // Intercepta clique na lixeira das fotos antigas
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-remove-photo')) {
                setTimeout(refreshGallery, 400); // Aguarda animação de fade
            }
        });
    }

    function updateLivePreview() {
        if(!els.card.titulo) return;
        els.card.titulo.textContent = els.inputs.titulo.value.trim() || 'Título do anúncio';
        els.card.preco.textContent = els.inputs.preco.value.trim() ? `R$ ${els.inputs.preco.value.trim()}` : 'R$ 0,00';
        
        const cidade = els.inputs.cidade.value.trim() || 'Cidade';
        const estado = els.inputs.estado.value || 'UF';
        els.card.local.textContent = (els.inputs.cidade.value || els.inputs.estado.value) ? `${cidade}, ${estado}` : 'Localização';
        if(els.card.estadoBadge) els.card.estadoBadge.textContent = estado;
        els.card.badgeLocal.style.display = els.inputs.estado.value ? 'block' : 'none';

        const catText = els.inputs.categoria.selectedIndex > 0 ? els.inputs.categoria.options[els.inputs.categoria.selectedIndex].text : '--';
        els.detail.categoria.textContent = catText;
        const condText = els.inputs.condicao.selectedIndex >= 0 ? els.inputs.condicao.options[els.inputs.condicao.selectedIndex].text : '--';
        els.detail.condicao.textContent = condText;
        els.detail.desc.textContent = els.inputs.descricao.value.trim() || 'A descrição aparecerá aqui...';
    }

    function handleNewFiles(files) {
        files.forEach(file => {
            if (newImages.length < 10) newImages.push(file);
        });
        renderNewMiniatures();
        currentPhotoIndex = gallerySources.length; // Foca na primeira das novas
        refreshGallery();
    }

    function renderNewMiniatures() {
        els.miniaturesNewGrid.innerHTML = '';
        newImages.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const div = document.createElement('div');
                div.className = 'mkt-miniature-item';
                div.innerHTML = `<img src="${e.target.result}" class="miniature-img-form"><button type="button" class="btn-remove-photo-new">&times;</button>`;
                div.querySelector('button').onclick = () => { 
                    newImages.splice(index, 1); 
                    renderNewMiniatures(); 
                    refreshGallery(); 
                };
                els.miniaturesNewGrid.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }

    // 5. ENVIO FINAL COM SEGURANÇA (CSRF + SweetAlert2)
    els.form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btnSubmit = els.form.querySelector('.btn-submit-mkt-final');
        const originalText = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> A guardar...';

        try {
            const formData = new FormData(els.form);
            
            // Remove o campo de arquivo padrão e anexa o nosso array controlado
            formData.delete('fotos[]');
            newImages.forEach(file => { formData.append('fotos[]', file); });

            // 🔒 BLINDAGEM CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                formData.append('csrf_token', csrfToken);
            } else {
                throw new Error("Erro de Segurança: Token CSRF não encontrado. Atualize a página.");
            }

            const apiUrl = (typeof BASE_PATH !== 'undefined') 
                ? BASE_PATH + 'api/marketplace/editar_anuncio.php'
                : '../api/marketplace/editar_anuncio.php';

            const response = await fetch(apiUrl, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                // Alerta de Sucesso Premium
                Swal.fire({
                    icon: 'success',
                    title: 'Atualizado!',
                    text: 'As alterações do seu anúncio foram guardadas.',
                    confirmButtonColor: '#0C2D54'
                }).then(() => {
                    const redirectUrl = (typeof BASE_PATH !== 'undefined') 
                        ? BASE_PATH + 'marketplace/meus-anuncios' 
                        : (result.redirect || 'marketplace');
                    window.location.href = redirectUrl;
                });
            } else {
                // Alerta de Erro de Servidor
                Swal.fire({
                    icon: 'error',
                    title: 'Falha na Edição',
                    text: result.message || 'Não foi possível guardar as alterações.',
                    confirmButtonColor: '#0C2D54'
                });
            }
        } catch (error) {
            console.error(error);
            // Alerta de Erro de Conexão
            Swal.fire({
                icon: 'error',
                title: 'Erro de Ligação',
                text: error.message || 'Ocorreu um erro ao conectar ao servidor.',
                confirmButtonColor: '#d33'
            });
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalText;
        }
    });

    init();
});