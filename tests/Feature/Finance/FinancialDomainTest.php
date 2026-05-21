<?php

namespace Tests\Feature\Finance;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_own_financial_domain_records(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create([
            'name' => 'Conta principal',
        ]);
        $category = Category::factory()->for($user)->expense()->create([
            'name' => 'Mercado',
        ]);

        $transaction = FinancialTransaction::factory()
            ->for($user)
            ->for($account)
            ->for($category)
            ->expense()
            ->create([
                'amount' => 123.45,
                'transaction_date' => '2026-05-21',
            ]);

        $this->assertTrue($user->accounts()->whereKey($account->getKey())->exists());
        $this->assertTrue($user->categories()->whereKey($category->getKey())->exists());
        $this->assertTrue($user->financialTransactions()->whereKey($transaction->getKey())->exists());

        $this->assertTrue($transaction->user->is($user));
        $this->assertTrue($transaction->account->is($account));
        $this->assertTrue($transaction->category->is($category));
        $this->assertSame(TransactionType::Expense, $transaction->type);
        $this->assertSame('123.45', $transaction->amount);
        $this->assertSame('2026-05-21', $transaction->transaction_date->toDateString());
    }

    public function test_user_deletion_cascades_financial_domain_records(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $category = Category::factory()->for($user)->income()->create();
        $transaction = FinancialTransaction::factory()
            ->for($user)
            ->for($account)
            ->for($category)
            ->income()
            ->create();

        $user->delete();

        $this->assertDatabaseMissing('accounts', ['id' => $account->id]);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $this->assertDatabaseMissing('financial_transactions', ['id' => $transaction->id]);
    }
}
