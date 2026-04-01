# Constituição: Central de Diagnóstico e Moderação Admin

## 1. Monitor de Integridade (Denúncias)
* **Objetivo:** Identificar perfis com comportamento suspeito diretamente na navegação.
* **Funcionamento:** Consulta read-only na tabela `Denuncias` filtrando pelo ID do perfil alvo.

## 2. Ferramentas de UI Debug (Desenvolvimento)
* **Objetivo:** Facilitar o ajuste fino do CSS em diferentes dispositivos.
* **Funcionalidade:** Interruptor 'Debug CSS' que injeta um contorno (outline) temporário via JavaScript em todas as `divs`.

## 3. Métricas de Banco de Dados
* **Objetivo:** Evitar sobrecarga na hospedagem monitorando o número de queries.
* **Funcionamento:** Exibição do contador de conexões ativas no script atual.

## 4. Segurança de Execução
* As consultas de moderação são estritamente de leitura (SELECT).
* O modo Debug de CSS é executado apenas no navegador do Administrador, não afetando outros usuários.