# Copilot Instructions for Expense Tracker

## Project Overview
Laravel 11 expense tracking application with CRUD operations, category filtering, and daily/monthly views. Uses SQLite database and follows Material UI design principles.

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

### Testing
```bash
cd laravel-app
php artisan test                    # Run all tests
php artisan test --filter=Expense   # Run expense tests only
```

Tests use `RefreshDatabase` trait - each test gets a clean database. Feature tests are in `tests/Feature/ExpenseControllerTest.php`.

### CI/CD
GitHub Actions workflow (`.github/workflows/laravel.yml`) runs on PRs to `main`:
- PHP 8.4 with SQLite
- Runs `php artisan test`
- Runs Laravel Pint for code style
- **PRs blocked if tests fail**

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

## Authentication (Feature 002-user-auth)

**Pattern**: Session-based authentication with custom middleware (no Eloquent User model)

### Configuration
- **Credentials**: Stored in environment variables `USERNAME` and `PASSWORD`
- **Session flag**: `session('authenticated')` boolean indicates auth state
- **Middleware**: `auth.custom` alias for `App\Http\Middleware\Authenticate`
- **Rate limiting**: 5 attempts per username OR 10 per IP in 15 minutes

### Key Components
- **AuthController** (`app/Http/Controllers/AuthController.php`):
  - `showLogin()` - Display login form
  - `login()` - Validate credentials using `hash_equals()`, check rate limits
  - `logout()` - Clear session with `$request->session()->flush()`

- **Authenticate Middleware** (`app/Http/Middleware/Authenticate.php`):
  - Checks `session('authenticated') === true` on every protected request
  - Redirects to `/login` if not authenticated

- **Login View** (`resources/views/auth/login.blade.php`):
  - Material UI styled form
  - Username and password fields
  - CSRF protection via `@csrf`

### Security Patterns
- **Timing-safe comparison**: Always use `hash_equals()` for password validation
  ```php
  $valid = hash_equals(env('USERNAME', ''), $username) 
        && hash_equals(env('PASSWORD'), $password);
  ```

- **Rate limiting** with Laravel's RateLimiter:
  ```php
  $userKey = "login-user:$username";
  $ipKey = "login-ip:$ip";
  
  if (RateLimiter::tooManyAttempts($userKey, 5)) {
      // Return error
  }
  
  RateLimiter::hit($userKey, 15 * 60); // 15 minutes
  RateLimiter::clear($userKey); // On successful login
  ```

- **Custom Blade directive**: `@auth` checks `session('authenticated')`
  ```php
  // In AppServiceProvider
  Blade::if('auth', fn() => session('authenticated') === true);
  ```

### Route Protection
```php
// Auth routes (before middleware group)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth.custom'])->group(function () {
    Route::resource('expenses', ExpenseController::class);
    // ... other protected routes
});
```

### Testing
- **Environment**: Use `.env.testing` with `USERNAME=testuser` / `PASSWORD=testpass`
- **Feature tests**: Add `session(['authenticated' => true])` to setUp() for protected route tests
- **Auth tests**: Test login/logout flows, rate limiting, unauthenticated access blocking

### References
- Specification: `specs/002-user-auth/spec.md`
- Implementation plan: `specs/002-user-auth/plan.md`
- Developer guide: `specs/002-user-auth/quickstart.md`
