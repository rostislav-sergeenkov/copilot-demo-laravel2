# Quickstart: User Authentication Implementation

**Feature**: [002-user-auth](spec.md)  
**For**: Developers implementing the authentication system  
**Estimated Time**: 2-3 hours  
**Prerequisites**: Existing Laravel 11 expense tracker application running

---

## Overview

This guide walks through implementing session-based authentication with credentials from environment variables. The system protects all expense routes behind a login wall while maintaining the existing expense functionality unchanged.

---

## Step 1: Environment Configuration (5 minutes)

### 1.1 Add Credentials to .env

**File**: `laravel-app/.env`

```bash
# Add these lines
USERNAME=admin
PASSWORD=your_secure_password_here
```

### 1.2 Create Test Environment File

**File**: `laravel-app/.env.testing`

```bash
USERNAME=testuser
PASSWORD=testpass
SESSION_DRIVER=array
CACHE_DRIVER=array
```

### 1.3 Validate Environment on Boot

**File**: `laravel-app/app/Providers/AppServiceProvider.php`

```php
public function boot(): void
{
    // Validate required authentication environment variables
    if (empty(env('USERNAME')) || empty(env('PASSWORD'))) {
        throw new \RuntimeException(
            'USERNAME and PASSWORD environment variables are required. ' .
            'Configure them in .env file.'
        );
    }
}
```

**Test**:
```bash
cd laravel-app
php artisan serve  # Should start without errors
```

---

## Step 2: Create Authentication Middleware (15 minutes)

### 2.1 Generate Middleware

```bash
cd laravel-app
php artisan make:middleware Authenticate
```

### 2.2 Implement Middleware Logic

**File**: `laravel-app/app/Http/Middleware/Authenticate.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (session('authenticated') !== true) {
            // Store intended URL for redirect after login
            return redirect()
                ->route('login')
                ->with('redirect', $request->url());
        }

        return $next($request);
    }
}
```

### 2.3 Register Middleware Alias

