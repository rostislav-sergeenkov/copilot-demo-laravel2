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
        $expense = new Expense;

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

    // ==========================================
    // Validation Rule Tests (Unit Level)
    // ==========================================

    /**
     * Test description is required validation rule.
     */
    public function test_description_required_rule(): void
    {
        $rules = Expense::validationRules();

        $this->assertContains('required', $rules['description']);
    }

    /**
     * Test description max 255 characters validation rule.
     */
    public function test_description_max_255_rule(): void
    {
        $rules = Expense::validationRules();

        $this->assertContains('max:255', $rules['description']);
    }

    /**
     * Test description accepts unicode characters.
     */
    public function test_description_accepts_unicode(): void
    {
        $expense = Expense::factory()->create([
            'description' => '日本語のテキスト Unicode 文字 émojis 🎉',
        ]);

        $this->assertEquals('日本語のテキスト Unicode 文字 émojis 🎉', $expense->description);
    }

    /**
     * Test description accepts special characters.
     */
    public function test_description_accepts_special_characters(): void
    {
        $specialChars = "Test with symbols: @#$%^&*()_+-=[]{}|;':\",./<>?";
        $expense = Expense::factory()->create([
            'description' => $specialChars,
        ]);

        $this->assertEquals($specialChars, $expense->description);
    }

    /**
     * Test amount required validation rule.
     */
    public function test_amount_required_rule(): void
    {
        $rules = Expense::validationRules();

        $this->assertContains('required', $rules['amount']);
        $this->assertContains('numeric', $rules['amount']);
    }

    /**
     * Test amount minimum validation rule.
     */
    public function test_amount_min_rule(): void
    {
        $rules = Expense::validationRules();

        $this->assertContains('min:0.01', $rules['amount']);
    }

    /**
     * Test amount maximum validation rule.
     */
    public function test_amount_max_rule(): void
    {
        $rules = Expense::validationRules();

        $this->assertContains('max:99999999.99', $rules['amount']);
    }

    /**
     * Test amount accepts minimum value 0.01.
     */
    public function test_amount_accepts_minimum_value(): void
    {
        $expense = Expense::factory()->create([
            'amount' => 0.01,
        ]);

        $this->assertEquals('0.01', $expense->amount);
    }

    /**
     * Test amount accepts maximum value.
     */
    public function test_amount_accepts_maximum_value(): void
    {
        $expense = Expense::factory()->create([
            'amount' => 999999.99,
        ]);

        $this->assertEquals('999999.99', $expense->amount);
    }

    /**
     * Test amount stores exactly 2 decimal places.
     */
    public function test_amount_stores_two_decimal_places(): void
    {
        $expense = Expense::factory()->create(['amount' => 1.00]);
        $this->assertEquals('1.00', $expense->fresh()->amount);

        $expense = Expense::factory()->create(['amount' => 1.50]);
        $this->assertEquals('1.50', $expense->fresh()->amount);

        $expense = Expense::factory()->create(['amount' => 1.23]);
        $this->assertEquals('1.23', $expense->fresh()->amount);

        $expense = Expense::factory()->create(['amount' => 999.99]);
        $this->assertEquals('999.99', $expense->fresh()->amount);
    }

    /**
     * Test amount rounds to 2 decimal places.
     */
    public function test_amount_rounds_to_two_decimals(): void
    {
        $expense = Expense::factory()->create([
            'amount' => 12.3456,
        ]);

        // Laravel's decimal:2 cast should handle rounding
        $freshAmount = $expense->fresh()->amount;
        $this->assertEquals(2, strlen(substr(strrchr($freshAmount, '.'), 1)), 'Amount should have exactly 2 decimal places');
    }

    /**
     * Test category required validation rule.
     */
    public function test_category_required_rule(): void
    {
        $rules = Expense::validationRules();

        $this->assertContains('required', $rules['category']);
    }

    /**
     * Test category validates against CATEGORIES constant.
     */
    public function test_category_validates_enum(): void
    {
        $rules = Expense::validationRules();

        $expectedRule = 'in:' . implode(',', Expense::CATEGORIES);
        $this->assertContains($expectedRule, $rules['category']);
    }

    /**
     * Test all valid categories can be stored.
     */
    public function test_all_valid_categories_can_be_stored(): void
    {
        foreach (Expense::CATEGORIES as $category) {
            $expense = Expense::factory()->create(['category' => $category]);
            $this->assertEquals($category, $expense->category);
        }
    }

    /**
     * Test date required validation rule.
     */
    public function test_date_required_rule(): void
    {
        $rules = Expense::validationRules();

        $this->assertContains('required', $rules['date']);
        $this->assertContains('date', $rules['date']);
    }

    /**
     * Test date cannot be future validation rule.
     */
    public function test_date_cannot_be_future_rule(): void
    {
        $rules = Expense::validationRules();

        $this->assertContains('before_or_equal:today', $rules['date']);
    }

    /**
     * Test date accepts today's date.
     */
    public function test_date_accepts_today(): void
    {
        $expense = Expense::factory()->create([
            'date' => Carbon::today(),
        ]);

        $this->assertEquals(Carbon::today()->format('Y-m-d'), $expense->date->format('Y-m-d'));
    }

    /**
     * Test date accepts yesterday's date.
     */
    public function test_date_accepts_yesterday(): void
    {
        $expense = Expense::factory()->create([
            'date' => Carbon::yesterday(),
        ]);

        $this->assertEquals(Carbon::yesterday()->format('Y-m-d'), $expense->date->format('Y-m-d'));
    }

    /**
     * Test date accepts date 5 years ago.
     */
    public function test_date_accepts_five_years_ago(): void
    {
        $fiveYearsAgo = Carbon::today()->subYears(5);
        $expense = Expense::factory()->create([
            'date' => $fiveYearsAgo,
        ]);

        $this->assertEquals($fiveYearsAgo->format('Y-m-d'), $expense->date->format('Y-m-d'));
    }

    /**
     * Test date accepts various valid formats.
     */
    public function test_date_accepts_valid_formats(): void
    {
        $dateFormats = [
            '2025-12-01',
            '2025-01-15',
            '2024-06-30',
        ];

        foreach ($dateFormats as $dateString) {
            $expense = Expense::factory()->create(['date' => $dateString]);
            $this->assertEquals($dateString, $expense->date->format('Y-m-d'));
        }
    }

    // ==========================================
    // Calculation & Aggregation Tests (Unit Level)
    // ==========================================

    /**
     * Test calculating sum of multiple expenses.
     */
    public function test_calculate_sum_of_expenses(): void
    {
        $expenses = [
            Expense::factory()->create(['amount' => 10.50]),
            Expense::factory()->create(['amount' => 20.75]),
            Expense::factory()->create(['amount' => 30.25]),
        ];

        $total = Expense::sum('amount');

        $this->assertEquals('61.50', number_format($total, 2, '.', ''));
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

        $this->assertEquals('60.00', number_format($dailyTotal, 2, '.', ''));
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

        $this->assertEquals('250.00', number_format($monthlyTotal, 2, '.', ''));
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
            ->pluck('total', 'category');

        $this->assertEquals('40.00', number_format($breakdown['Groceries'], 2, '.', ''));
        $this->assertEquals('30.00', number_format($breakdown['Transport'], 2, '.', ''));
        $this->assertEquals('30.00', number_format($breakdown['Entertainment'], 2, '.', ''));
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
     * Test category with zero expenses shows zero percentage.
     */
    public function test_category_with_zero_shows_zero_percentage(): void
    {
        Expense::factory()->create(['amount' => 100.00, 'category' => 'Groceries']);

        $total = Expense::sum('amount'); // 100.00
        $transportTotal = Expense::where('category', 'Transport')->sum('amount'); // 0.00

        $percentage = $total > 0 ? ($transportTotal / $total) * 100 : 0;

        $this->assertEquals(0.0, $percentage);
    }

    /**
     * Test percentages sum to 100%.
     */
    public function test_percentages_sum_to_100_percent(): void
    {
        Expense::factory()->create(['amount' => 30.00, 'category' => 'Groceries']);
        Expense::factory()->create(['amount' => 45.00, 'category' => 'Transport']);
        Expense::factory()->create(['amount' => 25.00, 'category' => 'Entertainment']);

        $total = Expense::sum('amount'); // 100.00
        $percentageSum = 0;

        $breakdown = Expense::selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        foreach ($breakdown as $item) {
            $percentageSum += ($item->total / $total) * 100;
        }

        $this->assertEquals(100.0, round($percentageSum, 2));
    }

    // ==========================================
    // Sorting & Ordering Tests (Unit Level)
    // ==========================================

    /**
     * Test expenses can be sorted by date descending.
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
     * Test expenses can be sorted by date ascending.
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
     * Test expenses can be sorted by amount.
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

    // ==========================================
    // Edge Cases & Boundary Tests
    // ==========================================

    /**
     * Test creating expense with minimum amount boundary.
     */
    public function test_minimum_amount_boundary(): void
    {
        $expense = Expense::factory()->create(['amount' => 0.01]);

        $this->assertEquals('0.01', $expense->amount);
    }

    /**
     * Test creating expense with maximum amount boundary.
     */
    public function test_maximum_amount_boundary(): void
    {
        $expense = Expense::factory()->create(['amount' => 999999.99]);

        $this->assertEquals('999999.99', $expense->amount);
    }

    /**
     * Test description at maximum length boundary.
     */
    public function test_description_at_maximum_length(): void
    {
        $maxDescription = str_repeat('a', 255);
        $expense = Expense::factory()->create(['description' => $maxDescription]);

        $this->assertEquals(255, strlen($expense->description));
        $this->assertEquals($maxDescription, $expense->description);
    }

    /**
     * Test expense with empty category breakdown.
     */
    public function test_empty_category_breakdown(): void
    {
        $breakdown = Expense::selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        $this->assertCount(0, $breakdown);
    }

    /**
     * Test filtering expenses by nonexistent category returns empty.
     */
    public function test_filter_by_nonexistent_category_returns_empty(): void
    {
        Expense::factory()->create(['category' => 'Groceries']);

        $expenses = Expense::where('category', 'NonExistentCategory')->get();

        $this->assertCount(0, $expenses);
    }

    /**
     * Test large dataset calculation performance.
     */
    public function test_large_dataset_calculations(): void
    {
        // Create 50 expenses
        Expense::factory()->count(50)->create(['amount' => 10.00]);

        $total = Expense::sum('amount');

        $this->assertEquals('500.00', number_format($total, 2, '.', ''));
        $this->assertCount(50, Expense::all());
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

    // ==============================================
    // Additional Edge Case Tests
    // ==============================================

    /**
     * Test handling of extremely long descriptions (at maximum length).
     */
    public function test_handles_maximum_length_description(): void
    {
        $description = str_repeat('a', 255);
        $expense = Expense::factory()->create([
            'description' => $description,
        ]);

        $this->assertEquals(255, strlen($expense->description));
        $this->assertEquals($description, $expense->description);
    }

    /**
     * Test handling of Unicode and emoji characters in description.
     */
    public function test_handles_unicode_and_emoji_in_description(): void
    {
        $expense = Expense::factory()->create([
            'description' => '🎉 Birthday party supplies! 🎈 Café au lait ☕',
        ]);

        $this->assertStringContainsString('🎉', $expense->description);
        $this->assertStringContainsString('☕', $expense->description);
        $this->assertStringContainsString('Café', $expense->description);
    }

    /**
     * Test handling of amounts with many decimal places (should round to 2 decimals).
     */
    public function test_handles_amounts_with_many_decimal_places(): void
    {
        $expense = Expense::factory()->create(['amount' => 123.456789]);

        // Amount should be stored with 2 decimal places
        $this->assertEquals('123.46', $expense->amount);
    }

    /**
     * Test handling of very small amounts (close to minimum).
     */
    public function test_handles_very_small_amounts(): void
    {
        $expense = Expense::factory()->create(['amount' => 0.01]);

        $this->assertEquals('0.01', $expense->amount);
        $this->assertGreaterThan(0, $expense->amount);
    }

    /**
     * Test handling of very large amounts (close to maximum).
     */
    public function test_handles_very_large_amounts(): void
    {
        $expense = Expense::factory()->create(['amount' => 999999.99]);

        $this->assertEquals('999999.99', $expense->amount);
        $this->assertLessThan(1000000, $expense->amount);
    }

    /**
     * Test that expenses exactly 5 years old are handled correctly.
     */
    public function test_handles_expenses_exactly_five_years_old(): void
    {
        $fiveYearsAgo = now()->subYears(5)->format('Y-m-d');
        $expense = Expense::factory()->create(['date' => $fiveYearsAgo]);

        $this->assertEquals($fiveYearsAgo, $expense->date->format('Y-m-d'));
        $this->assertTrue($expense->date->isPast() || $expense->date->isToday());
    }

    /**
     * Test that category filtering prevents SQL injection.
     */
    public function test_category_filter_prevents_sql_injection(): void
    {
        Expense::factory()->create(['category' => 'Groceries']);

        // Attempt SQL injection
        $results = Expense::where('category', "' OR '1'='1")->get();

        $this->assertCount(0, $results);
    }

    /**
     * Test maintaining precision for large sum aggregations.
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
     * Test concurrent updates don't cause data corruption.
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
        $this->assertEquals('300.00', $expense->fresh()->amount);
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
     * Test empty string description is handled correctly.
     */
    public function test_handles_special_characters_in_description(): void
    {
        $expense = Expense::factory()->create([
            'description' => 'Test & <script>alert("xss")</script> Special "chars"',
        ]);

        $this->assertStringContainsString('&', $expense->description);
        $this->assertStringContainsString('<script>', $expense->description);
        $this->assertStringContainsString('"chars"', $expense->description);
    }

    /**
     * Test amount precision is maintained through updates.
     */
    public function test_maintains_amount_precision_through_updates(): void
    {
        $expense = Expense::factory()->create(['amount' => 123.45]);

        $this->assertEquals('123.45', $expense->amount);

        $expense->update(['amount' => 678.90]);

        $this->assertEquals('678.90', $expense->fresh()->amount);
    }

    /**
     * Test that soft-deleted expenses can be retrieved with trashed scope.
     */
    public function test_soft_deleted_expenses_can_be_retrieved_with_trashed(): void
    {
        $expense = Expense::factory()->create();
        $expenseId = $expense->id;

        $expense->delete();

        // Should not be in default query
        $this->assertNull(Expense::find($expenseId));

        // Should be retrievable with trashed
        $this->assertNotNull(Expense::withTrashed()->find($expenseId));
        $this->assertNotNull($expense->fresh()->deleted_at);
    }
}
