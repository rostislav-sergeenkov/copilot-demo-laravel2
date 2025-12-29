import { defineConfig, devices } from '@playwright/test';
import { fileURLToPath } from 'url';
import path from 'path';
import fs from 'fs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

// Load Laravel .env file for authentication credentials
function loadEnv() {
  const envPath = path.join(__dirname, '.env');

  if (!fs.existsSync(envPath)) {
    console.warn('.env file not found at:', envPath);
    return {};
  }

  const envContent = fs.readFileSync(envPath, 'utf-8');

  const envVars: Record<string, string> = {};

  envContent.split('\n').forEach((line, index) => {
    const trimmed = line.trim();

    // Skip empty lines and comments
    if (!trimmed || trimmed.startsWith('#')) return;

    // Find first = sign
    const equalsIndex = trimmed.indexOf('=');
    if (equalsIndex === -1) return;

    const key = trimmed.substring(0, equalsIndex).trim();
    const value = trimmed.substring(equalsIndex + 1).trim();

    if (key && value) {
      envVars[key] = value;
    }
  });

  return envVars;
}

const laravelEnv = loadEnv();

// Get credentials from environment variables (CI/CD) or .env file (local)
const AUTH_USERNAME = process.env.AUTH_USERNAME || laravelEnv.AUTH_USERNAME;
const TEST_PASSWORD = process.env.TEST_PASSWORD || laravelEnv.TEST_PASSWORD;

// Validate required environment variables
if (!AUTH_USERNAME || !TEST_PASSWORD) {
  throw new Error(
    'AUTH_USERNAME and TEST_PASSWORD must be set via environment variables or .env file. ' +
    `Missing: ${!AUTH_USERNAME ? 'AUTH_USERNAME ' : ''}${!TEST_PASSWORD ? 'TEST_PASSWORD' : ''}`
  );
}

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
  timeout: 60 * 1000,
  
  // Run tests in files in parallel
  fullyParallel: false,
  
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

  // Set process.env before tests run
  globalSetup: undefined,

  // Make credentials available via process.env
  ...(() => {
    // Set in Node.js process.env so tests can access them
    process.env.AUTH_USERNAME = AUTH_USERNAME;
    process.env.TEST_PASSWORD = TEST_PASSWORD;
    return {};
  })(),

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
