# Copilot Instructions for Expense Tracker

## Project Overview
Laravel 11 expense tracking application with CRUD operations, category filtering, and daily/monthly views. Uses SQLite database and follows Material UI design principles.

## Additional Guidelines

This is the main instruction file. For detailed guidelines on specific topics, refer to:

- **[instructions/testing.md](./instructions/testing.md)** - Testing strategy, test writing guidelines, CI/CD
- **[instructions/authentication.md](./instructions/authentication.md)** - Authentication patterns, security, route protection

## Architecture

### Directory Structure
- **Working directory**: All Laravel code is in `laravel-app/` - always `cd laravel-app` before running artisan commands
- **Single model**: `Expense` is the only domain model (`app/Models/Expense.php`)
- **Form Requests**: Validation lives in `app/Http/Requests/` (StoreExpenseRequest, UpdateExpenseRequest)
- **Views**: Blade templates in `resources/views/expenses/` with shared partial `_form.blade.php`

### Key Patterns

**Categories are defined as constants** in the Expense model - always reference `Expense::CATEGORIES`:
```php
public const CATEGORIES = [
    'Groceries', 'Transport', 'Housing and Utilities',
    'Restaurants and Cafes', 'Health and Medicine',
    'Clothing & Footwear', 'Entertainment',
];
```

**Validation rules** are centralized in `Expense::validationRules()` and Form Request classes - don't duplicate validation logic.

**Soft deletes** are enabled - use `SoftDeletes` trait, never hard-delete expenses.

**Factory states** for testing - use `->category('Groceries')` or `->today()` states in `ExpenseFactory`.

## Developer Workflow

### Setup & Running
```bash
cd laravel-app
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed  # Seeds sample expenses
php artisan serve    # http://127.0.0.1:8000
```

## Conventions

### Routes
Custom routes (`/expenses/daily`, `/expenses/monthly`) must be defined **before** `Route::resource()` in `routes/web.php`.

### Controller Methods
- `index()` - paginated list with category filter
- `daily()` - expenses for specific date with category breakdown
- `monthly()` - expenses for month with category percentages

### Database
- Amount stored as `decimal(10,2)`, cast to `'decimal:2'` in model
- Date stored as `date` type, cast to Carbon in model
- Indexes exist on `date` and `category` columns

### Views
- Layout: `resources/views/layouts/app.blade.php`
- All expense views share `_form.blade.php` partial for create/edit forms
- Category filter dropdown present on all list views

## Code Style
- Laravel Pint enforces PSR-12 style
- Use typed properties and return types
- PHPDoc blocks on public methods
- Strict types enabled: `declare(strict_types=1);`

## Quick Reference

### Common Commands

```bash
# Development
cd laravel-app
php artisan serve              # Start dev server
php artisan test              # Run all tests (~8s)
php artisan pint              # Fix code style

# E2E Testing
npm run test:e2e              # Happy path (~3 min)
npm run test:e2e:all          # All tests (~20 min)
npm run test:e2e:ui           # Interactive mode

# Database
php artisan migrate:fresh     # Reset database
php artisan db:seed           # Seed sample data
```

### Key Files

- **Model**: `app/Models/Expense.php`
- **Controller**: `app/Http/Controllers/ExpenseController.php`
- **Form Requests**: `app/Http/Requests/StoreExpenseRequest.php`, `UpdateExpenseRequest.php`
- **Routes**: `routes/web.php`
- **Views**: `resources/views/expenses/`

### Documentation

- **Testing**: [instructions/testing.md](./instructions/testing.md) - Complete testing guidelines
- **Authentication**: [instructions/authentication.md](./instructions/authentication.md) - Auth patterns and security
- **Architecture**: `docs/project-architecture-blueprint.md` - Full architecture overview
- **Code Quality**: `docs/code-quality.md` - Linting and static analysis

