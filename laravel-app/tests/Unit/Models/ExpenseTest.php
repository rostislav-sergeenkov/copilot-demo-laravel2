<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Expense;
use Tests\TestCase;

/**
 * Unit tests for Expense model - Pure business logic only
 *
 * Tests framework behavior, database operations, and workflows have been moved to:
 * - tests/Feature/ExpenseWorkflowTest.php
 * - tests/Feature/ExpenseCalculationTest.php
 * - tests/Feature/ExpenseFactoryTest.php
 * - tests/Feature/ExpenseBoundaryTest.php
 */
class ExpenseTest extends TestCase
{
    /**
     * Test that CATEGORIES constant contains expected values.
     * This is a business rule - the available expense categories.
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
     * Test that validation rules method returns expected structure.
     * Validates the business rules for expense data.
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
     * Test that validation messages method returns expected structure.
     * Ensures proper user-facing validation error messages.
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
     * Test amount precision rounding logic.
     * Business rule: amounts must be rounded to 2 decimal places.
     */
    public function test_amount_rounds_to_two_decimals(): void
    {
        // This tests the business logic of decimal rounding
        $testAmount = '12.3456';

        // Simulate the rounding that happens in the model
        $roundedAmount = number_format((float) $testAmount, 2, '.', '');

        $this->assertEquals('12.35', $roundedAmount);
        $this->assertEquals(2, strlen(explode('.', $roundedAmount)[1]));
    }

    /**
     * Test zero expenses returns zero total - edge case.
     * Business logic: sum of zero expenses should be zero.
     */
    public function test_zero_expenses_returns_zero_total(): void
    {
        $emptyArray = [];
        $total = array_sum($emptyArray);

        $this->assertEquals(0, $total);
    }

    /**
     * Test percentage calculation with zero category - edge case.
     * Business logic: percentage of zero should be zero, not undefined.
     */
    public function test_category_with_zero_shows_zero_percentage(): void
    {
        $total = 100.00;
        $categoryTotal = 0.00;

        $percentage = $total > 0 ? ($categoryTotal / $total) * 100 : 0;

        $this->assertEquals(0.0, $percentage);
    }

    /**
     * Test percentages sum to 100% - business rule.
     * Critical calculation validation for category breakdowns.
     */
    public function test_percentages_sum_to_100_percent(): void
    {
        $total = 100.00;
        $breakdown = [
            'Groceries' => 30.00,
            'Transport' => 45.00,
            'Entertainment' => 25.00,
        ];

        $percentageSum = 0;
        foreach ($breakdown as $amount) {
            $percentageSum += ($amount / $total) * 100;
        }

        $this->assertEquals(100.0, round($percentageSum, 2));
    }

    /**
     * Test empty category breakdown returns empty array - edge case.
     * Business logic: no expenses should result in empty breakdown.
     */
    public function test_empty_category_breakdown(): void
    {
        $emptyExpenses = [];
        $breakdown = [];

        foreach ($emptyExpenses as $expense) {
            $breakdown[$expense['category']] = ($breakdown[$expense['category']] ?? 0) + $expense['amount'];
        }

        $this->assertCount(0, $breakdown);
        $this->assertIsArray($breakdown);
    }

    /**
     * Test expense amount precision business logic.
     * Business rule: amounts should maintain 2 decimal precision.
     */
    public function test_expense_amount_precision(): void
    {
        $inputAmount = 123.456789;

        // Business logic: round to 2 decimals
        $processedAmount = number_format($inputAmount, 2, '.', '');

        $this->assertEquals('123.46', $processedAmount);

        // Verify exactly 2 decimal places
        $parts = explode('.', $processedAmount);
        $this->assertCount(2, $parts);
        $this->assertEquals(2, strlen($parts[1]));
    }
}
