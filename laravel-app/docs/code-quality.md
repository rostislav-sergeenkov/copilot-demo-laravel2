# Code Quality & Static Analysis

This document describes the linter and static analysis tools configured for this Laravel project.

## Tools Overview

### 1. Laravel Pint (Code Style)

Laravel Pint is an opinionated PHP code style fixer built on PHP-CS-Fixer. It ensures consistent code formatting across the entire codebase.

**Configuration:** `pint.json`

**Usage:**
```bash
# Fix code style issues automatically
composer lint

# Check for code style issues (without fixing)
composer lint:test
```

**Features:**
- PSR-12 coding standards
- Laravel-specific conventions
- Short array syntax
- Single quote strings
- Proper spacing and indentation
- Automatic import ordering

### 2. Larastan (Static Analysis)

Larastan is a wrapper around PHPStan specifically designed for Laravel applications. It performs static code analysis to detect bugs, type errors, and potential issues without running the code.

**Configuration:** `phpstan.neon`

**Usage:**
```bash
# Run static analysis
composer analyze

# Generate baseline (for gradual adoption)
composer analyze:baseline
```

**Current Configuration:**
- **Level:** 5 (out of 10, gradually increase as you fix issues)
- **Paths analyzed:** `app/`, `config/`, `database/`, `routes/`, `tests/`
- **Excluded:** `vendor/`, `storage/`, `bootstrap/cache/`
- **Laravel-specific checks:** Model properties, unnecessary collection calls
- **Baseline:** `phpstan-baseline.neon` (allows gradual adoption)

### 3. Configuration Files

#### `pint.json`
Defines code style rules and preferences. Based on Laravel preset with custom overrides for:
- Array syntax
- Binary operator spacing
- Concatenation spacing
- Method argument spacing
- Import ordering

#### `phpstan.neon`
Defines static analysis rules and settings:
- Analysis level (currently 5)
- Paths to analyze
- Paths to exclude
- Laravel-specific checks
- Error ignoring patterns
- Baseline inclusion

#### `phpstan-baseline.neon`
Auto-generated file containing existing errors to ignore. This allows gradual adoption of stricter analysis without breaking the build. Re-generate after fixing errors to track progress.

## CI/CD Integration

Both tools run automatically on every pull request via GitHub Actions:

### Code Style Check (`.github/workflows/laravel.yml`)
```yaml
- name: Run Laravel Pint
  run: ./vendor/bin/pint --test
```

### Static Analysis (`.github/workflows/laravel.yml`)
```yaml
- name: Run PHPStan
  run: ./vendor/bin/phpstan analyse --memory-limit=2G --no-progress
```

**Pull requests will be blocked if:**
- Code style violations are detected
- PHPStan finds errors not in the baseline

## Best Practices

### Before Committing
1. Run `composer lint` to fix code style issues
2. Run `composer analyze` to check for type errors
3. Fix any errors found or add to baseline if necessary

### Gradual Improvement
1. Start with current baseline
2. Fix errors gradually
3. Re-generate baseline after fixes: `composer analyze:baseline`
4. Increase analysis level when comfortable (edit `phpstan.neon`)

### Common PHPStan Issues and Fixes

**Issue:** Property type mismatch
```php
// Before
/** @var array */
protected $fillable;

// After
/** @var array<int, string> */
protected $fillable;
```

**Issue:** Return type incompatibility
```php
// Before
public function definition()
{
    return [...];
}

// After
public function definition(): array
{
    return [...];
}
```

**Issue:** Undefined property access
```php
// Before
$expense->total;

// After - define accessor or use array
$expense->getAttribute('total');
```

## Updating Analysis Level

As code quality improves, increase the analysis level in `phpstan.neon`:

```yaml
parameters:
    level: 6  # Increase from 5 to 6, then gradually to 8
```

Re-run analysis and fix new errors, or update baseline.

## Resources

- [Laravel Pint Documentation](https://laravel.com/docs/pint)
- [Larastan Documentation](https://github.com/larastan/larastan)
- [PHPStan Documentation](https://phpstan.org/)
- [PSR-12 Coding Standards](https://www.php-fig.org/psr/psr-12/)
