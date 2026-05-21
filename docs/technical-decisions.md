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

As entidades financeiras foram modeladas, mas ainda nao existe CRUD. A proxima etapa deve implementar o CRUD de contas bancarias com validacao, autorizacao basica por usuario autenticado e testes de fluxo.
