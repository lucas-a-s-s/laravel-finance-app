<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-emerald-700">Finance App</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">
                    Extrato da conta
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    {{ $account->name }}
                </p>
            </div>

            <a href="{{ route('accounts.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-4 md:grid-cols-4">
                <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <p class="text-sm font-medium text-gray-500">Saldo atual</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        {{ $account->currency }} {{ number_format((float) $account->current_balance, 2, ',', '.') }}
                    </p>
                </div>

                <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <p class="text-sm font-medium text-gray-500">Entradas filtradas</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-700">
                        R$ {{ number_format((float) $incomeTotal, 2, ',', '.') }}
                    </p>
                </div>

                <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <p class="text-sm font-medium text-gray-500">Saidas filtradas</p>
                    <p class="mt-2 text-2xl font-bold text-rose-700">
                        R$ {{ number_format((float) $expenseTotal, 2, ',', '.') }}
                    </p>
                </div>

                <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <p class="text-sm font-medium text-gray-500">Resultado filtrado</p>
                    <p @class([
                        'mt-2 text-2xl font-bold',
                        'text-emerald-700' => $netTotal >= 0,
                        'text-rose-700' => $netTotal < 0,
                    ])>
                        R$ {{ number_format($netTotal, 2, ',', '.') }}
                    </p>
                </div>
            </section>

            <section class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <form method="GET" action="{{ route('accounts.statement', $account) }}" class="grid gap-4 md:grid-cols-4">
                    <div>
                        <x-input-label for="date_from" :value="__('De')" />
                        <x-text-input id="date_from" name="date_from" type="date" class="mt-1 block w-full" :value="$filters['date_from'] ?? ''" />
                        <x-input-error class="mt-2" :messages="$errors->get('date_from')" />
                    </div>

                    <div>
                        <x-input-label for="date_to" :value="__('Ate')" />
                        <x-text-input id="date_to" name="date_to" type="date" class="mt-1 block w-full" :value="$filters['date_to'] ?? ''" />
                        <x-input-error class="mt-2" :messages="$errors->get('date_to')" />
                    </div>

                    <div class="flex flex-wrap items-end gap-3 md:col-span-2 md:justify-end">
                        <a href="{{ route('accounts.statement', $account) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                            Limpar
                        </a>

                        <x-primary-button>
                            Filtrar
                        </x-primary-button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Data</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Descricao</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Categoria</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tipo</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-500">Valor</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($transactions as $transaction)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                        {{ $transaction->transaction_date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ $transaction->description ?: 'Sem descricao' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                        {{ $transaction->category->name }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span @class([
                                            'rounded-md px-2 py-1 text-xs font-semibold',
                                            'bg-emerald-50 text-emerald-700' => $transaction->type->value === 'income',
                                            'bg-rose-50 text-rose-700' => $transaction->type->value === 'expense',
                                        ])>
                                            {{ $transaction->type->value === 'income' ? 'Receita' : 'Despesa' }}
                                        </span>
                                    </td>
                                    <td @class([
                                        'whitespace-nowrap px-6 py-4 text-right text-sm font-semibold',
                                        'text-emerald-700' => $transaction->type->value === 'income',
                                        'text-rose-700' => $transaction->type->value === 'expense',
                                    ])>
                                        {{ $transaction->type->value === 'income' ? '+' : '-' }}
                                        R$ {{ number_format((float) $transaction->amount, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                                        Nenhum movimento pago encontrado para esta conta.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($transactions->hasPages())
                    <div class="border-t border-gray-200 px-6 py-4">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </section>

            <section class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Auditoria de saldo</h3>
                    <p class="mt-1 text-sm text-gray-500">Ultimos movimentos que alteraram o saldo desta conta.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Data</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Operacao</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Lancamento</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-500">Antes</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-500">Impacto</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-500">Depois</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($balanceMovements as $movement)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                        {{ $movement->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span @class([
                                            'rounded-md px-2 py-1 text-xs font-semibold',
                                            'bg-emerald-50 text-emerald-700' => $movement->operation->value === 'applied',
                                            'bg-amber-50 text-amber-700' => $movement->operation->value === 'reversed',
                                        ])>
                                            {{ $movement->operation->value === 'applied' ? 'Aplicacao' : 'Reversao' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <p class="font-medium text-gray-900">
                                            {{ $movement->financialTransaction->description ?: 'Sem descricao' }}
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $movement->financialTransaction->category->name }}
                                        </p>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-600">
                                        R$ {{ number_format((float) $movement->balance_before, 2, ',', '.') }}
                                    </td>
                                    <td @class([
                                        'whitespace-nowrap px-6 py-4 text-right text-sm font-semibold',
                                        'text-emerald-700' => (float) $movement->impact_amount >= 0,
                                        'text-rose-700' => (float) $movement->impact_amount < 0,
                                    ])>
                                        {{ (float) $movement->impact_amount >= 0 ? '+' : '-' }}
                                        R$ {{ number_format(abs((float) $movement->impact_amount), 2, ',', '.') }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-gray-900">
                                        R$ {{ number_format((float) $movement->balance_after, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                                        Nenhum movimento de auditoria encontrado para esta conta.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
