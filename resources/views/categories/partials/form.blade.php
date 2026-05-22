@csrf

@isset($method)
    @method($method)
@endisset

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <x-input-label for="name" :value="__('Nome')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $category?->name ?? '')" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="type" :value="__('Tipo')" />
        <select id="type" name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
            @foreach ($categoryTypes as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $category?->type?->value ?? 'expense') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('type')" />
    </div>

    <div>
        <x-input-label for="color" :value="__('Cor')" />
        <input id="color" name="color" type="color" value="{{ old('color', $category?->color ?? '#059669') }}" class="mt-1 h-11 w-full cursor-pointer rounded-md border border-gray-300 bg-white p-1 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
        <x-input-error class="mt-2" :messages="$errors->get('color')" />
    </div>

    <div>
        <x-input-label for="icon" :value="__('Icone')" />
        <select id="icon" name="icon" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <option value="">Sem icone</option>

            @foreach ($categoryIcons as $value => $label)
                <option value="{{ $value }}" @selected(old('icon', $category?->icon ?? 'tag') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('icon')" />
    </div>

    @isset($category)
        <div class="md:col-span-2">
            <input type="hidden" name="is_active" value="0">
            <label for="is_active" class="inline-flex items-center">
                <input id="is_active" type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $category->is_active)) class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                <span class="ms-2 text-sm text-gray-700">Categoria ativa</span>
            </label>
            <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
        </div>
    @endisset
</div>

<div class="mt-8 flex items-center justify-end gap-3">
    <a href="{{ route('categories.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
        Cancelar
    </a>

    <x-primary-button>
        {{ $submitLabel }}
    </x-primary-button>
</div>
