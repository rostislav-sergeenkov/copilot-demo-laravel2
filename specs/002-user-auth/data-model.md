# Data Model: User Authentication

**Feature**: [002-user-auth](spec.md)  
**Date**: December 25, 2025  
**Status**: Complete

## Overview

This feature does NOT introduce new database entities. Authentication is session-based with credentials stored as environment variables. This document describes the session structure and configuration entities.

---

## Session Structure

### Authenticated Session State

**Purpose**: Track whether current request is from authenticated user

**Storage**: Laravel session (file/cache/Redis)

**Schema**:
```php
[
    '_token' => string,           // Laravel CSRF token
    'authenticated' => bool,      // Custom auth flag (true if logged in)
    '_previous' => array,         // Laravel navigation history
    '_flash' => array,            // Laravel flash messages
]
```

**Lifecycle**:
- Created: On successful login (`session(['authenticated' => true])`)
- Checked: By `Authenticate` middleware on every protected route request
- Destroyed: On logout (`session()->flush()`) or expiration (120 minutes default)

**Validation Rules**: N/A (boolean flag, not user input)

**Relationships**: None (standalone session data)

---

## Configuration Entities

### Environment Credentials

**Purpose**: Store single shared username/password for application access

**Storage**: Environment variables (`.env` file locally, GitHub Secrets in production)

**Schema**:
```bash
USERNAME=string   # Single username for application access (required)
PASSWORD=string   # Single password for application access (required)
```

**Validation**:
- Both must be non-empty at application startup
- Validated in `AppServiceProvider::boot()` or bootstrap file
- Failure to provide results in RuntimeException (fail-fast)

**Access Pattern**:
```php
$expectedUsername = env('USERNAME');
$expectedPassword = env('PASSWORD');

// Never use direct comparison - use hash_equals()
$valid = hash_equals($expectedUsername, $inputUsername) 
      && hash_equals($expectedPassword, $inputPassword);
```

**Security Notes**:
- Never log these values
- Never expose in error messages
- Never send to client (even in encrypted form)
- Use timing-safe comparison (`hash_equals()`)

---

## Rate Limiting State

### Failed Login Tracking

**Purpose**: Track failed login attempts for brute force protection

**Storage**: Laravel cache (file/Redis/Memcached)

**Keys**:
```php
"login-user:{username}"  // Track attempts per username
"login-ip:{ip_address}"  // Track attempts per IP address
```

**Structure**:
```php
[
    'attempts' => int,      // Number of failed attempts
    'expires_at' => int,    // Unix timestamp when counter resets
]
```

**Thresholds**:
- Username-based: 5 attempts per 15 minutes
- IP-based: 10 attempts per 15 minutes
- Lockout: Until 15-minute window expires

**Lifecycle**:
- Incremented: On each failed login attempt
- Cleared: On successful login (username key only)
- Expired: Automatically after 15 minutes (TTL in cache)

**Implementation**:
```php
// Check if rate limited
RateLimiter::tooManyAttempts($key, $maxAttempts); // returns bool

// Increment attempt counter
RateLimiter::hit($key, $decaySeconds); // atomic increment

// Clear on success
RateLimiter::clear($key);

// Get available attempts
RateLimiter::availableIn($key); // seconds until reset
```

---

## No Database Changes

**Important**: This feature requires NO migrations or database schema changes.

Existing database:
- `expenses` table remains unchanged
- No `users` table created (deviation from standard Laravel auth)
- No `password_resets` or `personal_access_tokens` tables needed

---

## State Transitions

### Authentication State Machine

```
┌─────────────┐
│ Unauthenticated │ (No session or authenticated=false)
└────┬────────┘
     │ POST /login with valid credentials
     ▼
┌─────────────┐
│ Authenticated │ (session['authenticated']=true)
└────┬────────┘
     │
     ├─── POST /logout ────────────┐
     │                              ▼
     └─── Session timeout ───► [Clear session] ──┐
                                                  │
                                                  ▼
                                          ┌─────────────┐
                                          │ Unauthenticated │
                                          └─────────────┘
```

### Rate Limit State

