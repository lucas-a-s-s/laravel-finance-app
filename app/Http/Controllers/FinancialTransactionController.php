<?php

namespace App\Http\Controllers;

use App\Actions\FinancialTransactions\CreateFinancialTransaction;
use App\Enums\TransactionType;
use App\Http\Requests\StoreFinancialTransactionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $transactions = $request->user()
            ->financialTransactions()
            ->with(['account', 'category'])
            ->orderByDesc('transaction_date')
            ->latest('id')
            ->paginate(10);

        return view('financial-transactions.index', [
            'transactions' => $transactions,
            'paidIncomeTotal' => $this->paidTypeTotal($request, TransactionType::Income),
            'paidExpenseTotal' => $this->paidTypeTotal($request, TransactionType::Expense),
            'transactionTypes' => $this->transactionTypes(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('financial-transactions.create', [
            'accounts' => $request->user()->accounts()->where('is_active', true)->orderBy('name')->get(),
            'categories' => $request->user()->categories()->where('is_active', true)->orderBy('name')->get(),
            'transactionTypes' => $this->transactionTypes(),
        ]);
    }

    public function store(
        StoreFinancialTransactionRequest $request,
        CreateFinancialTransaction $createFinancialTransaction,
    ): RedirectResponse {
        $createFinancialTransaction->handle($request->user(), $request->validated());

        return to_route('financial-transactions.index')->with('status', 'Lancamento cadastrado com sucesso.');
    }

    private function paidTypeTotal(Request $request, TransactionType $type): string
    {
        return (string) $request->user()
            ->financialTransactions()
            ->where('type', $type->value)
            ->where('is_paid', true)
            ->sum('amount');
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
}
