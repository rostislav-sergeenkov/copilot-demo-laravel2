<?php

namespace Tests\Unit\Models;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that an expense can be created with valid data.
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
     * Test that expense has required fillable fields.
     */
    public function test_expense_has_required_fields(): void
    {
        $fillable = ['description', 'amount', 'category', 'date'];
        $expense = new Expense();

        $this->assertEquals($fillable, $expense->getFillable());
    }

    /**
     * Test that the date attribute is cast to a Carbon instance.
     */
    public function test_date_is_carbon_instance(): void
    {
        $expense = Expense::factory()->create([
            'date' => '2025-12-01',
        ]);

        $this->assertInstanceOf(Carbon::class, $expense->date);
        $this->assertEquals('2025-12-01', $expense->date->format('Y-m-d'));
    }

    /**
     * Test that the amount is cast to decimal.
     */
    public function test_amount_is_decimal(): void
    {
        $expense = Expense::factory()->create([
            'amount' => 123.45,
        ]);

        $this->assertEquals('123.45', $expense->amount);
        $this->assertIsString($expense->amount); // decimal:2 cast returns string
    }

    /**
     * Test that expense can be soft deleted.
     */
    public function test_expense_can_be_soft_deleted(): void
    {
        $expense = Expense::factory()->create();
        $expenseId = $expense->id;

        $expense->delete();

        $this->assertSoftDeleted('expenses', ['id' => $expenseId]);
    }

    /**
     * Test that soft deleted expense is not in default query.
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
     * Test that soft deleted expense can be restored.
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
     * Test that soft deleted expense can be retrieved with trashed.
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
     * Test that CATEGORIES constant contains expected values.
     */
    public function test_categories_constant_contains_expected_values(): void
    {
        $expectedCategories = [
            'Groceries',
            'Transport',
            'Housing and Utilities',
            'Restaurants and Cafes',
            'Health and Medicine',
            'Clothing & Footwear',
            'Entertainment',
        ];

        $this->assertEquals($expectedCategories, Expense::CATEGORIES);
        $this->assertCount(7, Expense::CATEGORIES);
    }

    /**
     * Test that validation rules method returns expected rules.
     */
    public function test_validation_rules_returns_expected_rules(): void
    {
        $rules = Expense::validationRules();

        $this->assertArrayHasKey('description', $rules);
        $this->assertArrayHasKey('amount', $rules);
        $this->assertArrayHasKey('category', $rules);
        $this->assertArrayHasKey('date', $rules);

        $this->assertContains('required', $rules['description']);
        $this->assertContains('string', $rules['description']);
        $this->assertContains('max:255', $rules['description']);

        $this->assertContains('required', $rules['amount']);
        $this->assertContains('numeric', $rules['amount']);
        $this->assertContains('min:0.01', $rules['amount']);

        $this->assertContains('required', $rules['date']);
        $this->assertContains('date', $rules['date']);
    }

    /**
     * Test that validation messages method returns expected messages.
     */
    public function test_validation_messages_returns_expected_messages(): void
    {
        $messages = Expense::validationMessages();

        $this->assertArrayHasKey('description.required', $messages);
        $this->assertArrayHasKey('amount.required', $messages);
        $this->assertArrayHasKey('category.required', $messages);
        $this->assertArrayHasKey('date.required', $messages);
        $this->assertArrayHasKey('date.before_or_equal', $messages);
    }

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
    }

    /**
     * Test factory today state.
     */
    public function test_factory_today_state(): void
    {
        $expense = Expense::factory()->today()->create();

        $this->assertEquals(Carbon::today()->format('Y-m-d'), $expense->date->format('Y-m-d'));
    }

    /**
     * Test expense can be found by category.
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
     * Test expense can be filtered by date range.
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
     * Test expense amount precision.
     */
    public function test_expense_amount_precision(): void
    {
        $expense = Expense::factory()->create([
            'amount' => 123.456789,
        ]);

        // Should be rounded/truncated to 2 decimal places
        $this->assertEquals('123.46', $expense->fresh()->amount);
    }

    /**
     * Test expense can be updated.
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

        $this->assertEquals('Updated Description', $expense->fresh()->description);
        $this->assertEquals('75.00', $expense->fresh()->amount);
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
}
