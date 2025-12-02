<?php

use App\Http\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('expenses.index');
});

// Custom expense views (must be before resource routes)
Route::get('expenses/daily', [ExpenseController::class, 'daily'])->name('expenses.daily');
Route::get('expenses/monthly', [ExpenseController::class, 'monthly'])->name('expenses.monthly');

Route::resource('expenses', ExpenseController::class);
