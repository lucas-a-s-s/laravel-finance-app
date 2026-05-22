<?php

namespace App\Actions\FinancialTransactions;

use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CancelFinancialTransaction
{
    public function __construct(
        private readonly AdjustAccountBalanceForTransaction $adjustAccountBalance,
    ) {}

    public function handle(User $user, FinancialTransaction $transaction): FinancialTransaction
    {
        return DB::transaction(function () use ($user, $transaction) {
            $transaction = $user->financialTransactions()
                ->whereKey($transaction->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($transaction->isCancelled()) {
                return $transaction;
            }

            $account = $user->accounts()
                ->whereKey($transaction->account_id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->adjustAccountBalance->reverse($account, $transaction);

            $transaction->update([
                'cancelled_at' => now(),
            ]);

            return $transaction;
        });
    }
}
