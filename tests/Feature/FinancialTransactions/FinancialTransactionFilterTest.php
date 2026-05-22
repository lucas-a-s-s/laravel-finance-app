<?php

namespace Tests\Feature\FinancialTransactions;

use App\Models\Account;
use App\Models\Category;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialTransactionFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_filter_transactions_by_period_type_account_and_category(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create([
            'name' => 'Conta filtrada',
        ]);
        $otherAccount = Account::factory()->for($user)->create();
        $category = Category::factory()->for($user)->expense()->create([
            'name' => 'Categoria filtrada',
        ]);
        $otherCategory = Category::factory()->for($user)->expense()->create();

        FinancialTransaction::factory()
            ->for($user)
            ->for($account)
            ->for($category)
            ->expense()
            ->create([
                'description' => 'Despesa encontrada',
                'amount' => '40.00',
                'transaction_date' => '2026-05-10',
                'is_paid' => true,
            ]);
        FinancialTransaction::factory()
            ->for($user)
            ->for($otherAccount)
            ->for($otherCategory)
            ->expense()
            ->create([
                'description' => 'Despesa de outra conta',
                'amount' => '70.00',
                'transaction_date' => '2026-05-10',
            ]);
        FinancialTransaction::factory()
            ->for($user)
            ->for($account)
            ->for(Category::factory()->for($user)->income())
            ->income()
            ->create([
                'description' => 'Receita fora do tipo',
                'amount' => '120.00',
                'transaction_date' => '2026-05-10',
            ]);
        FinancialTransaction::factory()
            ->for($user)
            ->for($account)
            ->for($category)
            ->expense()
            ->create([
                'description' => 'Despesa fora do periodo',
                'amount' => '15.00',
                'transaction_date' => '2026-04-30',
            ]);

        $response = $this->actingAs($user)->get(route('financial-transactions.index', [
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
            'type' => 'expense',
            'account_id' => $account->id,
            'category_id' => $category->id,
        ]));

        $response
            ->assertOk()
            ->assertSee('Despesa encontrada')
            ->assertDontSee('Despesa de outra conta')
            ->assertDontSee('Receita fora do tipo')
            ->assertDontSee('Despesa fora do periodo');

        $this->assertSame('40.00', number_format((float) $response->viewData('paidExpenseTotal'), 2, '.', ''));
        $this->assertSame('0.00', number_format((float) $response->viewData('paidIncomeTotal'), 2, '.', ''));
    }

    public function test_user_can_filter_transactions_by_status(): void
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
                'description' => 'Despesa paga',
                'is_paid' => true,
            ]);
        FinancialTransaction::factory()
            ->for($user)
            ->for($account)
            ->for($category)
            ->expense()
            ->pending()
            ->create([
                'description' => 'Despesa pendente',
            ]);
        FinancialTransaction::factory()
            ->for($user)
            ->for($account)
            ->for($category)
            ->expense()
            ->cancelled()
            ->create([
                'description' => 'Despesa cancelada',
            ]);

        $paidResponse = $this->actingAs($user)->get(route('financial-transactions.index', [
            'status' => 'paid',
        ]));

        $paidResponse
            ->assertOk()
            ->assertSee('Despesa paga')
            ->assertDontSee('Despesa pendente')
            ->assertDontSee('Despesa cancelada');

        $cancelledResponse = $this->actingAs($user)->get(route('financial-transactions.index', [
            'status' => 'cancelled',
        ]));

        $cancelledResponse
            ->assertOk()
            ->assertSee('Despesa cancelada')
            ->assertDontSee('Despesa paga')
            ->assertDontSee('Despesa pendente');
    }

    public function test_user_cannot_filter_transactions_with_records_from_another_user(): void
    {
        $user = User::factory()->create();
        $otherAccount = Account::factory()->create();
        $otherCategory = Category::factory()->expense()->create();

        $response = $this->actingAs($user)
            ->from(route('financial-transactions.index'))
            ->get(route('financial-transactions.index', [
                'account_id' => $otherAccount->id,
                'category_id' => $otherCategory->id,
            ]));

        $response
            ->assertSessionHasErrors(['account_id', 'category_id'])
            ->assertRedirect(route('financial-transactions.index'));
    }
}
