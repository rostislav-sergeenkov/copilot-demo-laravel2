# Expense Tracker - Implementation Plan

## Overview

This document outlines the step-by-step implementation plan for building the Expense Tracker application using Laravel framework with **minimal external libraries**. The frontend uses **vanilla HTML, CSS, and JavaScript** to keep the application lightweight and maintainable.

---

## Technology Stack

### Backend
| Component | Technology | Notes |
|-----------|------------|-------|
| Framework | Laravel 11.x | Core PHP framework |
| Database | SQLite | Local file-based database |
| Testing | PHPUnit | Built into Laravel |
| Validation | Laravel Validation | Built-in form requests |

### Frontend
| Component | Technology | Notes |
|-----------|------------|-------|
| Templating | Blade | Laravel's built-in templating |
| Styling | Vanilla CSS | Custom CSS, no frameworks |
| Interactivity | Vanilla JavaScript | No jQuery, no frameworks |
| Icons | SVG inline | No icon libraries |

### DevOps
| Component | Technology | Notes |
|-----------|------------|-------|
| Version Control | Git + GitHub | Repository hosting |
| CI/CD | GitHub Actions | Automated testing |

### Libraries NOT Used
- ❌ Tailwind CSS (use vanilla CSS)
- ❌ Bootstrap (use vanilla CSS)
- ❌ jQuery (use vanilla JS)
- ❌ Vue.js/React (use vanilla JS)
- ❌ Vite asset bundling for CSS/JS (inline or direct links)

---

## Implementation Phases

### Phase 1: Project Foundation
**Duration:** Day 1  
**Issues:** #21, #22, #32

#### Step 1.1: Initialize Laravel Project
```powershell
# Navigate to project directory
cd c:\Users\Rostislav_Sergeenkov\ROST\PROJECTS\copilot-demo-laravel2

# Create Laravel project (if laravel-app is empty)
composer create-project laravel/laravel laravel-app

# Navigate into the app
cd laravel-app
```

#### Step 1.2: Configure SQLite Database
**File:** `laravel-app/.env`
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

**Create SQLite file:**
```powershell
# Create empty SQLite database file
New-Item -Path "database/database.sqlite" -ItemType File
```

#### Step 1.3: Create Expenses Migration
**File:** `database/migrations/xxxx_xx_xx_create_expenses_table.php`

```php
Schema::create('expenses', function (Blueprint $table) {
    $table->id();
    $table->string('description', 255);
    $table->decimal('amount', 10, 2);
    $table->string('category');
    $table->date('date');
    $table->timestamps();
    $table->softDeletes();
    
    // Indexes for performance
    $table->index('date');
    $table->index('category');
    $table->index(['date', 'category']);
});
```

#### Step 1.4: Create Expense Model
**File:** `app/Models/Expense.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'description',
        'amount',
        'category',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

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
```

#### Step 1.5: Create Expense Factory and Seeder
**File:** `database/factories/ExpenseFactory.php`
**File:** `database/seeders/ExpenseSeeder.php`

Generate 50+ sample expenses across all categories and multiple months.

---

### Phase 2: Backend Implementation
**Duration:** Day 2  
**Issues:** #23

#### Step 2.1: Create Form Request Classes
**File:** `app/Http/Requests/StoreExpenseRequest.php`

```php
<?php

namespace App\Http\Requests;

use App\Models\Expense;
use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'category' => 'required|in:' . implode(',', Expense::CATEGORIES),
            'date' => 'required|date|before_or_equal:today',
        ];
    }
}
```

#### Step 2.2: Create ExpenseController
**File:** `app/Http/Controllers/ExpenseController.php`

Implement all CRUD methods:
- `index()` - List with pagination and optional category filter
- `create()` - Show create form
- `store()` - Validate and save new expense
- `edit()` - Show edit form with expense data
- `update()` - Validate and update expense
- `destroy()` - Soft delete expense
- `daily()` - Group expenses by date
- `monthly()` - Aggregate expenses by month

