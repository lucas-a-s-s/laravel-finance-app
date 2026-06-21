<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\FilterAccountStatementRequest;
use App\Models\Account;
use Illuminate\View\View;

class AccountStatementController extends Controller
{
    public function __invoke(FilterAccountStatementRequest $request, Account $account): View
    {
        abort_unless($account->user_id === $request->user()->id, 404);

        $filters = $request->validated();

        $statementQuery = $account->financialTransactions()
            ->with('category')
            ->where('user_id', $request->user()->id)
            ->where('is_paid', true)
            ->whereNull('cancelled_at')
            ->when($filters['date_from'] ?? null, function ($query, string $dateFrom): void {
                $query->whereDate('transaction_date', '>=', $dateFrom);
            })
            ->when($filters['date_to'] ?? null, function ($query, string $dateTo): void {
                $query->whereDate('transaction_date', '<=', $dateTo);
            });

        $incomeTotal = (clone $statementQuery)
            ->where('type', TransactionType::Income->value)
            ->sum('amount');

        $expenseTotal = (clone $statementQuery)
            ->where('type', TransactionType::Expense->value)
            ->sum('amount');

        $transactions = $statementQuery
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $balanceMovements = $account->balanceMovements()
            ->with(['financialTransaction.category'])
            ->where('user_id', $request->user()->id)
            ->when($filters['date_from'] ?? null, function ($query, string $dateFrom): void {
                $query->whereHas('financialTransaction', function ($transactionQuery) use ($dateFrom): void {
                    $transactionQuery->whereDate('transaction_date', '>=', $dateFrom);
                });
            })
            ->when($filters['date_to'] ?? null, function ($query, string $dateTo): void {
                $query->whereHas('financialTransaction', function ($transactionQuery) use ($dateTo): void {
                    $transactionQuery->whereDate('transaction_date', '<=', $dateTo);
                });
            })
            ->latest('id')
            ->limit(10)
            ->get();

        return view('accounts.statement', [
            'account' => $account,
            'transactions' => $transactions,
            'balanceMovements' => $balanceMovements,
            'filters' => $filters,
            'incomeTotal' => $incomeTotal,
            'expenseTotal' => $expenseTotal,
            'netTotal' => (float) $incomeTotal - (float) $expenseTotal,
        ]);
    }
}
