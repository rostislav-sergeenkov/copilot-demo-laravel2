<?php

namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for formatting helper functions.
 * 
 * Note: These tests validate formatting logic that would typically
 * be in helper functions. If your application uses Blade directives
 * or view composers for formatting, adapt these tests accordingly.
 */
class FormatHelperTest extends TestCase
{
    /**
     * Test currency formatting with 2 decimal places.
     */
    public function test_format_currency_with_two_decimals(): void
    {
        $this->assertEquals('$0.01', $this->formatCurrency(0.01));
        $this->assertEquals('$1.00', $this->formatCurrency(1.00));
        $this->assertEquals('$1.50', $this->formatCurrency(1.50));
        $this->assertEquals('$1,234.56', $this->formatCurrency(1234.56));
        $this->assertEquals('$999,999.99', $this->formatCurrency(999999.99));
    }

    /**
     * Test currency formatting handles zero.
     */
    public function test_format_currency_handles_zero(): void
    {
        $formatted = $this->formatCurrency(0);
        $this->assertEquals('$0.00', $formatted);
    }

    /**
     * Test currency formatting handles large numbers.
     */
    public function test_format_currency_handles_large_numbers(): void
    {
        $formatted = $this->formatCurrency(1234567.89);
        $this->assertEquals('$1,234,567.89', $formatted);
    }

    /**
     * Test currency formatting handles negative numbers.
     */
    public function test_format_currency_handles_negative(): void
    {
        $formatted = $this->formatCurrency(-50.00);
        $this->assertEquals('-$50.00', $formatted);
    }

    /**
     * Test date formatting to readable format.
     */
    public function test_format_date_to_readable(): void
    {
        $testCases = [
            '2025-12-01' => 'December 1, 2025',
            '2025-01-15' => 'January 15, 2025',
            '2024-06-30' => 'June 30, 2024',
        ];

        foreach ($testCases as $date => $expected) {
            $formatted = $this->formatDate($date);
            $this->assertEquals($expected, $formatted, "Date $date should format as $expected");
        }
    }

    /**
     * Test date formatting with different formats.
     */
    public function test_format_date_with_custom_format(): void
    {
        $date = '2025-12-17';
        
        $formatted = $this->formatDate($date, 'Y-m-d');
        $this->assertEquals('2025-12-17', $formatted);
        
        $formatted = $this->formatDate($date, 'd/m/Y');
        $this->assertEquals('17/12/2025', $formatted);
        
        $formatted = $this->formatDate($date, 'l, F j, Y');
        $this->assertEquals('Wednesday, December 17, 2025', $formatted);
    }

    /**
     * Test percentage formatting.
     */
    public function test_format_percentage(): void
    {
        $testCases = [
            0 => '0%',
            1 => '1%',
            25 => '25%',
            51 => '51%',
            100 => '100%',
        ];

        foreach ($testCases as $value => $expected) {
            $formatted = $this->formatPercentage($value);
            $this->assertEquals($expected, $formatted, "Percentage $value should format as $expected");
        }
    }

    /**
     * Test percentage formatting with decimals.
     */
    public function test_format_percentage_with_decimals(): void
    {
        $this->assertEquals('33.33%', $this->formatPercentage(33.33, 2));
        $this->assertEquals('66.67%', $this->formatPercentage(66.67, 2));
        $this->assertEquals('100.00%', $this->formatPercentage(100.00, 2));
    }

    /**
     * Test number formatting with thousand separators.
     */
    public function test_format_number_with_separators(): void
    {
        $testCases = [
            100 => '100',
            1000 => '1,000',
            1234567 => '1,234,567',
        ];

        foreach ($testCases as $number => $expected) {
            $formatted = number_format($number);
            $this->assertEquals($expected, $formatted);
        }
    }

    /**
     * Test truncating long text.
     */
    public function test_truncate_text(): void
    {
        $longText = 'This is a very long description that should be truncated';
        $truncated = $this->truncateText($longText, 20);
        
        $this->assertEquals('This is a very lo...', $truncated);
        $this->assertEquals(20, strlen($truncated));
    }

    /**
     * Test truncate does not truncate short text.
     */
    public function test_truncate_does_not_affect_short_text(): void
    {
        $shortText = 'Short text';
        $truncated = $this->truncateText($shortText, 20);
        
        $this->assertEquals('Short text', $truncated);
    }

    // ==========================================
    // Helper Methods (Simulate formatting logic)
    // ==========================================

    /**
     * Format amount as currency.
     * This simulates a helper function that would exist in your application.
     */
    private function formatCurrency(float $amount): string
    {
        if ($amount < 0) {
            return '-$' . number_format(abs($amount), 2);
        }
        return '$' . number_format($amount, 2);
    }

    /**
     * Format date to readable format.
     * This simulates a helper function that would exist in your application.
     */
    private function formatDate(string $date, string $format = 'F j, Y'): string
    {
        return date($format, strtotime($date));
    }

    /**
     * Format percentage.
     * This simulates a helper function that would exist in your application.
     */
    private function formatPercentage(float $value, int $decimals = 0): string
    {
        return number_format($value, $decimals) . '%';
    }

    /**
     * Truncate text to specified length.
     * This simulates a helper function that would exist in your application.
     */
    private function truncateText(string $text, int $length): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length - 3) . '...';
    }
}
