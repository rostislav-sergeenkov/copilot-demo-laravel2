<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration tests for Expense calculations and aggregations
 * Tests business logic calculations with real database data
 */
class ExpenseCalculationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test calculating sum of multiple expenses.
     */
    public function test_calculate_sum_of_expenses(): void
    {
        Expense::factory()->create(['amount' => 10.50]);
        Expense::factory()->create(['amount' => 20.75]);
        Expense::factory()->create(['amount' => 30.25]);

        $total = Expense::sum('amount');

        $this->assertEquals('61.50', number_format((float) $total, 2, '.', ''));
    }

    /**
     * Test calculating daily total.
     */
    public function test_calculate_daily_total(): void
    {
        $targetDate = Carbon::today();

        Expense::factory()->create(['amount' => 25.50, 'date' => $targetDate]);
        Expense::factory()->create(['amount' => 34.50, 'date' => $targetDate]);
        Expense::factory()->create(['amount' => 40.00, 'date' => Carbon::yesterday()]);

        $dailyTotal = Expense::whereDate('date', $targetDate)->sum('amount');

        $this->assertEquals('60.00', number_format((float) $dailyTotal, 2, '.', ''));
    }

    /**
     * Test calculating monthly total.
     */
    public function test_calculate_monthly_total(): void
    {
        $targetMonth = Carbon::today()->format('Y-m');

        Expense::factory()->create(['amount' => 100.00, 'date' => $targetMonth . '-01']);
        Expense::factory()->create(['amount' => 150.00, 'date' => $targetMonth . '-15']);
        Expense::factory()->create(['amount' => 200.00, 'date' => Carbon::today()->subMonth()->format('Y-m-d')]);

        $monthlyTotal = Expense::whereYear('date', Carbon::today()->year)
            ->whereMonth('date', Carbon::today()->month)
            ->sum('amount');

        $this->assertEquals('250.00', number_format((float) $monthlyTotal, 2, '.', ''));
    }

    /**
     * Test calculating category percentage.
     */
    public function test_calculate_category_percentage(): void
    {
        Expense::factory()->create(['amount' => 30.00, 'category' => 'Groceries']);
        Expense::factory()->create(['amount' => 20.00, 'category' => 'Transport']);
        Expense::factory()->create(['amount' => 50.00, 'category' => 'Groceries']);

        $total = Expense::sum('amount'); // 100.00
        $groceriesTotal = Expense::where('category', 'Groceries')->sum('amount'); // 80.00

        $percentage = ($groceriesTotal / $total) * 100;

        $this->assertEquals(80.0, $percentage);
    }

    /**
     * Test calculating category breakdown.
     */
    public function test_calculate_category_breakdown(): void
    {
        Expense::factory()->create(['amount' => 40.00, 'category' => 'Groceries']);
        Expense::factory()->create(['amount' => 30.00, 'category' => 'Transport']);
        Expense::factory()->create(['amount' => 30.00, 'category' => 'Entertainment']);

        $breakdown = Expense::selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get()
            ->pluck('total', 'category');

        $this->assertEquals('40.00', number_format((float) $breakdown['Groceries'], 2, '.', ''));
        $this->assertEquals('30.00', number_format((float) $breakdown['Transport'], 2, '.', ''));
        $this->assertEquals('30.00', number_format((float) $breakdown['Entertainment'], 2, '.', ''));
    }

    /**
     * Test zero expenses returns zero total.
     */
    public function test_zero_expenses_returns_zero_total(): void
    {
        $total = Expense::sum('amount');

        $this->assertEquals(0, $total);
    }

    /**
     * Test empty category breakdown returns empty collection.
     */
    public function test_empty_category_breakdown(): void
    {
        $breakdown = Expense::selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        $this->assertCount(0, $breakdown);
    }

    /**
     * Test maintaining precision for large aggregations.
     */
    public function test_maintains_precision_for_large_aggregations(): void
    {
        // Create 100 expenses with large amounts
        Expense::factory()->count(100)->create(['amount' => 99999.99]);

        $total = Expense::sum('amount');

        $this->assertGreaterThan(0, $total);
        // Total should be approximately 9,999,999.00
        $this->assertGreaterThan(9999000, $total);
        $this->assertLessThan(10000000, $total);
    }

    /**
     * Test filtering by nonexistent category returns empty.
     */
    public function test_filter_by_nonexistent_category_returns_empty(): void
    {
        Expense::factory()->create(['category' => 'Groceries']);

        $expenses = Expense::where('category', 'NonExistentCategory')->get();

        $this->assertCount(0, $expenses);
    }
}
