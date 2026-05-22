<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-emerald-700">Categorias</p>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                Nova categoria
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <section class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <form method="POST" action="{{ route('categories.store') }}">
                    @include('categories.partials.form', [
                        'category' => null,
                        'method' => null,
                        'submitLabel' => 'Cadastrar categoria',
                    ])
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
