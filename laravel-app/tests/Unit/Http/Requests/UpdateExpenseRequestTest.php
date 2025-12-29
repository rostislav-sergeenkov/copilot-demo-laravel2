<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateExpenseRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that all users are authorized to update expenses
     */
    public function test_authorize_returns_true(): void
    {
        $request = new UpdateExpenseRequest;

        $this->assertTrue($request->authorize());
    }

    /**
     * Test that validation rules contain all required fields
     */
    public function test_rules_contain_all_required_fields(): void
    {
        $request = new UpdateExpenseRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('description', $rules);
        $this->assertArrayHasKey('amount', $rules);
        $this->assertArrayHasKey('category', $rules);
        $this->assertArrayHasKey('date', $rules);
    }

    /**
     * Test that update request has same rules as store request
     */
    public function test_rules_match_store_request_rules(): void
    {
        $updateRequest = new UpdateExpenseRequest;
        $storeRequest = new \App\Http\Requests\StoreExpenseRequest;

        $this->assertEquals($storeRequest->rules(), $updateRequest->rules());
    }

    /**
     * Test description field validation rules
     */
    public function test_description_field_has_correct_rules(): void
    {
        $request = new UpdateExpenseRequest;
        $rules = $request->rules();

        /** @var array<int|string, mixed> $descriptionRules */
        $descriptionRules = is_array($rules['description']) ? $rules['description'] : [$rules['description']];
        $this->assertContains('required', $descriptionRules);
        $this->assertContains('string', $descriptionRules);
        $this->assertContains('max:255', $descriptionRules);
    }

    /**
     * Test amount field validation rules
     */
    public function test_amount_field_has_correct_rules(): void
    {
        $request = new UpdateExpenseRequest;
        $rules = $request->rules();

        /** @var array<int|string, mixed> $amountRules */
        $amountRules = is_array($rules['amount']) ? $rules['amount'] : [$rules['amount']];
        $this->assertContains('required', $amountRules);
        $this->assertContains('numeric', $amountRules);
        $this->assertContains('min:0.01', $amountRules);
        $this->assertContains('max:999999.99', $amountRules);
    }

    /**
     * Test category field validation rules
     */
    public function test_category_field_has_correct_rules(): void
    {
        $request = new UpdateExpenseRequest;
        $rules = $request->rules();

        /** @var array<int|string, mixed> $categoryRules */
        $categoryRules = is_array($rules['category']) ? $rules['category'] : [$rules['category']];
        $this->assertContains('required', $categoryRules);
        $this->assertContains('string', $categoryRules);

        // Verify the 'in' rule contains all categories
        $inRule = collect($categoryRules)->first(fn ($rule) => is_string($rule) && str_starts_with($rule, 'in:'));
        $this->assertNotNull($inRule);

        foreach (Expense::CATEGORIES as $category) {
            $this->assertStringContainsString($category, $inRule);
        }
    }

    /**
     * Test date field validation rules
     */
    public function test_date_field_has_correct_rules(): void
    {
        $request = new UpdateExpenseRequest;
        $rules = $request->rules();

        /** @var array<int|string, mixed> $dateRules */
        $dateRules = is_array($rules['date']) ? $rules['date'] : [$rules['date']];
        $this->assertContains('required', $dateRules);
        $this->assertContains('date', $dateRules);
        $this->assertContains('before_or_equal:today', $dateRules);
    }

    /**
     * Test that custom validation messages are defined
     */
    public function test_custom_messages_are_defined(): void
    {
        $request = new UpdateExpenseRequest;
        $messages = $request->messages();

        $this->assertNotEmpty($messages);

        // Check that key messages exist
        $this->assertArrayHasKey('description.required', $messages);
        $this->assertArrayHasKey('amount.required', $messages);
        $this->assertArrayHasKey('category.required', $messages);
        $this->assertArrayHasKey('date.required', $messages);
    }

    /**
     * Test validation passes with valid data
     */
    public function test_validation_passes_with_valid_data(): void
    {
        $request = new UpdateExpenseRequest;
        $validator = Validator::make([
            'description' => 'Updated expense',
            'amount' => '150.00',
            'category' => 'Transport',
            'date' => now()->format('Y-m-d'),
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    /**
     * Test validation fails with missing description
     */
    public function test_validation_fails_with_missing_description(): void
    {
        $request = new UpdateExpenseRequest;
        $validator = Validator::make([
            'amount' => '150.00',
            'category' => 'Transport',
            'date' => now()->format('Y-m-d'),
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('description', $validator->errors()->toArray());
    }

    /**
     * Test validation fails with invalid amount
     */
    public function test_validation_fails_with_invalid_amount(): void
    {
        $request = new UpdateExpenseRequest;
        $validator = Validator::make([
            'description' => 'Updated expense',
            'amount' => '-10.00', // Negative amount
            'category' => 'Transport',
            'date' => now()->format('Y-m-d'),
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('amount', $validator->errors()->toArray());
    }

    /**
     * Test validation fails with invalid category
     */
    public function test_validation_fails_with_invalid_category(): void
    {
        $request = new UpdateExpenseRequest;
        $validator = Validator::make([
            'description' => 'Updated expense',
            'amount' => '150.00',
            'category' => 'NonExistentCategory',
            'date' => now()->format('Y-m-d'),
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category', $validator->errors()->toArray());
    }

    /**
     * Test validation fails with future date
     */
    public function test_validation_fails_with_future_date(): void
    {
        $request = new UpdateExpenseRequest;
        $validator = Validator::make([
            'description' => 'Updated expense',
            'amount' => '150.00',
            'category' => 'Transport',
            'date' => now()->addDays(5)->format('Y-m-d'),
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('date', $validator->errors()->toArray());
    }

    /**
     * Test validation accepts all valid categories
     */
    public function test_validation_accepts_all_valid_categories(): void
    {
        $request = new UpdateExpenseRequest;

        foreach (Expense::CATEGORIES as $category) {
            $validator = Validator::make([
                'description' => 'Updated expense',
                'amount' => '150.00',
                'category' => $category,
                'date' => now()->format('Y-m-d'),
            ], $request->rules());

            $this->assertFalse($validator->fails(), "Category '{$category}' should be valid");
        }
    }

    /**
     * Test validation accepts today's date
     */
    public function test_validation_accepts_todays_date(): void
    {
        $request = new UpdateExpenseRequest;
        $validator = Validator::make([
            'description' => 'Updated expense',
            'amount' => '150.00',
            'category' => 'Transport',
            'date' => now()->format('Y-m-d'),
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    /**
     * Test validation accepts past dates
     */
    public function test_validation_accepts_past_dates(): void
    {
        $request = new UpdateExpenseRequest;
        $validator = Validator::make([
            'description' => 'Updated expense',
            'amount' => '150.00',
            'category' => 'Transport',
            'date' => now()->subDays(30)->format('Y-m-d'),
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    /**
     * Test validation accepts amount boundaries
     */
    public function test_validation_accepts_amount_boundaries(): void
    {
        $request = new UpdateExpenseRequest;

        // Minimum amount
        $validator = Validator::make([
            'description' => 'Updated expense',
            'amount' => '0.01',
            'category' => 'Transport',
            'date' => now()->format('Y-m-d'),
        ], $request->rules());
        $this->assertFalse($validator->fails());

        // Maximum amount
        $validator = Validator::make([
            'description' => 'Updated expense',
            'amount' => '999999.99',
            'category' => 'Transport',
            'date' => now()->format('Y-m-d'),
        ], $request->rules());
        $this->assertFalse($validator->fails());
    }

    /**
     * Test validation accepts Unicode characters
     */
    public function test_validation_accepts_unicode_in_description(): void
    {
        $request = new UpdateExpenseRequest;
        $validator = Validator::make([
            'description' => 'Café au lait ☕',
            'amount' => '5.50',
            'category' => 'Restaurants and Cafes',
            'date' => now()->format('Y-m-d'),
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }
}
