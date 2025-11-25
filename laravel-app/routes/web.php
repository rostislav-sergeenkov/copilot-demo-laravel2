<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\ExpenseController;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('expenses', ExpenseController::class);
Route::get('expenses/export/monthly-csv', [ExpenseController::class, 'exportMonthlyCsv'])->name('expenses.export.monthly');
