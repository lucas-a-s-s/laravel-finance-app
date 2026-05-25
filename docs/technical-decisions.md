# Decisoes Tecnicas

Este arquivo registra decisoes importantes para manter contexto historico do projeto.

## 1. Laravel 10 como base

O projeto usa Laravel 10 para manter estabilidade, ampla compatibilidade com pacotes e boa aderencia ao mercado PHP/Laravel.

## 2. Blade na primeira fase

Blade foi escolhido para a interface inicial porque reduz complexidade frontend, facilita aprendizado do fluxo MVC do Laravel e permite entregar telas server-side com rapidez.

## 3. Laravel Breeze para autenticacao

Laravel Breeze foi escolhido por ser um starter kit oficial, simples e transparente. Ele gera controllers, requests, rotas, views e testes de autenticacao sem esconder a arquitetura do Laravel.

## 4. MySQL como banco local

O banco local configurado e `finance_app`, usando `utf8mb4` e `utf8mb4_unicode_ci` para suportar textos e simbolos com seguranca.

## 5. Modelagem inicial do dominio financeiro

As primeiras entidades do dominio financeiro sao `Account`, `Category` e `FinancialTransaction`.

- `Account` representa uma conta, carteira ou cartao controlado pelo usuario.
- `Category` classifica receitas e despesas.
- `FinancialTransaction` representa um lancamento financeiro vinculado a usuario, conta e categoria.

Todas as entidades pertencem a um usuario, garantindo isolamento basico de dados desde o inicio.

Contas e categorias possuem `is_active` para permitir desativacao no fluxo da aplicacao. Exclusao fisica deve ser evitada nas telas futuras para preservar historico financeiro.

## 6. Valores monetarios com decimal

Valores financeiros usam `decimal(15, 2)` no banco e cast `decimal:2` no Eloquent. Isso evita problemas de precisao comuns em tipos flutuantes como `float` e `double`.

## 7. Tipo de lancamento com enum

O tipo de categoria e de lancamento usa o enum `TransactionType`, com os valores `income` e `expense`. Essa decisao evita strings soltas espalhadas pelo codigo e melhora legibilidade, validacao interna e manutencao.

## 8. Evolucao incremental

O CRUD inicial de contas foi implementado antes de categorias e lancamentos porque contas sao a base do controle de saldo.

As validacoes ficam em `StoreAccountRequest` e `UpdateAccountRequest`, mantendo controllers focados no fluxo HTTP. As consultas e alteracoes sao sempre filtradas pelo usuario autenticado, evitando acesso cruzado entre usuarios.

Contas sao desativadas em vez de excluidas fisicamente. Essa decisao preserva historico financeiro para quando os lancamentos forem implementados.

A proxima etapa deve implementar categorias, mantendo o mesmo padrao de Form Requests, isolamento por usuario e testes Feature.

## 9. CRUD inicial de categorias

Categorias seguem o padrao de contas: `CategoryController` coordena o fluxo HTTP e `StoreCategoryRequest` e `UpdateCategoryRequest` centralizam validacao.

O nome e unico por usuario e tipo financeiro. Isso permite que um usuario tenha, por exemplo, uma categoria de despesa e outra de receita com o mesmo nome quando isso fizer sentido no dominio.

Cor e icone entram no cadastro para preparar leitura visual do dashboard e dos lancamentos. A proxima etapa deve implementar lancamentos financeiros com validacao cruzada entre usuario, conta, categoria e tipo, alem de definir como o saldo de contas sera atualizado com consistencia.

## 10. Cadastro inicial de lancamentos financeiros

O primeiro fluxo de lancamentos implementa listagem e cadastro antes de edicao e exclusao. Essa escolha reduz risco, pois alterar um lancamento pago exige reverter seu efeito anterior no saldo antes de aplicar o novo estado.

`CreateFinancialTransaction` concentra a criacao do lancamento e a atualizacao de saldo dentro de uma transacao de banco. A conta e travada com `lockForUpdate`, receitas pagas incrementam saldo, despesas pagas decrementam saldo e lancamentos pendentes nao alteram saldo imediato.

`StoreFinancialTransactionRequest` valida que conta e categoria pertencem ao usuario autenticado, estao ativas e que o tipo da categoria combina com o tipo do lancamento. O proximo passo deve manter essas garantias no fluxo de edicao e na estrategia de cancelamento ou exclusao.

