# Implementation Tasks: User Authentication for Expense Tracker

**Feature**: [002-user-auth](spec.md) | **Branch**: `002-user-auth` | **Date**: December 25, 2025  
**Plan**: [plan.md](plan.md) | **Quickstart**: [quickstart.md](quickstart.md)

---

## Task Organization

Tasks are organized by **user story** to enable independent implementation and testing. Each user story phase represents a complete, testable increment of functionality.

**MVP Scope**: User Story 1 (Login to Access Expenses) provides the minimum viable product.

---

## Phase 1: Setup & Environment Configuration

**Goal**: Configure development environment and validate prerequisites

**Independent Test**: Environment validation passes, credentials accessible

### Tasks

- [ ] T001 Add USERNAME and PASSWORD to laravel-app/.env file
- [ ] T002 Create laravel-app/.env.testing with test credentials (USERNAME=testuser, PASSWORD=testpass, SESSION_DRIVER=array, CACHE_DRIVER=array)
- [ ] T003 Update laravel-app/.env.example to include USERNAME and PASSWORD placeholders
- [ ] T004 Add environment validation to laravel-app/app/Providers/AppServiceProvider.php boot() method (throw RuntimeException if USERNAME or PASSWORD empty)
- [ ] T005 Verify environment configuration by running php artisan serve from laravel-app/ directory

---

## Phase 2: Foundational Infrastructure

**Goal**: Create core authentication components that all user stories depend on

**Independent Test**: Middleware and controller are created and registered, routes defined

### Tasks

- [ ] T006 Generate Authenticate middleware: php artisan make:middleware Authenticate in laravel-app/
- [ ] T007 Implement session check logic in laravel-app/app/Http/Middleware/Authenticate.php (check session('authenticated') === true, redirect to login if false)
- [ ] T008 Register middleware alias 'auth.custom' in laravel-app/bootstrap/app.php withMiddleware() method
- [ ] T009 Generate AuthController: php artisan make:controller AuthController in laravel-app/
- [ ] T010 Create custom @auth Blade directive in laravel-app/app/Providers/AppServiceProvider.php (Blade::if('auth', fn() => session('authenticated') === true))

---

## Phase 3: User Story 1 - Login to Access Expenses (Priority: P1)

**Story Goal**: Users can log in with valid credentials and access protected expense pages. Invalid credentials show errors. Unauthenticated users cannot access expenses.

**Independent Test**: 
- Login with valid credentials → redirected to /expenses
- Login with invalid credentials → error shown
- Access /expenses without auth → redirected to /login
- After login, can access all expense pages

**Acceptance Criteria** (from spec.md):
1. Valid login redirects to expenses list
2. Invalid login shows error and stays on login page
3. Unauthenticated access redirects to login
4. Authenticated users can view and interact with expense data

### Tasks - Views

- [ ] T011 [US1] Create directory laravel-app/resources/views/auth/
- [ ] T012 [US1] Create login view laravel-app/resources/views/auth/login.blade.php with Material UI styling (form with username/password fields, @csrf token, error display, submit button)

### Tasks - Controller Logic

- [ ] T013 [US1] Implement showLogin() method in laravel-app/app/Http/Controllers/AuthController.php (check if authenticated, redirect to expenses if true, otherwise return login view)
- [ ] T014 [US1] Implement login() method validation in laravel-app/app/Http/Controllers/AuthController.php (validate username: required|string|max:255, password: required|string|max:255)
- [ ] T015 [US1] Implement rate limiting checks in login() method (check RateLimiter::tooManyAttempts for username key with 5 attempts threshold and IP key with 10 attempts threshold)
- [ ] T016 [US1] Implement credential validation in login() method using hash_equals() for timing-safe comparison (compare against env('USERNAME') and env('PASSWORD'))
- [ ] T017 [US1] Implement successful login logic in login() method (clear rate limiter for username, set session(['authenticated' => true]), redirect to intended URL or /expenses)
- [ ] T018 [US1] Implement failed login logic in login() method (increment rate limit counters with 15-minute TTL, return validation error)

### Tasks - Routes

