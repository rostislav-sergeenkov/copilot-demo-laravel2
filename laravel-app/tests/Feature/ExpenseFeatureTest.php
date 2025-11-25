<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Expense;

class ExpenseFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_expense(): void
    {
        $response = $this->post('/expenses', [
            'description' => 'Lunch',
            'amount' => 12.50,
            'category' => 'Restaurants and Cafes',
            'date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('expenses.index'));
        $this->assertDatabaseHas('expenses', ['description' => 'Lunch']);
    }

    public function test_category_filtering_returns_only_selected_category(): void
    {
        Expense::factory()->create([
            'description' => 'Bus Ticket',
            'amount' => 3.25,
            'category' => 'Transport',
            'date' => now()->toDateString(),
        ]);
        Expense::factory()->create([
            'description' => 'Apples',
            'amount' => 5.00,
            'category' => 'Groceries',
            'date' => now()->toDateString(),
        ]);

        $response = $this->get('/expenses?category=Transport');
        $response->assertStatus(200);
        $response->assertSee('Bus Ticket');
        $response->assertDontSee('Apples');
    }

    public function test_csv_export_returns_csv(): void
    {
        Expense::factory()->create([
            'description' => 'Shoes',
            'amount' => 60.00,
            'category' => 'Clothing & Footwear',
            'date' => now()->toDateString(),
        ]);

        $response = $this->get('/expenses/export/monthly-csv');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertSee('Shoes');
    }
}
