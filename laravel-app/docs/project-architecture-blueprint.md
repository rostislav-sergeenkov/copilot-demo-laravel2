# Expense Tracker - Architecture Blueprint

> Generated: December 30, 2025  
> Version: 2.0.0  
> Tech Stack: Laravel 11 / PHP 8.4 / SQLite / Session-Based Auth

---

## 1. Architecture Overview

### Architectural Pattern: MVC (Model-View-Controller)

This application follows Laravel's standard **MVC architecture** with these key characteristics:

- **Single Domain Model**: `Expense` is the sole business entity
- **Session-Based Authentication**: Custom middleware with environment-based credentials
- **Server-Side Rendering**: Blade templates with Material UI design
- **Form Request Validation**: Dedicated request classes for input validation
- **Soft Deletes**: Data preservation pattern for all deletions

### Guiding Principles

1. **Simplicity Over Abstraction**: Single model, no repositories or service layers
2. **Convention Over Configuration**: Follows Laravel defaults
3. **Centralized Validation**: Rules defined in model, consumed by Form Requests
4. **Shared View Components**: Blade partials reduce duplication

---

## 2. Architecture Visualization

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           CLIENT (Browser)                               │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                         PRESENTATION LAYER                               │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────────────┐  │
│  │   Blade Views   │  │  Layout (app)   │  │   Static Assets         │  │
│  │  - index        │  │  - Header/Nav   │  │   - app.css (Material)  │  │
│  │  - daily        │  │  - Flash Msgs   │  │   - app.js              │  │
│  │  - monthly      │  │  - Footer       │  │                         │  │
│  │  - create/edit  │  │                 │  │                         │  │
│  │  - _form.blade  │  │                 │  │                         │  │
│  └─────────────────┘  └─────────────────┘  └─────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                          APPLICATION LAYER                               │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │                     routes/web.php                                │   │
│  │   GET  /                    → redirect to expenses.index          │   │
│  │   GET  /expenses/daily      → ExpenseController@daily            │   │
│  │   GET  /expenses/monthly    → ExpenseController@monthly          │   │
│  │   Resource: /expenses       → ExpenseController (CRUD)           │   │
│  └──────────────────────────────────────────────────────────────────┘   │
│                                    │                                     │
│                                    ▼                                     │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │                    ExpenseController                              │   │
│  │   - index()   : Paginated list with category filter               │   │
│  │   - daily()   : Date-specific expenses with breakdown             │   │
│  │   - monthly() : Month aggregation with percentages                │   │
│  │   - create()  : Show form                                         │   │
│  │   - store()   : Create expense (via StoreExpenseRequest)          │   │
│  │   - show()    : Display single expense                            │   │
│  │   - edit()    : Show edit form                                    │   │
│  │   - update()  : Update expense (via UpdateExpenseRequest)         │   │
│  │   - destroy() : Soft delete expense                               │   │
│  └──────────────────────────────────────────────────────────────────┘   │
│                                    │                                     │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │                   Form Requests (Validation)                      │   │
│  │   StoreExpenseRequest   │   UpdateExpenseRequest                  │   │
│  │   - authorize(): true   │   - authorize(): true                   │   │
│  │   - rules(): validation │   - rules(): validation                 │   │
│  │   - messages(): custom  │   - messages(): custom                  │   │
│  └──────────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                           DOMAIN LAYER                                   │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │                      Expense Model                                │   │
│  │                                                                   │   │
│  │   Traits: HasFactory, SoftDeletes                                 │   │
│  │                                                                   │   │
│  │   Constants:                                                      │   │
│  │     CATEGORIES = ['Groceries', 'Transport', 'Housing and         │   │
│  │                   Utilities', 'Restaurants and Cafes',           │   │
│  │                   'Health and Medicine', 'Clothing & Footwear',  │   │
│  │                   'Entertainment']                                │   │
│  │                                                                   │   │
│  │   Fillable: description, amount, category, date                   │   │
│  │                                                                   │   │
│  │   Casts:                                                          │   │
│  │     amount → decimal:2                                            │   │
│  │     date   → date (Carbon)                                        │   │
│  │                                                                   │   │
│  │   Static Methods:                                                 │   │
│  │     validationRules()    : array                                  │   │
│  │     validationMessages() : array                                  │   │
│  └──────────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                        DATA ACCESS LAYER                                 │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │                    Eloquent ORM                                   │   │
│  │   - Query Builder with scopes                                     │   │
│  │   - Automatic timestamps (created_at, updated_at)                 │   │
│  │   - Soft deletes (deleted_at)                                     │   │
│  └──────────────────────────────────────────────────────────────────┘   │
│                                    │                                     │
│                                    ▼                                     │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │                    SQLite Database                                │   │
│  │   Table: expenses                                                 │   │
│  │     - id (PK)                                                     │   │
│  │     - description VARCHAR(255)                                    │   │
│  │     - amount DECIMAL(10,2)                                        │   │
│  │     - category VARCHAR                                            │   │
│  │     - date DATE                                                   │   │
│  │     - created_at, updated_at TIMESTAMP                            │   │
│  │     - deleted_at TIMESTAMP (soft delete)                          │   │
│  │                                                                   │   │
│  │   Indexes: date, category                                         │   │
│  └──────────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Core Components

