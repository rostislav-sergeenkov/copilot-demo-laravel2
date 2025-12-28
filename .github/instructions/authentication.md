---
description: Authentication patterns, security guidelines, and route protection
applyTo: laravel-app/app/Http/{Controllers/AuthController.php,Middleware/Authenticate.php}
---

# Authentication Guidelines for Expense Tracker

Authentication implementation patterns and security guidelines for the Laravel Expense Tracker application.

## Authentication Pattern

**Session-based authentication** with custom middleware (no Eloquent User model)

### Why This Approach?

- ✅ Simple expense tracker doesn't need multi-user functionality
- ✅ Environment-based credentials suitable for single-user apps
- ✅ Session-based auth integrates naturally with Laravel
- ✅ Custom middleware provides full control over auth logic
- ❌ Not suitable for multi-user applications (would need Eloquent User model)

---

## Configuration

### Environment Variables

Credentials stored in `.env` file:

```env
USERNAME=admin
PASSWORD=$2y$12$KWIPlkc0vIvtfAxLMhy6VOXo7EkHYH7lfv0OQAFbPvlPzvJ6Njbpy
```

**Never commit plain text passwords** - use bcrypt hashed passwords.

### Session Management

- **Session flag**: `session('authenticated')` boolean indicates auth state
- **Session driver**: Uses Laravel's default session driver (file-based)
- **Session lifetime**: Configured in `config/session.php`

### Middleware

- **Alias**: `auth.custom` registered in `bootstrap/app.php`
- **Class**: `App\Http\Middleware\Authenticate`
- **Behavior**: Checks `session('authenticated') === true` on every request

### Rate Limiting

- **Per username**: 5 attempts in 15 minutes
- **Per IP**: 10 attempts in 15 minutes
- **Implementation**: Laravel's `RateLimiter` facade

---

## Key Components

### 1. AuthController

**Location**: `app/Http/Controllers/AuthController.php`

**Methods**:
- `showLogin()` - Display login form
- `login()` - Validate credentials, check rate limits, set session
- `logout()` - Clear session and redirect to login

**Login flow**:
```php
public function login(Request $request): RedirectResponse
{
    // 1. Validate input
    $validated = $request->validate([
        'username' => 'required|string',
        'password' => 'required|string',
    ]);

    // 2. Check rate limits (username + IP)
    $username = $validated['username'];
    $ip = $request->ip();
    
    // 3. Validate credentials using hash_equals()
    $valid = hash_equals(env('USERNAME', ''), $username) 
          && hash_equals(env('PASSWORD'), $validated['password']);
    
    // 4. Set session or return error
    if ($valid) {
        RateLimiter::clear($userKey);
        $request->session()->put('authenticated', true);
        return redirect()->intended('/expenses');
    }
    
    RateLimiter::hit($userKey, 15 * 60);
    return back()->withErrors(['username' => 'Invalid credentials']);
}
```

### 2. Authenticate Middleware

**Location**: `app/Http/Middleware/Authenticate.php`

**Behavior**:
```php
public function handle(Request $request, Closure $next): Response
{
    if ($request->session()->get('authenticated') !== true) {
        return redirect()->route('login');
    }
    
    return $next($request);
}
```

### 3. Login View

**Location**: `resources/views/auth/login.blade.php`

**Features**:
- Material UI styled form
- Username and password fields
- CSRF protection via `@csrf`
- Error message display
- Remember me checkbox (optional)

### 4. Custom Blade Directive

**Location**: `app/Providers/AppServiceProvider.php`

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

---

## Security Patterns

### 1. Timing-Safe Comparison

**Always use `hash_equals()`** for password/username validation to prevent timing attacks:

```php
// ✅ Correct - Timing-safe
$valid = hash_equals(env('USERNAME', ''), $username) 
      && hash_equals(env('PASSWORD'), $password);

// ❌ Wrong - Vulnerable to timing attacks
$valid = (env('USERNAME') === $username) && (env('PASSWORD') === $password);
```

### 2. Rate Limiting

**Implement dual rate limiting** (username + IP) to prevent brute force:

```php
use Illuminate\Support\Facades\RateLimiter;

$userKey = "login-user:$username";
$ipKey = "login-ip:{$request->ip()}";

// Check username-based limit (5 attempts)
if (RateLimiter::tooManyAttempts($userKey, 5)) {
    $seconds = RateLimiter::availableIn($userKey);
    return back()->withErrors([
        'username' => "Too many attempts. Try again in $seconds seconds."
    ]);
}

// Check IP-based limit (10 attempts)
if (RateLimiter::tooManyAttempts($ipKey, 10)) {
    $seconds = RateLimiter::availableIn($ipKey);
    return back()->withErrors([
        'username' => "Too many attempts from this IP. Try again in $seconds seconds."
    ]);
}

// On failed login
RateLimiter::hit($userKey, 15 * 60); // 15 minutes
RateLimiter::hit($ipKey, 15 * 60);

// On successful login
RateLimiter::clear($userKey);
RateLimiter::clear($ipKey);
```