## 11. Edicao de lancamentos e reconciliacao de saldo

Editar um lancamento pago altera um fato que ja impactou saldo. Por isso `UpdateFinancialTransaction` executa a operacao em transacao de banco: trava o lancamento, trava as contas envolvidas, reverte o efeito anterior e aplica o novo estado persistido.

`AdjustAccountBalanceForTransaction` concentra a regra de aplicar e reverter impacto de receitas e despesas pagas. O cadastro e a edicao usam a mesma regra para reduzir divergencia entre fluxos que alteram saldo.

`UpdateFinancialTransactionRequest` mantem validacao de posse, tipo e referencias ativas. Na edicao, a conta e a categoria ja associadas ao lancamento continuam disponiveis mesmo se tiverem sido desativadas depois, preservando manutencao controlada do historico financeiro.

O proximo passo deve definir uma estrategia segura para cancelamento ou exclusao de lancamentos, evitando perder rastreabilidade ou deixar o saldo inconsistente.

## 12. Cancelamento explicito de lancamentos

Nesta fase, lancamentos financeiros nao sao excluidos fisicamente nem usam exclusao silenciosa. O sistema registra `cancelled_at` para preservar historico visivel e deixar claro que o fato financeiro foi cancelado.

`CancelFinancialTransaction` executa o cancelamento em transacao de banco, trava o lancamento e a conta associada, reverte o impacto anterior no saldo quando o lancamento estava pago e grava o instante do cancelamento. A acao e idempotente: cancelar o mesmo lancamento novamente nao reverte saldo duas vezes.

Lancamentos cancelados continuam na listagem com status proprio, deixam de compor totais pagos e nao podem mais ser editados. Essa regra reduz risco de reintroduzir impacto financeiro sem uma operacao explicita de reabertura ou estorno.

O proximo passo pode criar filtros por periodo, tipo, conta e categoria para melhorar leitura operacional da listagem antes de evoluir relatorios e dashboard.

## 13. Filtros iniciais da listagem de lancamentos

`FilterFinancialTransactionsRequest` valida os parametros de consulta da listagem. Contas e categorias usadas no filtro precisam pertencer ao usuario autenticado, evitando que IDs externos sejam aceitos mesmo em uma operacao somente de leitura.

O controller reaproveita a mesma query filtrada para paginacao e totais pagos. Assim, o resumo de receitas e despesas acompanha o recorte visivel por periodo, tipo, conta, categoria e status.

Os filtros permanecem na URL via requisicao `GET` e a paginacao preserva a query string. Essa escolha facilita compartilhar recortes de leitura e prepara a listagem para evoluir para relatorios sem introduzir estado de sessao para filtros.

O proximo passo pode melhorar a selecao de categorias no formulario de lancamentos para reduzir combinacoes invalidas entre tipo financeiro e categoria.

## 14. Filtro dinamico de categorias no formulario de lancamentos

O formulario de lancamentos agora filtra as categorias exibidas conforme o tipo financeiro escolhido (receita ou despesa). Essa melhoria de interface usa JavaScript simples (Vanilla JS) incluso diretamente na view do formulario.

Quando o usuario alterna o tipo de lancamento, as opcoes de categorias incompativeis sao ocultadas e desabilitadas, evitando selecoes invalidas. Se uma categoria ja estiver selecionada e o tipo for alterado para um incompativel, a selecao e limpa automaticamente.

A validacao backend continua existindo e e essencial: mesmo com o filtro visual, o `StoreFinancialTransactionRequest` e `UpdateFinancialTransactionRequest` validam que a categoria pertence ao usuario, esta ativa e combina com o tipo do lancamento. Essa defesa em profundidade garante integridade mesmo se JavaScript estiver desabilitado ou se requisicoes forem feitas diretamente via API.

O script e contido em uma funcao anonima auto-executavel para evitar poluicao do escopo global e e executado tanto no carregamento da pagina (para edicao) quanto ao mudar o tipo. Essa abordagem incremental melhora a experiencia do usuario sem introduzir dependencias adicionais como Alpine.js ou frameworks frontend mais pesados, mantendo coerencia com a stack Blade + Vite + Tailwind.