#### Step 2.3: Define Routes
**File:** `routes/web.php`

```php
<?php

use App\Http\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('expenses.index'));

Route::resource('expenses', ExpenseController::class)->except(['show']);
Route::get('expenses/daily', [ExpenseController::class, 'daily'])->name('expenses.daily');
Route::get('expenses/monthly', [ExpenseController::class, 'monthly'])->name('expenses.monthly');
```

---

### Phase 3: Frontend Implementation (Vanilla CSS/JS)
**Duration:** Days 3-4  
**Issues:** #24, #25

#### Step 3.1: Create Base Layout
**File:** `resources/views/layouts/app.blade.php`

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Expense Tracker')</title>
    <style>
        /* ========== CSS Variables (Material Design Colors) ========== */
        :root {
            --primary: #1976D2;
            --primary-dark: #1565C0;
            --primary-light: #BBDEFB;
            --accent: #FF5722;
            --text-primary: #212121;
            --text-secondary: #757575;
            --divider: #BDBDBD;
            --background: #FAFAFA;
            --surface: #FFFFFF;
            --error: #D32F2F;
            --success: #388E3C;
            --shadow-1: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
            --shadow-2: 0 3px 6px rgba(0,0,0,0.16), 0 3px 6px rgba(0,0,0,0.23);
            --spacing-unit: 8px;
        }

        /* ========== Reset & Base ========== */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Roboto, sans-serif;
            background: var(--background);
            color: var(--text-primary);
            line-height: 1.5;
        }

        /* ========== Layout ========== */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: calc(var(--spacing-unit) * 2);
        }

        /* ... Additional CSS ... */
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <a href="{{ route('expenses.index') }}" class="nav-brand">Expense Tracker</a>
            <ul class="nav-links">
                <li><a href="{{ route('expenses.index') }}">All Expenses</a></li>
                <li><a href="{{ route('expenses.daily') }}">Daily</a></li>
                <li><a href="{{ route('expenses.monthly') }}">Monthly</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    <script>
        // ========== Vanilla JavaScript ========== //
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide flash messages after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });

            // Delete confirmation
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to delete this expense?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
```

#### Step 3.2: Create Expense Views

**Index View:** `resources/views/expenses/index.blade.php`
- Expense table with sorting
- Category filter dropdown
- Pagination
- Action buttons (Edit, Delete)

**Form Partial:** `resources/views/expenses/_form.blade.php`
- Reusable form for create/edit
- Inline validation error display
- Native HTML5 form validation

**Create View:** `resources/views/expenses/create.blade.php`
**Edit View:** `resources/views/expenses/edit.blade.php`

#### Step 3.3: Material Design CSS Components

All CSS is written inline in the layout or as a separate CSS file (no preprocessors):

```css
/* Cards */
.card {
    background: var(--surface);
    border-radius: 4px;
    box-shadow: var(--shadow-1);
    padding: calc(var(--spacing-unit) * 2);
    margin-bottom: calc(var(--spacing-unit) * 2);
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 36px;
    padding: 0 16px;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    text-transform: uppercase;
    cursor: pointer;
    transition: background-color 0.2s, box-shadow 0.2s;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-dark);
    box-shadow: var(--shadow-2);
}

/* Form Inputs */
.form-group {
    margin-bottom: calc(var(--spacing-unit) * 2);
}

.form-label {
    display: block;
    margin-bottom: var(--spacing-unit);
    font-size: 12px;
    color: var(--text-secondary);
    text-transform: uppercase;
}

.form-input {
    width: 100%;
    padding: 12px;
    border: 1px solid var(--divider);
    border-radius: 4px;
    font-size: 16px;
    transition: border-color 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: var(--primary);
}

/* Tables */
.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid var(--divider);
}

.table th {
    font-weight: 500;
    color: var(--text-secondary);
    font-size: 12px;
    text-transform: uppercase;
}
```

#### Step 3.4: Vanilla JavaScript Features

```javascript
// Form validation feedback
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], select[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            showError(input, 'This field is required');
            isValid = false;
        } else {
            clearError(input);
        }
    });
    
    return isValid;
}

