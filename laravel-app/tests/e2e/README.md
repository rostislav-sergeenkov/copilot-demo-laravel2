# End-to-End Testing with Playwright

This directory contains Playwright end-to-end tests for the Expense Tracker application, based on the acceptance criteria defined in `.github/copilot-acceptance-checklist.md`.

## 📁 Test Structure

```
tests/e2e/
├── helpers.ts              # Shared test utilities and helper functions
├── crud.spec.ts            # F1: Expense CRUD Interface tests
├── daily-view.spec.ts      # F2: Daily Expenses View tests
├── monthly-view.spec.ts    # F3: Monthly Expenses View tests
├── filtering.spec.ts       # F4: Category Filtering tests
├── validation.spec.ts      # Data Validation tests
└── ui-accessibility.spec.ts # UI and Accessibility tests
```

## 🚀 Getting Started

### Installation

1. Install dependencies:
```bash
cd laravel-app
npm install
```

2. Install Playwright browsers:
```bash
npx playwright install
```

### Running Tests

#### Run all tests
```bash
npm run test:e2e
```

#### Run tests with UI mode (recommended for development)
```bash
npm run test:e2e:ui
```

#### Run tests in headed mode (see the browser)
```bash
npm run test:e2e:headed
```

#### Run tests in debug mode
```bash
npm run test:e2e:debug
```

#### Run specific test file
```bash
npx playwright test crud.spec.ts
```

#### Run tests in a specific browser
```bash
npx playwright test --project=chromium
npx playwright test --project=firefox
npx playwright test --project=webkit
```

## 🎯 Test Coverage

The test suite covers all acceptance criteria from the checklist:

### F1: Expense CRUD Interface (crud.spec.ts)
- ✅ Create Expense - form validation, submission, navigation
- ✅ Read Expenses - index page display, sorting, pagination
- ✅ Update Expense - pre-population, editing, saving
- ✅ Delete Expense - confirmation, soft-delete behavior

### F2: Daily Expenses View (daily-view.spec.ts)
- ✅ Date selection and navigation (previous/next/today)
- ✅ Daily totals calculation
- ✅ Category breakdown
- ✅ Category filtering
- ✅ Empty states

### F3: Monthly Expenses View (monthly-view.spec.ts)
- ✅ Month selection and navigation
- ✅ Monthly totals calculation
- ✅ Category percentages (sum = 100%)
- ✅ Empty states

### F4: Category Filtering (filtering.spec.ts)
- ✅ Filter dropdown on all views
- ✅ Filter application and clearing
- ✅ Total updates when filtered
- ✅ Filter persistence in URL

### Data Validation (validation.spec.ts)
- ✅ Description field (required, max 255 chars, Unicode support)
- ✅ Amount field (required, min $0.01, max $999,999.99, decimal support)
- ✅ Category field (required, valid values only)
- ✅ Date field (required, no future dates, within 5 years)
- ✅ Error message display (inline and flash messages)
- ✅ Form repopulation after errors
- ✅ Server-side validation

### UI & Accessibility (ui-accessibility.spec.ts)
- ✅ Layout and navigation
- ✅ Material Design compliance
- ✅ Responsive design (desktop, laptop, tablet, mobile)
- ✅ Keyboard navigation
- ✅ Screen reader support (ARIA labels, semantic HTML)
- ✅ Visual accessibility (color contrast, text resize)
- ✅ Performance (page load times)

## 🔧 Configuration

The Playwright configuration is in `playwright.config.ts` and includes:

- **Test directory**: `./tests/e2e`
- **Base URL**: `http://127.0.0.1:8000` (configurable via `APP_URL` env var)
- **Browsers**: Chromium, Firefox, WebKit
- **Mobile devices**: Pixel 5, iPhone 12, iPad Pro
- **Auto-start Laravel server**: Runs `php artisan serve` before tests
- **Retries**: 2 retries on CI, 0 locally
- **Screenshots**: Captured on failure
- **Videos**: Recorded on failure

## 📊 Test Reports

After running tests, view the HTML report:

```bash
npx playwright show-report
```

Reports include:
- Test results with pass/fail status
- Screenshots of failures
- Video recordings of failed tests
- Execution traces for debugging

## 🛠️ Helper Functions

The `helpers.ts` file provides reusable utilities:

- `createExpense()` - Create an expense through the UI
- `deleteExpense()` - Delete an expense
- `navigateToDailyView()` - Navigate to daily view with date
- `navigateToMonthlyView()` - Navigate to monthly view with month
- `applyFilter()` - Apply category filter
- `expectSuccessMessage()` - Verify success messages
- `expectErrorMessage()` - Verify error messages
- Date/currency formatting utilities

## 🎨 Writing New Tests

When adding new tests:

1. Follow the existing test structure
2. Use helpers from `helpers.ts` for common operations
3. Use descriptive test names that match acceptance criteria
4. Group related tests with `test.describe()`
5. Clean up any created test data if needed

Example:
```typescript
import { test, expect } from '@playwright/test';
import { createExpense, getTodayString } from './helpers';

test.describe('My Feature', () => {
  test('should do something specific', async ({ page }) => {
    await createExpense(page, {
      description: 'Test expense',
      amount: '10.00',
      category: 'Groceries',
      date: getTodayString(),
    });
    
    // Your test assertions
    await expect(page.locator('...')).toBeVisible();
  });
});
```

## 🐛 Debugging Tests

### Using UI Mode (Recommended)
```bash
npm run test:e2e:ui
```
UI mode provides:
- Time travel debugging
- Watch mode
- Visual test runner
- Step-by-step execution

### Using Debug Mode
```bash
npm run test:e2e:debug
```
Opens Playwright Inspector for step-by-step debugging.

### View Trace Files
If a test fails, you can view the trace:
```bash
npx playwright show-trace test-results/path-to-trace.zip
```

## 🔄 CI/CD Integration

The tests are configured to run in CI environments:

- Automatic retries (2x) on CI
- JSON reporter for pipeline integration
- Headless mode by default
- Screenshot and video capture on failure

To run tests in CI mode locally:
```bash
CI=true npx playwright test
```

## 📝 Database Considerations

Each test should:
- Use a fresh database state (Laravel's RefreshDatabase or similar)
- Clean up test data if needed
- Not depend on data from other tests
- Be able to run in parallel

**Note**: Currently, tests create data through the UI. For faster execution, consider:
- Using Laravel's database seeding
- Creating an API endpoint for test data setup
- Using database transactions

## 🚨 Common Issues

### Laravel server not starting
- Ensure you're in the `laravel-app` directory
- Check that port 8000 is not already in use
- Verify `.env` file is configured correctly

### Tests failing on CI but passing locally
- Check browser versions match
- Verify environment variables
- Review timing issues (increase timeouts if needed)

### Flaky tests
- Add explicit waits: `await page.waitForSelector()`
- Use `waitForLoadState('networkidle')`
- Check for race conditions

## 📚 Resources

- [Playwright Documentation](https://playwright.dev/)
- [Playwright Best Practices](https://playwright.dev/docs/best-practices)
- [Acceptance Checklist](../.github/copilot-acceptance-checklist.md)
- [Laravel Testing](https://laravel.com/docs/testing)

## ✅ Running the Full Test Suite

To run both PHPUnit (backend) and Playwright (E2E) tests:

```bash
# Backend tests
php artisan test

# E2E tests
npm run test:e2e
```

For a complete test run before deployment:
```bash
php artisan test && npm run test:e2e
```
