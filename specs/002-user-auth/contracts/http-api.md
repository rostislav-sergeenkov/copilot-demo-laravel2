# HTTP API Contracts: Authentication

**Feature**: [002-user-auth](../spec.md)  
**Date**: December 25, 2025  
**Protocol**: HTTP/1.1  
**Format**: Form-encoded requests, HTML responses (Blade views)

---

## Endpoints Overview

| Method | Path | Purpose | Auth Required |
|--------|------|---------|---------------|
| GET | `/login` | Show login form | No |
| POST | `/login` | Process login | No |
| POST | `/logout` | End session | Yes |

---

## GET /login

**Purpose**: Display login form to unauthenticated users

### Request

**Method**: GET  
**URL**: `/login`  
**Headers**: None required  
**Body**: None

**Query Parameters**: 
- `redirect` (optional): URL to redirect after successful login
  - Example: `/login?redirect=/expenses/daily`
  - Default: `/expenses` if omitted

### Response Success (200 OK)

**Content-Type**: text/html  
**Body**: Rendered Blade view (`auth.login`)

**HTML Structure**:
```html
<!DOCTYPE html>
<html>
<head>
    <title>Login - Expense Tracker</title>
    <!-- Material UI styles -->
</head>
<body>
    <main class="login-container">
        <form method="POST" action="/login">
            @csrf
            
            <h1>Login</h1>
            
            <!-- Error messages displayed here if present -->
            @if($errors->any())
                <div class="error-message">{{ $errors->first() }}</div>
            @endif
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       value="{{ old('username') }}"
                       required 
                       autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required>
            </div>
            
            <button type="submit">Login</button>
        </form>
    </main>
</body>
</html>
```

### Response Redirect (Already Authenticated)

**Status**: 302 Found  
**Location**: `/expenses`  
**Body**: None

**Condition**: User already has `session('authenticated') === true`

---

## POST /login

**Purpose**: Validate credentials and establish authenticated session

### Request

**Method**: POST  
**URL**: `/login`  
**Content-Type**: application/x-www-form-urlencoded  
**Headers**:
- `X-CSRF-TOKEN`: Laravel CSRF token (from form `@csrf` blade directive)

**Body Parameters**:
```
username=string   (required, max:255)
password=string   (required, max:255)
```

**Example**:
```http
POST /login HTTP/1.1
Content-Type: application/x-www-form-urlencoded
X-CSRF-TOKEN: abc123...

username=admin&password=secretpass
```

### Response Success (302 Found)

**Status**: 302 Found  
**Location**: `/expenses` (or value from `redirect` query param)  
**Set-Cookie**: Laravel session cookie with `authenticated=true`

**Example**:
```http
HTTP/1.1 302 Found
Location: /expenses
Set-Cookie: laravel_session=abc123...; Path=/; HttpOnly; SameSite=Lax
```

**Side Effects**:
- `session(['authenticated' => true])` set
- Rate limit counter cleared for username
- User can access protected routes

### Response Failure - Invalid Credentials (302 Found)

**Status**: 302 Found (redirect back to login)  
**Location**: `/login`  
**Body**: Session flash with validation errors

**Example**:
```http
HTTP/1.1 302 Found
Location: /login
```

**Rendered Error on Login Page**:
```html
<div class="error-message">
    Invalid username or password.
</div>
```

**Side Effects**:
- Rate limit counters incremented for username and IP
- Session remains unauthenticated

### Response Failure - Validation Error (302 Found)

**Status**: 302 Found  
**Location**: `/login`  
**Body**: Session flash with field-specific errors

**Validation Rules**:
- `username`: required|string|max:255
- `password`: required|string|max:255

**Example Error Messages**:
```
username: The username field is required.
password: The password field is required.
```

### Response Failure - Rate Limited (429 Too Many Requests)

**Status**: 429 Too Many Requests  
**Content-Type**: text/html  
**Body**: Login form with rate limit error

**Conditions**:
- More than 5 failed attempts for username in 15 minutes, OR
- More than 10 failed attempts from IP in 15 minutes

**Error Message**:
```html
<div class="error-message">
    Too many login attempts. Please try again in 15 minutes.
</div>
```

**Headers**:
```http
Retry-After: 900  (seconds until rate limit resets)
```

---

## POST /logout

**Purpose**: Terminate authenticated session and redirect to login

### Request

**Method**: POST  
**URL**: `/logout`  
**Content-Type**: application/x-www-form-urlencoded  
**Headers**:
- `X-CSRF-TOKEN`: Laravel CSRF token

**Body**: Empty (form can be submitted from any page via button)

**Example**:
```http
POST /logout HTTP/1.1
X-CSRF-TOKEN: xyz789...
```

### Response Success (302 Found)

**Status**: 302 Found  
**Location**: `/login`  
**Set-Cookie**: Cleared session cookie

**Example**:
```http
HTTP/1.1 302 Found
Location: /login
Set-Cookie: laravel_session=deleted; Path=/; Expires=Thu, 01 Jan 1970 00:00:00 GMT
```

**Side Effects**:
- `session()->flush()` called (all session data cleared)
- User cannot access protected routes until re-authentication

### Response Failure - Unauthenticated (302 Found)

**Status**: 302 Found  
**Location**: `/login`  
**Condition**: User was not authenticated (graceful handling)

