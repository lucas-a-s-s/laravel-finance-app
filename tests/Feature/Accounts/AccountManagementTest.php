<?php

namespace Tests\Feature\Accounts;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_accounts_page(): void
    {
        $this->get(route('accounts.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_view_only_their_accounts(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownAccount = Account::factory()->for($user)->create([
            'name' => 'Conta principal',
        ]);
        $otherAccount = Account::factory()->for($otherUser)->create([
            'name' => 'Conta privada',
        ]);

        $response = $this->actingAs($user)->get(route('accounts.index'));

        $response
            ->assertOk()
            ->assertSee($ownAccount->name)
            ->assertDontSee($otherAccount->name);
    }

    public function test_user_can_create_account(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('accounts.store'), [
            'name' => 'Banco Principal',
            'institution' => 'Banco Exemplo',
            'type' => 'checking',
            'initial_balance' => '1500.25',
            'currency' => 'brl',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('accounts.index'));

        $this->assertDatabaseHas('accounts', [
            'user_id' => $user->id,
            'name' => 'Banco Principal',
            'institution' => 'Banco Exemplo',
            'type' => 'checking',
            'initial_balance' => '1500.25',
            'current_balance' => '1500.25',
            'currency' => 'BRL',
            'is_active' => true,
        ]);
    }

    public function test_account_name_must_be_unique_per_user(): void
    {
        $user = User::factory()->create();
        Account::factory()->for($user)->create([
            'name' => 'Carteira',
        ]);

        $response = $this->actingAs($user)
            ->from(route('accounts.create'))
            ->post(route('accounts.store'), [
                'name' => 'Carteira',
                'type' => 'cash',
                'initial_balance' => '100.00',
                'currency' => 'BRL',
            ]);

        $response
            ->assertSessionHasErrors('name')
            ->assertRedirect(route('accounts.create'));
    }

    public function test_user_can_update_their_account(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create([
            'name' => 'Conta antiga',
            'current_balance' => '400.00',
        ]);

        $response = $this->actingAs($user)->patch(route('accounts.update', $account), [
            'name' => 'Conta atualizada',
            'institution' => 'Instituicao atualizada',
            'type' => 'savings',
            'currency' => 'usd',
            'is_active' => '1',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('accounts.index'));

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'name' => 'Conta atualizada',
            'institution' => 'Instituicao atualizada',
            'type' => 'savings',
            'currency' => 'USD',
            'current_balance' => '400.00',
            'is_active' => true,
        ]);
    }

    public function test_user_cannot_update_account_from_another_user(): void
    {
        $user = User::factory()->create();
        $otherAccount = Account::factory()->create([
            'name' => 'Conta de terceiro',
        ]);

        $response = $this->actingAs($user)->patch(route('accounts.update', $otherAccount), [
            'name' => 'Tentativa indevida',
            'type' => 'checking',
            'currency' => 'BRL',
            'is_active' => '1',
        ]);

        $response->assertNotFound();

        $this->assertDatabaseHas('accounts', [
            'id' => $otherAccount->id,
            'name' => 'Conta de terceiro',
        ]);
    }

    public function test_user_can_deactivate_their_account(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create([
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->delete(route('accounts.destroy', $account));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('accounts.index'));

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'is_active' => false,
        ]);
    }
}
