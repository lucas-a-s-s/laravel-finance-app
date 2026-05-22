<?php

namespace App\Actions\FinancialTransactions;

use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateFinancialTransaction
{
    public function __construct(
        private readonly AdjustAccountBalanceForTransaction $adjustAccountBalance,
    ) {}

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

            $this->adjustAccountBalance->apply($account, $transaction);

            return $transaction;
        });
    }
}
