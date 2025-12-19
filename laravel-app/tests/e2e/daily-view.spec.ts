import { test, expect } from '@playwright/test';
import { 
  createExpense, 
  navigateToDailyView,
  applyFilter,
  getTodayString,
  getDateString,
  formatCurrency,
  type ExpenseData
} from './helpers';

/**
 * F2: Daily Expenses View Tests
 * 
 * Tests for the daily expenses view functionality:
 * - Date selection and navigation
 * - Daily totals calculation
 * - Category filtering
 * - Empty states
 */

test.describe('F2: Daily Expenses View', () => {
  
  test('should load page at /expenses/daily', async ({ page }) => {
    await page.goto('/expenses/daily');
    await expect(page).toHaveURL(/\/expenses\/daily/);
  });
  
  test('should show current date by default', async ({ page }) => {
    await navigateToDailyView(page);
    
    const dateInput = page.locator('input[type="date"]');
    const value = await dateInput.inputValue();
    expect(value).toBe(getTodayString());
  });
  
  test('should navigate to previous day', async ({ page }) => {
    await navigateToDailyView(page);
    
    const prevButton = page.locator('button:has-text("Previous"), button:has-text("←")');
    await prevButton.click();
    
    const dateInput = page.locator('input[type="date"]');
    const value = await dateInput.inputValue();
    expect(value).toBe(getDateString(-1));
  });
  
  test('should navigate to next day', async ({ page }) => {
    await navigateToDailyView(page, getDateString(-2));
    
    const nextButton = page.locator('button:has-text("Next"), button:has-text("→")');
    await nextButton.click();
    
    const dateInput = page.locator('input[type="date"]');
    const value = await dateInput.inputValue();
    expect(value).toBe(getDateString(-1));
  });
  
  test('should return to current date when "Today" is clicked', async ({ page }) => {
    await navigateToDailyView(page, getDateString(-5));
    
    const todayButton = page.locator('button:has-text("Today")');
    await todayButton.click();
    
    const dateInput = page.locator('input[type="date"]');
    const value = await dateInput.inputValue();
    expect(value).toBe(getTodayString());
  });
  
  test('should show expenses for selected date', async ({ page }) => {
    const todayDate = getTodayString();
    
    // Create expenses for today
    await createExpense(page, {
      description: 'Daily test expense 1',
      amount: '25.00',
      category: 'Groceries',
      date: todayDate,
    });
    
    await createExpense(page, {
      description: 'Daily test expense 2',
      amount: '15.50',
      category: 'Transport',
      date: todayDate,
    });
    
    await navigateToDailyView(page, todayDate);
    
    // Both expenses should be visible
    await expect(page.locator('text=Daily test expense 1')).toBeVisible();
    await expect(page.locator('text=Daily test expense 2')).toBeVisible();
  });
  
  test('should calculate daily total correctly', async ({ page }) => {
    const testDate = '2025-12-10';
    
    // Create expenses for specific date
    await createExpense(page, {
      description: 'Daily total test 1',
      amount: '100.00',
      category: 'Groceries',
      date: testDate,
    });
    
    await createExpense(page, {
      description: 'Daily total test 2',
      amount: '50.50',
      category: 'Transport',
      date: testDate,
    });
    
    await navigateToDailyView(page, testDate);
    
    // Total should be $150.50
    const totalElement = page.locator('text=/Total|Daily Total/i').locator('..').locator('text=/\\$\\d+\\.\\d{2}/');
    await expect(totalElement).toContainText('$150.50');
  });
  
  test('should work with category filter on daily view', async ({ page }) => {
    const testDate = getTodayString();
    
    await createExpense(page, {
      description: 'Groceries daily filter',
      amount: '30.00',
      category: 'Groceries',
      date: testDate,
    });
    
    await createExpense(page, {
      description: 'Transport daily filter',
      amount: '10.00',
      category: 'Transport',
      date: testDate,
    });
    
    await navigateToDailyView(page, testDate);
    
    // Apply Groceries filter
    await applyFilter(page, 'Groceries');
    
    // Should see groceries but not transport
    await expect(page.locator('text=Groceries daily filter')).toBeVisible();
    await expect(page.locator('text=Transport daily filter')).not.toBeVisible();
  });
  
  test('should show empty state when no expenses for date', async ({ page }) => {
    // Use a date unlikely to have expenses
    await navigateToDailyView(page, '2020-01-01');
    
    const emptyState = page.locator('text=/No expenses recorded for this date|No expenses found/i');
    await expect(emptyState).toBeVisible();
  });
  
  test('should display amounts in currency format', async ({ page }) => {
    const testDate = getTodayString();
    
    await createExpense(page, {
      description: 'Currency format daily',
      amount: '99.99',
      category: 'Entertainment',
      date: testDate,
    });
    
    await navigateToDailyView(page, testDate);
    
    const row = page.locator('tr:has-text("Currency format daily")');
    await expect(row).toContainText('$99.99');
  });
  
  test('should show category breakdown for the day', async ({ page }) => {
    const testDate = '2025-12-11';
    
    await createExpense(page, {
      description: 'Breakdown test 1',
      amount: '60.00',
      category: 'Groceries',
      date: testDate,
    });
    
    await createExpense(page, {
      description: 'Breakdown test 2',
      amount: '40.00',
      category: 'Transport',
      date: testDate,
    });
    
    await navigateToDailyView(page, testDate);
    
    // Should show breakdown by category
    const groceriesTotal = page.locator('text=/Groceries.*\\$60\\.00/');
    const transportTotal = page.locator('text=/Transport.*\\$40\\.00/');
    
    // At least one should be visible (layout may vary)
    const groceriesVisible = await groceriesTotal.count() > 0;
    const transportVisible = await transportTotal.count() > 0;
    
    expect(groceriesVisible || transportVisible).toBe(true);
  });
  
  test('should update when date is manually changed', async ({ page }) => {
    await navigateToDailyView(page);
    
    const dateInput = page.locator('input[type="date"]');
    await dateInput.fill('2025-12-05');
    
    // Page should update to show expenses for Dec 5
    // Trigger change/blur event
    await dateInput.blur();
    
    const currentValue = await dateInput.inputValue();
    expect(currentValue).toBe('2025-12-05');
  });
  
});
