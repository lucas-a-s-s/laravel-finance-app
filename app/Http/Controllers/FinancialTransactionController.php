<?php

namespace App\Http\Controllers;

use App\Actions\FinancialTransactions\CancelFinancialTransaction;
use App\Actions\FinancialTransactions\CreateFinancialTransaction;
use App\Actions\FinancialTransactions\UpdateFinancialTransaction;
use App\Enums\TransactionType;
use App\Http\Requests\FilterFinancialTransactionsRequest;
use App\Http\Requests\StoreFinancialTransactionRequest;
use App\Http\Requests\UpdateFinancialTransactionRequest;
use App\Models\FinancialTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialTransactionController extends Controller
{
    public function index(FilterFinancialTransactionsRequest $request): View
    {
        $filters = $request->validated();
        $filteredTransactions = $this->filteredTransactionsQuery($request, $filters);

        $transactions = (clone $filteredTransactions)
            ->with(['account', 'category'])
            ->orderByDesc('transaction_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('financial-transactions.index', [
            'transactions' => $transactions,
            'paidIncomeTotal' => $this->paidTypeTotal($filteredTransactions, TransactionType::Income),
            'paidExpenseTotal' => $this->paidTypeTotal($filteredTransactions, TransactionType::Expense),
            'filters' => $filters,
            'filterAccounts' => $request->user()->accounts()->orderByDesc('is_active')->orderBy('name')->get(),
            'filterCategories' => $request->user()->categories()->orderByDesc('is_active')->orderBy('type')->orderBy('name')->get(),
            'transactionStatuses' => $this->transactionStatuses(),
            'transactionTypes' => $this->transactionTypes(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('financial-transactions.create', $this->transactionFormOptions($request));
    }

    public function store(
        StoreFinancialTransactionRequest $request,
        CreateFinancialTransaction $createFinancialTransaction,
    ): RedirectResponse {
        $createFinancialTransaction->handle($request->user(), $request->validated());

        return to_route('financial-transactions.index')->with('status', 'Lancamento cadastrado com sucesso.');
    }

    public function edit(Request $request, FinancialTransaction $financialTransaction): View
    {
        $financialTransaction = $this->editableTransactionFromAuthenticatedUser($request, $financialTransaction);

        return view('financial-transactions.edit', [
            'transaction' => $financialTransaction,
            ...$this->transactionFormOptions($request, $financialTransaction),
        ]);
    }

    public function update(
        UpdateFinancialTransactionRequest $request,
        FinancialTransaction $financialTransaction,
        UpdateFinancialTransaction $updateFinancialTransaction,
    ): RedirectResponse {
        $financialTransaction = $this->editableTransactionFromAuthenticatedUser($request, $financialTransaction);

        $updateFinancialTransaction->handle($request->user(), $financialTransaction, $request->validated());

        return to_route('financial-transactions.index')->with('status', 'Lancamento atualizado com sucesso.');
    }

    public function cancel(
        Request $request,
        FinancialTransaction $financialTransaction,
        CancelFinancialTransaction $cancelFinancialTransaction,
    ): RedirectResponse {
        $financialTransaction = $this->transactionFromAuthenticatedUser($request, $financialTransaction);

        $cancelFinancialTransaction->handle($request->user(), $financialTransaction);

        return to_route('financial-transactions.index')->with('status', 'Lancamento cancelado com sucesso.');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function filteredTransactionsQuery(Request $request, array $filters): Builder
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
        return (string) (clone $transactions)
            ->where('type', $type->value)
            ->where('is_paid', true)
            ->whereNull('cancelled_at')
            ->sum('amount');
    }

    private function transactionFromAuthenticatedUser(
        Request $request,
        FinancialTransaction $financialTransaction,
    ): FinancialTransaction {
        abort_unless($financialTransaction->user_id === $request->user()->id, 404);

        return $financialTransaction;
    }

    private function editableTransactionFromAuthenticatedUser(
        Request $request,
        FinancialTransaction $financialTransaction,
    ): FinancialTransaction {
        $financialTransaction = $this->transactionFromAuthenticatedUser($request, $financialTransaction);

        abort_if($financialTransaction->isCancelled(), 404);

        return $financialTransaction;
    }

    /**
     * @return array<string, mixed>
     */
    private function transactionFormOptions(
        Request $request,
        ?FinancialTransaction $financialTransaction = null,
    ): array {
        return [
            'accounts' => $request->user()
                ->accounts()
                ->where(function ($query) use ($financialTransaction) {
                    $query->where('is_active', true);

                    if ($financialTransaction !== null) {
                        $query->orWhere('id', $financialTransaction->account_id);
                    }
                })
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(),
            'categories' => $request->user()
                ->categories()
                ->where(function ($query) use ($financialTransaction) {
                    $query->where('is_active', true);

                    if ($financialTransaction !== null) {
                        $query->orWhere('id', $financialTransaction->category_id);
                    }
                })
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(),
            'transactionTypes' => $this->transactionTypes(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function transactionTypes(): array
    {
        return [
            TransactionType::Income->value => 'Receita',
            TransactionType::Expense->value => 'Despesa',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function transactionStatuses(): array
    {
        return [
            'paid' => 'Pago',
            'pending' => 'Pendente',
            'cancelled' => 'Cancelado',
        ];
    }
}
