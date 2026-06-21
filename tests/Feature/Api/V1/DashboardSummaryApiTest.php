<?php

namespace Tests\Feature\Api\V1;

use App\Models\Account;
use App\Models\Category;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardSummaryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_dashboard_summary_api(): void
    {
        $this->getJson('/api/v1/dashboard')
            ->assertUnauthorized();
    }

    public function test_user_can_get_dashboard_summary_from_api(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $account = Account::factory()->for($user)->create([
            'current_balance' => '1200.50',
            'is_active' => true,
        ]);
        Account::factory()->for($otherUser)->create([
            'current_balance' => '9999.99',
            'is_active' => true,
        ]);
        $incomeCategory = Category::factory()->for($user)->income()->create();
        $expenseCategory = Category::factory()->for($user)->expense()->create();

        FinancialTransaction::factory()->for($user)->for($account)->for($incomeCategory)->income()->create([
            'amount' => '700.00',
            'is_paid' => true,
            'cancelled_at' => null,
            'transaction_date' => now()->startOfMonth()->addDay(),
        ]);
        FinancialTransaction::factory()->for($user)->for($account)->for($expenseCategory)->expense()->create([
            'amount' => '200.00',
            'is_paid' => true,
            'cancelled_at' => null,
            'transaction_date' => now()->startOfMonth()->addDays(2),
        ]);
        FinancialTransaction::factory()->for($otherUser)->expense()->create([
            'amount' => '9999.99',
            'is_paid' => true,
            'transaction_date' => now()->startOfMonth()->addDays(3),
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('data.total_balance', '1200.50')
            ->assertJsonPath('data.monthly_income', '700.00')
            ->assertJsonPath('data.monthly_expense', '200.00')
            ->assertJsonPath('data.active_accounts_count', 1)
            ->assertJsonPath('data.active_categories_count', 2)
            ->assertJsonPath('data.transactions_count', 2)
            ->assertJsonCount(7, 'data.weekly_summary')
            ->assertJsonPath('data.expenses_by_category.0.amount', '200.00');
    }
}
