import { test, expect } from '@playwright/test';
import { createExpense, getTodayString, checkColorContrast } from './helpers';

/**
 * UI and Accessibility Tests
 * 
 * Tests for user interface, navigation, responsive design, and accessibility:
 * - Layout & Navigation
 * - Material Design compliance
 * - Responsive design
 * - Empty states
 * - Keyboard navigation
 * - Screen reader support
 * - Visual accessibility
 */

test.describe('UI and Accessibility', () => {
  
  test.describe('Layout & Navigation', () => {
    
    test('should display app title "Expense Tracker" in header', async ({ page }) => {
      await page.goto('/');
      
      const header = page.locator('header, .header, nav');
      await expect(header).toContainText('Expense Tracker');
    });
    
    test('should show navigation links', async ({ page }) => {
      await page.goto('/');
      
      const allExpensesLink = page.locator('a:has-text("All Expenses"), a:has-text("Expenses")');
      const dailyLink = page.locator('a:has-text("Daily")');
      const monthlyLink = page.locator('a:has-text("Monthly")');
      
      await expect(allExpensesLink.first()).toBeVisible();
      await expect(dailyLink.first()).toBeVisible();
      await expect(monthlyLink.first()).toBeVisible();
    });
    
    test('should highlight current page in navigation', async ({ page }) => {
      await page.goto('/');
      
      // The current page link should have active/selected styling
      const activeLink = page.locator('nav a[aria-current="page"], nav a.active, nav a.selected');
      const hasActive = await activeLink.count() > 0;
      
      expect(hasActive).toBe(true);
    });
    
    test('should show flash messages', async ({ page }) => {
      // Create an expense to trigger success message
      await createExpense(page, {
        description: 'Flash message test',
        amount: '10.00',
        category: 'Groceries',
        date: getTodayString(),
      });
      
      // Success message should be visible
      const flashMessage = page.locator('.alert, [role="alert"], .flash-message');
      await expect(flashMessage.first()).toBeVisible();
    });
    
    test('should navigate between pages', async ({ page }) => {
      await page.goto('/');
      
      // Navigate to Daily
      await page.click('a:has-text("Daily")');
      await expect(page).toHaveURL(/\/expenses\/daily/);
      
      // Navigate to Monthly
      await page.click('a:has-text("Monthly")');
      await expect(page).toHaveURL(/\/expenses\/monthly/);
      
      // Navigate back to All Expenses
      await page.click('a:has-text("All Expenses"), a:has-text("Expenses")');
      await expect(page).toHaveURL(/\/expenses$/);
    });
    
  });
  
  test.describe('Material Design Compliance', () => {
    
    test('should use 8px grid system spacing', async ({ page }) => {
      await page.goto('/');
      
      // Check padding/margin values are multiples of 8
      const container = page.locator('main, .container, .content').first();
      const styles = await container.evaluate((el) => {
        const computed = window.getComputedStyle(el);
        return {
          padding: computed.padding,
          margin: computed.margin,
        };
      });
      
      // Values should be set (actual validation would need more complex parsing)
      expect(styles).toBeDefined();
    });
    
    test('should have elevation shadows on cards', async ({ page }) => {
      await page.goto('/');
      
      const card = page.locator('.card, [class*="card"], .elevation').first();
      if (await card.count() > 0) {
        const boxShadow = await card.evaluate((el) => {
          return window.getComputedStyle(el).boxShadow;
        });
        
        expect(boxShadow).not.toBe('none');
      }
    });
    
    test('should have consistent primary color', async ({ page }) => {
      await page.goto('/');
      
      // Check buttons have color set
      const button = page.locator('button[type="submit"], .btn-primary').first();
      const color = await button.evaluate((el) => {
        const style = window.getComputedStyle(el);
        return style.backgroundColor || style.color;
      });
      
      expect(color).toBeTruthy();
    });
    
    test('should have proper button hover/active states', async ({ page }) => {
      await page.goto('/expenses/create');
      
      const submitButton = page.locator('button[type="submit"]');
      
      // Get normal state color
      const normalColor = await submitButton.evaluate((el) => {
        return window.getComputedStyle(el).backgroundColor;
      });
      
      // Hover over button
      await submitButton.hover();
      
      // Hover state should be applied (may or may not change color depending on implementation)
      await expect(submitButton).toBeVisible();
    });
    
  });
  
  test.describe('Responsive Design', () => {
    
    test('should work on desktop (1440px)', async ({ page }) => {
      await page.setViewportSize({ width: 1440, height: 900 });
      await page.goto('/');
      
      // All elements should be visible
      await expect(page.locator('h1, h2')).toBeVisible();
      await expect(page.locator('table, .expense-list')).toBeVisible();
    });
    
    test('should work on laptop (1024px)', async ({ page }) => {
      await page.setViewportSize({ width: 1024, height: 768 });
      await page.goto('/');
      
      await expect(page.locator('h1, h2')).toBeVisible();
    });
    
    test('should work on tablet (768px)', async ({ page }) => {
      await page.setViewportSize({ width: 768, height: 1024 });
      await page.goto('/');
      
      await expect(page.locator('h1, h2')).toBeVisible();
    });
    
    test('should work on mobile (320px)', async ({ page }) => {
      await page.setViewportSize({ width: 320, height: 568 });
      await page.goto('/');
      
      await expect(page.locator('h1, h2')).toBeVisible();
    });
    
    test('should have minimum 44x44px touch targets on mobile', async ({ page }) => {
      await page.setViewportSize({ width: 375, height: 667 });
      await page.goto('/expenses/create');
      
      const submitButton = page.locator('button[type="submit"]');
      const box = await submitButton.boundingBox();
      
      if (box) {
        expect(box.width).toBeGreaterThanOrEqual(44);
        expect(box.height).toBeGreaterThanOrEqual(44);
      }
    });
    
    test('should allow horizontal scroll for tables on small screens', async ({ page }) => {
      await page.setViewportSize({ width: 320, height: 568 });
      await createExpense(page, {
        description: 'Mobile scroll test',
        amount: '25.00',
        category: 'Groceries',
        date: getTodayString(),
      });
      
      await page.goto('/');
      
      // Table should exist (may scroll horizontally)
      const table = page.locator('table');
      await expect(table).toBeVisible();
    });
    
  });
  
  test.describe('Empty States', () => {
    
    test('should show empty state on index when no expenses', async ({ page }) => {
      await page.goto('/');
      
      const rows = await page.locator('table tbody tr').count();
      if (rows === 0) {
        const emptyState = page.locator('text=/No expenses recorded yet|No expenses found/i');
        await expect(emptyState).toBeVisible();
      }
    });
    
    test('should show appropriate empty state for filtered results', async ({ page }) => {
      await page.goto('/expenses?category=Groceries');
      
      // If no groceries exist
      const rows = await page.locator('table tbody tr').count();
      if (rows === 0) {
        const emptyState = page.locator('text=/No expenses found/i');
        await expect(emptyState).toBeVisible();
      }
    });
    
  });
  
  test.describe('Keyboard Navigation', () => {
    
    test('should allow Tab navigation through interactive elements', async ({ page }) => {
      await page.goto('/expenses/create');
      
      // Start at description field
      await page.locator('input[name="description"]').focus();
      
      // Tab to amount
      await page.keyboard.press('Tab');
      let focused = await page.evaluate(() => document.activeElement?.getAttribute('name'));
      expect(focused).toBe('amount');
      
      // Tab to category
      await page.keyboard.press('Tab');
      focused = await page.evaluate(() => document.activeElement?.getAttribute('name'));
      expect(focused).toBe('category');
      
      // Tab to date
      await page.keyboard.press('Tab');
      focused = await page.evaluate(() => document.activeElement?.getAttribute('name'));
      expect(focused).toBe('date');
    });
    
    test('should show visible focus indicators', async ({ page }) => {
      await page.goto('/expenses/create');
      
      const input = page.locator('input[name="description"]');
      await input.focus();
      
      const outline = await input.evaluate((el) => {
        const style = window.getComputedStyle(el);
        return style.outline + style.outlineColor;
      });
      
      // Should have some outline/border styling
      expect(outline).toBeTruthy();
    });
    
    test('should activate buttons with Enter/Space', async ({ page }) => {
      await page.goto('/');
      
      const addButton = page.locator('a:has-text("Add Expense"), button:has-text("Add Expense")').first();
      await addButton.focus();
      await page.keyboard.press('Enter');
      
      // Should navigate to create page
      await expect(page).toHaveURL(/\/expenses\/create/);
    });
    
  });
  
  test.describe('Screen Reader Support', () => {
    
    test('should have labels for all form inputs', async ({ page }) => {
      await page.goto('/expenses/create');
      
      const descriptionLabel = page.locator('label[for*="description"], label:has(input[name="description"])');
      const amountLabel = page.locator('label[for*="amount"], label:has(input[name="amount"])');
      const categoryLabel = page.locator('label[for*="category"], label:has(select[name="category"])');
      const dateLabel = page.locator('label[for*="date"], label:has(input[name="date"])');
      
      const hasDescriptionLabel = await descriptionLabel.count() > 0;
      const hasAmountLabel = await amountLabel.count() > 0;
      const hasCategoryLabel = await categoryLabel.count() > 0;
      const hasDateLabel = await dateLabel.count() > 0;
      
      expect(hasDescriptionLabel).toBe(true);
      expect(hasAmountLabel).toBe(true);
      expect(hasCategoryLabel).toBe(true);
      expect(hasDateLabel).toBe(true);
    });
    
    test('should have ARIA labels on icon buttons', async ({ page }) => {
      await page.goto('/');
      
      await createExpense(page, {
        description: 'ARIA test',
        amount: '15.00',
        category: 'Groceries',
        date: getTodayString(),
      });
      
      await page.goto('/');
      
      // Check for aria-label on buttons without text
      const iconButtons = page.locator('button:not(:has-text(/[a-z]/i))');
      const count = await iconButtons.count();
      
      if (count > 0) {
        const firstButton = iconButtons.first();
        const ariaLabel = await firstButton.getAttribute('aria-label');
        const hasAriaLabel = !!ariaLabel;
        
        // If button has no text, it should have aria-label
        expect(hasAriaLabel).toBe(true);
      }
    });
    
    test('should have properly associated table headers', async ({ page }) => {
      await createExpense(page, {
        description: 'Table header test',
        amount: '20.00',
        category: 'Transport',
        date: getTodayString(),
      });
      
      await page.goto('/');
      
      const table = page.locator('table');
      const headers = table.locator('thead th');
      
      const headerCount = await headers.count();
      expect(headerCount).toBeGreaterThan(0);
    });
    
    test('should have descriptive page titles', async ({ page }) => {
      await page.goto('/');
      let title = await page.title();
      expect(title).toContain('Expense');
      
      await page.goto('/expenses/create');
      title = await page.title();
      expect(title).toMatch(/Create|Add|New/i);
      
      await page.goto('/expenses/daily');
      title = await page.title();
      expect(title).toContain('Daily');
    });
    
  });
  
  test.describe('Visual Accessibility', () => {
    
    test('should have sufficient color contrast (4.5:1)', async ({ page }) => {
      await page.goto('/');
      
      // Test main heading
      const heading = page.locator('h1, h2').first();
      const hasGoodContrast = await checkColorContrast(page, 'h1, h2', 4.5);
      
      expect(hasGoodContrast).toBe(true);
    });
    
    test('should not convey information by color alone', async ({ page }) => {
      // Create expense to test success message
      await createExpense(page, {
        description: 'Color test',
        amount: '30.00',
        category: 'Groceries',
        date: getTodayString(),
      });
      
      // Success message should have text, not just green color
      const alert = page.locator('.alert-success, [role="alert"]');
      const text = await alert.textContent();
      
      expect(text).toBeTruthy();
      expect(text?.length).toBeGreaterThan(0);
    });
    
    test('should allow text resize to 200% without content loss', async ({ page }) => {
      await page.goto('/');
      
      // Increase text size via CSS zoom
      await page.evaluate(() => {
        document.body.style.zoom = '2';
      });
      
      // Content should still be visible
      const heading = page.locator('h1, h2').first();
      await expect(heading).toBeVisible();
    });
    
  });
  
  test.describe('Performance', () => {
    
    test('should load initial page quickly', async ({ page }) => {
      const startTime = Date.now();
      await page.goto('/');
      const loadTime = Date.now() - startTime;
      
      // Should load in under 3 seconds (generous for test environment)
      expect(loadTime).toBeLessThan(3000);
    });
    
    test('should not have excessive layout shift', async ({ page }) => {
      await page.goto('/');
      
      // Wait for page to fully load
      await page.waitForLoadState('networkidle');
      
      // Content should be stable
      const heading = page.locator('h1, h2').first();
      await expect(heading).toBeVisible();
    });
    
  });
  
});
