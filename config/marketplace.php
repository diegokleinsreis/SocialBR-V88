<?php
/**
 * Configuração do Módulo Marketplace (Vendas)
 * Arquivo de Regras de Negócio e Categorias
 */

return [
    // Define se o módulo está ativo ou em manutenção
    'ativo' => true,

    // Configurações de exibição de moeda
    'moeda_simbolo' => 'R$',
    'moeda_codigo'  => 'BRL',

    /**
     * ==========================================
     * CONFIGURAÇÕES DE SEGURANÇA E CONFIANÇA
     * ==========================================
     */
    'seguranca' => [
        // Exigir CPF para criar anúncios (True = Obrigatório)
        'exigir_cpf' => true,
        
        // Mensagem explicativa para o usuário (UX)
        'aviso_cpf_titulo' => 'Verificação de Segurança Necessária',
        'aviso_cpf_texto'  => 'Para garantir a segurança da nossa comunidade e evitar fraudes, solicitamos o CPF de todos os vendedores. Seus dados são armazenados de forma criptografada e utilizados apenas para validação de identidade. Não exibiremos seu CPF publicamente.'
    ],

    /**
     * ==========================================
     * CATEGORIAS DE VENDA
     * ==========================================
     * Chave => [Rótulo, Ícone FontAwesome]
     */
    'categorias' => [
        'veiculos' => [
            'label' => 'Veículos (Venda)', 
            'icon'  => 'fa-car'
        ],
        'imoveis' => [
            'label' => 'Imóveis (Venda)', 
            'icon'  => 'fa-home'
        ],
        'eletronicos' => [
            'label' => 'Eletrônicos e Celulares', 
            'icon'  => 'fa-mobile-alt'
        ],
        'informatica' => [
            'label' => 'Informática e Acessórios', 
            'icon'  => 'fa-laptop'
        ],
        'moveis' => [
            'label' => 'Móveis e Decoração', 
            'icon'  => 'fa-couch'
        ],
        'eletrodomesticos' => [
            'label' => 'Eletrodomésticos', 
            'icon'  => 'fa-blender'
        ],
        'moda_beleza' => [
            'label' => 'Moda e Beleza', 
            'icon'  => 'fa-tshirt'
        ],
        'esportes' => [
            'label' => 'Esportes e Lazer', 
            'icon'  => 'fa-futbol'
        ],
        'animais' => [
            'label' => 'Animais e Acessórios', 
            'icon'  => 'fa-paw'
        ],
        'bebes' => [
            'label' => 'Artigos Infantis e Bebês', 
            'icon'  => 'fa-baby-carriage'
        ],
        'instrumentos' => [
            'label' => 'Instrumentos Musicais', 
            'icon'  => 'fa-guitar'
        ],
        'games' => [
            'label' => 'Games e Consoles', 
            'icon'  => 'fa-gamepad'
        ],
        'ferramentas' => [
            'label' => 'Ferramentas e Construção', 
            'icon'  => 'fa-tools'
        ],
        'agro' => [
            'label' => 'Agro e Indústria', 
            'icon'  => 'fa-tractor'
        ],
        'outros' => [
            'label' => 'Outros Itens à Venda', 
            'icon'  => 'fa-box-open'
        ],
    ],

    /**
     * ==========================================
     * LOCALIZAÇÃO (Escopo Nacional)
     * ==========================================
     */
    'estados' => [
        'AC' => 'Acre',
        'AL' => 'Alagoas',
        'AP' => 'Amapá',
        'AM' => 'Amazonas',
        'BA' => 'Bahia',
        'CE' => 'Ceará',
        'DF' => 'Distrito Federal',
        'ES' => 'Espírito Santo',
        'GO' => 'Goiás',
        'MA' => 'Maranhão',
        'MT' => 'Mato Grosso',
        'MS' => 'Mato Grosso do Sul',
        'MG' => 'Minas Gerais',
        'PA' => 'Pará',
        'PB' => 'Paraíba',
        'PR' => 'Paraná',
        'PE' => 'Pernambuco',
        'PI' => 'Piauí',
        'RJ' => 'Rio de Janeiro',
        'RN' => 'Rio Grande do Norte',
        'RS' => 'Rio Grande do Sul',
        'RO' => 'Rondônia',
        'RR' => 'Roraima',
        'SC' => 'Santa Catarina',
        'SP' => 'São Paulo',
        'SE' => 'Sergipe',
        'TO' => 'Tocantins',
    ],

    /**
     * ==========================================
     * REGRAS DE EXIBIÇÃO
     * ==========================================
     */

    // Tipos de formulário (Define quais campos extras aparecem)
    'tipos_anuncio' => [
        'padrao'  => 'Produto / Objeto',
        'veiculo' => 'Veículo Automotor',
        'imovel'  => 'Imóvel Residencial/Comercial'
    ],

    // Condições aceitas para venda
    'condicoes' => [
        'novo' => 'Novo (Lacrado/Sem uso)',
        'usado_bom' => 'Usado - Excelente estado',
        'usado_marcas' => 'Usado - Marcas de uso visíveis',
        'defeito' => 'Com defeito / Para retirada de peças'
    ],

    // Filtros de Preço para a Sidebar
    'filtros_preco' => [
        ['min' => 0, 'max' => 100, 'label' => 'Até R$ 100'],
        ['min' => 100, 'max' => 500, 'label' => 'R$ 100 a R$ 500'],
        ['min' => 500, 'max' => 2000, 'label' => 'R$ 500 a R$ 2.000'],
        ['min' => 2000, 'max' => 10000, 'label' => 'R$ 2.000 a R$ 10.000'],
        ['min' => 10000, 'max' => 50000, 'label' => 'R$ 10.000 a R$ 50.000'],
        ['min' => 50000, 'max' => null, 'label' => 'Acima de R$ 50.000'],
    ]
];