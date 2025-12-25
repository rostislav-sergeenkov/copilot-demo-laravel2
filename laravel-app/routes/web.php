<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;

// Public authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes (require authentication)
Route::middleware(['auth.custom'])->group(function () {
  // Root route redirects to expenses
  Route::get('/', [ExpenseController::class, 'index'])->name('home');

  // Custom expense views (must be before resource routes)
  Route::get('expenses/daily', [ExpenseController::class, 'daily'])->name('expenses.daily');
  Route::get('expenses/monthly', [ExpenseController::class, 'monthly'])->name('expenses.monthly');

  // Resource routes for expenses
  Route::resource('expenses', ExpenseController::class);
});
