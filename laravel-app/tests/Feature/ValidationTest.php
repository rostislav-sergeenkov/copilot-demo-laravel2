<?php

namespace Tests\Feature;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for server-side validation enforcement.
 * Tests that HTTP requests properly validate input data.
 */
class ValidationTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // Description Field Validation
    // ==========================================

    /**
     * Test description required validation via HTTP.
     */
    public function test_description_required_via_http(): void
    {
        $response = $this->post(route('expenses.store'), [
            'description' => '',
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => Carbon::today()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('description');
        $response->assertStatus(302);
    }

    /**
     * Test description max 255 characters validation via HTTP.
     */
    public function test_description_max_255_via_http(): void
    {
        $response = $this->post(route('expenses.store'), [
            'description' => str_repeat('a', 256),
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => Carbon::today()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('description');
        $response->assertStatus(302);
    }

    /**
     * Test description accepts unicode characters via HTTP.
     */
    public function test_description_accepts_unicode_via_http(): void
    {
        $response = $this->post(route('expenses.store'), [
            'description' => '日本語のテキスト émojis 🎉',
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => Carbon::today()->format('Y-m-d'),
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('expenses.index'));
        $this->assertDatabaseHas('expenses', [
            'description' => '日本語のテキスト émojis 🎉',
        ]);
    }

    /**
     * Test description accepts special characters via HTTP.
     */
    public function test_description_accepts_special_characters_via_http(): void
    {
        $specialChars = "Test @#$%^&*()_+-=[]{}|;':\",./<>?";
        
        $response = $this->post(route('expenses.store'), [
            'description' => $specialChars,
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => Carbon::today()->format('Y-m-d'),
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('expenses.index'));
        $this->assertDatabaseHas('expenses', [
            'description' => $specialChars,
        ]);
    }

    // ==========================================
    // Amount Field Validation
    // ==========================================

    /**
     * Test amount required validation via HTTP.
     */
    public function test_amount_required_via_http(): void
    {
        $response = $this->post(route('expenses.store'), [
            'description' => 'Test Expense',
            'amount' => '',
            'category' => 'Groceries',
            'date' => Carbon::today()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('amount');
        $response->assertStatus(302);
    }

    /**
     * Test amount minimum validation via HTTP.
     */
    public function test_amount_minimum_via_http(): void
    {
        $response = $this->post(route('expenses.store'), [
            'description' => 'Test Expense',
            'amount' => 0.001,
            'category' => 'Groceries',
            'date' => Carbon::today()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('amount');
        $response->assertStatus(302);
    }

    /**
     * Test amount maximum validation via HTTP.
     */
    public function test_amount_maximum_via_http(): void
    {
        $response = $this->post(route('expenses.store'), [
            'description' => 'Test Expense',
            'amount' => 1000000.00,
            'category' => 'Groceries',
            'date' => Carbon::today()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('amount');
        $response->assertStatus(302);
    }

    /**
     * Test amount accepts valid decimals via HTTP.
     */
    public function test_amount_accepts_decimals_via_http(): void
    {
        $response = $this->post(route('expenses.store'), [
            'description' => 'Test Expense',
            'amount' => 123.45,
            'category' => 'Groceries',
            'date' => Carbon::today()->format('Y-m-d'),
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('expenses.index'));
        $this->assertDatabaseHas('expenses', [
            'amount' => '123.45',
        ]);
    }

    /**
     * Test amount rejects non-numeric input via HTTP.
     */
    public function test_amount_rejects_non_numeric_via_http(): void
    {
        $response = $this->post(route('expenses.store'), [
            'description' => 'Test Expense',
            'amount' => 'not-a-number',
            'category' => 'Groceries',
            'date' => Carbon::today()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('amount');
        $response->assertStatus(302);
    }

    // ==========================================
    // Category Field Validation
    // ==========================================

    /**
     * Test category required validation via HTTP.
     */
    public function test_category_required_via_http(): void
    {
        $response = $this->post(route('expenses.store'), [
            'description' => 'Test Expense',
            'amount' => 50.00,
            'category' => '',
            'date' => Carbon::today()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('category');
        $response->assertStatus(302);
    }

    /**
     * Test category validates against enum via HTTP.
     */
    public function test_category_validates_enum_via_http(): void
    {
        $response = $this->post(route('expenses.store'), [
            'description' => 'Test Expense',
            'amount' => 50.00,
            'category' => 'InvalidCategoryName',
            'date' => Carbon::today()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('category');
        $response->assertStatus(302);
    }

    /**
     * Test all valid categories accepted via HTTP.
     */
    public function test_all_valid_categories_accepted_via_http(): void
    {
        foreach (Expense::CATEGORIES as $category) {
            $response = $this->post(route('expenses.store'), [
                'description' => "Test {$category}",
                'amount' => 50.00,
                'category' => $category,
                'date' => Carbon::today()->format('Y-m-d'),
            ]);

            $response->assertSessionHasNoErrors();
            $response->assertRedirect(route('expenses.index'));
        }

        $this->assertDatabaseCount('expenses', count(Expense::CATEGORIES));
    }

    // ==========================================
    // Date Field Validation
    // ==========================================

    /**
     * Test date required validation via HTTP.
     */
    public function test_date_required_via_http(): void
    {
        $response = $this->post(route('expenses.store'), [
            'description' => 'Test Expense',
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => '',
        ]);

        $response->assertSessionHasErrors('date');
        $response->assertStatus(302);
    }

    /**
     * Test date cannot be future validation via HTTP.
     */
    public function test_date_cannot_be_future_via_http(): void
    {
        $response = $this->post(route('expenses.store'), [
            'description' => 'Test Expense',
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => Carbon::tomorrow()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('date');
        $response->assertStatus(302);
    }

    /**
     * Test date accepts today via HTTP.
     */
    public function test_date_accepts_today_via_http(): void
    {
        $response = $this->post(route('expenses.store'), [
            'description' => 'Test Expense',
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => Carbon::today()->format('Y-m-d'),
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('expenses.index'));
    }

    /**
     * Test date accepts past dates via HTTP.
     */
    public function test_date_accepts_past_dates_via_http(): void
    {
        $response = $this->post(route('expenses.store'), [
            'description' => 'Test Expense',
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => Carbon::yesterday()->format('Y-m-d'),
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('expenses.index'));
    }

    /**
     * Test date accepts valid format via HTTP.
     */
    public function test_date_accepts_valid_format_via_http(): void
    {
        $response = $this->post(route('expenses.store'), [
            'description' => 'Test Expense',
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => '2025-12-01',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('expenses.index'));
    }

    // ==========================================
    // Validation Error Handling
    // ==========================================

    /**
     * Test validation errors return 422 status for API.
     */
    public function test_validation_errors_return_422_status(): void
    {
        $response = $this->postJson(route('expenses.store'), [
            'description' => '',
            'amount' => 'invalid',
            'category' => '',
            'date' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['description', 'amount', 'category', 'date']);
    }

    /**
     * Test form repopulates with old input after validation error.
     */
    public function test_form_repopulates_with_old_input(): void
    {
        $response = $this->post(route('expenses.store'), [
            'description' => 'Valid Description',
            'amount' => -10.00, // Invalid
            'category' => 'Groceries',
            'date' => Carbon::today()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('amount');
        $response->assertSessionHasInput('description', 'Valid Description');
        $response->assertSessionHasInput('category', 'Groceries');
    }

    /**
     * Test multiple validation errors returned together.
     */
    public function test_multiple_validation_errors_returned(): void
    {
        $response = $this->post(route('expenses.store'), [
            'description' => '',
            'amount' => -50.00,
            'category' => 'InvalidCategory',
            'date' => Carbon::tomorrow()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors(['description', 'amount', 'category', 'date']);
        $response->assertStatus(302);
    }

    // ==========================================
    // Update Validation Tests
    // ==========================================

    /**
     * Test update validates all fields via HTTP.
     */
    public function test_update_validates_all_fields_via_http(): void
    {
        $expense = Expense::factory()->create();

        $response = $this->put(route('expenses.update', $expense), [
            'description' => '',
            'amount' => 'invalid',
            'category' => 'InvalidCategory',
            'date' => Carbon::tomorrow()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors(['description', 'amount', 'category', 'date']);
        $response->assertStatus(302);
    }

    /**
     * Test update accepts valid data via HTTP.
     */
    public function test_update_accepts_valid_data_via_http(): void
    {
        $expense = Expense::factory()->create();

        $response = $this->put(route('expenses.update', $expense), [
            'description' => 'Updated Description',
            'amount' => 99.99,
            'category' => 'Transport',
            'date' => Carbon::yesterday()->format('Y-m-d'),
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('expenses.index'));
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'description' => 'Updated Description',
            'amount' => '99.99',
            'category' => 'Transport',
        ]);
    }
}
