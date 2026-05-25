@csrf

@isset($method)
    @method($method)
@endisset

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <x-input-label for="type" :value="__('Tipo')" />
        <select id="type" name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
            @foreach ($transactionTypes as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $transaction?->type?->value ?? 'expense') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('type')" />
    </div>

    <div>
        <x-input-label for="transaction_date" :value="__('Data')" />
        <x-text-input id="transaction_date" name="transaction_date" type="date" class="mt-1 block w-full" :value="old('transaction_date', $transaction?->transaction_date?->toDateString() ?? now()->toDateString())" required />
        <x-input-error class="mt-2" :messages="$errors->get('transaction_date')" />
    </div>

    <div>
        <x-input-label for="account_id" :value="__('Conta')" />
        <select id="account_id" name="account_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
            <option value="">Selecione uma conta</option>

            @foreach ($accounts as $account)
                <option value="{{ $account->id }}" @selected((string) old('account_id', $transaction?->account_id) === (string) $account->id)>
                    {{ $account->name }} - {{ $account->currency }} {{ number_format((float) $account->current_balance, 2, ',', '.') }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('account_id')" />
    </div>

    <div>
        <x-input-label for="category_id" :value="__('Categoria')" />
        <select id="category_id" name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
            <option value="">Selecione uma categoria</option>

            @foreach ($transactionTypes as $typeValue => $typeLabel)
                <optgroup label="{{ $typeLabel }}" data-type="{{ $typeValue }}">
                    @foreach ($categories as $category)
                        @if ($category->type->value === $typeValue)
                            <option value="{{ $category->id }}" data-type="{{ $typeValue }}" @selected((string) old('category_id', $transaction?->category_id) === (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endif
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
    </div>

    <div>
        <x-input-label for="amount" :value="__('Valor')" />
        <x-text-input id="amount" name="amount" type="number" min="0.01" step="0.01" class="mt-1 block w-full" :value="old('amount', $transaction?->amount)" required />
        <x-input-error class="mt-2" :messages="$errors->get('amount')" />
    </div>

    <div>
        <x-input-label for="description" :value="__('Descricao')" />
        <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" :value="old('description', $transaction?->description)" />
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="notes" :value="__('Observacoes')" />
        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('notes', $transaction?->notes) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
    </div>

    <div class="md:col-span-2">
        <input type="hidden" name="is_paid" value="0">
        <label for="is_paid" class="inline-flex items-center">
            <input id="is_paid" type="checkbox" name="is_paid" value="1" @checked((bool) old('is_paid', $transaction?->is_paid ?? true)) class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
            <span class="ms-2 text-sm text-gray-700">Lancamento pago</span>
        </label>
        <x-input-error class="mt-2" :messages="$errors->get('is_paid')" />
    </div>
</div>

<script>
(function() {
    const typeSelect = document.getElementById('type');
    const categorySelect = document.getElementById('category_id');

    function filterCategoriesByType() {
        const selectedType = typeSelect.value;
        const selectedOption = categorySelect.selectedOptions[0];
        const currentCategoryType = selectedOption ? selectedOption.dataset.type : null;

        // Se a categoria selecionada não for compatível com o tipo, limpa a seleção
        if (selectedOption && selectedOption.value && currentCategoryType !== selectedType) {
            categorySelect.value = '';
        }

        // Mostra/esconde optgroups e options baseado no tipo selecionado
        Array.from(categorySelect.options).forEach(option => {
            if (option.value === '') {
                // Sempre mostra a opção placeholder
                option.style.display = '';
                option.disabled = false;
                return;
            }

            const optionType = option.dataset.type;
            if (optionType === selectedType) {
                option.style.display = '';
                option.disabled = false;
            } else {
                option.style.display = 'none';
                option.disabled = true;
            }
        });

        // Mostra/esconde optgroups
        Array.from(categorySelect.querySelectorAll('optgroup')).forEach(optgroup => {
            const optgroupType = optgroup.dataset.type;
            if (optgroupType === selectedType) {
                optgroup.style.display = '';
            } else {
                optgroup.style.display = 'none';
            }
        });
    }

    // Filtra ao mudar o tipo
    typeSelect.addEventListener('change', filterCategoriesByType);

    // Aplica filtro ao carregar a página (para edição ou novo cadastro)
    document.addEventListener('DOMContentLoaded', filterCategoriesByType);
})();
</script>

<div class="mt-8 flex items-center justify-end gap-3">
    <a href="{{ route('financial-transactions.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
        Cancelar
    </a>

    <x-primary-button>
        {{ $submitLabel }}
    </x-primary-button>
</div>
