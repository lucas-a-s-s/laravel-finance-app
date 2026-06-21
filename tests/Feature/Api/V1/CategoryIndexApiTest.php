<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryIndexApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_categories_api(): void
    {
        $this->getJson('/api/v1/categories')
            ->assertUnauthorized();
    }

    public function test_user_can_list_only_their_categories_from_api(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Category::factory()->for($user)->expense()->create([
            'name' => 'Mercado',
            'color' => '#DC2626',
            'icon' => 'cart',
            'is_active' => true,
        ]);
        Category::factory()->for($user)->income()->create([
            'name' => 'Salario',
            'color' => '#059669',
            'icon' => 'briefcase',
            'is_active' => false,
        ]);
        Category::factory()->for($otherUser)->expense()->create([
            'name' => 'Categoria privada',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/categories')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Mercado')
            ->assertJsonPath('data.0.type', 'expense')
            ->assertJsonPath('data.0.color', '#DC2626')
            ->assertJsonPath('data.0.icon', 'cart')
            ->assertJsonPath('data.0.is_active', true)
            ->assertJsonPath('data.1.name', 'Salario')
            ->assertJsonMissing(['name' => 'Categoria privada']);
    }

    public function test_user_can_filter_categories_api_by_type_and_active_status(): void
    {
        $user = User::factory()->create();

        Category::factory()->for($user)->expense()->create([
            'name' => 'Mercado ativo',
            'is_active' => true,
        ]);
        Category::factory()->for($user)->expense()->create([
            'name' => 'Mercado inativo',
            'is_active' => false,
        ]);
        Category::factory()->for($user)->income()->create([
            'name' => 'Receita ativa',
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/categories?type=expense&is_active=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Mercado ativo')
            ->assertJsonMissing(['name' => 'Mercado inativo'])
            ->assertJsonMissing(['name' => 'Receita ativa']);
    }

    public function test_categories_api_validates_filters(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/categories?type=invalid&is_active=maybe')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['type', 'is_active']);
    }
}