function showError(input, message) {
    const group = input.closest('.form-group');
    group.classList.add('has-error');
    let errorEl = group.querySelector('.error-message');
    if (!errorEl) {
        errorEl = document.createElement('span');
        errorEl.className = 'error-message';
        group.appendChild(errorEl);
    }
    errorEl.textContent = message;
}

function clearError(input) {
    const group = input.closest('.form-group');
    group.classList.remove('has-error');
    const errorEl = group.querySelector('.error-message');
    if (errorEl) errorEl.remove();
}

// Category filter (no page reload option)
function filterByCategory(category) {
    const url = new URL(window.location);
    if (category) {
        url.searchParams.set('category', category);
    } else {
        url.searchParams.delete('category');
    }
    window.location = url;
}
```

---

### Phase 4: Feature Views Implementation
**Duration:** Day 4  
**Issues:** #26, #27, #28

#### Step 4.1: Daily Expenses View
**File:** `resources/views/expenses/daily.blade.php`

```html
@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h1>Daily Expenses</h1>
        <div class="date-nav">
            <a href="?date={{ $previousDate }}" class="btn btn-icon">←</a>
            <input type="date" value="{{ $selectedDate }}" onchange="window.location='?date='+this.value">
            <a href="?date={{ $nextDate }}" class="btn btn-icon">→</a>
            <a href="?date={{ $today }}" class="btn btn-text">Today</a>
        </div>
    </div>
    
    <div class="daily-summary">
        <strong>Total: ${{ number_format($dailyTotal, 2) }}</strong>
    </div>
    
    @forelse($expenses as $expense)
        <div class="expense-item">
            <span class="expense-description">{{ $expense->description }}</span>
            <span class="expense-category badge">{{ $expense->category }}</span>
            <span class="expense-amount">${{ number_format($expense->amount, 2) }}</span>
        </div>
    @empty
        <p class="empty-state">No expenses recorded for this date.</p>
    @endforelse
</div>
@endsection
```

#### Step 4.2: Monthly Expenses View
**File:** `resources/views/expenses/monthly.blade.php`

Display monthly totals with category breakdown using pure server-side rendering.

#### Step 4.3: Category Filtering
Add category filter dropdown to all views that updates URL query parameter.

---

### Phase 5: Testing Implementation
**Duration:** Day 5  
**Issues:** #29, #30

#### Step 5.1: Configure PHPUnit for SQLite
**File:** `phpunit.xml`

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

#### Step 5.2: Unit Tests
**File:** `tests/Unit/Models/ExpenseTest.php`

```php
<?php

namespace Tests\Unit\Models;

use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_expense_with_valid_data(): void
    {
        $expense = Expense::create([
            'description' => 'Test expense',
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => '2025-12-01',
        ]);

        $this->assertDatabaseHas('expenses', [
            'description' => 'Test expense',
            'amount' => 50.00,
        ]);
    }

    public function test_expense_can_be_soft_deleted(): void
    {
        $expense = Expense::factory()->create();
        $expense->delete();

        $this->assertSoftDeleted($expense);
    }

    // Additional tests...
}
```

#### Step 5.3: Feature Tests
**File:** `tests/Feature/ExpenseControllerTest.php`

Test all CRUD operations, validation, and views.

---

### Phase 6: CI/CD Setup
**Duration:** Day 5  
**Issues:** #31

#### Step 6.1: GitHub Actions Workflow
**File:** `.github/workflows/laravel.yml`

```yaml
name: Laravel Tests

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  tests:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: sqlite3, pdo_sqlite

      - name: Cache Composer dependencies
        uses: actions/cache@v3
        with:
          path: vendor
          key: composer-${{ hashFiles('**/composer.lock') }}

      - name: Install Dependencies
        run: |
          cd laravel-app
          composer install --prefer-dist --no-progress

      - name: Prepare Application
        run: |
          cd laravel-app
          cp .env.example .env
          php artisan key:generate

      - name: Run Tests
        run: |
          cd laravel-app
          php artisan test
