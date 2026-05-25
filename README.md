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
- PHPUnit

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

Etapa atual: extrato por conta com movimentos pagos.

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
- Cadastro e listagem web inicial de lancamentos financeiros
- Validacao de conta e categoria por usuario autenticado
- Validacao de compatibilidade entre tipo do lancamento e tipo da categoria
- Acao de dominio para criar lancamento e atualizar saldo da conta
- Lancamentos pendentes sem impacto imediato no saldo
- Edicao de lancamentos com reversao do saldo anterior antes de aplicar o novo estado
- Reconciliacao de saldo ao alterar valor, conta, tipo ou status pago do lancamento
- Cancelamento de lancamentos sem exclusao fisica do historico
- Reversao de saldo ao cancelar lancamentos pagos
- Filtros de lancamentos por periodo, tipo, conta, categoria e status
- Filtro dinamico de categorias no formulario conforme tipo escolhido (JavaScript)
- Dashboard com dados reais:
  - Saldo total das contas ativas
  - Receitas e despesas do mes
  - Contadores de contas, categorias e lancamentos
  - Resumo semanal dos ultimos 7 dias
  - Despesas por categoria com barras de progresso
- Extrato por conta com:
  - Movimentos pagos e nao cancelados
  - Filtro por periodo
  - Totais de entradas, saidas e resultado filtrado
  - Isolamento por usuario autenticado
- Build frontend com Vite e Tailwind CSS
- 79 testes automatizados passando (259 assercoes)

## Proximas Etapas

1. Evoluir auditoria de saldo com tabela propria de movimentos de conta.
2. Definir se lancamentos cancelados poderao ser reabertos em etapa futura.
3. Preparar a primeira camada da API REST com autenticacao adequada.

## Licenca

Este projeto e desenvolvido para fins educacionais e de portfólio.
