<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-emerald-700">Finance App</p>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                Painel financeiro
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            {{-- Cards de resumo --}}
            <section class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <p class="text-sm font-medium text-gray-500">Saldo atual</p>
                    <p class="mt-3 text-3xl font-bold text-gray-900">
                        R$ {{ number_format((float) $totalBalance, 2, ',', '.') }}
                    </p>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <p class="text-sm font-medium text-gray-500">Receitas do mês</p>
                    <p class="mt-3 text-3xl font-bold text-emerald-700">
                        R$ {{ number_format((float) $monthlyIncome, 2, ',', '.') }}
                    </p>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <p class="text-sm font-medium text-gray-500">Despesas do mês</p>
                    <p class="mt-3 text-3xl font-bold text-rose-700">
                        R$ {{ number_format((float) $monthlyExpense, 2, ',', '.') }}
                    </p>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
                {{-- Resumo semanal --}}
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Resumo dos últimos 7 dias</h3>
                            <p class="mt-1 text-sm text-gray-500">Receitas e despesas diárias</p>
                        </div>
                    </div>

                    @if (count($weeklySummary) > 0)
                        <div class="mt-6 space-y-3">
                            @foreach ($weeklySummary as $day)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">
                                        {{ \Carbon\Carbon::parse($day['date'])->format('d/m') }}
                                    </span>
                                    <div class="flex items-center gap-4">
                                        <span class="text-sm font-medium text-emerald-600">
                                            +R$ {{ number_format((float) $day['income'], 2, ',', '.') }}
                                        </span>
                                        <span class="text-sm font-medium text-rose-600">
                                            -R$ {{ number_format((float) $day['expense'], 2, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="mt-6 h-32 rounded-md border border-dashed border-gray-300 bg-gray-50 flex items-center justify-center">
                            <span class="text-sm text-gray-500">Sem movimentação nos últimos 7 dias</span>
                        </div>
                    @endif
                </div>

                {{-- Despesas por categoria e base do sistema --}}
                <div class="space-y-6">
                    {{-- Despesas por categoria --}}
                    <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <h3 class="text-base font-semibold text-gray-900">Despesas por categoria</h3>
                        <p class="mt-1 text-sm text-gray-500">Este mês</p>

                        @if (count($expensesByCategory) > 0)
                            <div class="mt-5 space-y-4">
                                @foreach ($expensesByCategory as $category)
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-700">{{ $category['name'] }}</span>
                                            <span class="text-sm font-medium text-gray-900">
                                                R$ {{ number_format((float) $category['amount'], 2, ',', '.') }}
                                            </span>
                                        </div>
                                        <div class="mt-1 h-2 w-full rounded-full bg-gray-200">
                                            <div
                                                class="h-2 rounded-full bg-rose-500"
                                                style="width: {{ min($category['percentage'], 100) }}%"
                                            ></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-5 h-24 rounded-md border border-dashed border-gray-300 bg-gray-50 flex items-center justify-center">
                                <span class="text-sm text-gray-500">Sem despesas neste mês</span>
                            </div>
                        @endif
                    </div>

                    {{-- Base do sistema --}}
                    <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <h3 class="text-base font-semibold text-gray-900">Base do sistema</h3>

                        <dl class="mt-5 space-y-4">
                            <div class="flex items-center justify-between">
                                <dt class="text-sm text-gray-500">Contas cadastradas</dt>
                                <dd class="text-sm font-semibold text-gray-900">{{ $activeAccountsCount }}</dd>
                            </div>

                            <div class="flex items-center justify-between">
                                <dt class="text-sm text-gray-500">Categorias cadastradas</dt>
                                <dd class="text-sm font-semibold text-gray-900">{{ $activeCategoriesCount }}</dd>
                            </div>

                            <div class="flex items-center justify-between">
                                <dt class="text-sm text-gray-500">Lançamentos registrados</dt>
                                <dd class="text-sm font-semibold text-gray-900">{{ $transactionsCount }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
