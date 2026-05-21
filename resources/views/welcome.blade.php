<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Finance App') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <main class="min-h-screen bg-slate-950 text-white">
            <header class="mx-auto flex max-w-7xl items-center justify-between px-6 py-6 lg:px-8">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <x-application-logo class="h-10 w-10 text-emerald-400" />
                    <span class="text-lg font-semibold tracking-normal">{{ config('app.name', 'Finance App') }}</span>
                </a>

                @if (Route::has('login'))
                    <nav class="flex items-center gap-3">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-emerald-100">
                                Painel
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-slate-200 transition hover:text-white">
                                Entrar
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="rounded-md bg-emerald-400 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-emerald-300">
                                    Criar conta
                                </a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </header>

            <section class="mx-auto grid min-h-[calc(100vh-96px)] max-w-7xl items-center gap-12 px-6 pb-16 pt-8 lg:grid-cols-[1fr_0.95fr] lg:px-8">
                <div class="max-w-2xl">
                    <p class="mb-4 text-sm font-semibold uppercase tracking-widest text-emerald-300">Financas pessoais</p>
                    <h1 class="text-4xl font-bold tracking-normal text-white sm:text-5xl lg:text-6xl">
                        Controle financeiro pessoal com clareza e consistencia.
                    </h1>
                    <p class="mt-6 text-lg leading-8 text-slate-300">
                        Uma aplicacao Laravel para organizar contas, receitas, despesas, categorias e relatorios em uma experiencia simples e segura.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex justify-center rounded-md bg-emerald-400 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-300">
                                Acessar painel
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="inline-flex justify-center rounded-md bg-emerald-400 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-300">
                                Comecar agora
                            </a>
                            <a href="{{ route('login') }}" class="inline-flex justify-center rounded-md border border-slate-700 px-5 py-3 text-sm font-semibold text-white transition hover:border-emerald-300">
                                Ja tenho conta
                            </a>
                        @endauth
                    </div>
                </div>

                <div class="rounded-lg border border-white/10 bg-white/[0.04] p-5 shadow-2xl shadow-emerald-950/30">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-400">Saldo atual</p>
                            <p class="mt-1 text-3xl font-bold">R$ 0,00</p>
                        </div>
                        <span class="rounded-md bg-emerald-400/15 px-3 py-1 text-sm font-semibold text-emerald-200">Base inicial</span>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-md bg-slate-900/80 p-4">
                            <p class="text-sm text-slate-400">Receitas</p>
                            <p class="mt-2 text-2xl font-semibold text-emerald-300">R$ 0,00</p>
                        </div>
                        <div class="rounded-md bg-slate-900/80 p-4">
                            <p class="text-sm text-slate-400">Despesas</p>
                            <p class="mt-2 text-2xl font-semibold text-rose-300">R$ 0,00</p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-3">
                        <div class="flex items-center justify-between rounded-md bg-slate-900/80 px-4 py-3">
                            <span class="text-sm text-slate-300">Conta principal</span>
                            <span class="text-sm font-semibold text-white">R$ 0,00</span>
                        </div>
                        <div class="flex items-center justify-between rounded-md bg-slate-900/80 px-4 py-3">
                            <span class="text-sm text-slate-300">Categorias ativas</span>
                            <span class="text-sm font-semibold text-white">0</span>
                        </div>
                        <div class="flex items-center justify-between rounded-md bg-slate-900/80 px-4 py-3">
                            <span class="text-sm text-slate-300">Lancamentos do mes</span>
                            <span class="text-sm font-semibold text-white">0</span>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
