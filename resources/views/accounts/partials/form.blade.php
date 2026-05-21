@csrf

@isset($method)
    @method($method)
@endisset

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <x-input-label for="name" :value="__('Nome')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $account->name ?? '')" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="institution" :value="__('Instituicao')" />
        <x-text-input id="institution" name="institution" type="text" class="mt-1 block w-full" :value="old('institution', $account->institution ?? '')" />
        <x-input-error class="mt-2" :messages="$errors->get('institution')" />
    </div>

    <div>
        <x-input-label for="type" :value="__('Tipo')" />
        <select id="type" name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
            @foreach ($accountTypes as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $account->type ?? 'checking') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('type')" />
    </div>

    <div>
        <x-input-label for="currency" :value="__('Moeda')" />
        <x-text-input id="currency" name="currency" type="text" maxlength="3" class="mt-1 block w-full uppercase" :value="old('currency', $account->currency ?? 'BRL')" required />
        <x-input-error class="mt-2" :messages="$errors->get('currency')" />
    </div>

    @if ($showInitialBalance ?? false)
        <div>
            <x-input-label for="initial_balance" :value="__('Saldo inicial')" />
            <x-text-input id="initial_balance" name="initial_balance" type="number" step="0.01" class="mt-1 block w-full" :value="old('initial_balance', '0.00')" required />
            <x-input-error class="mt-2" :messages="$errors->get('initial_balance')" />
        </div>
    @endif

    @isset($account)
        <div>
            <x-input-label :value="__('Saldo atual')" />
            <div class="mt-1 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-900">
                {{ $account->currency }} {{ number_format((float) $account->current_balance, 2, ',', '.') }}
            </div>
        </div>

        <div class="md:col-span-2">
            <input type="hidden" name="is_active" value="0">
            <label for="is_active" class="inline-flex items-center">
                <input id="is_active" type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $account->is_active)) class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                <span class="ms-2 text-sm text-gray-700">Conta ativa</span>
            </label>
            <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
        </div>
    @endisset
</div>

<div class="mt-8 flex items-center justify-end gap-3">
    <a href="{{ route('accounts.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
        Cancelar
    </a>

    <x-primary-button>
        {{ $submitLabel }}
    </x-primary-button>
</div>
