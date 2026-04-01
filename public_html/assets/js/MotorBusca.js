/**
 * MotorBusca.js - Inteligência de Pesquisa socialbr.lol
 * VERSÃO: 3.0 (Implementação de Histórico Enriquecido - Entidades e Termos)
 * PAPEL: Gerir dropdowns, exibir histórico com fotos ao focar e sugerir resultados.
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. SELETORES GLOBAIS
    const inputDesktop = document.getElementById('input-busca-desktop');
    const inputMobile  = document.getElementById('input-busca-mobile');
    const btnBusca      = document.querySelector('.search-button');
    
    // Elementos Mobile Overlay
    const overlay      = document.getElementById('search-overlay');
    const btnAbrir      = document.getElementById('open-mobile-search');
    const btnFechar     = document.getElementById('close-mobile-search');
    const resultadosMb = document.getElementById('mobile-search-results');

    // 2. GESTÃO DO DROPDOWN DESKTOP
    const wrapperPc    = document.querySelector('.search-wrapper.desktop-only');
    let dropdownPc     = document.querySelector('.search-dropdown');
    
    if (wrapperPc && !dropdownPc) {
        dropdownPc = document.createElement('div');
        dropdownPc.className = 'search-dropdown';
        wrapperPc.appendChild(dropdownPc);
    }

    let debounceTimer;

    // --- LÓGICA DE INTERAÇÃO MOBILE ---

    if (btnAbrir) {
        btnAbrir.addEventListener('click', () => {
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden'; 
            setTimeout(() => inputMobile.focus(), 300);
        });
    }

    if (btnFechar) {
        btnFechar.addEventListener('click', () => {
            overlay.classList.remove('active');
            document.body.style.overflow = ''; 
            inputMobile.value = '';
            resultadosMb.innerHTML = '';
        });
    }

    // --- MOTOR DE BUSCA (SINCRONIZADO) ---

    const dispararBusca = (input, container) => {
        clearTimeout(debounceTimer);
        const termo = input.value.trim();

        // Se o termo estiver vazio, carregamos o histórico inteligente
        if (termo.length < 1) {
            carregarHistoricoRecente(container);
            return;
        }

        debounceTimer = setTimeout(() => {
            executarBuscaRapida(termo, container);
        }, 300);
    };

    // Listeners para digitação e foco
    if (inputDesktop) {
        inputDesktop.addEventListener('input', () => dispararBusca(inputDesktop, dropdownPc));
        inputDesktop.addEventListener('focus', () => {
            if (inputDesktop.value.trim() === '') carregarHistoricoRecente(dropdownPc);
        });
    }

    if (inputMobile) {
        inputMobile.addEventListener('input', () => dispararBusca(inputMobile, resultadosMb));
        inputMobile.addEventListener('focus', () => {
            if (inputMobile.value.trim() === '') carregarHistoricoRecente(resultadosMb);
        });
    }

    /**
     * HISTÓRICO RECENTE (Ajax via api_historico.php v2.0)
     */
    async function carregarHistoricoRecente(container) {
        const path = window.BASE_PATH || '/';
        
        try {
            const response = await fetch(`${path}api/busca/api_historico.php`);
            const data = await response.json();

            // Verifica se há resultados enriquecidos (perfis, grupos ou termos)
            if (data.sucesso && data.resultados && data.resultados.length > 0) {
                renderizarHistorico(data.resultados, container);
                container.classList.add('active');
            } else {
                container.classList.remove('active');
                container.innerHTML = '';
            }
        } catch (error) {
            console.error('Erro ao carregar histórico inteligente:', error);
        }
    }

    /**
     * RENDERIZAÇÃO DO HISTÓRICO (Suporte a Fotos e Ícones)
     */
    function renderizarHistorico(itens, container) {
        let html = '<div class="dropdown-header">Visto Recentemente</div>';
        
        itens.forEach(item => {
            // Se for um termo simples, usa o ícone de histórico
            if (item.tipo === 'termo') {
                html += `
                    <div class="history-item" data-link="${item.link}">
                        <div class="history-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <span class="history-text">${item.titulo}</span>
                    </div>
                `;
            } else {
                // Se for Perfil, Grupo ou Post, usa a imagem/foto retornada pela API
                html += `
                    <div class="history-item entity-history" data-link="${item.link}">
                        <img src="${item.imagem}" class="history-avatar" alt="${item.titulo}">
                        <div class="history-info">
                            <span class="history-text">${item.titulo}</span>
                            <span class="history-type-label">${item.tipo.toUpperCase()}</span>
                        </div>
                    </div>
                `;
            }
        });

        container.innerHTML = html;
    }

    /**
     * BUSCA RÁPIDA (API Sugestões)
     */
    async function executarBuscaRapida(termo, container) {
        renderizarSkeleton(container);
        container.classList.add('active');

        try {
            const path = window.BASE_PATH || '/';
            const response = await fetch(`${path}api/busca/sugestoes.php?q=${encodeURIComponent(termo)}`);
            const data = await response.json();

            if (data.sucesso && data.resultados.length > 0) {
                renderizarResultados(data.resultados, termo, container);
            } else {
                container.innerHTML = `<div class="dropdown-header">Sem resultados para "${termo}"</div>`;
            }
        } catch (error) {
            console.error('Erro na busca:', error);
            container.classList.remove('active');
        }
    }

    /**
     * RENDERIZAÇÃO DE RESULTADOS EM TEMPO REAL
     */
    function renderizarResultados(resultados, termo, container) {
        let html = '';
        let ultimoTipo = null;
        const path = window.BASE_PATH || '/';
        
        resultados.forEach(item => {
            if (item.tipo !== ultimoTipo) {
                const labelCategoria = item.label || 'Resultados';
                html += `<div class="dropdown-category-divider">${labelCategoria.toUpperCase()}</div>`;
                ultimoTipo = item.tipo;
            }

            html += `
                <a href="${item.link}" class="dropdown-item" 
                    data-id="${item.id}" 
                    data-tipo="${item.tipo}" 
                    data-termo="${termo}">
                    <img src="${item.imagem}" alt="${item.titulo}">
                    <div class="dropdown-info">
                        <span class="dropdown-title">${item.titulo}</span>
                        <span class="dropdown-subtitle">${item.subtitulo}</span>
                    </div>
                </a>
            `;
        });

        html += `
            <a href="${path}pesquisa?q=${encodeURIComponent(termo)}" class="dropdown-item ver-todos" data-tipo="geral" data-termo="${termo}">
                <i class="fas fa-search"></i>
                <div class="dropdown-info">
                    <span class="dropdown-title">Ver todos os resultados para "${termo}"</span>
                </div>
            </a>
        `;

        container.innerHTML = html;
    }

    /**
     * REGISTRO DE INTERAÇÃO (Backend Analytics)
     */
    async function registrarInteracaoNoBanco(termo, tipo, idAlvo = null, totalResultados = 0) {
        try {
            const path = window.BASE_PATH || '/';
            const formData = new FormData();
            formData.append('termo', termo);
            formData.append('tipo', tipo);
            formData.append('total_resultados', totalResultados);
            
            if (idAlvo) formData.append('id_alvo', idAlvo);

            fetch(`${path}api/busca/registrar_interacao.php`, {
                method: 'POST',
                body: formData
            }).catch(err => console.warn("Erro silencioso no rastreio."));

        } catch (e) {
            console.warn("Erro ao registrar estatística de busca.");
        }
    }

    // 3. CAPTURA DE CLIQUES (Gestão de Itens e Histórico)
    document.addEventListener('click', (e) => {
        // A. Clique em Resultado do Dropdown (Registra a Interação para o futuro histórico)
        const item = e.target.closest('.dropdown-item, .search-result-choice');
        if (item) {
            const termo = item.getAttribute('data-termo');
            const tipo  = item.getAttribute('data-tipo');
            const id    = item.getAttribute('data-id');
            
            if (termo && tipo) {
                registrarInteracaoNoBanco(termo, tipo, id, 1);
            }
        }

        // B. Clique em Item do Histórico (Buscas Recentes / Perfis Vistos)
        const historyItem = e.target.closest('.history-item');
        if (historyItem) {
            const linkDireto = historyItem.getAttribute('data-link');
            if (linkDireto) {
                window.location.href = linkDireto;
            }
        }

        // C. Fechamento do dropdown ao clicar fora
        if (wrapperPc && !wrapperPc.contains(e.target) && dropdownPc) {
            dropdownPc.classList.remove('active');
        }
    });

    function renderizarSkeleton(container) {
        container.innerHTML = `
            <div class="dropdown-header">Buscando...</div>
            ${[1, 2, 3].map(() => `
                <div class="skeleton-item">
                    <div class="skeleton-circle"></div>
                    <div class="dropdown-info" style="flex:1">
                        <div class="skeleton-line med"></div>
                        <div class="skeleton-line short"></div>
                    </div>
                </div>
            `).join('')}
        `;
    }

    // 4. CAPTURA DE ENTER
    const setupEnter = (input) => {
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                const termo = input.value.trim();
                if (termo) {
                    window.location.href = `${window.BASE_PATH || '/'}pesquisa?q=${encodeURIComponent(termo)}`;
                }
            }
        });
    };

    if (inputDesktop) setupEnter(inputDesktop);
    if (inputMobile) setupEnter(inputMobile);
});