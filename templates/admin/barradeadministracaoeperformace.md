# Manifesto: Sistema de Gestão e Monitorização Multi-Admin (V2)

## 1. Definição de Acesso (CORRIGIDO)
* **Critério:** A visibilidade das ferramentas administrativas é expandida para qualquer utilizador com o cargo de administrador.
* **Validação:** Verificação via sessão PHP (`$_SESSION['user_role'] === 'admin'`).

## 2. Monitor de Performance (Seguro & Leve)
* **Tempo de Resposta (TTFB):** Mede o tempo total de execução do PHP em milissegundos (ms).
* **Pico de Memória:** Exibe o consumo máximo de RAM utilizado pelo script (MB).
* **Carga do Servidor:** Mostra a média de processamento do processador (Load Average).

## 3. Segurança na Simulação ("Ver Como")
* A funcionalidade permite testar a interface visual sob diferentes perspetivas.
* Ações de escrita (API) permanecem protegidas, validando sempre o utilizador real para evitar riscos de integridade nos dados.

## 4. Estética de Alerta
* A barra deve manter a cor de destaque (laranja) quando em modo de simulação para que o administrador saiba sempre que não está na visão "Real".