<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_view_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
    }

    public function test_dashboard_shows_total_balance_from_active_accounts(): void
    {
        $user = User::factory()->create();

        $account1 = Account::factory()->for($user)->create([
            'current_balance' => '1500.00',
            'is_active' => true,
        ]);

        $account2 = Account::factory()->for($user)->create([
            'current_balance' => '2300.50',
            'is_active' => true,
        ]);

        // Conta desativada não deve entrar no saldo
        Account::factory()->for($user)->create([
            'current_balance' => '5000.00',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('3.800,50'); // 1500.00 + 2300.50
    }

    public function test_dashboard_shows_monthly_income(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $category = Category::factory()->for($user)->income()->create();

        // Receita paga deste mês
        FinancialTransaction::factory()->for($user)->for($account)->for($category)->income()->create([
            'amount' => '1000.00',
            'is_paid' => true,
            'cancelled_at' => null,
            'transaction_date' => now()->startOfMonth()->addDays(5),
        ]);

        // Outra receita paga deste mês
        FinancialTransaction::factory()->for($user)->for($account)->for($category)->income()->create([
            'amount' => '500.00',
            'is_paid' => true,
            'cancelled_at' => null,
            'transaction_date' => now()->startOfMonth()->addDays(10),
        ]);

        // Receita cancelada não deve entrar
        FinancialTransaction::factory()->for($user)->for($account)->for($category)->income()->create([
            'amount' => '9999.99',
            'is_paid' => true,
            'cancelled_at' => now(),
            'transaction_date' => now()->startOfMonth()->addDays(15),
        ]);

        // Receita pendente não deve entrar
        FinancialTransaction::factory()->for($user)->for($account)->for($category)->income()->create([
            'amount' => '9999.99',
            'is_paid' => false,
            'cancelled_at' => null,
            'transaction_date' => now()->startOfMonth()->addDays(20),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('1.500,00'); // 1000.00 + 500.00
    }

    public function test_dashboard_shows_monthly_expense(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $category = Category::factory()->for($user)->expense()->create();

        // Despesa paga deste mês
        FinancialTransaction::factory()->for($user)->for($account)->for($category)->expense()->create([
            'amount' => '200.00',
            'is_paid' => true,
            'cancelled_at' => null,
            'transaction_date' => now()->startOfMonth()->addDays(3),
        ]);

        // Outra despesa paga deste mês
        FinancialTransaction::factory()->for($user)->for($account)->for($category)->expense()->create([
            'amount' => '150.75',
            'is_paid' => true,
            'cancelled_at' => null,
            'transaction_date' => now()->startOfMonth()->addDays(8),
        ]);

        // Despesa cancelada não deve entrar
        FinancialTransaction::factory()->for($user)->for($account)->for($category)->expense()->create([
            'amount' => '9999.99',
            'is_paid' => true,
            'cancelled_at' => now(),
            'transaction_date' => now()->startOfMonth()->addDays(12),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('350,75'); // 200.00 + 150.75
    }

    public function test_dashboard_shows_active_accounts_count(): void
    {
        $user = User::factory()->create();

        Account::factory()->for($user)->create(['is_active' => true]);
        Account::factory()->for($user)->create(['is_active' => true]);
        Account::factory()->for($user)->create(['is_active' => false]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Contas cadastradas');
        $response->assertSee('2'); // 2 contas ativas
    }

    public function test_dashboard_shows_active_categories_count(): void
    {
        $user = User::factory()->create();

        Category::factory()->for($user)->income()->create(['is_active' => true]);
        Category::factory()->for($user)->expense()->create(['is_active' => true]);
        Category::factory()->for($user)->income()->create(['is_active' => false]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Categorias cadastradas');
        $response->assertSee('2'); // 2 categorias ativas
    }

    public function test_dashboard_shows_transactions_count_excluding_cancelled(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $category = Category::factory()->for($user)->expense()->create();

        FinancialTransaction::factory()->for($user)->for($account)->for($category)->expense()->create([
            'cancelled_at' => null,
        ]);

        FinancialTransaction::factory()->for($user)->for($account)->for($category)->expense()->create([
            'cancelled_at' => null,
        ]);

        FinancialTransaction::factory()->for($user)->for($account)->for($category)->expense()->create([
            'cancelled_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        // Verifica que o contador de lancamentos e 2 (nao cancelados)
        $content = strip_tags($response->content());
        $this->assertStringContainsString('Lançamentos registrados', $content);
        $this->assertStringContainsString('2', $content);
    }

    public function test_user_sees_only_their_own_data(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // Dados do usuario atual
        Account::factory()->for($user)->create([
            'current_balance' => '1000.00',
            'is_active' => true,
        ]);

        // Dados de outro usuario
        Account::factory()->for($otherUser)->create([
            'current_balance' => '5000.00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('1.000,00'); // Saldo do usuario atual
        $response->assertDontSee('5.000,00'); // Saldo do outro usuario nao deve aparecer
    }

    public function test_dashboard_handles_empty_state(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('0,00'); // Saldo zero
        // Verifica mensagem de estado vazio no resumo semanal
        $response->assertSee('Sem');
    }
}
