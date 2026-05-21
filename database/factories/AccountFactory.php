<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $balance = fake()->randomFloat(2, 0, 10000);

        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->words(2, true),
            'institution' => fake()->optional()->company(),
            'type' => fake()->randomElement(['checking', 'savings', 'cash', 'credit_card']),
            'initial_balance' => $balance,
            'current_balance' => $balance,
            'currency' => 'BRL',
            'is_active' => true,
        ];
    }
}
