<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'description',
        'amount',
        'category',
        'date',
    ];
}
