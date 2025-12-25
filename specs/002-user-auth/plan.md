# Implementation Plan: User Authentication for Expense Tracker

**Branch**: `002-user-auth` | **Date**: December 25, 2025 | **Spec**: [spec.md](spec.md)  
**Input**: Feature specification from `/specs/002-user-auth/spec.md`

## Summary

Implement session-based authentication with credentials from environment variables (GitHub secrets) to protect all expense functionality. Single shared username/password stored in `USERNAME` and `PASSWORD` environment variables. Custom middleware checks session state on every protected route request. Rate limiting prevents brute force attacks (5 attempts per username OR 10 per IP in 15 minutes). No database changes required.

**Technical Approach**: Custom `Authenticate` middleware + session flag + `AuthController` with timing-safe password comparison (`hash_equals()`) + Laravel's RateLimiter for brute force protection.

## Technical Context

**Language/Version**: PHP 8.4  
**Primary Dependencies**: Laravel 11, Laravel RateLimiter (built-in), PHP hash_equals()  
**Storage**: Session-based (file driver dev, Redis recommended prod), no database changes  
**Testing**: PHPUnit (feature/unit tests), Playwright (E2E)  
**Target Platform**: Linux server (production), Windows/Mac (development)  
**Project Type**: Web application (Laravel backend + Blade frontend)  
**Performance Goals**: Login < 2 seconds, session check < 5ms per request  
**Constraints**: Single user credentials, no multi-user support, HTTPS required in production  
**Scale/Scope**: Single application instance (expandable to multi-server with Redis sessions)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] **Simplicity Check**: Does this feature require abstraction layers beyond standard Laravel MVC? **NO** - Uses standard middleware, controller, session - no custom guards or user providers needed
- [x] **Test-First Compliance**: Have test scenarios been defined in spec.md? **YES** - All user stories include acceptance scenarios; tests specified in quickstart.md
- [x] **Validation Centralization**: Are new validation rules added to model's `validationRules()` method and consumed by Form Requests? **N/A** - No model validation (credentials from env); form validation in controller per Laravel best practices
- [x] **Soft Delete Enforcement**: If feature involves deletion, does it use `SoftDeletes` trait? **N/A** - No entity deletion in this feature
- [x] **Material UI Compliance**: Does design follow Material UI principles? Are shared layouts/partials used? **YES** - Login form follows Material UI (clean layout, proper spacing, accessible colors); can reuse app.blade.php layout
- [x] **Code Quality Gates**: Will code pass Laravel Pint and Larastan checks? **YES** - All code follows PSR-12, uses typed properties/returns, PHPDoc on public methods
- [x] **Data Integrity**: Are database fields properly typed (decimal for amounts, date for dates, proper indexes)? **N/A** - No database changes in this feature

**Actions Required**: None - All checks pass. No constitution violations identified.

## Project Structure

### Documentation (this feature)

```text
specs/002-user-auth/
├── spec.md              # Feature specification (COMPLETE)
├── plan.md              # This file (IN PROGRESS)
├── research.md          # Phase 0 output (COMPLETE)
├── data-model.md        # Phase 1 output (COMPLETE)
├── quickstart.md        # Phase 1 output (COMPLETE)
├── contracts/
│   └── http-api.md      # HTTP endpoints specification (COMPLETE)
└── checklists/
    └── requirements.md  # Specification quality checklist (COMPLETE)
```

### Source Code (repository root)

```text
laravel-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ExpenseController.php      # Existing - unchanged
│   │   │   └── AuthController.php         # NEW - login/logout logic
│   │   └── Middleware/
│   │       └── Authenticate.php           # NEW - session check middleware
│   ├── Models/
│   │   └── Expense.php                    # Existing - unchanged
│   └── Providers/
│       └── AppServiceProvider.php         # MODIFIED - add env validation + @auth directive
├── resources/
│   └── views/
│       ├── auth/
│       │   └── login.blade.php            # NEW - login form view
│       ├── expenses/                      # Existing - unchanged
│       └── layouts/
│           └── app.blade.php              # MODIFIED - add logout button
├── routes/
│   └── web.php                            # MODIFIED - add auth routes + middleware group
├── tests/
│   ├── Feature/
│   │   ├── ExpenseControllerTest.php      # MODIFIED - add authentication to setUp
│   │   └── AuthTest.php                   # NEW - authentication feature tests
│   └── e2e/
│       └── auth.spec.ts                   # NEW - authentication E2E tests
├── bootstrap/
│   └── app.php                            # MODIFIED - register middleware alias
├── .env                                   # MODIFIED - add USERNAME, PASSWORD
└── .env.testing                           # NEW - test credentials + array drivers
```

