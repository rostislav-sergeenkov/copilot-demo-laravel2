# Research: User Authentication for Expense Tracker

**Feature**: [002-user-auth](spec.md)  
**Date**: December 25, 2025  
**Status**: Complete

## Research Questions

### Q1: How to implement authentication without Laravel's built-in User model?

**Context**: The specification requires single-user authentication with credentials stored as GitHub secrets, not multi-user with database storage.

**Decision**: Use custom middleware and session-based authentication without Eloquent User model

**Rationale**:
- Laravel's default authentication (`php artisan make:auth`) assumes database-backed users table
- Specification requires single shared credentials from environment (GitHub secrets)
- Session-based auth is sufficient for this use case (no tokens, APIs, or multi-device requirements)
- Custom middleware can validate credentials against environment variables directly

**Alternatives considered**:
1. **Laravel Breeze/Fortify** - Rejected: Designed for multi-user with database persistence
2. **Custom guard with static user** - Rejected: Overcomplicated; guard system assumes user retrieval from storage
3. **HTTP Basic Auth** - Rejected: Poor UX (browser login prompt), no logout mechanism, credentials sent with every request
4. **Session + middleware** - ✅ Selected: Simple, follows Laravel conventions, full control over auth flow

**Implementation approach**:
- Create `Authenticate` middleware to check session for auth state
- Login controller validates credentials against `env('USERNAME')` and `env('PASSWORD')`
- Store boolean flag in session: `session(['authenticated' => true])`
- Apply middleware to expense routes
- Logout clears session and redirects to login

---

### Q2: How to securely compare passwords from environment variables?

**Context**: SR-002 requires timing-attack-safe password comparison

**Decision**: Use `hash_equals()` for constant-time string comparison

**Rationale**:
- PHP's `===` operator is vulnerable to timing attacks (early exit on first mismatch)
- `hash_equals()` performs constant-time comparison regardless of string differences
- Built-in PHP function, no external dependencies
- Widely used and audited for security applications

**Alternatives considered**:
1. **Direct comparison (`===`)** - Rejected: Vulnerable to timing attacks
2. **Laravel's Hash facade** - Rejected: Designed for password hashing (bcrypt/argon2), not plaintext comparison
3. **hash_equals()** - ✅ Selected: Industry standard for timing-safe comparison

**Code pattern**:
```php
$validUsername = hash_equals(env('USERNAME', ''), $request->input('username'));
$validPassword = hash_equals(env('PASSWORD', ''), $request->input('password'));

if ($validUsername && $validPassword) {
    // Authenticate
}
```

---

### Q3: How to implement rate limiting for brute force protection (SR-004)?

**Context**: Specification requires combined rate limiting: 5 attempts per username OR 10 per IP in 15 minutes

**Decision**: Use Laravel's built-in RateLimiter with custom keys

**Rationale**:
- Laravel provides `RateLimiter` facade with atomic counter in cache
- Supports per-key limits with automatic expiration
- No additional packages required
- Thread-safe with cache drivers (Redis, Memcached)
- Integrates with middleware for automatic throttling responses

**Alternatives considered**:
1. **Manual tracking in database** - Rejected: Complex, slower, requires cleanup jobs
2. **Session-based tracking** - Rejected: Vulnerable to session reset attacks
3. **Third-party package** - Rejected: Unnecessary dependency for simple use case
4. **Laravel RateLimiter** - ✅ Selected: Built-in, battle-tested, cache-backed

**Implementation approach**:
```php
// In login method
$username = $request->input('username');
$ip = $request->ip();

// Check username-based limit
if (RateLimiter::tooManyAttempts("login-user:$username", 5)) {
    return back()->withErrors(['username' => 'Too many login attempts.']);
}

// Check IP-based limit
if (RateLimiter::tooManyAttempts("login-ip:$ip", 10)) {
    return back()->withErrors(['general' => 'Too many requests from your location.']);
}

// After failed login
RateLimiter::hit("login-user:$username", 15 * 60); // 15 minutes
RateLimiter::hit("login-ip:$ip", 15 * 60);

// After successful login
RateLimiter::clear("login-user:$username");
```

---

### Q4: How to handle missing GitHub secrets in development vs production?

**Context**: SR-001 requires reading credentials from GitHub secrets; local development may not have these configured

**Decision**: Use `.env` file for local development, require secrets in production via validation

**Rationale**:
- GitHub secrets become environment variables in Actions runners
- Local `.env` can mirror structure: `USERNAME=admin` / `PASSWORD=secret`
- Application startup can validate required env vars and fail fast
- No code differences between environments (both use `env()`)

**Alternatives considered**:
1. **Hardcoded fallbacks** - Rejected: Security risk if defaults shipped to production
2. **Separate auth mechanisms per environment** - Rejected: Complexity, different code paths
3. **Environment validation** - ✅ Selected: Fail fast with clear error, consistent API

**Implementation**:
```php
// In AppServiceProvider or bootstrap
if (empty(env('USERNAME')) || empty(env('PASSWORD'))) {
    throw new \RuntimeException('USERNAME and PASSWORD environment variables required');
}
```

---

### Q5: How to test authentication without exposing credentials in tests?

**Context**: Tests need predictable credentials without hardcoding production secrets

**Decision**: Use test-specific `.env.testing` with known credentials

**Rationale**:
- PHPUnit supports environment-specific configuration
- `.env.testing` overrides `.env` during test execution
- Can use simple, obvious credentials: `USERNAME=testuser` / `PASSWORD=testpass`
- Tests remain readable and maintainable
- No interference with actual GitHub secrets

