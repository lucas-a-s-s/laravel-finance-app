<?php

namespace App\Actions\FinancialTransactions;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\FinancialTransaction;

class AdjustAccountBalanceForTransaction
{
    public function apply(Account $account, FinancialTransaction $transaction): void
    {
        if (! $transaction->is_paid) {
            return;
        }

        if ($transaction->type === TransactionType::Income) {
            $account->increment('current_balance', $transaction->amount);

            return;
        }

        $account->decrement('current_balance', $transaction->amount);
    }

    public function reverse(Account $account, FinancialTransaction $transaction): void
    {
        if (! $transaction->is_paid) {
            return;
        }

        if ($transaction->type === TransactionType::Income) {
            $account->decrement('current_balance', $transaction->amount);

            return;
        }

        $account->increment('current_balance', $transaction->amount);
    }
}
