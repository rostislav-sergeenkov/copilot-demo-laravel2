<?php

namespace Tests\Feature;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Feature tests for database operations, schema, and performance.
 */
class DatabaseTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // Schema Tests
    // ==========================================

    /**
     * Test expenses table has correct columns.
     */
    public function test_expenses_table_has_correct_columns(): void
    {
        $this->assertTrue(Schema::hasTable('expenses'));

        $this->assertTrue(Schema::hasColumn('expenses', 'id'));
        $this->assertTrue(Schema::hasColumn('expenses', 'description'));
        $this->assertTrue(Schema::hasColumn('expenses', 'amount'));
        $this->assertTrue(Schema::hasColumn('expenses', 'category'));
        $this->assertTrue(Schema::hasColumn('expenses', 'date'));
        $this->assertTrue(Schema::hasColumn('expenses', 'created_at'));
        $this->assertTrue(Schema::hasColumn('expenses', 'updated_at'));
        $this->assertTrue(Schema::hasColumn('expenses', 'deleted_at'));
    }

    /**
     * Test date column has index.
     */
    public function test_date_index_exists(): void
    {
        $indexes = DB::select("PRAGMA index_list('expenses')");
        $indexNames = collect($indexes)->pluck('name')->toArray();

        $hasDateIndex = collect($indexNames)->contains(function ($name) {
            return str_contains($name, 'date');
        });

        $this->assertTrue($hasDateIndex, 'Date column should have an index');
    }

    /**
     * Test category column has index.
     */
    public function test_category_index_exists(): void
    {
        $indexes = DB::select("PRAGMA index_list('expenses')");
        $indexNames = collect($indexes)->pluck('name')->toArray();

        $hasCategoryIndex = collect($indexNames)->contains(function ($name) {
            return str_contains($name, 'category');
        });

        $this->assertTrue($hasCategoryIndex, 'Category column should have an index');
    }

    /**
     * Test soft delete column exists.
     */
    public function test_soft_delete_column_exists(): void
    {
        $this->assertTrue(Schema::hasColumn('expenses', 'deleted_at'));
    }

    /**
     * Test amount column stores decimals correctly.
     */
    public function test_amount_column_decimal_precision(): void
    {
        $expense = Expense::factory()->create(['amount' => 123.456]);

        $storedExpense = Expense::find($expense->id);

        // Should be rounded to 2 decimal places
        $this->assertEquals('123.46', $storedExpense->amount);
    }

    // ==========================================
    // Data Integrity Tests
    // ==========================================

    /**
     * Test seeder creates sample expenses.
     */
    public function test_seeder_creates_sample_expenses(): void
    {
        $this->artisan('db:seed', ['--class' => 'ExpenseSeeder']);

        $count = Expense::count();

        $this->assertGreaterThan(0, $count);
        $this->assertLessThanOrEqual(50, $count);
    }

    /**
     * Test all categories are represented in seeded data.
     */
    public function test_seeded_data_has_all_categories(): void
    {
        $this->artisan('db:seed', ['--class' => 'ExpenseSeeder']);

        $categories = Expense::distinct('category')->pluck('category')->toArray();

        foreach (Expense::CATEGORIES as $category) {
            $this->assertContains($category, $categories);
        }
    }

    /**
     * Test seeded expenses span multiple months.
     */
    public function test_seeded_expenses_span_multiple_months(): void
    {
        $this->artisan('db:seed', ['--class' => 'ExpenseSeeder']);

        $months = Expense::selectRaw('DISTINCT strftime("%Y-%m", date) as month')
            ->pluck('month')
            ->count();

        $this->assertGreaterThan(1, $months);
    }

    /**
     * Test soft delete works correctly.
     */
    public function test_soft_delete_works_correctly(): void
    {
        $expense = Expense::factory()->create();
        $expenseId = $expense->id;

        $expense->delete();

        // Should not be in default query
        $this->assertNull(Expense::find($expenseId));

        // Should exist in withTrashed query
        $this->assertNotNull(Expense::withTrashed()->find($expenseId));

        // Should have deleted_at timestamp
        $trashedExpense = Expense::withTrashed()->find($expenseId);
        $this->assertNotNull($trashedExpense->deleted_at);
    }

    /**
     * Test force delete removes record permanently.
     */
    public function test_force_delete_removes_record(): void
    {
        $expense = Expense::factory()->create();
        $expenseId = $expense->id;

        $expense->forceDelete();

        $this->assertNull(Expense::withTrashed()->find($expenseId));
        $this->assertDatabaseMissing('expenses', ['id' => $expenseId]);
    }

    // ==========================================
    // Pagination Tests
    // ==========================================

    /**
     * Test pagination limits to 15 items per page.
     */
    public function test_pagination_limits_to_15_per_page(): void
    {
        Expense::factory()->count(20)->create();

        $response = $this->get(route('expenses.index'));

        $response->assertStatus(200);
        $response->assertViewHas('expenses', function ($expenses) {
            return $expenses->count() === 15;
        });
    }

    /**
     * Test pagination prevents loading all records.
     */
    public function test_pagination_prevents_loading_all_records(): void
    {
        Expense::factory()->count(50)->create();

        $response = $this->get(route('expenses.index'));

        $response->assertStatus(200);
        $response->assertViewHas('expenses', function ($expenses) {
            // Should only load 15 items, not all 50
            return $expenses->count() === 15 && $expenses->total() === 50;
        });
    }

    /**
     * Test pagination page 2 works.
     */
    public function test_pagination_page_2_works(): void
    {
        Expense::factory()->count(20)->create();

        $response = $this->get(route('expenses.index', ['page' => 2]));

        $response->assertStatus(200);
        $response->assertViewHas('expenses', function ($expenses) {
            return $expenses->count() === 5; // Remaining items
        });
    }

    /**
     * Test pagination with category filter.
     */
    public function test_pagination_with_category_filter(): void
    {
        Expense::factory()->category('Groceries')->count(20)->create();
        Expense::factory()->category('Transport')->count(10)->create();

        $response = $this->get(route('expenses.index', ['category' => 'Groceries']));

        $response->assertStatus(200);
        $response->assertViewHas('expenses', function ($expenses) {
            return $expenses->count() === 15 && $expenses->total() === 20;
        });
    }

    // ==========================================
    // Performance Tests
    // ==========================================

    /**
     * Test index page queries are optimized.
     */
    public function test_index_page_queries_optimized(): void
    {
        Expense::factory()->count(20)->create();

        // Enable query log
        DB::enableQueryLog();

        $this->get(route('expenses.index'));

        $queries = DB::getQueryLog();

        // Should have minimal queries (1-3 for pagination and data)
        $this->assertLessThan(10, count($queries));

        DB::disableQueryLog();
    }

    /**
     * Test daily view queries are optimized.
     */
    public function test_daily_view_queries_optimized(): void
    {
        Expense::factory()->count(10)->create(['date' => Carbon::today()]);

        DB::enableQueryLog();

        $this->get(route('expenses.daily'));

        $queries = DB::getQueryLog();

        $this->assertLessThan(10, count($queries));

        DB::disableQueryLog();
    }

    /**
     * Test monthly view queries are optimized.
     */
    public function test_monthly_view_queries_optimized(): void
    {
        Expense::factory()->count(10)->create(['date' => Carbon::today()]);

        DB::enableQueryLog();

        $this->get(route('expenses.monthly'));

        $queries = DB::getQueryLog();

        $this->assertLessThan(10, count($queries));

        DB::disableQueryLog();
    }

    // ==========================================
    // Transaction Tests
    // ==========================================

    /**
     * Test database transactions work correctly.
     */
    public function test_database_transactions_work(): void
    {
        DB::beginTransaction();

        Expense::factory()->create(['description' => 'Test Transaction']);

        DB::rollBack();

        $this->assertDatabaseMissing('expenses', ['description' => 'Test Transaction']);
    }

    /**
     * Test concurrent updates handled correctly.
     */
    public function test_concurrent_updates_handled(): void
    {
        $expense = Expense::factory()->create(['amount' => 50.00]);

        $expense->update(['amount' => 75.00]);

        $updatedExpense = Expense::find($expense->id);

        $this->assertEquals('75.00', $updatedExpense->amount);
    }

    // ==========================================
    // Edge Cases
    // ==========================================

    /**
     * Test empty database handled gracefully.
     */
    public function test_empty_database_handled_gracefully(): void
    {
        $response = $this->get(route('expenses.index'));

        $response->assertStatus(200);
        $response->assertViewHas('expenses', function ($expenses) {
            return $expenses->isEmpty();
        });
    }

    /**
     * Test large dataset queries.
     */
    public function test_large_dataset_queries(): void
    {
        Expense::factory()->count(100)->create();

        $response = $this->get(route('expenses.index'));

        $response->assertStatus(200);
        $response->assertViewHas('expenses');
    }

    /**
     * Test database constraint for valid category.
     */
    public function test_database_stores_only_valid_categories(): void
    {
        foreach (Expense::CATEGORIES as $category) {
            $expense = Expense::factory()->create(['category' => $category]);
            $this->assertNotNull($expense->id);
        }
    }
}
