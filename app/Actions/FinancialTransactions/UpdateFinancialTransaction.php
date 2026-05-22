<?php

namespace App\Actions\FinancialTransactions;

use App\Models\Account;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class UpdateFinancialTransaction
{
    public function __construct(
        private readonly AdjustAccountBalanceForTransaction $adjustAccountBalance,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $user, FinancialTransaction $transaction, array $attributes): FinancialTransaction
    {
        return DB::transaction(function () use ($user, $transaction, $attributes) {
            $transaction = $user->financialTransactions()
                ->whereKey($transaction->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $accounts = $user->accounts()
                ->whereIn('id', [$transaction->account_id, $attributes['account_id']])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $previousAccount = $accounts->get($transaction->account_id);
            $nextAccount = $accounts->get($attributes['account_id']);

            if (! $previousAccount instanceof Account || ! $nextAccount instanceof Account) {
                throw (new ModelNotFoundException)->setModel(Account::class);
            }

            if (! $nextAccount->is_active && ! $nextAccount->is($previousAccount)) {
                throw (new ModelNotFoundException)->setModel(Account::class);
            }

            $category = $user->categories()
                ->whereKey($attributes['category_id'])
                ->where('type', $attributes['type'])
                ->where(function ($query) use ($transaction) {
                    $query
                        ->where('is_active', true)
                        ->orWhere('id', $transaction->category_id);
                })
                ->firstOrFail();

            $this->adjustAccountBalance->reverse($previousAccount, $transaction);

            $transaction->update([
                ...$attributes,
                'account_id' => $nextAccount->getKey(),
                'category_id' => $category->getKey(),
            ]);

            $this->adjustAccountBalance->apply($nextAccount, $transaction);

            return $transaction;
        });
    }
}
