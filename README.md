# Finance App

Sistema financeiro pessoal desenvolvido com Laravel, criado como projeto real de portfólio para praticar arquitetura moderna PHP, boas práticas de desenvolvimento web e evolução incremental de produto.

## Objetivo

O Finance App tem como objetivo permitir que usuários acompanhem sua vida financeira de forma simples, organizada e segura. A aplicação será construída em etapas pequenas, priorizando qualidade de código, clareza arquitetural e decisões próximas de um ambiente de produção.

## Funcionalidades Planejadas

- Cadastro e autenticação de usuários
- Controle de contas bancárias
- Cadastro de receitas e despesas
- Categorias financeiras
- Dashboard com resumo financeiro
- Relatórios e filtros por período
- Controle de saldo
- API REST
- Notificações e recorrência em etapas futuras

## Stack

- PHP 8+
- Laravel 10+
- MySQL
- Blade
- API REST
- Git e GitHub
- Docker em etapa futura
- PHPUnit em etapa futura

## Arquitetura Inicial

O projeto começa utilizando a estrutura MVC padrão do Laravel:

- `app/`: regras principais da aplicação, models, providers e futuramente services/actions.
- `app/Http/Controllers/`: controllers responsáveis por receber requisições e devolver respostas.
- `app/Http/Requests/`: validações dedicadas para entradas HTTP.
- `database/`: migrations, seeders e factories responsáveis pela estrutura e dados de apoio.
- `resources/views/`: telas Blade renderizadas no servidor.
- `routes/`: definição das rotas web e API.
- `config/`: arquivos de configuração da aplicação.
- `public/`: ponto de entrada HTTP da aplicação.
- `tests/`: testes automatizados da aplicação.

Conforme o sistema crescer, responsabilidades serão separadas de forma incremental, evitando antecipar complexidade sem necessidade.

## Princípios Técnicos

- Código legível e organizado
- Separação clara de responsabilidades
- Uso correto de migrations e Eloquent
- Validações em pontos de entrada
- Segurança básica desde o início
- Evolução incremental por commits pequenos
- Documentação das decisões importantes

## Como Executar Localmente

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
php artisan serve
```

Depois, acesse:

```text
http://127.0.0.1:8000
```

## Status

Etapa atual: CRUD inicial de categorias implementado com Blade, validação e testes.

## Ja Implementado

- Base Laravel 10 criada
- Repositorio Git inicializado
- Banco MySQL `finance_app` configurado
- Migrations iniciais executadas
- Autenticacao web com Laravel Breeze e Blade
- Telas iniciais de entrada, login, cadastro, painel e perfil
- Models `Account`, `Category` e `FinancialTransaction`
- Enum `TransactionType` para receitas e despesas
- Migrations financeiras com relacionamentos, indices e valores monetarios em `decimal`
- Factories para entidades financeiras
- Testes de dominio financeiro
- CRUD web inicial de contas
- Form Requests para validacao de contas
- Protecao de contas por usuario autenticado
- Desativacao de contas sem exclusao fisica
- CRUD web inicial de categorias
- Validacao de categorias por tipo financeiro
- Protecao de categorias por usuario autenticado
- Controle visual de cor e icone de categorias
- Desativacao de categorias sem exclusao fisica
- Build frontend com Vite e Tailwind CSS
- Testes automatizados iniciais passando

## Proximas Etapas

1. Implementar CRUD inicial de lancamentos financeiros.
2. Validar conta, categoria e tipo do lancamento por usuario autenticado.
3. Atualizar saldo da conta com consistencia ao criar, editar ou cancelar lancamentos.
4. Criar filtros iniciais por periodo e tipo.
5. Cobrir fluxo de lancamentos com testes Feature.

## Licenca

Este projeto e desenvolvido para fins educacionais e de portfólio.