**Alternatives considered**:
1. **Mock env() helper** - Rejected: Complex, breaks real environment interaction
2. **Config override in tests** - Rejected: Doesn't test actual env var reading
3. **`.env.testing`** - ✅ Selected: Laravel convention, isolated test environment

**Test structure**:
```php
// .env.testing
USERNAME=testuser
PASSWORD=testpass

// Feature test
public function test_login_with_valid_credentials_succeeds()
{
    $response = $this->post('/login', [
        'username' => 'testuser',
        'password' => 'testpass',
    ]);
    
    $response->assertRedirect('/expenses');
    $this->assertTrue(session('authenticated'));
}
```

---

### Q6: Where should login/logout routes and views be located?

**Context**: Need to maintain Laravel conventions while adding authentication

**Decision**: 
- Routes: Define at top of `routes/web.php` (before expense routes)
- Controller: `app/Http/Controllers/AuthController.php`
- Views: `resources/views/auth/login.blade.php`
- Middleware: `app/Http/Middleware/Authenticate.php`

**Rationale**:
- Follows Laravel's standard auth structure (even though custom implementation)
- Auth routes should be accessible before middleware is applied
- Keeps authentication concerns separate from expense domain logic
- Easy to locate and maintain

**Route structure**:
```php
// routes/web.php
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Apply auth middleware to expense routes
Route::middleware(['auth.custom'])->group(function () {
    Route::resource('expenses', ExpenseController::class);
    // ... other expense routes
});
```

---

## Technology Stack Decisions

| Component | Technology | Rationale |
|-----------|-----------|-----------|
| Session Storage | Laravel sessions (file driver for dev, Redis for prod recommended) | Built-in, reliable, supports all features needed |
| Password Comparison | `hash_equals()` | Timing-attack safe, built-in PHP function |
| Rate Limiting | Laravel RateLimiter facade | Cache-backed, atomic, integrated with framework |
| Middleware | Custom `Authenticate` middleware | Simple session check, no User model dependency |
| Form Validation | Form Request class (`LoginRequest`) | Consistent with existing expense validation pattern |

---

## Security Best Practices Review

Based on OWASP Top 10 and Laravel security guidelines:

1. **A01: Broken Access Control** ✅ Addressed
   - All expense routes protected by middleware
   - Middleware checks session state on every request
   - No bypass routes (enforced in controller tests)

2. **A02: Cryptographic Failures** ✅ Addressed
   - HTTPS required in production (SR-005)
   - Session cookies use secure flag in production
   - `hash_equals()` for constant-time comparison

3. **A03: Injection** ✅ Addressed
   - Laravel's validation sanitizes input (VR-004)
   - No SQL in custom auth (session-based only)
   - Blade templates auto-escape output

4. **A04: Insecure Design** ✅ Addressed
   - Rate limiting prevents brute force (SR-004)
   - Clear session on logout
   - No credentials in logs or error messages

5. **A05: Security Misconfiguration** ✅ Addressed
   - Environment validation at startup
   - Production requires HTTPS
   - No default credentials shipped

6. **A07: Identification and Authentication Failures** ✅ Addressed
   - Session-based authentication
   - Timing-safe password comparison
   - Rate limiting per user and IP
   - Secure session configuration

---

## Performance Considerations

- **Session reads**: Every request checks session (1 filesystem/cache read)
- **Rate limit checks**: 2 cache reads per login attempt (username + IP keys)
- **Expected impact**: <5ms added latency per request (session lookup is cached)
- **Optimization opportunity**: Use Redis for sessions in production (faster than filesystem)

---

## Dependencies

**No new Composer dependencies required** - all functionality uses Laravel built-ins:
- Session management: `illuminate/session`
- Rate limiting: `illuminate/cache`
- Validation: `illuminate/validation`
- Middleware: `illuminate/routing`

---

## Testing Strategy

### Unit Tests
- Password comparison logic (timing safety)
- Rate limiter key generation
- Environment variable validation

### Feature Tests
- Login with valid credentials → success
- Login with invalid credentials → error
- Logout → session cleared
- Rate limiting triggered after threshold
- Unauthenticated access blocked
- Authenticated access allowed

### E2E Tests (Playwright)
- Full login flow: navigate to login, submit form, see expenses
- Logout flow: click logout, redirected to login, cannot access expenses
- Session persistence: login, navigate pages, refresh, still authenticated
- Rate limit message: attempt multiple failed logins, see lockout

---

## Compliance with Constitution

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Simplicity & Convention | ✅ PASS | No abstraction layers; uses Laravel's standard middleware/session |
| II. Test-First Development | ✅ PASS | All scenarios specified; tests will be written before implementation |
| III. Validation & Data Integrity | ✅ PASS | LoginRequest centralizes validation; no database entities affected |
| IV. Material UI Design | ✅ PASS | Login form will follow Material UI standards like expense forms |
| V. Code Quality Automation | ✅ PASS | Pint/Larastan will validate all new code |

**No constitution violations identified** - authentication implementation follows existing patterns and conventions.

---

## Open Questions / Risks

1. **Session driver in production**: Should recommend Redis/Memcached for scalability (file driver not suitable for multi-server)
2. **HTTPS enforcement**: Need to document production deployment requirements (SSL certificates)
3. **Credential rotation**: No mechanism for changing credentials without redeployment (acceptable per spec, out of scope)

---

## Next Steps

With research complete, proceed to **Phase 1: Design & Contracts**:
1. Create data-model.md (session entity structure)
2. Define API contracts (HTTP endpoints, request/response formats)
3. Generate quickstart.md (setup instructions for developers)
4. Update agent context with authentication patterns
