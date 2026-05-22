<?php

namespace App\Actions\FinancialTransactions;

use App\Enums\TransactionType;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateFinancialTransaction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $user, array $attributes): FinancialTransaction
    {
        return DB::transaction(function () use ($user, $attributes) {
            $account = $user->accounts()
                ->whereKey($attributes['account_id'])
                ->where('is_active', true)
                ->lockForUpdate()
                ->firstOrFail();

            $category = $user->categories()
                ->whereKey($attributes['category_id'])
                ->where('type', $attributes['type'])
                ->where('is_active', true)
                ->firstOrFail();

            $transaction = $user->financialTransactions()->create([
                ...$attributes,
                'account_id' => $account->getKey(),
                'category_id' => $category->getKey(),
            ]);

            if (! $transaction->is_paid) {
                return $transaction;
            }

            if ($transaction->type === TransactionType::Income) {
                $account->increment('current_balance', $transaction->amount);
            } else {
                $account->decrement('current_balance', $transaction->amount);
            }

            return $transaction;
        });
    }
}
