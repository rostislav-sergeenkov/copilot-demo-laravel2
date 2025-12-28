---
description: Testing strategy and guidelines for the Laravel Expense Tracker application
applyTo: laravel-app/tests/**
---

# Testing Guidelines for Expense Tracker

Comprehensive testing strategy using the **Testing Trophy** approach for integration-heavy Laravel applications.

## Testing Strategy: Testing Trophy

**Testing Trophy approach** optimizes test distribution for integration-heavy web applications:

```
                    /\
                   /  \
                  / E2E \      Browser-based user flows
                 /______\
                /        \
               / Integration \  LARGEST LAYER ← Sweet spot
              /______________\
             /                \
            /      Unit        \  Pure business logic
           /____________________\
          /                      \
         /   Static Analysis     \  Architecture, PHPStan, Pint
        /__________________________\
```

**Why Trophy over Pyramid?**
- ✅ Integration tests provide the most confidence for web applications
- ✅ Tests real workflows with actual database and HTTP layer
- ✅ Catches issues that isolated unit tests miss
- ✅ Better cost/benefit ratio for integration-heavy apps like Laravel

---

## Testing Layers

### 1. Static Analysis (Base Layer)

**Tools**: PHPStan, Laravel Pint, Pest Architecture  
**Commands**:
  - `composer analyze` - Run PHPStan static analysis (level 8)
  - `composer lint` - Fix code style with Pint
  - `composer lint:test` - Check code style without fixing
  - `composer test:arch` - Run architecture tests  
**Purpose**: Enforce code standards and architectural rules

**What to enforce**:
- Naming conventions (Controller, Request, Provider suffixes)
- Inheritance rules (extend base classes)
- No debug statements (dd, dump, var_dump)
- Strict types enabled
- Return types on public methods

---

### 2. Unit Tests (Small Layer)

**Location**: `tests/Unit/`  
**Commands**:
  - `php artisan test --testsuite=Unit` - Run unit tests
  - `composer test:unit:coverage` - Run with coverage report (min 70%)  
**Purpose**: Test pure business logic in isolation  
**Pattern**: NO database, NO HTTP, NO external services

**What belongs in Unit tests:**
- ✅ Pure business logic calculations
- ✅ Validation rule structures
- ✅ Edge cases in pure functions
- ✅ Constants and business rules
- ✅ Helper and utility functions
- ❌ Database operations
- ❌ HTTP requests
- ❌ Factory behavior
- ❌ Framework features

**Key areas**:
- Business logic in models (calculations, formatting)
- Validation rule structures in Form Requests
- Pure utility functions and helpers
- Edge cases in algorithms

---

### 3. Integration Tests (LARGEST Layer)

**Location**: `tests/Feature/`  
**Commands**:
  - `php artisan test --testsuite=Feature` - Run feature tests
  - `composer test:feature:coverage` - Run with coverage report (min 60%)  
**Purpose**: Test complete workflows with real dependencies  
**Pattern**: Use `RefreshDatabase`, test through HTTP or database

**What belongs in Integration tests:**
- ✅ CRUD workflows
- ✅ Database operations (create, update, delete, queries)
- ✅ HTTP requests and responses
- ✅ Validation with real requests
- ✅ Factory data generation
- ✅ Boundary conditions with database
- ✅ Aggregations and calculations with DB
- ✅ Authentication flows
- ❌ Testing framework behavior (trust Laravel)

**Key areas**:
- Complete user workflows (CRUD operations)
- Database queries, scopes, relationships
- HTTP layer and controller actions
- Form validation via real requests
- Authentication and authorization
- Multi-component interactions

---

### 4. E2E Tests (Small Layer)

**Location**: `tests/e2e/`  
**Technology**: Playwright (Node.js)  
**Strategy**: **Happy Path by default**, comprehensive on-demand  
**Commands**:
  - `composer test:e2e` - Happy path tests ✅ **Default**
  - `npm run test:e2e` - Same as composer command
  - `npm run test:e2e:all` - Comprehensive tests (pre-deploy)
  - `composer test:e2e:ui` - Interactive debugging mode
  - `npm run test:e2e:ui` - Same as composer command

**What belongs in E2E tests:**
- ✅ Critical business flows
- ✅ Multi-step user journeys
- ✅ Real browser interactions
- ✅ JavaScript-heavy features
- ❌ Validation errors (use Integration tests)
- ❌ Database edge cases (use Integration tests)
- ❌ API responses (use Integration tests)

**Happy Path strategy**:
- Focus on successful user workflows only
- Cover core business operations
- Run by default in CI/CD for fast feedback
- Comprehensive tests run before production deploys

---

## Composer Test Commands

**All-in-one commands** for running complete test suites:

```bash
cd laravel-app

# Run complete test suite (recommended before commits)
composer test:all

# Individual test suites
composer lint              # Fix code style issues
composer lint:test         # Check code style (no fixes)
composer analyze           # PHPStan static analysis
composer test:arch         # Architecture tests
composer test:unit:coverage    # Unit tests with coverage (min 70%)
composer test:feature:coverage # Feature tests with coverage (min 60%)
composer test:e2e          # E2E tests (happy path)
composer test:e2e:ui       # E2E tests (interactive mode)
```

**What `composer test:all` runs**:
1. `composer lint:test` - Code style validation
2. `composer analyze` - PHPStan static analysis (level 8)
3. `composer test:arch` - Architecture tests
4. `composer test:unit:coverage` - Unit tests with 70% minimum coverage
5. `composer test:feature:coverage` - Feature tests with 60% minimum coverage
6. `composer test:e2e` - E2E happy path tests

---

## Testing Workflow

### During Development

Run fast unit and feature tests frequently:

```bash
cd laravel-app
php artisan test           # Unit + Feature + Architecture
```

### Before Commit/PR

Run complete test suite with composer:

```bash
cd laravel-app
composer test:all          # Complete test suite (recommended)

# Or run manually:
composer lint:test         # Code style check
composer analyze           # Static analysis
php artisan test           # All Laravel tests
composer test:e2e          # Happy path E2E
```

### Before Production Deploy

Run comprehensive test suite:

```bash
cd laravel-app
composer test:all          # Complete test suite
npm run test:e2e:all       # Comprehensive E2E tests (beyond happy path)
```

### Debug Failing Tests

**PHPUnit debugging**:
```bash
php artisan test --filter=ExpenseTest       # Run specific test class
php artisan test --filter=test_can_create  # Run specific test method
php artisan test --stop-on-failure         # Stop at first failure
```

**E2E debugging**:
```bash
composer test:e2e:ui       # Interactive mode with time travel
npm run test:e2e:ui        # Same as above
npm run test:e2e:headed    # See browser in action
npm run test:e2e:debug     # Step-through debugging
```

---

## Test Writing Guidelines

### Unit Tests

**Pattern**: Test pure business logic without external dependencies

```php
test('percentages sum to 100 percent', function () {
    $total = 100.00;
    $breakdown = [
        'Groceries' => 30.00,
        'Transport' => 45.00,
        'Entertainment' => 25.00,
    ];

    $percentageSum = 0;
    foreach ($breakdown as $amount) {
        $percentageSum += ($amount / $total) * 100;
    }

    expect(round($percentageSum, 2))->toBe(100.0);
});
```

**Best practices**:
- ✅ One assertion per test (or closely related assertions)
- ✅ Use descriptive test names: `test_can_create_expense_with_valid_data()`
- ✅ Test edge cases and error conditions in pure functions
- ✅ NO database operations
- ✅ NO HTTP requests
- ✅ NO factory usage
- ❌ Don't test framework functionality
- ❌ Don't make database calls in unit tests

**Anti-patterns**:
```php
// ❌ DON'T test framework behavior
test('model has correct fillable', function () {
    expect((new Expense)->getFillable())->toBe([...]);
});

// ❌ DON'T test database in unit tests
test('can create expense', function () {
    $expense = Expense::create([...]); // Database call!
});

// ❌ DON'T test simple properties
test('can set description', function () {
    $expense = new Expense();
    $expense->description = 'test';
    expect($expense->description)->toBe('test'); // Trivial
});
```

---

### Integration Tests (Feature Tests)

**Pattern**: Test complete workflows with real database and HTTP

```php
test('can create expense through form submission', function () {
    $response = $this->post('/expenses', [
        'description' => 'Groceries',
        'amount' => 45.99,
        'category' => 'Groceries',
        'date' => now()->toDateString(),
    ]);
    
    $response->assertRedirect('/expenses');
    $this->assertDatabaseHas('expenses', [
        'description' => 'Groceries',
        'amount' => 45.99,
    ]);
});
```

**Best practices**:
- ✅ Test complete user workflows
- ✅ Use `RefreshDatabase` trait
- ✅ Use `$this->actingAs()` for authenticated requests
- ✅ Assert HTTP status codes, redirects, and session data
- ✅ Assert database changes with `assertDatabaseHas()`/`assertDatabaseMissing()`
- ✅ Test both success and failure scenarios
- ✅ Use factories for test data
- ✅ Test with real dependencies (database, HTTP)
- ❌ Don't test implementation details
- ❌ Don't test framework behavior (trust Laravel)
- ❌ Don't mock database or Eloquent

**Example - Complete workflow test**:
```php
test('filtering by category works with pagination', function () {
    // Setup real database state
    Expense::factory()->count(15)->create(['category' => 'Groceries']);
    Expense::factory()->count(10)->create(['category' => 'Transport']);
    
    // Make real HTTP request
    $response = $this->get('/expenses?category=Groceries');
    
    // Assert complete workflow
    $response->assertOk();
    $response->assertViewHas('expenses', function ($expenses) {
        return $expenses->count() === 10
            && $expenses->every(fn($e) => $e->category === 'Groceries');
    });
});
```

---

### E2E Tests

**Pattern**: Test from user's perspective in real browser

```typescript
test('should create a new expense successfully', async ({ page }) => {
  await page.goto('/expenses/create');
  await page.fill('input[name="description"]', 'Coffee');
  await page.fill('input[name="amount"]', '4.50');
  await page.selectOption('select[name="category"]', 'Restaurants and Cafes');
  await page.fill('input[name="date"]', getTodayString());
  await page.click('button[type="submit"]');
  
  await expect(page).toHaveURL('/expenses');
  await expectSuccessMessage(page, 'Expense created successfully');
});
```

**When to add to Happy Path** (`happy-path.spec.ts`):
- ✅ Tests core business workflow (successful scenarios only)
- ✅ Represents common user behavior
- ✅ Critical for business operations
- ✅ Tests happy path without edge cases

**When to create separate spec file**:
- ❌ Tests edge cases or error scenarios
- ❌ Validates UI details or styling
- ❌ Tests accessibility features
- ❌ Checks validation error messages
- ❌ Tests uncommon user flows

**Best practices**:
- ✅ Use helper functions from `helpers.ts`
- ✅ Test user-visible behavior, not implementation
- ✅ Use semantic selectors (roles, labels) over CSS selectors
- ✅ Clean up test data if needed
- ✅ Use `page.waitForSelector()` for dynamic content
- ❌ Don't test already covered by Unit/Feature tests
- ❌ Don't make tests dependent on each other

---

### Architecture Tests

**Pattern**: Enforce architectural rules

```php
test('form requests extend form request')
    ->expect('App\Http\Requests')
    ->toExtend('Illuminate\Foundation\Http\FormRequest');

test('no debug statements in application code')
    ->expect(['dd', 'dump', 'var_dump'])
    ->not->toBeUsed();
```

**Best practices**:
- ✅ Add rules for new architectural patterns
- ✅ Use Pest's `arch()` helper
- ✅ Document why each rule exists
- ✅ Keep rules aligned with project conventions
- ❌ Don't create rules that conflict with Laravel conventions

---

## Migration Guide: From Pyramid to Trophy

### Identifying Tests to Refactor

**Unit tests that should move to Integration**:
- Tests using `factory()->create()` - Database calls
- Tests with `assertDatabaseHas()` - Database assertions
- Tests of Eloquent relationships - Framework behavior
- Tests of database scopes - Framework behavior
- Tests of accessors/mutators - Framework behavior

**Unit tests that should be removed**:
- Tests of framework configuration (fillable, casts)
- Tests of simple getters/setters
- Tests duplicating framework functionality

**Unit tests to keep**:
- Pure calculation logic
- Business rule validation
- Edge case handling in pure functions
- Helper and utility functions

### Refactoring Process

**Step 1: Audit existing tests**
```bash
# Find database calls in unit tests
grep -r "factory()->create" tests/Unit/
grep -r "assertDatabaseHas" tests/Unit/
```

**Step 2: Move database tests to Integration**
```php
// Before (Unit test - WRONG)
test('can create expense', function () {
    $expense = Expense::factory()->create(['amount' => 50.00]);
    expect($expense->amount)->toBe('50.00');
});

// After (Integration test - CORRECT)
test('can create expense with valid data', function () {
    $response = $this->post('/expenses', [
        'description' => 'Test',
        'amount' => 50.00,
        'category' => 'Groceries',
        'date' => today()->toDateString(),
    ]);
    
    $response->assertRedirect('/expenses');
    $this->assertDatabaseHas('expenses', ['amount' => 50.00]);
});
```

**Step 3: Refactor unit tests to pure logic**
```php
// Keep only pure business logic
test('calculates category percentage correctly', function () {
    $total = 100.00;
    $amount = 25.00;
    
    expect(Expense::calculateCategoryPercentage($amount, $total))
        ->toBe(25.0);
});
```

**Step 4: Expand integration test coverage**
- Add workflow tests (multi-step operations)
- Test component interactions
- Focus on realistic user scenarios

---

## CI/CD Integration

GitHub Actions workflow runs on PRs to `main`:

```yaml
# .github/workflows/laravel-quality-gates.yml
- composer test:all             # Complete test suite
  # This runs:
  # - composer lint:test          (Code style check)
  # - composer analyze            (PHPStan level 8)
  # - composer test:arch          (Architecture tests)
  # - composer test:unit:coverage (Unit tests, min 70%)
  # - composer test:feature:coverage (Feature tests, min 60%)
  # - composer test:e2e           (E2E happy path)
```

**PR requirements**:
- ✅ All tests must pass
- ✅ Code style must be PSR-12 compliant (Pint)
- ✅ Static analysis must pass (PHPStan level 8)
- ✅ Minimum coverage: 70% (unit), 60% (feature)
- ❌ PRs blocked if tests fail

---

## Test Coverage Philosophy

**Focus on confidence, not metrics**:
- Integration tests provide the most confidence for web apps
- Cover critical user workflows completely
- Test edge cases in the appropriate layer
- Avoid testing framework functionality

**Coverage goals by layer**:
- **Static Analysis**: All code passes architectural rules
- **Unit Tests**: 100% of pure business logic
- **Integration Tests**: 100% of user workflows and controller actions
- **E2E Tests**: 100% of critical user journeys (happy path)

---

## References

- **Testing Overview**: `docs/testing-overview.md` - Complete testing guide with acceptance criteria mapping
- **E2E Testing Guide**: `docs/e2e-testing-guide.md` - Detailed Playwright setup and usage
- **Unit Tests Summary**: `docs/unit-tests-summary.md` - Coverage details
- **Feature Tests Summary**: `docs/feature-tests-summary.md` - Feature test coverage
- **Testing Trophy**: https://kentcdodds.com/blog/the-testing-trophy-and-testing-classifications
