<?php

namespace Tests\Feature;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the index page displays expenses list.
     */
    public function test_index_displays_expenses_list(): void
    {
        Expense::factory()->count(3)->create();

        $response = $this->get(route('expenses.index'));

        $response->assertStatus(200);
        $response->assertViewIs('expenses.index');
        $response->assertViewHas('expenses');
    }

    /**
     * Test the index page shows all expenses.
     */
    public function test_index_shows_all_expenses(): void
    {
        $expenses = Expense::factory()->count(5)->create();

        $response = $this->get(route('expenses.index'));

        $response->assertStatus(200);
        foreach ($expenses as $expense) {
            $response->assertSee($expense->description);
        }
    }

    /**
     * Test the index page with category filter.
     */
    public function test_index_filters_by_category(): void
    {
        Expense::factory()->category('Groceries')->count(2)->create();
        Expense::factory()->category('Transport')->count(3)->create();

        $response = $this->get(route('expenses.index', ['category' => 'Groceries']));

        $response->assertStatus(200);
        $response->assertViewHas('expenses', function ($expenses) {
            return $expenses->count() === 2 &&
                   $expenses->every(fn ($e) => $e->category === 'Groceries');
        });
    }

    /**
     * Test the index page passes categories to view.
     */
    public function test_index_passes_categories_to_view(): void
    {
        $response = $this->get(route('expenses.index'));

        $response->assertStatus(200);
        $response->assertViewHas('categories', Expense::CATEGORIES);
    }

    /**
     * Test the daily expenses page.
     */
    public function test_daily_displays_expenses_grouped_by_day(): void
    {
        Expense::factory()->create(['date' => '2025-12-01']);
        Expense::factory()->create(['date' => '2025-12-01']);
        Expense::factory()->create(['date' => '2025-12-02']);

        $response = $this->get(route('expenses.daily'));

        $response->assertStatus(200);
        $response->assertViewIs('expenses.daily');
        $response->assertViewHas('expenses');
    }

    /**
     * Test the daily expenses page with category filter.
     */
    public function test_daily_filters_by_category(): void
    {
        Expense::factory()->category('Groceries')->create(['date' => '2025-12-01']);
        Expense::factory()->category('Transport')->create(['date' => '2025-12-01']);

        $response = $this->get(route('expenses.daily', ['category' => 'Groceries']));

        $response->assertStatus(200);
        $response->assertViewHas('expenses');
    }

    /**
     * Test the monthly expenses page.
     */
    public function test_monthly_displays_expenses_grouped_by_month(): void
    {
        Expense::factory()->create(['date' => '2025-11-15']);
        Expense::factory()->create(['date' => '2025-12-01']);

        $response = $this->get(route('expenses.monthly'));

        $response->assertStatus(200);
        $response->assertViewIs('expenses.monthly');
        $response->assertViewHas('expenses');
    }

    /**
     * Test the monthly expenses page with category filter.
     */
    public function test_monthly_filters_by_category(): void
    {
        Expense::factory()->category('Health and Medicine')->create(['date' => '2025-12-01']);
        Expense::factory()->category('Entertainment')->create(['date' => '2025-12-01']);

        $response = $this->get(route('expenses.monthly', ['category' => 'Health and Medicine']));

        $response->assertStatus(200);
        $response->assertViewHas('expenses');
    }

    /**
     * Test the create page displays expense form.
     */
    public function test_create_displays_expense_form(): void
    {
        $response = $this->get(route('expenses.create'));

        $response->assertStatus(200);
        $response->assertViewIs('expenses.create');
        $response->assertViewHas('categories');
    }

    /**
     * Test storing a new expense with valid data.
     */
    public function test_store_creates_expense_with_valid_data(): void
    {
        $expenseData = [
            'description' => 'Test Expense',
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => '2025-12-01',
        ];

        $response = $this->post(route('expenses.store'), $expenseData);

        $response->assertRedirect(route('expenses.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('expenses', [
            'description' => 'Test Expense',
            'category' => 'Groceries',
        ]);
    }

    /**
     * Test store fails with missing description.
     */
    public function test_store_fails_without_description(): void
    {
        $expenseData = [
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => '2025-12-01',
        ];

        $response = $this->post(route('expenses.store'), $expenseData);

        $response->assertSessionHasErrors('description');
        $this->assertDatabaseCount('expenses', 0);
    }

    /**
     * Test store fails with missing amount.
     */
    public function test_store_fails_without_amount(): void
    {
        $expenseData = [
            'description' => 'Test Expense',
            'category' => 'Groceries',
            'date' => '2025-12-01',
        ];

        $response = $this->post(route('expenses.store'), $expenseData);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('expenses', 0);
    }

    /**
     * Test store fails with invalid amount (negative).
     */
    public function test_store_fails_with_negative_amount(): void
    {
        $expenseData = [
            'description' => 'Test Expense',
            'amount' => -50.00,
            'category' => 'Groceries',
            'date' => '2025-12-01',
        ];

        $response = $this->post(route('expenses.store'), $expenseData);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('expenses', 0);
    }

    /**
     * Test store fails with zero amount.
     */
    public function test_store_fails_with_zero_amount(): void
    {
        $expenseData = [
            'description' => 'Test Expense',
            'amount' => 0,
            'category' => 'Groceries',
            'date' => '2025-12-01',
        ];

        $response = $this->post(route('expenses.store'), $expenseData);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('expenses', 0);
    }

    /**
     * Test store fails with invalid category.
     */
    public function test_store_fails_with_invalid_category(): void
    {
        $expenseData = [
            'description' => 'Test Expense',
            'amount' => 50.00,
            'category' => 'InvalidCategory',
            'date' => '2025-12-01',
        ];

        $response = $this->post(route('expenses.store'), $expenseData);

        $response->assertSessionHasErrors('category');
        $this->assertDatabaseCount('expenses', 0);
    }

    /**
     * Test store fails with missing date.
     */
    public function test_store_fails_without_date(): void
    {
        $expenseData = [
            'description' => 'Test Expense',
            'amount' => 50.00,
            'category' => 'Groceries',
        ];

        $response = $this->post(route('expenses.store'), $expenseData);

        $response->assertSessionHasErrors('date');
        $this->assertDatabaseCount('expenses', 0);
    }

    /**
     * Test store fails with future date.
     */
    public function test_store_fails_with_future_date(): void
    {
        $expenseData = [
            'description' => 'Test Expense',
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => Carbon::tomorrow()->format('Y-m-d'),
        ];

        $response = $this->post(route('expenses.store'), $expenseData);

        $response->assertSessionHasErrors('date');
        $this->assertDatabaseCount('expenses', 0);
    }

    /**
     * Test store fails with description exceeding max length.
     */
    public function test_store_fails_with_long_description(): void
    {
        $expenseData = [
            'description' => str_repeat('a', 256),
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => '2025-12-01',
        ];

        $response = $this->post(route('expenses.store'), $expenseData);

        $response->assertSessionHasErrors('description');
        $this->assertDatabaseCount('expenses', 0);
    }

    /**
     * Test show displays expense details.
     */
    public function test_show_displays_expense_details(): void
    {
        $expense = Expense::factory()->create([
            'description' => 'Test Expense Details',
        ]);

        $response = $this->get(route('expenses.show', $expense));

        $response->assertStatus(200);
        $response->assertViewIs('expenses.show');
        $response->assertViewHas('expense', $expense);
        $response->assertSee('Test Expense Details');
    }

    /**
     * Test show returns 404 for non-existent expense.
     */
    public function test_show_returns_404_for_non_existent_expense(): void
    {
        $response = $this->get(route('expenses.show', 999999));

        $response->assertStatus(404);
    }

    /**
     * Test edit displays expense edit form.
     */
    public function test_edit_displays_expense_form(): void
    {
        $expense = Expense::factory()->create();

        $response = $this->get(route('expenses.edit', $expense));

        $response->assertStatus(200);
        $response->assertViewIs('expenses.edit');
        $response->assertViewHas('expense', $expense);
        $response->assertViewHas('categories');
    }

    /**
     * Test edit returns 404 for non-existent expense.
     */
    public function test_edit_returns_404_for_non_existent_expense(): void
    {
        $response = $this->get(route('expenses.edit', 999999));

        $response->assertStatus(404);
    }

    /**
     * Test update modifies expense with valid data.
     */
    public function test_update_modifies_expense_with_valid_data(): void
    {
        $expense = Expense::factory()->create([
            'description' => 'Original Description',
            'amount' => 50.00,
        ]);

        $updateData = [
            'description' => 'Updated Description',
            'amount' => 75.00,
            'category' => 'Transport',
            'date' => '2025-12-01',
        ];

        $response = $this->put(route('expenses.update', $expense), $updateData);

        $response->assertRedirect(route('expenses.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'description' => 'Updated Description',
            'amount' => 75.00,
            'category' => 'Transport',
        ]);
    }

    /**
     * Test update fails with invalid data.
     */
    public function test_update_fails_with_invalid_data(): void
    {
        $expense = Expense::factory()->create([
            'description' => 'Original Description',
        ]);

        $updateData = [
            'description' => '',
            'amount' => -50.00,
            'category' => 'InvalidCategory',
            'date' => Carbon::tomorrow()->format('Y-m-d'),
        ];

        $response = $this->put(route('expenses.update', $expense), $updateData);

        $response->assertSessionHasErrors(['description', 'amount', 'category', 'date']);
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'description' => 'Original Description',
        ]);
    }

    /**
     * Test update returns 404 for non-existent expense.
     */
    public function test_update_returns_404_for_non_existent_expense(): void
    {
        $updateData = [
            'description' => 'Updated Description',
            'amount' => 75.00,
            'category' => 'Transport',
            'date' => '2025-12-01',
        ];

        $response = $this->put(route('expenses.update', 999999), $updateData);

        $response->assertStatus(404);
    }

    /**
     * Test destroy deletes expense.
     */
    public function test_destroy_deletes_expense(): void
    {
        $expense = Expense::factory()->create();
        $expenseId = $expense->id;

        $response = $this->delete(route('expenses.destroy', $expense));

        $response->assertRedirect(route('expenses.index'));
        $response->assertSessionHas('success');
        $this->assertSoftDeleted('expenses', ['id' => $expenseId]);
    }

    /**
     * Test destroy returns 404 for non-existent expense.
     */
    public function test_destroy_returns_404_for_non_existent_expense(): void
    {
        $response = $this->delete(route('expenses.destroy', 999999));

        $response->assertStatus(404);
    }

    /**
     * Test store creates expense with all valid categories.
     */
    public function test_store_accepts_all_valid_categories(): void
    {
        foreach (Expense::CATEGORIES as $category) {
            $expenseData = [
                'description' => "Test {$category}",
                'amount' => 50.00,
                'category' => $category,
                'date' => '2025-12-01',
            ];

            $response = $this->post(route('expenses.store'), $expenseData);

            $response->assertRedirect(route('expenses.index'));
            $this->assertDatabaseHas('expenses', [
                'description' => "Test {$category}",
                'category' => $category,
            ]);
        }

        $this->assertDatabaseCount('expenses', count(Expense::CATEGORIES));
    }

    /**
     * Test store with minimum valid amount.
     */
    public function test_store_accepts_minimum_valid_amount(): void
    {
        $expenseData = [
            'description' => 'Minimum Amount Test',
            'amount' => 0.01,
            'category' => 'Groceries',
            'date' => '2025-12-01',
        ];

        $response = $this->post(route('expenses.store'), $expenseData);

        $response->assertRedirect(route('expenses.index'));
        $this->assertDatabaseHas('expenses', [
            'description' => 'Minimum Amount Test',
        ]);
    }

    /**
     * Test store with maximum description length.
     */
    public function test_store_accepts_max_description_length(): void
    {
        $maxDescription = str_repeat('a', 255);
        $expenseData = [
            'description' => $maxDescription,
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => '2025-12-01',
        ];

        $response = $this->post(route('expenses.store'), $expenseData);

        $response->assertRedirect(route('expenses.index'));
        $this->assertDatabaseHas('expenses', [
            'description' => $maxDescription,
        ]);
    }

    /**
     * Test index displays empty state when no expenses.
     */
    public function test_index_displays_empty_state(): void
    {
        $response = $this->get(route('expenses.index'));

        $response->assertStatus(200);
        $response->assertViewHas('expenses', function ($expenses) {
            return $expenses->isEmpty();
        });
    }

    /**
     * Test daily displays empty state when no expenses.
     */
    public function test_daily_displays_empty_state(): void
    {
        $response = $this->get(route('expenses.daily'));

        $response->assertStatus(200);
        $response->assertViewHas('expenses', function ($expenses) {
            return $expenses->isEmpty();
        });
    }

    /**
     * Test monthly displays empty state when no expenses.
     */
    public function test_monthly_displays_empty_state(): void
    {
        $response = $this->get(route('expenses.monthly'));

        $response->assertStatus(200);
        $response->assertViewHas('expenses', function ($expenses) {
            return $expenses->isEmpty();
        });
    }

    /**
     * Test index with invalid category filter shows all expenses.
     */
    public function test_index_with_invalid_category_filter(): void
    {
        Expense::factory()->count(3)->create();

        $response = $this->get(route('expenses.index', ['category' => 'NonExistentCategory']));

        $response->assertStatus(200);
        // Should not filter if category is invalid
        $response->assertViewHas('expenses');
    }

    /**
     * Test expenses are ordered by date descending by default.
     */
    public function test_index_orders_expenses_by_date_descending(): void
    {
        Expense::factory()->create(['date' => '2025-11-01', 'description' => 'Oldest']);
        Expense::factory()->create(['date' => '2025-12-15', 'description' => 'Middle']);
        Expense::factory()->create(['date' => '2025-12-20', 'description' => 'Newest']);

        $response = $this->get(route('expenses.index'));

        $response->assertStatus(200);
        $response->assertViewHas('expenses', function ($expenses) {
            $dates = $expenses->pluck('date')->toArray();
            $sortedDates = $dates;
            rsort($sortedDates);

            return $dates === $sortedDates;
        });
    }
}
