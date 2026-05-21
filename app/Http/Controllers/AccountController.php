<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $accounts = $request->user()
            ->accounts()
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(10);

        $activeBalance = $request->user()
            ->accounts()
            ->where('is_active', true)
            ->sum('current_balance');

        $activeAccountsCount = $request->user()
            ->accounts()
            ->where('is_active', true)
            ->count();

        return view('accounts.index', [
            'accounts' => $accounts,
            'activeBalance' => $activeBalance,
            'activeAccountsCount' => $activeAccountsCount,
            'accountTypes' => $this->accountTypes(),
        ]);
    }

    public function create(): View
    {
        return view('accounts.create', [
            'accountTypes' => $this->accountTypes(),
        ]);
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $request->user()->accounts()->create([
            ...$validated,
            'current_balance' => $validated['initial_balance'],
            'is_active' => true,
        ]);

        return to_route('accounts.index')->with('status', 'Conta cadastrada com sucesso.');
    }

    public function edit(Request $request, Account $account): View
    {
        $account = $this->accountFromAuthenticatedUser($request, $account);

        return view('accounts.edit', [
            'account' => $account,
            'accountTypes' => $this->accountTypes(),
        ]);
    }

    public function update(UpdateAccountRequest $request, Account $account): RedirectResponse
    {
        $account = $this->accountFromAuthenticatedUser($request, $account);

        $account->update($request->validated());

        return to_route('accounts.index')->with('status', 'Conta atualizada com sucesso.');
    }

    public function destroy(Request $request, Account $account): RedirectResponse
    {
        $account = $this->accountFromAuthenticatedUser($request, $account);

        $account->update([
            'is_active' => false,
        ]);

        return to_route('accounts.index')->with('status', 'Conta desativada com sucesso.');
    }

    private function accountFromAuthenticatedUser(Request $request, Account $account): Account
    {
        abort_unless($account->user_id === $request->user()->id, 404);

        return $account;
    }

    /**
     * @return array<string, string>
     */
    private function accountTypes(): array
    {
        return [
            'checking' => 'Conta corrente',
            'savings' => 'Poupanca',
            'cash' => 'Dinheiro',
            'credit_card' => 'Cartao de credito',
        ];
    }
}
