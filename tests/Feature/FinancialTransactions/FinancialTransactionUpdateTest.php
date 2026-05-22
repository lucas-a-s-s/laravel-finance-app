<?php

namespace Tests\Feature\FinancialTransactions;

use App\Models\Account;
use App\Models\Category;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialTransactionUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_paid_transaction_and_rebalance_same_account(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create([
            'current_balance' => '350.00',
        ]);
        $category = Category::factory()->for($user)->income()->create();
        $transaction = FinancialTransaction::factory()
            ->for($user)
            ->for($account)
            ->for($category)
            ->income()
            ->create([
                'amount' => '250.00',
                'is_paid' => true,
            ]);

        $response = $this->actingAs($user)->patch(route('financial-transactions.update', $transaction), [
            'account_id' => $account->id,
            'category_id' => $category->id,
            'type' => 'income',
            'amount' => '300.00',
            'description' => 'Salario revisado',
            'transaction_date' => '2026-05-22',
            'notes' => 'Valor corrigido',
            'is_paid' => '1',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('financial-transactions.index'));

        $this->assertDatabaseHas('financial_transactions', [
            'id' => $transaction->id,
            'amount' => '300.00',
            'description' => 'Salario revisado',
            'is_paid' => true,
        ]);
        $this->assertSame('400.00', $account->refresh()->current_balance);
    }

    public function test_user_can_move_paid_transaction_to_another_account_and_type(): void
    {
        $user = User::factory()->create();
        $previousAccount = Account::factory()->for($user)->create([
            'current_balance' => '450.00',
        ]);
        $nextAccount = Account::factory()->for($user)->create([
            'current_balance' => '200.00',
        ]);
        $expenseCategory = Category::factory()->for($user)->expense()->create();
        $incomeCategory = Category::factory()->for($user)->income()->create();
        $transaction = FinancialTransaction::factory()
            ->for($user)
            ->for($previousAccount)
            ->for($expenseCategory)
            ->expense()
            ->create([
                'amount' => '50.00',
                'is_paid' => true,
            ]);

        $this->actingAs($user)->patch(route('financial-transactions.update', $transaction), [
            'account_id' => $nextAccount->id,
            'category_id' => $incomeCategory->id,
            'type' => 'income',
            'amount' => '80.00',
            'transaction_date' => '2026-05-22',
            'is_paid' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('financial_transactions', [
            'id' => $transaction->id,
            'account_id' => $nextAccount->id,
            'category_id' => $incomeCategory->id,
            'type' => 'income',
            'amount' => '80.00',
        ]);
        $this->assertSame('500.00', $previousAccount->refresh()->current_balance);
        $this->assertSame('280.00', $nextAccount->refresh()->current_balance);
    }

    public function test_paid_transaction_becomes_pending_and_reverses_previous_balance_effect(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create([
            'current_balance' => '450.00',
        ]);
        $category = Category::factory()->for($user)->expense()->create();
        $transaction = FinancialTransaction::factory()
            ->for($user)
            ->for($account)
            ->for($category)
            ->expense()
            ->create([
                'amount' => '50.00',
                'is_paid' => true,
            ]);

        $this->actingAs($user)->patch(route('financial-transactions.update', $transaction), [
            'account_id' => $account->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => '50.00',
            'transaction_date' => '2026-05-22',
            'is_paid' => '0',
        ])->assertSessionHasNoErrors();

        $this->assertFalse($transaction->refresh()->is_paid);
        $this->assertSame('500.00', $account->refresh()->current_balance);
    }

    public function test_pending_transaction_becomes_paid_and_applies_balance_effect(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create([
            'current_balance' => '500.00',
        ]);
        $category = Category::factory()->for($user)->expense()->create();
        $transaction = FinancialTransaction::factory()
            ->for($user)
            ->for($account)
            ->for($category)
            ->expense()
            ->pending()
            ->create([
                'amount' => '50.00',
            ]);

        $this->actingAs($user)->patch(route('financial-transactions.update', $transaction), [
            'account_id' => $account->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => '50.00',
            'transaction_date' => '2026-05-22',
            'is_paid' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertTrue($transaction->refresh()->is_paid);
        $this->assertSame('450.00', $account->refresh()->current_balance);
    }

    public function test_user_cannot_update_transaction_from_another_user(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $category = Category::factory()->for($user)->expense()->create();
        $otherTransaction = FinancialTransaction::factory()->expense()->create();

        $response = $this->actingAs($user)->patch(route('financial-transactions.update', $otherTransaction), [
            'account_id' => $account->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => '25.00',
            'transaction_date' => '2026-05-22',
            'is_paid' => '1',
        ]);

        $response->assertNotFound();
    }

    public function test_user_can_keep_inactive_references_while_editing_transaction(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create([
            'name' => 'Conta historica',
        ]);
        $category = Category::factory()->for($user)->expense()->create([
            'name' => 'Categoria historica',
        ]);
        $transaction = FinancialTransaction::factory()
            ->for($user)
            ->for($account)
            ->for($category)
            ->expense()
            ->pending()
            ->create();

        $account->update(['is_active' => false]);
        $category->update(['is_active' => false]);

        $this->actingAs($user)->get(route('financial-transactions.edit', $transaction))
            ->assertOk()
            ->assertSee('Conta historica')
            ->assertSee('Categoria historica');

        $response = $this->actingAs($user)->patch(route('financial-transactions.update', $transaction), [
            'account_id' => $account->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => '25.00',
            'description' => 'Historico revisado',
            'transaction_date' => '2026-05-22',
            'is_paid' => '0',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('financial-transactions.index'));

        $this->assertDatabaseHas('financial_transactions', [
            'id' => $transaction->id,
            'description' => 'Historico revisado',
        ]);
    }

    public function test_category_type_must_match_updated_transaction_type(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $expenseCategory = Category::factory()->for($user)->expense()->create();
        $transaction = FinancialTransaction::factory()
            ->for($user)
            ->for($account)
            ->for($expenseCategory)
            ->expense()
            ->create();

        $response = $this->actingAs($user)
            ->from(route('financial-transactions.edit', $transaction))
            ->patch(route('financial-transactions.update', $transaction), [
                'account_id' => $account->id,
                'category_id' => $expenseCategory->id,
                'type' => 'income',
                'amount' => '25.00',
                'transaction_date' => '2026-05-22',
                'is_paid' => '1',
            ]);

        $response
            ->assertSessionHasErrors('category_id')
            ->assertRedirect(route('financial-transactions.edit', $transaction));
    }
}
