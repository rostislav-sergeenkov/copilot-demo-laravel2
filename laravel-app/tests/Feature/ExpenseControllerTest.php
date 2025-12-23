<?php

namespace Tests\Feature;

use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseControllerTest extends TestCase
{
    use RefreshDatabase;

    // ==================== F1: CREATE EXPENSE ====================

    public function test_create_form_displays_correctly(): void
    {
        $response = $this->get('/expenses/create');

        $response->assertStatus(200);
        $response->assertSee('Add Expense');
        $response->assertSee('Description');
        $response->assertSee('Amount');
        $response->assertSee('Category');
        $response->assertSee('Date');
    }

    public function test_create_form_shows_all_categories(): void
    {
        $response = $this->get('/expenses/create');

        foreach (Expense::CATEGORIES as $category) {
            $response->assertSee($category);
        }
    }

    public function test_store_creates_expense_in_database(): void
    {
        $data = [
            'description' => 'Test Grocery Shopping',
            'amount' => 125.50,
            'category' => 'Groceries',
            'date' => '2025-12-15',
        ];

        $response = $this->post('/expenses', $data);

        $response->assertRedirect('/expenses');
        $this->assertDatabaseHas('expenses', [
            'description' => 'Test Grocery Shopping',
            'amount' => 125.50,
            'category' => 'Groceries',
            'date' => '2025-12-15 00:00:00',
        ]);
    }

    public function test_store_redirects_with_success_message(): void
    {
        $data = [
            'description' => 'Test Expense',
            'amount' => 50.00,
            'category' => 'Transport',
            'date' => '2025-12-15',
        ];

        $response = $this->post('/expenses', $data);

        $response->assertRedirect('/expenses');
        $response->assertSessionHas('success');
    }

    // ==================== F1: READ EXPENSES (INDEX) ====================

    public function test_index_page_loads_successfully(): void
    {
        $response = $this->get('/expenses');

        $response->assertStatus(200);
        $response->assertSee('Expense Tracker');
    }

    public function test_index_displays_expenses_table(): void
    {
        Expense::factory()->create([
            'description' => 'Test Expense Display',
            'amount' => 100.00,
            'category' => 'Groceries',
            'date' => '2025-12-15',
        ]);

        $response = $this->get('/expenses');

        $response->assertSee('Test Expense Display');
        $response->assertSee('$100.00');
        $response->assertSee('Groceries');
    }

    public function test_index_shows_expenses_sorted_by_date_desc(): void
    {
        $older = Expense::factory()->create([
            'description' => 'Older Expense',
            'date' => '2025-12-10',
        ]);
        $newer = Expense::factory()->create([
            'description' => 'Newer Expense',
            'date' => '2025-12-15',
        ]);

        $response = $this->get('/expenses');

        $content = $response->getContent();
        $newerPos = strpos($content, 'Newer Expense');
        $olderPos = strpos($content, 'Older Expense');

        $this->assertLessThan($olderPos, $newerPos, 'Newer expense should appear before older expense');
    }

    public function test_index_pagination_works(): void
    {
        Expense::factory()->count(20)->create();

        $response = $this->get('/expenses');

        $response->assertStatus(200);
        // Should have pagination links
        $this->assertStringContainsString('pagination', $response->getContent());
    }

    public function test_index_shows_empty_state_when_no_expenses(): void
    {
        $response = $this->get('/expenses');

        $response->assertSee('No expenses yet');
    }

    public function test_index_amounts_display_as_currency(): void
    {
        Expense::factory()->create([
            'amount' => 1234.56,
        ]);

        $response = $this->get('/expenses');

        $response->assertSee('$1,234.56');
    }

    // ==================== F1: UPDATE EXPENSE ====================

    public function test_edit_form_displays_with_existing_data(): void
    {
        $expense = Expense::factory()->create([
            'description' => 'Original Description',
            'amount' => 99.99,
            'category' => 'Transport',
            'date' => '2025-12-10',
        ]);

        $response = $this->get("/expenses/{$expense->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Edit Expense');
        $response->assertSee('Original Description');
        $response->assertSee('99.99');
        $response->assertSee('Transport');
    }

    public function test_update_modifies_expense_in_database(): void
    {
        $expense = Expense::factory()->create([
            'description' => 'Original',
            'amount' => 50.00,
        ]);

        $updateData = [
            'description' => 'Updated Description',
            'amount' => 75.00,
            'category' => $expense->category,
            'date' => $expense->date->format('Y-m-d'),
        ];

        $response = $this->put("/expenses/{$expense->id}", $updateData);

        $response->assertRedirect('/expenses');
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'description' => 'Updated Description',
            'amount' => 75.00,
        ]);
    }

    public function test_update_redirects_with_success_message(): void
    {
        $expense = Expense::factory()->create();

        $updateData = [
            'description' => 'Updated',
            'amount' => 100.00,
            'category' => $expense->category,
            'date' => $expense->date->format('Y-m-d'),
        ];

        $response = $this->put("/expenses/{$expense->id}", $updateData);

        $response->assertRedirect('/expenses');
        $response->assertSessionHas('success');
    }

    // ==================== F1: DELETE EXPENSE ====================

    public function test_destroy_soft_deletes_expense(): void
    {
        $expense = Expense::factory()->create();

        $response = $this->delete("/expenses/{$expense->id}");

        $response->assertRedirect('/expenses');
        $this->assertSoftDeleted('expenses', ['id' => $expense->id]);
    }

    public function test_destroy_redirects_with_success_message(): void
    {
        $expense = Expense::factory()->create();

        $response = $this->delete("/expenses/{$expense->id}");

        $response->assertRedirect('/expenses');
        $response->assertSessionHas('success');
    }

    public function test_deleted_expense_not_visible_in_index(): void
    {
        $expense = Expense::factory()->create([
            'description' => 'To Be Deleted',
        ]);

        $this->delete("/expenses/{$expense->id}");

        $response = $this->get('/expenses');
        $response->assertDontSee('To Be Deleted');
    }

    // ==================== F2: DAILY EXPENSES VIEW ====================

    public function test_daily_view_loads_successfully(): void
    {
        $response = $this->get('/expenses/daily');

        $response->assertStatus(200);
        $response->assertSee('Daily Expenses');
    }

    public function test_daily_view_shows_current_date_by_default(): void
    {
        $response = $this->get('/expenses/daily');

        $response->assertStatus(200);
        $today = now()->format('F j, Y');
        $response->assertSee($today);
    }

    public function test_daily_view_displays_expenses_for_specific_date(): void
    {
        $date = '2025-12-15';
        Expense::factory()->create([
            'description' => 'Daily Test Expense',
            'date' => $date,
            'amount' => 50.00,
        ]);

        $response = $this->get("/expenses/daily?date={$date}");

        $response->assertSee('Daily Test Expense');
        $response->assertSee('$50.00');
    }

    public function test_daily_view_calculates_daily_total(): void
    {
        $date = '2025-12-15';
        Expense::factory()->create(['date' => $date, 'amount' => 25.00]);
        Expense::factory()->create(['date' => $date, 'amount' => 75.00]);

        $response = $this->get("/expenses/daily?date={$date}");

        $response->assertSee('$100.00'); // Total
    }

    public function test_daily_view_shows_empty_state_when_no_expenses(): void
    {
        $response = $this->get('/expenses/daily?date=2025-12-01');

        $response->assertSee('No expenses for this day');
    }

    public function test_daily_view_category_filter_works(): void
    {
        $date = '2025-12-15';
        Expense::factory()->create([
            'description' => 'Grocery Item',
            'category' => 'Groceries',
            'date' => $date,
        ]);
        Expense::factory()->create([
            'description' => 'Transport Item',
            'category' => 'Transport',
            'date' => $date,
        ]);

        $response = $this->get("/expenses/daily?date={$date}&category=Groceries");

        $response->assertSee('Grocery Item');
        $response->assertDontSee('Transport Item');
    }

    // ==================== F3: MONTHLY EXPENSES VIEW ====================

    public function test_monthly_view_loads_successfully(): void
    {
        $response = $this->get('/expenses/monthly');

        $response->assertStatus(200);
        $response->assertSee('Monthly Expenses');
    }

    public function test_monthly_view_shows_current_month_by_default(): void
    {
        $response = $this->get('/expenses/monthly');

        $response->assertStatus(200);
        $currentMonth = now()->format('F Y');
        $response->assertSee($currentMonth);
    }

    public function test_monthly_view_calculates_monthly_total(): void
    {
        $month = '2025-12';
        Expense::factory()->create(['date' => '2025-12-01', 'amount' => 100.00]);
        Expense::factory()->create(['date' => '2025-12-15', 'amount' => 200.00]);
        Expense::factory()->create(['date' => '2025-12-30', 'amount' => 50.00]);

        $response = $this->get("/expenses/monthly?month={$month}");

        $response->assertSee('$350.00'); // Total
    }

    public function test_monthly_view_shows_category_breakdown(): void
    {
        $month = '2025-12';
        Expense::factory()->create([
            'date' => '2025-12-15',
            'category' => 'Groceries',
            'amount' => 100.00,
        ]);

        $response = $this->get("/expenses/monthly?month={$month}");

        $response->assertSee('Groceries');
        $response->assertSee('$100.00');
    }

    public function test_monthly_view_calculates_category_percentages(): void
    {
        $month = '2025-12';
        Expense::factory()->create([
            'date' => '2025-12-15',
            'category' => 'Groceries',
            'amount' => 50.00,
        ]);
        Expense::factory()->create([
            'date' => '2025-12-15',
            'category' => 'Transport',
            'amount' => 50.00,
        ]);

        $response = $this->get("/expenses/monthly?month={$month}");

        $response->assertSee('50%'); // Each category should be 50%
    }

    public function test_monthly_view_shows_zero_percent_for_unused_categories(): void
    {
        $month = '2025-12';
        Expense::factory()->create([
            'date' => '2025-12-15',
            'category' => 'Groceries',
            'amount' => 100.00,
        ]);

        $response = $this->get("/expenses/monthly?month={$month}");

        // All other categories should show 0%
        $response->assertSee('0%');
    }

    public function test_monthly_view_shows_empty_state_when_no_expenses(): void
    {
        $response = $this->get('/expenses/monthly?month=2025-01');

        $response->assertSee('No expenses this month');
    }

    // ==================== F4: CATEGORY FILTERING ====================

    public function test_index_category_filter_shows_only_matching_expenses(): void
    {
        Expense::factory()->create([
            'description' => 'Grocery Item',
            'category' => 'Groceries',
        ]);
        Expense::factory()->create([
            'description' => 'Transport Item',
            'category' => 'Transport',
        ]);

        $response = $this->get('/expenses?category=Groceries');

        $response->assertSee('Grocery Item');
        $response->assertDontSee('Transport Item');
    }

    public function test_category_filter_updates_totals(): void
    {
        Expense::factory()->create([
            'category' => 'Groceries',
            'amount' => 100.00,
        ]);
        Expense::factory()->create([
            'category' => 'Transport',
            'amount' => 50.00,
        ]);

        $response = $this->get('/expenses?category=Groceries');

        // Should only show Groceries total, not Transport
        $response->assertSee('$100.00');
    }

    public function test_all_categories_option_shows_all_expenses(): void
    {
        Expense::factory()->create(['description' => 'Expense 1']);
        Expense::factory()->create(['description' => 'Expense 2']);

        $response = $this->get('/expenses');

        $response->assertSee('Expense 1');
        $response->assertSee('Expense 2');
    }

    // ==================== ERROR HANDLING ====================

    public function test_404_error_for_nonexistent_expense(): void
    {
        $response = $this->get('/expenses/99999/edit');

        $response->assertStatus(404);
    }

    public function test_update_404_for_nonexistent_expense(): void
    {
        $response = $this->put('/expenses/99999', [
            'description' => 'Test',
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => '2025-12-15',
        ]);

        $response->assertStatus(404);
    }

    public function test_delete_404_for_nonexistent_expense(): void
    {
        $response = $this->delete('/expenses/99999');

        $response->assertStatus(404);
    }

    // ==================== VALIDATION ====================

    public function test_store_validates_required_fields(): void
    {
        $response = $this->post('/expenses', []);

        $response->assertSessionHasErrors(['description', 'amount', 'category', 'date']);
    }

    public function test_store_rejects_future_date(): void
    {
        $futureDate = now()->addDays(1)->format('Y-m-d');

        $response = $this->post('/expenses', [
            'description' => 'Test',
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => $futureDate,
        ]);

        $response->assertSessionHasErrors('date');
    }

    public function test_store_rejects_invalid_amount(): void
    {
        $response = $this->post('/expenses', [
            'description' => 'Test',
            'amount' => 0.00, // Below minimum
            'category' => 'Groceries',
            'date' => '2025-12-15',
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_store_rejects_invalid_category(): void
    {
        $response = $this->post('/expenses', [
            'description' => 'Test',
            'amount' => 50.00,
            'category' => 'Invalid Category',
            'date' => '2025-12-15',
        ]);

        $response->assertSessionHasErrors('category');
    }

    public function test_update_validates_required_fields(): void
    {
        $expense = Expense::factory()->create();

        $response = $this->put("/expenses/{$expense->id}", []);

        $response->assertSessionHasErrors(['description', 'amount', 'category', 'date']);
    }

    // ==================== UI/UX ====================

    public function test_create_form_has_cancel_button(): void
    {
        $response = $this->get('/expenses/create');

        $response->assertSee('Cancel');
    }

    public function test_edit_form_has_cancel_button(): void
    {
        $expense = Expense::factory()->create();

        $response = $this->get("/expenses/{$expense->id}/edit");

        $response->assertSee('Cancel');
    }

    public function test_navigation_links_present(): void
    {
        $response = $this->get('/expenses');

        $response->assertSee('All');
        $response->assertSee('Daily');
        $response->assertSee('Monthly');
    }

    // ==================== DATA INTEGRITY ====================

    public function test_expense_timestamps_are_set(): void
    {
        $data = [
            'description' => 'Test',
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => '2025-12-15',
        ];

        $this->post('/expenses', $data);

        $expense = Expense::latest()->first();
        $this->assertNotNull($expense->created_at);
        $this->assertNotNull($expense->updated_at);
    }

    public function test_amount_stored_with_two_decimal_places(): void
    {
        $data = [
            'description' => 'Test',
            'amount' => 50.5, // One decimal
            'category' => 'Groceries',
            'date' => '2025-12-15',
        ];

        $this->post('/expenses', $data);

        $expense = Expense::latest()->first();
        $this->assertEquals('50.50', $expense->amount);
    }

    public function test_special_characters_in_description_handled_correctly(): void
    {
        $description = 'Test & Special <Characters> "Quotes"';

        $data = [
            'description' => $description,
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => '2025-12-15',
        ];

        $this->post('/expenses', $data);

        $response = $this->get('/expenses');
        $response->assertSee(htmlspecialchars($description), false);
    }

    public function test_unicode_in_description_handled_correctly(): void
    {
        $description = 'Café ☕ 日本語 🍕';

        $data = [
            'description' => $description,
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => '2025-12-15',
        ];

        $this->post('/expenses', $data);

        $this->assertDatabaseHas('expenses', [
            'description' => $description,
        ]);
    }

    // ==================== EDGE CASES ====================

    public function test_very_large_amount_displays_correctly(): void
    {
        Expense::factory()->create([
            'amount' => 999999.99,
        ]);

        $response = $this->get('/expenses');

        $response->assertSee('$999,999.99');
    }

    public function test_very_small_amount_displays_correctly(): void
    {
        Expense::factory()->create([
            'amount' => 0.01,
        ]);

        $response = $this->get('/expenses');

        $response->assertSee('$0.01');
    }

    public function test_maximum_length_description_accepted(): void
    {
        $description = str_repeat('a', 255);

        $data = [
            'description' => $description,
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => '2025-12-15',
        ];

        $response = $this->post('/expenses', $data);

        $response->assertRedirect('/expenses');
        $this->assertDatabaseHas('expenses', [
            'description' => $description,
        ]);
    }

    public function test_description_over_255_characters_rejected(): void
    {
        $description = str_repeat('a', 256);

        $data = [
            'description' => $description,
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => '2025-12-15',
        ];

        $response = $this->post('/expenses', $data);

        $response->assertSessionHasErrors('description');
    }

    public function test_date_exactly_five_years_ago_accepted(): void
    {
        $fiveYearsAgo = now()->subYears(5)->format('Y-m-d');

        $data = [
            'description' => 'Old Expense',
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => $fiveYearsAgo,
        ];

        $response = $this->post('/expenses', $data);

        $response->assertRedirect('/expenses');
    }

    public function test_todays_date_accepted(): void
    {
        $today = now()->format('Y-m-d');

        $data = [
            'description' => 'Today Expense',
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => $today,
        ];

        $response = $this->post('/expenses', $data);

        $response->assertRedirect('/expenses');
    }
}
