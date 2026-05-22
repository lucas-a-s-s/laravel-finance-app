<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-emerald-700">Finance App</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">
                    Categorias
                </h2>
            </div>

            <a href="{{ route('categories.create') }}" class="inline-flex items-center justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800">
                Nova categoria
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
                    <p class="text-sm font-medium text-gray-500">Total de categorias</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $categories->total() }}</p>
                </div>

                <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <p class="text-sm font-medium text-gray-500">Receitas ativas</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $activeIncomeCategoriesCount }}</p>
                </div>

                <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <p class="text-sm font-medium text-gray-500">Despesas ativas</p>
                    <p class="mt-2 text-2xl font-bold text-rose-700">{{ $activeExpenseCategoriesCount }}</p>
                </div>
            </section>

            <section class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Categoria</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tipo</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Icone</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-500">Acoes</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($categories as $category)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <div class="flex items-center gap-3">
                                            <span class="h-4 w-4 shrink-0 rounded-sm border border-gray-200" style="background-color: {{ $category->color ?: '#E5E7EB' }}"></span>
                                            <span>{{ $category->name }}</span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                        {{ $categoryTypes[$category->type->value] ?? $category->type->value }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                        {{ $categoryIcons[$category->icon] ?? ($category->icon ?: '-') }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span @class([
                                            'rounded-md px-2 py-1 text-xs font-semibold',
                                            'bg-emerald-50 text-emerald-700' => $category->is_active,
                                            'bg-gray-100 text-gray-600' => ! $category->is_active,
                                        ])>
                                            {{ $category->is_active ? 'Ativa' : 'Inativa' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                        <div class="flex justify-end gap-3">
                                            <a href="{{ route('categories.edit', $category) }}" class="text-emerald-700 hover:text-emerald-900">Editar</a>

                                            @if ($category->is_active)
                                                <form method="POST" action="{{ route('categories.destroy', $category) }}">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" class="text-rose-700 hover:text-rose-900">
                                                        Desativar
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                                        Nenhuma categoria cadastrada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($categories->hasPages())
                    <div class="border-t border-gray-200 px-6 py-4">
                        {{ $categories->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
