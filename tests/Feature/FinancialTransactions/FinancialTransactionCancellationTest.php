<?php

namespace Tests\Feature\FinancialTransactions;

use App\Models\Account;
use App\Models\Category;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialTransactionCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_cancel_paid_expense_and_restore_account_balance(): void
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

        $response = $this->actingAs($user)->patch(route('financial-transactions.cancel', $transaction));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('financial-transactions.index'));

        $this->assertNotNull($transaction->refresh()->cancelled_at);
        $this->assertSame('500.00', $account->refresh()->current_balance);
        $this->assertDatabaseHas('financial_transactions', [
            'id' => $transaction->id,
        ]);
    }

    public function test_pending_transaction_can_be_cancelled_without_changing_balance(): void
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

        $this->actingAs($user)->patch(route('financial-transactions.cancel', $transaction))
            ->assertSessionHasNoErrors();

        $this->assertNotNull($transaction->refresh()->cancelled_at);
        $this->assertSame('500.00', $account->refresh()->current_balance);
    }

    public function test_cancelling_transaction_twice_does_not_reverse_balance_twice(): void
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

        $this->actingAs($user)->patch(route('financial-transactions.cancel', $transaction));
        $this->actingAs($user)->patch(route('financial-transactions.cancel', $transaction));

        $this->assertSame('500.00', $account->refresh()->current_balance);
    }

    public function test_cancelled_transaction_is_shown_in_history_and_excluded_from_paid_total(): void
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
                'description' => 'Despesa confirmada',
                'amount' => '30.00',
                'is_paid' => true,
            ]);

        $cancelledTransaction = FinancialTransaction::factory()
            ->for($user)
            ->for($account)
            ->for($category)
            ->expense()
            ->create([
                'description' => 'Despesa cancelada',
                'amount' => '70.00',
                'is_paid' => true,
                'cancelled_at' => now(),
            ]);

        $response = $this->actingAs($user)->get(route('financial-transactions.index'));

        $response
            ->assertOk()
            ->assertSee('Despesa confirmada')
            ->assertSee('Despesa cancelada')
            ->assertSee('Cancelado')
            ->assertDontSee(route('financial-transactions.edit', $cancelledTransaction), false);

        $this->assertSame('30.00', number_format((float) $response->viewData('paidExpenseTotal'), 2, '.', ''));
    }

    public function test_cancelled_transaction_cannot_be_edited(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $category = Category::factory()->for($user)->expense()->create();
        $transaction = FinancialTransaction::factory()
            ->for($user)
            ->for($account)
            ->for($category)
            ->expense()
            ->create([
                'cancelled_at' => now(),
            ]);

        $this->actingAs($user)->get(route('financial-transactions.edit', $transaction))
            ->assertNotFound();

        $this->actingAs($user)->patch(route('financial-transactions.update', $transaction), [
            'account_id' => $account->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => '25.00',
            'transaction_date' => '2026-05-22',
            'is_paid' => '1',
        ])->assertNotFound();
    }

    public function test_user_cannot_cancel_transaction_from_another_user(): void
    {
        $user = User::factory()->create();
        $otherTransaction = FinancialTransaction::factory()->expense()->create();

        $this->actingAs($user)->patch(route('financial-transactions.cancel', $otherTransaction))
            ->assertNotFound();
    }
}
