import { defineConfig, devices } from '@playwright/test';
import { fileURLToPath } from 'url';
import path from 'path';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

/**
 * Playwright configuration for Laravel Expense Tracker E2E tests
 * 
 * By default, only happy-path.spec.ts runs for faster testing.
 * To run all tests: npx playwright test --grep-invert "@skip"
 * 
 * See https://playwright.dev/docs/test-configuration
 */
export default defineConfig({
  testDir: './tests/e2e',
  
  // Run only happy path tests by default (use --grep="" to run all)
  testMatch: process.env.TEST_ALL ? '**/*.spec.ts' : '**/happy-path.spec.ts',

  // Maximum time one test can run for
  timeout: 30 * 1000,
  
  // Run tests in files in parallel
  fullyParallel: true,
  
  // Fail the build on CI if you accidentally left test.only in the source code
  forbidOnly: !!process.env.CI,
  
  // Retry on CI only
  retries: process.env.CI ? 2 : 0,
  
  // Opt out of parallel tests on CI
  workers: process.env.CI ? 1 : undefined,
  
  // Reporter to use
  reporter: [
    ['html'],
    ['list'],
    // Add JSON reporter for CI/CD pipelines
    process.env.CI ? ['json', { outputFile: 'playwright-report/results.json' }] : null
  ].filter(Boolean),
  
  // Shared settings for all the projects below
  use: {
    // Base URL for the Laravel application
    baseURL: process.env.APP_URL || 'http://127.0.0.1:8000',
    
    // Collect trace when retrying the failed test
    trace: 'off',
    
    // Screenshot on failure
    screenshot: 'off',
    
    // Video on failure
    video: 'off',
  },

  // Configure projects for major browsers
  projects: [
    {
      name: 'chromium',
      use: { 
        ...devices['Desktop Chrome'],
        // Viewport for desktop testing
        viewport: { width: 1440, height: 900 },
      },
    },
  ],

  // Run your local Laravel dev server before starting the tests
  // Note: Comment out webServer to manually start server
  /*
  webServer: {
    command: 'php artisan serve',
    url: 'http://127.0.0.1:8000',
    reuseExistingServer: !process.env.CI,
    timeout: 120 * 1000,
    stdout: 'ignore',
    stderr: 'pipe',
    cwd: __dirname,
  },
  */
});