### 3.1 Expense Model (`app/Models/Expense.php`)

**Purpose**: Central domain entity representing a financial expense

**Key Design Decisions**:
- Categories defined as `const CATEGORIES` - single source of truth
- Validation rules as static methods for reuse across Form Requests
- Soft deletes enabled - expenses are never permanently removed
- Amount cast as `decimal:2` for precision

```php
// Pattern: Centralized validation rules
public static function validationRules(): array
{
    return [
        'description' => ['required', 'string', 'max:255'],
        'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
        'category' => ['required', 'string', 'in:'.implode(',', self::CATEGORIES)],
        'date' => ['required', 'date', 'before_or_equal:today'],
    ];
}
```

### 3.2 ExpenseController (`app/Http/Controllers/ExpenseController.php`)

**Purpose**: Handle all HTTP requests for expense operations

**Key Patterns**:
- Private helper methods for code reuse (`getCategories()`, `applyCategoryFilter()`)
- Form Requests for validation injection
- Consistent redirect patterns with flash messages
- View data passed as associative arrays

**Controller Methods**:
| Method | Route | Purpose |
|--------|-------|---------|
| `index()` | GET /expenses | Paginated list (15/page) with filter |
| `daily()` | GET /expenses/daily | Date-specific view with category breakdown |
| `monthly()` | GET /expenses/monthly | Month aggregation with percentages |
| `create()` | GET /expenses/create | Show creation form |
| `store()` | POST /expenses | Create new expense |
| `show()` | GET /expenses/{id} | Display single expense |
| `edit()` | GET /expenses/{id}/edit | Show edit form |
| `update()` | PUT /expenses/{id} | Update expense |
| `destroy()` | DELETE /expenses/{id} | Soft delete expense |

### 3.3 Form Requests (`app/Http/Requests/`)

**Purpose**: Encapsulate validation logic separate from controller

- `StoreExpenseRequest` - Validates new expense creation
- `UpdateExpenseRequest` - Validates expense updates

**Pattern**: Both consume `Expense::CATEGORIES` for category validation

---

## 4. Data Architecture

### Database Schema

```sql
CREATE TABLE expenses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    category VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP  -- Soft delete marker
);

CREATE INDEX expenses_date_index ON expenses(date);
CREATE INDEX expenses_category_index ON expenses(category);
```

### Data Flow

```
User Input → Form Request Validation → Controller → Eloquent Model → SQLite
                                                          ↓
User View  ←  Blade Template         ←  Controller  ←  Query Results
```

### Query Patterns

**Pagination**: `Expense::paginate(15)->withQueryString()`

**Category Filtering**:
```php
if ($category && in_array($category, Expense::CATEGORIES)) {
    $query->where('category', $category);
}
```

**Date Range Queries**:
```php
// Daily
Expense::whereDate('date', $date)->get();

// Monthly
Expense::whereBetween('date', [$startOfMonth, $endOfMonth])->get();
```