- [ ] T019 [US1] Add authentication routes to laravel-app/routes/web.php BEFORE middleware group (GET /login, POST /login, POST /logout with route names)
- [ ] T020 [US1] Wrap existing expense routes in laravel-app/routes/web.php with Route::middleware(['auth.custom'])->group()
- [ ] T021 [US1] Update root redirect in laravel-app/routes/web.php to redirect / to /expenses

### Tasks - Feature Tests

- [ ] T022 [US1] Create laravel-app/tests/Feature/AuthTest.php
- [ ] T023 [US1] Write test_login_page_is_accessible in AuthTest.php (GET /login returns 200, contains "Login")
- [ ] T024 [US1] Write test_login_with_valid_credentials in AuthTest.php (POST /login with testuser/testpass redirects to /expenses, session authenticated is true)
- [ ] T025 [US1] Write test_login_with_invalid_credentials in AuthTest.php (POST /login with wrong credentials redirects to /login, has errors, session authenticated is false)
- [ ] T026 [US1] Write test_unauthenticated_user_cannot_access_expenses in AuthTest.php (GET /expenses without session redirects to /login)
- [ ] T027 [US1] Write test_authenticated_user_can_access_expenses in AuthTest.php (set session authenticated=true, GET /expenses returns 200)
- [ ] T028 [US1] Run php artisan test --filter=AuthTest from laravel-app/ and verify tests pass

### Tasks - Integration

- [ ] T029 [US1] Update laravel-app/tests/Feature/ExpenseControllerTest.php setUp() method to add session(['authenticated' => true])
- [ ] T030 [US1] Run php artisan test from laravel-app/ to verify all tests pass including expense tests

### Tasks - E2E Tests

- [ ] T031 [US1] Create laravel-app/tests/e2e/auth.spec.ts
- [ ] T032 [US1] Write E2E test for unauthenticated redirect (navigate to /expenses, assert redirected to /login)
- [ ] T033 [US1] Write E2E test for successful login (fill form with testuser/testpass, submit, assert URL is /expenses, logout button visible)
- [ ] T034 [US1] Write E2E test for invalid credentials (fill form with wrong data, submit, assert error message shown)
- [ ] T035 [US1] Run npx playwright test tests/e2e/auth.spec.ts from laravel-app/ and verify tests pass

---

## Phase 4: User Story 2 - Logout to Secure Session (Priority: P2)

**Story Goal**: Authenticated users can log out, which clears their session and prevents further access to expenses until re-authentication.

**Independent Test**:
- Login successfully
- Click logout button
- Redirected to login page
- Cannot access expense pages without re-authentication
- Browser back button doesn't show cached expense data

**Acceptance Criteria** (from spec.md):
1. Logout button click ends session and redirects to login
2. After logout, expense pages redirect to login
3. Browser back button doesn't expose expense data

### Tasks - Controller

- [ ] T036 [P] [US2] Implement logout() method in laravel-app/app/Http/Controllers/AuthController.php (call $request->session()->flush(), redirect to login route)

### Tasks - Layout

- [ ] T037 [P] [US2] Add logout button to laravel-app/resources/views/layouts/app.blade.php navigation (form with POST to logout route, wrapped in @auth directive, styled as .btn-logout)
- [ ] T038 [P] [US2] Add .btn-logout CSS styles to laravel-app/resources/css/app.css or inline styles (red background, white text, hover effect)

### Tasks - Feature Tests

- [ ] T039 [P] [US2] Write test_logout_clears_session_and_redirects in laravel-app/tests/Feature/AuthTest.php (set session authenticated=true, POST /logout, assert redirects to /login, session authenticated is false)
- [ ] T040 [US2] Run php artisan test --filter=AuthTest::test_logout from laravel-app/ and verify test passes

### Tasks - E2E Tests

- [ ] T041 [P] [US2] Write E2E test for logout flow in laravel-app/tests/e2e/auth.spec.ts (login, click logout button, assert redirected to /login, navigate to /expenses, assert redirected to /login)
- [ ] T042 [US2] Run npx playwright test tests/e2e/auth.spec.ts --grep logout from laravel-app/ and verify test passes

---

## Phase 5: User Story 3 - Session Persistence (Priority: P3)

**Story Goal**: Login sessions persist across page reloads and navigation, so users don't need to re-authenticate for every action.

