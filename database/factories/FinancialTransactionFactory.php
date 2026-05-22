<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialTransaction>
 */
class FinancialTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(TransactionType::cases());

        return [
            'user_id' => User::factory(),
            'account_id' => Account::factory(),
            'category_id' => Category::factory()->state([
                'type' => $type,
            ]),
            'type' => $type,
            'amount' => fake()->randomFloat(2, 10, 2000),
            'description' => fake()->sentence(3),
            'transaction_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'notes' => fake()->optional()->sentence(),
            'is_paid' => true,
            'cancelled_at' => null,
        ];
    }

    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $attributes['category_id'] ?? Category::factory()->income(),
            'type' => TransactionType::Income,
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $attributes['category_id'] ?? Category::factory()->expense(),
            'type' => TransactionType::Expense,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_paid' => false,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'cancelled_at' => now(),
        ]);
    }
}
