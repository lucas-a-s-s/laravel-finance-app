<?php

namespace App\Http\Requests;

use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
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
                Rule::unique('categories', 'name')
                    ->where('user_id', $this->user()->id)
                    ->where('type', $this->input('type')),
            ],
            'type' => ['required', Rule::in(array_column(TransactionType::cases(), 'value'))],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => ['nullable', 'string', 'max:50', 'regex:/^[a-z0-9_-]+$/'],
        ];
    }
}
