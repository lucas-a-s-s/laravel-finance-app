<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-emerald-700">Finance App</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">
                    Lancamentos
                </h2>
            </div>

            <a href="{{ route('financial-transactions.create') }}" class="inline-flex items-center justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800">
                Novo lancamento
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <section class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <p class="text-sm font-medium text-gray-500">Lancamentos</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $transactions->total() }}</p>
                </div>

                <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <p class="text-sm font-medium text-gray-500">Receitas pagas</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-700">
                        R$ {{ number_format((float) $paidIncomeTotal, 2, ',', '.') }}
                    </p>
                </div>

                <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <p class="text-sm font-medium text-gray-500">Despesas pagas</p>
                    <p class="mt-2 text-2xl font-bold text-rose-700">
                        R$ {{ number_format((float) $paidExpenseTotal, 2, ',', '.') }}
                    </p>
                </div>
            </section>

            <section class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <form method="GET" action="{{ route('financial-transactions.index') }}" class="grid gap-4 lg:grid-cols-6">
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

                    <div>
                        <x-input-label for="type" :value="__('Tipo')" />
                        <select id="type" name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Todos</option>

                            @foreach ($transactionTypes as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['type'] ?? null) === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('type')" />
                    </div>

                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Todos</option>

                            @foreach ($transactionStatuses as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['status'] ?? null) === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('status')" />
                    </div>

                    <div>
                        <x-input-label for="account_id" :value="__('Conta')" />
                        <select id="account_id" name="account_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Todas</option>

                            @foreach ($filterAccounts as $account)
                                <option value="{{ $account->id }}" @selected((string) ($filters['account_id'] ?? '') === (string) $account->id)>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('account_id')" />
                    </div>

                    <div>
                        <x-input-label for="category_id" :value="__('Categoria')" />
                        <select id="category_id" name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Todas</option>

                            @foreach ($transactionTypes as $typeValue => $typeLabel)
                                <optgroup label="{{ $typeLabel }}">
                                    @foreach ($filterCategories as $category)
                                        @if ($category->type->value === $typeValue)
                                            <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>
                                                {{ $category->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
                    </div>

                    <div class="flex flex-wrap items-center justify-end gap-3 lg:col-span-6">
                        <a href="{{ route('financial-transactions.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
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
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Conta</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Categoria</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-500">Valor</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-500">Acoes</th>
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
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ $transaction->account->name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                        {{ $transaction->category->name }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span @class([
                                            'rounded-md px-2 py-1 text-xs font-semibold',
                                            'bg-gray-100 text-gray-600' => $transaction->isCancelled(),
                                            'bg-emerald-50 text-emerald-700' => ! $transaction->isCancelled() && $transaction->is_paid,
                                            'bg-amber-50 text-amber-700' => ! $transaction->isCancelled() && ! $transaction->is_paid,
                                        ])>
                                            @if ($transaction->isCancelled())
                                                Cancelado
                                            @else
                                                {{ $transaction->is_paid ? 'Pago' : 'Pendente' }}
                                            @endif
                                        </span>
                                    </td>
                                    <td @class([
                                        'whitespace-nowrap px-6 py-4 text-right text-sm font-semibold',
                                        'text-gray-500' => $transaction->isCancelled(),
                                        'text-emerald-700' => ! $transaction->isCancelled() && $transaction->type->value === 'income',
                                        'text-rose-700' => ! $transaction->isCancelled() && $transaction->type->value === 'expense',
                                    ])>
                                        {{ $transaction->type->value === 'income' ? '+' : '-' }}
                                        R$ {{ number_format((float) $transaction->amount, 2, ',', '.') }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                        @if ($transaction->isCancelled())
                                            <span class="text-gray-500">Sem acoes</span>
                                        @else
                                            <div class="flex justify-end gap-3">
                                                <a href="{{ route('financial-transactions.edit', $transaction) }}" class="text-emerald-700 transition hover:text-emerald-900">
                                                    Editar
                                                </a>

                                                <form method="POST" action="{{ route('financial-transactions.cancel', $transaction) }}">
                                                    @csrf
                                                    @method('PATCH')

                                                    <button type="submit" class="text-rose-700 transition hover:text-rose-900">
                                                        Cancelar
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500">
                                        Nenhum lancamento cadastrado.
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
        </div>
    </div>
</x-app-layout>
