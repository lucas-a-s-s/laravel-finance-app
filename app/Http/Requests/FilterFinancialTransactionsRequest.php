<?php

namespace App\Http\Requests;

use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterFinancialTransactionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'type' => ['nullable', Rule::in(array_column(TransactionType::cases(), 'value'))],
            'account_id' => [
                'nullable',
                Rule::exists('accounts', 'id')
                    ->where('user_id', $this->user()->id),
            ],
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')
                    ->where('user_id', $this->user()->id),
            ],
            'status' => ['nullable', Rule::in(['paid', 'pending', 'cancelled'])],
        ];
    }
}
