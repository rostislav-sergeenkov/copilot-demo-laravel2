<?php

namespace Database\Seeders;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Sample descriptions organized by category.
     *
     * @var array<string, array<int, string>>
     */
    protected array $descriptions = [
        'Groceries' => [
            'Weekly groceries',
            'Fruits and vegetables',
            'Milk and bread',
            'Organic produce',
            'Snacks and drinks',
            'Meat and fish',
            'Dairy products',
            'Bakery items',
        ],
        'Transport' => [
            'Gas station',
            'Bus ticket',
            'Uber ride',
            'Parking fee',
            'Metro pass',
            'Taxi fare',
            'Car wash',
            'Toll road fee',
        ],
        'Housing and Utilities' => [
            'Electric bill',
            'Water bill',
            'Internet service',
            'Monthly rent',
            'Gas bill',
            'Home insurance',
            'Phone bill',
            'Maintenance fee',
        ],
        'Restaurants and Cafes' => [
            'Lunch at cafe',
            'Dinner with friends',
            'Morning coffee',
            'Business lunch',
            'Weekend brunch',
            'Pizza delivery',
            'Fast food',
            'Ice cream shop',
        ],
        'Health and Medicine' => [
            'Pharmacy',
            'Doctor visit',
            'Vitamins and supplements',
            'Dental checkup',
            'Eye exam',
            'Prescription medicine',
            'First aid supplies',
            'Health insurance',
        ],
        'Clothing & Footwear' => [
            'New shoes',
            'Winter jacket',
            'T-shirts',
            'Jeans',
            'Sports wear',
            'Accessories',
            'Work clothes',
            'Socks and underwear',
        ],
        'Entertainment' => [
            'Movie tickets',
            'Netflix subscription',
            'Concert tickets',
            'Video game',
            'Books',
            'Spotify subscription',
            'Museum visit',
            'Sports event',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing expenses to make seeder idempotent
        Expense::query()->forceDelete();

        $expenses = [];
        $now = Carbon::now();

        // Generate expenses for the last 3 months
        foreach (Expense::CATEGORIES as $category) {
            $categoryDescriptions = $this->descriptions[$category];

            // Generate 7 expenses per category (total 49 expenses)
            $count = 7;

            for ($i = 0; $i < $count; $i++) {
                // Random date within the last 3 months
                $date = $now->copy()->subDays(rand(0, 90));

                // Random amount based on category
                $amount = $this->getAmountForCategory($category);

                // Random description from category
                $description = $categoryDescriptions[array_rand($categoryDescriptions)];

                $expenses[] = [
                    'description' => $description,
                    'amount' => $amount,
                    'category' => $category,
                    'date' => $date->format('Y-m-d'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Shuffle to mix categories
        shuffle($expenses);

        // Insert all expenses
        Expense::insert($expenses);

        $this->command->info('Created ' . count($expenses) . ' sample expenses.');
    }

    /**
     * Get a realistic amount range based on category.
     */
    protected function getAmountForCategory(string $category): float
    {
        $ranges = [
            'Groceries' => [15.00, 150.00],
            'Transport' => [2.00, 80.00],
            'Housing and Utilities' => [50.00, 500.00],
            'Restaurants and Cafes' => [5.00, 100.00],
            'Health and Medicine' => [10.00, 200.00],
            'Clothing & Footwear' => [20.00, 250.00],
            'Entertainment' => [5.00, 150.00],
        ];

        [$min, $max] = $ranges[$category] ?? [1.00, 100.00];

        // Generate random amount with 2 decimal places
        return round(rand($min * 100, $max * 100) / 100, 2);
    }
}
