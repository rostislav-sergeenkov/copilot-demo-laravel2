# Architectural Testing

This project includes comprehensive architectural tests to ensure code quality and adherence to Laravel best practices.

## Overview

Architectural tests automatically verify that the codebase follows established patterns and conventions. They run as part of the CI/CD pipeline and can be executed locally during development.

## Running Tests

```bash
# Run only architectural tests
composer test:arch

# Run all tests (includes architectural)
composer test:all
```

## Test Categories

### 1. **Model Architecture**
- ✅ Models extend Eloquent Model
- ✅ Models have proper mass assignment protection (`$fillable` or `$guarded`)
- ✅ Models do not reference Controllers (models should be "dumb" data structures)
- ✅ Models do not use `request()` helper or Request facade
- ✅ All models have corresponding Factories

### 2. **Controller Architecture**
- ✅ Controllers have "Controller" suffix
- ✅ Controllers extend base Controller class
- ✅ Controllers do not call other Controllers (prevents spaghetti logic)
- ✅ Controller methods have explicit return types
- ✅ Controllers follow RESTful/resourceful naming conventions

### 3. **Form Request Architecture**
- ✅ Form Requests have "Request" suffix
- ✅ Form Requests extend FormRequest
- ✅ Form Requests implement `rules()` method

### 4. **Middleware Architecture**
- ✅ Middleware is in correct namespace
- ✅ Middleware implements `handle()` method

### 5. **Provider Architecture**
- ✅ Providers have "Provider" suffix
- ✅ Providers extend ServiceProvider

### 6. **Factory Architecture**
- ✅ Factories have "Factory" suffix
- ✅ Factories extend base Factory class

### 7. **View Architecture**
- ✅ Views do not contain database queries (prevents N+1 problems)
- ✅ Data should be passed from controllers, not queried in views

### 8. **Type Safety**
- ✅ All PHP files use `declare(strict_types=1);`
- ✅ All public controller methods have return type declarations

### 9. **Security Rules**
- ✅ No dangerous functions (`eval`, `exec`, `system`, `shell_exec`)
- ✅ No raw SQL queries (`DB::statement`, `DB::raw`, `DB::select`, etc.)
- ✅ No debug statements in production code (`dd`, `dump`, `var_dump`, `print_r`)
- ✅ No direct `env()` calls in application code (use `config()` instead)
- ✅ No `die()` or `exit()` statements (breaks Laravel lifecycle)

## Architecture Rules

### Naming Conventions

**Controllers**
```php
// ✅ Good
class ExpenseController extends Controller { }

// ❌ Bad
class Expense extends Controller { }
class ExpenseCtrl extends Controller { }
```

**Form Requests**
```php
// ✅ Good
class StoreExpenseRequest extends FormRequest { }

// ❌ Bad
class StoreExpense extends FormRequest { }
class ExpenseStoreRequest extends FormRequest { }
```

**Factories**
```php
// ✅ Good
class ExpenseFactory extends Factory { }

// ❌ Bad
class Expense extends Factory { }
```

### Layer Separation

**Models Should Not Know About Controllers**
```php
// ❌ Bad - Model referencing Controller
namespace App\Models;
use App\Http\Controllers\ExpenseController;

class Expense extends Model {
    public function doSomething() {
        return ExpenseController::someMethod();
    }
}

// ✅ Good - Models are dumb data structures
namespace App\Models;

class Expense extends Model {
    protected $fillable = ['description', 'amount'];
}
```

**Controllers Should Not Call Other Controllers**
```php
// ❌ Bad - Controller calling another Controller
class ExpenseController extends Controller {
    public function __construct(
        private AuthController $authController
    ) {}
}

// ✅ Good - Use Services/Actions instead
class ExpenseController extends Controller {
    public function __construct(
        private ExpenseService $expenseService
    ) {}
}
```

**Views Should Not Query Database**
```php
<!-- ❌ Bad - Query in view -->
@foreach(App\Models\Expense::where('amount', '>', 100)->get() as $expense)
    {{ $expense->description }}
@endforeach

<!-- ✅ Good - Data passed from controller -->
@foreach($expenses as $expense)
    {{ $expense->description }}
@endforeach
```

**Environment Configuration**
```php
// ✅ Good - Use config() in application code
$username = config('auth.custom.username');

// ❌ Bad - Direct env() calls
$username = env('AUTH_USERNAME');

// ✅ Allowed - env() in config files and specific middleware
// config/auth.php or App\Http\Middleware\Authenticate.php
$username = env('AUTH_USERNAME');
```

**Mass Assignment Protection**
```php
// ✅ Good - Explicit fillable
class Expense extends Model {
    protected $fillable = ['description', 'amount', 'category', 'date'];
}

// ✅ Good - Guarded
class Expense extends Model {
    protected $guarded = ['id'];
}

// ❌ Bad - No protection
class Expense extends Model {
    // Missing $fillable or $guarded
}
```

### Type Safety

**Strict Types Declaration**
```php
// ✅ Good - Every file has strict types
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class ExpenseController extends Controller { }
```

**Return Types**
```php
// ✅ Good - Explicit return types
class ExpenseController extends Controller {
    public function index(): View { }
    public function store(Request $request): RedirectResponse { }
}

// ❌ Bad - Missing return types
class ExpenseController extends Controller {
    public function index() { }  // What does this return?
}
```

### Security

**No Raw SQL**
```php
// ❌ Bad - SQL injection risk
DB::statement("DELETE FROM expenses WHERE id = " . $id);
DB::raw("SELECT * FROM users WHERE email = '$email'");

// ✅ Good - Use Eloquent or Query Builder
Expense::destroy($id);
User::where('email', $email)->first();
```

