<?php

namespace App\Actions\FinancialTransactions;

use App\Enums\AccountBalanceMovementOperation;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\AccountBalanceMovement;
use App\Models\FinancialTransaction;

class AdjustAccountBalanceForTransaction
{
    public function apply(Account $account, FinancialTransaction $transaction): void
    {
        $this->adjust($account, $transaction, AccountBalanceMovementOperation::Applied);
    }

    public function reverse(Account $account, FinancialTransaction $transaction): void
    {
        $this->adjust($account, $transaction, AccountBalanceMovementOperation::Reversed);
    }

    private function adjust(
        Account $account,
        FinancialTransaction $transaction,
        AccountBalanceMovementOperation $operation,
    ): void {
        if (! $transaction->is_paid) {
            return;
        }

        $balanceBefore = $account->current_balance;
        $impactAmount = $this->impactAmount($transaction, $operation);

        if (str_starts_with($impactAmount, '-')) {
            $account->decrement('current_balance', ltrim($impactAmount, '-'));
        } else {
            $account->increment('current_balance', $impactAmount);
        }

        $account->refresh();

        AccountBalanceMovement::create([
            'user_id' => $transaction->user_id,
            'account_id' => $account->id,
            'financial_transaction_id' => $transaction->id,
            'operation' => $operation,
            'transaction_type' => $transaction->type,
            'amount' => $transaction->amount,
            'impact_amount' => $impactAmount,
            'balance_before' => $balanceBefore,
            'balance_after' => $account->current_balance,
        ]);
    }

    private function impactAmount(
        FinancialTransaction $transaction,
        AccountBalanceMovementOperation $operation,
    ): string {
        $amount = $transaction->amount;
        $increasesBalance = $operation === AccountBalanceMovementOperation::Applied
            ? $transaction->type === TransactionType::Income
            : $transaction->type === TransactionType::Expense;

        return $increasesBalance ? $amount : "-{$amount}";
    }
}
