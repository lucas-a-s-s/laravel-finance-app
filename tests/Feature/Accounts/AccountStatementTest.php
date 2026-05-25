<?php

namespace Tests\Feature\Accounts;

use App\Models\Account;
use App\Models\Category;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountStatementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_account_statement(): void
    {
        $account = Account::factory()->create();

        $this->get(route('accounts.statement', $account))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_view_paid_movements_from_their_account(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create([
            'name' => 'Conta principal',
            'current_balance' => '1250.00',
        ]);
        $incomeCategory = Category::factory()->for($user)->income()->create([
            'name' => 'Salario',
        ]);
        $expenseCategory = Category::factory()->for($user)->expense()->create([
            'name' => 'Mercado',
        ]);

        FinancialTransaction::factory()->for($user)->for($account)->for($incomeCategory)->income()->create([
            'description' => 'Pagamento mensal',
            'amount' => '1500.00',
            'transaction_date' => '2026-05-10',
            'is_paid' => true,
        ]);

        FinancialTransaction::factory()->for($user)->for($account)->for($expenseCategory)->expense()->create([
            'description' => 'Compra semanal',
            'amount' => '250.00',
            'transaction_date' => '2026-05-12',
            'is_paid' => true,
        ]);

        $response = $this->actingAs($user)->get(route('accounts.statement', $account));

        $response
            ->assertOk()
            ->assertSee('Conta principal')
            ->assertSee('Pagamento mensal')
            ->assertSee('Compra semanal')
            ->assertSee('R$ 1.500,00')
            ->assertSee('R$ 250,00')
            ->assertSee('R$ 1.250,00');
    }

    public function test_statement_excludes_pending_cancelled_and_other_account_transactions(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $otherAccount = Account::factory()->for($user)->create();
        $category = Category::factory()->for($user)->expense()->create();

        FinancialTransaction::factory()->for($user)->for($account)->for($category)->expense()->create([
            'description' => 'Movimento pago',
            'amount' => '90.00',
            'is_paid' => true,
        ]);

        FinancialTransaction::factory()->for($user)->for($account)->for($category)->expense()->pending()->create([
            'description' => 'Movimento pendente',
            'amount' => '80.00',
        ]);

        FinancialTransaction::factory()->for($user)->for($account)->for($category)->expense()->cancelled()->create([
            'description' => 'Movimento cancelado',
            'amount' => '70.00',
            'is_paid' => true,
        ]);

        FinancialTransaction::factory()->for($user)->for($otherAccount)->for($category)->expense()->create([
            'description' => 'Outra conta',
            'amount' => '60.00',
            'is_paid' => true,
        ]);

        $response = $this->actingAs($user)->get(route('accounts.statement', $account));

        $response
            ->assertOk()
            ->assertSee('Movimento pago')
            ->assertDontSee('Movimento pendente')
            ->assertDontSee('Movimento cancelado')
            ->assertDontSee('Outra conta')
            ->assertSee('R$ 90,00')
            ->assertDontSee('R$ 80,00')
            ->assertDontSee('R$ 70,00')
            ->assertDontSee('R$ 60,00');
    }

    public function test_user_can_filter_statement_by_period(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $category = Category::factory()->for($user)->expense()->create();

        FinancialTransaction::factory()->for($user)->for($account)->for($category)->expense()->create([
            'description' => 'Despesa dentro do periodo',
            'amount' => '120.00',
            'transaction_date' => '2026-05-10',
            'is_paid' => true,
        ]);

        FinancialTransaction::factory()->for($user)->for($account)->for($category)->expense()->create([
            'description' => 'Despesa fora do periodo',
            'amount' => '90.00',
            'transaction_date' => '2026-04-10',
            'is_paid' => true,
        ]);

        $response = $this->actingAs($user)->get(route('accounts.statement', [
            'account' => $account,
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
        ]));

        $response
            ->assertOk()
            ->assertSee('Despesa dentro do periodo')
            ->assertDontSee('Despesa fora do periodo')
            ->assertSee('R$ 120,00')
            ->assertDontSee('R$ 90,00');
    }

    public function test_user_cannot_view_statement_from_another_user_account(): void
    {
        $user = User::factory()->create();
        $otherAccount = Account::factory()->create();

        $this->actingAs($user)
            ->get(route('accounts.statement', $otherAccount))
            ->assertNotFound();
    }
}
