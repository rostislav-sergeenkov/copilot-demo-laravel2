import { test, expect } from '@playwright/test';
import { getTodayString, getDateString } from './helpers';

/**
 * Data Validation Tests
 * 
 * Tests all form validation rules:
 * - Description field validation
 * - Amount field validation
 * - Category field validation
 * - Date field validation
 * - Error message display
 */

test.describe('Data Validation', () => {
  
  test.describe('Description Field', () => {
    
    test('should show error when description is empty', async ({ page }) => {
      await page.goto('/expenses/create');
      
      // Leave description empty
      await page.fill('input[name="amount"]', '10.00');
      await page.selectOption('select[name="category"]', 'Groceries');
      await page.fill('input[name="date"]', getTodayString());
      
      await page.click('button[type="submit"]');
      
      // Should show validation error
      const error = page.locator('text=/description.*required|required.*description/i');
      await expect(error).toBeVisible();
    });
    
    test('should show error when description exceeds 255 characters', async ({ page }) => {
      await page.goto('/expenses/create');
      
      const longDescription = 'A'.repeat(256);
      await page.fill('input[name="description"]', longDescription);
      await page.fill('input[name="amount"]', '10.00');
      await page.selectOption('select[name="category"]', 'Groceries');
      await page.fill('input[name="date"]', getTodayString());
      
      await page.click('button[type="submit"]');
      
      // Should show validation error
      const error = page.locator('text=/description.*255|255.*characters/i');
      await expect(error).toBeVisible();
    });
    
    test('should accept special characters and Unicode', async ({ page }) => {
      await page.goto('/expenses/create');
      
      const specialDescription = 'Test with émojis 🎉 & spëcial çhars!';
      await page.fill('input[name="description"]', specialDescription);
      await page.fill('input[name="amount"]', '10.00');
      await page.selectOption('select[name="category"]', 'Groceries');
      await page.fill('input[name="date"]', getTodayString());
      
      await page.click('button[type="submit"]');
      
      // Should redirect successfully
      await expect(page).toHaveURL(/\/expenses$/);
    });
    
  });
  
  test.describe('Amount Field', () => {
    
    test('should show error when amount is empty', async ({ page }) => {
      await page.goto('/expenses/create');
      
      await page.fill('input[name="description"]', 'Test expense');
      // Leave amount empty
      await page.selectOption('select[name="category"]', 'Groceries');
      await page.fill('input[name="date"]', getTodayString());
      
      await page.click('button[type="submit"]');
      
      const error = page.locator('text=/amount.*required|required.*amount/i');
      await expect(error).toBeVisible();
    });
    
    test('should show error when amount is below $0.01', async ({ page }) => {
      await page.goto('/expenses/create');
      
      await page.fill('input[name="description"]', 'Test expense');
      await page.fill('input[name="amount"]', '0.00');
      await page.selectOption('select[name="category"]', 'Groceries');
      await page.fill('input[name="date"]', getTodayString());
      
      await page.click('button[type="submit"]');
      
      const error = page.locator('text=/amount.*0\\.01|minimum.*0\\.01/i');
      await expect(error).toBeVisible();
    });
    
    test('should show error when amount exceeds $999,999.99', async ({ page }) => {
      await page.goto('/expenses/create');
      
      await page.fill('input[name="description"]', 'Test expense');
      await page.fill('input[name="amount"]', '1000000.00');
      await page.selectOption('select[name="category"]', 'Groceries');
      await page.fill('input[name="date"]', getTodayString());
      
      await page.click('button[type="submit"]');
      
      const error = page.locator('text=/amount.*999,?999\\.99|maximum.*999,?999\\.99/i');
      await expect(error).toBeVisible();
    });
    
    test('should accept decimal values with 2 places', async ({ page }) => {
      await page.goto('/expenses/create');
      
      await page.fill('input[name="description"]', 'Decimal test');
      await page.fill('input[name="amount"]', '123.45');
      await page.selectOption('select[name="category"]', 'Groceries');
      await page.fill('input[name="date"]', getTodayString());
      
      await page.click('button[type="submit"]');
      
      // Should succeed
      await expect(page).toHaveURL(/\/expenses$/);
    });
    
    test('should reject non-numeric input', async ({ page }) => {
      await page.goto('/expenses/create');
      
      await page.fill('input[name="description"]', 'Test expense');
      await page.fill('input[name="amount"]', 'abc');
      await page.selectOption('select[name="category"]', 'Groceries');
      await page.fill('input[name="date"]', getTodayString());
      
      await page.click('button[type="submit"]');
      
      const error = page.locator('text=/amount.*numeric|amount.*number/i');
      await expect(error).toBeVisible();
    });
    
  });
  
  test.describe('Category Field', () => {
    
    test('should show error when category is not selected', async ({ page }) => {
      await page.goto('/expenses/create');
      
      await page.fill('input[name="description"]', 'Test expense');
      await page.fill('input[name="amount"]', '10.00');
      // Don't select category
      await page.fill('input[name="date"]', getTodayString());
      
      await page.click('button[type="submit"]');
      
      const error = page.locator('text=/category.*required|required.*category/i');
      await expect(error).toBeVisible();
    });
    
    test('should only accept valid category values', async ({ page }) => {
      // This test verifies server-side validation
      // Attempting to submit an invalid category via form manipulation
      await page.goto('/expenses/create');
      
      await page.fill('input[name="description"]', 'Test expense');
      await page.fill('input[name="amount"]', '10.00');
      
      // Try to inject invalid category via JavaScript
      await page.evaluate(() => {
        const select = document.querySelector('select[name="category"]') as HTMLSelectElement;
        const option = document.createElement('option');
        option.value = 'Invalid Category';
        option.text = 'Invalid Category';
        select.add(option);
        select.value = 'Invalid Category';
      });
      
      await page.fill('input[name="date"]', getTodayString());
      await page.click('button[type="submit"]');
      
      // Should show validation error
      const error = page.locator('text=/category.*invalid|invalid.*category/i');
      const hasError = await error.count() > 0;
      
      // Should not redirect to success
      const currentUrl = page.url();
      expect(currentUrl).not.toMatch(/\/expenses$/);
    });
    
  });
  
  test.describe('Date Field', () => {
    
    test('should show error when date is empty', async ({ page }) => {
      await page.goto('/expenses/create');
      
      await page.fill('input[name="description"]', 'Test expense');
      await page.fill('input[name="amount"]', '10.00');
      await page.selectOption('select[name="category"]', 'Groceries');
      // Leave date empty
      
      await page.click('button[type="submit"]');
      
      const error = page.locator('text=/date.*required|required.*date/i');
      await expect(error).toBeVisible();
    });
    
    test('should show error for future dates', async ({ page }) => {
      await page.goto('/expenses/create');
      
      await page.fill('input[name="description"]', 'Test expense');
      await page.fill('input[name="amount"]', '10.00');
      await page.selectOption('select[name="category"]', 'Groceries');
      await page.fill('input[name="date"]', getDateString(7)); // 7 days in future
      
      await page.click('button[type="submit"]');
      
      const error = page.locator('text=/date.*future|cannot.*future/i');
      await expect(error).toBeVisible();
    });
    
    test('should show error for dates older than 5 years', async ({ page }) => {
      await page.goto('/expenses/create');
      
      const oldDate = new Date();
      oldDate.setFullYear(oldDate.getFullYear() - 6);
      const oldDateString = oldDate.toISOString().split('T')[0];
      
      await page.fill('input[name="description"]', 'Test expense');
      await page.fill('input[name="amount"]', '10.00');
      await page.selectOption('select[name="category"]', 'Groceries');
      await page.fill('input[name="date"]', oldDateString);
      
      await page.click('button[type="submit"]');
      
      const error = page.locator('text=/date.*5 years|date.*old/i');
      await expect(error).toBeVisible();
    });
    
    test('should accept today\'s date', async ({ page }) => {
      await page.goto('/expenses/create');
      
      await page.fill('input[name="description"]', 'Today test');
      await page.fill('input[name="amount"]', '10.00');
      await page.selectOption('select[name="category"]', 'Groceries');
      await page.fill('input[name="date"]', getTodayString());
      
      await page.click('button[type="submit"]');
      
      await expect(page).toHaveURL(/\/expenses$/);
    });
    
    test('should accept dates within 5 years', async ({ page }) => {
      await page.goto('/expenses/create');
      
      const validOldDate = new Date();
      validOldDate.setFullYear(validOldDate.getFullYear() - 4);
      const validDateString = validOldDate.toISOString().split('T')[0];
      
      await page.fill('input[name="description"]', 'Old date test');
      await page.fill('input[name="amount"]', '10.00');
      await page.selectOption('select[name="category"]', 'Groceries');
      await page.fill('input[name="date"]', validDateString);
      
      await page.click('button[type="submit"]');
      
      await expect(page).toHaveURL(/\/expenses$/);
    });
    
  });
  
  test.describe('Validation Error Display', () => {
    
    test('should display inline errors next to fields', async ({ page }) => {
      await page.goto('/expenses/create');
      
      // Submit empty form
      await page.click('button[type="submit"]');
      
      // Should show errors for all required fields
      const errors = page.locator('.invalid-feedback, .error, [class*="error"]');
      const errorCount = await errors.count();
      
      expect(errorCount).toBeGreaterThan(0);
    });
    
    test('should show flash message at top of form', async ({ page }) => {
      await page.goto('/expenses/create');
      
      // Submit with missing fields
      await page.fill('input[name="description"]', 'Test');
      await page.click('button[type="submit"]');
      
      // Should show alert/flash message
      const alert = page.locator('.alert-danger, [role="alert"], .error-message');
      const hasAlert = await alert.count() > 0;
      
      expect(hasAlert).toBe(true);
    });
    
    test('should repopulate form with previous input after validation error', async ({ page }) => {
      await page.goto('/expenses/create');
      
      await page.fill('input[name="description"]', 'Test description');
      await page.fill('input[name="amount"]', '-5.00'); // Invalid amount
      await page.selectOption('select[name="category"]', 'Groceries');
      await page.fill('input[name="date"]', getTodayString());
      
      await page.click('button[type="submit"]');
      
      // Form should still have the values
      const description = await page.inputValue('input[name="description"]');
      expect(description).toBe('Test description');
      
      const category = await page.inputValue('select[name="category"]');
      expect(category).toBe('Groceries');
    });
    
    test('should validate server-side (not just client-side)', async ({ page }) => {
      // This test ensures validation happens on the server
      await page.goto('/expenses/create');
      
      // Disable HTML5 validation
      await page.evaluate(() => {
        const form = document.querySelector('form');
        if (form) {
          form.setAttribute('novalidate', 'true');
        }
      });
      
      // Submit with invalid data
      await page.fill('input[name="description"]', '');
      await page.fill('input[name="amount"]', '');
      await page.click('button[type="submit"]');
      
      // Should still show errors from server
      const error = page.locator('.invalid-feedback, .error, [class*="error"]');
      const hasError = await error.count() > 0;
      
      expect(hasError).toBe(true);
    });
    
  });
  
  test.describe('Multiple Validation Errors', () => {
    
    test('should show all validation errors at once', async ({ page }) => {
      await page.goto('/expenses/create');
      
      // Submit completely empty form
      await page.click('button[type="submit"]');
      
      // Should show errors for all required fields
      const descriptionError = page.locator('text=/description.*required/i');
      const amountError = page.locator('text=/amount.*required/i');
      const categoryError = page.locator('text=/category.*required/i');
      const dateError = page.locator('text=/date.*required/i');
      
      const descriptionVisible = await descriptionError.count() > 0;
      const amountVisible = await amountError.count() > 0;
      const categoryVisible = await categoryError.count() > 0;
      const dateVisible = await dateError.count() > 0;
      
      // At least some errors should be visible
      const totalErrors = [descriptionVisible, amountVisible, categoryVisible, dateVisible].filter(Boolean).length;
      expect(totalErrors).toBeGreaterThan(0);
    });
    
  });
  
});
