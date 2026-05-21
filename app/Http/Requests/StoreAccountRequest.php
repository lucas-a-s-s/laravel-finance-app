<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('accounts', 'name')->where('user_id', $this->user()->id),
            ],
            'institution' => ['nullable', 'string', 'max:120'],
            'type' => ['required', Rule::in(['checking', 'savings', 'cash', 'credit_card'])],
            'initial_balance' => ['required', 'numeric', 'between:-9999999999999.99,9999999999999.99'],
            'currency' => ['required', 'string', 'size:3'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'currency' => strtoupper((string) $this->input('currency', 'BRL')),
        ]);
    }
}
