<?php

namespace Tests\Feature\Finance;

use App\Enums\AccountBalanceMovementOperation;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\AccountBalanceMovement;
use App\Models\Category;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountBalanceMovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_income_records_applied_balance_movement(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create([
            'current_balance' => '100.00',
        ]);
        $category = Category::factory()->for($user)->income()->create();

        $this->actingAs($user)->post(route('financial-transactions.store'), [
            'account_id' => $account->id,
            'category_id' => $category->id,
            'type' => 'income',
            'amount' => '250.35',
            'transaction_date' => '2026-05-25',
            'is_paid' => '1',
        ])->assertSessionHasNoErrors();

        $transaction = FinancialTransaction::query()->firstOrFail();
        $movement = AccountBalanceMovement::query()->firstOrFail();

        $this->assertSame(AccountBalanceMovementOperation::Applied, $movement->operation);
        $this->assertSame(TransactionType::Income, $movement->transaction_type);
        $this->assertSame($user->id, $movement->user_id);
        $this->assertSame($account->id, $movement->account_id);
        $this->assertSame($transaction->id, $movement->financial_transaction_id);
        $this->assertSame('250.35', $movement->amount);
        $this->assertSame('250.35', $movement->impact_amount);
        $this->assertSame('100.00', $movement->balance_before);
        $this->assertSame('350.35', $movement->balance_after);
    }

    public function test_pending_transaction_does_not_record_balance_movement(): void
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
            'amount' => '80.00',
            'transaction_date' => '2026-05-25',
            'is_paid' => '0',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('account_balance_movements', 0);
        $this->assertSame('500.00', $account->refresh()->current_balance);
    }

    public function test_updating_paid_transaction_records_reversal_and_new_application(): void
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

        $this->actingAs($user)->patch(route('financial-transactions.update', $transaction), [
            'account_id' => $account->id,
            'category_id' => $category->id,
            'type' => 'income',
            'amount' => '300.00',
            'transaction_date' => '2026-05-25',
            'is_paid' => '1',
        ])->assertSessionHasNoErrors();

        $movements = AccountBalanceMovement::query()
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $movements);

        $this->assertSame(AccountBalanceMovementOperation::Reversed, $movements[0]->operation);
        $this->assertSame('-250.00', $movements[0]->impact_amount);
        $this->assertSame('350.00', $movements[0]->balance_before);
        $this->assertSame('100.00', $movements[0]->balance_after);

        $this->assertSame(AccountBalanceMovementOperation::Applied, $movements[1]->operation);
        $this->assertSame('300.00', $movements[1]->impact_amount);
        $this->assertSame('100.00', $movements[1]->balance_before);
        $this->assertSame('400.00', $movements[1]->balance_after);
    }

    public function test_cancelling_paid_expense_records_one_reversal_movement(): void
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

        $this->actingAs($user)->patch(route('financial-transactions.cancel', $transaction))
            ->assertSessionHasNoErrors();
        $this->actingAs($user)->patch(route('financial-transactions.cancel', $transaction))
            ->assertSessionHasNoErrors();

        $movement = AccountBalanceMovement::query()->firstOrFail();

        $this->assertDatabaseCount('account_balance_movements', 1);
        $this->assertSame(AccountBalanceMovementOperation::Reversed, $movement->operation);
        $this->assertSame(TransactionType::Expense, $movement->transaction_type);
        $this->assertSame('50.00', $movement->amount);
        $this->assertSame('50.00', $movement->impact_amount);
        $this->assertSame('450.00', $movement->balance_before);
        $this->assertSame('500.00', $movement->balance_after);
    }
}
