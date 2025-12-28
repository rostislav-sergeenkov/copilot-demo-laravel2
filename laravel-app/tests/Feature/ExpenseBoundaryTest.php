<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration tests for Expense boundary conditions and edge cases
 * Tests limits, constraints, and special character handling
 */
class ExpenseBoundaryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating expense with minimum amount boundary.
     */
    public function test_minimum_amount_boundary(): void
    {
        $expense = Expense::factory()->create(['amount' => 0.01]);

        $this->assertEquals('0.01', $expense->amount);
        $this->assertDatabaseHas('expenses', ['amount' => 0.01]);
    }

    /**
     * Test creating expense with maximum amount boundary.
     */
    public function test_maximum_amount_boundary(): void
    {
        $expense = Expense::factory()->create(['amount' => 999999.99]);

        $this->assertEquals('999999.99', $expense->amount);
        $this->assertDatabaseHas('expenses', ['amount' => 999999.99]);
    }

    /**
     * Test description at maximum length boundary (255 characters).
     */
    public function test_description_at_maximum_length(): void
    {
        $maxDescription = str_repeat('a', 255);
        $expense = Expense::factory()->create(['description' => $maxDescription]);

        $this->assertEquals(255, strlen($expense->description));
        $this->assertEquals($maxDescription, $expense->description);
    }

    /**
     * Test handling of extremely long descriptions (at maximum length).
     */
    public function test_handles_maximum_length_description(): void
    {
        $description = str_repeat('a', 255);
        $expense = Expense::factory()->create(['description' => $description]);

        $this->assertEquals(255, strlen($expense->description));
        $this->assertEquals($description, $expense->description);
    }

    /**
     * Test date accepts today's date.
     */
    public function test_date_accepts_today(): void
    {
        $expense = Expense::factory()->create(['date' => Carbon::today()]);

        $this->assertEquals(Carbon::today()->format('Y-m-d'), $expense->date->format('Y-m-d'));
    }

    /**
     * Test date accepts yesterday's date.
     */
    public function test_date_accepts_yesterday(): void
    {
        $expense = Expense::factory()->create(['date' => Carbon::yesterday()]);

        $this->assertEquals(Carbon::yesterday()->format('Y-m-d'), $expense->date->format('Y-m-d'));
    }

    /**
     * Test date accepts date 5 years ago.
     */
    public function test_date_accepts_five_years_ago(): void
    {
        $fiveYearsAgo = Carbon::today()->subYears(5);
        $expense = Expense::factory()->create(['date' => $fiveYearsAgo]);

        $this->assertEquals($fiveYearsAgo->format('Y-m-d'), $expense->date->format('Y-m-d'));
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
     * Test expense amount precision after database round-trip.
     */
    public function test_expense_amount_precision(): void
    {
        $expense = Expense::factory()->create(['amount' => 123.456789]);

        // Should be rounded/truncated to 2 decimal places
        $freshExpense = $expense->fresh();
        $this->assertEquals('123.46', $freshExpense->amount);
    }

    /**
     * Test amount stores exactly 2 decimal places.
     */
    public function test_amount_stores_two_decimal_places(): void
    {
        $expense1 = Expense::factory()->create(['amount' => 1.00]);
        $this->assertEquals('1.00', $expense1->fresh()->amount);

        $expense2 = Expense::factory()->create(['amount' => 1.50]);
        $this->assertEquals('1.50', $expense2->fresh()->amount);

        $expense3 = Expense::factory()->create(['amount' => 1.23]);
        $this->assertEquals('1.23', $expense3->fresh()->amount);

        $expense4 = Expense::factory()->create(['amount' => 999.99]);
        $this->assertEquals('999.99', $expense4->fresh()->amount);
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
     * Test description accepts special characters.
     */
    public function test_description_accepts_special_characters(): void
    {
        $specialChars = "Test with symbols: @#$%^&*()_+-=[]{}|;':\",./<>?";
        $expense = Expense::factory()->create(['description' => $specialChars]);

        $this->assertEquals($specialChars, $expense->description);
    }

    /**
     * Test handling special characters including HTML/script tags.
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

        $freshExpense = $expense->fresh();
        $this->assertEquals('678.90', $freshExpense->amount);
    }
}
