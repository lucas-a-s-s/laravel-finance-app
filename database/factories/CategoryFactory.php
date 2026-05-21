<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->words(2, true),
            'type' => fake()->randomElement(TransactionType::cases()),
            'color' => fake()->hexColor(),
            'icon' => fake()->randomElement(['wallet', 'home', 'cart', 'briefcase', 'receipt']),
            'is_active' => true,
        ];
    }

    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionType::Income,
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionType::Expense,
        ]);
    }
}
