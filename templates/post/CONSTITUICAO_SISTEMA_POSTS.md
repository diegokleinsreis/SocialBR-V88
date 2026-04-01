# 🏛️ Constituição do Sistema de Postagens (Versão 1.0)

Este documento define as regras sagradas de arquitetura para o feed e postagens. Nenhuma alteração de código deve violar estas diretrizes.

## 1. Filosofia de Arquitetura: Componentização Atômica
- **Regra de Ouro**: O arquivo `post_template.php` é APENAS um esqueleto (orquestrador).
- **Isolamento**: Cada parte visual deve residir em seu próprio arquivo na pasta `templates/post/`.
- **Objetivo**: Garantir que a manutenção em um componente (ex: botões) jamais afete outro (ex: vídeos).

## 2. Estrutura de Arquivos e Nomenclatura
- **Novos Componentes**: Devem ser nomeados em PORTUGUÊS (ex: `cabecalho.php`, `botoes_acao.php`).
- **Módulos Específicos (Legado/Consolidados)**: Mantêm nomes originais em INGLÊS (`post_poll.php`, `post_marketplace_card.php`, `post_shared_content.php`).
- **Localização**: 
    - Esqueleto: `templates/post_template.php`
    - Sub-componentes: `templates/post/`

## 3. Regras de Blindagem de Caminhos (Path Blindage)
- Toda API ou inclusão de arquivo deve usar o **Localizador de Caminhos Robusto**.
- Deve-se testar múltiplos níveis (`../`, `../../`, `../../../`) para encontrar a pasta `config/` e `src/`, garantindo funcionamento fora da `public_html`.

## 4. Separação de Lógica e View
- O Template (PHP) deve ser "burro": ele apenas exibe dados (`echo`).
- Cálculos complexos, formatação de datas e validações de permissão devem ser feitos ANTES da inclusão do template ou via classes em `src/`.

## 5. Integridade Visual e CSS
- **Z-Index**: Modais de interação devem possuir `z-index: 100000 !important` para sobrepor o Lightbox de fotos.
- **IDs Únicos**: Nunca usar IDs genéricos como `lightbox-modal` para comentários; usar `comment-interaction-modal` para evitar conflitos com o visualizador de imagens.

## 6. Comportamento AJAX e Segurança
- Todos os formulários de postagem/comentário devem incluir o `get_csrf_token()`.
- Interações (Votos, Curtidas, Comentários) não devem recarregar a página; devem usar o motor `apiFetch` definido no `main.js`.

## 7. Cláusula Pétrea (Não Simplificação)
- A IA jamais deve remover comentários de versão, simplificar lógicas de grid de mídia ou ignorar o tratamento de erros em APIs.