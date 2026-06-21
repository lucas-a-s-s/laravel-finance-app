<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DashboardSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardSummaryController extends Controller
{
    public function __invoke(Request $request, DashboardSummaryService $dashboardSummary): JsonResponse
    {
        $summary = $dashboardSummary->forUser($request->user());

        return response()->json([
            'data' => [
                'total_balance' => $summary['totalBalance'],
                'monthly_income' => $summary['monthlyIncome'],
                'monthly_expense' => $summary['monthlyExpense'],
                'active_accounts_count' => $summary['activeAccountsCount'],
                'active_categories_count' => $summary['activeCategoriesCount'],
                'transactions_count' => $summary['transactionsCount'],
                'weekly_summary' => $summary['weeklySummary'],
                'expenses_by_category' => $summary['expensesByCategory'],
            ],
        ]);
    }
}
