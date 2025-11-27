<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

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
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * The valid expense categories.
     *
     * @var array<int, string>
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
}