---

## 5. Cross-Cutting Concerns

### Validation

- **Location**: `app/Http/Requests/` (Form Requests)
- **Rules Source**: `Expense::validationRules()` static method
- **Error Display**: Blade `@error` directive with Material UI styling
- **Client-Side**: HTML5 validation attributes (`required`, `max`, `min`)

### Error Handling

- Laravel default exception handling
- Flash messages via `session('success')` / `session('error')`
- Snackbar UI component for user notifications

### Security

- CSRF protection via `@csrf` in forms
- Mass assignment protection via `$fillable`
- Input validation on all user data

### Configuration

- Environment: `.env` file
- Database: SQLite by default (`database/database.sqlite`)
- No external service dependencies

---

## 6. Testing Architecture

### Test Structure

```
tests/
├── TestCase.php           # Base test class
├── Feature/
│   ├── ExampleTest.php
│   └── ExpenseControllerTest.php  # Main feature tests
└── Unit/
    └── Models/            # Unit tests for model logic
```

### Testing Patterns

**Database**: `RefreshDatabase` trait - clean state per test

**Factory States**:
```php
// Set specific category
Expense::factory()->category('Groceries')->create();

// Set date to today
Expense::factory()->today()->create();
```

**Assertions**:
```php
$response->assertStatus(200);
$response->assertViewIs('expenses.index');
$response->assertViewHas('expenses');
$response->assertSee($expense->description);
```

### Running Tests

```bash
cd laravel-app
php artisan test                     # All tests
php artisan test --filter=Expense    # Expense tests only
```

---

## 7. Deployment Architecture

### CI/CD Pipeline (`.github/workflows/laravel.yml`)

```yaml
Trigger: Push/PR to main branch

Jobs:
  1. tests:
     - PHP 8.4 with SQLite
     - composer install
     - php artisan test
     
  2. code-style:
     - Laravel Pint (PSR-12)
```

### Environment Setup

```bash
cd laravel-app
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed
php artisan serve
```

---

## 8. Extension Patterns

### Adding a New Expense Field

1. **Migration**: Add column to `expenses` table
2. **Model**: Add to `$fillable`, `$casts` if needed
3. **Validation**: Update `Expense::validationRules()`
4. **Form Requests**: Rules auto-inherited (or override)
5. **Views**: Update `_form.blade.php` partial
6. **Tests**: Add assertions for new field

### Adding a New Category

Update `Expense::CATEGORIES` constant only:
```php
public const CATEGORIES = [
    'Groceries',
    'Transport',
    // ... existing
    'New Category',  // Add here
];
```

### Adding a New View (e.g., Weekly)

1. **Route** (before `Route::resource()`):
   ```php
   Route::get('expenses/weekly', [ExpenseController::class, 'weekly'])->name('expenses.weekly');
   ```

2. **Controller Method**:
   ```php
   public function weekly(Request $request): View { ... }
   ```

3. **View**: Create `resources/views/expenses/weekly.blade.php`

4. **Navigation**: Update `layouts/app.blade.php`

---

## 9. Common Pitfalls

### Route Ordering
❌ **Wrong**: Custom routes after `Route::resource()`
✅ **Correct**: Custom routes defined BEFORE resource routes

### Category Validation
❌ **Wrong**: Hardcoding category list in validation
✅ **Correct**: Reference `Expense::CATEGORIES`

### Deletion
❌ **Wrong**: `$expense->forceDelete()`
✅ **Correct**: `$expense->delete()` (soft delete)

### Testing
❌ **Wrong**: Using production database in tests
✅ **Correct**: Using `RefreshDatabase` trait

---

## 10. Authentication System

### Architecture Pattern

**Session-based authentication** with custom middleware (no Eloquent User model)

```
┌─────────────────────────────────────────────────────────────┐
│                    Authentication Flow                       │
│                                                             │
│  User Login → AuthController → Validate Credentials        │
│       ↓                              ↓                      │
│  Rate Limiter Check         hash_equals() Check            │
│       ↓                              ↓                      │
│  Success: session(['authenticated' => true])               │
│  Failure: Rate limit + error message                        │
│                                                             │
│  Protected Routes → Authenticate Middleware                 │
│       ↓                              ↓                      │
│  Check session('authenticated')                             │
│       ↓                              ↓                      │
│  Authenticated: Continue      Unauthenticated: Redirect     │
└─────────────────────────────────────────────────────────────┘
```

