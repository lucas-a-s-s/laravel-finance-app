<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterFinancialTransactionsRequest;
use App\Http\Resources\Api\V1\FinancialTransactionResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class FinancialTransactionIndexController extends Controller
{
    public function __invoke(FilterFinancialTransactionsRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $transactionsQuery = $this->filteredTransactionsQuery($request, $filters);
        $perPage = min(max((int) $request->query('per_page', 15), 1), 50);

        $transactions = (clone $transactionsQuery)
            ->with(['account', 'category'])
            ->orderByDesc('transaction_date')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return FinancialTransactionResource::collection($transactions)
            ->additional([
                'totals' => [
                    'paid_income' => $this->paidTypeTotal($transactionsQuery, TransactionType::Income),
                    'paid_expense' => $this->paidTypeTotal($transactionsQuery, TransactionType::Expense),
                ],
            ])
            ->response();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function filteredTransactionsQuery(FilterFinancialTransactionsRequest $request, array $filters): Builder
    {
        $transactions = $request->user()->financialTransactions()->getQuery();

        if ($filters['date_from'] ?? null) {
            $transactions->where('transaction_date', '>=', $filters['date_from']);
        }

        if ($filters['date_to'] ?? null) {
            $transactions->where('transaction_date', '<=', $filters['date_to']);
        }

        if ($filters['type'] ?? null) {
            $transactions->where('type', $filters['type']);
        }

        if ($filters['account_id'] ?? null) {
            $transactions->where('account_id', $filters['account_id']);
        }

        if ($filters['category_id'] ?? null) {
            $transactions->where('category_id', $filters['category_id']);
        }

        return match ($filters['status'] ?? null) {
            'paid' => $transactions->whereNull('cancelled_at')->where('is_paid', true),
            'pending' => $transactions->whereNull('cancelled_at')->where('is_paid', false),
            'cancelled' => $transactions->whereNotNull('cancelled_at'),
            default => $transactions,
        };
    }

    private function paidTypeTotal(Builder $transactions, TransactionType $type): string
    {
        return number_format((float) (clone $transactions)
            ->where('type', $type->value)
            ->where('is_paid', true)
            ->whereNull('cancelled_at')
            ->sum('amount'), 2, '.', '');
    }
}
