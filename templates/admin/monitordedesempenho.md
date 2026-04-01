# Manifesto: Sistema de Gestão e Monitorização Admin

## 1. Definição de Acesso
* **Critério:** A visibilidade de todas as ferramentas administrativas (Barra "Ver Como" e Monitor de Performance) é restrita a utilizadores com a função de administrador.
* **Validação:** Verificação via sessão PHP (`$_SESSION['role'] === 'admin'`).

## 2. Funcionalidade "Ver Como" (Simulação)
* **Objetivo:** Permitir que administradores visualizem perfis sob diferentes perspectivas (Dono, Amigo, Visitante, Bloqueado).
* **Segurança:** A simulação altera apenas a camada visual (front-end). Ações de escrita (APIs) continuam a validar o ID real do utilizador para evitar falhas de integridade.

## 3. Monitor de Performance (Tempo, RAM e CPU)
* **Tempo de Resposta:** Cálculo do tempo de execução do PHP desde o 'header' até à renderização final.
* **Memória RAM:** Exibição do pico de consumo de memória do script atual.
* **Carga de CPU:** Monitorização da carga média de processamento do servidor.

## 4. Estética e UX
* A barra deve ser fixa no topo, com cores de alerta (laranja/escuro) e totalmente responsiva.