-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 01/04/2026 às 14:23
-- Versão do servidor: 10.6.25-MariaDB
-- Versão do PHP: 8.4.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `klscom_social`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `Amizades`
--

CREATE TABLE `Amizades` (
  `id` int(11) NOT NULL,
  `usuario_um_id` int(11) NOT NULL COMMENT 'ID do usuário que enviou o pedido',
  `usuario_dois_id` int(11) NOT NULL COMMENT 'ID do usuário que recebeu o pedido',
  `status` enum('pendente','aceite','recusado','bloqueado') NOT NULL DEFAULT 'pendente',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Amizades`
--

INSERT INTO `Amizades` (`id`, `usuario_um_id`, `usuario_dois_id`, `status`, `data_criacao`, `data_atualizacao`) VALUES
(8, 5, 9, 'aceite', '2025-10-15 19:04:00', '2026-01-23 20:19:48'),
(9, 5, 4, 'aceite', '2025-10-15 19:04:12', '2026-01-23 20:19:48'),
(13, 12, 5, 'aceite', '2025-10-16 16:05:52', '2025-10-16 16:05:59'),
(24, 12, 14, 'pendente', '2025-11-15 17:15:16', '2025-11-15 17:15:16'),
(25, 13, 4, 'pendente', '2025-12-21 02:27:33', '2025-12-21 02:27:33'),
(27, 5, 14, 'aceite', '2026-01-23 20:19:48', '2026-01-23 20:19:48'),
(29, 5, 11, 'aceite', '2026-01-23 20:19:48', '2026-01-23 20:19:48'),
(33, 13, 14, 'pendente', '2026-02-25 21:04:30', '2026-02-25 21:04:30'),
(34, 15, 11, 'pendente', '2026-03-01 17:03:55', '2026-03-01 17:03:55'),
(35, 15, 5, 'aceite', '2026-03-30 20:03:42', '2026-03-30 20:05:20');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Anotacoes_Admin`
--

CREATE TABLE `Anotacoes_Admin` (
  `id` int(11) NOT NULL,
  `conteudo_texto` longtext DEFAULT NULL,
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `Anotacoes_Admin`
--

INSERT INTO `Anotacoes_Admin` (`id`, `conteudo_texto`, `data_atualizacao`) VALUES
(1, '--------------------------------------------- FAZER ---------------------------------------------------\n- FAZER O PWA DO SITE\n- FORÇAR SEGURANÇA NA HORA DO CADASTRO\n- CRIAR O SPLASH DO SITE (ANIMAÇÃO DO LOGO NA TELA INICIAL AO CARREGAR PELA PRIMEIRA VEZ)\n- BLOQUEAR VISUALMENTE UM MODULO NO MENU QUANDO ELE ESTIVER TRANCADO.\n- CRIAR LOGIN PELO GOOGLE E FACEBOOK\n- CRIAR MENÇÕES DE USUARIOS. \n- CRIAR O MODULO VERIFICADO (API PAGAMENTO, VERIFICAR OPÇÕES PARA VERIFICADOS, ETC..)\n- FAZER O MODULO STORY \n- MOVER O BOTAO DAS NOTIFICAÇÕES PARA O HEADER\n- MELHORAR PAGINA DE LOGIN E CADASTRO\n- FAZER O POLITICAS DE PRIVACIDADE, JA TEMOS UM MODELO PRONTO NO CHATGPT\n----------------------------------- ARRUMAR --------------------------------------------------------\n- botao de curtir e responder comentarios nao funciona no modal interação, e ao adicionar um comentario pelo campo input rapido (do post template) ele vai para postagem.php em vez de abrir o modal com os comentarios atualizados.\n- ao criar comentario, em vez de abrir o modal de comentarios/ modal interação, ele vai para a pagina postagem.php. \n- no perfil de quem pediu a solicitação (quando tem aceitar ou recusar) a biografia fica apertada, esta feio.\n- colocar uma imagem de fundo na pagina de login e cadastro, verificar o gemini, conversa arquivada\n- verificar o admin estatística, nao esta funcionando, erro critico, entrar primeiro e verificar depois no sentinela\n- no suporte do painel admin o menu hamburg não funciona.\n- quando um modulo esta trancado no banco de dados, nao aparece no modal, aparecer modulo em manutenção desativado. verifique o modulo marketplace\n- o botao ver mais em postagem.php nao funciona\n - O https://socialbr.lol/suporte/abrir nao esta responsivo, o botao de abir chamado nao aparece se nao abaixar o zoom da tela.\n - atenção no painel admin, algumas alterações nao é possivel realizar, exemplo, na denuncia, ao apertar em excluir post, da erro\n - erro de log no admin denuncias\n - post de usuarios bloqueados esta aparecendo no feed.\n - atualizar o admin usuarios para acrescentar as novas colunas da tabela usuario, como email verificado, conta verificada (via pagamento), quantas curtidas fez, quantas curtidas recebeu, quantos comentarios feito, quantos comentarios realizados, quantos posts compartilhados, quantos post dele que foi compartilhados, grupos que participa, grupos que é dono, quantos anuncios feitos no marketplace, postagens criadas, midias enviadas, links postados, ultimo acesso e etc... verificar a tabela usuarios novamente quando for arrumar isso, por favor.\n\n------------------------------ ANOTAÇÕES DA IA -------------------------------------------------------------\n\nResumos/Dicas que a Manus IA me deu:\n\n# Análise Crítica e Feedback do Projeto (V82)\n\nApós uma análise profunda da estrutura de arquivos, arquitetura do código e lógica de funcionamento do seu site, aqui estão minhas impressões técnicas, pontos positivos, vulnerabilidades identificadas e sugestões de melhoria.\n\n---\n\n## 1. Visão Geral e Pontos Fortes\n\nO projeto demonstra um amadurecimento significativo (versão V82), com uma estrutura bem definida e funcionalidades complexas integradas.\n\n*   **Arquitetura Organizada**: O uso de um **Front Controller** (`index.php`) e a separação da lógica de negócios em `/src` (ex: `PostLogic.php`, `UserLogic.php`) mostram uma boa adoção de padrões de projeto, facilitando a manutenção.\n*   **Modularidade de Componentes**: A pasta `/templates` com componentes reutilizáveis (modais, posts, cabeçalhos) é excelente para manter a consistência visual e reduzir a duplicidade de código.\n*   **Recursos Avançados**: A presença de um sistema de Marketplace, Chat em tempo real (via AJAX), Enquetes e um sistema de \"Salvos Premium\" coloca o site em um patamar de rede social funcional.\n*   **Ferramentas de Debug**: O diretório `super_debug` e os logs de auditoria SQL indicam uma preocupação real com a performance e o rastreamento de erros durante o desenvolvimento.\n\n---\n\n## 2. Pontos de Atenção e Possíveis Erros\n\n### 2.1. Segurança Crítica\n\n> ⚠️ **Exposição de Credenciais**: No arquivo `config/database.php`, as credenciais do banco de dados (usuário e senha) estão expostas diretamente no código. \n> *   **Risco**: Se o servidor for mal configurado ou se houver um vazamento de código, seu banco de dados estará totalmente vulnerável.\n> *   **Dica**: Use variáveis de ambiente (`.env`) para armazenar essas informações sensíveis.\n\n> ⚠️ **SQL Injection Potencial**: Embora o uso de `bind_param` em `PostLogic.php` seja um bom passo para prevenir SQL Injection, é crucial garantir que *todas* as consultas SQL que recebem entrada do usuário utilizem prepared statements. Uma única falha pode comprometer todo o banco de dados.\n> *   **Risco**: Se alguma consulta não estiver parametrizada corretamente, um atacante pode injetar código SQL malicioso.\n> *   **Dica**: Faça uma auditoria completa de todas as interações com o banco de dados para garantir que `prepare()` e `bind_param()` sejam usados consistentemente.\n\n### 2.2. Gerenciamento de Assets\n*   **Fragmentação de JavaScript**: Há muitos arquivos JS pequenos na pasta `assets/js`. Embora isso seja bom para organização, pode aumentar o tempo de carregamento da página devido ao número de requisições HTTP.\n*   **Sugestão**: Considere usar um *bundler* (como Vite ou Webpack) para minificar e agrupar esses arquivos em produção.\n\n### 2.3. Logs de Erro Expostos\n\n---\n\n## 3. O Que Falta? (Sugestões de Melhoria)\n\n### 3.1. Funcionalidades de Usuário\n\n*   **Recuperação de Senha Robusta**: Implementar um fluxo completo de recuperação de senha que inclua o envio de um token único por e-mail, com tempo de expiração e validação segura, é crucial para a usabilidade e segurança.\n*   **Autenticação de Dois Fatores (2FA)**: Para um sistema que lida com dados de usuário e interações sociais, a adição de 2FA (via aplicativo autenticador ou SMS) eleva significativamente a segurança das contas.\n*   **Sistema de Busca Global Avançado**: Expandir a funcionalidade de busca para incluir pesquisa por tags, hashtags, conteúdo de posts e perfis de forma mais eficiente, talvez com a integração de soluções de busca como Elasticsearch ou Apache Solr, pode melhorar drasticamente a experiência do usuário.\n*   **Notificações em Tempo Real**: Embora haja um sistema de notificações, a implementação de WebSockets para notificações push em tempo real (ex: novas mensagens de chat, curtidas, comentários) tornaria a experiência mais dinâmica e engajadora.\n*   **Personalização de Perfil**: Oferecer mais opções de personalização para os perfis dos usuários (ex: temas, layouts, informações adicionais) pode aumentar o engajamento.\n*   **Relatórios e Análises para Usuários (Opcional)**: Para usuários com perfis de criadores de conteúdo ou empresas, um painel com métricas básicas sobre o desempenho de seus posts e interações poderia ser um grande diferencial.\n\n\n### 3.2. Infraestrutura Técnica\n\n*   **API RESTful Consistente**: Refatorar os endpoints da API para seguir um padrão RESTful mais rigoroso (ex: `POST /api/posts`, `GET /api/posts/{id}`, `PUT /api/posts/{id}`) não só padroniza a comunicação, mas também facilita a integração com aplicações front-end mais complexas e futuras aplicações móveis.\n*   **Camada de Cache**: Implementar uma camada de cache (ex: Redis, Memcached) para dados frequentemente acessados, como feeds de posts, perfis de usuários e configurações, pode reduzir drasticamente a carga no banco de dados e melhorar o tempo de resposta da aplicação.\n*   **Sistema de Filas (Queues)**: Para tarefas que consomem muitos recursos ou tempo (ex: processamento de imagens, envio de e-mails em massa, notificações), a utilização de um sistema de filas (ex: RabbitMQ, SQS) pode melhorar a responsividade da aplicação e a experiência do usuário.\n*   **Otimização de Imagens e Mídias**: Implementar um pipeline de otimização automática para imagens e vídeos enviados (redimensionamento, compressão, conversão para formatos webp/avif) pode melhorar significativamente o desempenho do carregamento das páginas.\n*   **Monitoramento e Alertas**: Configurar ferramentas de monitoramento de desempenho (APM) e alertas para identificar proativamente gargalos, erros e problemas de segurança em produção.\n\n\n---\n\n## 4. Dicas de Ouro para o Desenvolvedor\n\n1.  **Proteção contra CSRF**: Vi que você já tem uma lógica de `csrf_token` no `database.php`. Certifique-se de que **todos** os formulários e chamadas AJAX da API validam esse token.\n2.  **Uploads de Arquivos**: Verifique se a pasta `public_html/uploads` tem permissões restritas e se você está validando o tipo de arquivo (MIME type) no servidor para evitar que alguém envie um script `.php` malicioso.\n3.  **Documentação**: Como o projeto está grande, criar uma documentação da API (usando Swagger/OpenAPI) ajudaria muito se você decidir trabalhar em equipe ou criar um aplicativo mobile para este site.\n\n---\n\n## Conclusão\n\nSeu site está **muito bem construído** e funcional. A estrutura é sólida e as funcionalidades são ambiciosas. Resolvendo a questão da segurança das credenciais e otimizando a entrega de assets, você terá um sistema de nível profissional. \n\n**Nota técnica estimada: 8.5/10** (pela organização e complexidade).\n', '2026-04-01 17:00:53');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Avisos_Destinatarios`
--

CREATE TABLE `Avisos_Destinatarios` (
  `id` int(11) NOT NULL,
  `id_aviso` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `data_vinculo` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `Avisos_Lidos`
--

CREATE TABLE `Avisos_Lidos` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_aviso` int(11) NOT NULL,
  `data_leitura` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Avisos_Lidos`
--

INSERT INTO `Avisos_Lidos` (`id`, `id_usuario`, `id_aviso`, `data_leitura`) VALUES
(25, 5, 27, '2026-02-03 21:53:00'),
(26, 13, 27, '2026-02-03 21:53:20'),
(29, 15, 27, '2026-02-03 22:51:46'),
(30, 5, 29, '2026-02-04 22:59:06'),
(31, 15, 29, '2026-02-05 00:31:56');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Avisos_Sistema`
--

CREATE TABLE `Avisos_Sistema` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `mensagem` text NOT NULL,
  `cor_preset` enum('gold','emergency','info','success') NOT NULL DEFAULT 'gold',
  `is_sticky` tinyint(1) NOT NULL DEFAULT 0,
  `data_inicio` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_expiracao` timestamp NULL DEFAULT NULL COMMENT 'Dica de Ouro: Expiração automática',
  `cta_texto` varchar(50) DEFAULT NULL,
  `cta_link` varchar(255) DEFAULT NULL,
  `icone` varchar(50) DEFAULT NULL,
  `criado_por` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Avisos_Sistema`
--

INSERT INTO `Avisos_Sistema` (`id`, `titulo`, `mensagem`, `cor_preset`, `is_sticky`, `data_inicio`, `data_criacao`, `data_expiracao`, `cta_texto`, `cta_link`, `icone`, `criado_por`) VALUES
(27, 'testst', 'fsdfsf', 'emergency', 1, '2026-02-03 21:52:31', '2026-02-03 21:52:31', '2026-02-04 21:52:31', NULL, NULL, NULL, 5),
(29, 'Bem vindo ao SocialBR', 'Estamos feliz por você estar aqui!!', 'gold', 1, '2026-02-04 22:58:55', '2026-02-04 22:58:55', '2026-02-05 22:58:55', NULL, NULL, 'fa-bullhorn', 5);

-- --------------------------------------------------------

--
-- Estrutura para tabela `Bairros`
--

CREATE TABLE `Bairros` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `id_cidade` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Bairros`
--

INSERT INTO `Bairros` (`id`, `nome`, `id_cidade`) VALUES
(1, 'Espinheiros', 129),
(2, 'Santa Regina', 129),
(3, 'Itaipava', 129),
(4, 'Independência', 129),
(5, 'Loteamento São Francisco de Assis', 129),
(6, 'Quilômetro 12', 129),
(7, 'Arraial dos Cunhas', 129),
(8, 'Salseiros', 129),
(9, 'Espinheirinhos', 129),
(10, 'Campeche', 129),
(11, 'Limoeiro', 129),
(12, 'São Roque', 129),
(13, 'Colônia Japonesa', 129),
(14, 'Brilhante', 129),
(15, 'Cordeiros', 129),
(16, 'Murta', 129),
(17, 'São Judas', 129),
(18, 'Barra do Rio', 129),
(19, 'Vila Operária', 129),
(20, 'Dom Bosco', 129),
(21, 'Praia Brava', 129),
(22, 'Centro', 129),
(23, 'São João', 129),
(24, 'São Vicente', 129),
(25, 'Ressacada', 129),
(26, 'Fazenda', 129),
(27, 'Cabeçudas', 129);

-- --------------------------------------------------------

--
-- Estrutura para tabela `Bloqueios`
--

CREATE TABLE `Bloqueios` (
  `id` int(11) NOT NULL,
  `bloqueador_id` int(11) NOT NULL,
  `bloqueado_id` int(11) NOT NULL,
  `data_bloqueio` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `Bloqueios`
--

INSERT INTO `Bloqueios` (`id`, `bloqueador_id`, `bloqueado_id`, `data_bloqueio`) VALUES
(9, 15, 13, '2026-02-25 19:19:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `busca_interacoes`
--

CREATE TABLE `busca_interacoes` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `termo` varchar(100) NOT NULL COMMENT 'O que o usuário digitou',
  `tipo_clicado` enum('perfil','grupo','post','geral') NOT NULL DEFAULT 'geral' COMMENT 'O que ele escolheu ver',
  `id_alvo` int(11) DEFAULT NULL COMMENT 'ID do objeto clicado (ID do usuário, do grupo ou do post)',
  `total_resultados` int(11) NOT NULL DEFAULT 0,
  `data_interacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `busca_interacoes`
--

INSERT INTO `busca_interacoes` (`id`, `id_usuario`, `termo`, `tipo_clicado`, `id_alvo`, `total_resultados`, `data_interacao`) VALUES
(1, 5, 'teste', 'geral', NULL, 1, '2026-02-17 17:52:06'),
(2, 5, 'teste', 'geral', NULL, 8, '2026-02-17 18:05:31'),
(3, 5, 'teste', 'grupo', 5, 1, '2026-02-17 18:05:34'),
(4, 5, 'aaa', 'geral', NULL, 0, '2026-02-17 18:11:06'),
(5, 5, 'manu', 'geral', NULL, 1, '2026-02-17 18:20:27'),
(6, 5, 'man', 'geral', NULL, 1, '2026-02-17 20:56:18'),
(7, 5, 'dasjkfhds', 'geral', NULL, 1, '2026-02-17 20:57:19'),
(8, 5, 'dasjkfhds', 'geral', NULL, 0, '2026-02-17 20:57:20'),
(9, 5, 'abra', 'geral', NULL, 1, '2026-02-17 20:59:30'),
(10, 5, 'abra', 'geral', NULL, 0, '2026-02-17 20:59:30'),
(11, 5, 'nnn', 'geral', NULL, 0, '2026-02-17 21:02:42'),
(12, 5, 'diego', 'perfil', 12, 1, '2026-02-17 21:03:53'),
(13, 5, 'nnn', 'geral', NULL, 0, '2026-02-17 21:17:27'),
(14, 5, 'diego', 'perfil', 13, 1, '2026-02-17 21:23:53'),
(15, 5, 'diego', 'geral', NULL, 3, '2026-02-18 13:21:15'),
(16, 5, 'diego', 'perfil', 12, 1, '2026-02-18 13:55:44'),
(17, 5, 'teste', 'grupo', 5, 1, '2026-02-18 14:08:06'),
(18, 5, 'teste', 'post', 85, 1, '2026-02-18 16:21:44'),
(19, 5, 'teste', 'geral', NULL, 20, '2026-02-18 16:21:51'),
(20, 5, 'teste', 'post', 66, 1, '2026-02-18 16:22:05'),
(21, 5, 'lore', 'post', 41, 1, '2026-02-18 16:22:19'),
(22, 13, 'teste', 'geral', NULL, 18, '2026-02-27 19:07:48'),
(23, 15, 'lore', 'post', 34, 1, '2026-02-28 20:05:06'),
(24, 15, 'teste', 'perfil', 11, 1, '2026-03-17 00:28:40'),
(25, 15, 'teste', 'perfil', 11, 1, '2026-03-18 19:55:21'),
(26, 15, 'teste', 'perfil', 11, 1, '2026-03-18 20:06:26'),
(27, 5, '\'', 'geral', NULL, 0, '2026-04-01 16:08:25'),
(28, 5, '\"', 'geral', NULL, 0, '2026-04-01 16:08:38'),
(29, 5, 'nome\' order by 1--', 'geral', NULL, 0, '2026-04-01 16:09:04'),
(30, 5, 'usuarios\' order by 1--', 'geral', NULL, 0, '2026-04-01 16:09:22');

-- --------------------------------------------------------

--
-- Estrutura para tabela `busca_sinonimos`
--

CREATE TABLE `busca_sinonimos` (
  `id` int(11) NOT NULL,
  `termo_digitado` varchar(100) NOT NULL COMMENT 'Ex: trampo, jobs, vags',
  `termo_real` varchar(100) NOT NULL COMMENT 'Ex: empregos',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `busca_sinonimos`
--

INSERT INTO `busca_sinonimos` (`id`, `termo_digitado`, `termo_real`, `data_criacao`) VALUES
(1, 'ajuda', 'suporte', '2026-02-15 17:52:09'),
(2, 'vaga', 'emprego', '2026-02-15 17:52:09');

-- --------------------------------------------------------

--
-- Estrutura para tabela `chat_conversas`
--

CREATE TABLE `chat_conversas` (
  `id` int(11) NOT NULL,
  `tipo` enum('privada','grupo') NOT NULL DEFAULT 'privada',
  `titulo` varchar(100) DEFAULT NULL,
  `capa_url` varchar(255) DEFAULT NULL,
  `dono_id` int(11) DEFAULT NULL,
  `status` enum('ativa','arquivada','bloqueada') DEFAULT 'ativa',
  `ultima_mensagem_at` datetime DEFAULT current_timestamp(),
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `chat_conversas`
--

INSERT INTO `chat_conversas` (`id`, `tipo`, `titulo`, `capa_url`, `dono_id`, `status`, `ultima_mensagem_at`, `criado_em`) VALUES
(1, 'privada', NULL, NULL, NULL, 'ativa', '2026-03-02 12:39:09', '2026-01-19 23:30:58'),
(2, 'privada', NULL, NULL, NULL, 'ativa', '2026-01-20 13:43:51', '2026-01-20 13:43:51'),
(3, 'privada', NULL, NULL, NULL, 'ativa', '2026-01-28 13:37:01', '2026-01-20 18:54:17'),
(4, 'privada', NULL, NULL, NULL, 'ativa', '2026-01-22 19:57:39', '2026-01-22 19:57:31'),
(5, 'privada', NULL, NULL, NULL, 'ativa', '2026-03-30 17:49:01', '2026-01-23 11:37:29'),
(6, 'grupo', 'Primeiro grupo para testes', NULL, 5, 'ativa', '2026-03-28 11:28:20', '2026-01-23 17:33:12'),
(7, 'privada', NULL, NULL, NULL, 'ativa', '2026-03-28 11:16:23', '2026-01-26 20:57:13'),
(8, 'privada', NULL, NULL, NULL, 'ativa', '2026-01-28 19:51:41', '2026-01-28 19:51:27'),
(9, 'privada', NULL, NULL, NULL, 'ativa', '2026-01-30 12:06:10', '2026-01-30 12:06:10'),
(10, 'privada', NULL, NULL, NULL, 'ativa', '2026-01-30 12:45:47', '2026-01-30 12:07:45'),
(11, 'grupo', 'Segundo Grupo para Teste', NULL, 5, 'ativa', '2026-02-04 11:50:55', '2026-02-01 14:08:04'),
(12, 'privada', NULL, NULL, NULL, 'ativa', '2026-03-01 14:04:27', '2026-03-01 14:04:03'),
(13, 'grupo', 'teste', NULL, 15, 'ativa', '2026-03-23 18:27:25', '2026-03-23 18:27:25'),
(14, 'grupo', 'sadad', NULL, 15, 'ativa', '2026-03-30 21:06:08', '2026-03-30 21:06:08'),
(15, 'privada', NULL, NULL, NULL, 'ativa', '2026-04-01 13:11:30', '2026-04-01 13:11:30');

-- --------------------------------------------------------

--
-- Estrutura para tabela `chat_mensagens`
--

CREATE TABLE `chat_mensagens` (
  `id` int(11) NOT NULL,
  `conversa_id` int(11) NOT NULL,
  `remetente_id` int(11) NOT NULL,
  `mensagem` text DEFAULT NULL,
  `midia_url` varchar(255) DEFAULT NULL,
  `tipo_midia` enum('texto','foto','video','audio') DEFAULT 'texto',
  `token_seguranca` varchar(100) DEFAULT NULL,
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `chat_mensagens`
--

INSERT INTO `chat_mensagens` (`id`, `conversa_id`, `remetente_id`, `mensagem`, `midia_url`, `tipo_midia`, `token_seguranca`, `criado_em`) VALUES
(1, 3, 13, 'teste', NULL, 'texto', '844c966796662bcbb7a43c242c6b929e', '2026-01-22 12:18:35'),
(2, 3, 13, 'teste', NULL, 'texto', '266adda7d93144780240f9b3e4a8500e', '2026-01-22 12:27:37'),
(3, 3, 12, 'oi', NULL, 'texto', '9ff8dfcd0e494edf3df79384ad17bcce', '2026-01-22 12:32:28'),
(4, 3, 12, 'teste', NULL, 'texto', 'd7f3d7ff4903e2a85c72454bd764fd04', '2026-01-22 12:37:58'),
(5, 3, 13, 'Opa', NULL, 'texto', '3ca076cc4ec70085e732e6a907e79322', '2026-01-22 12:47:03'),
(6, 3, 12, 'teste', NULL, 'texto', '3668a3a78281f46dc4864e9ff33d6248', '2026-01-22 14:32:23'),
(7, 3, 12, 'fdsfsdfsdfsdjnfjsdnfjksdnjkfnsdkjfnskdjnfkjsdnfjknskjdfnjkdsnfkjnsdjkfnsjkdnfkjsdnfjknsdjkfnsjkdnfjksndjkfnsdjknfjksdnfjksdnjkfnsdjkfnjksdnfkjsndkjfnjksdnfjksndjkfnksjdnfkjsnfkjnsdkjfnsjkdnfkjsdnfjksdnfjksdnfjknsd', NULL, 'texto', '8d1f08790e92e6856b0aa102c9b4d2b8', '2026-01-22 14:34:21'),
(8, 3, 12, 'teste', NULL, 'texto', '884f97198a1cd37bd409812c6323b066', '2026-01-22 17:18:12'),
(9, 3, 13, 'teste', NULL, 'texto', '85d9227705e06428c1b194952d6a04f1', '2026-01-22 18:00:16'),
(10, 4, 15, 'Oi', NULL, 'texto', '677034c018d8ab490737b093c8ebce61', '2026-01-22 19:57:39'),
(11, 1, 5, 'oi', NULL, 'texto', '276b99b0122a165523cfc886b949087a', '2026-01-23 11:35:21'),
(12, 5, 5, 'teste', NULL, 'texto', 'ce3edb040823411f18bcaff927c4e682', '2026-01-23 11:37:34'),
(13, 5, 15, 'Opa', NULL, 'texto', 'd40d0a56f27f0bc888e4f8a0bb01a0e4', '2026-01-23 12:24:52'),
(14, 1, 13, 'fasdf', NULL, 'texto', '296c090a77b491fbf0e6644e4dd67ae8', '2026-01-23 12:45:51'),
(15, 6, 5, 'teste', NULL, 'texto', '1129761ee6b77b1e99e70dbddcec4970', '2026-01-24 13:04:30'),
(16, 6, 12, 'Oi', NULL, 'texto', '7e718b5fc400ccebd8437c0952095002', '2026-01-24 13:20:13'),
(17, 6, 15, 'Ola', NULL, 'texto', '62831432c06ef9bf3ba4e612a48aa484', '2026-01-24 13:23:15'),
(18, 6, 15, 'Teste', NULL, 'texto', '9418fedcd2663c51be92959acb101bb5', '2026-01-24 13:23:36'),
(19, 6, 15, 'Gdgsgdhshdgdghdhh sushehsuus dhehshd', NULL, 'texto', '065eb9286346c6dc2695eb253c99f6e7', '2026-01-24 13:23:43'),
(20, 6, 15, 'Ggyygg', NULL, 'texto', '0a82c321f9c8dbda130a2c12e13a036d', '2026-01-24 13:23:50'),
(21, 6, 12, 'Fala tu', NULL, 'texto', '3ba2b99b7ff729c4d37ca45b9582baef', '2026-01-24 21:28:36'),
(22, 6, 12, 'Hsbs', NULL, 'texto', '8561c6b3e8c2107b29931d09eca24b5c', '2026-01-24 21:29:15'),
(23, 6, 12, 'iojioj', NULL, 'texto', '342bd973815c23610af9f150fd5eb039', '2026-01-26 17:10:09'),
(24, 6, 12, 'sdfdsf\r\nfsdfsdf\r\nfsdf', NULL, 'texto', '95d73023a5ca86e5d0796b90219ce2a0', '2026-01-26 17:10:20'),
(25, 7, 12, 'Oba', NULL, 'texto', '0696a7b1cc4372df8824c8c032a8804d', '2026-01-26 20:57:23'),
(26, 6, 5, '', 'assets/uploads/chat/sb_20260127_153557_16283c08.webm', 'video', '9373e05afe71c1f7da2e534920808cf7', '2026-01-27 15:35:57'),
(27, 6, 5, '', 'assets/uploads/chat/sb_20260127_153606_b5384364.webm', 'audio', '556d948e22bbf751e1c03d7110410708', '2026-01-27 15:36:06'),
(28, 6, 5, 'teste', NULL, 'texto', 'f4c4e81e7502ba58b0fa13e0828045d3', '2026-01-27 15:36:46'),
(29, 6, 13, '', 'assets/uploads/chat/sb_20260127_180547_757735f3.jpg', 'foto', '96ab3916c63abdd4a86b7e3584f3e4ce', '2026-01-27 18:05:47'),
(30, 3, 13, '', 'assets/uploads/chat/sb_20260128_133701_4305d568.jpg', 'foto', '9da95a6af29648eace15f50c37380238', '2026-01-28 13:37:01'),
(31, 6, 15, '', 'assets/uploads/chat/sb_20260128_194902_685919de.mp4', 'video', '7d39b741e122161ddb69f36e8b547650', '2026-01-28 19:49:02'),
(32, 8, 5, 'Ola', NULL, 'texto', '99b6401cb9a695fa72b48f3a3a9a1e84', '2026-01-28 19:51:41'),
(33, 10, 13, 'teste', NULL, 'texto', '43ee980537c206ea861a3a60d4794095', '2026-01-30 12:45:47'),
(34, 6, 13, '', 'assets/uploads/chat/sb_20260130_131857_1c9d09f0.webm', 'audio', 'dc6158f87b26f8819809d2df342122a2', '2026-01-30 13:18:57'),
(35, 6, 13, '', 'assets/uploads/chat/sb_20260130_142431_16c97e80.jpg', 'foto', 'd6d7d548dcbd48bbb6d0ac6c7081def1', '2026-01-30 14:24:31'),
(36, 1, 5, 'ola', NULL, 'texto', 'e1da7754b16a5f5725c3525ef701cb7d', '2026-02-01 13:48:22'),
(37, 1, 5, 'ola', NULL, 'texto', '197db169d3f4da566936b91828866376', '2026-02-01 13:48:28'),
(38, 1, 5, 'ola', NULL, 'texto', 'a2e9268883a001acc8d51e5759ffe7de', '2026-02-01 13:48:28'),
(39, 1, 5, 'ola', NULL, 'texto', '3065fde26ddf462b262253be2181b71e', '2026-02-01 13:48:29'),
(40, 1, 5, 'ola', NULL, 'texto', 'be93171dc1a32560ef493269fd37d50a', '2026-02-01 13:48:30'),
(41, 1, 13, 'blza', NULL, 'texto', 'f7208717a4a030b2827ad81decc4c16e', '2026-02-01 13:49:31'),
(42, 11, 5, 'teste', NULL, 'texto', '894bdd9738dc32c16fc2ca2b5f9475ed', '2026-02-03 09:26:13'),
(43, 1, 13, 'teste', NULL, 'texto', 'be495444b75344b99387a078811f774a', '2026-02-03 12:25:43'),
(44, 1, 13, 'tes', NULL, 'texto', 'cb4c28c096d1b160dfe1278cb8dca6f3', '2026-02-03 12:26:14'),
(45, 1, 5, 'asdads', NULL, 'texto', '8faf07db4ce6dd2b6ed2875e3ed68214', '2026-02-03 18:44:26'),
(47, 6, 15, 'Olha isso', 'assets/uploads/chat/sb_20260204_214740_dabba119.jpg', 'foto', '737ca53bccac8d91d7a69f10b26903a3', '2026-02-04 21:47:40'),
(48, 12, 15, 'Teste', NULL, 'texto', 'afa9302a49f35c3bd0cbdfdef9136bc9', '2026-03-01 14:04:27'),
(49, 1, 13, 'teste', NULL, 'texto', '29c79318b29f230dde5ef5beeddf6e37', '2026-03-02 12:39:09'),
(50, 5, 5, 'teste', NULL, 'texto', '573059ca5fc15be945e7b65a00a00bb0', '2026-03-11 11:54:35'),
(51, 5, 5, 'tes', NULL, 'texto', '840f5503f178962b6708bde584bcd23a', '2026-03-11 12:05:27'),
(52, 5, 5, 'teste', NULL, 'texto', 'a8c084ea7f52de54104161eec3eb5aeb', '2026-03-11 12:09:25'),
(53, 5, 5, 'tesa', NULL, 'texto', '42de5c6b04ab6c9bf496851505719834', '2026-03-11 12:12:34'),
(54, 5, 5, 'fsaf', NULL, 'texto', '358d66aa3a54e47d5fc2bb6e60f29cfd', '2026-03-11 12:13:48'),
(55, 5, 5, 'teste', NULL, 'texto', 'e929b547ebdb893ccdd8184892ef783e', '2026-03-11 14:39:24'),
(56, 7, 15, '', 'midias/chat/fotos/15_manus_2026-03-28_11-16-23_privado.webp', 'foto', '6d1410e24cfa381b68b7b36fff877bff', '2026-03-28 11:16:23'),
(57, 6, 15, '', 'midias/chat/fotos/15_manus_2026-03-28_11-17-11_grupo.webp', 'foto', '83b2a3ac78dc6b7dcccbcb730787c92c', '2026-03-28 11:17:11'),
(58, 6, 15, '', 'midias/chat/videos/15_manus_2026-03-28_11-18-03_grupo.mp4', 'video', '60d2640e4cc5501f54cf952801d22d41', '2026-03-28 11:18:03'),
(59, 6, 15, '', 'midias/chat/audios/15_manus_2026-03-28_11-28-20_grupo.webm', 'audio', 'eb8ba29f0a685b538e63b53f94c52e17', '2026-03-28 11:28:20'),
(60, 5, 15, 'ESTE', NULL, 'texto', '0a2a27aeee2f422b94199ebf56f081a1', '2026-03-30 17:49:01');

-- --------------------------------------------------------

--
-- Estrutura para tabela `chat_participantes`
--

CREATE TABLE `chat_participantes` (
  `conversa_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fixada` tinyint(1) DEFAULT 0,
  `silenciada` tinyint(1) DEFAULT 0,
  `ultima_leitura_at` datetime DEFAULT current_timestamp(),
  `entrada_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `chat_participantes`
--

INSERT INTO `chat_participantes` (`conversa_id`, `usuario_id`, `fixada`, `silenciada`, `ultima_leitura_at`, `entrada_em`) VALUES
(1, 5, 1, 0, '2026-03-30 13:58:08', '2026-01-19 23:30:58'),
(1, 13, 0, 0, '2026-02-25 18:42:23', '2026-01-19 23:30:58'),
(2, 5, 0, 0, '2026-01-20 13:43:51', '2026-01-20 13:43:51'),
(2, 12, 0, 0, '2026-01-20 13:43:51', '2026-01-20 13:43:51'),
(3, 12, 1, 0, '2026-01-22 18:00:19', '2026-01-20 18:54:17'),
(3, 13, 1, 0, '2026-01-22 17:19:06', '2026-01-20 18:54:17'),
(4, 13, 0, 0, '2026-01-23 11:35:00', '2026-01-22 19:57:31'),
(4, 15, 0, 0, '2026-01-22 19:57:31', '2026-01-22 19:57:31'),
(5, 5, 0, 0, '2026-01-23 17:34:55', '2026-01-23 11:37:29'),
(5, 15, 0, 0, '2026-03-23 12:53:05', '2026-01-23 11:37:29'),
(6, 4, 0, 0, '2026-01-23 17:33:12', '2026-01-23 17:33:12'),
(6, 5, 0, 0, '2026-01-30 14:24:39', '2026-01-23 17:33:12'),
(6, 9, 0, 0, '2026-01-23 17:33:12', '2026-01-23 17:33:12'),
(6, 11, 0, 0, '2026-01-23 17:33:12', '2026-01-23 17:33:12'),
(6, 12, 0, 0, '2026-01-28 10:38:33', '2026-01-23 17:33:12'),
(6, 13, 0, 0, '2026-02-09 17:05:53', '2026-01-23 17:33:12'),
(6, 14, 0, 0, '2026-01-23 17:33:12', '2026-01-23 17:33:12'),
(6, 15, 0, 0, '2026-01-30 15:53:43', '2026-01-23 17:33:12'),
(7, 12, 0, 0, '2026-01-26 20:57:13', '2026-01-26 20:57:13'),
(7, 15, 1, 0, '2026-01-27 16:29:41', '2026-01-26 20:57:13'),
(8, 5, 0, 0, '2026-01-28 19:51:27', '2026-01-28 19:51:27'),
(8, 9, 0, 0, '2026-01-28 19:51:27', '2026-01-28 19:51:27'),
(9, 9, 0, 0, '2026-01-30 12:06:10', '2026-01-30 12:06:10'),
(9, 13, 0, 0, '2026-01-30 12:06:10', '2026-01-30 12:06:10'),
(10, 13, 0, 0, '2026-01-30 12:07:45', '2026-01-30 12:07:45'),
(10, 14, 0, 0, '2026-01-30 12:07:45', '2026-01-30 12:07:45'),
(11, 5, 0, 0, '2026-02-01 14:08:04', '2026-02-01 14:08:04'),
(11, 13, 0, 0, '2026-02-01 14:08:04', '2026-02-01 14:08:04'),
(12, 11, 0, 0, '2026-03-01 14:04:03', '2026-03-01 14:04:03'),
(12, 15, 0, 0, '2026-03-01 14:04:03', '2026-03-01 14:04:03'),
(13, 5, 0, 0, '2026-03-23 18:27:25', '2026-03-23 18:27:25'),
(13, 15, 0, 0, '2026-03-23 18:27:25', '2026-03-23 18:27:25'),
(14, 5, 0, 0, '2026-03-30 21:06:08', '2026-03-30 21:06:08'),
(15, 5, 0, 0, '2026-04-01 13:11:30', '2026-04-01 13:11:30'),
(15, 11, 0, 0, '2026-04-01 13:11:30', '2026-04-01 13:11:30');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Cidades`
--

CREATE TABLE `Cidades` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `id_estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Cidades`
--

INSERT INTO `Cidades` (`id`, `nome`, `id_estado`) VALUES
(1, 'Teste', 24),
(2, 'Abdon Batista', 24),
(3, 'Abelardo Luz', 24),
(4, 'Agrolândia', 24),
(5, 'Agronômica', 24),
(6, 'Água Doce', 24),
(7, 'Águas de Chapecó', 24),
(8, 'Águas Frias', 24),
(9, 'Águas Mornas', 24),
(10, 'Alfredo Wagner', 24),
(11, 'Alto Bela Vista', 24),
(12, 'Anchieta', 24),
(13, 'Angelina', 24),
(14, 'Anita Garibaldi', 24),
(15, 'Anitápolis', 24),
(16, 'Antônio Carlos', 24),
(17, 'Apiúna', 24),
(18, 'Arabutã', 24),
(19, 'Araquari', 24),
(20, 'Araranguá', 24),
(21, 'Armazém', 24),
(22, 'Arroio Trinta', 24),
(23, 'Arvoredo', 24),
(24, 'Ascurra', 24),
(25, 'Atalanta', 24),
(26, 'Aurora', 24),
(27, 'Balneário Arroio do Silva', 24),
(28, 'Balneário Barra do Sul', 24),
(29, 'Balneário Camboriú', 24),
(30, 'Balneário Gaivota', 24),
(31, 'Balneário Piçarras', 24),
(32, 'Balneário Rincão', 24),
(33, 'Bandeirante', 24),
(34, 'Barra Bonita', 24),
(35, 'Barra Velha', 24),
(36, 'Bela Vista do Toldo', 24),
(37, 'Belmonte', 24),
(38, 'Benedito Novo', 24),
(39, 'Biguaçu', 24),
(40, 'Blumenau', 24),
(41, 'Bocaina do Sul', 24),
(42, 'Bom Jardim da Serra', 24),
(43, 'Bom Jesus', 24),
(44, 'Bom Jesus do Oeste', 24),
(45, 'Bom Retiro', 24),
(46, 'Bombinhas', 24),
(47, 'Botuverá', 24),
(48, 'Braço do Norte', 24),
(49, 'Braço do Trombudo', 24),
(50, 'Brunópolis', 24),
(51, 'Brusque', 24),
(52, 'Caçador', 24),
(53, 'Caibi', 24),
(54, 'Calmon', 24),
(55, 'Camboriú', 24),
(56, 'Campo Alegre', 24),
(57, 'Campo Belo do Sul', 24),
(58, 'Campo Erê', 24),
(59, 'Campos Novos', 24),
(60, 'Canelinha', 24),
(61, 'Canoinhas', 24),
(62, 'Capão Alto', 24),
(63, 'Capinzal', 24),
(64, 'Capivari de Baixo', 24),
(65, 'Catanduvas', 24),
(66, 'Caxambu do Sul', 24),
(67, 'Celso Ramos', 24),
(68, 'Cerro Negro', 24),
(69, 'Chapadão do Lageado', 24),
(70, 'Chapecó', 24),
(71, 'Cocal do Sul', 24),
(72, 'Concórdia', 24),
(73, 'Cordilheira Alta', 24),
(74, 'Coronel Freitas', 24),
(75, 'Coronel Martins', 24),
(76, 'Correia Pinto', 24),
(77, 'Corupá', 24),
(78, 'Criciúma', 24),
(79, 'Cunha Porã', 24),
(80, 'Cunhataí', 24),
(81, 'Curitibanos', 24),
(82, 'Descanso', 24),
(83, 'Dionísio Cerqueira', 24),
(84, 'Dona Emma', 24),
(85, 'Doutor Pedrinho', 24),
(86, 'Entre Rios', 24),
(87, 'Ermo', 24),
(88, 'Erval Velho', 24),
(89, 'Faxinal dos Guedes', 24),
(90, 'Flor do Sertão', 24),
(91, 'Florianópolis', 24),
(92, 'Formosa do Sul', 24),
(93, 'Forquilhinha', 24),
(94, 'Fraiburgo', 24),
(95, 'Frei Rogério', 24),
(96, 'Galvão', 24),
(97, 'Garopaba', 24),
(98, 'Garuva', 24),
(99, 'Gaspar', 24),
(100, 'Governador Celso Ramos', 24),
(101, 'Grão-Pará', 24),
(102, 'Gravatal', 24),
(103, 'Guabiruba', 24),
(104, 'Guaraciaba', 24),
(105, 'Guaramirim', 24),
(106, 'Guarujá do Sul', 24),
(107, 'Guatambú', 24),
(108, 'Herval d\'Oeste', 24),
(109, 'Ibiam', 24),
(110, 'Ibicaré', 24),
(111, 'Ibirama', 24),
(112, 'Içara', 24),
(113, 'Ilhota', 24),
(114, 'Imaruí', 24),
(115, 'Imbituba', 24),
(116, 'Imbuia', 24),
(117, 'Indaial', 24),
(118, 'Iomerê', 24),
(119, 'Ipira', 24),
(120, 'Iporã do Oeste', 24),
(121, 'Ipuaçu', 24),
(122, 'Ipumirim', 24),
(123, 'Iraceminha', 24),
(124, 'Irani', 24),
(125, 'Irati', 24),
(126, 'Irineópolis', 24),
(127, 'Itá', 24),
(128, 'Itaiópolis', 24),
(129, 'Itajaí', 24),
(130, 'Itapema', 24),
(131, 'Itapiranga', 24),
(132, 'Itapoá', 24),
(133, 'Ituporanga', 24),
(134, 'Jaborá', 24),
(135, 'Jacinto Machado', 24),
(136, 'Jaguaruna', 24),
(137, 'Jaraguá do Sul', 24),
(138, 'Jardinópolis', 24),
(139, 'Joaçaba', 24),
(140, 'Joinville', 24),
(141, 'José Boiteux', 24),
(142, 'Jupiá', 24),
(143, 'Lacerdópolis', 24),
(144, 'Lages', 24),
(145, 'Laguna', 24),
(146, 'Lajeado Grande', 24),
(147, 'Laurentino', 24),
(148, 'Lauro Müller', 24),
(149, 'Lebon Régis', 24),
(150, 'Leoberto Leal', 24),
(151, 'Lindóia do Sul', 24),
(152, 'Lontras', 24),
(153, 'Luiz Alves', 24),
(154, 'Luzerna', 24),
(155, 'Macieira', 24),
(156, 'Mafra', 24),
(157, 'Major Gercino', 24),
(158, 'Major Vieira', 24),
(159, 'Maracajá', 24),
(160, 'Maravilha', 24),
(161, 'Marema', 24),
(162, 'Massaranduba', 24),
(163, 'Matos Costa', 24),
(164, 'Meleiro', 24),
(165, 'Mirim Doce', 24),
(166, 'Modelo', 24),
(167, 'Mondaí', 24),
(168, 'Monte Carlo', 24),
(169, 'Monte Castelo', 24),
(170, 'Morro da Fumaça', 24),
(171, 'Morro Grande', 24),
(172, 'Navegantes', 24),
(173, 'Nova Erechim', 24),
(174, 'Nova Itaberaba', 24),
(175, 'Nova Trento', 24),
(176, 'Nova Veneza', 24),
(177, 'Novo Horizonte', 24),
(178, 'Orleans', 24),
(179, 'Otacílio Costa', 24),
(180, 'Ouro', 24),
(181, 'Ouro Verde', 24),
(182, 'Paial', 24),
(183, 'Painel', 24),
(184, 'Palhoça', 24),
(185, 'Palma Sola', 24),
(186, 'Palmeira', 24),
(187, 'Palmitos', 24),
(188, 'Papanduva', 24),
(189, 'Paraíso', 24),
(190, 'Passo de Torres', 24),
(191, 'Passos Maia', 24),
(192, 'Paulo Lopes', 24),
(193, 'Pedras Grandes', 24),
(194, 'Penha', 24),
(195, 'Peritiba', 24),
(196, 'Pescaria Brava', 24),
(197, 'Petrolândia', 24),
(198, 'Pinhalzinho', 24),
(199, 'Pinheiro Preto', 24),
(200, 'Piratuba', 24),
(201, 'Planalto Alegre', 24),
(202, 'Pomerode', 24),
(203, 'Ponte Alta', 24),
(204, 'Ponte Alta do Norte', 24),
(205, 'Ponte Serrada', 24),
(206, 'Porto Belo', 24),
(207, 'Porto União', 24),
(208, 'Pouso Redondo', 24),
(209, 'Praia Grande', 24),
(210, 'Presidente Castello Branco', 24),
(211, 'Presidente Getúlio', 24),
(212, 'Presidente Nereu', 24),
(213, 'Princesa', 24),
(214, 'Quilombo', 24),
(215, 'Rancho Queimado', 24),
(216, 'Rio das Antas', 24),
(217, 'Rio do Campo', 24),
(218, 'Rio do Oeste', 24),
(219, 'Rio do Sul', 24),
(220, 'Rio dos Cedros', 24),
(221, 'Rio Fortuna', 24),
(222, 'Rio Negrinho', 24),
(223, 'Rio Rufino', 24),
(224, 'Riqueza', 24),
(225, 'Rodeio', 24),
(226, 'Romelândia', 24),
(227, 'Salete', 24),
(228, 'Saltinho', 24),
(229, 'Salto Veloso', 24),
(230, 'Sangão', 24),
(231, 'Santa Cecília', 24),
(232, 'Santa Helena', 24),
(233, 'Santa Rosa de Lima', 24),
(234, 'Santa Rosa do Sul', 24),
(235, 'Santa Terezinha', 24),
(236, 'Santa Terezinha do Progresso', 24),
(237, 'Santiago do Sul', 24),
(238, 'Santo Amaro da Imperatriz', 24),
(239, 'São Bento do Sul', 24),
(240, 'São Bernardino', 24),
(241, 'São Bonifácio', 24),
(242, 'São Carlos', 24),
(243, 'São Cristóvão do Sul', 24),
(244, 'São Domingos', 24),
(245, 'São Francisco do Sul', 24),
(246, 'São João Batista', 24),
(247, 'São João do Itaperiú', 24),
(248, 'São João do Oeste', 24),
(249, 'São João do Sul', 24),
(250, 'São Joaquim', 24),
(251, 'São José', 24),
(252, 'São José do Cedro', 24),
(253, 'São José do Cerrito', 24),
(254, 'São Lourenço do Oeste', 24),
(255, 'São Ludgero', 24),
(256, 'São Martinho', 24),
(257, 'São Miguel da Boa Vista', 24),
(258, 'São Miguel do Oeste', 24),
(259, 'São Pedro de Alcântara', 24),
(260, 'Saudades', 24),
(261, 'Schroeder', 24),
(262, 'Seara', 24),
(263, 'Serra Alta', 24),
(264, 'Siderópolis', 24),
(265, 'Sombrio', 24),
(266, 'Sul Brasil', 24),
(267, 'Taió', 24),
(268, 'Tangará', 24),
(269, 'Tigrinhos', 24),
(270, 'Tijucas', 24),
(271, 'Timbé do Sul', 24),
(272, 'Timbó', 24),
(273, 'Timbó Grande', 24),
(274, 'Três Barras', 24),
(275, 'Treviso', 24),
(276, 'Treze de Maio', 24),
(277, 'Treze Tílias', 24),
(278, 'Trombudo Central', 24),
(279, 'Tubarão', 24),
(280, 'Tunápolis', 24),
(281, 'Turvo', 24),
(282, 'União do Oeste', 24),
(283, 'Urubici', 24),
(284, 'Urupema', 24),
(285, 'Urussanga', 24),
(286, 'Vargeão', 24),
(287, 'Vargem', 24),
(288, 'Vargem Bonita', 24),
(289, 'Vidal Ramos', 24),
(290, 'Videira', 24),
(291, 'Vitor Meireles', 24),
(292, 'Witmarsum', 24),
(293, 'Xanxerê', 24),
(294, 'Xavantina', 24),
(295, 'Xaxim', 24),
(296, 'Zortéa', 24);

-- --------------------------------------------------------

--
-- Estrutura para tabela `Comentarios`
--

CREATE TABLE `Comentarios` (
  `id` int(11) NOT NULL,
  `id_postagem` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_comentario_pai` int(11) DEFAULT NULL,
  `conteudo_texto` text NOT NULL,
  `status` enum('ativo','inativo','excluido_pelo_usuario') NOT NULL DEFAULT 'ativo',
  `data_comentario` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Comentarios`
--

INSERT INTO `Comentarios` (`id`, `id_postagem`, `id_usuario`, `id_comentario_pai`, `conteudo_texto`, `status`, `data_comentario`) VALUES
(1, 3, 9, NULL, 'Olá', 'ativo', '2025-10-03 20:57:18'),
(2, 3, 9, 1, 'ola', 'ativo', '2025-10-03 21:39:21'),
(3, 3, 9, NULL, 'eae', 'ativo', '2025-10-03 21:39:32'),
(4, 3, 5, 1, 'opa', 'excluido_pelo_usuario', '2025-10-03 21:39:46'),
(5, 3, 5, NULL, 'boaa meu povo', 'ativo', '2025-10-03 21:40:28'),
(6, 2, 5, NULL, 'opa', 'ativo', '2025-10-06 15:16:45'),
(7, 3, 5, 1, 'boa tarde tudo bem?', 'ativo', '2025-10-06 15:55:03'),
(8, 3, 5, NULL, 'Opa', 'ativo', '2025-10-08 19:09:49'),
(9, 3, 9, NULL, 'legal', 'ativo', '2025-10-10 16:53:13'),
(10, 5, 12, NULL, 'Q legal', 'ativo', '2025-10-13 01:44:39'),
(11, 3, 12, NULL, 'Tudooo', 'ativo', '2025-10-13 01:45:39'),
(12, 2, 12, NULL, 'Opa', 'ativo', '2025-10-13 01:45:51'),
(13, 7, 5, NULL, 'ola', 'ativo', '2025-10-14 14:58:59'),
(14, 7, 5, 13, 'TESTE', 'ativo', '2025-10-15 14:13:57'),
(15, 8, 5, NULL, 'teste', 'ativo', '2025-10-15 14:19:10'),
(16, 10, 5, NULL, 'Teste', 'ativo', '2025-10-16 23:03:04'),
(17, 12, 5, NULL, 'Teste', 'ativo', '2025-10-16 23:03:26'),
(18, 10, 5, NULL, 'Aa', 'ativo', '2025-10-17 03:18:06'),
(19, 10, 5, NULL, 'Dddd', 'ativo', '2025-10-17 03:18:11'),
(20, 10, 5, 19, 'Serd', 'ativo', '2025-10-17 03:18:14'),
(21, 10, 5, NULL, 'Hehejdjd', 'ativo', '2025-10-17 03:18:19'),
(22, 10, 5, NULL, 'Ysydududu', 'ativo', '2025-10-17 03:18:23'),
(23, 10, 5, NULL, 'Uxhdhdjrjd', 'ativo', '2025-10-17 03:18:26'),
(24, 10, 5, NULL, 'teste', 'ativo', '2025-10-17 14:27:45'),
(25, 10, 5, NULL, 'teste', 'ativo', '2025-10-17 14:37:49'),
(26, 10, 5, NULL, 'teste 1133', 'ativo', '2025-10-17 14:37:58'),
(27, 10, 5, NULL, 'teste 1150', 'ativo', '2025-10-17 14:55:19'),
(28, 10, 5, NULL, 'teste 1154', 'ativo', '2025-10-17 14:59:23'),
(29, 10, 5, NULL, 'teste 12', 'ativo', '2025-10-17 15:05:02'),
(30, 13, 5, NULL, 'teste', 'ativo', '2025-10-17 15:35:41'),
(31, 13, 5, NULL, 'teste', 'ativo', '2025-10-17 15:45:37'),
(32, 13, 5, NULL, 'tessadasa', 'ativo', '2025-10-17 15:45:41'),
(33, 13, 5, NULL, 'teetasf', 'ativo', '2025-10-17 15:45:43'),
(34, 13, 5, NULL, 'teste 1252', 'ativo', '2025-10-17 15:57:27'),
(35, 13, 5, NULL, 'teste', 'ativo', '2025-10-17 16:40:40'),
(36, 13, 5, NULL, 'AGAGDAD', 'ativo', '2025-10-17 16:52:07'),
(37, 14, 5, NULL, 'TESTE', 'ativo', '2025-10-17 16:53:29'),
(38, 14, 5, NULL, 'tesssss', 'ativo', '2025-10-17 17:09:09'),
(39, 13, 5, NULL, 'tesssteetet', 'ativo', '2025-10-17 17:09:19'),
(40, 13, 5, NULL, 'Teste', 'ativo', '2025-10-17 18:51:10'),
(41, 13, 5, 30, 'ollaaaa', 'ativo', '2025-10-17 20:29:51'),
(42, 13, 5, 30, 'teeetet', 'ativo', '2025-10-18 21:51:32'),
(43, 15, 5, NULL, 'teste', 'ativo', '2025-10-31 20:58:15'),
(44, 13, 5, 30, 'Teste', 'ativo', '2025-11-01 18:54:36'),
(45, 13, 5, 30, 'Teste', 'ativo', '2025-11-01 18:54:43'),
(46, 15, 14, NULL, 'Kkkkk', 'ativo', '2025-11-07 01:20:18'),
(47, 8, 14, NULL, 'Top', 'ativo', '2025-11-07 01:22:58'),
(48, 15, 5, NULL, 'teste', 'ativo', '2025-11-08 14:57:22'),
(49, 16, 5, NULL, 'Teste', 'excluido_pelo_usuario', '2025-11-09 14:30:23'),
(50, 31, 5, NULL, 'Lorem ipsum dolor sit amet. Non quaerat placeat aut voluptatem dolorem non inventore velit et voluptatum eligendi sed aliquam autem. Ab rerum excepturi sed veritatis sapiente aut reiciendis quia et odit perspiciatis quo asperiores officiis. Aut vitae beatae ab suscipit consequatur non laborum veritatis aut commodi repudiandae qui voluptatum incidunt et sapiente earum eum autem quas!   Ut voluptas doloremque qui minima velit rem omnis distinctio. Ad temporibus odit est nulla blanditiis sed itaque dignissimos qui accusamus dolor et mollitia pariatur in quos voluptas aut illum quod.   Sed labore internos sit sequi omnis nam unde accusantium et magnam modi eum quia illo ut ipsa voluptas sed omnis asperiores. Id nobis voluptatem sit velit quisquam sed Quis cumque ex totam officia. Cum dignissimos dolores ea necessitatibus cupiditate ut alias accusantium eos alias harum? Et dicta unde est harum aperiam ut expedita ipsa et facilis voluptas sed sapiente earum. TESTE 2', 'ativo', '2025-11-10 15:30:59'),
(51, 16, 5, NULL, 'Lorem ipsum dolor sit amet. Non quaerat placeat aut voluptatem dolorem non inventore velit et voluptatum eligendi sed aliquam autem. Ab rerum excepturi sed veritatis sapiente aut reiciendis quia et odit perspiciatis quo asperiores officiis. Aut vitae beatae ab suscipit consequatur non laborum veritatis aut commodi repudiandae qui voluptatum incidunt et sapiente earum eum autem quas!  Ut voluptas doloremque qui minima velit rem omnis distinctio. Ad temporibus odit est nulla blanditiis sed itaque dignissimos qui accusamus dolor et mollitia pariatur in quos voluptas aut illum quod.  Sed labore internos sit sequi omnis nam unde accusantium et magnam modi eum quia illo ut ipsa voluptas sed omnis asperiores. Id nobis voluptatem sit velit quisquam sed Quis cumque ex totam officia. Cum dignissimos dolores ea necessitatibus cupiditate ut alias accusantium eos alias harum? Et dicta unde est harum aperiam ut expedita ipsa et facilis voluptas sed sapiente earum. TESTE 2', 'ativo', '2025-11-10 19:17:41'),
(52, 16, 12, NULL, 'aaaaa', 'inativo', '2025-11-10 21:09:07'),
(53, 16, 5, NULL, 'Teste', 'ativo', '2025-11-10 23:08:47'),
(54, 16, 5, NULL, 'Teste', 'ativo', '2025-11-10 23:10:25'),
(55, 35, 5, NULL, 'teste', 'ativo', '2025-11-12 15:10:47'),
(56, 35, 5, NULL, 'testr', 'ativo', '2025-11-12 15:11:07'),
(57, 35, 5, 55, 'aaa', 'ativo', '2025-11-12 15:12:34'),
(58, 36, 5, NULL, 'Opa', 'ativo', '2025-12-20 22:14:25'),
(59, 40, 13, NULL, 'Opa', 'ativo', '2025-12-21 02:43:10'),
(60, 52, 13, NULL, 'Mulher burra kk', 'ativo', '2025-12-25 19:31:51'),
(61, 57, 5, NULL, 'teste', 'ativo', '2025-12-29 17:42:14'),
(62, 57, 5, 61, 'teste2', 'ativo', '2025-12-29 17:42:29'),
(63, 64, 5, NULL, 'teste', 'ativo', '2026-01-07 16:25:57'),
(64, 64, 13, NULL, 'Outro teste', 'ativo', '2026-01-07 19:04:00'),
(65, 64, 13, 63, 'Teste 2', 'ativo', '2026-01-07 19:04:07'),
(66, 68, 5, NULL, 'Não funciona apertar nas enquetes', 'ativo', '2026-01-08 00:46:35'),
(67, 68, 13, 66, 'É verdade', 'ativo', '2026-01-08 02:09:17'),
(68, 68, 13, NULL, 'Nao funciona', 'ativo', '2026-01-08 02:09:33'),
(69, 68, 13, NULL, 'L', 'ativo', '2026-01-08 02:11:09'),
(70, 68, 13, NULL, 'Tem uma div em cuma', 'ativo', '2026-01-08 02:13:32'),
(71, 68, 13, NULL, 'Tem uma div em cima de outra div ai, div duplicada, faz o post ficar menor', 'ativo', '2026-01-08 02:15:23'),
(72, 67, 13, NULL, 'Teste', 'ativo', '2026-01-08 22:05:59'),
(73, 73, 5, NULL, 'Teste', 'ativo', '2026-01-09 21:34:50'),
(74, 71, 13, NULL, 'Teste', 'ativo', '2026-01-14 01:29:29'),
(75, 74, 5, NULL, 'Teste', 'ativo', '2026-01-16 22:19:18'),
(76, 75, 5, NULL, 'teste', 'ativo', '2026-01-19 15:08:22'),
(77, 74, 5, NULL, 'teste', 'ativo', '2026-01-19 15:26:53'),
(78, 76, 5, NULL, 'teste', 'ativo', '2026-01-19 15:44:29'),
(79, 80, 12, NULL, 'teste', 'ativo', '2026-01-31 20:54:57'),
(80, 81, 13, NULL, 'teste', 'ativo', '2026-01-31 21:10:53'),
(81, 81, 13, NULL, 'teste', 'ativo', '2026-01-31 21:37:12'),
(82, 81, 12, NULL, 'teste', 'ativo', '2026-01-31 21:37:34'),
(83, 80, 12, NULL, 'teste', 'ativo', '2026-01-31 21:38:07'),
(84, 81, 5, NULL, 'teste', 'ativo', '2026-02-01 13:17:10'),
(85, 82, 5, NULL, 'testado', 'ativo', '2026-02-01 15:37:35'),
(86, 85, 13, NULL, 'teste', 'ativo', '2026-02-03 15:25:04'),
(87, 88, 5, NULL, 'puta', 'ativo', '2026-02-18 15:08:52'),
(88, 69, 5, NULL, 'Oba', 'ativo', '2026-02-23 11:20:42'),
(89, 92, 13, NULL, 'teste 2', 'ativo', '2026-02-23 20:59:38'),
(90, 41, 13, NULL, 'teste', 'ativo', '2026-02-23 21:17:08'),
(91, 92, 13, NULL, 'fsdklfgndklgnjkdfn gdfj nfdjkn gfjkdn jkdnh jdfnh jkh ndfjkh jkfhnjdngfgfgnfg hgnh fg', 'excluido_pelo_usuario', '2026-02-23 21:25:59'),
(92, 92, 15, NULL, 'teste 3', 'ativo', '2026-02-23 21:32:47'),
(93, 92, 15, NULL, 'teste4', 'ativo', '2026-03-24 16:05:32'),
(94, 85, 15, NULL, 'teste', 'ativo', '2026-03-30 20:38:21'),
(95, 106, 5, NULL, 'teste', 'ativo', '2026-03-30 20:38:53'),
(96, 106, 15, NULL, 'tese', 'ativo', '2026-03-30 20:46:10'),
(97, 107, 15, NULL, 'teste', 'ativo', '2026-04-01 16:56:05'),
(98, 107, 15, NULL, 'teste', 'ativo', '2026-04-01 17:09:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Comentarios_Edicoes`
--

CREATE TABLE `Comentarios_Edicoes` (
  `id` int(11) NOT NULL,
  `id_comentario` int(11) NOT NULL,
  `conteudo_antigo` text NOT NULL,
  `data_edicao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Comentarios_Edicoes`
--

INSERT INTO `Comentarios_Edicoes` (`id`, `id_comentario`, `conteudo_antigo`, `data_edicao`) VALUES
(1, 7, 'boa tarde', '2025-10-06 17:59:30'),
(2, 5, 'boaa', '2025-10-06 18:00:03'),
(3, 50, 'Lorem ipsum dolor sit amet. Non quaerat placeat aut voluptatem dolorem non inventore velit et voluptatum eligendi sed aliquam autem. Ab rerum excepturi sed veritatis sapiente aut reiciendis quia et odit perspiciatis quo asperiores officiis. Aut vitae beatae ab suscipit consequatur non laborum veritatis aut commodi repudiandae qui voluptatum incidunt et sapiente earum eum autem quas!   Ut voluptas doloremque qui minima velit rem omnis distinctio. Ad temporibus odit est nulla blanditiis sed itaque dignissimos qui accusamus dolor et mollitia pariatur in quos voluptas aut illum quod.   Sed labore internos sit sequi omnis nam unde accusantium et magnam modi eum quia illo ut ipsa voluptas sed omnis asperiores. Id nobis voluptatem sit velit quisquam sed Quis cumque ex totam officia. Cum dignissimos dolores ea necessitatibus cupiditate ut alias accusantium eos alias harum? Et dicta unde est harum aperiam ut expedita ipsa et facilis voluptas sed sapiente earum. TESTE', '2025-11-10 15:49:52'),
(4, 89, 'teste', '2026-02-23 21:16:10'),
(5, 92, 'teste', '2026-02-24 00:01:16'),
(6, 92, 'teste 2', '2026-03-11 14:13:42');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Configuracoes`
--

CREATE TABLE `Configuracoes` (
  `id` int(11) NOT NULL,
  `chave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Configuracoes`
--

INSERT INTO `Configuracoes` (`id`, `chave`, `valor`) VALUES
(1, 'site_nome', 'Social BR'),
(2, 'site_descricao', 'Sua rede social brasileira OFICIAL'),
(3, 'site_url', 'https://seusite.com.br'),
(4, 'email_contato', 'suporte@socialbr.lol'),
(5, 'url_logo_header', 'assets/images/logo.png'),
(6, 'url_favicon', 'assets/images/favicon.png'),
(7, 'cor_tema_primaria', '#0c2d54'),
(8, 'modo_manutencao', '0'),
(9, 'permite_cadastro', '1'),
(10, 'modo_dev', '0'),
(11, 'versao_assets', '1.0.0'),
(12, 'MODULO_COMPARTILHAR_ATIVO', '1'),
(13, 'CIDADE_PADRAO_ID', '129'),
(14, 'modo_censura', '1');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Curtidas`
--

CREATE TABLE `Curtidas` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_postagem` int(11) NOT NULL,
  `data_curtida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Curtidas`
--

INSERT INTO `Curtidas` (`id`, `id_usuario`, `id_postagem`, `data_curtida`) VALUES
(1, 4, 2, '2025-10-01 17:29:01'),
(2, 5, 3, '2025-10-02 16:08:42'),
(3, 5, 2, '2025-10-03 20:56:42'),
(7, 5, 6, '2025-10-12 23:47:42'),
(8, 9, 3, '2025-10-12 23:57:38'),
(9, 9, 5, '2025-10-12 23:58:06'),
(11, 12, 6, '2025-10-13 01:45:30'),
(13, 12, 2, '2025-10-13 01:45:34'),
(24, 5, 7, '2025-10-14 22:56:16'),
(25, 12, 5, '2025-10-14 22:57:23'),
(26, 12, 3, '2025-10-14 22:57:25'),
(28, 5, 10, '2025-10-17 14:33:30'),
(41, 5, 14, '2025-10-17 17:09:06'),
(45, 14, 13, '2025-11-07 01:20:36'),
(46, 14, 8, '2025-11-07 01:22:47'),
(47, 14, 7, '2025-11-07 01:23:27'),
(48, 14, 6, '2025-11-07 01:23:28'),
(49, 14, 5, '2025-11-07 01:23:29'),
(50, 14, 3, '2025-11-07 01:23:30'),
(51, 14, 2, '2025-11-07 01:23:32'),
(53, 14, 15, '2025-11-07 21:02:22'),
(55, 5, 15, '2025-11-08 14:57:19'),
(57, 5, 17, '2025-11-09 13:39:11'),
(58, 5, 16, '2025-11-10 22:20:39'),
(59, 5, 13, '2025-11-11 00:34:35'),
(60, 5, 35, '2025-11-12 15:00:28'),
(62, 12, 40, '2025-12-18 18:12:17'),
(63, 12, 39, '2025-12-20 22:12:50'),
(64, 12, 41, '2025-12-20 22:12:52'),
(65, 12, 36, '2025-12-20 22:12:55'),
(66, 13, 2, '2025-12-21 02:27:31'),
(67, 13, 40, '2025-12-21 02:43:02'),
(69, 5, 55, '2025-12-28 17:33:34'),
(71, 13, 55, '2025-12-28 17:35:59'),
(78, 5, 57, '2025-12-29 17:41:45'),
(79, 12, 61, '2026-01-04 00:51:04'),
(80, 13, 61, '2026-01-04 16:13:10'),
(81, 13, 57, '2026-01-04 16:13:13'),
(82, 13, 56, '2026-01-04 16:13:14'),
(83, 13, 54, '2026-01-04 16:13:19'),
(84, 12, 55, '2026-01-05 14:52:23'),
(87, 15, 51, '2026-01-07 02:49:34'),
(88, 15, 50, '2026-01-07 02:49:36'),
(89, 15, 48, '2026-01-07 02:49:38'),
(91, 13, 64, '2026-01-07 19:04:14'),
(92, 5, 66, '2026-01-08 22:12:54'),
(93, 5, 65, '2026-01-08 22:12:57'),
(97, 5, 74, '2026-01-13 20:39:41'),
(171, 5, 76, '2026-01-19 15:01:01'),
(182, 5, 73, '2026-01-19 22:21:09'),
(185, 5, 80, '2026-01-31 18:22:34'),
(193, 13, 81, '2026-01-31 21:10:33'),
(194, 12, 80, '2026-01-31 21:24:14'),
(195, 13, 79, '2026-02-01 14:34:28'),
(196, 5, 82, '2026-02-01 15:37:23'),
(197, 13, 85, '2026-02-03 15:24:39'),
(198, 15, 56, '2026-02-05 00:37:35'),
(199, 15, 55, '2026-02-05 00:37:36'),
(200, 15, 61, '2026-02-05 00:37:38'),
(201, 15, 54, '2026-02-05 00:37:40'),
(202, 15, 53, '2026-02-05 00:37:45'),
(203, 5, 95, '2026-03-11 14:55:07'),
(204, 15, 101, '2026-03-30 18:37:27'),
(206, 15, 103, '2026-03-30 21:11:32'),
(207, 15, 107, '2026-03-30 23:58:17'),
(208, 15, 106, '2026-04-01 17:06:47');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Curtidas_Comentarios`
--

CREATE TABLE `Curtidas_Comentarios` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_comentario` int(11) NOT NULL,
  `data_curtida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Curtidas_Comentarios`
--

INSERT INTO `Curtidas_Comentarios` (`id`, `id_usuario`, `id_comentario`, `data_curtida`) VALUES
(2, 5, 5, '2025-10-09 00:06:06'),
(3, 5, 1, '2025-10-09 01:21:09'),
(10, 12, 13, '2025-10-14 18:17:03'),
(11, 12, 6, '2025-10-14 22:57:32'),
(12, 5, 10, '2025-10-14 22:58:12'),
(13, 5, 16, '2025-10-17 14:37:43'),
(22, 5, 30, '2025-10-17 15:57:17'),
(25, 5, 41, '2025-11-02 21:22:10'),
(26, 5, 50, '2025-11-10 16:23:13'),
(27, 5, 55, '2025-11-12 15:14:33'),
(28, 5, 57, '2025-11-12 15:14:35'),
(29, 13, 6, '2025-12-21 02:27:17'),
(30, 13, 12, '2025-12-21 02:27:18'),
(31, 13, 66, '2026-01-08 02:09:09'),
(32, 13, 83, '2026-01-31 21:38:31'),
(33, 15, 84, '2026-02-01 13:17:33'),
(40, 15, 95, '2026-03-30 20:45:18'),
(42, 15, 97, '2026-04-01 17:06:56');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Denuncias`
--

CREATE TABLE `Denuncias` (
  `id` int(11) NOT NULL,
  `id_usuario_denunciou` int(11) NOT NULL,
  `tipo_conteudo` enum('post','comentario','usuario') NOT NULL,
  `id_conteudo` int(11) NOT NULL,
  `motivo` text NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_denuncia` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pendente','revisado','ignorado','excluida_pelo_adm') NOT NULL DEFAULT 'pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Denuncias`
--

INSERT INTO `Denuncias` (`id`, `id_usuario_denunciou`, `tipo_conteudo`, `id_conteudo`, `motivo`, `descricao`, `data_denuncia`, `status`) VALUES
(1, 5, 'post', 7, 'Bullying, assédio ou abuso', NULL, '2025-10-14 21:38:31', 'revisado'),
(2, 12, 'post', 10, 'Conteúdo violento, que promove o ódio ou é perturbador', NULL, '2025-10-16 20:56:16', 'revisado'),
(3, 12, 'post', 14, 'Bullying, assédio ou abuso', NULL, '2025-10-17 17:34:31', 'revisado'),
(4, 12, 'post', 13, 'Golpe, fraude ou informação falsa', NULL, '2025-10-17 17:34:58', 'pendente'),
(5, 12, 'post', 14, 'Bullying, assédio ou abuso', NULL, '2025-10-17 17:40:48', 'pendente'),
(6, 12, 'post', 13, 'Golpe, fraude ou informação falsa', NULL, '2025-10-17 17:40:54', 'pendente'),
(7, 12, 'post', 13, 'Bullying, assédio ou abuso', NULL, '2025-10-17 17:40:58', 'revisado'),
(8, 12, 'post', 14, 'Spam', NULL, '2025-10-17 17:41:06', 'revisado'),
(9, 5, 'usuario', 12, 'Perfil Falso', NULL, '2025-10-17 18:17:01', 'revisado'),
(10, 12, 'post', 15, 'Spam', NULL, '2025-10-18 18:12:38', 'revisado'),
(11, 5, 'usuario', 12, 'Perfil Falso', NULL, '2025-10-31 23:25:55', 'pendente'),
(12, 5, 'post', 8, 'Conteúdo violento, que promove o ódio ou é perturbador', NULL, '2025-11-06 20:53:47', 'pendente'),
(13, 5, 'usuario', 12, 'Perfil Falso', NULL, '2025-11-06 20:54:07', 'ignorado'),
(14, 5, 'post', 7, 'Venda ou promoção de itens restritos', NULL, '2025-11-06 20:54:21', 'pendente'),
(15, 14, 'post', 13, 'Bullying, assédio ou abuso', NULL, '2025-11-07 01:20:58', 'pendente'),
(16, 14, 'post', 8, 'Spam', NULL, '2025-11-07 01:23:09', 'pendente'),
(17, 5, 'usuario', 14, 'Perfil Falso', NULL, '2025-11-07 02:05:54', 'pendente'),
(18, 14, 'post', 27, 'Bullying, assédio ou abuso', NULL, '2025-11-08 16:25:01', 'pendente'),
(19, 5, 'post', 17, 'Bullying, assédio ou abuso', NULL, '2025-11-08 17:44:24', 'pendente'),
(20, 12, 'post', 31, 'Conteúdo violento, que promove o ódio ou é perturbador', NULL, '2025-11-10 14:30:41', 'pendente'),
(21, 12, 'usuario', 14, 'Conteúdo impróprio no perfil (foto, nome)', NULL, '2025-11-10 15:03:25', 'ignorado'),
(22, 5, 'comentario', 52, 'Bullying, assédio ou abuso', NULL, '2025-11-10 22:20:49', 'pendente'),
(23, 5, 'comentario', 52, 'Spam', NULL, '2025-11-10 22:32:59', 'pendente'),
(24, 5, 'usuario', 14, 'Perfil Falso', NULL, '2025-11-16 02:03:04', 'pendente'),
(25, 13, 'post', 40, 'Conteúdo violento, que promove o ódio ou é perturbador', NULL, '2025-12-21 02:28:04', 'pendente'),
(26, 13, 'post', 45, 'Bullying, assédio ou abuso', NULL, '2025-12-21 19:34:29', 'revisado'),
(27, 13, 'post', 44, 'Golpe, fraude ou informação falsa', NULL, '2025-12-21 19:34:34', 'revisado'),
(28, 12, 'post', 46, 'Conteúdo violento, que promove o ódio ou é perturbador', NULL, '2025-12-21 22:00:03', 'ignorado'),
(29, 13, 'post', 85, 'Bullying, assédio ou abuso', 'teste', '2026-02-25 17:58:17', 'pendente'),
(30, 13, 'post', 85, 'Spam', 'informação adicional', '2026-02-25 18:05:42', 'pendente'),
(31, 13, 'post', 85, 'Bullying, assédio ou abuso', 'FDASFSDF', '2026-02-25 20:29:52', 'pendente'),
(32, 13, 'post', 84, 'Golpe, fraude ou informação falsa', 'spam', '2026-02-25 20:31:49', 'pendente'),
(33, 13, 'post', 84, 'Golpe, fraude ou informação falsa', 'fsdfs', '2026-02-25 20:34:48', 'pendente'),
(34, 15, 'post', 92, 'Bullying, assédio ou abuso', 'Teste', '2026-02-26 02:04:52', 'pendente'),
(35, 15, 'post', 98, 'Discurso de ódio', 'palavrao', '2026-03-04 20:22:32', 'pendente');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Enquetes`
--

CREATE TABLE `Enquetes` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `pergunta` varchar(255) NOT NULL,
  `data_expiracao` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Enquetes`
--

INSERT INTO `Enquetes` (`id`, `post_id`, `pergunta`, `data_expiracao`, `created_at`) VALUES
(1, 44, 'pergunta', NULL, '2025-12-21 18:05:51'),
(2, 46, 'Testexxxx', NULL, '2025-12-21 20:41:02'),
(3, 68, 'Teste', NULL, '2026-01-07 19:05:01'),
(4, 78, 'Voce jogam', NULL, '2026-01-20 22:27:58'),
(5, 94, 'teste', NULL, '2026-02-26 19:47:41'),
(6, 95, 'pergunta', NULL, '2026-02-28 14:54:24'),
(7, 107, 'ESTAO GOSTANDO', NULL, '2026-03-30 21:13:36');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Enquete_Opcoes`
--

CREATE TABLE `Enquete_Opcoes` (
  `id` int(11) NOT NULL,
  `enquete_id` int(11) NOT NULL,
  `opcao_texto` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Enquete_Opcoes`
--

INSERT INTO `Enquete_Opcoes` (`id`, `enquete_id`, `opcao_texto`) VALUES
(1, 1, 'resposta1'),
(2, 1, 'resposta2'),
(3, 1, 'resposta 3'),
(4, 2, 'Nsbshs'),
(5, 2, 'Xxzz'),
(6, 3, '1'),
(7, 3, '2'),
(8, 3, '3'),
(9, 4, 'Sim'),
(10, 4, 'Nao'),
(11, 5, '1'),
(12, 5, '2'),
(13, 6, 'resposta1'),
(14, 6, 'resposta2'),
(15, 7, 'SIM'),
(16, 7, 'NAO');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Enquete_Votos`
--

CREATE TABLE `Enquete_Votos` (
  `id` int(11) NOT NULL,
  `opcao_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `data_voto` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Enquete_Votos`
--

INSERT INTO `Enquete_Votos` (`id`, `opcao_id`, `usuario_id`, `data_voto`) VALUES
(5, 2, 5, '2025-12-21 20:36:12'),
(7, 1, 12, '2025-12-21 22:11:36'),
(8, 3, 13, '2025-12-21 22:14:20'),
(11, 4, 13, '2025-12-21 22:23:39'),
(13, 11, 15, '2026-02-26 19:59:09'),
(14, 14, 13, '2026-03-02 14:25:06'),
(16, 12, 13, '2026-03-02 14:25:23'),
(17, 15, 15, '2026-03-30 21:13:53'),
(18, 16, 5, '2026-04-01 15:57:27');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Estados`
--

CREATE TABLE `Estados` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `sigla` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Estados`
--

INSERT INTO `Estados` (`id`, `nome`, `sigla`) VALUES
(1, 'Acre', 'AC'),
(2, 'Alagoas', 'AL'),
(3, 'Amapá', 'AP'),
(4, 'Amazonas', 'AM'),
(5, 'Bahia', 'BA'),
(6, 'Ceará', 'CE'),
(7, 'Distrito Federal', 'DF'),
(8, 'Espírito Santo', 'ES'),
(9, 'Goiás', 'GO'),
(10, 'Maranhão', 'MA'),
(11, 'Mato Grosso', 'MT'),
(12, 'Mato Grosso do Sul', 'MS'),
(13, 'Minas Gerais', 'MG'),
(14, 'Pará', 'PA'),
(15, 'Paraíba', 'PB'),
(16, 'Paraná', 'PR'),
(17, 'Pernambuco', 'PE'),
(18, 'Piauí', 'PI'),
(19, 'Rio de Janeiro', 'RJ'),
(20, 'Rio Grande do Norte', 'RN'),
(21, 'Rio Grande do Sul', 'RS'),
(22, 'Rondônia', 'RO'),
(23, 'Roraima', 'RR'),
(24, 'Santa Catarina', 'SC'),
(25, 'São Paulo', 'SP'),
(26, 'Sergipe', 'SE'),
(27, 'Tocantins', 'TO');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Grupos`
--

CREATE TABLE `Grupos` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `privacidade` enum('publico','privado') NOT NULL DEFAULT 'publico',
  `foto_capa_url` varchar(255) DEFAULT NULL,
  `id_dono` int(11) NOT NULL,
  `status` enum('ativo','excluido','suspenso') NOT NULL DEFAULT 'ativo',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Grupos`
--

INSERT INTO `Grupos` (`id`, `nome`, `descricao`, `privacidade`, `foto_capa_url`, `id_dono`, `status`, `data_criacao`) VALUES
(1, 'Social BR', 'Grupo oficial do site, aqui postamos novidades no grupo, atualizações e futuras novidades. Aqui é a central para os membros poderem tirar suas duvidas.', 'publico', 'assets/uploads/grupos/capa_grp_1768666152_69791812.png', 5, 'ativo', '2026-01-17 16:09:12'),
(2, 'Grupo teste', 'apenas para teste', 'privado', 'assets/uploads/grupos/capas/capa_upd_1768834073_9903cd43.jpg', 12, 'ativo', '2026-01-17 21:01:56'),
(3, 'Todo mundo erra', 'Grupo do pica pau', 'privado', 'assets/uploads/grupos/capa_grp_1768685840_95624755.jpeg', 13, 'ativo', '2026-01-17 21:37:20'),
(4, 'God of War', 'Grupo para o jogo good of war', 'publico', 'assets/uploads/grupos/capas/capa_grp_1768947887_c25fdf17.jpg', 13, 'excluido', '2026-01-20 22:24:47'),
(5, 'Teste', 'Teste grupo', 'publico', 'assets/uploads/grupos/capas/capa_upd_1768960270_907fb0e2.jpg', 13, 'ativo', '2026-01-21 01:50:58'),
(6, 'Sonic teste', 'Teste tesye', 'publico', 'assets/uploads/grupos/capas/capa_upd_1771513134_b4adfc28.jpg', 5, 'ativo', '2026-02-05 00:46:10'),
(7, 'Televisão', 'Grupo pr tvs', 'publico', 'assets/uploads/grupos/capas/capa_grp_1772384890_36ebf20c.jpg', 15, 'ativo', '2026-03-01 17:08:10'),
(8, 'welcome rupo', 'grupo teste', 'privado', 'midias/grupos/fotos/15_manus_2026-03-28_13-21-56_capa.webp', 15, 'ativo', '2026-03-27 15:12:19');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Grupos_Membros`
--

CREATE TABLE `Grupos_Membros` (
  `id` int(11) NOT NULL,
  `id_grupo` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nivel_permissao` enum('membro','moderador','dono') NOT NULL DEFAULT 'membro',
  `notificacoes_ativas` tinyint(1) NOT NULL DEFAULT 1,
  `data_adesao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Grupos_Membros`
--

INSERT INTO `Grupos_Membros` (`id`, `id_grupo`, `id_usuario`, `nivel_permissao`, `notificacoes_ativas`, `data_adesao`) VALUES
(1, 1, 5, 'dono', 1, '2026-01-17 16:09:12'),
(3, 1, 13, 'moderador', 1, '2026-01-17 21:00:48'),
(4, 2, 5, 'moderador', 1, '2026-01-17 21:01:56'),
(5, 3, 13, 'dono', 1, '2026-01-17 21:37:20'),
(7, 2, 12, 'dono', 1, '2026-01-19 14:49:05'),
(8, 4, 13, 'dono', 1, '2026-01-20 22:24:47'),
(9, 5, 13, 'dono', 1, '2026-01-21 01:50:58'),
(11, 5, 5, 'membro', 1, '2026-02-02 15:16:00'),
(12, 1, 15, 'membro', 1, '2026-02-05 00:38:22'),
(13, 6, 15, 'moderador', 1, '2026-02-05 00:46:10'),
(14, 7, 15, 'dono', 1, '2026-03-01 17:08:10'),
(15, 8, 15, 'dono', 1, '2026-03-27 15:12:19'),
(16, 8, 5, 'moderador', 1, '2026-03-30 21:08:03');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Grupos_Solicitacoes`
--

CREATE TABLE `Grupos_Solicitacoes` (
  `id` int(11) NOT NULL,
  `id_grupo` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `data_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pendente','aceito','recusado') NOT NULL DEFAULT 'pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `Links_Cliques`
--

CREATE TABLE `Links_Cliques` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `url_destino` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `data_clique` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `Links_Cliques`
--

INSERT INTO `Links_Cliques` (`id`, `post_id`, `usuario_id`, `url_destino`, `ip_address`, `user_agent`, `data_clique`) VALUES
(1, 51, 5, 'https://www1.folha.uol.com.br/mercado/2025/12/inteligencia-artificial-cria-negocio-imobiliario-bilionario-no-brasil-com-data-centers.shtml', '191.187.237.97', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 17:47:41'),
(2, 50, 5, 'https://jornalrazao.com/', '191.187.237.97', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 17:51:24'),
(3, 47, 5, 'https://santistas.net/noticias-do-santos/santos-fc-rafa-21-12/', '191.187.237.97', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 17:51:28'),
(4, 47, 5, 'https://santistas.net/noticias-do-santos/santos-fc-rafa-21-12/', '191.187.237.97', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 17:51:32'),
(5, 47, 5, 'https://santistas.net/noticias-do-santos/santos-fc-rafa-21-12/', '191.187.237.97', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 17:51:35'),
(6, 51, 5, 'https://www1.folha.uol.com.br/mercado/2025/12/inteligencia-artificial-cria-negocio-imobiliario-bilionario-no-brasil-com-data-centers.shtml', '191.187.233.158', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2025-12-23 23:44:18'),
(7, 51, 5, 'https://www1.folha.uol.com.br/mercado/2025/12/inteligencia-artificial-cria-negocio-imobiliario-bilionario-no-brasil-com-data-centers.shtml', '191.187.233.158', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2025-12-26 20:07:06'),
(8, 64, 5, 'https://www.cnnbrasil.com.br/nacional/brasil/irmao-de-eliza-samudio-se-pronuncia-sobre-passaporte-encontrado-em-portugal/', '191.187.237.97', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 11:52:56'),
(9, 64, 5, 'https://www.cnnbrasil.com.br/nacional/brasil/irmao-de-eliza-samudio-se-pronuncia-sobre-passaporte-encontrado-em-portugal/', '191.187.233.158', 'Mozilla/5.0 (Linux; Android 11; SM-A205G Build/RP1A.200720.012; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/144.0.7559.31 Mobile Safari/537.36', '2026-01-08 00:12:21'),
(10, 79, 5, 'https://socialbr.lol/~klscom/tarefas/', '191.187.233.158', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 19:36:36');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Logs_Acessos_Negados`
--

CREATE TABLE `Logs_Acessos_Negados` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL COMMENT 'ID do utilizador se estiver logado',
  `slug_tentado` varchar(255) NOT NULL COMMENT 'URL ou Slug que gerou o erro',
  `erro_codigo` int(5) NOT NULL COMMENT 'Código do erro (404, 403, 503)',
  `ip_endereco` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL COMMENT 'Navegador/Dispositivo do utilizador',
  `data_tentativa` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `Logs_Acessos_Negados`
--

INSERT INTO `Logs_Acessos_Negados` (`id`, `usuario_id`, `slug_tentado`, `erro_codigo`, `ip_endereco`, `user_agent`, `data_tentativa`) VALUES
(1, 5, 'assets/images/video-placeholder.png', 404, '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-30 15:55:00'),
(2, 5, 'assets/images/video-placeholder.png', 404, '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-30 15:55:05'),
(3, NULL, 'wordpress/wp-admin/setup-config.php', 404, '104.23.223.138', 'https://socialbr.lol/wordpress/wp-admin/setup-config.php', '2026-03-30 16:23:15'),
(4, NULL, 'wordpress/wp-admin/setup-config.php', 404, '104.23.221.94', 'http://socialbr.lol/wordpress/wp-admin/setup-config.php', '2026-03-30 16:23:34'),
(5, NULL, 'wp-admin/setup-config.php', 404, '104.23.223.138', 'https://socialbr.lol/wp-admin/setup-config.php', '2026-03-30 16:24:41'),
(6, NULL, 'wp-admin/setup-config.php', 404, '172.68.192.238', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-30 16:25:58'),
(7, 15, 'assets/images/video-placeholder.png', 404, '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-30 20:03:23'),
(8, 15, 'assets/images/video-placeholder.png', 404, '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-30 20:03:29'),
(9, NULL, 'robots.txt', 404, '69.171.231.10', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', '2026-03-31 09:29:32'),
(10, NULL, 'wp-admin/setup-config.php', 404, '172.71.172.205', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-31 17:05:24'),
(11, NULL, 'wp-admin/setup-config.php', 404, '172.70.246.245', 'http://socialbr.lol/wp-admin/setup-config.php', '2026-03-31 17:07:03'),
(12, NULL, 'wordpress/wp-admin/setup-config.php', 404, '162.158.95.254', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-31 17:07:23'),
(13, NULL, 'wordpress/wp-admin/setup-config.php', 404, '172.70.248.122', 'http://socialbr.lol/wordpress/wp-admin/setup-config.php', '2026-03-31 17:08:56'),
(14, NULL, 'dump.sql.tar.gz', 404, '103.153.182.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0', '2026-03-31 18:34:44'),
(15, NULL, 'db-backup.tar.gz', 404, '103.153.182.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0', '2026-03-31 18:34:45'),
(16, NULL, 'db_dump.zip', 404, '103.153.182.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36', '2026-03-31 18:34:45'),
(17, NULL, 'db_dump.tar.gz', 404, '103.153.182.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36', '2026-03-31 18:34:46'),
(18, NULL, 'db_dump.tar', 404, '103.153.182.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36', '2026-03-31 18:34:46'),
(19, NULL, 'website-backup.tar', 404, '103.153.182.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0', '2026-03-31 18:34:47'),
(20, NULL, 'web-backup.zip', 404, '103.153.182.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36', '2026-03-31 18:34:47'),
(21, NULL, 'db_backup.tar.gz', 404, '103.153.182.11', 'Mozilla/5.0 (compatible; SecurityScanner/1.0)', '2026-03-31 18:34:51'),
(22, NULL, 'db_backup.tar', 404, '103.153.182.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36', '2026-03-31 18:34:51'),
(23, NULL, 'db-backup.tar', 404, '103.153.182.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36', '2026-03-31 18:34:51'),
(24, NULL, 'site_backup.tar.gz', 404, '103.153.182.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36', '2026-03-31 18:34:51'),
(25, NULL, 'site_backup.tar', 404, '103.153.182.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36', '2026-03-31 18:34:51'),
(26, NULL, 'website-backup.tar.gz', 404, '103.153.182.11', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36', '2026-03-31 18:34:51'),
(27, NULL, 'db-dump.zip', 404, '103.153.182.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36', '2026-03-31 18:34:52'),
(28, NULL, 'db-dump.tar', 404, '103.153.182.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0', '2026-03-31 18:34:52'),
(29, NULL, 'db_backup.zip', 404, '103.153.182.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36', '2026-03-31 18:34:52'),
(30, NULL, 'db-backup.zip', 404, '103.153.182.11', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36', '2026-03-31 18:34:53'),
(31, NULL, 'backup-2025.tar.gz', 404, '103.153.182.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36', '2026-03-31 18:34:53'),
(32, NULL, 'backup-2025.zip', 404, '103.153.182.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36', '2026-03-31 18:34:53'),
(33, NULL, 'backup-2025.tar', 404, '103.153.182.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36', '2026-03-31 18:34:54'),
(34, NULL, 'robots.txt', 404, '192.178.6.6', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', '2026-04-01 03:31:28'),
(35, NULL, 'wordpress/wp-admin/setup-config.php', 404, '172.70.248.122', 'http://socialbr.lol/wordpress/wp-admin/setup-config.php', '2026-04-01 05:47:05'),
(36, NULL, 'wp-admin/setup-config.php', 404, '104.23.223.138', 'http://socialbr.lol/wp-admin/setup-config.php', '2026-04-01 05:47:49'),
(37, NULL, 'wp-admin/setup-config.php', 404, '172.68.10.44', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-01 05:48:28'),
(38, NULL, 'wordpress/wp-admin/setup-config.php', 404, '172.68.10.45', 'https://socialbr.lol/wordpress/wp-admin/setup-config.php', '2026-04-01 05:48:32'),
(39, NULL, 'robots.txt', 404, '173.252.82.20', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', '2026-04-01 06:20:38'),
(40, NULL, 'robots.txt', 404, '66.249.75.227', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', '2026-04-01 11:57:03');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Logs_Admin`
--

CREATE TABLE `Logs_Admin` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `acao` varchar(100) NOT NULL,
  `tipo_objeto` varchar(50) NOT NULL,
  `id_objeto` int(11) NOT NULL,
  `detalhes` text DEFAULT NULL,
  `data_log` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `Logs_Admin`
--

INSERT INTO `Logs_Admin` (`id`, `admin_id`, `acao`, `tipo_objeto`, `id_objeto`, `detalhes`, `data_log`) VALUES
(1, 5, 'alterar_privacidade', 'grupo', 6, 'Privacidade do grupo #6 alternada via painel admin.', '2026-02-21 14:52:24'),
(2, 5, 'alterar_privacidade', 'grupo', 6, 'Privacidade do grupo #6 alternada via painel admin.', '2026-02-21 14:52:26'),
(3, 5, 'alterar_privacidade', 'grupo', 6, 'Privacidade do grupo #6 alternada via painel admin.', '2026-02-21 14:52:35'),
(4, 5, 'alterar_privacidade', 'grupo', 6, 'Privacidade do grupo #6 alternada via painel admin.', '2026-02-21 14:52:36'),
(5, 5, 'alterar_status', 'grupo', 6, 'Status do grupo #6 alternado (Ativo/Suspenso).', '2026-02-21 14:52:42'),
(6, 5, 'alterar_status', 'grupo', 6, 'Status do grupo #6 alternado (Ativo/Suspenso).', '2026-02-21 14:52:43'),
(7, 5, 'alterar_privacidade', 'grupo', 6, 'Privacidade do grupo #6 alternada via painel admin.', '2026-02-21 15:02:33'),
(8, 5, 'alterar_status', 'grupo', 6, 'Status do grupo #6 alternado (Ativo/Suspenso).', '2026-02-21 15:03:07'),
(9, 5, 'alterar_status', 'grupo', 6, 'Status do grupo #6 alternado (Ativo/Suspenso).', '2026-02-21 15:03:30'),
(10, 5, 'alterar_status', 'grupo', 6, 'Status do grupo #6 alternado (Ativo/Suspenso).', '2026-02-21 15:04:02'),
(11, 5, 'alterar_status', 'grupo', 6, 'Status do grupo #6 alternado (Ativo/Suspenso).', '2026-02-21 15:04:15'),
(12, 5, 'transferencia_posse_admin', 'grupo', 6, 'Propriedade do grupo #6 transferida para o UID 5.', '2026-02-21 15:05:53'),
(13, 5, 'alterar_status', 'grupo', 6, 'Status do grupo #6 alternado (Ativo/Suspenso).', '2026-02-21 15:06:06'),
(14, 5, 'alterar_status', 'grupo', 6, 'Status do grupo #6 alternado (Ativo/Suspenso).', '2026-02-21 15:06:12'),
(15, 5, 'alterar_status', '0', 6, 'Status do grupo #6 (Sonic teste) alterado para SUSPENSO.', '2026-02-21 15:52:52'),
(16, 5, 'alterar_status', 'grupo', 6, 'Status do grupo #6 (Sonic teste) alterado para ATIVO.', '2026-02-21 15:55:38'),
(17, 5, 'atualizar_configuracoes', 'sistema', 0, 'O administrador atualizou as configurações gerais do painel administrativo.', '2026-02-21 16:00:55'),
(18, 5, 'atualizar_configuracoes', 'sistema', 0, 'Configurações alteradas: MODO_MANUTENCAO: DESATIVADO | PERMITE_CADASTRO: DESATIVADO | MODO_DEV: ATIVADO | MODO_CENSURA: ATIVADO | SITE_NOME: \'Social BR\' | SITE_DESCRICAO: \'Sua rede social hiperlocal focada em conectar vizinhos.\' | EMAIL_CONTATO: \'contato@seusite.com.br\' | VERSAO_ASSETS: \'1.0.0\'', '2026-02-21 16:05:19'),
(19, 5, 'atualizar_status_denuncia', 'denuncia', 28, 'Denúncia #28 de POST (Motivo: Conteúdo violento, que promove o ódio ou é perturbador) marcada como IGNORADA.', '2026-02-21 16:18:17'),
(20, 5, 'atualizar_status_denuncia', 'denuncia', 28, 'Denúncia #28 de POST (Motivo: Conteúdo violento, que promove o ódio ou é perturbador) marcada como IGNORADA.', '2026-02-21 16:18:18'),
(21, 5, 'atualizar_status_denuncia', 'denuncia', 27, 'Denúncia #27 de POST (Motivo: Golpe, fraude ou informação falsa) marcada como REVISADA (CONTEÚDO MANTIDO).', '2026-02-21 16:19:26'),
(22, 5, 'atualizar_status_denuncia', 'denuncia', 27, 'Denúncia #27 de POST (Motivo: Golpe, fraude ou informação falsa) marcada como REVISADA (CONTEÚDO MANTIDO).', '2026-02-21 16:19:26'),
(23, 5, 'atualizar_status_denuncia', 'denuncia', 26, 'Denúncia #26 de POST (Motivo: Bullying, assédio ou abuso) marcada como REVISADA (CONTEÚDO MANTIDO).', '2026-02-21 16:24:22'),
(24, 5, 'atualizar_status_denuncia', 'denuncia', 21, 'Denúncia #21 de USUARIO (Motivo: Conteúdo impróprio no perfil (foto, nome)) marcada como IGNORADA.', '2026-02-21 16:25:59'),
(25, 5, 'editar_usuario', 'usuario', 14, 'Alterações no perfil #14 (testeste): Nome: \'testeste\' -> \'teste\'', '2026-02-21 16:28:25'),
(26, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-02-21 16:43:03'),
(27, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-02-21 16:56:12'),
(28, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-02-21 17:13:24'),
(29, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-02-25 18:00:37'),
(30, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-02-25 18:00:48'),
(31, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-02-26 14:24:26'),
(32, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-02-26 15:38:20'),
(33, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-02 17:07:24'),
(34, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-11 14:15:57'),
(35, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-18 20:05:22'),
(36, 5, 'atualizar_configuracoes', 'sistema', 0, 'Configurações alteradas: MODO_MANUTENCAO: DESATIVADO | PERMITE_CADASTRO: DESATIVADO | MODO_DEV: DESATIVADO | MODO_CENSURA: ATIVADO | SITE_NOME: \'Social BR\' | SITE_DESCRICAO: \'Sua rede social hiperlocal focada em conectar vizinhos.\' | EMAIL_CONTATO: \'contato@seusite.com.br\' | VERSAO_ASSETS: \'1.0.0\'', '2026-03-18 21:13:08'),
(37, 5, 'atualizar_configuracoes', 'sistema', 0, 'Configurações alteradas: MODO_MANUTENCAO: DESATIVADO | PERMITE_CADASTRO: DESATIVADO | MODO_DEV: ATIVADO | MODO_CENSURA: ATIVADO | SITE_NOME: \'Social BR\' | SITE_DESCRICAO: \'Sua rede social hiperlocal focada em conectar vizinhos.\' | EMAIL_CONTATO: \'contato@seusite.com.br\' | VERSAO_ASSETS: \'1.0.0\'', '2026-03-18 21:37:50'),
(38, 5, 'atualizar_configuracoes', 'sistema', 0, 'Configurações alteradas: MODO_MANUTENCAO: DESATIVADO | PERMITE_CADASTRO: DESATIVADO | MODO_DEV: DESATIVADO | MODO_CENSURA: ATIVADO | SITE_NOME: \'Social BR\' | SITE_DESCRICAO: \'Sua rede social hiperlocal focada em conectar vizinhos.\' | EMAIL_CONTATO: \'contato@seusite.com.br\' | VERSAO_ASSETS: \'1.0.0\'', '2026-03-19 16:36:15'),
(39, 5, 'atualizar_configuracoes', 'sistema', 0, 'Configurações alteradas: MODO_MANUTENCAO: DESATIVADO | PERMITE_CADASTRO: ATIVADO | MODO_DEV: DESATIVADO | MODO_CENSURA: ATIVADO | SITE_NOME: \'Social BR\' | SITE_DESCRICAO: \'Sua rede social brasileira OFICIAL\' | EMAIL_CONTATO: \'suporte@socialbr.lol\' | VERSAO_ASSETS: \'1.0.0\'', '2026-03-22 23:03:33'),
(40, 5, 'editar_usuario', 'usuario', 16, 'Alterações no perfil #16 (dioantonio): Nome: \'dioga\' -> \'diogo\' | Username: \'dioantonio\' -> \'diogoantonio\'', '2026-03-22 23:12:15'),
(41, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-23 15:23:03'),
(42, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-23 16:32:45'),
(43, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-23 16:51:00'),
(44, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-23 17:24:58'),
(45, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-23 18:46:50'),
(46, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-23 20:23:33'),
(47, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-23 20:37:05'),
(48, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-26 14:04:46'),
(49, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-26 14:20:16'),
(50, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-26 17:29:00'),
(51, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-26 17:30:11'),
(52, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-26 21:36:30'),
(53, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-27 15:00:18'),
(54, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-28 14:52:35'),
(55, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-28 14:52:50'),
(56, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-29 15:11:28'),
(57, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-29 15:11:52'),
(58, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-29 16:15:00'),
(59, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-29 16:18:05'),
(60, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-29 18:55:05'),
(61, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-29 19:29:44'),
(62, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-30 01:33:42'),
(63, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-30 01:34:58'),
(64, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-30 01:35:17'),
(65, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-30 01:35:25'),
(66, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-30 01:35:48'),
(67, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-30 01:48:00'),
(68, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-30 14:57:35'),
(69, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-30 14:58:23'),
(70, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-30 15:22:21'),
(71, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-30 15:35:58'),
(72, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-30 20:05:10'),
(73, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-30 20:39:37'),
(74, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-30 20:44:37'),
(75, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-03-30 20:53:10'),
(76, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-04-01 16:53:03'),
(77, 5, 'atualizar_anotacoes', 'sistema', 1, 'O administrador atualizou o conteúdo do bloco de notas interno (Anotações Rápidas).', '2026-04-01 17:00:53');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Logs_Emails`
--

CREATE TABLE `Logs_Emails` (
  `id` int(11) NOT NULL,
  `destinatario` varchar(255) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `status` enum('sucesso','falha') NOT NULL,
  `data_envio` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `Logs_Emails`
--

INSERT INTO `Logs_Emails` (`id`, `destinatario`, `tipo`, `status`, `data_envio`) VALUES
(1, 'didiego2010.dr@gmail.com', 'verificacao_conta', 'sucesso', '2026-03-26 15:25:27'),
(2, 'didiego2010.dr@gmail.com', 'verificacao_conta', 'sucesso', '2026-03-26 15:27:40'),
(3, 'didiego2010.dr@gmail.com', 'verificacao_conta', 'sucesso', '2026-03-26 15:42:31'),
(4, 'didiego2010.dr@gmail.com', 'recuperacao_senha', 'sucesso', '2026-03-26 15:51:10'),
(5, 'didiego2010.dr@gmail.com', 'recuperacao_senha', 'sucesso', '2026-03-26 15:52:22'),
(6, 'didiego2010.dr@gmail.com', 'recuperacao_senha', 'sucesso', '2026-03-26 16:56:35'),
(7, 'suporte@socialbr.lol', 'alerta_suporte', 'sucesso', '2026-03-26 17:23:38'),
(8, 'suporte@socialbr.lol', 'alerta_suporte', 'sucesso', '2026-03-30 01:29:45');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Logs_Erros_Sistema`
--

CREATE TABLE `Logs_Erros_Sistema` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL COMMENT 'ID do utilizador se estiver logado',
  `tipo` varchar(50) NOT NULL COMMENT 'Ex: Fatal Error, Warning, Exception',
  `mensagem` text NOT NULL,
  `arquivo` varchar(255) NOT NULL,
  `linha` int(11) NOT NULL,
  `url_acessada` varchar(255) DEFAULT NULL,
  `ip_endereco` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `stack_trace` longtext DEFAULT NULL,
  `hash_erro` varchar(32) NOT NULL COMMENT 'MD5(tipo + mensagem + arquivo + linha)',
  `ocorrencias` int(11) NOT NULL DEFAULT 1 COMMENT 'Contador para deduplicação',
  `status` enum('pendente','em_analise','corrigido','ignorado') NOT NULL DEFAULT 'pendente',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `Logs_Erros_Sistema`
--

INSERT INTO `Logs_Erros_Sistema` (`id`, `usuario_id`, `tipo`, `mensagem`, `arquivo`, `linha`, `url_acessada`, `ip_endereco`, `user_agent`, `stack_trace`, `hash_erro`, `ocorrencias`, `status`, `data_criacao`, `data_atualizacao`) VALUES
(1, 5, 'JS Runtime Error', '[URL: https://socialbr.lol/admin/usuarios] - Uncaught SyntaxError: Identifier \'BASE_PATH\' has already been declared', 'https://socialbr.lol/admin/usuarios', 673, '/api/admin/registrar_erro_js.php', '181.221.152.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'SyntaxError: Identifier \'BASE_PATH\' has already been declared', 'bc78f18da5874ff4f6ca05b7b8349c46', 1, 'pendente', '2026-03-22 23:11:18', '2026-03-22 23:11:18'),
(2, 5, 'JS Runtime Error', '[URL: https://socialbr.lol/admin/admin_editar_usuario.php?id=16] - Uncaught SyntaxError: Identifier \'BASE_PATH\' has already been declared', 'https://socialbr.lol/admin/admin_editar_usuario.php?id=16', 246, '/api/admin/registrar_erro_js.php', '181.221.152.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'SyntaxError: Identifier \'BASE_PATH\' has already been declared', '4621ebf4d1466b595279bf1c1e4e8ec4', 2, 'pendente', '2026-03-22 23:12:02', '2026-03-23 14:51:46'),
(3, 5, 'JS Runtime Error', '[URL: https://socialbr.lol/admin/admin_usuarios.php?success=1] - Uncaught SyntaxError: Identifier \'BASE_PATH\' has already been declared', 'https://socialbr.lol/admin/admin_usuarios.php?success=1', 675, '/api/admin/registrar_erro_js.php', '181.221.152.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'SyntaxError: Identifier \'BASE_PATH\' has already been declared', '5fd315452ace63abdf34b1e2b73a8a92', 1, 'pendente', '2026-03-22 23:12:15', '2026-03-22 23:12:15'),
(4, 5, 'JS Runtime Error', '[URL: https://socialbr.lol/admin/usuarios] - Uncaught SyntaxError: Identifier \'BASE_PATH\' has already been declared', 'https://socialbr.lol/admin/usuarios', 675, '/api/admin/registrar_erro_js.php', '181.221.152.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'SyntaxError: Identifier \'BASE_PATH\' has already been declared', '06db7e6714601be8520062745cd92abd', 2, 'pendente', '2026-03-23 14:51:43', '2026-03-23 14:52:05'),
(7, 5, 'PHP Error (8192)', 'htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated', '/home/klscom/public_html/admin/menus/rastreador_cliques.php', 85, '/admin/menus-rotas', '181.221.152.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '01a92db8a29044f35db3593d5b33d4f5', 8, 'pendente', '2026-03-23 15:02:50', '2026-03-23 21:25:49'),
(8, 5, 'PHP Error (8192)', 'strtolower(): Passing null to parameter #1 ($string) of type string is deprecated', '/home/klscom/public_html/admin/menus/rastreador_cliques.php', 86, '/admin/menus-rotas', '181.221.152.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, 'ad0765c59c13ece173f0fe9a0334b18a', 8, 'pendente', '2026-03-23 15:02:50', '2026-03-23 21:25:49'),
(9, 5, 'PHP Error (8192)', 'substr(): Passing null to parameter #1 ($string) of type string is deprecated', '/home/klscom/public_html/admin/menus/rastreador_cliques.php', 87, '/admin/menus-rotas', '181.221.152.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '8af67ddafe49adf814f062f9fda27713', 8, 'pendente', '2026-03-23 15:02:50', '2026-03-23 21:25:49'),
(25, 5, 'JS Runtime Error', '[URL: https://socialbr.lol/admin/admin_editar_usuario.php?id=15] - Uncaught SyntaxError: Identifier \'BASE_PATH\' has already been declared', 'https://socialbr.lol/admin/admin_editar_usuario.php?id=15', 246, '/api/admin/registrar_erro_js.php', '181.221.152.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'SyntaxError: Identifier \'BASE_PATH\' has already been declared', '619300dcc4ac35f9ba2081ccf4805bd0', 1, 'pendente', '2026-03-23 18:46:01', '2026-03-23 18:46:01'),
(26, 13, 'JS Runtime Error', '[URL: https://socialbr.lol/chat] - Uncaught ReferenceError: chatMotor is not defined', 'https://socialbr.lol/chat', 837, '/api/admin/registrar_erro_js.php', '181.221.152.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'ReferenceError: chatMotor is not defined\n    at HTMLDivElement.onclick (https://socialbr.lol/chat:837:44)', 'ddeaa27392b1bba7bef0e9ade7838c97', 1, 'pendente', '2026-03-23 20:35:08', '2026-03-23 20:35:08'),
(30, 15, 'Warning', 'require_once(/home/klscom/views/chat/componentes/../../../../config/database.php): Failed to open stream: No such file or directory', '/home/klscom/views/chat/componentes/modal_iniciador_chat.php', 14, '/chat?ajax_iniciador=1', '181.221.152.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '6591b29f730350aafc76215e40f1a7c9', 2, 'pendente', '2026-03-23 21:20:48', '2026-03-23 21:21:59'),
(31, 15, 'Exception: Error', 'Failed opening required \'/home/klscom/views/chat/componentes/../../../../config/database.php\' (include_path=\'.:/opt/alt/php81/usr/share/pear:/opt/alt/php81/usr/share/php:/usr/share/pear:/usr/share/php\')', '/home/klscom/views/chat/componentes/modal_iniciador_chat.php', 14, '/chat?ajax_iniciador=1', '181.221.152.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '#0 /home/klscom/public_html/index.php(103): include()\n#1 {main}', '473b1dc53e835b17f3c960571c1552fe', 2, 'pendente', '2026-03-23 21:20:48', '2026-03-23 21:21:59'),
(37, 15, 'JS Promise Rejection', '[URL: https://socialbr.lol/feed] - Cannot set properties of null (setting \'value\')', 'Async/Fetch Context', 0, '/api/admin/registrar_erro_js.php', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'TypeError: Cannot set properties of null (setting \'value\')\n    at abrirModalComentarios (https://socialbr.lol/assets/js/comentarios.js?v=1.0.0:27:32)\n    at HTMLBodyElement.<anonymous> (https://socialbr.lol/assets/js/comentarios.js?v=1.0.0:204:28)', 'e1aa580216df47cf036c016fe760f5ce', 7, 'pendente', '2026-03-24 15:47:41', '2026-03-24 15:47:45'),
(44, 5, 'JS Runtime Error', '[URL: https://socialbr.lol/admin/usuarios] - Uncaught SyntaxError: Identifier \'BASE_PATH\' has already been declared', 'https://socialbr.lol/admin/usuarios', 646, '/api/admin/registrar_erro_js.php', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'SyntaxError: Identifier \'BASE_PATH\' has already been declared', '71b6455d381b7dd3ef4828d292af675b', 1, 'pendente', '2026-03-26 17:25:34', '2026-03-26 17:25:34'),
(45, 5, 'Warning', 'Undefined array key \"titulo_produto\"', '/home/klscom/views/marketplace/detalhes.php', 138, '/marketplace/item/10', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '16448eed813bd0f16db1ee7af9e20cc9', 2, 'pendente', '2026-03-28 16:12:31', '2026-03-28 21:29:08'),
(46, 5, 'PHP Error (8192)', 'htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated', '/home/klscom/views/marketplace/detalhes.php', 138, '/marketplace/item/10', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, 'c6c57488300e411399c2c42e9437b61d', 2, 'pendente', '2026-03-28 16:12:31', '2026-03-28 21:29:08'),
(47, 5, 'Warning', 'Undefined array key \"status_venda\"', '/home/klscom/views/marketplace/detalhes.php', 141, '/marketplace/item/10', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '3d4a07223ea11f7f6263899cdc0f5c50', 2, 'pendente', '2026-03-28 16:12:31', '2026-03-28 21:29:08'),
(48, 5, 'Warning', 'Undefined array key \"titulo_produto\"', '/home/klscom/views/marketplace/detalhes.php', 163, '/marketplace/item/10', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '4f3249d97d3ef35bc828c98ad5cb7945', 2, 'pendente', '2026-03-28 16:12:31', '2026-03-28 21:29:08'),
(49, 5, 'PHP Error (8192)', 'htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated', '/home/klscom/views/marketplace/detalhes.php', 163, '/marketplace/item/10', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, 'b0df32e92652146296056798f0463980', 2, 'pendente', '2026-03-28 16:12:31', '2026-03-28 21:29:08'),
(50, 5, 'Warning', 'Undefined array key \"preco_formatado\"', '/home/klscom/views/marketplace/detalhes.php', 165, '/marketplace/item/10', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '51e12625512544085ad57a09fba12369', 2, 'pendente', '2026-03-28 16:12:31', '2026-03-28 21:29:08'),
(51, 5, 'Warning', 'Undefined array key \"condicao\"', '/home/klscom/views/marketplace/detalhes.php', 172, '/marketplace/item/10', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, 'aa5e73536b4a2803c5e7cad1dbffc2b4', 2, 'pendente', '2026-03-28 16:12:31', '2026-03-28 21:29:08'),
(52, 5, 'PHP Error (8192)', 'str_replace(): Passing null to parameter #3 ($subject) of type array|string is deprecated', '/home/klscom/views/marketplace/detalhes.php', 101, '/marketplace/item/10', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '238e0944839ba8c82a7ec429032cf3ee', 2, 'pendente', '2026-03-28 16:12:31', '2026-03-28 21:29:08'),
(53, 5, 'Warning', 'Undefined array key \"cidade\"', '/home/klscom/views/marketplace/detalhes.php', 176, '/marketplace/item/10', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '24628863870ea6515679c91a5722b56b', 2, 'pendente', '2026-03-28 16:12:31', '2026-03-28 21:29:08'),
(54, 5, 'Warning', 'Undefined array key \"estado\"', '/home/klscom/views/marketplace/detalhes.php', 176, '/marketplace/item/10', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, 'd7f9b3be1862426a0a3a8d8ba1f9d62b', 2, 'pendente', '2026-03-28 16:12:31', '2026-03-28 21:29:08'),
(55, 5, 'Warning', 'Undefined array key \"descricao_completa\"', '/home/klscom/views/marketplace/detalhes.php', 183, '/marketplace/item/10', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '791c77f87007f39745561d5a0be3db75', 2, 'pendente', '2026-03-28 16:12:31', '2026-03-28 21:29:08'),
(56, 5, 'PHP Error (8192)', 'htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated', '/home/klscom/views/marketplace/detalhes.php', 183, '/marketplace/item/10', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, 'b3b527753fd9ed4739c47d5539a4b92d', 2, 'pendente', '2026-03-28 16:12:31', '2026-03-28 21:29:08'),
(57, 5, 'Warning', 'Undefined array key \"vendedor_avatar\"', '/home/klscom/views/marketplace/detalhes.php', 189, '/marketplace/item/10', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '248943496a5771541181578e4429a0a2', 2, 'pendente', '2026-03-28 16:12:31', '2026-03-28 21:29:08'),
(58, 5, 'Warning', 'Undefined array key \"vendedor_nome_completo\"', '/home/klscom/views/marketplace/detalhes.php', 193, '/marketplace/item/10', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '545d6676cc2f4f0d98569f2d320d9fd8', 2, 'pendente', '2026-03-28 16:12:31', '2026-03-28 21:29:08'),
(59, 5, 'PHP Error (8192)', 'htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated', '/home/klscom/views/marketplace/detalhes.php', 193, '/marketplace/item/10', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, 'ce3d5dc740d78bea2825605438b81eee', 2, 'pendente', '2026-03-28 16:12:31', '2026-03-28 21:29:08'),
(60, 5, 'Warning', 'Undefined array key \"is_owner\"', '/home/klscom/views/marketplace/detalhes.php', 204, '/marketplace/item/10', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '8840054469b2ca536f35ee9c8435b0bc', 2, 'pendente', '2026-03-28 16:12:31', '2026-03-28 21:29:08'),
(61, 5, 'Warning', 'Undefined array key \"status_venda\"', '/home/klscom/views/marketplace/detalhes.php', 226, '/marketplace/item/10', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '5d3196e262999698a731dcc4ad035de4', 2, 'pendente', '2026-03-28 16:12:31', '2026-03-28 21:29:08'),
(62, 5, 'Warning', 'Undefined array key \"titulo_produto\"', '/home/klscom/views/marketplace/detalhes.php', 227, '/marketplace/item/10', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '8ababcd8344c9122653f8bb789362471', 2, 'pendente', '2026-03-28 16:12:31', '2026-03-28 21:29:08'),
(63, 5, 'PHP Error (8192)', 'urlencode(): Passing null to parameter #1 ($string) of type string is deprecated', '/home/klscom/views/marketplace/detalhes.php', 227, '/marketplace/item/10', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2721cb5554648cc7b91a3c46dcf85f20', 2, 'pendente', '2026-03-28 16:12:31', '2026-03-28 21:29:08'),
(64, 5, 'JS Runtime Error', '[URL: https://socialbr.lol/marketplace/editar/10] - Uncaught TypeError: Cannot read properties of null (reading \'value\')', 'https://socialbr.lol/assets/js/mkt_editar.js?v=1774733324', 152, '/api/admin/registrar_erro_js.php', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'TypeError: Cannot read properties of null (reading \'value\')\n    at updateLivePreview (https://socialbr.lol/assets/js/mkt_editar.js?v=1774733324:152:42)\n    at init (https://socialbr.lol/assets/js/mkt_editar.js?v=1774733324:53:9)\n    at HTMLDocument.<anonymous> (https://socialbr.lol/assets/js/mkt_editar.js?v=1774733324:263:5)', '3f0ce136a5e1e3f0cc2e7e2e70eb0f69', 1, 'pendente', '2026-03-28 21:28:49', '2026-03-28 21:28:49'),
(84, 5, 'JS Runtime Error', '[URL: https://socialbr.lol/marketplace/editar/10] - Uncaught TypeError: Cannot read properties of null (reading \'value\')', 'https://socialbr.lol/assets/js/mkt_editar.js?v=1774796925', 152, '/api/admin/registrar_erro_js.php', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'TypeError: Cannot read properties of null (reading \'value\')\n    at updateLivePreview (https://socialbr.lol/assets/js/mkt_editar.js?v=1774796925:152:42)\n    at init (https://socialbr.lol/assets/js/mkt_editar.js?v=1774796925:53:9)\n    at HTMLDocument.<anonymous> (https://socialbr.lol/assets/js/mkt_editar.js?v=1774796925:263:5)', '7bfa157086d4825734c87c9e963c5026', 1, 'pendente', '2026-03-29 15:08:50', '2026-03-29 15:08:50'),
(85, 15, 'JS Runtime Error', '[URL: https://socialbr.lol/historico_notificacoes] - Uncaught SyntaxError: Identifier \'BASE_PATH\' has already been declared', 'https://socialbr.lol/historico_notificacoes', 1386, '/api/admin/registrar_erro_js.php', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'SyntaxError: Identifier \'BASE_PATH\' has already been declared', '50d37c67f51b635b432df03562676798', 2, 'pendente', '2026-03-29 22:50:40', '2026-03-29 22:53:11'),
(87, 5, 'JS Runtime Error', '[URL: https://socialbr.lol/admin/marketplace] - Uncaught SyntaxError: Identifier \'BASE_PATH\' has already been declared', 'https://socialbr.lol/admin/marketplace', 641, '/api/admin/registrar_erro_js.php', '181.221.152.213', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', 'SyntaxError: Identifier \'BASE_PATH\' has already been declared', '0f5d0bf422f80144a9d10e6807e5bf15', 1, 'pendente', '2026-03-30 01:36:57', '2026-03-30 01:36:57'),
(88, 5, 'Exception: Error', 'Call to undefined method mysqli_result::fetchAll()', '/home/klscom/src/BuscaLogic.php', 194, '/admin/busca', '181.221.152.213', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '#0 /home/klscom/public_html/admin/admin_busca.php(19): BuscaLogic->getTopTermos()\n#1 /home/klscom/public_html/admin/roteador.php(126): require_once(\'/home/klscom/pu...\')\n#2 /home/klscom/public_html/index.php(116): require_once(\'/home/klscom/pu...\')\n#3 {main}', '549ace4725ef3708e7aa608c7317d038', 1, 'pendente', '2026-03-30 01:38:22', '2026-03-30 01:38:22'),
(89, 5, 'PHP Error (8192)', 'Function strftime() is deprecated', '/home/klscom/public_html/admin/admin_estatisticas.php', 33, '/admin/estatisticas', '181.221.152.213', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '21be04fad6d575326a8662805f9c79d7', 84, 'pendente', '2026-03-30 01:42:59', '2026-03-30 01:42:59'),
(173, 5, 'Exception: Error', 'LoggedMySQLi object is already closed', '/home/klscom/config/database.php', 47, '/admin/estatisticas', '181.221.152.213', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '#0 /home/klscom/config/database.php(47): mysqli->query()\n#1 /home/klscom/src/SuporteLogic.php(194): LoggedMySQLi->query()\n#2 /home/klscom/public_html/admin/templates/admin_menu_links.php(19): SuporteLogic::getStatsAdmin()\n#3 /home/klscom/public_html/admin/templates/admin_mobile_nav.php(4): include(\'/home/klscom/pu...\')\n#4 /home/klscom/public_html/admin/admin_estatisticas.php(225): include(\'/home/klscom/pu...\')\n#5 /home/klscom/public_html/admin/roteador.php(126): require_once(\'/home/klscom/pu...\')\n#6 /home/klscom/public_html/index.php(116): require_once(\'/home/klscom/pu...\')\n#7 {main}', 'bc6a836f701216744951dfcccb4a1fee', 1, 'pendente', '2026-03-30 01:42:59', '2026-03-30 01:42:59'),
(174, 5, 'JS Runtime Error', '[URL: https://socialbr.lol/historico_notificacoes] - Uncaught SyntaxError: Identifier \'BASE_PATH\' has already been declared', 'https://socialbr.lol/historico_notificacoes', 4452, '/api/admin/registrar_erro_js.php', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'SyntaxError: Identifier \'BASE_PATH\' has already been declared', '953c8b4fc146cf6136ba71f69542d0c6', 2, 'pendente', '2026-03-30 15:16:32', '2026-03-30 15:16:51'),
(176, 15, 'JS Runtime Error', '[URL: https://socialbr.lol/historico_notificacoes] - Uncaught SyntaxError: Identifier \'BASE_PATH\' has already been declared', 'https://socialbr.lol/historico_notificacoes', 1389, '/api/admin/registrar_erro_js.php', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'SyntaxError: Identifier \'BASE_PATH\' has already been declared', 'dfe95176960ea5731766ff64bf84ecf0', 1, 'pendente', '2026-03-30 15:19:52', '2026-03-30 15:19:52'),
(177, 15, 'JS Runtime Error', '[URL: https://socialbr.lol/historico_notificacoes] - Uncaught SyntaxError: Identifier \'BASE_PATH\' has already been declared', 'https://socialbr.lol/historico_notificacoes', 1353, '/api/admin/registrar_erro_js.php', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'SyntaxError: Identifier \'BASE_PATH\' has already been declared', '4591996d25e2233ef6956cfde3e1a14d', 2, 'pendente', '2026-03-30 15:20:34', '2026-03-30 15:39:24'),
(179, 15, 'JS Runtime Error', '[URL: https://socialbr.lol/historico_notificacoes#] - Uncaught SyntaxError: Identifier \'BASE_PATH\' has already been declared', 'https://socialbr.lol/historico_notificacoes', 1353, '/api/admin/registrar_erro_js.php', '191.187.232.168', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', 'SyntaxError: Identifier \'BASE_PATH\' has already been declared', 'aa25329819251f703d523ad5a30198c8', 1, 'pendente', '2026-03-30 15:45:10', '2026-03-30 15:45:10'),
(180, 15, 'JS Promise Rejection', '[URL: https://socialbr.lol/historico_notificacoes] - Failed to fetch', 'Async/Fetch Context', 0, '/api/admin/registrar_erro_js.php', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'TypeError: Failed to fetch\n    at fetchChatUnreadCount (https://socialbr.lol/assets/js/notificacoes.js?v=1.0.0:193:9)\n    at https://socialbr.lol/assets/js/notificacoes.js?v=1.0.0:27:9', 'e522c0d1c2461c5321ed55184157f042', 1, 'pendente', '2026-03-30 16:04:53', '2026-03-30 16:04:53'),
(181, 5, 'JS Promise Rejection', '[URL: https://socialbr.lol/grupos/ver/8] - Failed to execute \'json\' on \'Response\': Unexpected end of JSON input', 'Async/Fetch Context', 0, '/api/admin/registrar_erro_js.php', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'SyntaxError: Unexpected end of JSON input\n    at https://socialbr.lol/grupos/ver/8:3765:26', 'bf26a755ef5c73dec12d854469d54ca2', 2, 'pendente', '2026-03-30 21:07:47', '2026-03-30 21:07:51'),
(183, 15, 'JS Promise Rejection', '[URL: https://socialbr.lol/feed] - Failed to fetch', 'Async/Fetch Context', 0, '/api/admin/registrar_erro_js.php', '191.187.232.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'TypeError: Failed to fetch\n    at fetchChatUnreadCount (https://socialbr.lol/assets/js/notificacoes.js?v=1.0.0:223:9)\n    at https://socialbr.lol/assets/js/notificacoes.js?v=1.0.0:25:9', '2c240e14033ad755ba994e7503c979db', 1, 'pendente', '2026-03-30 23:02:37', '2026-03-30 23:02:37');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Logs_Login`
--

CREATE TABLE `Logs_Login` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `data_login` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_usuario` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Logs_Login`
--

INSERT INTO `Logs_Login` (`id`, `id_usuario`, `data_login`, `ip_usuario`) VALUES
(1, 13, '2025-11-02 17:10:46', '191.187.237.97'),
(2, 5, '2025-11-02 19:46:06', '189.35.9.14'),
(3, 5, '2025-11-02 20:12:25', '189.35.9.14'),
(4, 5, '2025-11-02 20:12:32', '189.35.9.14'),
(5, 5, '2025-11-02 20:13:12', '189.35.9.14'),
(6, 5, '2025-11-02 20:14:13', '189.35.9.14'),
(7, 5, '2025-11-02 20:28:23', '189.35.9.14'),
(8, 5, '2025-11-02 20:55:19', '191.187.237.97'),
(9, 5, '2025-11-03 00:05:01', '189.35.9.14'),
(10, 5, '2025-11-03 00:26:30', '189.35.9.14'),
(11, 5, '2025-11-03 00:46:15', '189.35.9.14'),
(12, 5, '2025-11-03 01:26:15', '189.35.9.14'),
(13, 5, '2025-11-04 01:21:11', '189.35.9.14'),
(14, 5, '2025-11-04 01:31:45', '189.35.9.14'),
(15, 5, '2025-11-04 01:34:37', '189.35.9.14'),
(16, 5, '2025-11-04 01:51:37', '189.35.9.14'),
(17, 12, '2025-11-06 16:16:44', '191.187.237.97'),
(18, 5, '2025-11-06 18:29:48', '191.187.237.97'),
(19, 5, '2025-11-06 20:16:24', '191.187.237.97'),
(20, 5, '2025-11-07 01:19:17', '189.35.9.14'),
(21, 14, '2025-11-07 01:19:56', '189.35.9.14'),
(22, 5, '2025-11-07 01:54:23', '189.35.9.14'),
(23, 5, '2025-11-07 14:15:18', '191.187.237.97'),
(24, 12, '2025-11-07 14:15:33', '191.187.237.97'),
(25, 12, '2025-11-07 14:35:00', '191.187.237.97'),
(26, 14, '2025-11-07 14:35:19', '191.187.237.97'),
(27, 5, '2025-11-07 14:35:46', '191.187.237.97'),
(28, 14, '2025-11-07 14:36:20', '191.187.237.97'),
(29, 5, '2025-11-07 14:36:55', '191.187.237.97'),
(30, 14, '2025-11-07 14:37:06', '191.187.237.97'),
(31, 5, '2025-11-07 15:05:45', '191.187.237.97'),
(32, 14, '2025-11-07 17:44:34', '191.187.237.97'),
(33, 14, '2025-11-07 18:41:11', '191.187.237.97'),
(34, 5, '2025-11-07 18:41:34', '191.187.237.97'),
(35, 5, '2025-11-07 20:51:00', '191.187.237.97'),
(36, 14, '2025-11-07 21:02:15', '191.187.237.97'),
(37, 5, '2025-11-07 22:02:09', '189.35.9.14'),
(38, 14, '2025-11-07 22:02:56', '189.35.9.14'),
(39, 5, '2025-11-07 22:03:44', '189.35.9.14'),
(40, 5, '2025-11-07 23:21:33', '189.35.9.14'),
(41, 14, '2025-11-08 00:12:52', '189.35.9.14'),
(42, 5, '2025-11-08 00:14:05', '189.35.9.14'),
(43, 14, '2025-11-08 00:22:46', '189.35.9.14'),
(44, 14, '2025-11-08 00:23:41', '189.35.9.14'),
(45, 5, '2025-11-08 00:23:58', '189.35.9.14'),
(46, 14, '2025-11-08 00:24:28', '189.35.9.14'),
(47, 5, '2025-11-08 00:25:03', '189.35.9.14'),
(48, 14, '2025-11-08 00:41:17', '189.35.9.14'),
(49, 5, '2025-11-08 00:47:57', '189.35.9.14'),
(50, 14, '2025-11-08 01:05:11', '189.35.9.14'),
(51, 5, '2025-11-08 01:05:48', '189.35.9.14'),
(52, 14, '2025-11-08 01:06:41', '189.35.9.14'),
(53, 5, '2025-11-08 14:49:41', '191.187.237.97'),
(54, 14, '2025-11-08 16:24:12', '191.187.237.97'),
(55, 5, '2025-11-08 22:20:03', '189.35.9.14'),
(56, 5, '2025-11-09 00:53:21', '189.35.9.14'),
(57, 5, '2025-11-09 01:05:37', '189.35.9.14'),
(58, 5, '2025-11-09 01:40:56', '189.35.9.14'),
(59, 5, '2025-11-09 13:38:30', '189.35.9.14'),
(60, 5, '2025-11-09 14:30:04', '189.35.9.14'),
(61, 5, '2025-11-09 15:45:48', '191.187.237.97'),
(62, 5, '2025-11-09 23:16:55', '191.187.237.97'),
(63, 5, '2025-11-09 23:51:52', '189.35.9.14'),
(64, 5, '2025-11-10 00:04:51', '189.35.9.14'),
(65, 5, '2025-11-10 01:21:00', '189.35.9.14'),
(66, 12, '2025-11-10 14:24:35', '191.187.237.97'),
(67, 5, '2025-11-10 14:24:52', '191.187.237.97'),
(68, 12, '2025-11-10 14:30:35', '191.187.237.97'),
(69, 12, '2025-11-10 19:32:07', '189.35.9.14'),
(70, 5, '2025-11-10 21:05:34', '191.187.237.97'),
(71, 12, '2025-11-10 21:08:54', '191.187.237.97'),
(72, 5, '2025-11-10 22:19:16', '189.35.9.14'),
(73, 5, '2025-11-11 00:09:13', '189.35.9.14'),
(74, 5, '2025-11-11 01:04:45', '189.35.9.14'),
(75, 5, '2025-11-11 15:16:36', '191.187.237.97'),
(76, 5, '2025-11-11 21:14:14', '191.187.237.97'),
(77, 5, '2025-11-11 22:18:13', '189.35.9.14'),
(78, 5, '2025-11-11 23:36:47', '189.35.9.14'),
(79, 5, '2025-11-12 12:09:54', '189.35.9.14'),
(80, 12, '2025-11-12 15:15:42', '189.35.9.14'),
(81, 5, '2025-11-13 15:40:26', '191.187.237.97'),
(82, 12, '2025-11-13 15:40:37', '191.187.237.97'),
(83, 14, '2025-11-13 15:41:01', '191.187.237.97'),
(84, 14, '2025-11-13 18:37:26', '191.187.237.97'),
(85, 5, '2025-11-13 20:03:43', '191.187.237.97'),
(86, 14, '2025-11-13 21:05:16', '191.187.237.97'),
(87, 5, '2025-11-14 12:35:02', '189.35.9.14'),
(88, 5, '2025-11-14 14:46:59', '191.187.237.97'),
(89, 12, '2025-11-14 16:13:51', '191.187.237.97'),
(90, 12, '2025-11-15 17:14:50', '191.187.237.97'),
(91, 5, '2025-11-16 01:51:57', '189.35.9.14'),
(92, 5, '2025-11-19 22:48:46', '191.187.233.158'),
(93, 5, '2025-11-19 23:04:34', '191.187.233.158'),
(94, 5, '2025-11-20 00:06:25', '191.187.233.158'),
(95, 5, '2025-11-20 01:01:46', '191.187.233.158'),
(96, 5, '2025-11-28 22:01:39', '191.187.233.158'),
(97, 12, '2025-12-17 17:33:43', '191.187.237.97'),
(98, 12, '2025-12-17 17:34:15', '191.187.237.97'),
(99, 5, '2025-12-17 22:31:20', '191.187.233.158'),
(100, 5, '2025-12-18 15:52:49', '191.187.237.97'),
(101, 12, '2025-12-18 17:09:26', '191.187.237.97'),
(102, 5, '2025-12-18 21:52:39', '191.187.237.97'),
(103, 5, '2025-12-19 14:45:44', '191.187.237.97'),
(104, 5, '2025-12-20 01:33:27', '191.187.233.158'),
(105, 12, '2025-12-20 01:40:56', '191.187.233.158'),
(106, 12, '2025-12-20 16:05:13', '191.187.237.97'),
(107, 12, '2025-12-20 22:09:53', '191.187.233.158'),
(108, 5, '2025-12-20 22:10:12', '191.187.233.158'),
(109, 12, '2025-12-20 22:12:19', '191.187.233.158'),
(110, 12, '2025-12-20 22:13:27', '191.187.233.158'),
(111, 5, '2025-12-20 22:13:37', '191.187.233.158'),
(112, 14, '2025-12-20 22:15:02', '191.187.233.158'),
(113, 5, '2025-12-21 01:01:09', '191.187.233.158'),
(114, 13, '2025-12-21 02:23:57', '191.187.233.158'),
(115, 13, '2025-12-21 02:30:31', '191.187.233.158'),
(116, 5, '2025-12-21 02:35:59', '191.187.233.158'),
(117, 14, '2025-12-21 02:36:10', '191.187.233.158'),
(118, 12, '2025-12-21 02:36:24', '191.187.233.158'),
(119, 13, '2025-12-21 02:39:25', '191.187.233.158'),
(120, 5, '2025-12-21 02:40:18', '191.187.233.158'),
(121, 13, '2025-12-21 02:40:53', '191.187.233.158'),
(122, 5, '2025-12-21 02:43:38', '191.187.233.158'),
(123, 13, '2025-12-21 02:44:26', '191.187.233.158'),
(124, 5, '2025-12-21 16:23:01', '191.187.237.97'),
(125, 12, '2025-12-21 19:09:48', '191.187.237.97'),
(126, 13, '2025-12-21 19:12:07', '191.187.237.97'),
(127, 12, '2025-12-21 19:35:50', '191.187.237.97'),
(128, 5, '2025-12-21 20:35:47', '191.187.233.158'),
(129, 12, '2025-12-21 21:44:42', '191.187.237.97'),
(130, 5, '2025-12-21 22:11:57', '191.187.237.97'),
(131, 13, '2025-12-21 22:14:10', '191.187.237.97'),
(132, 5, '2025-12-22 00:04:51', '191.187.233.158'),
(133, 5, '2025-12-22 00:11:46', '191.187.233.158'),
(134, 5, '2025-12-22 00:12:36', '191.187.233.158'),
(135, 5, '2025-12-22 01:00:36', '191.187.233.158'),
(136, 5, '2025-12-22 17:16:58', '191.187.237.97'),
(137, 5, '2025-12-22 20:42:28', '191.187.237.97'),
(138, 5, '2025-12-22 21:41:01', '191.187.237.97'),
(139, 5, '2025-12-23 01:47:38', '191.187.233.158'),
(140, 5, '2025-12-24 02:43:22', '191.187.233.158'),
(141, 5, '2025-12-24 14:45:43', '191.187.237.97'),
(142, 5, '2025-12-24 21:14:12', '191.187.233.158'),
(143, 5, '2025-12-24 22:02:00', '191.187.233.158'),
(144, 5, '2025-12-25 02:12:56', '179.221.200.183'),
(145, 5, '2025-12-25 19:26:39', '191.187.233.158'),
(146, 13, '2025-12-25 19:29:17', '191.187.233.158'),
(147, 13, '2025-12-25 19:31:31', '191.187.233.158'),
(148, 13, '2025-12-25 21:00:44', '191.187.233.158'),
(149, 5, '2025-12-25 21:23:48', '191.187.233.158'),
(150, 13, '2025-12-25 23:27:35', '191.187.233.158'),
(151, 5, '2025-12-26 00:53:56', '191.187.233.158'),
(152, 13, '2025-12-26 04:09:26', '191.187.233.158'),
(153, 5, '2025-12-26 05:21:12', '191.187.233.158'),
(154, 5, '2025-12-26 14:23:46', '191.187.237.97'),
(155, 5, '2025-12-26 18:54:02', '191.187.233.158'),
(156, 5, '2025-12-26 22:23:47', '191.187.233.158'),
(157, 5, '2025-12-26 23:06:37', '191.187.233.158'),
(158, 5, '2025-12-27 00:57:21', '191.187.233.158'),
(159, 13, '2025-12-27 01:09:22', '191.187.233.158'),
(160, 5, '2025-12-27 03:56:52', '191.187.233.158'),
(161, 5, '2025-12-27 05:09:02', '191.187.233.158'),
(162, 5, '2025-12-27 15:08:13', '191.187.237.97'),
(163, 5, '2025-12-27 21:58:05', '191.187.237.97'),
(164, 13, '2025-12-27 21:59:58', '191.187.237.97'),
(165, 5, '2025-12-28 02:29:34', '191.187.233.158'),
(166, 5, '2025-12-28 03:30:09', '191.187.233.158'),
(167, 13, '2025-12-28 03:35:06', '191.187.233.158'),
(168, 5, '2025-12-28 03:39:45', '191.187.233.158'),
(169, 13, '2025-12-28 03:43:45', '191.187.233.158'),
(170, 13, '2025-12-28 16:32:18', '191.187.237.97'),
(171, 5, '2025-12-28 16:32:51', '191.187.237.97'),
(172, 13, '2025-12-28 16:47:04', '191.187.237.97'),
(173, 5, '2025-12-28 20:09:22', '191.187.233.158'),
(174, 5, '2025-12-29 02:08:50', '191.187.233.158'),
(175, 5, '2025-12-29 14:27:40', '191.187.237.97'),
(176, 5, '2025-12-29 15:59:55', '191.187.237.97'),
(177, 5, '2025-12-29 20:21:21', '191.187.237.97'),
(178, 5, '2025-12-29 20:24:03', '191.187.237.97'),
(179, 5, '2025-12-29 21:51:44', '191.187.233.158'),
(180, 5, '2025-12-29 23:09:38', '191.187.233.158'),
(181, 13, '2025-12-30 01:04:15', '191.187.233.158'),
(182, 5, '2025-12-30 14:49:44', '191.187.237.97'),
(183, 5, '2025-12-31 20:37:31', '191.187.237.97'),
(184, 5, '2026-01-01 00:36:04', '179.221.200.131'),
(185, 5, '2026-01-01 17:53:56', '191.187.237.97'),
(186, 5, '2026-01-01 23:16:22', '191.187.237.97'),
(187, 5, '2026-01-02 03:00:56', '191.187.233.158'),
(188, 5, '2026-01-02 05:19:33', '191.187.233.158'),
(189, 5, '2026-01-02 14:51:26', '191.187.237.97'),
(190, 5, '2026-01-02 17:21:24', '191.187.237.97'),
(191, 5, '2026-01-03 06:25:25', '191.187.233.158'),
(192, 5, '2026-01-03 14:54:46', '191.187.237.97'),
(193, 5, '2026-01-03 22:14:40', '191.187.233.158'),
(194, 5, '2026-01-04 00:04:29', '191.187.233.158'),
(195, 12, '2026-01-04 00:10:19', '191.187.233.158'),
(196, 5, '2026-01-04 00:54:13', '191.187.233.158'),
(197, 12, '2026-01-04 00:56:48', '191.187.233.158'),
(198, 5, '2026-01-04 00:57:56', '191.187.233.158'),
(199, 12, '2026-01-04 01:00:16', '191.187.233.158'),
(200, 5, '2026-01-04 01:00:33', '191.187.233.158'),
(201, 5, '2026-01-04 16:11:34', '191.187.233.158'),
(202, 13, '2026-01-04 16:13:00', '191.187.233.158'),
(203, 5, '2026-01-04 16:13:34', '191.187.233.158'),
(204, 5, '2026-01-05 14:23:35', '191.187.237.97'),
(205, 12, '2026-01-05 14:46:46', '191.187.237.97'),
(206, 5, '2026-01-05 18:21:21', '191.187.237.97'),
(207, 5, '2026-01-05 19:23:31', '191.187.233.158'),
(208, 5, '2026-01-05 20:39:43', '191.187.237.97'),
(209, 5, '2026-01-05 22:10:27', '191.187.233.158'),
(210, 5, '2026-01-06 16:19:17', '191.187.237.97'),
(211, 5, '2026-01-06 16:19:36', '191.187.237.97'),
(212, 13, '2026-01-06 21:33:14', '191.187.237.97'),
(213, 5, '2026-01-06 21:42:45', '191.187.237.97'),
(214, 15, '2026-01-06 21:42:53', '191.187.237.97'),
(215, 5, '2026-01-07 02:35:13', '191.187.233.158'),
(216, 15, '2026-01-07 02:48:49', '191.187.233.158'),
(217, 5, '2026-01-07 14:48:24', '191.187.237.97'),
(218, 5, '2026-01-07 17:46:51', '191.187.237.97'),
(219, 13, '2026-01-07 19:03:35', '191.187.233.158'),
(220, 5, '2026-01-07 20:40:26', '191.187.237.97'),
(221, 5, '2026-01-08 00:46:07', '191.187.233.158'),
(222, 13, '2026-01-08 02:08:21', '191.187.233.158'),
(223, 5, '2026-01-08 02:24:38', '191.187.233.158'),
(224, 5, '2026-01-08 02:25:41', '191.187.233.158'),
(225, 13, '2026-01-08 02:32:15', '40.160.233.102'),
(226, 5, '2026-01-08 03:10:37', '191.187.233.158'),
(227, 5, '2026-01-08 21:27:17', '191.187.237.97'),
(228, 13, '2026-01-08 22:05:45', '189.35.9.144'),
(229, 5, '2026-01-08 22:12:14', '191.187.233.158'),
(230, 5, '2026-01-09 03:18:47', '191.187.233.158'),
(231, 5, '2026-01-09 16:52:24', '191.187.237.97'),
(232, 5, '2026-01-09 19:05:02', '191.187.233.158'),
(233, 5, '2026-01-09 21:00:11', '191.187.237.97'),
(234, 5, '2026-01-09 21:24:54', '191.187.237.97'),
(235, 5, '2026-01-09 22:16:41', '191.187.233.158'),
(236, 5, '2026-01-10 14:40:16', '191.187.237.97'),
(237, 5, '2026-01-10 17:33:17', '191.187.237.97'),
(238, 5, '2026-01-10 18:46:58', '191.187.237.97'),
(239, 5, '2026-01-10 20:11:22', '191.187.237.97'),
(240, 5, '2026-01-11 00:53:10', '191.187.233.158'),
(241, 5, '2026-01-11 17:06:50', '191.187.237.97'),
(242, 12, '2026-01-11 18:45:23', '191.187.237.97'),
(243, 5, '2026-01-11 18:56:43', '191.187.237.97'),
(244, 5, '2026-01-11 19:44:07', '191.187.233.158'),
(245, 5, '2026-01-11 19:46:08', '191.187.233.158'),
(246, 13, '2026-01-11 19:46:48', '191.187.233.158'),
(247, 13, '2026-01-11 20:26:37', '191.187.237.97'),
(248, 5, '2026-01-11 20:27:04', '191.187.237.97'),
(249, 5, '2026-01-11 21:35:51', '191.187.237.97'),
(250, 12, '2026-01-11 21:36:05', '191.187.237.97'),
(251, 5, '2026-01-11 21:36:58', '191.187.237.97'),
(252, 12, '2026-01-11 22:01:38', '191.187.237.97'),
(253, 5, '2026-01-11 23:30:36', '191.187.237.97'),
(254, 5, '2026-01-12 00:20:56', '191.187.233.158'),
(255, 5, '2026-01-12 02:03:42', '191.187.233.158'),
(256, 5, '2026-01-12 02:29:43', '191.187.233.158'),
(257, 5, '2026-01-13 17:59:04', '191.187.237.97'),
(258, 5, '2026-01-13 19:30:21', '191.187.233.158'),
(259, 5, '2026-01-13 20:39:34', '191.187.237.97'),
(260, 5, '2026-01-13 22:37:56', '191.187.233.158'),
(261, 5, '2026-01-13 23:57:15', '191.187.233.158'),
(262, 5, '2026-01-14 00:21:07', '191.187.233.158'),
(263, 5, '2026-01-14 01:28:13', '191.187.233.158'),
(264, 13, '2026-01-14 01:28:57', '191.187.233.158'),
(265, 5, '2026-01-14 01:37:16', '191.187.237.97'),
(266, 5, '2026-01-14 01:52:50', '143.208.98.79'),
(267, 5, '2026-01-14 02:01:28', '191.187.233.158'),
(268, 5, '2026-01-14 15:11:43', '191.187.237.97'),
(269, 5, '2026-01-14 16:26:48', '191.187.237.97'),
(270, 5, '2026-01-14 19:06:35', '191.187.233.158'),
(271, 5, '2026-01-14 20:22:26', '191.187.237.97'),
(272, 5, '2026-01-14 22:04:09', '191.187.237.97'),
(273, 5, '2026-01-15 00:04:03', '191.187.233.158'),
(274, 5, '2026-01-15 01:19:28', '191.187.233.158'),
(275, 5, '2026-01-15 14:23:27', '191.187.237.97'),
(276, 5, '2026-01-15 15:48:42', '191.187.237.97'),
(277, 5, '2026-01-15 17:43:42', '191.187.237.97'),
(278, 5, '2026-01-15 20:00:49', '191.187.237.97'),
(279, 5, '2026-01-15 21:49:08', '191.187.237.97'),
(280, 5, '2026-01-15 23:20:31', '191.187.233.158'),
(281, 5, '2026-01-15 23:39:20', '191.187.233.158'),
(282, 5, '2026-01-15 23:57:22', '191.187.233.158'),
(283, 5, '2026-01-16 00:10:52', '191.187.233.158'),
(284, 5, '2026-01-16 01:22:43', '191.187.233.158'),
(285, 5, '2026-01-16 14:22:35', '191.187.237.97'),
(286, 5, '2026-01-16 17:56:43', '191.187.237.97'),
(287, 5, '2026-01-16 21:00:41', '191.187.237.97'),
(288, 5, '2026-01-16 21:08:10', '191.187.237.97'),
(289, 5, '2026-01-16 22:17:07', '191.187.237.97'),
(290, 5, '2026-01-16 23:35:20', '179.221.200.125'),
(291, 5, '2026-01-17 02:11:59', '191.187.233.158'),
(292, 5, '2026-01-17 02:13:52', '191.187.233.158'),
(293, 13, '2026-01-17 02:14:27', '191.187.233.158'),
(294, 5, '2026-01-17 14:28:18', '191.187.237.97'),
(295, 13, '2026-01-17 18:47:23', '191.187.233.158'),
(296, 13, '2026-01-17 18:52:09', '191.187.233.158'),
(297, 5, '2026-01-17 18:52:30', '191.187.233.158'),
(298, 5, '2026-01-17 19:06:25', '191.187.233.158'),
(299, 5, '2026-01-17 19:06:54', '191.187.233.158'),
(300, 5, '2026-01-17 20:31:58', '191.187.237.97'),
(301, 13, '2026-01-17 20:50:01', '191.187.237.97'),
(302, 13, '2026-01-17 21:05:15', '191.187.237.97'),
(303, 5, '2026-01-17 21:13:18', '191.187.237.97'),
(304, 12, '2026-01-17 21:48:50', '191.187.237.97'),
(305, 5, '2026-01-17 22:23:24', '191.187.233.158'),
(306, 5, '2026-01-18 16:33:29', '191.187.237.97'),
(307, 5, '2026-01-18 19:50:24', '191.187.233.158'),
(308, 5, '2026-01-18 22:24:38', '191.187.237.97'),
(309, 12, '2026-01-18 22:47:22', '191.187.237.97'),
(310, 13, '2026-01-18 23:30:46', '191.187.237.97'),
(311, 12, '2026-01-18 23:33:32', '191.187.237.97'),
(312, 13, '2026-01-18 23:37:30', '191.187.237.97'),
(313, 13, '2026-01-18 23:59:05', '191.187.233.158'),
(314, 5, '2026-01-19 01:53:00', '191.187.233.158'),
(315, 5, '2026-01-19 14:31:24', '191.187.237.97'),
(316, 12, '2026-01-19 14:48:53', '191.187.237.97'),
(317, 5, '2026-01-19 18:47:55', '179.221.200.104'),
(318, 5, '2026-01-19 20:53:16', '191.187.237.97'),
(319, 13, '2026-01-19 21:04:08', '191.187.237.97'),
(320, 5, '2026-01-19 22:10:24', '191.187.233.158'),
(321, 5, '2026-01-19 22:17:46', '191.187.233.158'),
(322, 13, '2026-01-19 22:19:15', '191.187.233.158'),
(323, 13, '2026-01-19 22:20:08', '191.187.233.158'),
(324, 5, '2026-01-19 22:20:53', '191.187.233.158'),
(325, 5, '2026-01-20 02:25:42', '191.187.233.158'),
(326, 13, '2026-01-20 16:24:15', '191.187.237.97'),
(327, 12, '2026-01-20 16:43:41', '191.187.237.97'),
(328, 12, '2026-01-20 20:04:15', '191.187.237.97'),
(329, 13, '2026-01-20 22:12:23', '191.6.90.246'),
(330, 13, '2026-01-20 22:23:35', '191.187.233.158'),
(331, 13, '2026-01-21 01:50:09', '191.187.233.158'),
(332, 13, '2026-01-21 23:29:44', '191.187.233.158'),
(333, 5, '2026-01-21 23:29:55', '191.187.233.158'),
(334, 13, '2026-01-21 23:30:17', '191.187.233.158'),
(335, 13, '2026-01-22 14:24:43', '191.187.237.97'),
(336, 12, '2026-01-22 15:29:09', '191.187.237.97'),
(337, 13, '2026-01-22 15:38:14', '191.187.237.97'),
(338, 13, '2026-01-22 15:39:49', '191.187.237.97'),
(339, 5, '2026-01-22 15:40:25', '191.187.237.97'),
(340, 12, '2026-01-22 15:40:47', '191.187.237.97'),
(341, 13, '2026-01-22 15:41:47', '191.187.237.97'),
(342, 13, '2026-01-22 16:23:12', '191.187.237.97'),
(343, 13, '2026-01-22 17:33:06', '191.187.237.97'),
(344, 12, '2026-01-22 17:33:49', '191.187.237.97'),
(345, 12, '2026-01-22 20:03:48', '191.187.237.97'),
(346, 13, '2026-01-22 20:18:27', '191.187.237.97'),
(347, 13, '2026-01-22 20:35:14', '191.187.237.97'),
(348, 15, '2026-01-22 22:57:19', '191.187.233.158'),
(349, 5, '2026-01-23 14:32:08', '191.187.237.97'),
(350, 13, '2026-01-23 14:34:47', '191.187.237.97'),
(351, 13, '2026-01-23 14:53:55', '191.187.237.97'),
(352, 15, '2026-01-23 15:24:31', '191.187.237.97'),
(353, 13, '2026-01-23 19:50:29', '191.187.237.97'),
(354, 5, '2026-01-23 20:06:58', '191.187.237.97'),
(355, 5, '2026-01-24 15:32:33', '191.187.237.97'),
(356, 13, '2026-01-24 16:05:04', '191.187.237.97'),
(357, 13, '2026-01-24 16:06:22', '191.187.237.97'),
(358, 12, '2026-01-24 16:19:55', '191.187.237.97'),
(359, 5, '2026-01-24 16:22:46', '191.187.237.97'),
(360, 15, '2026-01-24 16:22:53', '191.187.237.97'),
(361, 13, '2026-01-24 17:50:19', '191.187.237.97'),
(362, 12, '2026-01-24 21:29:56', '191.187.237.97'),
(363, 12, '2026-01-24 21:31:56', '191.187.237.97'),
(364, 12, '2026-01-25 00:27:47', '191.187.233.158'),
(365, 12, '2026-01-26 20:09:39', '191.187.237.97'),
(366, 13, '2026-01-26 21:02:30', '191.187.237.97'),
(367, 12, '2026-01-26 21:37:48', '191.187.237.97'),
(368, 15, '2026-01-26 23:55:16', '191.187.233.158'),
(369, 12, '2026-01-26 23:55:54', '191.187.233.158'),
(370, 5, '2026-01-27 14:43:45', '191.187.237.97'),
(371, 13, '2026-01-27 14:52:48', '191.187.237.97'),
(372, 5, '2026-01-27 16:17:10', '191.187.237.97'),
(373, 15, '2026-01-27 18:48:46', '191.187.233.158'),
(374, 15, '2026-01-27 19:29:07', '191.187.233.158'),
(375, 13, '2026-01-27 20:08:19', '191.187.237.97'),
(376, 13, '2026-01-27 20:08:22', '191.187.237.97'),
(377, 13, '2026-01-27 20:22:29', '191.187.237.97'),
(378, 15, '2026-01-28 02:18:45', '191.187.233.158'),
(379, 15, '2026-01-28 13:37:43', '191.187.237.97'),
(380, 12, '2026-01-28 13:38:17', '191.187.237.97'),
(381, 13, '2026-01-28 14:10:06', '191.187.237.97'),
(382, 13, '2026-01-28 21:12:32', '191.187.237.97'),
(383, 15, '2026-01-28 21:45:27', '191.187.237.97'),
(384, 15, '2026-01-28 21:55:56', '191.187.237.97'),
(385, 15, '2026-01-28 22:48:20', '191.187.233.158'),
(386, 5, '2026-01-28 22:51:11', '191.187.233.158'),
(387, 13, '2026-01-30 14:37:14', '191.187.237.97'),
(388, 5, '2026-01-30 14:39:00', '191.187.237.97'),
(389, 15, '2026-01-30 16:30:51', '191.187.237.97'),
(390, 13, '2026-01-30 16:32:23', '191.187.237.97'),
(391, 15, '2026-01-30 18:53:31', '191.187.233.158'),
(392, 13, '2026-01-30 20:14:42', '191.187.237.97'),
(393, 15, '2026-01-30 21:55:42', '191.187.237.97'),
(394, 5, '2026-01-30 22:19:51', '191.187.233.158'),
(395, 5, '2026-01-30 22:36:10', '191.187.233.158'),
(396, 13, '2026-01-31 14:43:53', '191.187.237.97'),
(397, 5, '2026-01-31 17:49:25', '191.187.237.97'),
(398, 13, '2026-01-31 20:46:42', '191.187.237.97'),
(399, 12, '2026-01-31 20:47:03', '191.187.237.97'),
(400, 5, '2026-02-01 13:06:28', '191.187.237.97'),
(401, 15, '2026-02-01 13:17:24', '191.187.237.97'),
(402, 5, '2026-02-01 14:33:53', '191.187.233.158'),
(403, 13, '2026-02-01 14:34:16', '191.187.233.158'),
(404, 13, '2026-02-01 15:37:01', '191.187.237.97'),
(405, 5, '2026-02-02 13:27:48', '191.187.237.97'),
(406, 13, '2026-02-02 14:56:48', '191.187.237.97'),
(407, 5, '2026-02-02 15:09:18', '191.187.237.97'),
(408, 5, '2026-02-02 21:13:12', '191.187.237.97'),
(409, 5, '2026-02-03 11:21:09', '191.187.237.97'),
(410, 13, '2026-02-03 15:23:01', '191.187.237.97'),
(411, 15, '2026-02-03 17:17:58', '191.187.237.97'),
(412, 5, '2026-02-03 18:10:52', '191.187.237.97'),
(413, 5, '2026-02-03 20:29:42', '191.187.237.97'),
(414, 13, '2026-02-03 21:05:50', '191.187.237.97'),
(415, 5, '2026-02-03 22:47:13', '191.187.233.158'),
(416, 5, '2026-02-03 22:48:06', '191.187.233.158'),
(417, 15, '2026-02-03 22:51:35', '191.187.233.158'),
(418, 5, '2026-02-04 12:53:20', '191.187.237.97'),
(419, 5, '2026-02-04 20:58:55', '191.187.237.97'),
(420, 5, '2026-02-04 22:55:14', '191.187.233.158'),
(421, 5, '2026-02-05 00:26:54', '191.187.233.158'),
(422, 15, '2026-02-05 00:31:18', '191.187.233.158'),
(423, 15, '2026-02-05 00:41:21', '191.187.233.158'),
(424, 5, '2026-02-05 00:41:32', '191.187.233.158'),
(425, 15, '2026-02-05 00:41:59', '191.187.233.158'),
(426, 5, '2026-02-06 13:35:22', '181.77.103.190'),
(427, 5, '2026-02-09 18:11:58', '181.77.105.3'),
(428, 13, '2026-02-09 20:05:31', '191.187.233.158'),
(429, 5, '2026-02-11 12:13:29', '191.187.237.97'),
(430, 13, '2026-02-11 15:18:48', '191.187.237.97'),
(431, 5, '2026-02-12 12:57:23', '191.187.237.97'),
(432, 13, '2026-02-12 12:57:38', '191.187.237.97'),
(433, 5, '2026-02-12 13:20:15', '191.187.237.97'),
(434, 13, '2026-02-12 14:31:02', '191.187.237.97'),
(435, 5, '2026-02-12 15:06:00', '191.187.237.97'),
(436, 15, '2026-02-12 15:07:00', '191.187.237.97'),
(437, 15, '2026-02-12 15:45:59', '191.187.237.97'),
(438, 5, '2026-02-12 17:07:14', '191.187.237.97'),
(439, 15, '2026-02-12 18:44:05', '191.187.233.158'),
(440, 13, '2026-02-12 20:54:10', '191.187.237.97'),
(441, 5, '2026-02-12 21:05:32', '191.187.237.97'),
(442, 15, '2026-02-12 23:46:58', '191.187.233.158'),
(443, 5, '2026-02-12 23:47:38', '191.187.233.158'),
(444, 13, '2026-02-13 12:46:07', '191.187.237.97'),
(445, 5, '2026-02-13 13:04:55', '191.187.237.97'),
(446, 5, '2026-02-13 20:58:31', '191.187.237.97'),
(447, 15, '2026-02-13 23:29:43', '191.187.233.158'),
(448, 5, '2026-02-15 16:02:11', '191.187.237.97'),
(449, 5, '2026-02-17 16:53:35', '191.187.237.97'),
(450, 5, '2026-02-17 19:02:35', '191.187.233.158'),
(451, 5, '2026-02-18 13:09:09', '191.187.237.97'),
(452, 5, '2026-02-18 19:59:33', '191.187.237.97'),
(453, 5, '2026-02-19 14:34:52', '191.187.237.97'),
(454, 15, '2026-02-19 14:56:38', '191.187.237.97'),
(455, 15, '2026-02-20 23:52:12', '191.187.233.158'),
(456, 5, '2026-02-20 23:55:44', '191.187.233.158'),
(457, 5, '2026-02-21 14:19:04', '191.187.237.97'),
(458, 5, '2026-02-22 01:15:13', '191.187.233.158'),
(459, 13, '2026-02-22 15:56:26', '191.187.237.97'),
(460, 5, '2026-02-22 16:43:00', '191.187.237.97'),
(461, 5, '2026-02-22 17:10:50', '191.187.237.97'),
(462, 5, '2026-02-22 21:21:10', '191.187.237.97'),
(463, 5, '2026-02-23 11:18:50', '191.187.233.158'),
(464, 13, '2026-02-23 15:27:33', '191.187.237.97'),
(465, 13, '2026-02-23 17:36:10', '191.187.237.97'),
(466, 13, '2026-02-23 19:55:45', '191.187.237.97'),
(467, 15, '2026-02-23 21:32:30', '191.187.237.97'),
(468, 15, '2026-02-24 00:00:50', '191.187.233.158'),
(469, 13, '2026-02-25 14:33:11', '191.187.237.97'),
(470, 5, '2026-02-25 17:56:02', '191.187.237.97'),
(471, 13, '2026-02-25 18:41:57', '191.187.237.97'),
(472, 13, '2026-02-25 20:18:59', '191.187.237.97'),
(473, 13, '2026-02-25 20:28:30', '191.187.237.97'),
(474, 15, '2026-02-25 22:18:23', '191.187.233.158'),
(475, 15, '2026-02-26 02:04:38', '191.187.233.158'),
(476, 15, '2026-02-26 14:00:15', '191.187.237.97'),
(477, 13, '2026-02-26 14:01:27', '191.187.237.97'),
(478, 15, '2026-02-26 14:02:06', '191.187.237.97'),
(479, 5, '2026-02-26 14:16:15', '191.187.237.97'),
(480, 15, '2026-02-27 14:19:57', '191.187.237.97'),
(481, 13, '2026-02-27 16:59:28', '191.187.237.97'),
(482, 15, '2026-02-27 21:00:15', '191.187.237.97'),
(483, 15, '2026-02-28 14:29:29', '191.187.237.97'),
(484, 15, '2026-03-01 17:01:22', '191.187.233.158'),
(485, 13, '2026-03-02 14:24:58', '191.187.237.97'),
(486, 5, '2026-03-02 14:36:08', '191.187.237.97'),
(487, 13, '2026-03-02 22:00:57', '191.187.233.158'),
(488, 5, '2026-03-02 22:01:46', '191.187.233.158'),
(489, 15, '2026-03-03 19:39:34', '191.187.237.97'),
(490, 13, '2026-03-03 19:40:12', '191.187.237.97'),
(491, 5, '2026-03-03 19:41:09', '191.187.237.97'),
(492, 15, '2026-03-03 19:51:14', '191.187.237.97'),
(493, 5, '2026-03-04 16:30:49', '191.187.237.97'),
(494, 13, '2026-03-04 16:44:56', '191.187.237.97'),
(495, 15, '2026-03-04 16:45:18', '191.187.237.97'),
(496, 15, '2026-03-04 20:22:15', '191.187.237.97'),
(497, 15, '2026-03-05 14:06:18', '191.187.237.97'),
(498, 5, '2026-03-05 14:16:38', '191.187.237.97'),
(499, 15, '2026-03-05 20:26:37', '191.187.237.97'),
(500, 15, '2026-03-11 14:00:27', '191.187.237.97'),
(501, 5, '2026-03-11 14:11:52', '191.187.237.97'),
(502, 15, '2026-03-11 17:38:44', '191.187.237.97'),
(503, 5, '2026-03-11 17:39:05', '191.187.237.97'),
(504, 15, '2026-03-11 20:18:59', '191.187.237.97'),
(505, 15, '2026-03-12 15:14:32', '191.187.237.97'),
(506, 5, '2026-03-12 17:13:56', '191.187.237.97'),
(507, 5, '2026-03-12 19:11:00', '191.187.233.158'),
(508, 5, '2026-03-12 20:07:11', '191.187.237.97'),
(509, 5, '2026-03-13 13:17:44', '191.187.237.97'),
(510, 5, '2026-03-13 14:11:25', '191.187.237.97'),
(511, 15, '2026-03-13 18:20:01', '191.187.237.97'),
(512, 5, '2026-03-13 20:22:50', '191.187.237.97'),
(513, 15, '2026-03-13 20:27:14', '191.187.237.97'),
(514, 15, '2026-03-13 20:57:01', '191.187.237.97'),
(515, 5, '2026-03-15 16:45:11', '191.187.237.97'),
(516, 5, '2026-03-15 16:45:11', '191.187.237.97'),
(517, 15, '2026-03-16 21:18:53', '191.187.237.97'),
(518, 15, '2026-03-17 00:27:16', '189.35.9.144'),
(519, 15, '2026-03-17 11:28:50', '191.187.233.158'),
(520, 15, '2026-03-18 19:46:51', '191.187.237.97'),
(521, 5, '2026-03-18 20:03:44', '191.187.237.97'),
(522, 5, '2026-03-18 22:17:40', '138.118.29.144'),
(523, 5, '2026-03-19 01:26:15', '191.187.233.158'),
(524, 5, '2026-03-19 11:22:25', '191.187.233.158'),
(525, 15, '2026-03-19 14:18:17', '191.187.237.97'),
(526, 5, '2026-03-19 14:18:32', '191.187.237.97'),
(527, 15, '2026-03-19 20:42:02', '191.187.237.97'),
(528, 5, '2026-03-19 20:47:53', '191.187.237.97'),
(529, 15, '2026-03-20 15:06:50', '181.221.152.79'),
(530, 5, '2026-03-20 18:18:31', '181.221.152.61'),
(531, 5, '2026-03-20 18:19:27', '181.221.152.61'),
(532, 5, '2026-03-20 20:21:12', '181.221.152.185'),
(533, 5, '2026-03-20 20:42:20', '181.221.152.185'),
(534, 15, '2026-03-20 23:22:57', '181.221.152.98'),
(535, 15, '2026-03-21 15:42:29', '181.221.152.185'),
(536, 5, '2026-03-21 15:43:37', '181.221.152.185'),
(537, 5, '2026-03-21 16:00:31', '181.221.152.185'),
(538, 15, '2026-03-21 16:58:15', '181.221.152.185'),
(539, 5, '2026-03-22 18:25:33', '68.235.61.182'),
(540, 5, '2026-03-22 18:42:33', '68.235.61.182'),
(541, 15, '2026-03-22 19:05:19', '68.235.61.182'),
(542, 5, '2026-03-22 19:21:36', '138.118.29.144'),
(543, 5, '2026-03-22 19:44:52', '181.221.152.98'),
(544, 5, '2026-03-22 21:56:13', '181.221.152.185'),
(545, 16, '2026-03-22 23:14:20', '181.221.152.185'),
(546, 5, '2026-03-23 00:18:01', '181.221.152.98'),
(547, 5, '2026-03-23 14:46:18', '181.221.152.185'),
(548, 17, '2026-03-23 15:09:44', '181.221.152.185'),
(549, 15, '2026-03-23 15:33:22', '181.221.152.185'),
(550, 13, '2026-03-23 20:34:58', '181.221.152.185'),
(551, 15, '2026-03-23 21:20:31', '181.221.152.185'),
(552, 15, '2026-03-24 14:53:13', '191.187.232.168'),
(553, 5, '2026-03-24 15:48:09', '191.187.232.168'),
(554, 5, '2026-03-26 14:00:35', '191.187.232.168'),
(555, 5, '2026-03-26 15:26:16', '191.187.232.168'),
(556, 5, '2026-03-26 15:52:03', '191.187.232.168'),
(557, 5, '2026-03-26 16:56:20', '191.187.232.168'),
(558, 5, '2026-03-26 16:58:07', '191.187.232.168'),
(559, 15, '2026-03-26 17:22:55', '191.187.232.168'),
(560, 18, '2026-03-26 17:26:55', '191.187.232.168'),
(561, 15, '2026-03-26 17:29:30', '191.187.232.168'),
(562, 5, '2026-03-27 14:59:32', '191.187.232.168'),
(563, 15, '2026-03-27 15:11:40', '191.187.232.168'),
(564, 15, '2026-03-28 13:59:39', '191.187.232.168'),
(565, 5, '2026-03-28 14:08:08', '191.187.232.168'),
(566, 15, '2026-03-29 15:07:41', '191.187.232.168'),
(567, 5, '2026-03-29 15:08:20', '191.187.232.168'),
(568, 15, '2026-03-30 01:19:47', '181.221.152.213'),
(569, 5, '2026-03-30 01:26:11', '181.221.152.213'),
(570, 5, '2026-03-30 01:39:10', '181.221.152.213'),
(571, 5, '2026-03-30 01:46:42', '181.221.152.213'),
(572, 15, '2026-03-30 01:49:05', '181.221.152.213'),
(573, 15, '2026-03-30 14:56:47', '191.187.232.168'),
(574, 5, '2026-03-30 14:56:59', '191.187.232.168'),
(575, 15, '2026-03-30 14:58:37', '191.187.232.168'),
(576, 15, '2026-03-30 15:20:44', '191.187.232.168'),
(577, 15, '2026-03-31 17:16:54', '181.221.152.213'),
(578, 15, '2026-04-01 15:56:02', '191.187.232.168'),
(579, 5, '2026-04-01 15:57:19', '191.187.232.168'),
(580, 5, '2026-04-01 16:08:13', '191.187.232.168');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Logs_Visualizacao_Post`
--

CREATE TABLE `Logs_Visualizacao_Post` (
  `id` int(11) NOT NULL,
  `id_postagem` int(11) NOT NULL,
  `id_usuario_visualizou` int(11) DEFAULT NULL,
  `data_visualizacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Logs_Visualizacao_Post`
--

INSERT INTO `Logs_Visualizacao_Post` (`id`, `id_postagem`, `id_usuario_visualizou`, `data_visualizacao`) VALUES
(1, 15, 5, '2025-11-02 18:03:23'),
(2, 15, 5, '2025-11-02 18:03:26'),
(3, 13, 5, '2025-11-02 21:22:04'),
(4, 13, 5, '2025-11-02 21:48:19'),
(5, 13, 5, '2025-11-02 22:00:04'),
(6, 13, 5, '2025-11-02 22:04:37'),
(7, 8, 5, '2025-11-03 00:05:40'),
(8, 13, 5, '2025-11-03 00:47:56'),
(9, 5, 12, '2025-11-06 16:19:09'),
(10, 5, 12, '2025-11-06 16:19:21'),
(11, 15, 12, '2025-11-06 16:20:13'),
(12, 15, 12, '2025-11-06 16:20:24'),
(13, 15, 12, '2025-11-06 16:28:09'),
(14, 15, 12, '2025-11-06 16:28:16'),
(15, 13, 5, '2025-11-06 20:16:45'),
(16, 13, 5, '2025-11-06 20:16:50'),
(17, 7, 5, '2025-11-06 20:55:46'),
(18, 7, 5, '2025-11-06 20:55:51'),
(19, 7, 5, '2025-11-06 20:56:17'),
(20, 7, 5, '2025-11-06 20:56:21'),
(21, 8, 5, '2025-11-06 20:56:40'),
(22, 8, 5, '2025-11-06 20:56:44'),
(23, 7, 5, '2025-11-06 20:56:55'),
(24, 7, 5, '2025-11-06 20:56:57'),
(25, 8, 5, '2025-11-06 20:57:01'),
(26, 8, 5, '2025-11-06 20:57:04'),
(27, 8, 5, '2025-11-06 21:02:08'),
(28, 8, 5, '2025-11-06 21:02:11'),
(29, 15, 14, '2025-11-07 01:20:18'),
(30, 8, 14, '2025-11-07 01:22:58'),
(31, 3, 5, '2025-11-07 02:10:36'),
(32, 3, 5, '2025-11-07 15:05:45'),
(33, 5, 5, '2025-11-07 15:18:31'),
(34, 5, 5, '2025-11-07 15:18:34'),
(35, 13, 5, '2025-11-07 15:18:36'),
(36, 13, 5, '2025-11-07 15:18:39'),
(37, 15, 5, '2025-11-07 15:32:43'),
(38, 15, 5, '2025-11-07 15:32:46'),
(39, 16, 5, '2025-11-07 18:16:10'),
(40, 16, 5, '2025-11-07 18:16:12'),
(41, 15, 5, '2025-11-07 18:16:20'),
(42, 15, 5, '2025-11-07 18:16:23'),
(43, 15, 5, '2025-11-07 18:19:06'),
(44, 15, 5, '2025-11-07 18:19:09'),
(46, 15, 5, '2025-11-07 18:19:40'),
(47, 15, 5, '2025-11-07 18:19:43'),
(48, 15, 5, '2025-11-07 18:40:38'),
(49, 15, 5, '2025-11-07 18:40:41'),
(50, 15, 5, '2025-11-07 18:41:40'),
(51, 15, 5, '2025-11-07 18:41:44'),
(52, 15, 5, '2025-11-07 20:56:28'),
(53, 15, 5, '2025-11-07 20:56:31'),
(54, 15, 5, '2025-11-07 20:58:12'),
(55, 15, 5, '2025-11-08 00:14:33'),
(56, 15, 5, '2025-11-08 00:22:25'),
(57, 15, 5, '2025-11-08 01:05:56'),
(58, 15, 5, '2025-11-08 14:57:22'),
(59, 15, 5, '2025-11-08 14:57:26'),
(65, 28, 5, '2025-11-08 16:36:59'),
(66, 28, 5, '2025-11-08 16:37:02'),
(67, 28, 5, '2025-11-08 16:37:08'),
(68, 28, 5, '2025-11-08 16:37:11'),
(69, 28, 5, '2025-11-08 16:37:24'),
(70, 28, 5, '2025-11-08 16:37:28'),
(73, 34, 5, '2025-11-10 01:49:18'),
(74, 34, 5, '2025-11-10 01:49:26'),
(75, 34, 5, '2025-11-10 02:23:26'),
(76, 34, 5, '2025-11-10 02:23:35'),
(77, 34, 5, '2025-11-10 15:20:05'),
(78, 34, 5, '2025-11-10 15:20:09'),
(79, 31, 5, '2025-11-10 15:23:17'),
(80, 31, 5, '2025-11-10 15:23:20'),
(81, 31, 5, '2025-11-10 15:30:59'),
(82, 31, 5, '2025-11-10 15:31:02'),
(83, 31, 5, '2025-11-10 15:40:37'),
(84, 31, 5, '2025-11-10 15:40:40'),
(85, 31, 5, '2025-11-10 15:46:32'),
(86, 31, 5, '2025-11-10 15:46:35'),
(87, 31, 5, '2025-11-10 15:49:43'),
(88, 31, 5, '2025-11-10 15:49:46'),
(89, 31, 5, '2025-11-10 15:49:54'),
(90, 31, 5, '2025-11-10 15:49:56'),
(91, 31, 5, '2025-11-10 16:14:52'),
(92, 31, 5, '2025-11-10 16:14:55'),
(93, 31, 5, '2025-11-10 16:17:15'),
(94, 31, 5, '2025-11-10 16:17:18'),
(95, 31, 5, '2025-11-10 16:19:04'),
(96, 31, 5, '2025-11-10 16:19:06'),
(97, 31, 5, '2025-11-10 16:21:11'),
(98, 31, 5, '2025-11-10 16:21:13'),
(99, 31, 5, '2025-11-10 16:22:55'),
(100, 31, 5, '2025-11-10 16:22:57'),
(101, 31, 5, '2025-11-10 16:23:16'),
(102, 31, 5, '2025-11-10 16:23:19'),
(103, 34, 5, '2025-11-10 17:12:21'),
(104, 34, 5, '2025-11-10 17:12:25'),
(105, 34, 5, '2025-11-10 17:14:02'),
(106, 34, 5, '2025-11-10 17:14:06'),
(107, 31, 5, '2025-11-10 17:14:28'),
(108, 31, 5, '2025-11-10 17:14:30'),
(109, 31, 5, '2025-11-10 17:29:22'),
(110, 31, 5, '2025-11-10 17:29:25'),
(111, 15, 5, '2025-11-10 17:33:29'),
(112, 15, 5, '2025-11-10 17:33:32'),
(113, 15, 5, '2025-11-10 17:42:55'),
(114, 15, 5, '2025-11-10 17:43:00'),
(115, 31, 5, '2025-11-10 19:16:41'),
(116, 31, 5, '2025-11-10 19:16:44'),
(117, 31, 5, '2025-11-10 22:19:32'),
(118, 16, 5, '2025-11-10 22:20:36'),
(119, 16, 5, '2025-11-10 22:20:40'),
(120, 16, 5, '2025-11-10 22:20:58'),
(121, 16, 5, '2025-11-10 22:32:27'),
(122, 16, 5, '2025-11-11 00:12:51'),
(123, 16, 5, '2025-11-11 00:15:29'),
(124, 16, 5, '2025-11-11 00:33:45'),
(125, 13, 5, '2025-11-11 00:46:15'),
(126, 13, 5, '2025-11-11 01:00:59'),
(127, 13, 5, '2025-11-11 01:04:45'),
(128, 34, 5, '2025-11-11 16:06:29'),
(129, 34, 5, '2025-11-11 16:06:32'),
(130, 35, 5, '2025-11-11 16:14:49'),
(131, 35, 5, '2025-11-11 16:14:54'),
(132, 35, 5, '2025-11-11 16:16:52'),
(133, 35, 5, '2025-11-11 16:16:56'),
(134, 35, 5, '2025-11-11 16:20:38'),
(135, 35, 5, '2025-11-11 16:20:42'),
(136, 35, 5, '2025-11-11 16:20:58'),
(137, 35, 5, '2025-11-11 16:21:02'),
(138, 35, 5, '2025-11-11 16:21:05'),
(139, 35, 5, '2025-11-11 16:21:08'),
(140, 35, 5, '2025-11-11 16:39:42'),
(141, 35, 5, '2025-11-11 16:39:46'),
(142, 35, 5, '2025-11-11 16:39:51'),
(143, 35, 5, '2025-11-11 16:39:54'),
(144, 35, 5, '2025-11-12 14:47:56'),
(145, 35, 5, '2025-11-12 14:48:07'),
(146, 35, 5, '2025-11-12 14:48:18'),
(147, 35, 5, '2025-11-12 15:10:47'),
(148, 35, 5, '2025-11-12 15:11:07'),
(149, 35, 5, '2025-11-12 15:11:28'),
(150, 35, 5, '2025-11-12 15:12:21'),
(151, 35, 5, '2025-11-12 15:12:35'),
(152, 35, 5, '2025-11-12 15:14:28'),
(153, 35, 5, '2025-11-12 15:14:37'),
(154, 35, 5, '2025-11-12 15:54:11'),
(155, 35, 5, '2025-12-19 17:50:53'),
(156, 35, 5, '2025-12-19 17:50:55'),
(157, 40, 12, '2025-12-20 22:12:59'),
(158, 39, 5, '2025-12-20 22:13:47'),
(159, 41, 5, '2025-12-20 22:13:53'),
(160, 36, 5, '2025-12-20 22:14:03'),
(161, 36, 5, '2025-12-20 22:14:26'),
(162, 2, 13, '2025-12-21 02:26:33'),
(163, 40, 13, '2025-12-21 02:43:11'),
(164, 43, 5, '2025-12-21 17:19:10'),
(165, 43, 5, '2025-12-21 17:19:13');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Marketplace_Anuncios`
--

CREATE TABLE `Marketplace_Anuncios` (
  `id` int(11) NOT NULL,
  `id_postagem` int(11) NOT NULL,
  `titulo_produto` varchar(150) NOT NULL,
  `descricao_produto` text DEFAULT NULL,
  `preco` decimal(14,2) NOT NULL,
  `moeda` varchar(3) DEFAULT 'BRL',
  `categoria` varchar(50) NOT NULL,
  `condicao` enum('novo','usado_bom','usado_marcas','defeito') NOT NULL,
  `estado` char(2) NOT NULL COMMENT 'Sigla do Estado (Ex: SP, SC, RJ)',
  `cidade` varchar(100) NOT NULL COMMENT 'Nome da cidade por extenso',
  `status_venda` enum('disponivel','reservado','vendido') DEFAULT 'disponivel',
  `views_count` int(11) DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `Marketplace_Anuncios`
--

INSERT INTO `Marketplace_Anuncios` (`id`, `id_postagem`, `titulo_produto`, `descricao_produto`, `preco`, `moeda`, `categoria`, `condicao`, `estado`, `cidade`, `status_venda`, `views_count`, `criado_em`, `atualizado_em`) VALUES
(1, 53, 'iPhone 15 Pro Max - Teste', NULL, 4500.00, 'BRL', 'eletronicos', 'usado_bom', 'SP', 'São Paulo', 'vendido', 22, '2025-12-25 23:17:44', '2026-01-04 00:50:57'),
(2, 54, 'Ventilador', 'Ponto blu', 174.99, 'BRL', 'eletrodomesticos', 'novo', 'SC', 'Itajaí ', 'disponivel', 29, '2025-12-26 22:24:48', '2026-01-07 17:43:54'),
(3, 55, 'Mochila do Sonic', 'Mochila infantil', 999.99, 'BRL', 'bebes', 'novo', 'AM', 'Ataga', 'disponivel', 85, '2025-12-26 22:28:09', '2026-01-05 17:58:03'),
(4, 56, 'Produto Teste', 'Isso é apenas um teste.', 0.00, 'BRL', 'moveis', 'novo', 'BA', 'Feira de Santana', 'disponivel', 19, '2025-12-29 02:14:42', '2026-02-21 16:38:45'),
(8, 61, 'Venha anunciar aqui !!', 'Venha anunciar seus produtos no nosso marketplace !!', 0.01, 'BRL', 'moveis', 'usado_marcas', 'GO', 'Ataga', 'vendido', 19, '2026-01-03 22:16:08', '2026-01-08 22:14:07'),
(9, 93, 'etsad', 'dfafd', 5454.00, 'BRL', 'instrumentos', 'usado_bom', 'MG', 'fsdf', 'disponivel', 0, '2026-02-26 14:18:12', '2026-02-26 14:18:12'),
(10, 103, 'tigre', 'esate', 26.00, 'BRL', 'instrumentos', 'usado_bom', 'MG', 'sçgçd', 'disponivel', 2, '2026-03-28 14:44:39', '2026-03-28 21:29:08');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Marketplace_Interesses`
--

CREATE TABLE `Marketplace_Interesses` (
  `id` int(11) NOT NULL,
  `id_anuncio` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `data_interesse` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `Marketplace_Interesses`
--

INSERT INTO `Marketplace_Interesses` (`id`, `id_anuncio`, `id_usuario`, `data_interesse`) VALUES
(1, 1, 13, '2025-12-28 18:08:10'),
(4, 8, 12, '2026-01-05 14:52:50');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Menus_Sistema`
--

CREATE TABLE `Menus_Sistema` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL COMMENT 'ID do menu pai para submenus (Ex: Configurações)',
  `slug` varchar(100) NOT NULL COMMENT 'A URL amigável (Ex: marketplace/vender)',
  `label` varchar(100) NOT NULL COMMENT 'Nome que aparece no menu',
  `icone` varchar(100) DEFAULT NULL COMMENT 'Classe FontAwesome',
  `arquivo_destino` varchar(255) NOT NULL COMMENT 'Caminho físico relativo à raiz do projeto',
  `permissao` enum('todos','logado','admin') DEFAULT 'logado',
  `status` tinyint(1) DEFAULT 1 COMMENT '1: Ativo, 0: Inativo',
  `exibir_no_menu` tinyint(1) DEFAULT 1 COMMENT 'Se aparece na sidebar',
  `ordem` int(11) DEFAULT 0 COMMENT 'Posição de exibição',
  `permite_parametros` tinyint(1) DEFAULT 0 COMMENT 'Se a rota aceita IDs ou termos (Ex: perfil/1)',
  `manutencao_modulo` tinyint(1) DEFAULT 0 COMMENT 'Modo manutenção específico',
  `liberacao_em` datetime DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `Menus_Sistema`
--

INSERT INTO `Menus_Sistema` (`id`, `parent_id`, `slug`, `label`, `icone`, `arquivo_destino`, `permissao`, `status`, `exibir_no_menu`, `ordem`, `permite_parametros`, `manutencao_modulo`, `liberacao_em`, `data_criacao`) VALUES
(1, NULL, 'login', 'Entrar', 'fas fa-sign-in-alt', '/views/login.php', 'todos', 1, 0, 1, 0, 0, NULL, '2026-03-12 15:20:00'),
(2, NULL, 'cadastro', 'Criar Conta', 'fas fa-user-plus', '/views/cadastro.php', 'todos', 1, 0, 2, 0, 0, NULL, '2026-03-12 15:20:00'),
(3, NULL, 'manutencao', 'Manutenção', 'fas fa-tools', '/views/manutencao.php', 'todos', 1, 0, 3, 0, 0, NULL, '2026-03-12 15:20:00'),
(4, NULL, 'feed', 'Início', 'fas fa-home', '/views/feed.php', 'logado', 1, 1, 1, 0, 0, NULL, '2026-03-12 15:20:00'),
(5, NULL, 'perfil', 'Meu Perfil', 'fas fa-user', '/views/perfil.php', 'logado', 1, 1, 20, 0, 0, NULL, '2026-03-12 15:20:00'),
(6, NULL, 'chat', 'Chat', 'fas fa-comments', '/views/chat/home.php', 'logado', 1, 1, 30, 1, 0, NULL, '2026-03-12 15:20:00'),
(7, NULL, 'salvos', 'Salvos', 'fas fa-bookmark', '/views/salvos/home.php', 'logado', 1, 1, 40, 0, 0, NULL, '2026-03-12 15:20:00'),
(8, NULL, 'grupos', 'Grupos', 'fas fa-users', '/views/grupos/home.php', 'logado', 1, 1, 50, 0, 0, NULL, '2026-03-12 15:20:00'),
(9, NULL, 'marketplace', 'Marketplace', 'fas fa-store', '/views/marketplace/feed.php', 'logado', 1, 1, 60, 0, 1, NULL, '2026-03-12 15:20:00'),
(10, NULL, 'suporte', 'Suporte', 'fas fa-headset', '/views/suporte.php', 'logado', 1, 1, 100, 0, 0, '2026-03-13 18:16:00', '2026-03-12 15:20:00'),
(11, NULL, 'postagem', 'Ver Postagem', 'fas fa-eye', '/views/postagem.php', 'logado', 1, 0, 0, 1, 0, NULL, '2026-03-12 15:20:00'),
(12, NULL, 'pesquisa', 'Pesquisar', 'fas fa-search', '/views/busca/pesquisa.php', 'logado', 1, 0, 0, 1, 0, NULL, '2026-03-12 15:20:00'),
(13, NULL, 'historico_notificacoes', 'Notificações', 'fas fa-bell', '/views/notificacoes/historico_notificacoes.php', 'logado', 1, 0, 41, 0, 0, NULL, '2026-03-12 15:20:00'),
(14, NULL, 'marketplace/vender', 'Vender Item', 'fas fa-plus', '/views/marketplace/criar.php', 'logado', 1, 0, 0, 0, 0, NULL, '2026-03-12 15:20:00'),
(15, NULL, 'marketplace/meus-anuncios', 'Meus Anúncios', 'fas fa-list', '/views/marketplace/meus_anuncios.php', 'logado', 1, 0, 0, 1, 0, NULL, '2026-03-12 15:20:00'),
(16, NULL, 'marketplace/item', 'Detalhes do Produto', 'fas fa-tag', '/views/marketplace/detalhes.php', 'logado', 1, 0, 0, 1, 0, NULL, '2026-03-12 15:20:00'),
(17, NULL, 'marketplace/editar', 'Editar Anúncio', 'fas fa-edit', '/views/marketplace/editar.php', 'logado', 1, 0, 0, 1, 0, NULL, '2026-03-12 15:20:00'),
(18, NULL, 'grupos/criar', 'Criar Grupo', 'fas fa-plus-circle', '/views/grupos/criar.php', 'logado', 1, 0, 0, 0, 0, NULL, '2026-03-12 15:20:00'),
(19, NULL, 'grupos/ver', 'Ver Grupo', 'fas fa-users-cog', '/views/grupos/ver.php', 'logado', 1, 0, 0, 1, 0, NULL, '2026-03-12 15:20:00'),
(20, NULL, 'grupos/solicitacoes', 'Solicitações', 'fas fa-user-check', '/views/grupos/solicitacoes.php', 'logado', 1, 0, 0, 0, 0, NULL, '2026-03-12 15:20:00'),
(21, NULL, 'grupos/membros', 'Membros do Grupo', 'fas fa-user-friends', '/views/grupos/membros.php', 'logado', 1, 0, 0, 0, 0, NULL, '2026-03-12 15:20:00'),
(22, NULL, 'grupos/configurar', 'Configurar Grupo', 'fas fa-cog', '/views/grupos/configurar.php', 'logado', 1, 0, 0, 0, 0, NULL, '2026-03-12 15:20:00'),
(23, NULL, 'configurar_perfil', 'Configurações Gerais', 'fas fa-user-cog', '/views/configurar_perfil.php', 'logado', 1, 1, 90, 0, 0, NULL, '2026-03-12 15:20:00'),
(24, 23, 'gerenciar_bloqueios', 'Bloqueios', 'fas fa-user-slash', '/views/gerenciar_bloqueios.php', 'logado', 1, 1, 20, 0, 0, NULL, '2026-03-12 15:20:00'),
(25, NULL, 'explorar', 'Explorar', 'fas fa-compass', '/views/explorar.php', 'logado', 0, 1, 70, 0, 0, NULL, '2026-03-12 15:20:00'),
(26, NULL, 'admin', 'Painel Admin', 'fas fa-shield-alt', '/public_html/admin/roteador.php', 'admin', 1, 1, 110, 1, 0, NULL, '2026-03-12 15:20:00'),
(28, NULL, 'teste', 'Teste de Erro', 'fas fa-icons', '/views/teste_erro.php', 'todos', 1, 0, 0, 0, 0, NULL, '2026-03-18 21:08:46'),
(29, NULL, 'verificar-email', 'Verificar E-mail', 'fas fa-user-check', '/views/verificar_email.php', 'todos', 1, 0, 0, 0, 0, NULL, '2026-03-21 16:35:48');

-- --------------------------------------------------------

--
-- Estrutura para tabela `notificacoes`
--

CREATE TABLE `notificacoes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `remetente_id` int(11) NOT NULL,
  `tipo` varchar(30) NOT NULL,
  `id_referencia` int(11) NOT NULL,
  `lida` tinyint(1) NOT NULL DEFAULT 0,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `notificacoes`
--

INSERT INTO `notificacoes` (`id`, `usuario_id`, `remetente_id`, `tipo`, `id_referencia`, `lida`, `data_criacao`) VALUES
(1, 5, 9, 'curtida_post', 3, 1, '2025-10-12 23:01:54'),
(2, 5, 9, 'curtida_post', 5, 1, '2025-10-12 23:02:23'),
(3, 9, 5, 'curtida_post', 6, 1, '2025-10-12 23:47:42'),
(4, 5, 9, 'curtida_post', 3, 1, '2025-10-12 23:57:38'),
(5, 5, 9, 'curtida_post', 5, 1, '2025-10-12 23:58:06'),
(6, 5, 12, 'curtida_post', 5, 1, '2025-10-13 01:44:35'),
(7, 5, 12, 'comentario_post', 5, 1, '2025-10-13 01:44:39'),
(8, 9, 12, 'curtida_post', 6, 0, '2025-10-13 01:45:30'),
(9, 5, 12, 'curtida_post', 3, 1, '2025-10-13 01:45:33'),
(10, 4, 12, 'curtida_post', 2, 0, '2025-10-13 01:45:34'),
(11, 5, 12, 'comentario_post', 3, 1, '2025-10-13 01:45:39'),
(12, 4, 12, 'comentario_post', 2, 0, '2025-10-13 01:45:51'),
(13, 12, 5, 'curtida_post', 7, 1, '2025-10-13 01:46:51'),
(14, 12, 5, 'curtida_post', 7, 1, '2025-10-14 14:24:38'),
(15, 12, 5, 'curtida_post', 7, 1, '2025-10-14 14:24:50'),
(16, 12, 5, 'curtida_post', 7, 1, '2025-10-14 14:29:23'),
(17, 12, 5, 'curtida_post', 7, 1, '2025-10-14 14:32:43'),
(18, 12, 5, 'curtida_post', 7, 1, '2025-10-14 14:58:38'),
(19, 12, 5, 'curtida_post', 7, 1, '2025-10-14 14:58:55'),
(20, 12, 5, 'comentario_post', 7, 1, '2025-10-14 14:58:59'),
(21, 5, 12, 'curtida_post', 5, 1, '2025-10-14 15:17:02'),
(22, 12, 5, 'curtida_post', 7, 1, '2025-10-14 15:42:50'),
(23, 5, 12, 'curtida_comentario', 7, 1, '2025-10-14 17:42:24'),
(24, 5, 12, 'curtida_comentario', 7, 1, '2025-10-14 17:46:19'),
(25, 5, 12, 'curtida_comentario', 7, 1, '2025-10-14 17:46:44'),
(26, 5, 12, 'curtida_comentario', 7, 1, '2025-10-14 17:52:50'),
(27, 5, 12, 'curtida_comentario', 7, 1, '2025-10-14 18:17:03'),
(28, 12, 5, 'curtida_post', 7, 1, '2025-10-14 18:22:37'),
(29, 12, 5, 'curtida_post', 7, 1, '2025-10-14 22:56:16'),
(30, 5, 12, 'curtida_post', 5, 1, '2025-10-14 22:57:23'),
(31, 5, 12, 'curtida_post', 3, 1, '2025-10-14 22:57:25'),
(32, 5, 12, 'curtida_comentario', 2, 1, '2025-10-14 22:57:32'),
(33, 12, 5, 'curtida_comentario', 5, 1, '2025-10-14 22:58:12'),
(34, 12, 5, 'curtida_post', 8, 1, '2025-10-14 23:25:44'),
(35, 12, 5, 'comentario_post', 7, 1, '2025-10-15 14:13:57'),
(36, 12, 5, 'comentario_post', 8, 1, '2025-10-15 14:19:10'),
(37, 12, 5, 'pedido_amizade', 5, 1, '2025-10-15 17:50:32'),
(38, 5, 12, 'pedido_amizade', 12, 1, '2025-10-15 17:51:41'),
(39, 12, 5, 'pedido_amizade', 5, 1, '2025-10-15 17:52:13'),
(40, 12, 5, 'pedido_amizade', 5, 1, '2025-10-15 17:53:16'),
(41, 5, 12, 'pedido_amizade', 12, 1, '2025-10-15 17:53:28'),
(42, 12, 5, 'pedido_amizade', 5, 1, '2025-10-15 18:07:50'),
(43, 12, 5, 'pedido_amizade', 5, 1, '2025-10-15 18:08:03'),
(44, 9, 5, 'pedido_amizade', 5, 0, '2025-10-15 19:04:00'),
(45, 4, 5, 'pedido_amizade', 5, 0, '2025-10-15 19:04:12'),
(46, 5, 12, 'pedido_amizade', 12, 1, '2025-10-16 14:22:48'),
(47, 5, 12, 'pedido_amizade', 12, 1, '2025-10-16 14:30:15'),
(48, 5, 12, 'pedido_amizade', 12, 1, '2025-10-16 16:05:35'),
(49, 5, 12, 'pedido_amizade', 12, 1, '2025-10-16 16:05:52'),
(50, 5, 14, 'curtida_post', 15, 1, '2025-11-07 01:20:05'),
(51, 5, 14, 'comentario_post', 15, 1, '2025-11-07 01:20:18'),
(52, 5, 14, 'curtida_post', 13, 1, '2025-11-07 01:20:36'),
(53, 12, 14, 'curtida_post', 8, 1, '2025-11-07 01:22:47'),
(54, 12, 14, 'comentario_post', 8, 1, '2025-11-07 01:22:58'),
(55, 12, 14, 'curtida_post', 7, 1, '2025-11-07 01:23:27'),
(56, 9, 14, 'curtida_post', 6, 0, '2025-11-07 01:23:28'),
(57, 5, 14, 'curtida_post', 5, 1, '2025-11-07 01:23:29'),
(58, 5, 14, 'curtida_post', 3, 1, '2025-11-07 01:23:30'),
(59, 4, 14, 'curtida_post', 2, 0, '2025-11-07 01:23:32'),
(60, 12, 14, 'pedido_amizade', 14, 1, '2025-11-07 01:28:36'),
(61, 5, 14, 'pedido_amizade', 14, 1, '2025-11-07 01:29:08'),
(62, 14, 5, 'pedido_amizade', 5, 1, '2025-11-07 14:37:34'),
(63, 14, 5, 'pedido_amizade', 5, 1, '2025-11-07 15:10:29'),
(64, 5, 14, '', 14, 1, '2025-11-07 15:10:40'),
(65, 14, 5, 'pedido_amizade', 5, 1, '2025-11-07 15:32:06'),
(66, 5, 14, '', 14, 1, '2025-11-07 15:32:23'),
(67, 5, 14, '', 14, 1, '2025-11-07 15:39:21'),
(68, 5, 14, '', 14, 1, '2025-11-07 15:42:44'),
(69, 5, 14, 'amizade_aceita', 14, 1, '2025-11-07 15:49:08'),
(70, 5, 14, 'pedido_amizade', 14, 1, '2025-11-07 17:44:56'),
(71, 14, 5, 'amizade_aceita', 5, 1, '2025-11-07 17:45:33'),
(72, 5, 14, 'curtida_post', 15, 1, '2025-11-07 18:41:23'),
(73, 5, 14, 'curtida_post', 15, 1, '2025-11-07 21:02:22'),
(74, 5, 14, 'pedido_amizade', 14, 1, '2025-11-07 22:03:18'),
(75, 14, 5, 'amizade_aceita', 5, 0, '2025-11-07 22:03:57'),
(76, 5, 14, 'pedido_amizade', 14, 1, '2025-11-08 16:34:47'),
(77, 14, 5, 'amizade_aceita', 5, 0, '2025-11-08 16:35:04'),
(78, 14, 5, 'curtida_post', 17, 0, '2025-11-09 13:39:11'),
(79, 14, 5, 'comentario_post', 16, 0, '2025-11-09 14:30:23'),
(80, 14, 12, 'amizade_aceita', 12, 0, '2025-11-10 15:03:12'),
(81, 14, 5, 'comentario_post', 16, 0, '2025-11-10 19:17:41'),
(82, 14, 12, 'comentario_post', 16, 0, '2025-11-10 21:09:07'),
(83, 14, 5, 'curtida_post', 16, 0, '2025-11-10 22:20:39'),
(84, 14, 5, 'comentario_post', 16, 0, '2025-11-10 23:08:47'),
(85, 14, 5, 'comentario_post', 16, 0, '2025-11-10 23:10:25'),
(86, 14, 5, 'amizade_aceita', 5, 1, '2025-11-13 18:35:01'),
(87, 5, 14, 'pedido_amizade', 14, 1, '2025-11-13 18:37:39'),
(88, 14, 5, 'amizade_aceita', 5, 0, '2025-11-13 18:37:52'),
(89, 5, 14, 'pedido_amizade', 14, 1, '2025-11-13 21:13:37'),
(90, 14, 5, 'amizade_aceita', 5, 0, '2025-11-13 21:13:49'),
(91, 14, 12, 'pedido_amizade', 12, 0, '2025-11-15 17:15:16'),
(92, 12, 5, 'curtida_post', 40, 1, '2025-12-18 18:10:28'),
(93, 5, 12, 'curtida_post', 39, 1, '2025-12-20 22:12:50'),
(94, 5, 12, 'curtida_post', 41, 1, '2025-12-20 22:12:52'),
(95, 5, 12, 'curtida_post', 36, 1, '2025-12-20 22:12:55'),
(96, 5, 13, 'curtida_comentario', 2, 1, '2025-12-21 02:27:17'),
(97, 12, 13, 'curtida_comentario', 2, 0, '2025-12-21 02:27:18'),
(98, 4, 13, 'curtida_post', 2, 0, '2025-12-21 02:27:31'),
(99, 4, 13, 'pedido_amizade', 13, 0, '2025-12-21 02:27:33'),
(100, 12, 13, 'curtida_post', 40, 0, '2025-12-21 02:43:02'),
(101, 12, 13, 'comentario_post', 40, 0, '2025-12-21 02:43:10'),
(102, 5, 13, 'pedido_amizade', 13, 1, '2025-12-21 02:43:29'),
(103, 13, 5, 'amizade_aceita', 5, 1, '2025-12-21 02:44:03'),
(104, 5, 12, '', 44, 1, '2025-12-21 22:11:36'),
(105, 5, 13, '', 44, 1, '2025-12-21 22:14:20'),
(106, 5, 13, '', 46, 1, '2025-12-21 22:15:20'),
(107, 5, 13, 'voto_enquete', 46, 1, '2025-12-21 22:23:39'),
(108, 5, 13, 'compartilhamento_post', 47, 1, '2025-12-21 22:34:19'),
(109, 5, 13, 'compartilhamento_post', 48, 1, '2025-12-21 22:39:45'),
(110, 5, 13, 'compartilhamento_post', 50, 1, '2025-12-21 22:51:28'),
(111, 5, 13, 'compartilhamento_post', 51, 1, '2025-12-21 23:09:30'),
(112, 5, 13, 'comentario_post', 52, 1, '2025-12-25 19:31:51'),
(113, 5, 13, 'curtida_post', 55, 0, '2025-12-28 17:35:48'),
(114, 5, 13, 'curtida_post', 54, 1, '2025-12-28 17:39:11'),
(115, 5, 13, 'interesse_mkt', 53, 1, '2025-12-28 18:08:10'),
(117, 5, 13, 'curtida_post', 61, 0, '2026-01-04 16:13:10'),
(118, 5, 13, 'curtida_post', 57, 0, '2026-01-04 16:13:13'),
(119, 5, 13, 'curtida_post', 56, 0, '2026-01-04 16:13:14'),
(120, 5, 13, 'curtida_post', 54, 0, '2026-01-04 16:13:19'),
(121, 5, 12, 'interesse_mkt', 8, 0, '2026-01-05 14:52:50'),
(122, 5, 12, 'compartilhamento_post', 62, 0, '2026-01-05 14:54:10'),
(123, 5, 15, 'curtida_post', 53, 0, '2026-01-07 02:49:27'),
(124, 5, 15, 'curtida_post', 54, 0, '2026-01-07 02:49:32'),
(125, 13, 15, 'curtida_post', 51, 1, '2026-01-07 02:49:34'),
(126, 13, 15, 'curtida_post', 50, 1, '2026-01-07 02:49:36'),
(127, 13, 15, 'curtida_post', 48, 1, '2026-01-07 02:49:38'),
(128, 5, 13, 'comentario_post', 64, 0, '2026-01-07 19:04:00'),
(129, 5, 13, 'comentario_post', 64, 0, '2026-01-07 19:04:07'),
(130, 5, 13, 'curtida_post', 64, 0, '2026-01-07 19:04:14'),
(131, 5, 13, 'compartilhamento_post', 67, 0, '2026-01-07 19:04:26'),
(132, 13, 5, 'comentario_post', 68, 1, '2026-01-08 00:46:35'),
(133, 5, 13, 'curtida_comentario', 68, 0, '2026-01-08 02:09:09'),
(134, 15, 5, 'curtida_post', 66, 1, '2026-01-08 22:12:54'),
(135, 13, 5, 'curtida_post', 65, 0, '2026-01-08 22:12:57'),
(136, 12, 5, 'curtida_post', 62, 0, '2026-01-13 19:32:59'),
(137, 12, 5, 'curtida_post', 40, 0, '2026-01-13 19:33:02'),
(138, 13, 5, 'curtida_post', 67, 0, '2026-01-13 19:33:19'),
(139, 5, 13, 'comentario_post', 71, 0, '2026-01-14 01:29:29'),
(142, 13, 5, 'convite_grupo', 2, 1, '2026-01-18 23:38:34'),
(143, 13, 5, 'convite_grupo', 2, 1, '2026-01-18 23:45:14'),
(146, 13, 5, 'curtida_post', 80, 0, '2026-01-31 18:00:33'),
(147, 13, 5, 'curtida', 80, 0, '2026-01-31 18:04:54'),
(148, 13, 5, 'curtida', 80, 0, '2026-01-31 18:22:34'),
(149, 13, 12, 'curtida', 80, 0, '2026-01-31 20:48:09'),
(150, 13, 12, 'curtida', 80, 0, '2026-01-31 20:54:03'),
(151, 13, 12, 'curtida', 80, 0, '2026-01-31 20:54:23'),
(152, 13, 12, 'curtida', 80, 0, '2026-01-31 20:54:52'),
(153, 13, 12, 'comentario_post', 80, 0, '2026-01-31 20:54:57'),
(154, 13, 12, 'curtida', 80, 0, '2026-01-31 20:56:09'),
(155, 12, 13, 'curtida', 81, 0, '2026-01-31 21:10:01'),
(156, 12, 13, 'curtida', 81, 0, '2026-01-31 21:10:19'),
(157, 12, 13, 'curtida', 81, 0, '2026-01-31 21:10:33'),
(158, 12, 13, 'comentario_post', 81, 0, '2026-01-31 21:10:53'),
(159, 13, 12, 'curtida', 80, 0, '2026-01-31 21:24:14'),
(160, 12, 13, 'comentario', 81, 0, '2026-01-31 21:37:12'),
(161, 13, 12, 'comentario', 80, 0, '2026-01-31 21:38:07'),
(162, 12, 13, 'curtida', 80, 0, '2026-01-31 21:38:31'),
(163, 12, 5, 'comentario', 81, 0, '2026-02-01 13:17:10'),
(164, 5, 15, 'curtida_comentario', 81, 0, '2026-02-01 13:17:33'),
(165, 5, 13, 'curtida', 79, 0, '2026-02-01 14:34:28'),
(166, 13, 5, 'curtida', 82, 0, '2026-02-01 15:37:23'),
(167, 13, 5, 'comentario', 82, 0, '2026-02-01 15:37:35'),
(168, 13, 5, 'compartilhamento', 83, 0, '2026-02-01 15:37:53'),
(169, 13, 5, 'compartilhar', 84, 0, '2026-02-01 15:40:46'),
(170, 13, 5, 'mensagem', 1, 0, '2026-02-01 16:48:22'),
(171, 13, 5, 'mensagem', 1, 0, '2026-02-01 16:48:28'),
(172, 13, 5, 'mensagem', 1, 0, '2026-02-01 16:48:28'),
(173, 13, 5, 'mensagem', 1, 0, '2026-02-01 16:48:29'),
(174, 13, 5, 'mensagem', 1, 0, '2026-02-01 16:48:30'),
(175, 5, 13, 'mensagem', 1, 0, '2026-02-01 16:49:31'),
(176, 13, 5, 'convite_chat_grupo', 11, 0, '2026-02-01 17:08:04'),
(177, 13, 5, 'aceite_convite_grupo', 5, 0, '2026-02-02 15:09:45'),
(178, 5, 13, 'transferencia_dono', 5, 0, '2026-02-02 15:10:41'),
(179, 13, 5, 'rebaixamento_membro', 5, 0, '2026-02-02 15:13:43'),
(180, 13, 5, 'transferencia_dono', 5, 0, '2026-02-02 15:13:56'),
(181, 5, 13, 'expulsao_grupo', 5, 0, '2026-02-02 15:14:25'),
(184, 13, 5, 'mensagem', 11, 0, '2026-02-03 12:26:13'),
(185, 5, 13, 'curtida', 85, 0, '2026-02-03 15:24:39'),
(186, 5, 13, 'comentario', 85, 0, '2026-02-03 15:25:04'),
(187, 5, 13, 'mensagem', 1, 0, '2026-02-03 15:25:43'),
(188, 5, 13, 'mensagem', 1, 0, '2026-02-03 15:26:14'),
(189, 12, 5, 'transferencia_dono', 2, 0, '2026-02-03 20:45:00'),
(190, 13, 5, 'mensagem', 1, 0, '2026-02-03 21:44:26'),
(191, 13, 5, 'mensagem', 11, 0, '2026-02-04 14:50:55'),
(192, 5, 15, 'curtida', 56, 0, '2026-02-05 00:37:35'),
(193, 5, 15, 'curtida', 55, 0, '2026-02-05 00:37:36'),
(194, 5, 15, 'curtida', 61, 0, '2026-02-05 00:37:38'),
(195, 5, 15, 'curtida', 54, 0, '2026-02-05 00:37:40'),
(196, 5, 15, 'curtida', 53, 0, '2026-02-05 00:37:45'),
(197, 4, 15, 'mensagem', 6, 0, '2026-02-05 00:47:40'),
(198, 13, 5, 'pedido_amizade', 5, 0, '2026-02-22 18:38:08'),
(199, 13, 5, 'pedido_amizade', 5, 0, '2026-02-22 21:49:57'),
(200, 13, 5, 'pedido_amizade', 5, 1, '2026-02-23 11:19:15'),
(201, 5, 13, 'amizade_aceita', 13, 0, '2026-02-23 15:27:57'),
(202, 5, 13, 'comentario', 41, 0, '2026-02-23 21:17:08'),
(203, 13, 15, 'comentario', 92, 0, '2026-02-23 21:32:47'),
(204, 14, 13, 'pedido_amizade', 13, 0, '2026-02-25 21:04:30'),
(205, 11, 15, 'pedido_amizade', 15, 0, '2026-03-01 17:03:55'),
(206, 11, 15, 'mensagem', 12, 0, '2026-03-01 17:04:27'),
(207, 5, 13, 'mensagem', 1, 1, '2026-03-02 15:39:09'),
(208, 15, 5, 'mensagem', 5, 1, '2026-03-11 14:54:35'),
(209, 15, 5, 'curtida', 95, 1, '2026-03-11 14:55:07'),
(210, 15, 5, 'mensagem', 5, 1, '2026-03-11 15:05:27'),
(211, 15, 5, 'mensagem', 5, 1, '2026-03-11 15:09:25'),
(212, 15, 5, 'mensagem', 5, 1, '2026-03-11 15:12:34'),
(213, 15, 5, 'mensagem', 5, 1, '2026-03-11 15:13:48'),
(214, 15, 5, 'mensagem', 5, 1, '2026-03-11 17:39:24'),
(215, 5, 15, 'convite_chat_grupo', 13, 1, '2026-03-23 21:27:25'),
(216, 13, 15, 'comentario', 92, 0, '2026-03-24 16:05:32'),
(217, 12, 15, 'mensagem', 7, 0, '2026-03-28 14:16:23'),
(218, 4, 15, 'mensagem', 6, 0, '2026-03-28 14:17:11'),
(219, 4, 15, 'mensagem', 6, 0, '2026-03-28 14:18:03'),
(220, 4, 15, 'mensagem', 6, 0, '2026-03-28 14:28:20'),
(221, 5, 15, 'curtida_post', 101, 0, '2026-03-30 18:37:27'),
(222, 5, 15, 'pedido_amizade', 15, 1, '2026-03-30 20:03:42'),
(223, 15, 5, 'amizade_aceita', 5, 0, '2026-03-30 20:05:20'),
(224, 5, 15, 'compartilhamento_post', 104, 0, '2026-03-30 20:09:46'),
(225, 5, 15, 'compartilhamento_post', 105, 0, '2026-03-30 20:16:06'),
(226, 5, 15, 'compartilhamento_post', 106, 0, '2026-03-30 20:25:46'),
(227, 5, 15, 'comentario_post', 85, 0, '2026-03-30 20:38:21'),
(228, 15, 5, 'comentario_post', 106, 1, '2026-03-30 20:38:53'),
(229, 5, 15, 'curtida_comentario', 106, 0, '2026-03-30 20:45:18'),
(230, 5, 15, 'mensagem', 5, 0, '2026-03-30 20:49:01'),
(231, 5, 15, 'curtida_post', 103, 0, '2026-03-30 20:49:31'),
(234, 15, 5, 'solicitacao_grupo', 8, 0, '2026-03-30 21:00:42'),
(235, 5, 15, 'aceite_solicitacao_grupo', 8, 0, '2026-03-30 21:08:03'),
(236, 5, 15, 'promocao_moderador', 8, 0, '2026-03-30 21:08:19'),
(237, 5, 15, 'curtida', 103, 0, '2026-03-30 21:11:32'),
(238, 5, 15, 'voto_enquete', 107, 0, '2026-03-30 21:13:53'),
(239, 5, 15, 'curtida_post', 107, 0, '2026-03-30 23:58:17'),
(240, 5, 15, 'convite_chat_grupo', 14, 0, '2026-03-31 00:06:08'),
(241, 5, 15, 'comentario_post', 107, 0, '2026-04-01 16:56:05'),
(242, 5, 15, 'comentario_post', 107, 0, '2026-04-01 17:09:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Palavras_Proibidas`
--

CREATE TABLE `Palavras_Proibidas` (
  `id` int(11) NOT NULL,
  `termo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `Palavras_Proibidas`
--

INSERT INTO `Palavras_Proibidas` (`id`, `termo`) VALUES
(40, 'anus'),
(21, 'babaca'),
(32, 'bicha'),
(35, 'boiola'),
(41, 'boquete'),
(10, 'bosta'),
(12, 'buceta'),
(13, 'bvceta'),
(39, 'c#'),
(50, 'c4r4lh0'),
(11, 'cacete'),
(5, 'caralho'),
(38, 'cu'),
(28, 'desgraça'),
(20, 'escroto'),
(3, 'fdp'),
(4, 'filho da puta'),
(18, 'foda'),
(17, 'foder'),
(19, 'fodido'),
(29, 'maldito'),
(9, 'merda'),
(37, 'mongoloide'),
(46, 'orgia'),
(23, 'otaria'),
(22, 'otario'),
(8, 'p0rra'),
(14, 'pica'),
(16, 'pinto'),
(27, 'piranha'),
(7, 'porra'),
(51, 'pqp'),
(42, 'punheta'),
(1, 'puta'),
(52, 'putaquepariu'),
(2, 'putaria'),
(49, 'pvt4'),
(24, 'quenga'),
(25, 'rapariga'),
(36, 'retardado'),
(15, 'rola'),
(33, 'sapatao'),
(43, 'siririca'),
(45, 'suruba'),
(34, 'traveco'),
(44, 'trepar'),
(31, 'v1ado'),
(26, 'vagabunda'),
(30, 'viado'),
(47, 'xereca'),
(48, 'xota');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Postagens`
--

CREATE TABLE `Postagens` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `post_original_id` int(11) DEFAULT NULL,
  `conteudo_texto` text NOT NULL,
  `tipo_media` enum('imagem','video') DEFAULT NULL,
  `privacidade` enum('publico','amigos') NOT NULL DEFAULT 'publico',
  `status` enum('ativo','inativo','excluido_pelo_usuario') NOT NULL DEFAULT 'ativo',
  `contador_compartilhamentos` int(11) NOT NULL DEFAULT 0,
  `url_media` varchar(255) DEFAULT NULL,
  `data_postagem` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipo_post` enum('padrao','venda') DEFAULT 'padrao',
  `id_grupo` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Postagens`
--

INSERT INTO `Postagens` (`id`, `id_usuario`, `post_original_id`, `conteudo_texto`, `tipo_media`, `privacidade`, `status`, `contador_compartilhamentos`, `url_media`, `data_postagem`, `tipo_post`, `id_grupo`) VALUES
(2, 4, NULL, 'teste', NULL, 'publico', 'ativo', 0, NULL, '2025-10-01 17:11:08', 'padrao', NULL),
(3, 5, NULL, 'Olá pessoas tudo bom????', NULL, 'amigos', 'ativo', 0, NULL, '2025-10-01 20:21:21', 'padrao', NULL),
(4, 5, NULL, 'teste', NULL, 'amigos', 'excluido_pelo_usuario', 0, NULL, '2025-10-06 21:27:13', 'padrao', NULL),
(5, 5, NULL, 'teste 1', NULL, 'amigos', 'ativo', 0, NULL, '2025-10-12 23:02:07', 'padrao', NULL),
(6, 9, NULL, 'TESSSSTE', NULL, 'publico', 'ativo', 0, NULL, '2025-10-12 23:47:36', 'padrao', NULL),
(7, 12, NULL, 'Ola pessoas', NULL, 'publico', 'ativo', 0, NULL, '2025-10-13 01:45:25', 'padrao', NULL),
(8, 12, NULL, 'Diego reis', NULL, 'publico', 'ativo', 0, NULL, '2025-10-14 22:57:12', 'padrao', NULL),
(9, 5, NULL, 'teste', NULL, 'amigos', 'excluido_pelo_usuario', 0, NULL, '2025-10-16 16:33:43', 'padrao', NULL),
(10, 5, NULL, 'teste', NULL, 'amigos', 'ativo', 0, 'uploads/posts/post_5_1760633778.jpeg', '2025-10-16 16:56:18', 'padrao', NULL),
(11, 5, NULL, 'Olaaaw', NULL, 'amigos', 'excluido_pelo_usuario', 0, 'uploads/posts/post_5_1760646734.jpg', '2025-10-16 20:32:14', 'padrao', NULL),
(12, 5, NULL, 'Kkkkkkk', NULL, 'amigos', 'excluido_pelo_usuario', 0, 'uploads/posts/post_5_1760649510.jpg', '2025-10-16 21:18:30', 'padrao', NULL),
(13, 5, NULL, 'IMAGEM TESTE 17/10', 'imagem', 'amigos', 'ativo', 0, 'uploads/posts/post_5_1760715213.jpeg', '2025-10-17 15:33:33', 'padrao', NULL),
(14, 5, NULL, 'video testee', 'video', 'amigos', 'excluido_pelo_usuario', 0, 'uploads/posts/post_5_1760716955.mp4', '2025-10-17 16:02:37', 'padrao', NULL),
(15, 5, NULL, 'Pokemon', 'video', 'amigos', 'ativo', 1, 'uploads/posts/post_5_1760811037.mp4', '2025-10-18 18:10:37', 'padrao', NULL),
(16, 14, NULL, 'Eaer', 'imagem', 'amigos', 'ativo', 2, 'uploads/posts/post_14_1762478865.jpg', '2025-11-07 01:27:45', 'padrao', NULL),
(17, 14, NULL, 'Teste', NULL, 'amigos', 'ativo', 0, NULL, '2025-11-08 00:23:48', 'padrao', NULL),
(28, 14, 15, '', NULL, 'publico', 'ativo', 0, NULL, '2025-11-08 16:35:34', 'padrao', NULL),
(31, 5, 16, 'Teste', NULL, 'amigos', 'ativo', 0, NULL, '2025-11-09 02:19:12', 'padrao', NULL),
(33, 5, 16, 'Kkkkk', NULL, 'amigos', 'excluido_pelo_usuario', 0, NULL, '2025-11-10 00:05:06', 'padrao', NULL),
(34, 5, NULL, 'Lorem ipsum dolor sit amet. Non quaerat placeat aut voluptatem dolorem non inventore velit et voluptatum eligendi sed aliquam autem. Ab rerum excepturi sed veritatis sapiente aut reiciendis quia et odit perspiciatis quo asperiores officiis. Aut vitae beatae ab suscipit consequatur non laborum veritatis aut commodi repudiandae qui voluptatum incidunt et sapiente earum eum autem quas! \r\n\r\nUt voluptas doloremque qui minima velit rem omnis distinctio. Ad temporibus odit est nulla blanditiis sed itaque dignissimos qui accusamus dolor et mollitia pariatur in quos voluptas aut illum quod. \r\n\r\nSed labore internos sit sequi omnis nam unde accusantium et magnam modi eum quia illo ut ipsa voluptas sed omnis asperiores. Id nobis voluptatem sit velit quisquam sed Quis cumque ex totam officia. Cum dignissimos dolores ea necessitatibus cupiditate ut alias accusantium eos alias harum? Et dicta unde est harum aperiam ut expedita ipsa et facilis voluptas sed sapiente earum. TESTE 2', NULL, 'amigos', 'ativo', 2, NULL, '2025-11-10 01:32:02', 'padrao', NULL),
(35, 5, NULL, 'Uma mudança na política da privacidade da Meta vai permitir que a big tech use conversas dos usuários com a Meta Intelligence (a inteligência artificial da empresa) para direcionar anúncios personalizados no Instagram e no Facebook.\r\n\r\nAinda, a empresa vai começar a usar dados públicos e conversas de usuários do Threads para treinar a IA.\r\n\r\nQuer saber como bloquear o uso de suas informações públicas para treinar a IA? Acesse nossa matéria e veja o passo a passo. Link nos comentários.', 'imagem', 'amigos', 'ativo', 0, 'uploads/posts/post_5_1762823113.jpg', '2025-11-11 01:05:13', 'padrao', NULL),
(36, 5, 34, 'teste', NULL, 'amigos', 'ativo', 0, NULL, '2025-11-12 15:11:57', 'padrao', NULL),
(37, 5, NULL, 'Tttt', 'imagem', 'amigos', 'excluido_pelo_usuario', 0, 'uploads/posts/post_5_1763592937.jpg', '2025-11-19 22:55:37', 'padrao', NULL),
(38, 5, NULL, 'Gshs', 'imagem', 'amigos', 'excluido_pelo_usuario', 0, 'uploads/posts/post_5_1763594631.webp', '2025-11-19 23:23:51', 'padrao', NULL),
(39, 5, NULL, '', NULL, 'amigos', 'ativo', 0, NULL, '2025-11-20 01:42:20', 'padrao', NULL),
(40, 12, NULL, '', NULL, 'publico', 'ativo', 0, NULL, '2025-12-18 17:11:43', 'padrao', NULL),
(41, 5, 34, 'Lorem ipsum dolor sit amet. Non quaerat placeat aut voluptatem dolorem non inventore velit et voluptatum eligendi sed aliquam autem. Ab rerum excepturi sed veritatis sapiente aut reiciendis quia et odit perspiciatis quo asperiores officiis. Aut vitae beatae ab suscipit consequatur non laborum veritatis aut commodi repudiandae qui voluptatum incidunt et sapiente earum eum autem quas! \r\n\r\nUt voluptas doloremque qui minima velit rem omnis distinctio. Ad temporibus odit est nulla blanditiis sed itaque dignissimos qui accusamus dolor et mollitia pariatur in quos voluptas aut illum quod. \r\n\r\nSed labore internos sit sequi omnis nam unde accusantium et magnam modi eum quia illo ut ipsa voluptas sed omnis asperiores. Id nobis voluptatem sit velit quisquam sed Quis cumque ex totam officia. Cum dignissimos dolores ea necessitatibus cupiditate ut alias accusantium eos alias harum? Et dicta unde est harum aperiam ut expedita ipsa et facilis voluptas sed sapiente earum. TESTE 2', NULL, 'amigos', 'ativo', 0, NULL, '2025-12-18 20:32:02', 'padrao', NULL),
(42, 13, NULL, 'Porque não?', NULL, 'publico', 'ativo', 0, NULL, '2025-12-21 02:42:43', 'padrao', NULL),
(43, 5, NULL, 'esse é um site:\r\nhttps://www.google.com/', NULL, 'amigos', 'ativo', 0, NULL, '2025-12-21 17:19:04', 'padrao', NULL),
(44, 5, NULL, '', NULL, 'amigos', 'ativo', 1, NULL, '2025-12-21 18:05:51', 'padrao', NULL),
(45, 5, NULL, 'https://santistas.net/noticias-do-santos/santos-fc-rafa-21-12/', NULL, 'amigos', 'inativo', 2, NULL, '2025-12-21 18:55:05', 'padrao', NULL),
(46, 5, NULL, 'https://www1.folha.uol.com.br/mercado/2025/12/inteligencia-artificial-cria-negocio-imobiliario-bilionario-no-brasil-com-data-centers.shtml', NULL, 'amigos', 'ativo', 1, NULL, '2025-12-21 20:41:02', 'padrao', NULL),
(47, 13, 45, 'teste', NULL, 'publico', 'ativo', 0, NULL, '2025-12-21 22:34:19', 'padrao', NULL),
(48, 13, 44, '', NULL, 'publico', 'ativo', 0, NULL, '2025-12-21 22:39:45', 'padrao', NULL),
(49, 5, NULL, 'isso é outro teste\r\nhttps://jornalrazao.com/', NULL, 'amigos', 'excluido_pelo_usuario', 1, NULL, '2025-12-21 22:51:12', 'padrao', NULL),
(50, 13, 49, '', NULL, 'publico', 'ativo', 0, NULL, '2025-12-21 22:51:28', 'padrao', NULL),
(51, 13, 46, '', NULL, 'publico', 'ativo', 0, NULL, '2025-12-21 23:09:30', 'padrao', NULL),
(52, 5, NULL, '', NULL, 'amigos', 'ativo', 0, NULL, '2025-12-25 19:27:33', 'padrao', NULL),
(53, 5, NULL, 'Vendo este iPhone em ótimo estado!', NULL, 'publico', 'ativo', 0, NULL, '2025-12-25 23:17:44', 'venda', NULL),
(54, 5, NULL, 'Ventilador\n\nPonto blu', 'imagem', 'publico', 'ativo', 0, NULL, '2025-12-26 22:24:48', 'venda', NULL),
(55, 5, NULL, 'Mochila do Sonic\n\nMochila infantil', 'imagem', 'publico', 'ativo', 0, NULL, '2025-12-26 22:28:09', 'venda', NULL),
(56, 5, NULL, 'Produto Teste\n\nIsso é apenas um teste.', 'imagem', 'publico', 'ativo', 0, NULL, '2025-12-29 02:14:42', 'venda', NULL),
(57, 5, NULL, '', NULL, 'amigos', 'ativo', 0, NULL, '2025-12-29 16:00:44', 'padrao', NULL),
(61, 5, NULL, 'Venha anunciar aqui !!\n\nVenha anunciar seus produtos no nosso marketplace !!', 'imagem', 'publico', 'ativo', 1, NULL, '2026-01-03 22:16:08', 'venda', NULL),
(62, 12, 61, '', NULL, 'publico', 'ativo', 0, NULL, '2026-01-05 14:54:10', 'padrao', NULL),
(63, 5, 45, 'teste2', NULL, 'amigos', 'ativo', 0, NULL, '2026-01-06 16:52:38', 'padrao', NULL),
(64, 5, NULL, 'https://www.cnnbrasil.com.br/nacional/brasil/irmao-de-eliza-samudio-se-pronuncia-sobre-passaporte-encontrado-em-portugal/', NULL, 'amigos', 'excluido_pelo_usuario', 1, NULL, '2026-01-06 17:26:33', 'padrao', NULL),
(65, 13, NULL, 'Olá! Estou explorando o Social BR. Parece uma plataforma interessante para conectar pessoas. #Teste #Exploração', NULL, 'publico', 'ativo', 0, NULL, '2026-01-06 21:33:38', 'padrao', NULL),
(66, 15, NULL, 'Esta é uma postagem feita pelo novo perfil @usuario_teste_123! Explorando as funcionalidades do Social BR. #NovoPerfil #SocialBR #Teste', NULL, 'publico', 'ativo', 0, NULL, '2026-01-06 21:52:18', 'padrao', NULL),
(67, 13, 64, 'Vish', NULL, 'publico', 'ativo', 0, NULL, '2026-01-07 19:04:26', 'padrao', NULL),
(68, 13, NULL, '', NULL, 'publico', 'ativo', 0, NULL, '2026-01-07 19:05:01', 'padrao', NULL),
(69, 5, NULL, '', NULL, 'amigos', 'ativo', 0, NULL, '2026-01-09 03:19:40', 'padrao', NULL),
(70, 5, NULL, '', NULL, 'amigos', 'ativo', 0, NULL, '2026-01-09 17:15:11', 'padrao', NULL),
(71, 5, NULL, '', NULL, 'amigos', 'ativo', 0, NULL, '2026-01-09 17:15:26', 'padrao', NULL),
(72, 5, NULL, '', NULL, 'amigos', 'ativo', 0, NULL, '2026-01-09 17:17:01', 'padrao', NULL),
(73, 5, NULL, '', NULL, 'amigos', 'ativo', 0, NULL, '2026-01-09 21:25:31', 'padrao', NULL),
(74, 5, NULL, 'Teste', NULL, 'amigos', 'ativo', 0, NULL, '2026-01-12 00:21:58', 'padrao', NULL),
(75, 5, NULL, 'isso é um teste', NULL, 'publico', 'ativo', 0, NULL, '2026-01-18 16:34:09', 'padrao', 2),
(76, 5, NULL, 'Isso é outro teste', NULL, 'publico', 'ativo', 0, NULL, '2026-01-18 19:51:36', 'padrao', 2),
(77, 13, NULL, 'Teste', NULL, 'publico', 'ativo', 0, NULL, '2026-01-20 22:27:43', 'padrao', 4),
(78, 13, NULL, '', NULL, 'publico', 'ativo', 0, NULL, '2026-01-20 22:27:58', 'padrao', 4),
(79, 5, NULL, 'https://socialbr.lol/~klscom/tarefas/', NULL, 'amigos', 'ativo', 0, NULL, '2026-01-30 22:36:24', 'padrao', NULL),
(80, 13, NULL, 'teste', NULL, 'publico', 'ativo', 0, NULL, '2026-01-31 17:46:54', 'padrao', NULL),
(81, 12, NULL, 'outro teste', NULL, 'publico', 'ativo', 0, NULL, '2026-01-31 21:09:49', 'padrao', NULL),
(82, 13, NULL, 'teste', NULL, 'publico', 'ativo', 2, NULL, '2026-02-01 15:37:06', 'padrao', NULL),
(83, 5, 82, '', NULL, 'amigos', 'excluido_pelo_usuario', 0, NULL, '2026-02-01 15:37:53', 'padrao', NULL),
(84, 5, 82, '', NULL, 'amigos', 'ativo', 0, NULL, '2026-02-01 15:40:46', 'padrao', NULL),
(85, 5, NULL, 'teste', NULL, 'amigos', 'ativo', 0, NULL, '2026-02-03 14:40:04', 'padrao', NULL),
(86, 15, NULL, 'Oi', NULL, 'publico', 'ativo', 0, NULL, '2026-02-05 00:46:25', 'padrao', 6),
(87, 13, NULL, 'ola', NULL, 'publico', 'excluido_pelo_usuario', 0, NULL, '2026-02-13 13:03:50', 'padrao', NULL),
(88, 5, NULL, 'puta', NULL, 'amigos', 'excluido_pelo_usuario', 1, NULL, '2026-02-18 15:05:25', 'padrao', NULL),
(89, 5, 88, '', NULL, 'amigos', 'excluido_pelo_usuario', 0, NULL, '2026-02-18 15:10:38', 'padrao', NULL),
(90, 5, NULL, 'porra buceta caralho puta', NULL, 'amigos', 'excluido_pelo_usuario', 0, NULL, '2026-02-18 20:22:30', 'padrao', NULL),
(91, 5, NULL, 'porrabucetacaralhoputa', NULL, 'amigos', 'excluido_pelo_usuario', 0, NULL, '2026-02-18 20:22:43', 'padrao', NULL),
(92, 13, NULL, 'teste', NULL, 'publico', 'ativo', 0, NULL, '2026-02-23 15:48:32', 'padrao', NULL),
(93, 5, NULL, 'etsad\n\ndfafd', 'imagem', 'publico', 'ativo', 0, NULL, '2026-02-26 14:18:12', 'venda', NULL),
(94, 15, NULL, '', NULL, 'publico', 'ativo', 0, NULL, '2026-02-26 19:47:41', 'padrao', NULL),
(95, 15, NULL, 'teste 1', NULL, 'publico', 'ativo', 0, NULL, '2026-02-28 14:54:24', 'padrao', NULL),
(96, 15, NULL, 'Teste', NULL, 'publico', 'ativo', 0, NULL, '2026-03-01 17:08:31', 'padrao', 7),
(97, 13, NULL, '3 sonic', NULL, 'publico', 'ativo', 0, NULL, '2026-03-02 14:35:24', 'padrao', NULL),
(98, 13, NULL, 'puta', NULL, 'publico', 'ativo', 0, NULL, '2026-03-02 15:43:38', 'padrao', NULL),
(99, 15, NULL, 'teste', NULL, 'publico', 'ativo', 0, NULL, '2026-03-22 19:05:27', 'padrao', NULL),
(101, 5, NULL, 'teste', NULL, 'amigos', 'ativo', 3, NULL, '2026-03-26 17:08:00', 'padrao', NULL),
(102, 15, NULL, 'TESTE', NULL, 'publico', 'excluido_pelo_usuario', 0, NULL, '2026-03-26 21:28:28', 'padrao', NULL),
(103, 5, NULL, '🛒 ANÚNCIO: tigre\n\nesate', 'imagem', 'publico', 'ativo', 0, NULL, '2026-03-28 14:44:39', 'venda', NULL),
(104, 15, 101, '', NULL, 'publico', 'ativo', 0, NULL, '2026-03-30 20:09:46', 'padrao', NULL),
(105, 15, 101, '', NULL, 'publico', 'ativo', 0, NULL, '2026-03-30 20:16:06', 'padrao', NULL),
(106, 15, 101, '', NULL, 'publico', 'ativo', 0, NULL, '2026-03-30 20:25:46', 'padrao', NULL),
(107, 5, NULL, '', NULL, 'amigos', 'ativo', 0, NULL, '2026-03-30 21:13:36', 'padrao', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `Postagens_Edicoes`
--

CREATE TABLE `Postagens_Edicoes` (
  `id` int(11) NOT NULL,
  `id_postagem` int(11) NOT NULL,
  `conteudo_antigo` text NOT NULL,
  `data_edicao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Postagens_Edicoes`
--

INSERT INTO `Postagens_Edicoes` (`id`, `id_postagem`, `conteudo_antigo`, `data_edicao`) VALUES
(1, 3, 'Olá pessoas', '2025-10-06 21:20:02'),
(2, 3, 'Olá pessoas tudo bom?', '2025-10-06 21:25:53'),
(3, 5, 'teste', '2025-10-15 14:44:57'),
(4, 34, 'Lorem ipsum dolor sit amet. Non quaerat placeat aut voluptatem dolorem non inventore velit et voluptatum eligendi sed aliquam autem. Ab rerum excepturi sed veritatis sapiente aut reiciendis quia et odit perspiciatis quo asperiores officiis. Aut vitae beatae ab suscipit consequatur non laborum veritatis aut commodi repudiandae qui voluptatum incidunt et sapiente earum eum autem quas! \r\n\r\nUt voluptas doloremque qui minima velit rem omnis distinctio. Ad temporibus odit est nulla blanditiis sed itaque dignissimos qui accusamus dolor et mollitia pariatur in quos voluptas aut illum quod. \r\n\r\nSed labore internos sit sequi omnis nam unde accusantium et magnam modi eum quia illo ut ipsa voluptas sed omnis asperiores. Id nobis voluptatem sit velit quisquam sed Quis cumque ex totam officia. Cum dignissimos dolores ea necessitatibus cupiditate ut alias accusantium eos alias harum? Et dicta unde est harum aperiam ut expedita ipsa et facilis voluptas sed sapiente earum.', '2025-11-10 15:28:55'),
(5, 34, 'Lorem ipsum dolor sit amet. Non quaerat placeat aut voluptatem dolorem non inventore velit et voluptatum eligendi sed aliquam autem. Ab rerum excepturi sed veritatis sapiente aut reiciendis quia et odit perspiciatis quo asperiores officiis. Aut vitae beatae ab suscipit consequatur non laborum veritatis aut commodi repudiandae qui voluptatum incidunt et sapiente earum eum autem quas! \r\n\r\nUt voluptas doloremque qui minima velit rem omnis distinctio. Ad temporibus odit est nulla blanditiis sed itaque dignissimos qui accusamus dolor et mollitia pariatur in quos voluptas aut illum quod. \r\n\r\nSed labore internos sit sequi omnis nam unde accusantium et magnam modi eum quia illo ut ipsa voluptas sed omnis asperiores. Id nobis voluptatem sit velit quisquam sed Quis cumque ex totam officia. Cum dignissimos dolores ea necessitatibus cupiditate ut alias accusantium eos alias harum? Et dicta unde est harum aperiam ut expedita ipsa et facilis voluptas sed sapiente earum. TESTE', '2025-11-10 15:40:08'),
(6, 92, 'teste', '2026-02-23 20:15:26'),
(7, 92, 'teste 2', '2026-02-23 20:43:52'),
(8, 95, 'teste', '2026-03-11 14:13:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Postagens_Midia`
--

CREATE TABLE `Postagens_Midia` (
  `id` int(11) NOT NULL,
  `id_postagem` int(11) NOT NULL,
  `url_midia` varchar(255) NOT NULL,
  `tipo_midia` enum('imagem','video') NOT NULL DEFAULT 'imagem',
  `salvo_na_galeria` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = O utilizador quer exibir na aba Galeria do perfil',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Postagens_Midia`
--

INSERT INTO `Postagens_Midia` (`id`, `id_postagem`, `url_midia`, `tipo_midia`, `salvo_na_galeria`, `data_criacao`) VALUES
(1, 10, 'uploads/posts/post_5_1760633778.jpeg', '', 0, '2025-11-19 23:33:07'),
(2, 11, 'uploads/posts/post_5_1760646734.jpg', '', 0, '2025-11-19 23:33:07'),
(3, 12, 'uploads/posts/post_5_1760649510.jpg', '', 0, '2025-11-19 23:33:07'),
(4, 13, 'uploads/posts/post_5_1760715213.jpeg', 'imagem', 0, '2025-11-19 23:33:07'),
(5, 14, 'uploads/posts/post_5_1760716955.mp4', 'video', 0, '2025-11-19 23:33:07'),
(6, 15, 'uploads/posts/post_5_1760811037.mp4', 'video', 0, '2025-11-19 23:33:07'),
(7, 16, 'uploads/posts/post_14_1762478865.jpg', 'imagem', 0, '2025-11-19 23:33:07'),
(8, 35, 'uploads/posts/post_5_1762823113.jpg', 'imagem', 0, '2025-11-19 23:33:07'),
(9, 37, 'uploads/posts/post_5_1763592937.jpg', 'imagem', 0, '2025-11-19 23:33:07'),
(10, 38, 'uploads/posts/post_5_1763594631.webp', 'imagem', 0, '2025-11-19 23:33:07'),
(16, 39, 'uploads/posts/post_5_39_1763602940_0.webp', 'imagem', 1, '2025-11-20 01:42:20'),
(17, 39, 'uploads/posts/post_5_39_1763602940_1.webp', 'imagem', 1, '2025-11-20 01:42:20'),
(18, 39, 'uploads/posts/post_5_39_1763602940_2.webp', 'imagem', 1, '2025-11-20 01:42:21'),
(19, 40, 'uploads/posts/post_12_40_1766077903_0.webp', 'imagem', 1, '2025-12-18 17:11:43'),
(20, 42, 'uploads/posts/post_13_42_1766284963_0.webp', 'imagem', 0, '2025-12-21 02:42:43'),
(21, 52, 'uploads/posts/post_5_52_1766690853_0.webp', 'imagem', 0, '2025-12-25 19:27:33'),
(22, 53, 'assets/images/placeholder-image.png', 'imagem', 1, '2025-12-25 23:17:44'),
(23, 54, 'uploads/posts/post_5_54_694f0b3002978.jpg', 'imagem', 1, '2025-12-26 22:24:48'),
(24, 55, 'uploads/posts/post_5_55_694f0bf972a92.jpg', 'imagem', 1, '2025-12-26 22:28:09'),
(25, 56, 'uploads/posts/post_5_56_6951e4122e4ae.jpg', 'imagem', 1, '2025-12-29 02:14:42'),
(26, 56, 'uploads/posts/post_5_56_6951e4122eee4.jpg', 'imagem', 1, '2025-12-29 02:14:42'),
(27, 57, 'uploads/posts/post_5_57_1767024044_0.mp4', 'video', 0, '2025-12-29 16:00:44'),
(28, 61, 'uploads/posts/post_5_61_695995284b408.png', 'imagem', 1, '2026-01-03 22:16:08'),
(29, 69, 'uploads/posts/post_5_69_1767928780_0.webp', 'imagem', 0, '2026-01-09 03:19:40'),
(30, 69, 'uploads/posts/post_5_69_1767928780_1.webp', 'imagem', 0, '2026-01-09 03:19:40'),
(31, 69, 'uploads/posts/post_5_69_1767928780_2.webp', 'imagem', 0, '2026-01-09 03:19:41'),
(32, 70, 'uploads/posts/post_5_70_1767978911_0.webp', 'imagem', 0, '2026-01-09 17:15:11'),
(33, 71, 'uploads/posts/post_5_71_1767978926_0.webp', 'imagem', 0, '2026-01-09 17:15:26'),
(34, 71, 'uploads/posts/post_5_71_1767978926_1.webp', 'imagem', 0, '2026-01-09 17:15:26'),
(35, 71, 'uploads/posts/post_5_71_1767978926_2.webp', 'imagem', 0, '2026-01-09 17:15:26'),
(36, 72, 'uploads/posts/post_5_72_1767979021_0.webp', 'imagem', 0, '2026-01-09 17:17:01'),
(37, 73, 'uploads/posts/post_5_73_1767993931_0.webp', 'imagem', 0, '2026-01-09 21:25:32'),
(38, 73, 'uploads/posts/post_5_73_1767993932_1.webp', 'imagem', 0, '2026-01-09 21:25:32'),
(39, 73, 'uploads/posts/post_5_73_1767993932_2.webp', 'imagem', 0, '2026-01-09 21:25:32'),
(40, 74, 'uploads/posts/post_5_74_1768177318_0.webp', 'imagem', 0, '2026-01-12 00:21:58'),
(41, 76, 'uploads/posts/post_5_76_1768765896_0.webp', 'imagem', 0, '2026-01-18 19:51:36'),
(42, 93, 'uploads/posts/post_5_93_69a05624bc336.jpeg', 'imagem', 1, '2026-02-26 14:18:12'),
(43, 97, 'uploads/posts/post_13_97_1772462124_0.webp', 'imagem', 1, '2026-03-02 14:35:24'),
(44, 97, 'uploads/posts/post_13_97_1772462124_1.webp', 'imagem', 1, '2026-03-02 14:35:24'),
(45, 97, 'uploads/posts/post_13_97_1772462124_2.webp', 'imagem', 1, '2026-03-02 14:35:24'),
(46, 102, 'midias/feed/fotos/15_manus_2026-03-26_18-28-28_postagem_0.webp', 'imagem', 0, '2026-03-26 21:28:28'),
(47, 103, 'midias/marketplace/fotos/5_diego_2026-03-28_11-44-39_0.webp', 'imagem', 1, '2026-03-28 14:44:39');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Postagens_Salvas`
--

CREATE TABLE `Postagens_Salvas` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `colecao_id` int(11) DEFAULT NULL,
  `id_postagem` int(11) NOT NULL,
  `data_salvo` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Postagens_Salvas`
--

INSERT INTO `Postagens_Salvas` (`id`, `id_usuario`, `colecao_id`, `id_postagem`, `data_salvo`) VALUES
(10, 5, NULL, 2, '2025-10-09 15:32:55'),
(12, 12, NULL, 6, '2025-10-16 14:24:13'),
(13, 5, NULL, 12, '2025-10-16 23:04:42'),
(15, 14, NULL, 13, '2025-11-07 01:20:49'),
(16, 14, NULL, 8, '2025-11-07 01:23:12'),
(19, 5, NULL, 17, '2025-11-08 17:44:21'),
(20, 5, NULL, 31, '2025-11-09 13:38:49'),
(24, 15, NULL, 79, '2026-02-26 15:37:32'),
(26, 15, 4, 68, '2026-02-26 18:13:03'),
(27, 13, NULL, 92, '2026-02-27 17:00:21'),
(28, 15, 4, 95, '2026-02-28 16:04:47'),
(29, 15, 4, 93, '2026-02-28 17:34:37'),
(30, 15, 4, 74, '2026-02-28 17:35:48'),
(31, 15, 4, 34, '2026-02-28 20:05:15'),
(34, 13, 7, 55, '2026-03-02 14:33:43'),
(35, 13, 7, 40, '2026-03-02 14:33:59'),
(36, 13, 7, 97, '2026-03-02 14:35:40');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Post_Meta`
--

CREATE TABLE `Post_Meta` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `meta_key` varchar(100) NOT NULL,
  `meta_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Post_Meta`
--

INSERT INTO `Post_Meta` (`id`, `post_id`, `meta_key`, `meta_value`, `created_at`) VALUES
(1, 43, 'link_url', 'https://www.google.com/', '2025-12-21 17:19:04'),
(2, 43, 'link_title', 'Google', '2025-12-21 17:19:04'),
(3, 43, 'link_desc', 'Search the world\'s information, including webpages, images, videos and more. Google has many special features to help you find exactly what you\'re looking for.', '2025-12-21 17:19:04'),
(4, 45, 'link_url', 'https://santistas.net/noticias-do-santos/santos-fc-rafa-21-12/', '2025-12-21 18:55:05'),
(5, 45, 'link_title', 'Santos FC: Após início turbulento, Zé Rafael muda cenário e surpreende nos bastidores', '2025-12-21 18:55:05'),
(6, 45, 'link_image', 'https://santistas.net/wp-content/uploads/2025/12/Capas-para-materias-61.jpg', '2025-12-21 18:55:05'),
(7, 45, 'link_desc', 'Zé Rafael supera início difícil e se firma como opção importante no Santos FC', '2025-12-21 18:55:05'),
(8, 46, 'link_url', 'https://www1.folha.uol.com.br/mercado/2025/12/inteligencia-artificial-cria-negocio-imobiliario-bilionario-no-brasil-com-data-centers.shtml', '2025-12-21 20:41:02'),
(9, 46, 'link_title', 'Inteligência artificial cria negócio imobiliário bilionário no Brasil com data centers', '2025-12-21 20:41:02'),
(10, 46, 'link_image', 'https://f.i.uol.com.br/fotografia/2025/11/28/1764371370692a2baa29800_1764371370_3x2_lg.jpg', '2025-12-21 20:41:02'),
(11, 46, 'link_desc', 'Mercado atrai investidores como fundos de BTG Pactual, Patria, Goldman Sachs e Actis', '2025-12-21 20:41:02'),
(12, 49, 'link_url', 'https://jornalrazao.com/', '2025-12-21 22:51:12'),
(13, 49, 'link_title', 'Notícias de Santa Catarina | Jornal Razão - Jornal Razão', '2025-12-21 22:51:12'),
(14, 49, 'link_image', 'https://jornalrazao.com/wp-content/uploads/2025/06/1977F3-2.png', '2025-12-21 22:51:12'),
(15, 49, 'link_desc', 'Fique por dentro das últimas notícias de Santa Catarina com o Jornal Razão. Credibilidade e cobertura completa em todo o estado.', '2025-12-21 22:51:12'),
(16, 64, 'link_url', 'https://www.cnnbrasil.com.br/nacional/brasil/irmao-de-eliza-samudio-se-pronuncia-sobre-passaporte-encontrado-em-portugal/', '2026-01-06 17:26:33'),
(17, 64, 'link_title', 'Irmão de Eliza Samudio se pronuncia sobre passaporte encontrado em Portugal | CNN Brasil', '2026-01-06 17:26:33'),
(18, 64, 'link_image', 'https://admin.cnnbrasil.com.br/wp-content/uploads/sites/12/2026/01/ELIZA-SAMUDIO-6.jpg?w=1200&h=630&crop=1', '2026-01-06 17:26:33'),
(19, 64, 'link_desc', 'Arlie Moura, irmão por parte de mãe de Eliza, diz acreditar na veracidade do documento e aguarda confirmações das autoridades', '2026-01-06 17:26:33'),
(20, 79, 'link_url', 'https://socialbr.lol/~klscom/tarefas/', '2026-01-30 22:36:24'),
(21, 79, 'link_title', 'CheckYou | Gerenciador', '2026-01-30 22:36:24');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Salvos_Colecoes`
--

CREATE TABLE `Salvos_Colecoes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `privacidade` enum('privada','publica') DEFAULT 'privada',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `Salvos_Colecoes`
--

INSERT INTO `Salvos_Colecoes` (`id`, `usuario_id`, `nome`, `privacidade`, `data_criacao`) VALUES
(1, 15, 'Geral', 'privada', '2026-02-27 15:35:49'),
(2, 13, 'Geral', 'privada', '2026-02-27 16:59:39'),
(4, 15, 'teste', 'privada', '2026-02-28 15:31:11'),
(6, 15, 'teste 2', 'privada', '2026-02-28 21:21:11'),
(7, 13, 'Sonic', 'publica', '2026-03-02 14:32:59'),
(8, 5, 'Geral', 'privada', '2026-03-02 14:36:16');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Suporte_Chamados`
--

CREATE TABLE `Suporte_Chamados` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `assunto` varchar(255) NOT NULL,
  `categoria` varchar(50) DEFAULT 'Geral',
  `status` enum('aberto','em_andamento','resolvido') NOT NULL DEFAULT 'aberto',
  `diagnostico_json` text DEFAULT NULL COMMENT 'URL de origem, Navegador e Resolução (Dica de Ouro)',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Suporte_Chamados`
--

INSERT INTO `Suporte_Chamados` (`id`, `usuario_id`, `assunto`, `categoria`, `status`, `diagnostico_json`, `data_criacao`, `data_atualizacao`) VALUES
(1, 13, 'Chamado teste', 'Bug/Erro Técnico', 'resolvido', '{\"url\":\"https:\\/\\/socialbr.lol\\/suporte\\/abrir\",\"browser\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/145.0.0.0 Safari\\/537.36\",\"res\":\"1280x800\"}', '2026-03-02 20:59:06', '2026-03-02 21:33:37'),
(2, 15, 'Outro teste', 'Dúvida de Uso', 'em_andamento', '{\"url\":\"https:\\/\\/socialbr.lol\\/suporte\\/abrir\",\"browser\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/145.0.0.0 Safari\\/537.36\",\"res\":\"1280x800\"}', '2026-03-03 19:51:42', '2026-03-11 14:11:24'),
(3, 15, 'mais um teste', 'Bug/Erro Técnico', 'aberto', '{\"url\":\"https:\\/\\/socialbr.lol\\/suporte\\/abrir\",\"browser\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Safari\\/537.36\",\"res\":\"1280x800\"}', '2026-03-26 17:23:38', '2026-03-28 14:56:35'),
(4, 5, 'Chamado diego kleins', 'Bug/Erro Técnico', 'aberto', '{\"url\":\"https:\\/\\/socialbr.lol\\/suporte\\/abrir\",\"browser\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"res\":\"412x892\"}', '2026-03-30 01:29:44', '2026-03-30 01:29:44');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Suporte_Mensagens`
--

CREATE TABLE `Suporte_Mensagens` (
  `id` int(11) NOT NULL,
  `chamado_id` int(11) NOT NULL,
  `remetente_tipo` enum('usuario','admin') NOT NULL,
  `mensagem` text DEFAULT NULL,
  `foto_url` varchar(255) DEFAULT NULL COMMENT 'Caminho da foto de alta qualidade para análise',
  `data_envio` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Suporte_Mensagens`
--

INSERT INTO `Suporte_Mensagens` (`id`, `chamado_id`, `remetente_tipo`, `mensagem`, `foto_url`, `data_envio`) VALUES
(1, 1, 'usuario', 'Isso é uma explicação teste', 'uploads/suporte/suporte_1772485146_69a5fa1a3538f.jpg', '2026-03-02 20:59:06'),
(2, 1, 'admin', 'isso é uma resposta teste', 'uploads/suporte/suporte_1772487103_69a601bff0291.jpg', '2026-03-02 21:31:43'),
(3, 2, 'usuario', 'Duvida teste', NULL, '2026-03-03 19:51:42'),
(4, 2, 'admin', 'teste', NULL, '2026-03-03 20:35:33'),
(5, 2, 'usuario', 'teste', NULL, '2026-03-03 20:55:35'),
(6, 2, 'admin', 'teste', NULL, '2026-03-04 16:45:41'),
(7, 2, 'admin', 'fcf', NULL, '2026-03-04 16:48:23'),
(8, 2, 'usuario', 'teste', 'uploads/suporte/suporte_1772644418_69a868425ff6c.png', '2026-03-04 17:13:38'),
(9, 2, 'admin', 'teste', 'uploads/suporte/suporte_1772644635_69a8691b8fa7d.png', '2026-03-04 17:17:15'),
(10, 2, 'usuario', 'teste', 'uploads/suporte/suporte_1773237805_69b1762d7abfd.png', '2026-03-11 14:03:25'),
(11, 2, 'usuario', 'teste', 'uploads/suporte/suporte_1773237900_69b1768ce2961.png', '2026-03-11 14:05:00'),
(12, 2, 'usuario', 'teste', NULL, '2026-03-11 14:05:24'),
(13, 2, 'usuario', 'teste', NULL, '2026-03-11 14:11:24'),
(14, 3, 'usuario', 'isso é uma descrição teste', NULL, '2026-03-26 17:23:38'),
(15, 3, 'usuario', 'teste', 'midias/suporte/fotos/15_manus_2026-03-28_11-56-35_suporte.jpg', '2026-03-28 14:56:35'),
(16, 4, 'usuario', 'Teste', NULL, '2026-03-30 01:29:44');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Usuarios`
--

CREATE TABLE `Usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `sobrenome` varchar(100) NOT NULL,
  `data_nascimento` date DEFAULT NULL,
  `nome_de_usuario` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `email_verificado` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = Não Verificado, 1 = Verificado',
  `token_verificacao` varchar(100) DEFAULT NULL COMMENT 'Token único para o link de ativação enviado por e-mail',
  `data_ultimo_aviso_verificacao` datetime DEFAULT NULL COMMENT 'Data da última exibição do Toast de aviso (Controle de frequência de 24h)',
  `cpf` varchar(14) DEFAULT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'membro',
  `status` enum('ativo','suspenso') NOT NULL DEFAULT 'ativo',
  `foto_perfil_url` varchar(255) DEFAULT NULL,
  `id_bairro` int(11) DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `relacionamento` enum('Não especificado','Solteiro(a)','Em um relacionamento sério','Casado(a)','Divorciado(a)') NOT NULL DEFAULT 'Não especificado' COMMENT 'Status de relacionamento do usuário',
  `biografia` text DEFAULT NULL COMMENT 'Pequena biografia ou descrição do usuário',
  `perfil_privado` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = Público, 1 = Privado (apenas amigos)',
  `privacidade_posts_padrao` enum('publico','amigos') NOT NULL DEFAULT 'publico' COMMENT 'Define a escolha padrão do usuário para novos posts',
  `privacidade_amigos` enum('todos','amigos','ninguem') NOT NULL DEFAULT 'amigos' COMMENT 'Define quem pode ver a lista de amigos do utilizador',
  `ultimo_acesso` timestamp NULL DEFAULT NULL COMMENT 'Timestamp da última atividade do usuário',
  `foto_capa_url` varchar(255) DEFAULT NULL,
  `capa_posicao_y` int(11) DEFAULT 50
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Usuarios`
--

INSERT INTO `Usuarios` (`id`, `nome`, `sobrenome`, `data_nascimento`, `nome_de_usuario`, `email`, `email_verificado`, `token_verificacao`, `data_ultimo_aviso_verificacao`, `cpf`, `senha_hash`, `role`, `status`, `foto_perfil_url`, `id_bairro`, `data_cadastro`, `relacionamento`, `biografia`, `perfil_privado`, `privacidade_posts_padrao`, `privacidade_amigos`, `ultimo_acesso`, `foto_capa_url`, `capa_posicao_y`) VALUES
(4, 'teste1', 'adm', '2025-09-18', 'teste', 'teste1@teste.com', 1, NULL, NULL, NULL, '$2y$10$jhpLUvgFXuNjDsld1M8uVeIFXZUJ84eo7AQ3WUTYd06eAA5Wdtjva', 'membro', 'ativo', 'uploads/avatars/user_4_1759353654.png', 12, '2025-09-29 20:46:34', 'Não especificado', NULL, 0, 'publico', 'amigos', NULL, NULL, 50),
(5, 'Diego', 'Kleins', '1997-10-30', 'diegokleins', 'didiego2010.dr@gmail.com', 1, NULL, '2026-03-26 12:42:31', '46913936842', '$2y$10$ZbugitlOTfOUlIcW.0dm1.dmQcF7ryqmLKu8PDUGrl3fNaHjiWjWC', 'admin', 'ativo', 'uploads/avatars/user_5_1760544118.jpeg', 24, '2025-10-01 20:20:10', 'Casado(a)', 'Fundador e CEO desse site :)', 0, 'amigos', 'amigos', '2026-04-01 17:23:43', 'uploads/covers/cover_5_1766159379.webp', 50),
(9, 'Centro', 'Itajai', '2025-10-04', 'centroitajai2', 'centroitajai@gmail.com', 1, NULL, NULL, NULL, '$2y$10$U3OktZm8BQskcbyr64GJP.9I8WxJehrTAcklOLw7r8vOFjwsbUomq', 'membro', 'ativo', NULL, 22, '2025-10-03 17:52:02', 'Não especificado', NULL, 0, 'publico', 'amigos', NULL, NULL, 50),
(11, 'conta', 'teste', '2005-06-15', 'contateste', 'email@email.com', 1, NULL, NULL, NULL, '$2y$10$uThJHp0XYx1VilFB4osTeuxor2HqbbcKD18nYV1WZqMWkcnrjGC.q', 'membro', 'ativo', NULL, 20, '2025-10-03 18:04:32', 'Não especificado', NULL, 0, 'publico', 'amigos', NULL, NULL, 50),
(12, 'Diego', 'Reis', '2023-06-05', 'diegoreis', 'testeg@teste.com', 1, NULL, NULL, NULL, '$2y$10$v5nD5q90ptjx7xIAg78zx.9neqmDuWI656exSBmXq7YaZ2svtRWWS', 'admin', 'ativo', 'uploads/avatars/user_12_1760319912.jpg', 22, '2025-10-13 01:43:48', 'Não especificado', NULL, 0, 'publico', 'amigos', '2026-01-31 21:38:45', 'uploads/covers/cover_12_1766195183.webp', 50),
(13, 'diego', 'teste', '1999-10-30', 'diegoteste', 'teste@teste.com', 1, NULL, NULL, NULL, '$2y$10$5i5l7YppmADt0UtR7gLvge8b2Ulbx14AeS5yt0Dqupio7aRN3WEAG', 'membro', 'ativo', 'uploads/avatars/user_13_1766284414.webp', 20, '2025-11-02 16:31:29', 'Não especificado', 'Esse é um perfil teste', 0, 'publico', 'amigos', '2026-03-23 21:20:22', 'uploads/covers/cover_13_1766284524.webp', 50),
(14, 'teste', 'tes', '2025-11-10', 'testeste', 'testeste@testeste.com', 1, NULL, NULL, NULL, '$2y$10$HqlLX8gPVOo8S5pxAbBdeud.6ioAVBSPPOSzwCoDSFteZt1YdB0zu', 'membro', 'ativo', 'uploads/avatars/user_14_1762480373.jpg', 3, '2025-11-06 15:40:28', 'Solteiro(a)', 'Oi esse sou eu', 1, 'amigos', 'amigos', '2025-12-21 02:36:14', 'uploads/covers/cover_14_1766269044.webp', 50),
(15, 'Manus', '1', '1995-10-10', 'manus1', 'manus1@teste.com', 1, NULL, NULL, NULL, '$2y$10$tM0xtGWCfN/S9j8Or3TNF.dLfBu68RH8Eoh9sL5ENY/wfY3FTKBei', 'membro', 'ativo', 'midias/perfil/fotos/15_manus_2026-03-26_18-16-03_avatar.webp', 7, '2026-01-06 21:41:46', 'Não especificado', 'Oi, eu fui criado pela Manus, uma inteligência artificial.', 0, 'publico', 'amigos', '2026-04-01 17:22:42', NULL, 50),
(18, 'diego', 'diego', '1997-10-30', 'diegodiego', 'hibov15557@exahut.com', 1, NULL, NULL, NULL, '$2y$10$TkE5PwgaPKdOMuS.RfvjqekoYU8eIncmQQF9wKNcD9XfU.M66RgM.', 'membro', 'ativo', NULL, 14, '2026-03-26 17:26:47', 'Não especificado', NULL, 0, 'publico', 'amigos', '2026-03-26 17:27:21', NULL, 50);

-- --------------------------------------------------------

--
-- Estrutura para tabela `Usuarios_Recuperacao`
--

CREATE TABLE `Usuarios_Recuperacao` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `codigo` varchar(6) NOT NULL,
  `token` varchar(64) DEFAULT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_expiracao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `Usuarios_Recuperacao`
--

INSERT INTO `Usuarios_Recuperacao` (`id`, `usuario_id`, `codigo`, `token`, `usado`, `data_criacao`, `data_expiracao`) VALUES
(1, 5, '794745', NULL, 1, '2026-03-20 18:23:52', '2026-03-20 18:38:52'),
(2, 5, '963373', NULL, 1, '2026-03-20 20:40:07', '2026-03-20 20:55:07'),
(3, 13, '026365', NULL, 0, '2026-03-21 14:51:59', '2026-03-21 15:06:59'),
(4, 5, '545973', NULL, 1, '2026-03-22 19:20:45', '2026-03-22 19:35:45'),
(5, 5, '888938', NULL, 1, '2026-03-22 19:44:01', '2026-03-22 19:59:01'),
(6, 5, '508127', NULL, 1, '2026-03-26 15:51:10', '2026-03-26 16:06:10'),
(7, 5, '239736', NULL, 1, '2026-03-26 15:52:22', '2026-03-26 16:07:22'),
(8, 5, '967270', NULL, 1, '2026-03-26 16:56:35', '2026-03-26 17:11:35');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `Amizades`
--
ALTER TABLE `Amizades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `amizade_unica` (`usuario_um_id`,`usuario_dois_id`),
  ADD KEY `idx_usuario_dois` (`usuario_dois_id`);

--
-- Índices de tabela `Anotacoes_Admin`
--
ALTER TABLE `Anotacoes_Admin`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `Avisos_Destinatarios`
--
ALTER TABLE `Avisos_Destinatarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_aviso` (`id_aviso`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices de tabela `Avisos_Lidos`
--
ALTER TABLE `Avisos_Lidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_usuario_aviso` (`id_usuario`,`id_aviso`),
  ADD KEY `fk_lido_aviso` (`id_aviso`);

--
-- Índices de tabela `Avisos_Sistema`
--
ALTER TABLE `Avisos_Sistema`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_aviso_admin` (`criado_por`);

--
-- Índices de tabela `Bairros`
--
ALTER TABLE `Bairros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cidade` (`id_cidade`);

--
-- Índices de tabela `Bloqueios`
--
ALTER TABLE `Bloqueios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_bloqueio` (`bloqueador_id`,`bloqueado_id`),
  ADD KEY `bloqueado_id` (`bloqueado_id`);

--
-- Índices de tabela `busca_interacoes`
--
ALTER TABLE `busca_interacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_interacao_usuario` (`id_usuario`),
  ADD KEY `idx_termo_frequencia` (`termo`);

--
-- Índices de tabela `busca_sinonimos`
--
ALTER TABLE `busca_sinonimos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_termo_digitado` (`termo_digitado`);

--
-- Índices de tabela `chat_conversas`
--
ALTER TABLE `chat_conversas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_chat_dono` (`dono_id`),
  ADD KEY `idx_ultima_msg` (`ultima_mensagem_at`);

--
-- Índices de tabela `chat_mensagens`
--
ALTER TABLE `chat_mensagens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_chat_conv_msg` (`conversa_id`),
  ADD KEY `fk_chat_user_msg` (`remetente_id`),
  ADD KEY `idx_msg_criado` (`criado_em`);

--
-- Índices de tabela `chat_participantes`
--
ALTER TABLE `chat_participantes`
  ADD PRIMARY KEY (`conversa_id`,`usuario_id`),
  ADD KEY `fk_chat_user_part` (`usuario_id`);

--
-- Índices de tabela `Cidades`
--
ALTER TABLE `Cidades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_estado` (`id_estado`);

--
-- Índices de tabela `Comentarios`
--
ALTER TABLE `Comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_postagem` (`id_postagem`),
  ADD KEY `idx_usuario` (`id_usuario`),
  ADD KEY `fk_comentario_pai` (`id_comentario_pai`);

--
-- Índices de tabela `Comentarios_Edicoes`
--
ALTER TABLE `Comentarios_Edicoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_comentario` (`id_comentario`);

--
-- Índices de tabela `Configuracoes`
--
ALTER TABLE `Configuracoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chave_unica` (`chave`);

--
-- Índices de tabela `Curtidas`
--
ALTER TABLE `Curtidas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `curtida_unica` (`id_usuario`,`id_postagem`),
  ADD KEY `id_postagem` (`id_postagem`);

--
-- Índices de tabela `Curtidas_Comentarios`
--
ALTER TABLE `Curtidas_Comentarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `curtida_comentario_unica` (`id_usuario`,`id_comentario`),
  ADD KEY `fk_curtida_comentario_comentario` (`id_comentario`);

--
-- Índices de tabela `Denuncias`
--
ALTER TABLE `Denuncias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario_denunciou` (`id_usuario_denunciou`);

--
-- Índices de tabela `Enquetes`
--
ALTER TABLE `Enquetes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Índices de tabela `Enquete_Opcoes`
--
ALTER TABLE `Enquete_Opcoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enquete_id` (`enquete_id`);

--
-- Índices de tabela `Enquete_Votos`
--
ALTER TABLE `Enquete_Votos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unico_voto` (`opcao_id`,`usuario_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `Estados`
--
ALTER TABLE `Estados`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `Grupos`
--
ALTER TABLE `Grupos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_grupo_dono` (`id_dono`);
ALTER TABLE `Grupos` ADD FULLTEXT KEY `idx_busca_full_grupo` (`nome`,`descricao`);

--
-- Índices de tabela `Grupos_Membros`
--
ALTER TABLE `Grupos_Membros`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_membro_unico` (`id_grupo`,`id_usuario`),
  ADD KEY `fk_membro_usuario` (`id_usuario`);

--
-- Índices de tabela `Grupos_Solicitacoes`
--
ALTER TABLE `Grupos_Solicitacoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_solicitacao_unica` (`id_grupo`,`id_usuario`),
  ADD KEY `fk_sol_usuario` (`id_usuario`);

--
-- Índices de tabela `Links_Cliques`
--
ALTER TABLE `Links_Cliques`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_post` (`post_id`),
  ADD KEY `idx_data` (`data_clique`);

--
-- Índices de tabela `Logs_Acessos_Negados`
--
ALTER TABLE `Logs_Acessos_Negados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_slug` (`slug_tentado`),
  ADD KEY `idx_data` (`data_tentativa`),
  ADD KEY `fk_logs_usuario` (`usuario_id`);

--
-- Índices de tabela `Logs_Admin`
--
ALTER TABLE `Logs_Admin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_log_admin` (`admin_id`);

--
-- Índices de tabela `Logs_Emails`
--
ALTER TABLE `Logs_Emails`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `Logs_Erros_Sistema`
--
ALTER TABLE `Logs_Erros_Sistema`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_hash_erro` (`hash_erro`),
  ADD KEY `fk_logs_erros_usuario` (`usuario_id`);

--
-- Índices de tabela `Logs_Login`
--
ALTER TABLE `Logs_Login`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_usuario` (`id_usuario`);

--
-- Índices de tabela `Logs_Visualizacao_Post`
--
ALTER TABLE `Logs_Visualizacao_Post`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_postagem` (`id_postagem`),
  ADD KEY `idx_id_usuario` (`id_usuario_visualizou`);

--
-- Índices de tabela `Marketplace_Anuncios`
--
ALTER TABLE `Marketplace_Anuncios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_postagem` (`id_postagem`),
  ADD KEY `idx_categoria` (`categoria`),
  ADD KEY `idx_preco` (`preco`),
  ADD KEY `idx_geolocalizacao` (`estado`,`cidade`),
  ADD KEY `idx_status` (`status_venda`);

--
-- Índices de tabela `Marketplace_Interesses`
--
ALTER TABLE `Marketplace_Interesses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_interesse` (`id_anuncio`,`id_usuario`),
  ADD KEY `idx_usuario_interesse` (`id_usuario`);

--
-- Índices de tabela `Menus_Sistema`
--
ALTER TABLE `Menus_Sistema`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `fk_menu_pai` (`parent_id`);

--
-- Índices de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `remetente_id` (`remetente_id`);

--
-- Índices de tabela `Palavras_Proibidas`
--
ALTER TABLE `Palavras_Proibidas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `palavra_unica` (`termo`);

--
-- Índices de tabela `Postagens`
--
ALTER TABLE `Postagens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `fk_post_original` (`post_original_id`),
  ADD KEY `idx_tipo_post` (`tipo_post`),
  ADD KEY `idx_post_grupo` (`id_grupo`),
  ADD KEY `idx_post_status_priv` (`status`,`privacidade`);
ALTER TABLE `Postagens` ADD FULLTEXT KEY `idx_busca_full_post` (`conteudo_texto`);

--
-- Índices de tabela `Postagens_Edicoes`
--
ALTER TABLE `Postagens_Edicoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_postagem` (`id_postagem`);

--
-- Índices de tabela `Postagens_Midia`
--
ALTER TABLE `Postagens_Midia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_postagem_midia` (`id_postagem`),
  ADD KEY `idx_galeria` (`id_postagem`,`salvo_na_galeria`);

--
-- Índices de tabela `Postagens_Salvas`
--
ALTER TABLE `Postagens_Salvas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `salvo_unico` (`id_usuario`,`id_postagem`),
  ADD KEY `fk_salvo_postagem` (`id_postagem`),
  ADD KEY `fk_salvos_colecao` (`colecao_id`),
  ADD KEY `idx_salvos_usuario_colecao` (`id_usuario`,`colecao_id`);

--
-- Índices de tabela `Post_Meta`
--
ALTER TABLE `Post_Meta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Índices de tabela `Salvos_Colecoes`
--
ALTER TABLE `Salvos_Colecoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_colecao_usuario` (`usuario_id`);

--
-- Índices de tabela `Suporte_Chamados`
--
ALTER TABLE `Suporte_Chamados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_suporte_usuario` (`usuario_id`);

--
-- Índices de tabela `Suporte_Mensagens`
--
ALTER TABLE `Suporte_Mensagens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_suporte_mensagem_chamado` (`chamado_id`);

--
-- Índices de tabela `Usuarios`
--
ALTER TABLE `Usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome_de_usuario` (`nome_de_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `idx_cpf_unique` (`cpf`),
  ADD KEY `fk_usuario_bairro` (`id_bairro`),
  ADD KEY `idx_status_perfil` (`status`,`perfil_privado`),
  ADD KEY `idx_token_verificacao` (`token_verificacao`);
ALTER TABLE `Usuarios` ADD FULLTEXT KEY `idx_busca_full_usuario` (`nome`,`sobrenome`,`nome_de_usuario`);

--
-- Índices de tabela `Usuarios_Recuperacao`
--
ALTER TABLE `Usuarios_Recuperacao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_recuperacao_usuario` (`usuario_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `Amizades`
--
ALTER TABLE `Amizades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de tabela `Anotacoes_Admin`
--
ALTER TABLE `Anotacoes_Admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `Avisos_Destinatarios`
--
ALTER TABLE `Avisos_Destinatarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `Avisos_Lidos`
--
ALTER TABLE `Avisos_Lidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de tabela `Avisos_Sistema`
--
ALTER TABLE `Avisos_Sistema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de tabela `Bairros`
--
ALTER TABLE `Bairros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `Bloqueios`
--
ALTER TABLE `Bloqueios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `busca_interacoes`
--
ALTER TABLE `busca_interacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de tabela `busca_sinonimos`
--
ALTER TABLE `busca_sinonimos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `chat_conversas`
--
ALTER TABLE `chat_conversas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `chat_mensagens`
--
ALTER TABLE `chat_mensagens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT de tabela `Cidades`
--
ALTER TABLE `Cidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=297;

--
-- AUTO_INCREMENT de tabela `Comentarios`
--
ALTER TABLE `Comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT de tabela `Comentarios_Edicoes`
--
ALTER TABLE `Comentarios_Edicoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `Configuracoes`
--
ALTER TABLE `Configuracoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `Curtidas`
--
ALTER TABLE `Curtidas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=209;

--
-- AUTO_INCREMENT de tabela `Curtidas_Comentarios`
--
ALTER TABLE `Curtidas_Comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de tabela `Denuncias`
--
ALTER TABLE `Denuncias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de tabela `Enquetes`
--
ALTER TABLE `Enquetes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `Enquete_Opcoes`
--
ALTER TABLE `Enquete_Opcoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `Enquete_Votos`
--
ALTER TABLE `Enquete_Votos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `Estados`
--
ALTER TABLE `Estados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `Grupos`
--
ALTER TABLE `Grupos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `Grupos_Membros`
--
ALTER TABLE `Grupos_Membros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `Grupos_Solicitacoes`
--
ALTER TABLE `Grupos_Solicitacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `Links_Cliques`
--
ALTER TABLE `Links_Cliques`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `Logs_Acessos_Negados`
--
ALTER TABLE `Logs_Acessos_Negados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de tabela `Logs_Admin`
--
ALTER TABLE `Logs_Admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT de tabela `Logs_Emails`
--
ALTER TABLE `Logs_Emails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `Logs_Erros_Sistema`
--
ALTER TABLE `Logs_Erros_Sistema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=184;

--
-- AUTO_INCREMENT de tabela `Logs_Login`
--
ALTER TABLE `Logs_Login`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=581;

--
-- AUTO_INCREMENT de tabela `Logs_Visualizacao_Post`
--
ALTER TABLE `Logs_Visualizacao_Post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=166;

--
-- AUTO_INCREMENT de tabela `Marketplace_Anuncios`
--
ALTER TABLE `Marketplace_Anuncios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `Marketplace_Interesses`
--
ALTER TABLE `Marketplace_Interesses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `Menus_Sistema`
--
ALTER TABLE `Menus_Sistema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=243;

--
-- AUTO_INCREMENT de tabela `Palavras_Proibidas`
--
ALTER TABLE `Palavras_Proibidas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT de tabela `Postagens`
--
ALTER TABLE `Postagens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT de tabela `Postagens_Edicoes`
--
ALTER TABLE `Postagens_Edicoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `Postagens_Midia`
--
ALTER TABLE `Postagens_Midia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de tabela `Postagens_Salvas`
--
ALTER TABLE `Postagens_Salvas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de tabela `Post_Meta`
--
ALTER TABLE `Post_Meta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de tabela `Salvos_Colecoes`
--
ALTER TABLE `Salvos_Colecoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `Suporte_Chamados`
--
ALTER TABLE `Suporte_Chamados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `Suporte_Mensagens`
--
ALTER TABLE `Suporte_Mensagens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `Usuarios`
--
ALTER TABLE `Usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `Usuarios_Recuperacao`
--
ALTER TABLE `Usuarios_Recuperacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `Amizades`
--
ALTER TABLE `Amizades`
  ADD CONSTRAINT `fk_amizade_usuario_dois` FOREIGN KEY (`usuario_dois_id`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_amizade_usuario_um` FOREIGN KEY (`usuario_um_id`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `Avisos_Destinatarios`
--
ALTER TABLE `Avisos_Destinatarios`
  ADD CONSTRAINT `Avisos_Destinatarios_ibfk_1` FOREIGN KEY (`id_aviso`) REFERENCES `Avisos_Sistema` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Avisos_Destinatarios_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Avisos_Lidos`
--
ALTER TABLE `Avisos_Lidos`
  ADD CONSTRAINT `fk_lido_aviso` FOREIGN KEY (`id_aviso`) REFERENCES `Avisos_Sistema` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lido_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Avisos_Sistema`
--
ALTER TABLE `Avisos_Sistema`
  ADD CONSTRAINT `fk_aviso_admin` FOREIGN KEY (`criado_por`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Bairros`
--
ALTER TABLE `Bairros`
  ADD CONSTRAINT `Bairros_ibfk_1` FOREIGN KEY (`id_cidade`) REFERENCES `Cidades` (`id`);

--
-- Restrições para tabelas `Bloqueios`
--
ALTER TABLE `Bloqueios`
  ADD CONSTRAINT `Bloqueios_ibfk_1` FOREIGN KEY (`bloqueador_id`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Bloqueios_ibfk_2` FOREIGN KEY (`bloqueado_id`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `busca_interacoes`
--
ALTER TABLE `busca_interacoes`
  ADD CONSTRAINT `fk_interacao_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `chat_conversas`
--
ALTER TABLE `chat_conversas`
  ADD CONSTRAINT `fk_chat_dono` FOREIGN KEY (`dono_id`) REFERENCES `Usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `chat_mensagens`
--
ALTER TABLE `chat_mensagens`
  ADD CONSTRAINT `fk_chat_conv_msg` FOREIGN KEY (`conversa_id`) REFERENCES `chat_conversas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_chat_user_msg` FOREIGN KEY (`remetente_id`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `chat_participantes`
--
ALTER TABLE `chat_participantes`
  ADD CONSTRAINT `fk_chat_conv_part` FOREIGN KEY (`conversa_id`) REFERENCES `chat_conversas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_chat_user_part` FOREIGN KEY (`usuario_id`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Cidades`
--
ALTER TABLE `Cidades`
  ADD CONSTRAINT `Cidades_ibfk_1` FOREIGN KEY (`id_estado`) REFERENCES `Estados` (`id`);

--
-- Restrições para tabelas `Comentarios`
--
ALTER TABLE `Comentarios`
  ADD CONSTRAINT `fk_comentario_pai` FOREIGN KEY (`id_comentario_pai`) REFERENCES `Comentarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comentario_postagem` FOREIGN KEY (`id_postagem`) REFERENCES `Postagens` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comentario_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `Comentarios_Edicoes`
--
ALTER TABLE `Comentarios_Edicoes`
  ADD CONSTRAINT `fk_edicao_comentario` FOREIGN KEY (`id_comentario`) REFERENCES `Comentarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `Curtidas`
--
ALTER TABLE `Curtidas`
  ADD CONSTRAINT `Curtidas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Curtidas_ibfk_2` FOREIGN KEY (`id_postagem`) REFERENCES `Postagens` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Curtidas_Comentarios`
--
ALTER TABLE `Curtidas_Comentarios`
  ADD CONSTRAINT `fk_curtida_comentario_comentario` FOREIGN KEY (`id_comentario`) REFERENCES `Comentarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_curtida_comentario_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `Denuncias`
--
ALTER TABLE `Denuncias`
  ADD CONSTRAINT `denuncias_ibfk_1` FOREIGN KEY (`id_usuario_denunciou`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Enquetes`
--
ALTER TABLE `Enquetes`
  ADD CONSTRAINT `Enquetes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `Postagens` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Enquete_Opcoes`
--
ALTER TABLE `Enquete_Opcoes`
  ADD CONSTRAINT `Enquete_Opcoes_ibfk_1` FOREIGN KEY (`enquete_id`) REFERENCES `Enquetes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Enquete_Votos`
--
ALTER TABLE `Enquete_Votos`
  ADD CONSTRAINT `Enquete_Votos_ibfk_1` FOREIGN KEY (`opcao_id`) REFERENCES `Enquete_Opcoes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Enquete_Votos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Grupos`
--
ALTER TABLE `Grupos`
  ADD CONSTRAINT `fk_grupo_dono` FOREIGN KEY (`id_dono`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Grupos_Membros`
--
ALTER TABLE `Grupos_Membros`
  ADD CONSTRAINT `fk_membro_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `Grupos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_membro_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Grupos_Solicitacoes`
--
ALTER TABLE `Grupos_Solicitacoes`
  ADD CONSTRAINT `fk_sol_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `Grupos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sol_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Logs_Acessos_Negados`
--
ALTER TABLE `Logs_Acessos_Negados`
  ADD CONSTRAINT `fk_logs_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `Usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `Logs_Admin`
--
ALTER TABLE `Logs_Admin`
  ADD CONSTRAINT `fk_log_admin_user` FOREIGN KEY (`admin_id`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Logs_Erros_Sistema`
--
ALTER TABLE `Logs_Erros_Sistema`
  ADD CONSTRAINT `fk_logs_erros_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `Usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `Logs_Visualizacao_Post`
--
ALTER TABLE `Logs_Visualizacao_Post`
  ADD CONSTRAINT `fk_log_post_postagem` FOREIGN KEY (`id_postagem`) REFERENCES `Postagens` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_log_post_usuario` FOREIGN KEY (`id_usuario_visualizou`) REFERENCES `Usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `Marketplace_Anuncios`
--
ALTER TABLE `Marketplace_Anuncios`
  ADD CONSTRAINT `Marketplace_Anuncios_ibfk_1` FOREIGN KEY (`id_postagem`) REFERENCES `Postagens` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Marketplace_Interesses`
--
ALTER TABLE `Marketplace_Interesses`
  ADD CONSTRAINT `fk_interesse_anuncio` FOREIGN KEY (`id_anuncio`) REFERENCES `Marketplace_Anuncios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_interesse_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Menus_Sistema`
--
ALTER TABLE `Menus_Sistema`
  ADD CONSTRAINT `fk_menu_pai` FOREIGN KEY (`parent_id`) REFERENCES `Menus_Sistema` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD CONSTRAINT `notificacoes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notificacoes_ibfk_2` FOREIGN KEY (`remetente_id`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Postagens`
--
ALTER TABLE `Postagens`
  ADD CONSTRAINT `Postagens_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_post_original` FOREIGN KEY (`post_original_id`) REFERENCES `Postagens` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_postagem_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `Grupos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Postagens_Edicoes`
--
ALTER TABLE `Postagens_Edicoes`
  ADD CONSTRAINT `fk_edicao_postagem` FOREIGN KEY (`id_postagem`) REFERENCES `Postagens` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `Postagens_Midia`
--
ALTER TABLE `Postagens_Midia`
  ADD CONSTRAINT `fk_midia_postagem` FOREIGN KEY (`id_postagem`) REFERENCES `Postagens` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `Postagens_Salvas`
--
ALTER TABLE `Postagens_Salvas`
  ADD CONSTRAINT `fk_salvo_postagem` FOREIGN KEY (`id_postagem`) REFERENCES `Postagens` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_salvo_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_salvos_colecao` FOREIGN KEY (`colecao_id`) REFERENCES `Salvos_Colecoes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Post_Meta`
--
ALTER TABLE `Post_Meta`
  ADD CONSTRAINT `Post_Meta_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `Postagens` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Salvos_Colecoes`
--
ALTER TABLE `Salvos_Colecoes`
  ADD CONSTRAINT `fk_colecao_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Suporte_Chamados`
--
ALTER TABLE `Suporte_Chamados`
  ADD CONSTRAINT `fk_suporte_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Suporte_Mensagens`
--
ALTER TABLE `Suporte_Mensagens`
  ADD CONSTRAINT `fk_suporte_mensagem_chamado` FOREIGN KEY (`chamado_id`) REFERENCES `Suporte_Chamados` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Usuarios`
--
ALTER TABLE `Usuarios`
  ADD CONSTRAINT `fk_usuario_bairro` FOREIGN KEY (`id_bairro`) REFERENCES `Bairros` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `Usuarios_Recuperacao`
--
ALTER TABLE `Usuarios_Recuperacao`
  ADD CONSTRAINT `fk_recuperacao_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `Usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
