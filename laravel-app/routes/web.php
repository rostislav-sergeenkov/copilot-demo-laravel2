<?php

use App\Http\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('expenses.index');
});

Route::resource('expenses', ExpenseController::class);
