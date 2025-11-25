<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Groceries',
            'Transport',
            'Housing and Utilities',
            'Restaurants and Cafes',
            'Health and Medicine',
            'Clothing & Footwear',
            'Entertainment',
        ];

        return [
            'description' => fake()->words(2, true),
            'amount' => fake()->randomFloat(2, 1, 200),
            'category' => fake()->randomElement($categories),
            'date' => fake()->dateTimeThisMonth()->format('Y-m-d'),
        ];
    }
}
