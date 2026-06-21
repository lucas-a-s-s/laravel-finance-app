<?php

namespace Tests\Feature\Api\V1;

use App\Models\Account;
use App\Models\Category;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FinancialTransactionIndexApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_financial_transactions_api(): void
    {
        $this->getJson('/api/v1/financial-transactions')
            ->assertUnauthorized();
    }

    public function test_user_can_list_only_their_financial_transactions_from_api(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $account = Account::factory()->for($user)->create([
            'name' => 'Conta API',
        ]);
        $category = Category::factory()->for($user)->expense()->create([
            'name' => 'Mercado API',
        ]);

        FinancialTransaction::factory()
            ->for($user)
            ->for($account)
            ->for($category)
            ->expense()
            ->create([
                'description' => 'Compra API',
                'amount' => '120.00',
                'transaction_date' => '2026-05-10',
                'is_paid' => true,
            ]);
        FinancialTransaction::factory()
            ->for($otherUser)
            ->expense()
            ->create([
                'description' => 'Lancamento privado API',
            ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/financial-transactions')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.description', 'Compra API')
            ->assertJsonPath('data.0.amount', '120.00')
            ->assertJsonPath('data.0.type', 'expense')
            ->assertJsonPath('data.0.account.name', 'Conta API')
            ->assertJsonPath('data.0.category.name', 'Mercado API')
            ->assertJsonMissing(['description' => 'Lancamento privado API']);
    }

    public function test_user_can_filter_financial_transactions_api(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $category = Category::factory()->for($user)->expense()->create();

        FinancialTransaction::factory()
            ->for($user)
            ->for($account)
            ->for($category)
            ->expense()
            ->create([
                'description' => 'Despesa filtrada API',
                'amount' => '80.00',
                'transaction_date' => '2026-05-15',
                'is_paid' => true,
                'cancelled_at' => null,
            ]);
        FinancialTransaction::factory()
            ->for($user)
            ->for($account)
            ->for($category)
            ->expense()
            ->pending()
            ->create([
                'description' => 'Despesa pendente API',
                'amount' => '70.00',
                'transaction_date' => '2026-05-16',
            ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/financial-transactions?status=paid&type=expense&date_from=2026-05-01&date_to=2026-05-31')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.description', 'Despesa filtrada API')
            ->assertJsonPath('totals.paid_expense', '80.00')
            ->assertJsonPath('totals.paid_income', '0.00')
            ->assertJsonMissing(['description' => 'Despesa pendente API']);
    }

    public function test_user_cannot_filter_financial_transactions_api_with_other_user_account(): void
    {
        $user = User::factory()->create();
        $otherAccount = Account::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/financial-transactions?account_id={$otherAccount->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('account_id');
    }
}
