<?php

namespace Tests\Feature\Api\V1;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountIndexApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_accounts_api(): void
    {
        $this->getJson('/api/v1/accounts')
            ->assertUnauthorized();
    }

    public function test_user_can_list_only_their_accounts_from_api(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Account::factory()->for($user)->create([
            'name' => 'Conta Corrente',
            'institution' => 'Banco Principal',
            'type' => 'checking',
            'current_balance' => '1000.00',
            'is_active' => true,
        ]);
        Account::factory()->for($user)->create([
            'name' => 'Carteira',
            'type' => 'cash',
            'current_balance' => '150.50',
            'is_active' => false,
        ]);
        Account::factory()->for($otherUser)->create([
            'name' => 'Conta privada',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/accounts')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Conta Corrente')
            ->assertJsonPath('data.0.institution', 'Banco Principal')
            ->assertJsonPath('data.0.type', 'checking')
            ->assertJsonPath('data.0.current_balance', '1000.00')
            ->assertJsonPath('data.0.is_active', true)
            ->assertJsonPath('data.1.name', 'Carteira')
            ->assertJsonMissing(['name' => 'Conta privada']);
    }
}