**Dangerous Functions**
```php
// ❌ Never use these
eval($code);
exec($command);
system($command);
shell_exec($command);
```

**Process Lifecycle**
```php
// ❌ Bad - Kills PHP process
if ($error) {
    die('Error occurred');
}
exit(1);

// ✅ Good - Throw exceptions or return responses
if ($error) {
    throw new \Exception('Error occurred');
}
return response()->json(['error' => 'Failed'], 500);
```

**Debug Statements**
```php
// ❌ Never commit these
dd($data);
dump($data);
var_dump($data);
print_r($data);

// ✅ Use logging instead
Log::debug('Data', ['data' => $data]);
```

### RESTful Controller Methods

**Standard Resource Methods**
```php
// ✅ Good - RESTful methods
class ExpenseController extends Controller {
    public function index(): View { }
    public function create(): View { }
    public function store(Request $request): RedirectResponse { }
    public function show(Expense $expense): View { }
    public function edit(Expense $expense): View { }
    public function update(Request $request, Expense $expense): RedirectResponse { }
    public function destroy(Expense $expense): RedirectResponse { }
    
    // Custom methods are ok if descriptive
    public function daily(Request $request): View { }
    public function monthly(Request $request): View { }
}

// ❌ Bad - Non-standard naming
class ExpenseController extends Controller {
    public function get_all_expenses() { }  // Use index()
    public function getData() { }           // Use index()
    public function do_something() { }      // Unclear intent
    public function process_expense() { }   // Use store() or update()
}
```

**Model Request Isolation**
```php
// ❌ Bad - Model knows about HTTP request
class Expense extends Model {
    public function getCurrentUserExpenses() {
        return $this->where('user_id', request()->user()->id)->get();
    }
}

// ✅ Good - Pass data explicitly
class Expense extends Model {
    public function scopeForUser($query, int $userId) {
        return $query->where('user_id', $userId);
    }
}

// In controller
$expenses = Expense::forUser($request->user()->id)->get();
```

**Factory Requirement**
```php
// Every model must have a factory
// app/Models/Expense.php
class Expense extends Model {
    use HasFactory;
}

// database/factories/ExpenseFactory.php
class ExpenseFactory extends Factory {
    public function definition(): array {
        return [
            'description' => fake()->sentence(),
            'amount' => fake()->randomFloat(2, 1, 1000),
        ];
    }
}

// ❌ Bad - No factory, manual seeding
DB::table('expenses')->insert([
    'description' => 'Test',
    'amount' => 100,
]);

// ✅ Good - Use factory
Expense::factory()->count(10)->create();
```

## Adding New Rules

To add new architectural tests:

1. Open `tests/Architecture/ArchitectureTest.php`
2. Add a new test method with `#[Test]` attribute
3. Use reflection or file inspection to verify your rule
4. Run `composer test:arch` to verify

Example:
```php
#[Test]
public function repositories_have_repository_suffix(): void
{
    $reposDir = app_path('Repositories');
    $classes = $this->getClassesFromDirectory($reposDir, 'App\\Repositories\\');

    foreach ($classes as $class) {
        $this->assertStringEndsWith(
            'Repository',
            $class,
            "Repository {$class} should end with 'Repository' suffix"
        );
    }
}
```

## CI/CD Integration

Architectural tests run automatically in GitHub Actions as part of the `test:all` script:

```yaml
- name: Run tests
  run: composer test:all
```

Pull requests are blocked if architectural tests fail.

## Benefits

- **Consistency**: Enforces naming conventions across the codebase
- **Security**: Prevents dangerous patterns from being committed
- **AI-Proof**: Catches common AI coding mistakes (N+1 queries, raw SQL, etc.)
- **Type Safety**: Ensures strict typing throughout the application
- **Maintainability**: Ensures code follows established patterns
- **Documentation**: Tests serve as executable documentation of architecture decisions
- **Automation**: Catches violations early in development

## Troubleshooting

### Test Fails: "Must declare strict types"

Add `declare(strict_types=1);` after the opening PHP tag:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;
```

### Test Fails: "Controller method must declare a return type"

Add explicit return types to all public methods:

```php
// ❌ Before
public function index()
{
    return view('expenses.index');
}

// ✅ After
public function index(): View
{
    return view('expenses.index');
}
```

### Test Fails: "Should not use raw SQL"

Replace raw SQL with Eloquent or Query Builder:

```bash
# Search for raw SQL usage
grep -r "DB::raw" app/
grep -r "DB::statement" app/
```

### Test Fails: "Model should not use request() helper"

Pass data explicitly from controllers:

```php
// ❌ In Model
public function getForCurrentUser() {
    return $this->where('user_id', request()->user()->id);
}

// ✅ In Controller
public function index(Request $request) {
    $expenses = Expense::where('user_id', $request->user()->id)->get();
}
```

### Test Fails: "View contains database queries"

Move all queries to controllers:

```php
// ❌ In view
@foreach(Expense::where('amount', '>', 100)->get() as $expense)

// ✅ In controller
$expenses = Expense::where('amount', '>', 100)->get();
return view('expenses.index', compact('expenses'));
```

## Related Documentation

- [Laravel Architecture Concepts](https://laravel.com/docs/architecture-concepts)
- [Testing in Laravel](https://laravel.com/docs/testing)
- [Project Architecture Blueprint](../docs/Project_Architecture_Blueprint.md)
