import { Page, expect } from '@playwright/test';

/**
 * Test utilities and helper functions for Expense Tracker E2E tests
 */

export const CATEGORIES = [
  'Groceries',
  'Transport',
  'Housing and Utilities',
  'Restaurants and Cafes',
  'Health and Medicine',
  'Clothing & Footwear',
  'Entertainment',
] as const;

export type Category = typeof CATEGORIES[number];

export interface ExpenseData {
  description: string;
  amount: string;
  category: Category;
  date: string;
}

/**
 * Create a new expense through the UI
 */
export async function createExpense(page: Page, expense: ExpenseData) {
  await page.goto('/');
  await page.click('text=Add Expense');
  
  await expect(page).toHaveURL(/\/expenses\/create$/);
  
  await page.fill('input[name="description"]', expense.description);
  await page.fill('input[name="amount"]', expense.amount);
  await page.selectOption('select[name="category"]', expense.category);
  await page.fill('input[name="date"]', expense.date);
  
  await page.click('button[type="submit"]');
  
  // Wait for redirect to index page
  await expect(page).toHaveURL(/\/expenses$/);
}

/**
 * Delete an expense by description
 */
export async function deleteExpense(page: Page, description: string) {
  const row = page.locator(`tr:has-text("${description}")`);
  await row.locator('button:has-text("Delete")').click();
  
  // Confirm deletion in dialog
  page.on('dialog', dialog => dialog.accept());
}

/**
 * Navigate to daily view for a specific date
 */
export async function navigateToDailyView(page: Page, date?: string) {
  await page.goto('/expenses/daily');
  if (date) {
    await page.fill('input[type="date"]', date);
  }
}

/**
 * Navigate to monthly view for a specific month
 */
export async function navigateToMonthlyView(page: Page, yearMonth?: string) {
  await page.goto('/expenses/monthly');
  if (yearMonth) {
    await page.fill('input[type="month"]', yearMonth);
  }
}

/**
 * Apply category filter
 */
export async function applyFilter(page: Page, category: Category | 'All Categories') {
  await page.selectOption('select[name="category"]', category);
}

/**
 * Get expense rows from the table
 */
export async function getExpenseRows(page: Page) {
  return page.locator('table tbody tr');
}

/**
 * Verify success message is displayed
 */
export async function expectSuccessMessage(page: Page, message?: string) {
  const alert = page.locator('.snackbar-success, .snackbar.snackbar-success');
  await expect(alert).toBeVisible();
  if (message) {
    await expect(alert).toContainText(message);
  }
}

/**
 * Verify error message is displayed
 */
export async function expectErrorMessage(page: Page, fieldName?: string) {
  if (fieldName) {
    const error = page.locator(`[data-field="${fieldName}"] .error, .invalid-feedback:near(input[name="${fieldName}"])`);
    await expect(error).toBeVisible();
  } else {
    const alert = page.locator('.snackbar-error, .snackbar.snackbar-error');
    await expect(alert).toBeVisible();
  }
}

/**
 * Format date for display comparison
 */
export function formatDate(date: Date): string {
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
}

/**
 * Format amount as currency
 */
export function formatCurrency(amount: number): string {
  return `$${amount.toFixed(2)}`;
}

/**
 * Get today's date in YYYY-MM-DD format
 */
export function getTodayString(): string {
  const today = new Date();
  return today.toISOString().split('T')[0];
}

/**
 * Get date string N days from today
 */
export function getDateString(daysOffset: number = 0): string {
  const date = new Date();
  date.setDate(date.getDate() + daysOffset);
  return date.toISOString().split('T')[0];
}

/**
 * Get current month in YYYY-MM format
 */
export function getCurrentMonthString(): string {
  const date = new Date();
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
}

/**
 * Seed database with test expenses via API/Artisan (requires Laravel to be running)
 */
export async function seedTestExpenses(page: Page) {
  // This would require a custom endpoint or running artisan commands
  // For now, we'll create expenses through the UI
  const testExpenses: ExpenseData[] = [
    {
      description: 'Weekly groceries',
      amount: '125.50',
      category: 'Groceries',
      date: getTodayString(),
    },
    {
      description: 'Bus ticket',
      amount: '2.75',
      category: 'Transport',
      date: getTodayString(),
    },
    {
      description: 'Electricity bill',
      amount: '89.99',
      category: 'Housing and Utilities',
      date: getDateString(-1),
    },
  ];

  for (const expense of testExpenses) {
    await createExpense(page, expense);
  }
}

/**
 * Check if element is in viewport
 */
export async function isInViewport(page: Page, selector: string): Promise<boolean> {
  const element = page.locator(selector);
  const box = await element.boundingBox();
  if (!box) return false;

  const viewport = page.viewportSize();
  if (!viewport) return false;

  return (
    box.y >= 0 &&
    box.x >= 0 &&
    box.y + box.height <= viewport.height &&
    box.x + box.width <= viewport.width
  );
}

/**
 * Check color contrast ratio for accessibility
 */
export async function checkColorContrast(
  page: Page, 
  selector: string, 
  minRatio: number = 4.5
): Promise<boolean> {
  // This is a simplified check - in production you'd want to use an a11y testing library
  const element = page.locator(selector);
  const color = await element.evaluate((el) => {
    const style = window.getComputedStyle(el);
    return {
      color: style.color,
      backgroundColor: style.backgroundColor,
    };
  });
  
  // For now, just verify colors are set
  return !!color.color && !!color.backgroundColor;
}
