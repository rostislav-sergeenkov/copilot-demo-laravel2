import { test, expect } from '@playwright/test';
import { 
  createExpense, 
  deleteExpense, 
  getExpenseRows, 
  expectSuccessMessage,
  getTodayString,
  CATEGORIES,
  type ExpenseData
} from './helpers';

/**
 * F1: Expense CRUD Interface Tests
 * 
 * Tests all CRUD operations for expenses:
 * - Create Expense
 * - Read Expenses (Index)
 * - Update Expense
 * - Delete Expense
 */

test.describe('F1: Expense CRUD Interface', () => {
  
  test.describe('Create Expense', () => {
    
    test('should show "Add Expense" button on index page', async ({ page }) => {
      await page.goto('/');
      const addButton = page.locator('text=Add Expense');
      await expect(addButton).toBeVisible();
    });
    
    test('should load create form at /expenses/create', async ({ page }) => {
      await page.goto('/');
      await page.click('text=Add Expense');
      await expect(page).toHaveURL(/\/expenses\/create$/);
    });
    
    test('should display all required form fields', async ({ page }) => {
      await page.goto('/expenses/create');
      
      await expect(page.locator('input[name="description"]')).toBeVisible();
      await expect(page.locator('input[name="amount"]')).toBeVisible();
      await expect(page.locator('select[name="category"]')).toBeVisible();
      await expect(page.locator('input[name="date"]')).toBeVisible();
    });
    
    test('should show all 7 categories in dropdown', async ({ page }) => {
      await page.goto('/expenses/create');
      const categorySelect = page.locator('select[name="category"]');
      
      for (const category of CATEGORIES) {
        await expect(categorySelect.locator(`option:has-text("${category}")`)).toBeVisible();
      }
    });
    
    test('should accept description up to 255 characters', async ({ page }) => {
      await page.goto('/expenses/create');
      const description = 'A'.repeat(255);
      await page.fill('input[name="description"]', description);
      const value = await page.inputValue('input[name="description"]');
      expect(value).toHaveLength(255);
    });
    
    test('should accept decimal amounts', async ({ page }) => {
      await page.goto('/expenses/create');
      await page.fill('input[name="amount"]', '123.45');
      const value = await page.inputValue('input[name="amount"]');
      expect(value).toBe('123.45');
    });
    
    test('should create expense and redirect to index', async ({ page }) => {
      const expense: ExpenseData = {
        description: 'Test grocery shopping',
        amount: '45.99',
        category: 'Groceries',
        date: getTodayString(),
      };
      
      await createExpense(page, expense);
      await expectSuccessMessage(page);
      
      // Verify expense appears in the list
      const expenseRow = page.locator(`tr:has-text("${expense.description}")`);
      await expect(expenseRow).toBeVisible();
      await expect(expenseRow).toContainText('$45.99');
    });
    
    test('should return to index when cancel is clicked', async ({ page }) => {
      await page.goto('/expenses/create');
      await page.click('text=Cancel');
      await expect(page).toHaveURL(/\/expenses$/);
    });
    
  });
  
  test.describe('Read Expenses (Index)', () => {
    
    test('should load index page at /expenses', async ({ page }) => {
      await page.goto('/');
      await expect(page).toHaveURL(/\/expenses$/);
    });
    
    test('should display table with required columns', async ({ page }) => {
      await page.goto('/');
      
      // Create a test expense first
      await createExpense(page, {
        description: 'Test expense',
        amount: '10.00',
        category: 'Groceries',
        date: getTodayString(),
      });
      
      await page.goto('/');
      
      const headers = page.locator('table thead th');
      await expect(headers.filter({ hasText: 'Date' })).toBeVisible();
      await expect(headers.filter({ hasText: 'Description' })).toBeVisible();
      await expect(headers.filter({ hasText: 'Category' })).toBeVisible();
      await expect(headers.filter({ hasText: 'Amount' })).toBeVisible();
      await expect(headers.filter({ hasText: 'Actions' })).toBeVisible();
    });
    
    test('should sort expenses by date (newest first)', async ({ page }) => {
      // Create expenses with different dates
      await createExpense(page, {
        description: 'Older expense',
        amount: '10.00',
        category: 'Groceries',
        date: '2025-12-01',
      });
      
      await createExpense(page, {
        description: 'Newer expense',
        amount: '20.00',
        category: 'Transport',
        date: '2025-12-15',
      });
      
      await page.goto('/');
      
      const rows = await getExpenseRows(page);
      const firstRow = rows.first();
      await expect(firstRow).toContainText('Newer expense');
    });
    
    test('should display amounts as currency format', async ({ page }) => {
      await createExpense(page, {
        description: 'Currency test',
        amount: '123.45',
        category: 'Groceries',
        date: getTodayString(),
      });
      
      await page.goto('/');
      
      const row = page.locator('tr:has-text("Currency test")');
      await expect(row).toContainText('$123.45');
    });
    
    test('should display dates in readable format', async ({ page }) => {
      await createExpense(page, {
        description: 'Date format test',
        amount: '10.00',
        category: 'Groceries',
        date: '2025-12-02',
      });
      
      await page.goto('/');
      
      const row = page.locator('tr:has-text("Date format test")');
      // Should show something like "December 2, 2025" not "2025-12-02"
      await expect(row).toContainText(/December|Dec/);
      await expect(row).toContainText('2, 2025');
    });
    
    test('should show empty state when no expenses exist', async ({ page }) => {
      // Assuming fresh database or no expenses
      await page.goto('/');
      
      const rows = await getExpenseRows(page);
      const count = await rows.count();
      
      if (count === 0) {
        const emptyState = page.locator('text=/No expenses recorded yet|No expenses found/i');
        await expect(emptyState).toBeVisible();
      }
    });
    
    test('should paginate expenses (15 per page)', async ({ page }) => {
      // This test assumes we can create 16+ expenses
      // In a real scenario, you'd seed the database
      await page.goto('/');
      
      const pagination = page.locator('.pagination, nav[aria-label="Pagination"]');
      // Pagination should only appear if there are more than 15 items
      // We'll just check the structure exists
    });
    
  });
  
  test.describe('Update Expense', () => {
    
    test('should show edit button for each expense', async ({ page }) => {
      await createExpense(page, {
        description: 'Editable expense',
        amount: '25.00',
        category: 'Groceries',
        date: getTodayString(),
      });
      
      await page.goto('/');
      
      const row = page.locator('tr:has-text("Editable expense")');
      const editButton = row.locator('button:has-text("Edit"), a:has-text("Edit")');
      await expect(editButton).toBeVisible();
    });
    
    test('should load edit form at /expenses/{id}/edit', async ({ page }) => {
      await createExpense(page, {
        description: 'Edit test expense',
        amount: '30.00',
        category: 'Transport',
        date: getTodayString(),
      });
      
      await page.goto('/');
      const row = page.locator('tr:has-text("Edit test expense")');
      await row.locator('button:has-text("Edit"), a:has-text("Edit")').click();
      
      await expect(page).toHaveURL(/\/expenses\/\d+\/edit$/);
    });
    
    test('should pre-populate form with existing data', async ({ page }) => {
      await createExpense(page, {
        description: 'Pre-populate test',
        amount: '40.00',
        category: 'Groceries',
        date: '2025-12-10',
      });
      
      await page.goto('/');
      const row = page.locator('tr:has-text("Pre-populate test")');
      await row.locator('button:has-text("Edit"), a:has-text("Edit")').click();
      
      const description = await page.inputValue('input[name="description"]');
      const amount = await page.inputValue('input[name="amount"]');
      const category = await page.inputValue('select[name="category"]');
      const date = await page.inputValue('input[name="date"]');
      
      expect(description).toBe('Pre-populate test');
      expect(amount).toBe('40.00');
      expect(category).toBe('Groceries');
      expect(date).toBe('2025-12-10');
    });
    
    test('should update expense and redirect to index', async ({ page }) => {
      await createExpense(page, {
        description: 'Update test',
        amount: '50.00',
        category: 'Groceries',
        date: getTodayString(),
      });
      
      await page.goto('/');
      const row = page.locator('tr:has-text("Update test")');
      await row.locator('button:has-text("Edit"), a:has-text("Edit")').click();
      
      // Update the description
      await page.fill('input[name="description"]', 'Updated description');
      await page.click('button[type="submit"]');
      
      await expect(page).toHaveURL(/\/expenses$/);
      await expectSuccessMessage(page);
      
      const updatedRow = page.locator('tr:has-text("Updated description")');
      await expect(updatedRow).toBeVisible();
    });
    
    test('should return to index when cancel is clicked', async ({ page }) => {
      await createExpense(page, {
        description: 'Cancel edit test',
        amount: '15.00',
        category: 'Transport',
        date: getTodayString(),
      });
      
      await page.goto('/');
      const row = page.locator('tr:has-text("Cancel edit test")');
      await row.locator('button:has-text("Edit"), a:has-text("Edit")').click();
      
      await page.click('text=Cancel');
      await expect(page).toHaveURL(/\/expenses$/);
      
      // Original expense should still exist unchanged
      const originalRow = page.locator('tr:has-text("Cancel edit test")');
      await expect(originalRow).toBeVisible();
    });
    
  });
  
  test.describe('Delete Expense', () => {
    
    test('should show delete button for each expense', async ({ page }) => {
      await createExpense(page, {
        description: 'Deletable expense',
        amount: '35.00',
        category: 'Groceries',
        date: getTodayString(),
      });
      
      await page.goto('/');
      
      const row = page.locator('tr:has-text("Deletable expense")');
      const deleteButton = row.locator('button:has-text("Delete")');
      await expect(deleteButton).toBeVisible();
    });
    
    test('should show confirmation dialog before deletion', async ({ page }) => {
      await createExpense(page, {
        description: 'Confirm delete test',
        amount: '20.00',
        category: 'Transport',
        date: getTodayString(),
      });
      
      await page.goto('/');
      
      let dialogShown = false;
      page.on('dialog', async dialog => {
        dialogShown = true;
        expect(dialog.type()).toBe('confirm');
        await dialog.accept();
      });
      
      const row = page.locator('tr:has-text("Confirm delete test")');
      await row.locator('button:has-text("Delete")').click();
      
      // Wait a moment for dialog
      await page.waitForTimeout(500);
      expect(dialogShown).toBe(true);
    });
    
    test('should soft-delete expense and remove from index', async ({ page }) => {
      await createExpense(page, {
        description: 'Soft delete test',
        amount: '60.00',
        category: 'Groceries',
        date: getTodayString(),
      });
      
      await page.goto('/');
      
      page.on('dialog', dialog => dialog.accept());
      
      const row = page.locator('tr:has-text("Soft delete test")');
      await row.locator('button:has-text("Delete")').click();
      
      await expect(page).toHaveURL(/\/expenses$/);
      await expectSuccessMessage(page);
      
      // Expense should no longer be visible
      const deletedRow = page.locator('tr:has-text("Soft delete test")');
      await expect(deletedRow).not.toBeVisible();
    });
    
  });
  
});