---

## Protected Routes Contract

All expense routes require authentication middleware check:

### Middleware Behavior

**Middleware**: `Authenticate` (custom middleware class)  
**Applied to**: All routes under `/expenses/*` and expense resource routes

**Check Logic**:
```php
if (session('authenticated') !== true) {
    return redirect()->route('login')
                     ->with('redirect', $request->url());
}
```

### Protected Endpoints

| Method | Path | Original Behavior |
|--------|------|-------------------|
| GET | `/expenses` | List expenses (paginated) |
| GET | `/expenses/create` | Show create form |
| POST | `/expenses` | Store new expense |
| GET | `/expenses/{id}` | Show single expense |
| GET | `/expenses/{id}/edit` | Show edit form |
| PUT/PATCH | `/expenses/{id}` | Update expense |
| DELETE | `/expenses/{id}` | Delete expense (soft) |
| GET | `/expenses/daily` | Show daily totals |
| GET | `/expenses/monthly` | Show monthly breakdown |

### Unauthenticated Access Behavior

**Request Example**:
```http
GET /expenses HTTP/1.1
```

**Response**:
```http
HTTP/1.1 302 Found
Location: /login?redirect=/expenses
```

**After Login**:
User is redirected to originally requested URL (`/expenses` in this example)

---

## Error Responses

### Validation Errors

**Format**: Laravel validation error bag (session flash)

**Structure**:
```php
[
    'username' => ['The username field is required.'],
    'password' => ['The password field is required.'],
]
```

**Display**: Rendered via Blade `@error` directive on login form

### CSRF Token Mismatch

**Status**: 419 Page Expired  
**Response**: Laravel's default CSRF error page  
**Cause**: Form submitted without valid CSRF token or token expired

**User Action**: Refresh page and resubmit

### Rate Limit Exceeded

**Status**: 429 Too Many Requests  
**Response**: Login form with error message  
**Header**: `Retry-After: 900` (seconds)

**User Action**: Wait 15 minutes before retry

---

## Security Headers

All responses include:

```http
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
```

**Production Additional Headers** (when `APP_ENV=production`):
```http
Strict-Transport-Security: max-age=31536000; includeSubDomains
Content-Security-Policy: default-src 'self'
```

---

## Session Contract

### Cookie Structure

**Name**: `laravel_session` (or configured via `SESSION_COOKIE` env)  
**Lifetime**: 120 minutes (configurable)  
**Attributes**:
- `HttpOnly`: true (prevents JavaScript access)
- `Secure`: true in production (HTTPS only)
- `SameSite`: Lax (CSRF protection)
- `Path`: /

### Session Data Structure

```php
[
    '_token' => 'csrf_token_here',
    'authenticated' => true,  // Custom auth flag
    '_previous' => ['url' => '/expenses'],
    '_flash' => [
        'old' => [],
        'new' => ['username' => 'attempted_username']
    ]
]
```

---

## Testing Contracts

### Feature Test Assertions

```php
// Successful login
$response = $this->post('/login', [
    'username' => 'testuser',
    'password' => 'testpass',
]);
$response->assertRedirect('/expenses');
$this->assertTrue(session('authenticated'));

// Failed login
$response = $this->post('/login', [
    'username' => 'wrong',
    'password' => 'wrong',
]);
$response->assertRedirect('/login');
$response->assertSessionHasErrors();
$this->assertFalse(session('authenticated'));

// Protected route access
$response = $this->get('/expenses');
$response->assertRedirect('/login');

// Authenticated access
session(['authenticated' => true]);
$response = $this->get('/expenses');
$response->assertOk();
```

---

## Integration with Existing Application

### Route Definition Order

**File**: `routes/web.php`

```php
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExpenseController;

// Auth routes (MUST be before middleware group)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected expense routes
Route::middleware(['auth.custom'])->group(function () {
    // Custom routes BEFORE resource (existing pattern)
    Route::get('/expenses/daily', [ExpenseController::class, 'daily'])->name('expenses.daily');
    Route::get('/expenses/monthly', [ExpenseController::class, 'monthly'])->name('expenses.monthly');
    
    // Resource routes
    Route::resource('expenses', ExpenseController::class);
});
```

### Blade Layout Integration

**File**: `resources/views/layouts/app.blade.php`

Add logout button to existing layout:

```blade
<nav>
    <!-- Existing navigation -->
    @auth
        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
            @csrf
            <button type="submit">Logout</button>
        </form>
    @endauth
</nav>
```

**Custom `@auth` Directive**:
```php
// In AppServiceProvider
Blade::if('auth', function () {
    return session('authenticated') === true;
});
```

---

## Compatibility Notes

- All existing expense endpoints remain unchanged
- No breaking changes to expense API contracts
- Authentication is transparent to expense controllers
- Middleware handles all auth logic before request reaches controller
- Existing tests continue to work (add `session(['authenticated' => true])` in setUp)

---

## Summary

**New Routes**: 3 (GET /login, POST /login, POST /logout)  
**Modified Routes**: 0 (expense routes unchanged, middleware added)  
**Response Formats**: HTML (Blade views)  
**Authentication Method**: Session-based with custom flag  
**CSRF Protection**: Laravel's built-in (all POST routes)  
**Rate Limiting**: Laravel's RateLimiter (cache-backed)