### 3. Session Security

**Best practices**:
```php
// ✅ Use session flag, not user object
$request->session()->put('authenticated', true);

// ✅ Flush entire session on logout
$request->session()->flush();

// ✅ Regenerate session ID after login
$request->session()->regenerate();

// ❌ Don't store sensitive data in session
$request->session()->put('password', '...'); // Never do this
```

### 4. CSRF Protection

**Always use `@csrf` directive in forms**:
```blade
<form method="POST" action="{{ route('login') }}">
    @csrf
    <!-- form fields -->
</form>
```

---

## Route Protection

### Setup in `routes/web.php`

```php
// Public auth routes (before middleware group)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes group
Route::middleware(['auth.custom'])->group(function () {
    Route::resource('expenses', ExpenseController::class);
    Route::get('/expenses/daily', [ExpenseController::class, 'daily'])->name('expenses.daily');
    Route::get('/expenses/monthly', [ExpenseController::class, 'monthly'])->name('expenses.monthly');
});
```

**Route naming**:
- ✅ Name login route as `login` - Laravel's default redirect target
- ✅ Use `middleware()` method for route groups
- ✅ Define auth routes before protected routes

---

## Testing Authentication

### Environment Setup

**Use `.env.testing`** with test credentials:

```env
USERNAME=testuser
PASSWORD=testpass
```

### Testing Protected Routes

**Add authenticated session in test setup**:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    session(['authenticated' => true]);
});

test('authenticated user can access expenses', function () {
    $response = $this->get('/expenses');
    $response->assertStatus(200);
});

test('unauthenticated user is redirected to login', function () {
    session()->forget('authenticated'); // Remove auth
    
    $response = $this->get('/expenses');
    $response->assertRedirect('/login');
});
```

### Testing Login Flow

```php
test('user can login with valid credentials', function () {
    $response = $this->post('/login', [
        'username' => env('USERNAME'),
        'password' => env('PASSWORD'),
    ]);
    
    $response->assertRedirect('/expenses');
    expect(session('authenticated'))->toBeTrue();
});

test('login fails with invalid credentials', function () {
    $response = $this->post('/login', [
        'username' => 'wrong',
        'password' => 'wrong',
    ]);
    
    $response->assertRedirect();
    $response->assertSessionHasErrors(['username']);
    expect(session('authenticated'))->not->toBeTrue();
});
```

### Testing Rate Limiting

```php
test('rate limiting blocks after 5 failed attempts', function () {
    // Make 5 failed attempts
    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'username' => 'testuser',
            'password' => 'wrong',
        ]);
    }
    
    // 6th attempt should be blocked
    $response = $this->post('/login', [
        'username' => 'testuser',
        'password' => 'wrong',
    ]);
    
    $response->assertSessionHasErrors(['username']);
    expect($response->getSession()->get('errors')->first('username'))
        ->toContain('Too many attempts');
});
```

### E2E Auth Testing

For E2E tests, use the test authentication endpoint (only available in local/testing):

```typescript
// In helpers.ts
export async function login(page: Page) {
  const username = process.env.AUTH_USERNAME || 'admin';
  const passwordHash = process.env.PASSWORD_HASH || '';
  
  await page.goto('/test/auth');
  await page.evaluate(({ username, passwordHash }) => {
    document.cookie = `auth_user=${username}; path=/`;
    document.cookie = `auth_hash=${passwordHash}; path=/`;
  }, { username, passwordHash });
  
  await page.goto('/expenses');
}
```

---

## Architecture Rules

**Enforced by Architecture Tests**:

- ✅ Middleware must be in correct namespace (`App\Http\Middleware`)
- ✅ Middleware must have `handle()` method
- ✅ No debug statements in auth code
- ✅ Controllers have return types
- ✅ All PHP files use strict types

---

## Migration Path (Future)

**If multi-user functionality is needed**:

1. Create User model with Laravel Breeze/Fortify
2. Migrate to database-backed authentication
3. Update middleware to use `Auth::check()`
4. Add user relationships to Expense model
5. Update tests to use `actingAs($user)`

**Current implementation makes this migration straightforward** - auth logic is isolated in AuthController and middleware.

---

## References

- **Specification**: `specs/002-user-auth/spec.md`
- **Implementation Plan**: `specs/002-user-auth/plan.md`
- **Developer Guide**: `specs/002-user-auth/quickstart.md`
- **Feature Tests**: `tests/Feature/Auth/AuthenticationTest.php`
