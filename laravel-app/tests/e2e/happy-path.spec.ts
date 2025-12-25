import { test, expect } from '@playwright/test';
import { 
  createExpense, 
  deleteExpense, 
  getExpenseRows,
  expectSuccessMessage,
  navigateToDailyView,
  navigateToMonthlyView,
  applyFilter,
  getTodayString,
  getDateString,
  getCurrentMonthString,
  CATEGORIES,
  type ExpenseData
} from './helpers';

/**
 * Happy Path E2E Tests - Core Business Flow
 * 
 * This test suite contains only the essential "happy path" tests
 * that validate the core business functionality:
 * - Basic CRUD operations (Create, Read, Update, Delete)
 * - Daily expense view
 * - Monthly expense view
 * - Basic category filtering
 * 
 * These tests represent the most common user workflows and ensure
 * the application's core features work correctly.
 */

test.describe('Happy Path: Core Business Flow', () => {
  
  test.describe('Basic CRUD Operations', () => {
    
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
      await page.click('button[type="submit"]');
      
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
    
    test('should sort expenses by date (newest first)', async ({ page }) => {
      // Create expenses with different dates
      await createExpense(page, {
        description: 'Older expense',
        amount: '10.00',
        category: 'Groceries',
        date: getDateString(-5), // 5 days ago
      });
      
      await createExpense(page, {
        description: 'Newer expense',
        amount: '20.00',
        category: 'Transport',
        date: getTodayString(),
      });
      
      await page.goto('/');
      
      // Verify newest expense appears first
      const rows = await getExpenseRows(page);
      const firstRow = rows.first();
      await expect(firstRow).toContainText('Newer expense');
    });
    
    test('should navigate through create form', async ({ page }) => {
      await page.goto('/');
      
      // Click Add Expense button
      const addButton = page.locator('text=Add Expense');
      await expect(addButton).toBeVisible();
      await addButton.click();
      
      // Verify form loads
      await expect(page).toHaveURL(/\/expenses\/create$/);
      await expect(page.locator('input[name="description"]')).toBeVisible();
      await expect(page.locator('input[name="amount"]')).toBeVisible();
      await expect(page.locator('select[name="category"]')).toBeVisible();
      await expect(page.locator('input[name="date"]')).toBeVisible();
      
      // Test cancel button
      await page.click('text=Cancel');
      await expect(page).toHaveURL(/\/expenses$/);
    });
    
    test('should display amounts in currency format', async ({ page }) => {
      await createExpense(page, {
        description: 'Currency format test',
        amount: '123.45',
        category: 'Groceries',
        date: getTodayString(),
      });
      
      await page.goto('/');
      
      const row = page.locator('tr:has-text("Currency format test")').first();
      // Should display as $123.45 not 123.45
      await expect(row).toContainText(/\$\d+\.\d{2}/);
    });
    
  });
  
  test.describe('Daily Expenses View', () => {
    
    test('should load daily view and show current date', async ({ page }) => {
      await navigateToDailyView(page);
      
      // Verify URL
      await expect(page).toHaveURL(/\/expenses\/daily/);
      
      // Verify current date is selected
      const dateInput = page.locator('input[type="date"]');
      const value = await dateInput.inputValue();
      expect(value).toBe(getTodayString());
    });
    
    test('should display expenses for selected date', async ({ page }) => {
      const today = getTodayString();
      
      // Create expenses for today
      await createExpense(page, {
        description: 'Daily expense 1',
        amount: '25.00',
        category: 'Groceries',
        date: today,
      });
      
      await createExpense(page, {
        description: 'Daily expense 2',
        amount: '15.50',
        category: 'Transport',
        date: today,
      });
      
      await navigateToDailyView(page, today);
      
      // Both expenses should be visible
      await expect(page.locator('text=Daily expense 1')).toBeVisible();
      await expect(page.locator('text=Daily expense 2')).toBeVisible();
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
    
  });
  
  test.describe('Monthly Expenses View', () => {
    
    test('should load monthly view and show current month', async ({ page }) => {
      await navigateToMonthlyView(page);
      
      // Verify URL
      await expect(page).toHaveURL(/\/expenses\/monthly/);
      
      // Verify current month is selected
      const monthInput = page.locator('input[type="month"]');
      const value = await monthInput.inputValue();
      expect(value).toBe(getCurrentMonthString());
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
    
    test('should show category breakdown', async ({ page }) => {
      const testMonth = '2025-11';
      
      // Create expenses in different categories
      await createExpense(page, {
        description: 'Breakdown test groceries',
        amount: '100.00',
        category: 'Groceries',
        date: '2025-11-10',
      });
      
      await createExpense(page, {
        description: 'Breakdown test transport',
        amount: '50.00',
        category: 'Transport',
        date: '2025-11-15',
      });
      
      await navigateToMonthlyView(page, testMonth);
      
      // Verify both categories appear in breakdown
      await expect(page.locator('text=Groceries')).toBeVisible();
      await expect(page.locator('text=Transport')).toBeVisible();
      
      // Verify amounts are shown
      await expect(page.locator('text=/\\$100\\.00/')).toBeVisible();
      await expect(page.locator('text=/\\$50\\.00/')).toBeVisible();
    });
    
  });
  
  test.describe('Category Filtering', () => {
    
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
      
      // Verify category filter exists
      const categoryFilter = page.locator('select[name="category"]');
      await expect(categoryFilter).toBeVisible();
      
      // Apply Groceries filter
      await applyFilter(page, 'Groceries');
      
      // Should see groceries but not transport
      await expect(page.locator('text=Grocery item')).toBeVisible();
      await expect(page.locator('text=Bus fare')).not.toBeVisible();
    });
    
    test('should clear filter to show all expenses', async ({ page }) => {
      await createExpense(page, {
        description: 'Filter test 1',
        amount: '20.00',
        category: 'Groceries',
        date: getTodayString(),
      });
      
      await createExpense(page, {
        description: 'Filter test 2',
        amount: '15.00',
        category: 'Transport',
        date: getTodayString(),
      });
      
      await page.goto('/');
      
      // Apply filter
      await applyFilter(page, 'Groceries');
      await expect(page.locator('text=Filter test 2')).not.toBeVisible();
      
      // Clear filter by selecting "All Categories"
      await page.selectOption('select[name="category"]', '');
      
      // Both should now be visible
      await expect(page.locator('text=Filter test 1')).toBeVisible();
      await expect(page.locator('text=Filter test 2')).toBeVisible();
    });
    
  });
  
  test.describe('Navigation', () => {
    
    test('should navigate between all main pages', async ({ page }) => {
      await page.goto('/');
      
      // Verify we can navigate to Daily view
      await page.click('a:has-text("Daily")');
      await expect(page).toHaveURL(/\/expenses\/daily/);
      
      // Navigate to Monthly view
      await page.click('a:has-text("Monthly")');
      await expect(page).toHaveURL(/\/expenses\/monthly/);
      
      // Navigate back to All Expenses
      await page.click('a:has-text("All Expenses"), a:has-text("Expenses")');
      await expect(page).toHaveURL(/\/expenses$/);
    });
    
  });
  
});
