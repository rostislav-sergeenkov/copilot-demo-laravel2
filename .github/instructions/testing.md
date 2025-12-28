---
description: Testing strategy and guidelines for the Laravel Expense Tracker application
applyTo: laravel-app/tests/**
---

# Testing Guidelines for Expense Tracker

Comprehensive testing strategy and guidelines for the Laravel Expense Tracker application.

## Testing Strategy

**Three-tier testing pyramid** ensures comprehensive coverage with fast feedback:

### 1. Unit Tests (~4s)
- **Location**: `tests/Unit/`
- **Coverage**: Models, Form Requests, Helpers
- **Count**: ~120 tests
- **Command**: `php artisan test --testsuite=Unit`
- **Purpose**: Test individual classes in isolation
- **Pattern**: Use `RefreshDatabase` trait, test one method per test

**Key areas**:
- `ExpenseTest.php` - Model behavior, scopes, relationships (74 tests)
- `StoreExpenseRequestTest.php`, `UpdateExpenseRequestTest.php` - Validation rules (35 tests)
- `FormatHelperTest.php` - Utility functions (11 tests)

### 2. Feature Tests (~4s)
- **Location**: `tests/Feature/`
- **Coverage**: Controllers, Database, Integration
- **Count**: ~80 tests
- **Command**: `php artisan test --testsuite=Feature`
- **Purpose**: Test HTTP requests and database interactions
- **Pattern**: Use `RefreshDatabase`, test through HTTP layer

**Key areas**:
- `ExpenseControllerTest.php` - CRUD operations (36 tests)
- `ValidationTest.php` - Form validation (22 tests)
- `DatabaseTest.php` - Data integrity (22 tests)
- `Auth/AuthenticationTest.php` - Login/logout flows

### 3. E2E Tests (Default: ~3 min, Full: ~20 min)
- **Location**: `tests/e2e/`
- **Technology**: Playwright (Node.js)
- **Strategy**: **Happy Path by default**, comprehensive on-demand
- **Commands**:
  - `npm run test:e2e` - 16 happy path tests (~3 min) ✅ **Default**
  - `npm run test:e2e:all` - 80+ comprehensive tests (~20 min)
  - `npm run test:e2e:ui` - Interactive debugging mode

**Happy Path tests** (`happy-path.spec.ts`):
- ✅ Basic CRUD operations (7 tests)
- ✅ Daily view functionality (3 tests)
- ✅ Monthly view functionality (3 tests)
- ✅ Category filtering (2 tests)
- ✅ Navigation flows (1 test)

**Comprehensive tests** (on-demand):
- `crud.spec.ts` - Detailed CRUD (25 tests)
- `daily-view.spec.ts` - Daily view edge cases (12 tests)
- `monthly-view.spec.ts` - Monthly view edge cases (13 tests)
- `filtering.spec.ts` - Filter persistence (15 tests)
- `validation.spec.ts` - Form validation (25 tests)
- `ui-accessibility.spec.ts` - UI/A11y compliance (30+ tests)

### 4. Architecture Tests
- **Location**: `tests/Architecture/ArchitectureTest.php`
- **Purpose**: Enforce architectural rules and code standards
- **Command**: `php artisan test --testsuite=Architecture`
- **Runs with**: All PHPUnit tests

**Enforced rules**:
- Form requests extend FormRequest base class
- Middleware has handle() method
- Models have proper fillable/guarded
- No debug statements (dd, dump, var_dump)
- Controllers follow resourceful naming
- No circular dependencies
- Strict types enabled in PHP files

---

## Testing Workflow

### During Development

Run fast unit and feature tests frequently:

```bash
cd laravel-app
php artisan test           # Unit + Feature + Architecture (~8s)
```

### Before Commit/PR

Run all backend tests plus E2E happy path:

```bash
cd laravel-app
php artisan test           # All Laravel tests (~8s)
npm run test:e2e           # Happy path E2E (~3 min)
```

### Before Production Deploy

Run comprehensive test suite:

```bash
cd laravel-app
php artisan test           # All Laravel tests
npm run test:e2e:all       # All E2E tests (~20 min)
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
npm run test:e2e:ui        # Interactive mode with time travel
npm run test:e2e:headed    # See browser in action
npm run test:e2e:debug     # Step-through debugging
```

---

## Test Writing Guidelines

### Unit Tests

**Pattern**: Test one method per test, isolate dependencies

```php
test('can create expense with valid data', function () {
    $expense = Expense::factory()->create([
        'description' => 'Test expense',
        'amount' => 50.00,
    ]);
    
    expect($expense->description)->toBe('Test expense');
    expect($expense->amount)->toBe('50.00');
});
```

**Best practices**:
- ✅ One assertion per test (or closely related assertions)
- ✅ Use descriptive test names: `test_can_create_expense_with_valid_data()`
- ✅ Mock external dependencies
- ✅ Test edge cases and error conditions
- ✅ Use factories for test data
- ❌ Don't test framework functionality
- ❌ Don't make database calls in pure unit tests

### Feature Tests

**Pattern**: Test complete user workflows through HTTP

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
- ✅ Use `$this->actingAs()` for authenticated requests
- ✅ Assert HTTP status codes, redirects, and session data
- ✅ Assert database changes with `assertDatabaseHas()`/`assertDatabaseMissing()`
- ✅ Test both success and failure scenarios
- ❌ Don't test implementation details
- ❌ Don't duplicate unit test coverage

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

## CI/CD Integration

GitHub Actions workflow runs on PRs to `main`:

```yaml
# .github/workflows/laravel.yml
- php artisan test              # All PHPUnit tests
- php artisan pint --test       # Code style check
```

**PR requirements**:
- ✅ All tests must pass
- ✅ Code style must be PSR-12 compliant
- ❌ PRs blocked if tests fail

---

## Test Coverage Goals

- **Unit Tests**: 100% of model methods, validation rules
- **Feature Tests**: 100% of controller actions, critical workflows
- **E2E Tests**: 100% of user-facing features (happy path by default)
- **Architecture Tests**: Key architectural patterns enforced

---

## References

- **Testing Overview**: `docs/testing-overview.md` - Complete testing guide with acceptance criteria mapping
- **E2E Testing Guide**: `docs/e2e-testing-guide.md` - Detailed Playwright setup and usage
- **Unit Tests Summary**: `docs/unit-tests-summary.md` - Coverage details
- **Feature Tests Summary**: `docs/feature-tests-summary.md` - Feature test coverage
