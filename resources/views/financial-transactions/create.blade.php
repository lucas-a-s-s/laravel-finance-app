<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-emerald-700">Lancamentos</p>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                Novo lancamento
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if ($accounts->isEmpty() || $categories->isEmpty())
                <section class="rounded-lg border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900">
                    Cadastre ao menos uma conta ativa e uma categoria ativa antes de registrar lancamentos.
                </section>
            @endif

            <section class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <form method="POST" action="{{ route('financial-transactions.store') }}">
                    @include('financial-transactions.partials.form', [
                        'transaction' => null,
                        'submitLabel' => 'Cadastrar lancamento',
                    ])
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
