<?php

namespace Tests\Feature\Categories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_categories_page(): void
    {
        $this->get(route('categories.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_view_only_their_categories(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownCategory = Category::factory()->for($user)->expense()->create([
            'name' => 'Mercado',
        ]);
        $otherCategory = Category::factory()->for($otherUser)->income()->create([
            'name' => 'Renda privada',
        ]);

        $response = $this->actingAs($user)->get(route('categories.index'));

        $response
            ->assertOk()
            ->assertSee($ownCategory->name)
            ->assertDontSee($otherCategory->name);
    }

    public function test_user_can_create_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Salario',
            'type' => 'income',
            'color' => '#059669',
            'icon' => 'briefcase',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Salario',
            'type' => 'income',
            'color' => '#059669',
            'icon' => 'briefcase',
            'is_active' => true,
        ]);
    }

    public function test_category_name_must_be_unique_per_user_and_type(): void
    {
        $user = User::factory()->create();
        Category::factory()->for($user)->expense()->create([
            'name' => 'Moradia',
        ]);

        $response = $this->actingAs($user)
            ->from(route('categories.create'))
            ->post(route('categories.store'), [
                'name' => 'Moradia',
                'type' => 'expense',
                'color' => '#DC2626',
                'icon' => 'home',
            ]);

        $response
            ->assertSessionHasErrors('name')
            ->assertRedirect(route('categories.create'));

        $allowedResponse = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Moradia',
            'type' => 'income',
            'color' => '#16A34A',
            'icon' => 'home',
        ]);

        $allowedResponse
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('categories.index'));
    }

    public function test_user_can_update_their_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->expense()->create([
            'name' => 'Transporte',
        ]);

        $response = $this->actingAs($user)->patch(route('categories.update', $category), [
            'name' => 'Mobilidade',
            'type' => 'expense',
            'color' => '#0EA5E9',
            'icon' => 'receipt',
            'is_active' => '1',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Mobilidade',
            'type' => 'expense',
            'color' => '#0EA5E9',
            'icon' => 'receipt',
            'is_active' => true,
        ]);
    }

    public function test_user_cannot_update_category_from_another_user(): void
    {
        $user = User::factory()->create();
        $otherCategory = Category::factory()->expense()->create([
            'name' => 'Categoria de terceiro',
        ]);

        $response = $this->actingAs($user)->patch(route('categories.update', $otherCategory), [
            'name' => 'Tentativa indevida',
            'type' => 'income',
            'color' => '#059669',
            'icon' => 'tag',
            'is_active' => '1',
        ]);

        $response->assertNotFound();

        $this->assertDatabaseHas('categories', [
            'id' => $otherCategory->id,
            'name' => 'Categoria de terceiro',
        ]);
    }

    public function test_user_can_deactivate_their_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->income()->create([
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->delete(route('categories.destroy', $category));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'is_active' => false,
        ]);
    }
}