```

---

## File Checklist

### Backend Files
- [ ] `app/Models/Expense.php`
- [ ] `app/Http/Controllers/ExpenseController.php`
- [ ] `app/Http/Requests/StoreExpenseRequest.php`
- [ ] `app/Http/Requests/UpdateExpenseRequest.php`
- [ ] `database/migrations/xxxx_create_expenses_table.php`
- [ ] `database/factories/ExpenseFactory.php`
- [ ] `database/seeders/ExpenseSeeder.php`
- [ ] `routes/web.php`

### Frontend Files (Vanilla HTML/CSS/JS)
- [ ] `resources/views/layouts/app.blade.php`
- [ ] `resources/views/expenses/index.blade.php`
- [ ] `resources/views/expenses/create.blade.php`
- [ ] `resources/views/expenses/edit.blade.php`
- [ ] `resources/views/expenses/_form.blade.php`
- [ ] `resources/views/expenses/daily.blade.php`
- [ ] `resources/views/expenses/monthly.blade.php`
- [ ] `public/css/app.css` (optional, can be inline)
- [ ] `public/js/app.js` (optional, can be inline)

### Test Files
- [ ] `tests/Unit/Models/ExpenseTest.php`
- [ ] `tests/Feature/ExpenseControllerTest.php`

### Configuration Files
- [ ] `.env` (SQLite configuration)
- [ ] `phpunit.xml` (test database configuration)
- [ ] `.github/workflows/laravel.yml`

---

## Commands Reference

```powershell
# Project Setup
cd laravel-app
composer install
cp .env.example .env
php artisan key:generate
New-Item -Path "database/database.sqlite" -ItemType File

# Database
php artisan migrate
php artisan db:seed --class=ExpenseSeeder

# Development
php artisan serve

# Testing
php artisan test
php artisan test --filter=ExpenseTest
php artisan test --coverage

# Code Generation
php artisan make:model Expense -mf
php artisan make:controller ExpenseController --resource
php artisan make:request StoreExpenseRequest
php artisan make:request UpdateExpenseRequest
php artisan make:seeder ExpenseSeeder
php artisan make:test ExpenseTest --unit
php artisan make:test ExpenseControllerTest
```

---

## Implementation Timeline

| Day | Phase | Tasks | Issues |
|-----|-------|-------|--------|
| 1 | Foundation | Laravel setup, SQLite config, Migration, Model, Seeder | #21, #22, #32 |
| 2 | Backend | Controller, Form Requests, Routes | #23 |
| 3 | Frontend | Layout, CSS styles, Base components | #24 |
| 4 | Views | CRUD views, Daily view, Monthly view, Filtering | #25, #26, #27, #28 |
| 5 | Quality | Unit tests, Feature tests, GitHub Actions | #29, #30, #31 |

---

## Key Implementation Notes

### Why Vanilla CSS/JS?
1. **Reduced complexity** - No build step required for styles
2. **Faster load times** - No large framework bundles
3. **Better understanding** - Direct control over all code
4. **Laravel integration** - Works seamlessly with Blade

### CSS Strategy
- Use CSS custom properties (variables) for theming
- Inline critical CSS in `<head>` for fast initial render
- Keep CSS organized with clear comments/sections
- Use BEM-like naming for maintainability

### JavaScript Strategy
- Use modern vanilla JS (ES6+)
- No build tools required
- Progressive enhancement approach
- Forms work without JS (server-side validation)

### Performance Optimizations
- No external CSS/JS dependencies
- Database indexes for common queries
- Pagination for large datasets
- Efficient Eloquent queries with eager loading

---

## References

- [Specification Document](/.github/copilot-specification.md)
- [Project Constitution](/.github/copilot-constitution.md)
- [Requirements](/requirement.txt)
- [GitHub Issues](https://github.com/rostislav-sergeenkov/copilot-demo-laravel2/issues)