### Key Components

#### AuthController (`app/Http/Controllers/AuthController.php`)

**Purpose**: Handle login/logout operations

**Methods**:
- `showLogin()` - Display login form
- `login()` - Validate credentials and set session
- `logout()` - Clear session and redirect

**Security Features**:
- Rate limiting (5 attempts per username, 10 per IP)
- Timing-safe comparison with `hash_equals()`
- Session regeneration after login
- CSRF protection

```php
public function login(Request $request): RedirectResponse
{
    // Validate input
    $validated = $request->validate([
        'username' => 'required|string',
        'password' => 'required|string',
    ]);

    // Rate limiting checks
    $userKey = "login-user:{$validated['username']}";
    $ipKey = "login-ip:{$request->ip()}";
    
    // Timing-safe credential validation
    $validUsername = hash_equals(
        env('AUTH_USERNAME', ''),
        $validated['username']
    );
    $validPassword = hash_equals(
        env('PASSWORD_HASH', ''),
        $validated['password']
    );
    
    if ($validUsername && $validPassword) {
        RateLimiter::clear($userKey);
        RateLimiter::clear($ipKey);
        $request->session()->regenerate();
        $request->session()->put('authenticated', true);
        return redirect()->intended('/expenses');
    }
    
    // Record failed attempt
    RateLimiter::hit($userKey, 15 * 60);
    RateLimiter::hit($ipKey, 15 * 60);
    
    return back()->withErrors([
        'username' => 'Invalid credentials.'
    ]);
}
```

#### Authenticate Middleware (`app/Http/Middleware/Authenticate.php`)

**Purpose**: Protect routes from unauthenticated access

**Implementation**:
```php
public function handle(Request $request, Closure $next): Response
{
    if ($request->session()->get('authenticated') !== true) {
        return redirect()->route('login');
    }
    
    return $next($request);
}
```

**Registration** (`bootstrap/app.php`):
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'auth.custom' => \App\Http\Middleware\Authenticate::class,
    ]);
})
```

#### Custom Blade Directive

**Registration** (`app/Providers/AppServiceProvider.php`):
```php
public function boot(): void
{
    Blade::if('auth', fn() => session('authenticated') === true);
}
```

**Usage in views**:
```blade
@auth
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
@endauth

@guest
    <a href="{{ route('login') }}">Login</a>
@endguest
```

### Environment Configuration

**Required variables** (`.env`):
```env
# Authentication credentials
AUTH_USERNAME=your.email@example.com
PASSWORD_HASH=$2y$12$your_bcrypt_hash_here

# For E2E tests only
TEST_PASSWORD=plain_text_password
```

**Generate password hash**:
```bash
php artisan tinker
>>> echo Hash::make('your-password');
```

### Route Protection

**Public routes** (no authentication):
```php
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
```

**Protected routes** (authentication required):
```php
Route::middleware(['auth.custom'])->group(function () {
    Route::resource('expenses', ExpenseController::class);
    Route::get('/expenses/daily', [ExpenseController::class, 'daily'])
        ->name('expenses.daily');
    Route::get('/expenses/monthly', [ExpenseController::class, 'monthly'])
        ->name('expenses.monthly');
});
```

### Testing Authentication

**Feature Tests** (`tests/Feature/Auth/AuthenticationTest.php`):
```php
test('user can login with valid credentials', function () {
    $response = $this->post('/login', [
        'username' => config('auth.custom.username'),
        'password' => env('TEST_PASSWORD'),
    ]);
    
    $response->assertRedirect('/expenses');
    expect(session('authenticated'))->toBeTrue();
});
```

**E2E Tests** - Uses test-only endpoint:
```typescript
export async function login(page: Page) {
  await page.goto('/test/auth');
  await page.evaluate(() => {
    sessionStorage.setItem('authenticated', 'true');
  });
  await page.goto('/expenses');
}
```

### Security Considerations

✅ **Timing-safe comparisons**: Use `hash_equals()` to prevent timing attacks  
✅ **Rate limiting**: Dual limits (username + IP) prevent brute force  
✅ **Session regeneration**: Prevents session fixation attacks  
✅ **CSRF protection**: Automatic Laravel token validation  
✅ **Environment variables**: Credentials never in source code  
✅ **Bcrypt hashing**: Industry-standard password hashing  

### Migration Path

If multi-user functionality is needed:
1. Create User model with Laravel Breeze/Fortify
2. Update middleware to use `Auth::check()`
3. Add user relationships to Expense model
4. Update tests to use `actingAs($user)`

Current implementation makes this migration straightforward - auth logic is isolated.

---

## 11. Deployment Configuration

### Fly.io Setup

**Dockerfile** (`Dockerfile`):
- Multi-stage build with PHP 8.4-fpm-alpine
- Composer dependencies installed
- SQLite extension enabled
- Proper file permissions for Laravel

**Fly.toml Configuration**:
```toml
app = "your-app-name"
primary_region = "fra"

