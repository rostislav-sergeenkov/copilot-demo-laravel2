# E2E Test Authentication

## Overview

E2E tests use a secure authentication mechanism that doesn't require storing plain text passwords. The system uses a test-only endpoint that accepts the hashed password directly from the environment.

## How It Works

1. **Test Endpoint** (`/test/auth`): Only available in `local` and `testing` environments
   - Accepts `username` and `password_hash` from `.env`
   - Validates credentials against environment variables
   - Sets authenticated session for the test

2. **Login Helper** (`tests/e2e/helpers.ts`): 
   - Reads `AUTH_USERNAME` and `PASSWORD_HASH` from environment
   - Calls `/test/auth` endpoint to establish session
   - Navigates to expenses page to activate session cookies

3. **Automatic Authentication**: 
   - `test.beforeEach()` hook logs in before each test
   - Tests run with authenticated session

## Security

✅ **Secure**: No plain text passwords stored
✅ **Environment-specific**: Test endpoint only available in local/testing
✅ **Production-safe**: Test endpoint automatically disabled in production

## Configuration

The following environment variables are used (from `.env`):

```env
AUTH_USERNAME=admin
PASSWORD_HASH=$2y$12$KWIPlkc0vIvtfAxLMhy6VOXo7EkHYH7lfv0OQAFbPvlPzvJ6Njbpy
```

These are automatically passed to Playwright via `playwright.config.ts`.

## Usage

```typescript
import { login } from './helpers';

test.beforeEach(async ({ page }) => {
  await login(page);
});
```

## Files Modified

- `app/Http/Controllers/TestAuthController.php` - Test authentication controller
- `routes/web.php` - Test endpoint route (environment-gated)
- `tests/e2e/helpers.ts` - Login helper function
- `tests/e2e/happy-path.spec.ts` - BeforeEach hook added
- `playwright.config.ts` - Environment variable configuration
