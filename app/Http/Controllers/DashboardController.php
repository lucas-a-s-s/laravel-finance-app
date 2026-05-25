<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $currentMonth = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();

        // Saldo total somando todas as contas ativas
        $totalBalance = $user->accounts()
            ->where('is_active', true)
            ->sum('current_balance');

        // Receitas do mês (pagas, não canceladas)
        $monthlyIncome = $user->financialTransactions()
            ->where('type', TransactionType::Income)
            ->where('is_paid', true)
            ->whereNull('cancelled_at')
            ->whereBetween('transaction_date', [$currentMonth, $currentMonthEnd])
            ->sum('amount');

        // Despesas do mês (pagas, não canceladas)
        $monthlyExpense = $user->financialTransactions()
            ->where('type', TransactionType::Expense)
            ->where('is_paid', true)
            ->whereNull('cancelled_at')
            ->whereBetween('transaction_date', [$currentMonth, $currentMonthEnd])
            ->sum('amount');

        // Contas ativas
        $activeAccountsCount = $user->accounts()
            ->where('is_active', true)
            ->count();

        // Categorias ativas
        $activeCategoriesCount = $user->categories()
            ->where('is_active', true)
            ->count();

        // Lançamentos registrados (excluindo cancelados)
        $transactionsCount = $user->financialTransactions()
            ->whereNull('cancelled_at')
            ->count();

        // Resumo semanal para os últimos 7 dias
        $weeklySummary = $this->getWeeklySummary($user);

        // Despesas por categoria no mês
        $expensesByCategory = $this->getExpensesByCategory($user, $currentMonth, $currentMonthEnd);

        return view('dashboard', [
            'totalBalance' => $totalBalance,
            'monthlyIncome' => $monthlyIncome,
            'monthlyExpense' => $monthlyExpense,
            'activeAccountsCount' => $activeAccountsCount,
            'activeCategoriesCount' => $activeCategoriesCount,
            'transactionsCount' => $transactionsCount,
            'weeklySummary' => $weeklySummary,
            'expensesByCategory' => $expensesByCategory,
        ]);
    }

    /**
     * Retorna resumo diário dos últimos 7 dias
     */
    private function getWeeklySummary($user): array
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
            $date = $transaction->date;
            if ($transaction->type === TransactionType::Income) {
                $summary[$date]['income'] = $transaction->total;
            } else {
                $summary[$date]['expense'] = $transaction->total;
            }
        }

        return array_values($summary);
    }

    /**
     * Retorna despesas agrupadas por categoria no período
     */
    private function getExpensesByCategory($user, $startDate, $endDate): array
    {
        $totalExpense = $user->financialTransactions()
            ->where('type', TransactionType::Expense)
            ->where('is_paid', true)
            ->whereNull('cancelled_at')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        if ($totalExpense == 0) {
            return [];
        }

        $categories = $user->financialTransactions()
            ->join('categories', 'categories.id', '=', 'financial_transactions.category_id')
            ->where('financial_transactions.type', TransactionType::Expense)
            ->where('financial_transactions.is_paid', true)
            ->whereNull('financial_transactions.cancelled_at')
            ->whereBetween('financial_transactions.transaction_date', [$startDate, $endDate])
            ->selectRaw('categories.name, SUM(financial_transactions.amount) as amount')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('amount')
            ->limit(5)
            ->get();

        return $categories->map(function ($category) use ($totalExpense) {
            return [
                'name' => $category->name,
                'amount' => $category->amount,
                'percentage' => ($category->amount / $totalExpense) * 100,
            ];
        })->toArray();
    }
}
