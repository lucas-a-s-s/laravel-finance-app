<?php

namespace Tests\Feature\FinancialTransactions;

use App\Models\Account;
use App\Models\Category;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialTransactionEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_financial_transactions_page(): void
    {
        $this->get(route('financial-transactions.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_view_only_their_financial_transactions(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownTransaction = FinancialTransaction::factory()
            ->for($user)
            ->for(Account::factory()->for($user))
            ->for(Category::factory()->for($user)->expense())
            ->expense()
            ->create([
                'description' => 'Aluguel',
            ]);
        $otherTransaction = FinancialTransaction::factory()
            ->for($otherUser)
            ->for(Account::factory()->for($otherUser))
            ->for(Category::factory()->for($otherUser)->income())
            ->income()
            ->create([
                'description' => 'Lancamento privado',
            ]);

        $response = $this->actingAs($user)->get(route('financial-transactions.index'));

        $response
            ->assertOk()
            ->assertSee($ownTransaction->description)
            ->assertDontSee($otherTransaction->description);
    }

    public function test_paid_income_increases_account_balance(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create([
            'current_balance' => '100.00',
        ]);
        $category = Category::factory()->for($user)->income()->create();

        $response = $this->actingAs($user)->post(route('financial-transactions.store'), [
            'account_id' => $account->id,
            'category_id' => $category->id,
            'type' => 'income',
            'amount' => '250.35',
            'description' => 'Salario parcial',
            'transaction_date' => '2026-05-21',
            'notes' => 'Pagamento recebido',
            'is_paid' => '1',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('financial-transactions.index'));

        $this->assertDatabaseHas('financial_transactions', [
            'user_id' => $user->id,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'type' => 'income',
            'amount' => '250.35',
            'description' => 'Salario parcial',
            'is_paid' => true,
        ]);
        $this->assertSame('350.35', $account->refresh()->current_balance);
    }

    public function test_paid_expense_decreases_account_balance(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create([
            'current_balance' => '500.00',
        ]);
        $category = Category::factory()->for($user)->expense()->create();

        $this->actingAs($user)->post(route('financial-transactions.store'), [
            'account_id' => $account->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => '42.10',
            'transaction_date' => '2026-05-21',
            'is_paid' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertSame('457.90', $account->refresh()->current_balance);
    }

    public function test_pending_transaction_does_not_change_account_balance(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create([
            'current_balance' => '500.00',
        ]);
        $category = Category::factory()->for($user)->expense()->create();

        $this->actingAs($user)->post(route('financial-transactions.store'), [
            'account_id' => $account->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => '42.10',
            'transaction_date' => '2026-05-21',
            'is_paid' => '0',
        ])->assertSessionHasNoErrors();

        $this->assertSame('500.00', $account->refresh()->current_balance);
    }

    public function test_user_cannot_store_transaction_with_records_from_another_user(): void
    {
        $user = User::factory()->create();
        $otherAccount = Account::factory()->create();
        $otherCategory = Category::factory()->expense()->create();

        $response = $this->actingAs($user)
            ->from(route('financial-transactions.create'))
            ->post(route('financial-transactions.store'), [
                'account_id' => $otherAccount->id,
                'category_id' => $otherCategory->id,
                'type' => 'expense',
                'amount' => '25.00',
                'transaction_date' => '2026-05-21',
                'is_paid' => '1',
            ]);

        $response
            ->assertSessionHasErrors(['account_id', 'category_id'])
            ->assertRedirect(route('financial-transactions.create'));
    }

    public function test_category_type_must_match_transaction_type(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $expenseCategory = Category::factory()->for($user)->expense()->create();

        $response = $this->actingAs($user)
            ->from(route('financial-transactions.create'))
            ->post(route('financial-transactions.store'), [
                'account_id' => $account->id,
                'category_id' => $expenseCategory->id,
                'type' => 'income',
                'amount' => '25.00',
                'transaction_date' => '2026-05-21',
                'is_paid' => '1',
            ]);

        $response
            ->assertSessionHasErrors('category_id')
            ->assertRedirect(route('financial-transactions.create'));
    }
}
