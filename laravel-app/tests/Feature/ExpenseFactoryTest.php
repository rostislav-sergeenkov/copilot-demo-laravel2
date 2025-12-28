<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration tests for Expense factory behavior
 * Tests factory states, data generation, and validation
 */
class ExpenseFactoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that factory generates valid expense data.
     */
    public function test_factory_generates_valid_data(): void
    {
        $expense = Expense::factory()->create();

        $this->assertNotEmpty($expense->description);
        $this->assertGreaterThan(0, $expense->amount);
        $this->assertContains($expense->category, Expense::CATEGORIES);
        $this->assertInstanceOf(Carbon::class, $expense->date);
        $this->assertLessThanOrEqual(Carbon::today(), $expense->date);
        $this->assertDatabaseHas('expenses', ['id' => $expense->id]);
    }

    /**
     * Test factory can create multiple expenses.
     */
    public function test_factory_can_create_multiple_expenses(): void
    {
        Expense::factory()->count(5)->create();

        $this->assertCount(5, Expense::all());
    }

    /**
     * Test factory category state.
     */
    public function test_factory_category_state(): void
    {
        $expense = Expense::factory()->category('Transport')->create();

        $this->assertEquals('Transport', $expense->category);
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'category' => 'Transport',
        ]);
    }

    /**
     * Test factory today state.
     */
    public function test_factory_today_state(): void
    {
        $expense = Expense::factory()->today()->create();

        $this->assertEquals(Carbon::today()->format('Y-m-d'), $expense->date->format('Y-m-d'));
        
        // Verify in database - date column stores date as Y-m-d format
        $retrieved = Expense::find($expense->id);
        $this->assertNotNull($retrieved);
        $this->assertEquals(Carbon::today()->format('Y-m-d'), $retrieved->date->format('Y-m-d'));
    }

    /**
     * Test all valid categories can be stored via factory.
     */
    public function test_all_valid_categories_can_be_stored(): void
    {
        foreach (Expense::CATEGORIES as $category) {
            $expense = Expense::factory()->create(['category' => $category]);
            
            $this->assertEquals($category, $expense->category);
            $this->assertDatabaseHas('expenses', [
                'id' => $expense->id,
                'category' => $category,
            ]);
        }
    }

    /**
     * Test factory generates valid expenses across all categories.
     */
    public function test_factory_generates_valid_expenses_for_all_categories(): void
    {
        foreach (Expense::CATEGORIES as $category) {
            $expense = Expense::factory()->category($category)->create();

            $this->assertEquals($category, $expense->category);
            $this->assertContains($expense->category, Expense::CATEGORIES);
        }
    }

    /**
     * Test factory generates expenses with valid amount distribution.
     */
    public function test_factory_generates_valid_amount_distribution(): void
    {
        $expenses = Expense::factory()->count(100)->create();

        foreach ($expenses as $expense) {
            $this->assertGreaterThanOrEqual(0.01, $expense->amount);
            $this->assertLessThanOrEqual(999999.99, $expense->amount);
            // Amount should have at most 2 decimal places
            $this->assertMatchesRegularExpression('/^\d+\.\d{2}$/', $expense->amount);
        }
    }

    /**
     * Test factory generates expenses with valid date range.
     */
    public function test_factory_generates_expenses_with_valid_date_range(): void
    {
        $expenses = Expense::factory()->count(50)->create();

        foreach ($expenses as $expense) {
            $this->assertInstanceOf(Carbon::class, $expense->date);
            $this->assertTrue(
                $expense->date->isPast() || $expense->date->isToday(),
                'Expense date should not be in the future'
            );
        }
    }

    /**
     * Test amount accepts minimum value 0.01.
     */
    public function test_amount_accepts_minimum_value(): void
    {
        $expense = Expense::factory()->create(['amount' => 0.01]);

        $this->assertEquals('0.01', $expense->amount);
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'amount' => 0.01]);
    }

    /**
     * Test amount accepts maximum value.
     */
    public function test_amount_accepts_maximum_value(): void
    {
        $expense = Expense::factory()->create(['amount' => 999999.99]);

        $this->assertEquals('999999.99', $expense->amount);
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'amount' => 999999.99]);
    }

    /**
     * Test factory generates expenses with proper persistence.
     */
    public function test_factory_expenses_are_persisted(): void
    {
        $expense = Expense::factory()->create([
            'description' => 'Factory Test Expense',
            'amount' => 42.50,
            'category' => 'Groceries',
        ]);

        // Verify in database
        $this->assertDatabaseHas('expenses', [
            'description' => 'Factory Test Expense',
            'amount' => 42.50,
            'category' => 'Groceries',
        ]);

        // Verify can be retrieved
        $retrieved = Expense::find($expense->id);
        $this->assertNotNull($retrieved);
        $this->assertEquals('Factory Test Expense', $retrieved->description);
    }
}
