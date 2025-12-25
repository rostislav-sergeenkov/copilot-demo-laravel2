# Quick Start: E2E Testing

## ⚡ TL;DR - Happy Path (Fast Testing)

```bash
cd laravel-app
npm install && npx playwright install
npm run test:e2e  # Runs 16 core tests in ~2-3 minutes
```

## 📖 Testing Strategy

By default, E2E tests run **only Happy Path tests** (core business flows) for speed:
- ✅ **Default**: 16 tests (~2-3 minutes) - `npm run test:e2e`
- 🔍 **Comprehensive**: 80+ tests (~15-20 minutes) - `npm run test:e2e:all`

📖 See [E2E Happy Path Strategy](docs/E2E-Happy-Path-Strategy.md) for details.

---

## 🚀 First Time Setup

### 1. Install Dependencies
```bash
# From the project root
cd laravel-app
npm install
npx playwright install
```

Or use the setup script (Windows):
```powershell
.\setup-e2e-tests.ps1
```

### 2. Ensure Laravel is Running
Make sure your Laravel application is set up:
```bash
cd laravel-app
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed  # Optional: seed sample data
```

## 🎯 Running Tests

### ⚡ Default: Happy Path Only (Recommended)
```bash
npm run test:e2e
```
Runs 16 core tests covering essential user workflows.

### 🔍 All Comprehensive Tests
```bash
npm run test:e2e:all
```
Runs 80+ tests including edge cases, validation, and UI details.

### 🎨 Recommended: UI Mode
Best for development and debugging:
```bash
npm run test:e2e:ui
```

### Run Specific Test File
```bash
npx playwright test happy-path.spec.ts  # Happy path only
npx playwright test crud.spec.ts        # Detailed CRUD tests
npx playwright test validation.spec.ts  # Validation tests
```

### Run in Headed Mode (See Browser)
```bash
npm run test:e2e:headed
```

### Debug Mode
Step through tests:
```bash
npm run test:e2e:debug
```

### Run Specific Browser
```bash
npx playwright test --project=chromium
npx playwright test --project=firefox
npx playwright test --project=webkit
```

## 📊 View Test Results

After running tests:
```bash
npx playwright show-report
```

## 🔍 Test Structure

```
tests/e2e/
├── crud.spec.ts            # Create, Read, Update, Delete tests
├── daily-view.spec.ts      # Daily expenses view tests
├── monthly-view.spec.ts    # Monthly expenses view tests
├── filtering.spec.ts       # Category filtering tests
├── validation.spec.ts      # Form validation tests
├── ui-accessibility.spec.ts # UI and a11y tests
└── helpers.ts              # Shared test utilities
```

## 📖 Documentation

- **Detailed Guide**: [tests/e2e/README.md](laravel-app/tests/e2e/README.md)
- **Test Coverage**: [TESTING.md](TESTING.md)
- **Acceptance Criteria**: [.github/copilot-acceptance-checklist.md](.github/copilot-acceptance-checklist.md)

## 🐛 Common Issues

### Port 8000 Already in Use
```bash
# Find and kill the process
netstat -ano | findstr :8000
taskkill /PID <PID> /F
```

### Database Locked
```bash
# Reset database
cd laravel-app
rm database/database.sqlite
touch database/database.sqlite
php artisan migrate:fresh --seed
```

### Tests Failing Randomly
- Increase timeout in specific test
- Use `await page.waitForLoadState('networkidle')`
- Check network conditions

## 💡 Pro Tips

1. **Use UI Mode** during development - it's the best way to debug tests
2. **Run specific tests** while developing - faster iteration
3. **Check traces** for failed tests in CI - download artifacts from GitHub Actions
4. **Use headed mode** to see what's happening in the browser
5. **Filter tests** by name: `npx playwright test -g "should create expense"`

## 🎨 Writing New Tests

```typescript
import { test, expect } from '@playwright/test';
import { createExpense } from './helpers';

test.describe('My Feature', () => {
  test('should do something', async ({ page }) => {
    // Your test code
  });
});
```

## 🔄 CI/CD

Tests run automatically on:
- Push to `main` or `develop` branches
- Pull requests to `main`

View results in GitHub Actions tab.

## 📝 Test Reports

- **HTML Report**: `playwright-report/index.html`
- **JSON Results**: `playwright-report/results.json` (CI only)
- **Screenshots**: `test-results/*/screenshots/`
- **Videos**: `test-results/*/videos/`
- **Traces**: `test-results/*/traces/`

## 🆘 Need Help?

- [Playwright Documentation](https://playwright.dev/)
- [Project README](README.md)
- [Issue Tracker](https://github.com/your-repo/issues)