**File**: `laravel-app/bootstrap/app.php`

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register custom authentication middleware alias
        $middleware->alias([
            'auth.custom' => \App\Http\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

---

## Step 3: Create Authentication Controller (20 minutes)

### 3.1 Generate Controller

```bash
cd laravel-app
php artisan make:controller AuthController
```

### 3.2 Implement Controller Methods

**File**: `laravel-app/app/Http/Controllers/AuthController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLogin(Request $request)
    {
        // Redirect if already authenticated
        if (session('authenticated') === true) {
            return redirect('/expenses');
        }

        return view('auth.login');
    }

    /**
     * Process login attempt.
     */
    public function login(Request $request)
    {
        // Validate input
        $credentials = $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        $username = $credentials['username'];
        $password = $credentials['password'];
        $ip = $request->ip();

        // Check rate limits
        $userKey = "login-user:$username";
        $ipKey = "login-ip:$ip";

        if (RateLimiter::tooManyAttempts($userKey, 5)) {
            $seconds = RateLimiter::availableIn($userKey);
            throw ValidationException::withMessages([
                'username' => ["Too many login attempts. Please try again in " . ceil($seconds / 60) . " minutes."],
            ]);
        }

        if (RateLimiter::tooManyAttempts($ipKey, 10)) {
            $seconds = RateLimiter::availableIn($ipKey);
            throw ValidationException::withMessages([
                'general' => ["Too many login attempts from your location. Please try again in " . ceil($seconds / 60) . " minutes."],
            ]);
        }

        // Validate credentials using timing-safe comparison
        $validUsername = hash_equals(env('USERNAME', ''), $username);
        $validPassword = hash_equals(env('PASSWORD', ''), $password);

        if ($validUsername && $validPassword) {
            // Clear rate limit on successful login
            RateLimiter::clear($userKey);

            // Set authentication session
            session(['authenticated' => true]);

            // Redirect to intended page or default
            $redirect = session('redirect', '/expenses');
            session()->forget('redirect');

            return redirect($redirect);
        }

        // Increment rate limit counters (15 minutes = 900 seconds)
        RateLimiter::hit($userKey, 15 * 60);
        RateLimiter::hit($ipKey, 15 * 60);

        // Return with error
        throw ValidationException::withMessages([
            'username' => ['Invalid username or password.'],
        ]);
    }

    /**
     * Logout and clear session.
     */
    public function logout(Request $request)
    {
        // Clear all session data
        $request->session()->flush();

        return redirect()->route('login');
    }
}
```

---

## Step 4: Create Login View (20 minutes)

### 4.1 Create Auth Views Directory

```bash
mkdir -p laravel-app/resources/views/auth
```

### 4.2 Create Login Blade Template

**File**: `laravel-app/resources/views/auth/login.blade.php`

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Expense Tracker</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .login-container h1 {
            margin-bottom: 1.5rem;
            color: #333;
            text-align: center;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        .form-group input:focus {
            outline: none;
            border-color: #007bff;
        }
        .error-message {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .btn-login {
            width: 100%;
            padding: 0.75rem;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-login:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <main class="login-container">
        <h1>Login</h1>

        @if($errors->any())
            <div class="error-message">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ url('/login') }}">
            @csrf

            <div class="form-group">
                <label for="username">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    value="{{ old('username') }}"
                    required 
                    autofocus
                    autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    autocomplete="current-password">
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>
    </main>
</body>
</html>
```

---

## Step 5: Update Routes (10 minutes)

### 5.1 Add Authentication Routes

**File**: `laravel-app/routes/web.php`

```php
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;

// Authentication routes (MUST be before middleware group)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected expense routes
Route::middleware(['auth.custom'])->group(function () {
    // Custom routes before resource (existing pattern)
    Route::get('/expenses/daily', [ExpenseController::class, 'daily'])->name('expenses.daily');
    Route::get('/expenses/monthly', [ExpenseController::class, 'monthly'])->name('expenses.monthly');

    // Resource routes
    Route::resource('expenses', ExpenseController::class);
});

// Redirect root to expenses (will require auth)
Route::get('/', function () {
    return redirect('/expenses');
});
```

---

## Step 6: Add Logout Button to Layout (10 minutes)

### 6.1 Create Custom Blade Directive

**File**: `laravel-app/app/Providers/AppServiceProvider.php`

```php
use Illuminate\Support\Facades\Blade;

public function boot(): void
{
    // ... existing validation code ...

    // Register custom @auth directive
    Blade::if('auth', function () {
        return session('authenticated') === true;
    });
}
```

### 6.2 Update Main Layout

**File**: `laravel-app/resources/views/layouts/app.blade.php`

Find the navigation section and add logout button:

```blade
<nav class="navbar">
    <div class="nav-content">
        <a href="{{ url('/expenses') }}" class="nav-brand">Expense Tracker</a>
        
        <div class="nav-links">
            <a href="{{ url('/expenses') }}">All Expenses</a>
            <a href="{{ url('/expenses/daily') }}">Daily View</a>
            <a href="{{ url('/expenses/monthly') }}">Monthly View</a>
            
            @auth
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-logout">Logout</button>
                </form>
            @endauth
        </div>
    </div>
</nav>
```

**Add logout button styles** to your CSS:

```css
.btn-logout {
    background: #dc3545;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
}

.btn-logout:hover {
    background: #c82333;
}
```

---

## Step 7: Testing (30 minutes)

### 7.1 Manual Testing

```bash
cd laravel-app
php artisan serve
```

**Test Scenarios**:
1. Visit http://127.0.0.1:8000 → Should redirect to /login
2. Visit http://127.0.0.1:8000/expenses → Should redirect to /login
3. Login with valid credentials → Should redirect to /expenses
4. Login with invalid credentials → Should show error
5. Try 6 failed logins → Should show rate limit message
6. Click logout → Should redirect to /login
7. After logout, try /expenses → Should redirect to /login

### 7.2 Create Feature Tests

**File**: `laravel-app/tests/Feature/AuthTest.php`

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertOk();
        $response->assertSee('Login');
    }

    public function test_login_with_valid_credentials_redirects_to_expenses(): void
    {
        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'testpass',
        ]);

        $response->assertRedirect('/expenses');
        $this->assertTrue(session('authenticated'));
    }

    public function test_login_with_invalid_credentials_returns_error(): void
    {
        $response = $this->post('/login', [
            'username' => 'wrong',
            'password' => 'wrong',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('username');
        $this->assertFalse(session('authenticated'));
    }

    public function test_unauthenticated_user_cannot_access_expenses(): void
    {
        $response = $this->get('/expenses');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_expenses(): void
    {
        session(['authenticated' => true]);

        $response = $this->get('/expenses');
        $response->assertOk();
    }

    public function test_logout_clears_session_and_redirects(): void
    {
        session(['authenticated' => true]);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertFalse(session('authenticated'));
    }

    public function test_rate_limiting_blocks_after_5_failed_attempts(): void
    {
        // Attempt 5 failed logins
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'username' => 'attacker',
                'password' => 'wrong',
            ]);
        }

        // 6th attempt should be blocked
        $response = $this->post('/login', [
            'username' => 'attacker',
            'password' => 'wrong',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertStringContainsString('Too many', session('errors')->first('username'));
    }
}
```

**Run Tests**:
```bash
cd laravel-app
php artisan test --filter=AuthTest
```

Expected output: All tests passing

### 7.3 Update Existing Expense Tests

**File**: `laravel-app/tests/Feature/ExpenseControllerTest.php`

Add authentication to setUp method:

```php
protected function setUp(): void
{
    parent::setUp();
    
    // Authenticate for all expense tests
    session(['authenticated' => true]);
}
```

**Run All Tests**:
```bash
php artisan test
```

Expected: All tests passing (expense tests now authenticated)

---

## Step 8: E2E Tests (30 minutes)

### 8.1 Create Authentication E2E Test

**File**: `laravel-app/tests/e2e/auth.spec.ts`

```typescript
import { test, expect } from '@playwright/test';

test.describe('Authentication', () => {
    test('should redirect unauthenticated users to login', async ({ page }) => {
        await page.goto('/expenses');
        await expect(page).toHaveURL(/\/login/);
    });

    test('should login with valid credentials', async ({ page }) => {
        await page.goto('/login');
        
        await page.fill('input[name="username"]', 'testuser');
        await page.fill('input[name="password"]', 'testpass');
        await page.click('button[type="submit"]');
        
        await expect(page).toHaveURL(/\/expenses/);
        await expect(page.locator('nav')).toContainText('Logout');
    });

    test('should show error for invalid credentials', async ({ page }) => {
        await page.goto('/login');
        
        await page.fill('input[name="username"]', 'wrong');
        await page.fill('input[name="password"]', 'wrong');
        await page.click('button[type="submit"]');
        
        await expect(page.locator('.error-message')).toContainText('Invalid username or password');
    });

    test('should logout successfully', async ({ page }) => {
        // Login first
        await page.goto('/login');
        await page.fill('input[name="username"]', 'testuser');
        await page.fill('input[name="password"]', 'testpass');
        await page.click('button[type="submit"]');
        
        // Logout
        await page.click('button:has-text("Logout")');
        
        await expect(page).toHaveURL(/\/login/);
        
        // Verify cannot access expenses
        await page.goto('/expenses');
        await expect(page).toHaveURL(/\/login/);
    });

    test('should persist session across page reloads', async ({ page }) => {
        // Login
        await page.goto('/login');
        await page.fill('input[name="username"]', 'testuser');
        await page.fill('input[name="password"]', 'testpass');
        await page.click('button[type="submit"]');
        
        // Reload page
        await page.reload();
        
        // Should still be authenticated
        await expect(page).toHaveURL(/\/expenses/);
        await expect(page.locator('nav')).toContainText('Logout');
    });
});
```

**Run E2E Tests**:
```bash
cd laravel-app
npx playwright test tests/e2e/auth.spec.ts
```

---

## Step 9: Production Configuration (10 minutes)

### 9.1 Update .env.example

**File**: `laravel-app/.env.example`

```bash
# Add to file
USERNAME=admin
PASSWORD=change_me_in_production
```

### 9.2 Configure GitHub Secrets

In your GitHub repository settings:

1. Go to Settings → Secrets and variables → Actions
2. Add repository secrets:
   - `USERNAME`: Your production username
   - `PASSWORD`: Your production password

### 9.3 Update GitHub Actions Workflow

**File**: `.github/workflows/laravel.yml`

```yaml
# Add to env section
env:
  USERNAME: ${{ secrets.USERNAME }}
  PASSWORD: ${{ secrets.PASSWORD }}
```

### 9.4 Production Session Configuration

For production deployment, update `.env`:

```bash
SESSION_DRIVER=redis        # Or database for multi-server
CACHE_DRIVER=redis          # For rate limiting atomicity
SESSION_SECURE_COOKIE=true  # HTTPS only
```

---

## Troubleshooting

### Issue: "USERNAME and PASSWORD environment variables are required"

**Solution**: Add USERNAME and PASSWORD to your .env file

### Issue: Rate limiting not working in tests

**Solution**: Use `CACHE_DRIVER=array` in .env.testing

### Issue: Session not persisting

**Solution**: Check that `SESSION_DRIVER` is not set to `array` in production .env

### Issue: CSRF token mismatch

**Solution**: Ensure forms include `@csrf` blade directive

### Issue: Tests fail with "Route [login] not defined"

**Solution**: Ensure route is named: `Route::get('/login', ...)->name('login')`

---

## Verification Checklist

- [ ] Environment variables configured (USERNAME, PASSWORD)
- [ ] Middleware created and registered
- [ ] AuthController implemented
- [ ] Login view created with Material UI styling
- [ ] Routes defined (auth before middleware group)
- [ ] Logout button added to layout
- [ ] Custom @auth Blade directive working
- [ ] Feature tests passing (php artisan test)
- [ ] E2E tests passing (npx playwright test)
- [ ] Manual testing complete (all scenarios work)
- [ ] GitHub secrets configured for CI/CD
- [ ] Production session/cache configuration documented

---

## Next Steps

After completing this quickstart:

1. Review security headers in production (CSP, HSTS)
2. Consider Redis for session/cache in production
3. Monitor rate limiting effectiveness
4. Set up alerts for failed login attempts
5. Document credential rotation process for team

---

## Estimated Timings

- Step 1: Environment Configuration - 5 min
- Step 2: Create Middleware - 15 min
- Step 3: Create Controller - 20 min
- Step 4: Create Login View - 20 min
- Step 5: Update Routes - 10 min
- Step 6: Add Logout Button - 10 min
- Step 7: Testing - 30 min
- Step 8: E2E Tests - 30 min
- Step 9: Production Config - 10 min

**Total: 2.5 hours** (plus contingency for debugging)
