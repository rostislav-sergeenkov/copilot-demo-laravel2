<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\StoreExpenseRequest;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreExpenseRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that all users are authorized to create expenses
     */
    public function test_authorize_returns_true(): void
    {
        $request = new StoreExpenseRequest();
        
        $this->assertTrue($request->authorize());
    }

    /**
     * Test that validation rules contain all required fields
     */
    public function test_rules_contain_all_required_fields(): void
    {
        $request = new StoreExpenseRequest();
        $rules = $request->rules();
        
        $this->assertArrayHasKey('description', $rules);
        $this->assertArrayHasKey('amount', $rules);
        $this->assertArrayHasKey('category', $rules);
        $this->assertArrayHasKey('date', $rules);
    }

    /**
     * Test description field validation rules
     */
    public function test_description_field_has_correct_rules(): void
    {
        $request = new StoreExpenseRequest();
        $rules = $request->rules();
        
        $this->assertContains('required', $rules['description']);
        $this->assertContains('string', $rules['description']);
        $this->assertContains('max:255', $rules['description']);
    }

    /**
     * Test amount field validation rules
     */
    public function test_amount_field_has_correct_rules(): void
    {
        $request = new StoreExpenseRequest();
        $rules = $request->rules();
        
        $this->assertContains('required', $rules['amount']);
        $this->assertContains('numeric', $rules['amount']);
        $this->assertContains('min:0.01', $rules['amount']);
        $this->assertContains('max:999999.99', $rules['amount']);
    }

    /**
     * Test category field validation rules
     */
    public function test_category_field_has_correct_rules(): void
    {
        $request = new StoreExpenseRequest();
        $rules = $request->rules();
        
        $this->assertContains('required', $rules['category']);
        $this->assertContains('string', $rules['category']);
        
        // Verify the 'in' rule contains all categories
        $inRule = collect($rules['category'])->first(fn($rule) => str_starts_with($rule, 'in:'));
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
        $request = new StoreExpenseRequest();
        $rules = $request->rules();
        
        $this->assertContains('required', $rules['date']);
        $this->assertContains('date', $rules['date']);
        $this->assertContains('before_or_equal:today', $rules['date']);
    }

    /**
     * Test that custom validation messages are defined
     */
    public function test_custom_messages_are_defined(): void
    {
        $request = new StoreExpenseRequest();
        $messages = $request->messages();
        
        $this->assertIsArray($messages);
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
        $request = new StoreExpenseRequest();
        $validator = Validator::make([
            'description' => 'Test expense',
            'amount' => '100.00',
            'category' => 'Groceries',
            'date' => now()->format('Y-m-d'),
        ], $request->rules());
        
        $this->assertFalse($validator->fails());
    }

    /**
     * Test validation fails with missing description
     */
    public function test_validation_fails_with_missing_description(): void
    {
        $request = new StoreExpenseRequest();
        $validator = Validator::make([
            'amount' => '100.00',
            'category' => 'Groceries',
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
        $request = new StoreExpenseRequest();
        $validator = Validator::make([
            'description' => 'Test expense',
            'amount' => '0.00', // Below minimum
            'category' => 'Groceries',
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
        $request = new StoreExpenseRequest();
        $validator = Validator::make([
            'description' => 'Test expense',
            'amount' => '100.00',
            'category' => 'Invalid Category',
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
        $request = new StoreExpenseRequest();
        $validator = Validator::make([
            'description' => 'Test expense',
            'amount' => '100.00',
            'category' => 'Groceries',
            'date' => now()->addDay()->format('Y-m-d'),
        ], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('date', $validator->errors()->toArray());
    }

    /**
     * Test validation accepts all valid categories
     */
    public function test_validation_accepts_all_valid_categories(): void
    {
        $request = new StoreExpenseRequest();
        
        foreach (Expense::CATEGORIES as $category) {
            $validator = Validator::make([
                'description' => 'Test expense',
                'amount' => '100.00',
                'category' => $category,
                'date' => now()->format('Y-m-d'),
            ], $request->rules());
            
            $this->assertFalse($validator->fails(), "Category '{$category}' should be valid");
        }
    }

    /**
     * Test validation accepts maximum amount
     */
    public function test_validation_accepts_maximum_amount(): void
    {
        $request = new StoreExpenseRequest();
        $validator = Validator::make([
            'description' => 'Test expense',
            'amount' => '999999.99',
            'category' => 'Groceries',
            'date' => now()->format('Y-m-d'),
        ], $request->rules());
        
        $this->assertFalse($validator->fails());
    }

    /**
     * Test validation accepts minimum amount
     */
    public function test_validation_accepts_minimum_amount(): void
    {
        $request = new StoreExpenseRequest();
        $validator = Validator::make([
            'description' => 'Test expense',
            'amount' => '0.01',
            'category' => 'Groceries',
            'date' => now()->format('Y-m-d'),
        ], $request->rules());
        
        $this->assertFalse($validator->fails());
    }

    /**
     * Test validation accepts description at maximum length
     */
    public function test_validation_accepts_maximum_length_description(): void
    {
        $request = new StoreExpenseRequest();
        $validator = Validator::make([
            'description' => str_repeat('a', 255),
            'amount' => '100.00',
            'category' => 'Groceries',
            'date' => now()->format('Y-m-d'),
        ], $request->rules());
        
        $this->assertFalse($validator->fails());
    }

    /**
     * Test validation fails with description exceeding maximum length
     */
    public function test_validation_fails_with_description_too_long(): void
    {
        $request = new StoreExpenseRequest();
        $validator = Validator::make([
            'description' => str_repeat('a', 256),
            'amount' => '100.00',
            'category' => 'Groceries',
            'date' => now()->format('Y-m-d'),
        ], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('description', $validator->errors()->toArray());
    }

    /**
     * Test validation accepts Unicode characters in description
     */
    public function test_validation_accepts_unicode_in_description(): void
    {
        $request = new StoreExpenseRequest();
        $validator = Validator::make([
            'description' => '🎉 Birthday party supplies! 🎈',
            'amount' => '100.00',
            'category' => 'Entertainment',
            'date' => now()->format('Y-m-d'),
        ], $request->rules());
        
        $this->assertFalse($validator->fails());
    }
}
