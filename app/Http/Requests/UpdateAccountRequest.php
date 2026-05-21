<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $accountId = $this->route('account')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('accounts', 'name')
                    ->where('user_id', $this->user()->id)
                    ->ignore($accountId),
            ],
            'institution' => ['nullable', 'string', 'max:120'],
            'type' => ['required', Rule::in(['checking', 'savings', 'cash', 'credit_card'])],
            'currency' => ['required', 'string', 'size:3'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'currency' => strtoupper((string) $this->input('currency', 'BRL')),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