```
┌──────────────┐
│ No Attempts   │ (No cache entry for key)
└────┬─────────┘
     │ Failed login
     ▼
┌──────────────┐
│ 1-4 Attempts  │ (Username) or 1-9 Attempts (IP)
└────┬─────────┘     │
     │               │ Successful login (clears username key)
     │               ▼
     │          ┌──────────────┐
     │          │ No Attempts   │
     │          └──────────────┘
     │
     │ 5th failed attempt (username) or 10th (IP)
     ▼
┌──────────────┐
│ Locked Out    │ (Returns 429 or error message)
└────┬─────────┘
     │ Wait 15 minutes (TTL expires)
     ▼
┌──────────────┐
│ No Attempts   │
└──────────────┘
```

---

## Data Access Patterns

### Login Flow
1. User submits credentials via POST /login
2. Validate input (required fields)
3. Check rate limits for username and IP
4. Compare credentials using `hash_equals()`
5. If valid:
   - Clear rate limit for username
   - Set `session(['authenticated' => true])`
   - Redirect to /expenses
6. If invalid:
   - Increment rate limit counters
   - Return error message
   - Stay on login page

### Protected Request Flow
1. User requests protected route (e.g., GET /expenses)
2. `Authenticate` middleware intercepts request
3. Check `session('authenticated')` === true
4. If true: Allow request to proceed
5. If false: Redirect to /login with intended URL

### Logout Flow
1. User clicks logout (POST /logout)
2. Call `session()->flush()` (clears all session data)
3. Redirect to /login

---

## Session Configuration

**File**: `config/session.php`

**Key Settings**:
```php
'driver' => env('SESSION_DRIVER', 'file'),  // file for dev, redis for prod
'lifetime' => 120,                           // 120 minutes (2 hours)
'expire_on_close' => false,                  // Session persists after browser close
'encrypt' => false,                          // Session already secure via file permissions
'http_only' => true,                         // Prevent JavaScript access (XSS protection)
'secure' => env('SESSION_SECURE_COOKIE', false),  // true in production (HTTPS only)
'same_site' => 'lax',                        // CSRF protection
```

**Production Recommendations**:
- `SESSION_DRIVER=redis` for multi-server deployments
- `SESSION_SECURE_COOKIE=true` to enforce HTTPS
- Configure Redis connection in `config/database.php`

---

## Cache Configuration

**File**: `config/cache.php`

**Key Settings** (for rate limiting):
```php
'default' => env('CACHE_DRIVER', 'file'),  // file for dev, redis for prod

'stores' => [
    'file' => [
        'driver' => 'file',
        'path' => storage_path('framework/cache/data'),
    ],
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
    ],
],
```

**Production Recommendations**:
- `CACHE_DRIVER=redis` for atomic operations and performance
- Rate limiting requires atomic increments (Redis guarantees atomicity)

---

## Testing Entities

### Test Environment Variables

**File**: `.env.testing`

```bash
USERNAME=testuser
PASSWORD=testpass
SESSION_DRIVER=array     # In-memory for faster tests
CACHE_DRIVER=array       # In-memory for faster tests
```

**Rationale**:
- Predictable credentials for feature tests
- In-memory drivers eliminate filesystem/network I/O
- Isolated from development/production environments

---

## Comparison with Standard Laravel Auth

| Aspect | Standard Laravel Auth | This Implementation |
|--------|----------------------|---------------------|
| User storage | Database (`users` table) | Environment variables |
| Authentication | Eloquent User model | Session boolean flag |
| User retrieval | `Auth::user()` | N/A (no user object) |
| Multiple users | Yes (unlimited) | No (single shared credentials) |
| Password hashing | Bcrypt/Argon2 | Plaintext in env (timing-safe comparison) |
| Guards | Multiple (web, api) | Single custom middleware |
| Password reset | Built-in | N/A (manual credential update) |
| Remember me | Built-in | N/A (session-only) |

**Justification for Deviation**: 
- Specification requires single shared credentials from GitHub secrets
- Multi-user database infrastructure unnecessary for use case
- Simpler implementation maintains constitution principle of simplicity

---

## Summary

This authentication system introduces:
- **1 session field**: `authenticated` (boolean)
- **2 environment variables**: `USERNAME`, `PASSWORD`
- **2 cache key patterns**: `login-user:*`, `login-ip:*`
- **0 database tables**: No schema changes

All state is ephemeral (session/cache) and requires no migrations or data persistence beyond Laravel's standard session/cache mechanisms.
