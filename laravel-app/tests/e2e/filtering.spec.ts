import { test, expect } from '@playwright/test';
import { 
  createExpense, 
  applyFilter,
  navigateToDailyView,
  navigateToMonthlyView,
  getTodayString,
  getCurrentMonthString,
  CATEGORIES,
  type ExpenseData
} from './helpers';

/**
 * F4: Category Filtering Tests
 * 
 * Tests for category filtering across all views:
 * - Index page filtering
 * - Daily view filtering
 * - Monthly view filtering
 * - Filter persistence
 */

test.describe('F4: Category Filtering', () => {
  
  test.describe('Index Page Filtering', () => {
    
    test('should show category filter dropdown on index page', async ({ page }) => {
      await page.goto('/');
      
      const categoryFilter = page.locator('select[name="category"]');
      await expect(categoryFilter).toBeVisible();
    });
    
    test('should show "All Categories" option', async ({ page }) => {
      await page.goto('/');
      
      const categoryFilter = page.locator('select[name="category"]');
      const allOption = categoryFilter.locator('option:has-text("All Categories"), option[value=""]');
      await expect(allOption).toBeVisible();
    });
    
    test('should filter to show only matching expenses', async ({ page }) => {
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
    
    test('should clear filter when "All Categories" is selected', async ({ page }) => {
      await createExpense(page, {
        description: 'Filter clear test 1',
        amount: '20.00',
        category: 'Groceries',
        date: getTodayString(),
      });
      
      await createExpense(page, {
        description: 'Filter clear test 2',
        amount: '15.00',
        category: 'Transport',
        date: getTodayString(),
      });
      
      await page.goto('/');
      
      // First filter by Groceries
      await applyFilter(page, 'Groceries');
      await expect(page.locator('text=Filter clear test 2')).not.toBeVisible();
      
      // Then clear filter
      await page.selectOption('select[name="category"]', '');
      
      // Both should now be visible
      await expect(page.locator('text=Filter clear test 1')).toBeVisible();
      await expect(page.locator('text=Filter clear test 2')).toBeVisible();
    });
    
    test('should persist filter through pagination', async ({ page }) => {
      // This assumes pagination exists
      await page.goto('/expenses?category=Groceries');
      
      const categoryFilter = page.locator('select[name="category"]');
      const selectedValue = await categoryFilter.inputValue();
      expect(selectedValue).toBe('Groceries');
    });
    
    test('should preserve filter state in URL', async ({ page }) => {
      await page.goto('/');
      
      await applyFilter(page, 'Transport');
      
      // URL should contain category parameter
      await expect(page).toHaveURL(/category=Transport/);
    });
    
  });
  
  test.describe('Daily View Filtering', () => {
    
    test('should show category filter dropdown on daily view', async ({ page }) => {
      await navigateToDailyView(page);
      
      const categoryFilter = page.locator('select[name="category"]');
      await expect(categoryFilter).toBeVisible();
    });
    
    test('should filter daily expenses by category', async ({ page }) => {
      const today = getTodayString();
      
      await createExpense(page, {
        description: 'Daily groceries filter',
        amount: '40.00',
        category: 'Groceries',
        date: today,
      });
      
      await createExpense(page, {
        description: 'Daily transport filter',
        amount: '10.00',
        category: 'Transport',
        date: today,
      });
      
      await navigateToDailyView(page, today);
      
      // Filter by Transport
      await applyFilter(page, 'Transport');
      
      await expect(page.locator('text=Daily transport filter')).toBeVisible();
      await expect(page.locator('text=Daily groceries filter')).not.toBeVisible();
    });
    
    test('should update daily total when filtered', async ({ page }) => {
      const today = getTodayString();
      
      await createExpense(page, {
        description: 'Daily total filter 1',
        amount: '100.00',
        category: 'Groceries',
        date: today,
      });
      
      await createExpense(page, {
        description: 'Daily total filter 2',
        amount: '50.00',
        category: 'Transport',
        date: today,
      });
      
      await navigateToDailyView(page, today);
      
      // Total should be $150
      let totalElement = page.locator('text=/Total.*\\$150\\.00/');
      let hasTotal = await totalElement.count() > 0;
      expect(hasTotal).toBe(true);
      
      // Filter by Groceries only
      await applyFilter(page, 'Groceries');
      
      // Total should now be $100
      totalElement = page.locator('text=/Total.*\\$100\\.00/');
      hasTotal = await totalElement.count() > 0;
      expect(hasTotal).toBe(true);
    });
    
  });
  
  test.describe('Monthly View Filtering', () => {
    
    test('should show category filter dropdown on monthly view', async ({ page }) => {
      await navigateToMonthlyView(page);
      
      const categoryFilter = page.locator('select[name="category"]');
      await expect(categoryFilter).toBeVisible();
    });
    
    test('should filter monthly expenses by category', async ({ page }) => {
      const testMonth = '2025-04';
      
      await createExpense(page, {
        description: 'Monthly groceries filter',
        amount: '200.00',
        category: 'Groceries',
        date: '2025-04-10',
      });
      
      await createExpense(page, {
        description: 'Monthly entertainment filter',
        amount: '75.00',
        category: 'Entertainment',
        date: '2025-04-15',
      });
      
      await navigateToMonthlyView(page, testMonth);
      
      // Filter by Entertainment
      await applyFilter(page, 'Entertainment');
      
      await expect(page.locator('text=Monthly entertainment filter')).toBeVisible();
      await expect(page.locator('text=Monthly groceries filter')).not.toBeVisible();
    });
    
    test('should update monthly total when filtered', async ({ page }) => {
      const testMonth = '2025-03';
      
      await createExpense(page, {
        description: 'Monthly total filter 1',
        amount: '300.00',
        category: 'Housing and Utilities',
        date: '2025-03-05',
      });
      
      await createExpense(page, {
        description: 'Monthly total filter 2',
        amount: '100.00',
        category: 'Transport',
        date: '2025-03-10',
      });
      
      await navigateToMonthlyView(page, testMonth);
      
      // Filter by Housing and Utilities
      await applyFilter(page, 'Housing and Utilities');
      
      // Total should be $300
      const totalElement = page.locator('text=/Total.*\\$300\\.00/');
      const hasTotal = await totalElement.count() > 0;
      expect(hasTotal).toBe(true);
    });
    
    test('should update category percentages when filtered', async ({ page }) => {
      const testMonth = '2025-02';
      
      await createExpense(page, {
        description: 'Percentage filter 1',
        amount: '80.00',
        category: 'Groceries',
        date: '2025-02-05',
      });
      
      await createExpense(page, {
        description: 'Percentage filter 2',
        amount: '20.00',
        category: 'Transport',
        date: '2025-02-10',
      });
      
      await navigateToMonthlyView(page, testMonth);
      
      // When filtered to Groceries, it should show 100%
      await applyFilter(page, 'Groceries');
      
      const groceriesPercent = page.locator('text=/Groceries.*100%/');
      const hasPercent = await groceriesPercent.count() > 0;
      expect(hasPercent).toBe(true);
    });
    
  });
  
  test.describe('Filter Persistence', () => {
    
    test('should maintain filter when navigating back from create', async ({ page }) => {
      await page.goto('/');
      
      await applyFilter(page, 'Groceries');
      
      // Navigate to create page
      await page.click('text=Add Expense');
      
      // Go back
      await page.click('text=Cancel');
      
      // Filter should still be applied
      const categoryFilter = page.locator('select[name="category"]');
      const selectedValue = await categoryFilter.inputValue();
      expect(selectedValue).toBe('Groceries');
    });
    
    test('should allow switching between different category filters', async ({ page }) => {
      await createExpense(page, {
        description: 'Switch filter test 1',
        amount: '25.00',
        category: 'Groceries',
        date: getTodayString(),
      });
      
      await createExpense(page, {
        description: 'Switch filter test 2',
        amount: '35.00',
        category: 'Transport',
        date: getTodayString(),
      });
      
      await createExpense(page, {
        description: 'Switch filter test 3',
        amount: '45.00',
        category: 'Entertainment',
        date: getTodayString(),
      });
      
      await page.goto('/');
      
      // Test Groceries
      await applyFilter(page, 'Groceries');
      await expect(page.locator('text=Switch filter test 1')).toBeVisible();
      await expect(page.locator('text=Switch filter test 2')).not.toBeVisible();
      
      // Switch to Transport
      await applyFilter(page, 'Transport');
      await expect(page.locator('text=Switch filter test 2')).toBeVisible();
      await expect(page.locator('text=Switch filter test 1')).not.toBeVisible();
      
      // Switch to Entertainment
      await applyFilter(page, 'Entertainment');
      await expect(page.locator('text=Switch filter test 3')).toBeVisible();
      await expect(page.locator('text=Switch filter test 1')).not.toBeVisible();
    });
    
  });
  
  test.describe('All Categories Available', () => {
    
    test('should have all 7 categories in filter dropdown', async ({ page }) => {
      await page.goto('/');
      
      const categoryFilter = page.locator('select[name="category"]');
      
      for (const category of CATEGORIES) {
        const option = categoryFilter.locator(`option:has-text("${category}")`);
        await expect(option).toBeVisible();
      }
    });
    
  });
  
});
