<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\User;

class DashboardSummaryService
{
    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        $currentMonth = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();

        return [
            'totalBalance' => $this->decimal($user->accounts()
                ->where('is_active', true)
                ->sum('current_balance')),
            'monthlyIncome' => $this->decimal($user->financialTransactions()
                ->where('type', TransactionType::Income)
                ->where('is_paid', true)
                ->whereNull('cancelled_at')
                ->whereBetween('transaction_date', [$currentMonth, $currentMonthEnd])
                ->sum('amount')),
            'monthlyExpense' => $this->decimal($user->financialTransactions()
                ->where('type', TransactionType::Expense)
                ->where('is_paid', true)
                ->whereNull('cancelled_at')
                ->whereBetween('transaction_date', [$currentMonth, $currentMonthEnd])
                ->sum('amount')),
            'activeAccountsCount' => $user->accounts()
                ->where('is_active', true)
                ->count(),
            'activeCategoriesCount' => $user->categories()
                ->where('is_active', true)
                ->count(),
            'transactionsCount' => $user->financialTransactions()
                ->whereNull('cancelled_at')
                ->count(),
            'weeklySummary' => $this->weeklySummary($user),
            'expensesByCategory' => $this->expensesByCategory($user, $currentMonth, $currentMonthEnd),
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function weeklySummary(User $user): array
    {
        $startDate = now()->subDays(6)->startOfDay();
        $endDate = now()->endOfDay();

        $transactions = $user->financialTransactions()
            ->whereNull('cancelled_at')
            ->where('is_paid', true)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('DATE(transaction_date) as date, type, SUM(amount) as total')
            ->groupBy('date', 'type')
            ->get();

        $summary = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $summary[$date] = [
                'date' => $date,
                'income' => '0.00',
                'expense' => '0.00',
            ];
        }

        foreach ($transactions as $transaction) {
            if ($transaction->type === TransactionType::Income) {
                $summary[$transaction->date]['income'] = $this->decimal($transaction->total);
            } else {
                $summary[$transaction->date]['expense'] = $this->decimal($transaction->total);
            }
        }

        return array_values($summary);
    }

    /**
     * @return array<int, array{name: string, amount: string, percentage: float}>
     */
    private function expensesByCategory(User $user, mixed $startDate, mixed $endDate): array
    {
        $totalExpense = $user->financialTransactions()
            ->where('type', TransactionType::Expense)
            ->where('is_paid', true)
            ->whereNull('cancelled_at')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        if ((float) $totalExpense === 0.0) {
            return [];
        }

        return $user->financialTransactions()
            ->join('categories', 'categories.id', '=', 'financial_transactions.category_id')
            ->where('financial_transactions.type', TransactionType::Expense)
            ->where('financial_transactions.is_paid', true)
            ->whereNull('financial_transactions.cancelled_at')
            ->whereBetween('financial_transactions.transaction_date', [$startDate, $endDate])
            ->selectRaw('categories.name, SUM(financial_transactions.amount) as amount')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('amount')
            ->limit(5)
            ->get()
            ->map(fn ($category) => [
                'name' => $category->name,
                'amount' => $this->decimal($category->amount),
                'percentage' => ((float) $category->amount / (float) $totalExpense) * 100,
            ])
            ->toArray();
    }

    private function decimal(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
