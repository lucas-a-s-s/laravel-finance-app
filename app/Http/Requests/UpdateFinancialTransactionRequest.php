<?php

namespace App\Http\Requests;

use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFinancialTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $transaction = $this->route('financial_transaction');

        return [
            'account_id' => [
                'required',
                Rule::exists('accounts', 'id')
                    ->where('user_id', $this->user()->id)
                    ->where(function ($query) use ($transaction) {
                        $query->where('is_active', true);

                        if ($transaction !== null) {
                            $query->orWhere('id', $transaction->account_id);
                        }
                    }),
            ],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')
                    ->where('user_id', $this->user()->id)
                    ->where('type', $this->input('type'))
                    ->where(function ($query) use ($transaction) {
                        $query->where('is_active', true);

                        if ($transaction !== null) {
                            $query->orWhere('id', $transaction->category_id);
                        }
                    }),
            ],
            'type' => ['required', Rule::in(array_column(TransactionType::cases(), 'value'))],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99'],
            'description' => ['nullable', 'string', 'max:160'],
            'transaction_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_paid' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_paid' => $this->boolean('is_paid'),
        ]);
    }
}