**Structure Decision**: This is a web application (Laravel MVC) with all code in `laravel-app/` directory. Authentication implementation adds 3 new files (AuthController, Authenticate middleware, login view), modifies 5 existing files (routes, layout, AppServiceProvider, bootstrap/app.php, existing tests), and creates new test files. No database migrations or new models required.

## Complexity Tracking

**No Constitution Violations** - This section intentionally left empty as all constitution checks passed. No justification needed for complexity additions.

---

## Implementation Phases

### Phase 0: Research & Design Discovery ✅ COMPLETE

**Status**: Complete  
**Artifacts**: [research.md](research.md)

**Key Decisions Made**:
1. Session-based auth without Eloquent User model (credentials from environment)
2. Custom middleware approach over Laravel guards (simpler for single-user case)
3. `hash_equals()` for timing-safe password comparison
4. Laravel RateLimiter for brute force protection (cache-backed)
5. Test credentials in `.env.testing` for predictable testing

**Research Questions Resolved**: 6/6
- Q1: Custom middleware + session flag (no User model)
- Q2: `hash_equals()` for timing-safe comparison
- Q3: Laravel RateLimiter with dual keys (username + IP)
- Q4: `.env` for local dev, GitHub secrets for production
- Q5: `.env.testing` for test credentials
- Q6: Standard Laravel auth structure (app/Http/Controllers/AuthController, etc.)

---

### Phase 1: Data Model & API Contracts ✅ COMPLETE

**Status**: Complete  
**Artifacts**: [data-model.md](data-model.md), [contracts/http-api.md](contracts/http-api.md), [quickstart.md](quickstart.md)

**Data Model Summary**:
- **Session state**: `authenticated` boolean flag
- **Environment**: `USERNAME`, `PASSWORD` variables
- **Rate limiting**: Cache keys for username and IP tracking
- **No database changes**: All state is ephemeral (session/cache)

**API Contracts**:
- GET `/login` - Display login form
- POST `/login` - Validate and authenticate
- POST `/logout` - End session
- All `/expenses/*` routes protected by `auth.custom` middleware

**Constitution Re-Check**: ✅ All checks still pass after design phase

---

### Phase 2: Implementation Roadmap

**Status**: Planned (not executed by `/speckit.plan`)  
**Next Command**: `/speckit.tasks` to generate detailed task breakdown

**Implementation Order** (from quickstart.md):

1. **Environment Setup** (5 min)
   - Add USERNAME, PASSWORD to .env
   - Create .env.testing
   - Validate env vars in AppServiceProvider

2. **Middleware** (15 min)
   - Generate Authenticate middleware
   - Implement session check logic
   - Register in bootstrap/app.php

3. **Controller** (20 min)
   - Generate AuthController
   - Implement login method (validation, rate limiting, hash_equals)
   - Implement logout method
   - Implement showLogin method

4. **Views** (20 min)
   - Create resources/views/auth directory
   - Create login.blade.php with Material UI styling
   - Add @auth Blade directive in AppServiceProvider

5. **Routes** (10 min)
   - Add auth routes (before middleware group)
   - Wrap expense routes in auth.custom middleware

6. **Layout Updates** (10 min)
   - Add logout button to app.blade.php layout

7. **Testing** (60 min)
   - Create AuthTest.php feature tests (7 scenarios)
   - Update ExpenseControllerTest setUp
   - Create auth.spec.ts E2E tests (5 scenarios)
   - Run all tests and verify passing

8. **Production Config** (10 min)
   - Update .env.example
   - Document GitHub secrets setup
   - Document Redis configuration for production

**Total Estimated Time**: 2.5 hours

---

## Testing Strategy

### Unit Tests (N/A for this feature)
No unit-testable logic outside of controller methods (which are feature-tested)

### Feature Tests (PHPUnit)

**File**: `tests/Feature/AuthTest.php`

| Test | Scenario | Expected Outcome |
|------|----------|------------------|
| test_login_page_is_accessible | GET /login | 200 OK, see "Login" |
| test_login_with_valid_credentials | POST /login (valid) | Redirect to /expenses, session true |
| test_login_with_invalid_credentials | POST /login (invalid) | Redirect to /login, session false, errors |
| test_unauthenticated_access | GET /expenses (no auth) | Redirect to /login |
| test_authenticated_access | GET /expenses (with auth) | 200 OK |
| test_logout_clears_session | POST /logout | Redirect to /login, session false |
| test_rate_limiting | 6 failed logins | Errors after 5 attempts |

**Coverage Target**: 100% of AuthController methods

### E2E Tests (Playwright)

**File**: `tests/e2e/auth.spec.ts`

