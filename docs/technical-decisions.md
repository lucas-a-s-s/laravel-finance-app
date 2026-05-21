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

## 5. Evolucao incremental

As entidades financeiras ainda nao foram criadas. A proxima etapa deve modelar contas, categorias e lancamentos antes de qualquer tela de CRUD, para evitar retrabalho na regra de negocio.
