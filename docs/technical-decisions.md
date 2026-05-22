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
