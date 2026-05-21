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
            <section class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <p class="text-sm font-medium text-gray-500">Saldo atual</p>
                    <p class="mt-3 text-3xl font-bold text-gray-900">R$ 0,00</p>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <p class="text-sm font-medium text-gray-500">Receitas do mes</p>
                    <p class="mt-3 text-3xl font-bold text-emerald-700">R$ 0,00</p>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <p class="text-sm font-medium text-gray-500">Despesas do mes</p>
                    <p class="mt-3 text-3xl font-bold text-rose-700">R$ 0,00</p>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Resumo mensal</h3>
                            <p class="mt-1 text-sm text-gray-500">Periodo atual</p>
                        </div>
                        <span class="rounded-md bg-gray-100 px-3 py-1 text-sm font-medium text-gray-600">Sem dados</span>
                    </div>

                    <div class="mt-6 h-48 rounded-md border border-dashed border-gray-300 bg-gray-50"></div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <h3 class="text-base font-semibold text-gray-900">Base do sistema</h3>

                    <dl class="mt-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-gray-500">Contas cadastradas</dt>
                            <dd class="text-sm font-semibold text-gray-900">0</dd>
                        </div>

                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-gray-500">Categorias cadastradas</dt>
                            <dd class="text-sm font-semibold text-gray-900">0</dd>
                        </div>

                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-gray-500">Lancamentos registrados</dt>
                            <dd class="text-sm font-semibold text-gray-900">0</dd>
                        </div>
                    </dl>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