## 15. Dashboard com dados reais do dominio financeiro

O dashboard foi evoluído de uma tela estática para uma view dinâmica que exibe dados reais do domínio financeiro do usuário. Foi criado um `DashboardController` dedicado que calcula e passa as seguintes informações para a view:

- **Saldo total**: soma dos saldos de todas as contas ativas do usuário
- **Receitas do mês**: total de receitas pagas e não canceladas no mês atual
- **Despesas do mês**: total de despesas pagas e não canceladas no mês atual
- **Contas ativas**: contador de contas ativas
- **Categorias ativas**: contador de categorias ativas
- **Lançamentos registrados**: total de lançamentos não cancelados
- **Resumo semanal**: receitas e despesas dos últimos 7 dias, agrupadas por dia
- **Despesas por categoria**: top 5 categorias de despesas do mês com porcentagem

Todas as consultas são filtradas pelo usuário autenticado, garantindo isolamento de dados. Lançamentos cancelados são excluídos dos totais de receitas e despesas, assim como lançamentos pendentes. O resumo semanal sempre mostra os últimos 7 dias, mesmo que não haja movimentação.

A view foi atualizada para exibir esses dados de forma clara, com formatação monetária adequada e barras de progresso para as despesas por categoria. Em estados vazios, mensagens apropriadas são exibidas.

Testes foram criados para validar:
- Redirecionamento de guests
- Exibicao correta de saldos, receitas e despesas
- Contagem de contas, categorias e lancamentos (excluindo cancelados)
- Isolamento de dados entre usuarios
- Estados vazios

## 16. Extrato inicial por conta

O extrato por conta foi implementado como uma tela de leitura baseada nos lancamentos financeiros ja existentes. Nesta primeira etapa, o extrato lista somente lancamentos pagos e nao cancelados, pois apenas eles representam movimentos que impactam o saldo atual da conta.

Foi criado um `AccountStatementController` invocavel para manter o `AccountController` focado no CRUD de contas. A validacao de periodo fica em `FilterAccountStatementRequest`, mantendo a entrada HTTP separada da consulta de dominio e seguindo o mesmo padrao usado na listagem de lancamentos.

A rota `accounts/{account}/statement` exige autenticacao e valida que a conta pertence ao usuario logado antes de exibir qualquer dado. Os totais de entradas, saidas e resultado filtrado usam a mesma query base do extrato, garantindo que os cards acompanhem o periodo selecionado.

Esta decisao entrega valor de produto sem criar uma estrutura de auditoria complexa antes da hora. A proxima evolucao natural e criar uma tabela propria de movimentos de conta, registrando operacoes de aplicacao, reversao e cancelamento de saldo com rastreabilidade mais forte.

## 17. Auditoria inicial de movimentos de saldo

Foi criada a tabela `account_balance_movements` para registrar cada alteracao real no saldo de uma conta. A tabela guarda usuario, conta, lancamento financeiro, operacao executada, tipo do lancamento, valor original, impacto assinado no saldo, saldo anterior e saldo posterior.

O model `AccountBalanceMovement` representa essa trilha tecnica de auditoria. A operacao usa o enum `AccountBalanceMovementOperation`, com os valores `applied` e `reversed`, evitando strings soltas no codigo e deixando claro se o saldo foi aplicado ou revertido.

A regra foi integrada em `AdjustAccountBalanceForTransaction`, que ja era o ponto unico de aplicacao e reversao de saldo. Assim, criacao, edicao e cancelamento continuam usando a mesma regra centralizada, e a auditoria nasce dentro da mesma transacao de banco que altera o saldo da conta.

Lancamentos pendentes nao geram movimentos de saldo, pois ainda nao afetam o saldo atual. Lancamentos pagos geram movimento de aplicacao. Edicoes de lancamentos pagos podem gerar uma reversao do estado anterior e uma nova aplicacao. Cancelamentos geram uma reversao apenas uma vez, preservando a idempotencia ja existente.

Essa etapa ainda nao cria uma tela propria de auditoria. A decisao foi primeiro garantir consistencia dos dados e cobertura automatizada. A proxima evolucao pode expor esses movimentos no extrato da conta ou em uma tela tecnica separada.
