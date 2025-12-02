<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Valid expense categories.
     */
    public const CATEGORIES = [
        'Groceries',
        'Transport',
        'Housing and Utilities',
        'Restaurants and Cafes',
        'Health and Medicine',
        'Clothing & Footwear',
        'Entertainment',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'description',
        'amount',
        'category',
        'date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    /**
     * Get validation rules for expense creation/update.
     *
     * @return array<string, mixed>
     */
    public static function validationRules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'category' => ['required', 'string', 'in:' . implode(',', self::CATEGORIES)],
            'date' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    /**
     * Get validation messages for expense creation/update.
     *
     * @return array<string, string>
     */
    public static function validationMessages(): array
    {
        return [
            'description.required' => 'Description is required.',
            'description.max' => 'Description cannot exceed 255 characters.',
            'amount.required' => 'Amount is required.',
            'amount.min' => 'Amount must be at least $0.01.',
            'amount.max' => 'Amount cannot exceed $99,999,999.99.',
            'category.required' => 'Category is required.',
            'category.in' => 'Invalid category selected.',
            'date.required' => 'Date is required.',
            'date.before_or_equal' => 'Date cannot be in the future.',
        ];
    }
}