**Independent Test**:
- Login successfully
- Navigate between expense pages (list, create, daily, monthly)
- Refresh browser
- Session remains active (no redirect to login)

**Acceptance Criteria** (from spec.md):
1. Session persists across page navigation
2. Session persists across browser refresh
3. Session persists when reopening tab (within timeout period)

### Tasks - Feature Tests

- [ ] T043 [P] [US3] Write test_session_persists_across_requests in laravel-app/tests/Feature/AuthTest.php (login, access multiple routes, assert session authenticated remains true)
- [ ] T044 [US3] Run php artisan test --filter=AuthTest::test_session_persists from laravel-app/ and verify test passes

### Tasks - E2E Tests

- [ ] T045 [P] [US3] Write E2E test for session persistence in laravel-app/tests/e2e/auth.spec.ts (login, reload page, assert still at /expenses, logout button visible)
- [ ] T046 [US3] Run npx playwright test tests/e2e/auth.spec.ts --grep persistence from laravel-app/ and verify test passes

---

## Phase 6: Security - Rate Limiting (Cross-Cutting)

**Goal**: Protect against brute force attacks with rate limiting

**Independent Test**:
- Attempt 6 failed logins with same username
- 6th attempt shows rate limit error
- Wait or login with different user works

### Tasks - Feature Tests

- [ ] T047 [P] Write test_rate_limiting_blocks_after_5_username_attempts in laravel-app/tests/Feature/AuthTest.php (attempt 5 failed logins with same username, 6th attempt shows error containing "Too many")
- [ ] T048 [P] Write test_rate_limiting_blocks_after_10_ip_attempts in laravel-app/tests/Feature/AuthTest.php (attempt 10 failed logins with different usernames, 11th attempt shows error)
- [ ] T049 Write test_successful_login_clears_username_rate_limit in laravel-app/tests/Feature/AuthTest.php (fail 3 times, succeed once, fail again - should allow since counter was cleared)
- [ ] T050 Run php artisan test --filter=AuthTest from laravel-app/ and verify all rate limiting tests pass

---

## Phase 7: Polish & Production Configuration

**Goal**: Prepare for production deployment with proper configuration and documentation

### Tasks - Configuration

- [ ] T051 [P] Document GitHub secrets setup in laravel-app/README.md (add section explaining USERNAME and PASSWORD secrets configuration)
- [ ] T052 [P] Update .github/workflows/laravel.yml to inject USERNAME and PASSWORD from secrets (add env section with ${{ secrets.USERNAME }} and ${{ secrets.PASSWORD }})
- [ ] T053 [P] Document production session configuration in laravel-app/README.md (recommend SESSION_DRIVER=redis, CACHE_DRIVER=redis, SESSION_SECURE_COOKIE=true)

### Tasks - Code Quality

- [ ] T054 [P] Run composer lint from laravel-app/ and fix any Pint style issues
- [ ] T055 [P] Run composer analyze from laravel-app/ and fix any Larastan warnings
- [ ] T056 Run php artisan test from laravel-app/ and verify all tests pass (100% success rate)
- [ ] T057 Run npx playwright test from laravel-app/ and verify all E2E tests pass

### Tasks - Documentation

- [ ] T058 Verify laravel-app/.env.example includes USERNAME and PASSWORD with example values
- [ ] T059 Verify laravel-app/README.md includes authentication setup instructions
- [ ] T060 Verify .github/copilot-instructions.md includes authentication patterns (already updated in planning phase)

---

## Dependencies & Execution Order

### Story Completion Order

1. **Phase 1 (Setup)** → MUST complete first
2. **Phase 2 (Foundational)** → MUST complete before user stories
3. **Phase 3 (US1)** → MVP - can be deployed independently
4. **Phase 4 (US2)** → Depends on US1 (needs login to test logout)
5. **Phase 5 (US3)** → Depends on US1 (needs login to test persistence)
6. **Phase 6 (Rate Limiting)** → Can run parallel with US2/US3 (independent security feature)
7. **Phase 7 (Polish)** → Final phase before merge

### Parallel Opportunities

Tasks marked with `[P]` can be executed in parallel (different files, no dependencies):

**Parallel Batch 1** (After Phase 2):
- T011-T012 (views)
- T013-T018 (controller logic)
- Can be done by different developers simultaneously

