import { test, expect } from '@playwright/test';
import { 
  login,
  createExpense, 
  deleteExpense, 
  expectSuccessMessage,
  navigateToDailyView,
  navigateToMonthlyView,
  applyFilter,
  getTodayString,
  getDateString,
  clearAllExpenses,
  type ExpenseData
} from './helpers';

/**
 * Critical E2E Tests - Essential Business Flows
 * 
 * This minimal test suite covers only the most critical user journeys:
 * 1. Create expense - Primary user action
 * 2. Display expenses - Core data visibility
 * 3. Update expense - Essential CRUD operation
 * 4. Delete expense - Essential CRUD operation
 * 5. Daily view with totals - Key feature for daily tracking
 * 6. Monthly view with totals - Key feature for budget planning
 * 7. Category filtering - Critical for expense organization
 * 
 * These tests validate core business value and would immediately
 * indicate if the application's primary functionality is broken.
 */

test.describe.configure({ mode: 'serial' });

test.describe('Critical E2E Tests', () => {
  
  test.beforeEach(async ({ page }) => {
    await login(page);
    await clearAllExpenses(page);
  });

  test('should create a new expense successfully', async ({ page }) => {
    const expense: ExpenseData = {
      description: 'Grocery shopping',
      amount: '45.99',
      category: 'Groceries',
      date: getTodayString(),
    };

    await createExpense(page, expense);
    await expectSuccessMessage(page);

    // Verify expense appears in the list
    const expenseRow = page.locator(`tr:has-text("${expense.description}")`).first();
    await expect(expenseRow).toBeVisible();
    await expect(expenseRow).toContainText('$45.99');
    await expect(expenseRow).toContainText('Groceries');
  });

  test('should display expense list with all details', async ({ page }) => {
    // Create a test expense
    await createExpense(page, {
      description: 'Test expense for display',
      amount: '25.50',
      category: 'Transport',
      date: getTodayString(),
    });

    await page.goto('/');

    // Verify table columns exist
    const headers = page.locator('table thead th');
    await expect(headers.filter({ hasText: 'Date' })).toBeVisible();
    await expect(headers.filter({ hasText: 'Description' })).toBeVisible();
    await expect(headers.filter({ hasText: 'Category' })).toBeVisible();
    await expect(headers.filter({ hasText: 'Amount' })).toBeVisible();
    await expect(headers.filter({ hasText: 'Actions' })).toBeVisible();

    // Verify expense is displayed with correct formatting
    const row = page.locator('tr:has-text("Test expense for display")').first();
    await expect(row).toContainText('$25.50');
    await expect(row).toContainText('Transport');
  });

  test('should update an existing expense', async ({ page }) => {
    // Create expense to edit
    await createExpense(page, {
      description: 'Original description',
      amount: '30.00',
      category: 'Groceries',
      date: getTodayString(),
    });

    await page.goto('/');

    // Click edit button
    const row = page.locator('tr:has-text("Original description")').first();
    await row.locator('button:has-text("Edit"), a:has-text("Edit")').click();

    // Verify we're on edit page
    await expect(page).toHaveURL(/\/expenses\/\d+\/edit$/);

    // Update the expense
    await page.fill('input[name="description"]', 'Updated description');
    await page.fill('input[name="amount"]', '50.00');
    await page.selectOption('select[name="category"]', 'Transport');
    await page.click('form[action*="expenses"] button[type="submit"]');

    // Verify redirect and success
    await expect(page).toHaveURL(/\/expenses$/);
    await expectSuccessMessage(page);

    // Verify updated data is displayed
    const updatedRow = page.locator('tr:has-text("Updated description")').first();
    await expect(updatedRow).toBeVisible();
    await expect(updatedRow).toContainText('$50.00');
    await expect(updatedRow).toContainText('Transport');
  });

  test('should delete an expense', async ({ page }) => {
    // Create expense to delete
    await createExpense(page, {
      description: 'Expense to delete',
      amount: '15.00',
      category: 'Entertainment',
      date: getTodayString(),
    });

    await page.goto('/');

    // Delete the expense
    await deleteExpense(page, 'Expense to delete');

    // Verify expense is removed from list
    const row = page.locator('tr:has-text("Expense to delete")');
    await expect(row).toHaveCount(0);
  });

  test('should calculate and display daily total', async ({ page }) => {
    const testDate = getDateString(-1); // Yesterday

    // Create expenses
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

    // Verify total is calculated correctly: $150.50
    const totalElement = page.locator('text=/Total|Daily Total/i').locator('..').locator('text=/\\$\\d+\\.\\d{2}/');
    await expect(totalElement).toContainText('$150.50');
  });

  test('should display monthly total', async ({ page }) => {
    const testMonth = '2025-12';

    // Create expenses for December 2025
    await createExpense(page, {
      description: 'Monthly test 1',
      amount: '200.00',
      category: 'Groceries',
      date: '2025-12-05',
    });

    await createExpense(page, {
      description: 'Monthly test 2',
      amount: '150.00',
      category: 'Transport',
      date: '2025-12-10',
    });

    await navigateToMonthlyView(page, testMonth);

    // Verify total: $350.00
    const totalElement = page.locator('text=/Total|Monthly Total/i').locator('..').locator('text=/\\$\\d+\\.\\d{2}/');
    await expect(totalElement).toContainText('$350.00');
  });

  test('should filter expenses by category', async ({ page }) => {
    // Create expenses in different categories
    await createExpense(page, {
      description: 'Grocery item',
      amount: '30.00',
      category: 'Groceries',
      date: getTodayString(),
    });

    await createExpense(page, {
      description: 'Bus fare',
      amount: '5.00',
      category: 'Transport',
      date: getTodayString(),
    });

    await page.goto('/');

    // Apply Groceries filter
    await applyFilter(page, 'Groceries');

    // Should see groceries but not transport
    await expect(page.locator('text=Grocery item')).toBeVisible();
    await expect(page.locator('text=Bus fare')).not.toBeVisible();
  });
  
});
