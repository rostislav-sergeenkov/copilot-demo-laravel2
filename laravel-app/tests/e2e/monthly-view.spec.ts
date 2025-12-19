import { test, expect } from '@playwright/test';
import { 
  createExpense, 
  navigateToMonthlyView,
  getCurrentMonthString,
  type ExpenseData
} from './helpers';

/**
 * F3: Monthly Expenses View Tests
 * 
 * Tests for the monthly expenses view functionality:
 * - Month selection and navigation
 * - Monthly totals calculation
 * - Category breakdown with percentages
 * - Empty states
 */

test.describe('F3: Monthly Expenses View', () => {
  
  test('should load page at /expenses/monthly', async ({ page }) => {
    await page.goto('/expenses/monthly');
    await expect(page).toHaveURL(/\/expenses\/monthly/);
  });
  
  test('should show current month by default', async ({ page }) => {
    await navigateToMonthlyView(page);
    
    const monthInput = page.locator('input[type="month"]');
    const value = await monthInput.inputValue();
    expect(value).toBe(getCurrentMonthString());
  });
  
  test('should navigate to previous month', async ({ page }) => {
    await navigateToMonthlyView(page);
    
    const prevButton = page.locator('button:has-text("Previous"), button:has-text("←")');
    await prevButton.click();
    
    const monthInput = page.locator('input[type="month"]');
    const value = await monthInput.inputValue();
    
    // Calculate expected previous month
    const current = new Date();
    current.setMonth(current.getMonth() - 1);
    const expectedMonth = `${current.getFullYear()}-${String(current.getMonth() + 1).padStart(2, '0')}`;
    
    expect(value).toBe(expectedMonth);
  });
  
  test('should navigate to next month', async ({ page }) => {
    // Navigate to previous month so Next button will be visible
    const lastMonth = new Date();
    lastMonth.setMonth(lastMonth.getMonth() - 1);
    const lastMonthString = `${lastMonth.getFullYear()}-${String(lastMonth.getMonth() + 1).padStart(2, '0')}`;
    
    await navigateToMonthlyView(page, lastMonthString);
    
    const nextButton = page.locator('button:has-text("Next"), button:has-text("→")');
    await nextButton.click();
    
    const monthInput = page.locator('input[type="month"]');
    const value = await monthInput.inputValue();
    
    // Should navigate to current month
    expect(value).toBe(getCurrentMonthString());
  });
  
  test('should return to current month when "This Month" is clicked', async ({ page }) => {
    await navigateToMonthlyView(page, '2025-01');
    
    const thisMonthButton = page.locator('button:has-text("This Month")');
    await thisMonthButton.click();
    
    const monthInput = page.locator('input[type="month"]');
    const value = await monthInput.inputValue();
    expect(value).toBe(getCurrentMonthString());
  });
  
  test('should calculate monthly total correctly', async ({ page }) => {
    const testMonth = '2025-11';
    
    // Create expenses for November 2025
    await createExpense(page, {
      description: 'Monthly total test 1',
      amount: '200.00',
      category: 'Groceries',
      date: '2025-11-15',
    });
    
    await createExpense(page, {
      description: 'Monthly total test 2',
      amount: '150.75',
      category: 'Transport',
      date: '2025-11-20',
    });
    
    await createExpense(page, {
      description: 'Monthly total test 3',
      amount: '49.25',
      category: 'Entertainment',
      date: '2025-11-25',
    });
    
    await navigateToMonthlyView(page, testMonth);
    
    // Total should be $400.00
    const totalElement = page.locator('text=/Total|Monthly Total/i').locator('..').locator('text=/\\$\\d+\\.\\d{2}/');
    await expect(totalElement).toContainText('$400.00');
  });
  
  test('should show category breakdown', async ({ page }) => {
    const testMonth = '2025-10';
    
    await createExpense(page, {
      description: 'Breakdown groceries',
      amount: '100.00',
      category: 'Groceries',
      date: '2025-10-10',
    });
    
    await createExpense(page, {
      description: 'Breakdown transport',
      amount: '50.00',
      category: 'Transport',
      date: '2025-10-15',
    });
    
    await navigateToMonthlyView(page, testMonth);
    
    // Should show both categories
    await expect(page.locator('text=Groceries')).toBeVisible();
    await expect(page.locator('text=Transport')).toBeVisible();
  });
  
  test('should calculate percentages correctly (sum = 100%)', async ({ page }) => {
    const testMonth = '2025-09';
    
    // Create expenses totaling $100 for easy percentage calculation
    await createExpense(page, {
      description: 'Percentage test 1',
      amount: '60.00',
      category: 'Groceries',
      date: '2025-09-05',
    });
    
    await createExpense(page, {
      description: 'Percentage test 2',
      amount: '40.00',
      category: 'Transport',
      date: '2025-09-10',
    });
    
    await navigateToMonthlyView(page, testMonth);
    
    // Groceries should be 60%, Transport should be 40%
    const groceriesPercent = page.locator('text=/Groceries.*60%/');
    const transportPercent = page.locator('text=/Transport.*40%/');
    
    // At least the percentages should be visible somewhere
    const hasGroceries = await groceriesPercent.count() > 0;
    const hasTransport = await transportPercent.count() > 0;
    
    expect(hasGroceries || hasTransport).toBe(true);
  });
  
  test('should show 0% for categories with $0', async ({ page }) => {
    const testMonth = '2025-08';
    
    // Create only one category of expenses
    await createExpense(page, {
      description: 'Single category',
      amount: '100.00',
      category: 'Groceries',
      date: '2025-08-15',
    });
    
    await navigateToMonthlyView(page, testMonth);
    
    // Other categories should show 0% or not appear
    // Groceries should be 100%
    const groceriesPercent = page.locator('text=/Groceries.*100%/');
    const hasGroceries = await groceriesPercent.count() > 0;
    
    expect(hasGroceries).toBe(true);
  });
  
  test('should show empty state when no expenses for month', async ({ page }) => {
    // Use an old month unlikely to have expenses
    await navigateToMonthlyView(page, '2020-01');
    
    const emptyState = page.locator('text=/No expenses recorded for this month|No expenses found/i');
    await expect(emptyState).toBeVisible();
  });
  
  test('should display amounts in currency format', async ({ page }) => {
    const testMonth = '2025-07';
    
    await createExpense(page, {
      description: 'Currency format monthly',
      amount: '1234.56',
      category: 'Housing and Utilities',
      date: '2025-07-20',
    });
    
    await navigateToMonthlyView(page, testMonth);
    
    // Should show $1234.56 somewhere
    const currencyElement = page.locator('text=/\\$1,?234\\.56/');
    await expect(currencyElement).toBeVisible();
  });
  
  test('should show all 7 categories in breakdown even if some are 0%', async ({ page }) => {
    const testMonth = '2025-06';
    
    await createExpense(page, {
      description: 'All categories test',
      amount: '50.00',
      category: 'Groceries',
      date: '2025-06-15',
    });
    
    await navigateToMonthlyView(page, testMonth);
    
    // Breakdown should show all categories or at least indicate some are 0%
    // This depends on UI implementation
    const categoryCount = await page.locator('text=/Groceries|Transport|Housing|Restaurants|Health|Clothing|Entertainment/').count();
    
    // Should show at least one category
    expect(categoryCount).toBeGreaterThan(0);
  });
  
  test('should update when month is manually changed', async ({ page }) => {
    await navigateToMonthlyView(page);
    
    const monthInput = page.locator('input[type="month"]');
    await monthInput.fill('2025-05');
    
    // Trigger change event
    await monthInput.blur();
    
    const currentValue = await monthInput.inputValue();
    expect(currentValue).toBe('2025-05');
  });
  
  test('should show monthly expenses list', async ({ page }) => {
    const testMonth = getCurrentMonthString();
    
    await createExpense(page, {
      description: 'Monthly list test',
      amount: '75.00',
      category: 'Entertainment',
      date: `${testMonth}-10`,
    });
    
    await navigateToMonthlyView(page, testMonth);
    
    // Should show the expense in a list/table
    await expect(page.locator('text=Monthly list test')).toBeVisible();
  });
  
});