**Parallel Batch 2** (After T019-T021 routes done):
- T022-T028 (feature tests)
- T031-T035 (E2E tests)
- Can be done simultaneously

**Parallel Batch 3** (After US1 complete):
- T036 (logout controller)
- T037-T038 (layout updates)
- T039-T040 (logout tests)
- All independent modifications

**Parallel Batch 4** (US2 and US3 can run in parallel):
- US2 tasks (T036-T042)
- US3 tasks (T043-T046)
- Rate limiting tasks (T047-T050)
- All three user stories can be implemented simultaneously by different developers

**Parallel Batch 5** (Polish phase):
- T051-T053 (documentation)
- T054-T055 (code quality)
- Can run in parallel

---

## Implementation Strategy

### MVP First Approach

**Minimum Viable Product**: Phase 1 + Phase 2 + Phase 3 (User Story 1)

This delivers:
- ✅ Login functionality
- ✅ Protected expense routes
- ✅ Session management
- ✅ Error handling
- ✅ Basic security (timing-safe comparison)

**Total MVP Tasks**: 35 (T001-T035)  
**Estimated Time**: 2 hours

### Incremental Delivery

After MVP, each phase adds value independently:

- **+US2 (Logout)**: 7 tasks, ~30 minutes
- **+US3 (Persistence)**: 4 tasks, ~20 minutes  
- **+Rate Limiting**: 4 tasks, ~20 minutes
- **+Polish**: 10 tasks, ~30 minutes

**Total Implementation**: 60 tasks, ~3.5 hours

---

## Task Summary

| Phase | Task Count | P1 | P2 | P3 | Security | Polish |
|-------|-----------|----|----|----|---------| --------|
| Phase 1: Setup | 5 | ✓ | | | | |
| Phase 2: Foundation | 5 | ✓ | | | | |
| Phase 3: US1 Login | 25 | ✓ | | | | |
| Phase 4: US2 Logout | 7 | | ✓ | | | |
| Phase 5: US3 Persistence | 4 | | | ✓ | | |
| Phase 6: Rate Limiting | 4 | | | | ✓ | |
| Phase 7: Polish | 10 | | | | | ✓ |
| **Total** | **60** | **35** | **7** | **4** | **4** | **10** |

---

## Validation Checklist

### Pre-Implementation
- [x] All constitution checks pass (verified in plan.md)
- [x] Test scenarios defined in spec.md
- [x] API contracts documented
- [x] Quickstart guide available
- [ ] Development environment ready (USERNAME/PASSWORD in .env)

### During Implementation (Per Phase)
- [ ] Write tests first (feature + E2E)
- [ ] Implement minimum code to pass tests
- [ ] Run tests continuously
- [ ] Refactor while tests stay green
- [ ] Run Pint and Larastan before commit

### Post-Implementation
- [ ] All 60 tasks completed and checked
- [ ] php artisan test passes (100% success)
- [ ] npx playwright test passes (all E2E)
- [ ] composer lint passes (PSR-12 compliance)
- [ ] composer analyze passes (no type errors)
- [ ] Manual testing checklist complete
- [ ] Documentation updated
- [ ] GitHub secrets configured
- [ ] Ready for PR to main

---

## Testing Coverage

| Test Type | File | Test Count | Coverage |
|-----------|------|-----------|----------|
| Feature | AuthTest.php | 10 tests | All auth scenarios |
| Integration | ExpenseControllerTest.php | Updated setUp | Expense routes with auth |
| E2E | auth.spec.ts | 5 tests | All user flows |
| **Total** | **3 files** | **15+ tests** | **100% auth functionality** |

---

## References

- Feature Specification: [spec.md](spec.md)
- Implementation Plan: [plan.md](plan.md)
- Developer Quickstart: [quickstart.md](quickstart.md)
- API Contracts: [contracts/http-api.md](contracts/http-api.md)
- Data Model: [data-model.md](data-model.md)
- Research: [research.md](research.md)

---

## Ready to Implement

All planning complete. Begin implementation by following tasks in order (T001 → T060), or implement user stories incrementally (US1 → US2 → US3). Each task includes specific file paths and implementation details.

For step-by-step guidance, refer to [quickstart.md](quickstart.md).