| Test | User Flow | Assertions |
|------|-----------|-----------|
| Unauthenticated redirect | Navigate to /expenses | Redirected to /login |
| Successful login | Fill form, submit | See /expenses, logout button visible |
| Invalid credentials | Wrong username/password | Error message shown |
| Logout | Click logout button | Redirected to /login, cannot access expenses |
| Session persistence | Login, reload page | Still authenticated |

**Coverage Target**: All P1 and P2 user stories from spec.md

### Integration with Existing Tests

**File**: `tests/Feature/ExpenseControllerTest.php`

**Modification**: Add to setUp method:
```php
session(['authenticated' => true]);
```

This ensures all existing expense tests continue to pass (they now run as authenticated)

---

## Deployment Considerations

### Development Environment
- **Session driver**: File (default)
- **Cache driver**: File (default)
- **Credentials**: .env file (USERNAME, PASSWORD)
- **HTTPS**: Not required (local development)

### Production Environment
- **Session driver**: Redis (recommended for multi-server)
- **Cache driver**: Redis (required for atomic rate limiting)
- **Credentials**: GitHub repository secrets → environment variables
- **HTTPS**: Required (SESSION_SECURE_COOKIE=true)
- **Headers**: CSP, HSTS, X-Frame-Options

### GitHub Actions CI/CD
- **Environment**: USERNAME and PASSWORD from GitHub secrets
- **Test execution**: Use .env.testing (array drivers for speed)
- **E2E tests**: Run against test credentials

---

## Risk Mitigation

| Risk | Mitigation |
|------|-----------|
| Missing environment variables | Startup validation throws RuntimeException |
| Timing attacks on password | Using hash_equals() for constant-time comparison |
| Brute force attacks | Rate limiting (5 per user, 10 per IP) |
| Session hijacking | HttpOnly cookies, SameSite=Lax, HTTPS in prod |
| CSRF attacks | Laravel's built-in CSRF protection on all POST routes |
| Credential exposure | Never log credentials, never in error messages |

---

## Success Metrics

Based on spec.md Success Criteria:

| Metric | Target | Measurement |
|--------|--------|-------------|
| SC-001: Login time | < 10 seconds | Manual testing + Playwright timing |
| SC-002: Route protection | 100% blocked | Feature test coverage |
| SC-003: Error feedback | < 2 seconds | Manual testing + Playwright |
| SC-004: Session persistence | No re-auth needed | Feature tests + E2E |
| SC-005: Logout time | < 2 seconds | Feature tests |
| SC-006: Security testing | Zero bypasses | Penetration testing |

---

## Dependencies & Prerequisites

### No New Composer Dependencies
All functionality uses Laravel 11 built-ins:
- `illuminate/session` (session management)
- `illuminate/cache` (rate limiting)
- `illuminate/validation` (form validation)
- `illuminate/routing` (middleware)

### Environment Requirements
- PHP 8.4+ (existing requirement)
- Laravel 11 (existing)
- USERNAME and PASSWORD environment variables (new)
- Redis recommended for production (optional for dev)

### Breaking Changes
**None** - All existing functionality preserved. Expense routes require authentication but controllers/views unchanged.

---

## Rollback Plan

If critical issues discovered after deployment:

1. **Quick rollback**: Remove middleware group from routes/web.php
   ```php
   // Temporarily remove middleware wrapper
   // Route::middleware(['auth.custom'])->group(function () {
       // Expense routes work unauthenticated
   // });
   ```

2. **Full rollback**: Revert branch to main
   ```bash
   git checkout main
   git branch -D 002-user-auth
   ```

3. **Data impact**: None (no database changes)

---

## Post-Implementation Tasks

After implementation complete (via `/speckit.tasks`):

1. Update README.md with authentication setup instructions
2. Add security audit checklist to docs/
3. Configure monitoring for failed login attempts
4. Document credential rotation procedure
5. Add alert thresholds for rate limiting events

---

## Next Steps

1. **Run `/speckit.tasks`** to generate detailed task breakdown (tasks.md)
2. **Implement in order**: Follow quickstart.md step-by-step
3. **Test continuously**: Run tests after each major component
4. **Update documentation**: Keep constitution.md and copilot-instructions.md in sync
5. **Security review**: Before merging to main, conduct security checklist review

---

## References

- Feature Specification: [spec.md](spec.md)
- Research Notes: [research.md](research.md)
- Data Model: [data-model.md](data-model.md)
- API Contracts: [contracts/http-api.md](contracts/http-api.md)
- Developer Guide: [quickstart.md](quickstart.md)
- Constitution: [../../.specify/memory/constitution.md](../../.specify/memory/constitution.md)
