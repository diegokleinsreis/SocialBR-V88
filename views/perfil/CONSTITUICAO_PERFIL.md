# 📜 CONSTITUIÇÃO DO PERFIL — socialbr.lol


## 2. PILARES DA ARQUITETURA (SOOC)
* **Orquestração Cega:** O arquivo `views/perfil.php` é o único responsável pela lógica de banco de dados, verificação de amizade e bloqueios.
* **Pureza dos Componentes:** Os componentes dentro desta pasta não fazem consultas SQL complexas; eles apenas renderizam os dados injetados pelo orquestrador.
* **Blindagem de Erros:** A falha no carregamento de uma "aba" (ex: fotos) não deve impedir a visualização do cabeçalho ou das informações básicas do usuário.

## 3. STACK TÉCNICA E REGRAS DE OURO
* **PHP 8.x:** Uso de null coalescing (`??`) e tipagem rigorosa.
* **Zero Inline:** Proibido o uso de `style="..."` ou `onclick="..."`. Toda estética e comportamento devem estar no CSS/JS externo.
* **Caminhos Robustos:** Inclusões e links devem obrigatoriamente usar a constante `$config['base_path']`.
* **Segurança:** Toda saída de texto deve ser tratada com `htmlspecialchars()` para prevenir XSS.

## 4. ESTRUTURA DE COMPONENTES
A página de perfil é dividida em quatro camadas:

### A. Identidade (Topo)
- `capa_perfil.php`: Gestão visual da cobertura.
- `identidade_perfil.php`: Avatar e molduras de destaque.
- `informacoes_topo.php`: Nome, @username e biografia.
- `acoes_relacionamento.php`: Botões dinâmicos (Seguir, Mensagem, Configurar).

### B. Navegação
- `menu_abas.php`: Roteador visual das seções do perfil.

### C. Estados de Segurança
- `perfil_privado.php`: View de conta restrita.
- `perfil_bloqueado.php`: View para usuários bloqueados.
- `convite_login.php`: View para visitantes não autenticados.

### D. Conteúdo Dinâmico (Pasta: /abas)
- `aba_postagens.php`: Feed de publicações do usuário.
- `aba_sobre.php`: Dados detalhados e biografia.
- `aba_fotos.php`: Galeria de mídias.
- `aba_amigos.php`: Listagem de conexões.

## 5. CONTRATO DE VARIÁVEIS (O que o Orquestrador entrega)
Para que um componente funcione, o `perfil.php` deve garantir:
- `$perfil_data`: Dados brutos do banco de dados.
- `$is_own_profile`: Booleano (é o meu perfil?).
- `$is_friend`: Booleano (somos amigos?).
- `$pode_ver_conteudo`: Booleano (permissão final de visualização).

---
**Assinado:** Arquiteto de Software Sênior & Guardião da Arquitetura.