[build]

[deploy]
  release_command = "php /var/www/html/artisan migrate --force"

[env]
  APP_ENV = "production"
  LOG_LEVEL = "info"

[http_service]
  internal_port = 8080
  force_https = true
  auto_stop_machines = "stop"
  auto_start_machines = true
  min_machines_running = 0
  processes = ["app"]

[mounts]
  source = "data"
  destination = "/var/www/html/database"
```

**Key Features**:
- **Persistent Volume**: SQLite database stored on persistent volume
- **Auto-scaling**: Machines start/stop based on traffic
- **HTTPS**: Automatic SSL certificates
- **Release Command**: Migrations run on every deploy

### Environment Secrets

Set via `flyctl secrets set`:
```bash
APP_KEY=base64:your-key-here
APP_ENV=production
APP_DEBUG=false
AUTH_USERNAME=your.email@example.com
PASSWORD_HASH='$2y$12$your_hash_here'
SESSION_DRIVER=database
```

### Database Persistence

**Volume Creation**:
```bash
flyctl volumes create data --region fra --size 1 -a your-app-name
```

**Mount Point**: `/var/www/html/database`  
**Database File**: `database.sqlite`  
**Migrations**: Run automatically on deploy via release command

### Deployment Workflow

1. **Initial Deploy**: `flyctl launch`
2. **Create Volume**: `flyctl volumes create data`
3. **Set Secrets**: `flyctl secrets set KEY=value`
4. **Deploy App**: `flyctl deploy`
5. **Monitor**: `flyctl logs`

---

## 12. File Quick Reference

| Purpose | File Path |
|---------|-----------|
| Domain Model | `app/Models/Expense.php` |
| Expense Controller | `app/Http/Controllers/ExpenseController.php` |
| Auth Controller | `app/Http/Controllers/AuthController.php` |
| Auth Middleware | `app/Http/Middleware/Authenticate.php` |
| Store Validation | `app/Http/Requests/StoreExpenseRequest.php` |
| Update Validation | `app/Http/Requests/UpdateExpenseRequest.php` |
| Routes | `routes/web.php` |
| Main Layout | `resources/views/layouts/app.blade.php` |
| Form Partial | `resources/views/expenses/_form.blade.php` |
| Migration | `database/migrations/2025_12_02_215238_create_expenses_table.php` |
| Factory | `database/factories/ExpenseFactory.php` |
| Seeder | `database/seeders/ExpenseSeeder.php` |
| Expense Feature Tests | `tests/Feature/ExpenseControllerTest.php` |
| Auth Feature Tests | `tests/Feature/Auth/AuthenticationTest.php` |
| CI/CD | `.github/workflows/laravel-quality-gates.yml` |
| Deployment | `Dockerfile`, `fly.toml` |
| CSS | `public/css/app.css` |

---

*This blueprint reflects the architecture as of December 30, 2025. Update when significant structural changes occur.*
