<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration tests for Expense workflows and database operations
 * Testing Trophy approach: Tests complete workflows with real database
 */
class ExpenseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test complete expense creation workflow.
     */
    public function test_can_create_expense_with_valid_data(): void
    {
        $expense = Expense::create([
            'description' => 'Test Expense',
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => '2025-12-01',
        ]);

        $this->assertDatabaseHas('expenses', [
            'description' => 'Test Expense',
            'amount' => 50.00,
            'category' => 'Groceries',
        ]);

        $this->assertInstanceOf(Expense::class, $expense);
        $this->assertEquals('Test Expense', $expense->description);
    }

    /**
     * Test expense update workflow.
     */
    public function test_expense_can_be_updated(): void
    {
        $expense = Expense::factory()->create([
            'description' => 'Original Description',
            'amount' => 50.00,
        ]);

        $expense->update([
            'description' => 'Updated Description',
            'amount' => 75.00,
        ]);

        $freshExpense = $expense->fresh();
        $this->assertNotNull($freshExpense);
        $this->assertEquals('Updated Description', $freshExpense->description);
        $this->assertEquals('75.00', $freshExpense->amount);
    }

    /**
     * Test soft delete workflow.
     */
    public function test_expense_can_be_soft_deleted(): void
    {
        $expense = Expense::factory()->create();
        $expenseId = $expense->id;

        $expense->delete();

        $this->assertSoftDeleted('expenses', ['id' => $expenseId]);
    }

    /**
     * Test soft deleted expense not in default query.
     */
    public function test_soft_deleted_expense_not_in_default_query(): void
    {
        $expense = Expense::factory()->create();
        $expenseId = $expense->id;

        $expense->delete();

        $this->assertNull(Expense::find($expenseId));
        $this->assertCount(0, Expense::all());
    }

    /**
     * Test soft deleted expense restore workflow.
     */
    public function test_soft_deleted_expense_can_be_restored(): void
    {
        $expense = Expense::factory()->create();
        $expenseId = $expense->id;

        $expense->delete();
        $this->assertSoftDeleted('expenses', ['id' => $expenseId]);

        $expense->restore();
        $this->assertDatabaseHas('expenses', ['id' => $expenseId, 'deleted_at' => null]);
        $this->assertNotNull(Expense::find($expenseId));
    }

    /**
     * Test retrieving soft deleted expense with trashed scope.
     */
    public function test_soft_deleted_expense_can_be_retrieved_with_trashed(): void
    {
        $expense = Expense::factory()->create();
        $expenseId = $expense->id;

        $expense->delete();

        $trashedExpense = Expense::withTrashed()->find($expenseId);
        $this->assertNotNull($trashedExpense);
        $this->assertNotNull($trashedExpense->deleted_at);
    }

    /**
     * Test filtering expenses by category.
     */
    public function test_expense_can_be_found_by_category(): void
    {
        Expense::factory()->category('Groceries')->count(3)->create();
        Expense::factory()->category('Transport')->count(2)->create();

        $groceries = Expense::where('category', 'Groceries')->get();
        $transport = Expense::where('category', 'Transport')->get();

        $this->assertCount(3, $groceries);
        $this->assertCount(2, $transport);
    }

    /**
     * Test filtering expenses by date range.
     */
    public function test_expense_can_be_filtered_by_date_range(): void
    {
        Expense::factory()->create(['date' => '2025-11-01']);
        Expense::factory()->create(['date' => '2025-11-15']);
        Expense::factory()->create(['date' => '2025-12-01']);

        $novemberExpenses = Expense::whereBetween('date', ['2025-11-01', '2025-11-30'])->get();

        $this->assertCount(2, $novemberExpenses);
    }

    /**
     * Test expense data types after persistence.
     */
    public function test_date_is_carbon_instance_after_retrieval(): void
    {
        $expense = Expense::factory()->create([
            'date' => '2025-12-01',
        ]);

        $this->assertInstanceOf(Carbon::class, $expense->date);
        $this->assertEquals('2025-12-01', $expense->date->format('Y-m-d'));
    }

    /**
     * Test amount decimal cast after persistence.
     */
    public function test_amount_is_decimal_string_after_retrieval(): void
    {
        $expense = Expense::factory()->create([
            'amount' => 123.45,
        ]);

        $this->assertEquals('123.45', $expense->amount);
        $this->assertIsString($expense->amount); // decimal:2 cast returns string
    }

    /**
     * Test expense timestamps are managed.
     */
    public function test_expense_has_timestamps(): void
    {
        $expense = Expense::factory()->create();

        $this->assertNotNull($expense->created_at);
        $this->assertNotNull($expense->updated_at);
        $this->assertInstanceOf(Carbon::class, $expense->created_at);
        $this->assertInstanceOf(Carbon::class, $expense->updated_at);
    }

    /**
     * Test sorting expenses by date descending.
     */
    public function test_expenses_sorted_by_date_desc(): void
    {
        Expense::factory()->create(['date' => '2025-11-01', 'description' => 'First']);
        Expense::factory()->create(['date' => '2025-12-01', 'description' => 'Third']);
        Expense::factory()->create(['date' => '2025-11-15', 'description' => 'Second']);

        $expenses = Expense::orderBy('date', 'desc')->get();

        $this->assertEquals('Third', $expenses[0]->description);
        $this->assertEquals('Second', $expenses[1]->description);
        $this->assertEquals('First', $expenses[2]->description);
    }

    /**
     * Test sorting expenses by date ascending.
     */
    public function test_expenses_sorted_by_date_asc(): void
    {
        Expense::factory()->create(['date' => '2025-12-01', 'description' => 'Third']);
        Expense::factory()->create(['date' => '2025-11-01', 'description' => 'First']);
        Expense::factory()->create(['date' => '2025-11-15', 'description' => 'Second']);

        $expenses = Expense::orderBy('date', 'asc')->get();

        $this->assertEquals('First', $expenses[0]->description);
        $this->assertEquals('Second', $expenses[1]->description);
        $this->assertEquals('Third', $expenses[2]->description);
    }

    /**
     * Test sorting expenses by amount.
     */
    public function test_expenses_sorted_by_amount(): void
    {
        Expense::factory()->create(['amount' => 50.00, 'description' => 'Medium']);
        Expense::factory()->create(['amount' => 100.00, 'description' => 'High']);
        Expense::factory()->create(['amount' => 10.00, 'description' => 'Low']);

        $expenses = Expense::orderBy('amount', 'asc')->get();

        $this->assertEquals('Low', $expenses[0]->description);
        $this->assertEquals('Medium', $expenses[1]->description);
        $this->assertEquals('High', $expenses[2]->description);
    }

    /**
     * Test grouping expenses by date.
     */
    public function test_grouping_expenses_by_date(): void
    {
        Expense::factory()->count(3)->create(['date' => '2025-12-01']);
        Expense::factory()->count(2)->create(['date' => '2025-12-02']);

        $grouped = Expense::selectRaw('DATE(date) as expense_date, COUNT(*) as count')
            ->groupBy('expense_date')
            ->get()
            ->pluck('count', 'expense_date');

        $this->assertEquals(3, $grouped['2025-12-01']);
        $this->assertEquals(2, $grouped['2025-12-02']);
    }

    /**
     * Test concurrent updates workflow.
     */
    public function test_handles_concurrent_updates_gracefully(): void
    {
        $expense = Expense::factory()->create(['amount' => 100.00]);

        // Simulate concurrent updates
        $expense1 = Expense::find($expense->id);
        $expense2 = Expense::find($expense->id);

        $expense1->update(['amount' => 200.00]);
        $expense2->update(['amount' => 300.00]);

        // Last update should win
        $freshExpense = $expense->fresh();
        $this->assertEquals('300.00', $freshExpense->amount);
    }

    /**
     * Test large dataset performance.
     */
    public function test_large_dataset_calculations(): void
    {
        // Create 50 expenses
        Expense::factory()->count(50)->create(['amount' => 10.00]);

        $total = Expense::sum('amount');

        $this->assertEquals('500.00', number_format((float) $total, 2, '.', ''));
        $this->assertCount(50, Expense::all());
    }
}
