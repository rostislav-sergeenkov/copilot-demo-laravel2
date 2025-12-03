# Project Acceptance Checklist

**Project:** Expense Tracker  
**Version:** 1.0  
**Date:** December 2025

Use this checklist to verify the application meets all acceptance criteria before release.

---

## ✅ Feature Acceptance

### F1: Expense CRUD Interface

#### Create Expense
- [ ] "Add Expense" button visible on index page
- [ ] Create form loads at `/expenses/create`
- [ ] Form contains: Description, Amount, Category, Date fields
- [ ] Description field accepts up to 255 characters
- [ ] Amount field accepts decimals (0.01 - 999999.99)
- [ ] Category dropdown shows all 7 categories
- [ ] Date picker restricts to today or earlier
- [ ] Date picker restricts to within 5 years
- [ ] Submit creates expense in database
- [ ] Redirects to index with success message
- [ ] Cancel button returns to index without saving

#### Read Expenses (Index)
- [ ] Index page loads at `/expenses`
- [ ] Table displays columns: Date, Description, Category, Amount, Actions
- [ ] Expenses sorted by date (newest first)
- [ ] Pagination works (15 items per page)
- [ ] Amounts display as currency ($X.XX)
- [ ] Dates display in readable format (December 2, 2025)
- [ ] Empty state shows when no expenses exist

#### Update Expense
- [ ] Edit button visible for each expense row
- [ ] Edit form loads at `/expenses/{id}/edit`
- [ ] Form pre-populates with existing expense data
- [ ] Submit updates expense in database
- [ ] Redirects to index with success message
- [ ] Cancel button returns to index without saving

#### Delete Expense
- [ ] Delete button visible for each expense row
- [ ] Confirmation dialog appears before deletion
- [ ] Expense is soft-deleted (not permanently removed)
- [ ] Deleted expense no longer appears in index
- [ ] Redirects to index with success message

---

### F2: Daily Expenses View
- [ ] Page loads at `/expenses/daily`
- [ ] Date selector shows current date by default
- [ ] Previous/Next day navigation works
- [ ] "Today" button returns to current date
- [ ] Expenses grouped by selected date
- [ ] Daily total is calculated correctly
- [ ] Category filter works on daily view
- [ ] Empty state shows when no expenses for date
- [ ] Amounts display as currency format

---

### F3: Monthly Expenses View
- [ ] Page loads at `/expenses/monthly`
- [ ] Month selector shows current month by default
- [ ] Previous/Next month navigation works
- [ ] "This Month" button returns to current month
- [ ] Monthly total is calculated correctly
- [ ] Category breakdown shows all categories
- [ ] Percentages calculated correctly (sum = 100%)
- [ ] Categories with $0 show 0%
- [ ] Empty state shows when no expenses for month
- [ ] Amounts display as currency format

---

### F4: Category Filtering
- [ ] Category filter dropdown on index page
- [ ] Category filter dropdown on daily view
- [ ] Category filter dropdown on monthly view
- [ ] Filter shows only matching expenses
- [ ] Totals update to reflect filtered results
- [ ] "All Categories" option clears filter
- [ ] Filter persists through pagination
- [ ] Filter state preserved in URL

---

## ✅ Data Validation

### Description Field
- [ ] Required - shows error when empty
- [ ] Max 255 characters - shows error when exceeded
- [ ] Accepts special characters and Unicode

### Amount Field
- [ ] Required - shows error when empty
- [ ] Minimum $0.01 - shows error below
- [ ] Maximum $999,999.99 - shows error above
- [ ] Accepts decimal values (2 places)
- [ ] Rejects non-numeric input

### Category Field
- [ ] Required - shows error when not selected
- [ ] Only accepts valid category values
- [ ] Shows error for invalid category

### Date Field
- [ ] Required - shows error when empty
- [ ] Cannot be future date - shows error
- [ ] Cannot be older than 5 years - shows error
- [ ] Accepts valid date format

### Validation Errors
- [ ] Inline errors display next to fields
- [ ] Flash message appears at top of form
- [ ] Form repopulates with previous input
- [ ] All validation rules work server-side

---

## ✅ User Interface

### Layout & Navigation
- [ ] Header displays app title "Expense Tracker"
- [ ] Navigation links: All Expenses, Daily, Monthly
- [ ] Current page highlighted in navigation
- [ ] Footer (if applicable) displays correctly
- [ ] Flash messages appear and auto-hide

### Material Design Compliance
- [ ] 8px grid system spacing used
- [ ] Cards have elevation shadows
- [ ] Primary color consistent throughout
- [ ] Typography follows scale guidelines
- [ ] Buttons have proper hover/active states

### Responsive Design
- [ ] Desktop (1440px+) - full layout
- [ ] Laptop (1024px) - adjusted layout
- [ ] Tablet (768px) - responsive layout
- [ ] Mobile (320px) - mobile-friendly layout
- [ ] Touch targets minimum 44x44px on mobile
- [ ] Tables scroll horizontally on small screens

### Empty States
- [ ] Index: "No expenses recorded yet..."
- [ ] Filtered index: "No expenses found for..."
- [ ] Daily: "No expenses recorded for this date."
- [ ] Monthly: "No expenses recorded for this month."

---

## ✅ Accessibility

### Keyboard Navigation
- [ ] All interactive elements focusable via Tab
- [ ] Focus order is logical
- [ ] Focus indicators visible
- [ ] Enter/Space activates buttons
- [ ] Escape closes dialogs

### Screen Reader Support
- [ ] All form inputs have labels
- [ ] ARIA labels on icon buttons
- [ ] Table headers properly associated
- [ ] Error messages announced
- [ ] Page titles descriptive

### Visual Accessibility
- [ ] Color contrast ratio ≥ 4.5:1
- [ ] Information not conveyed by color alone
- [ ] Text resizable to 200% without loss
- [ ] No content cut off when zoomed

---

## ✅ Performance

### Page Load
- [ ] Initial page load < 2 seconds
- [ ] Time to Interactive < 3 seconds
- [ ] No layout shift after load

### Database
- [ ] Queries per page < 10
- [ ] Index on `date` column exists
- [ ] Index on `category` column exists
- [ ] Composite index on `date, category` exists
- [ ] Pagination prevents loading all records

### Assets
- [ ] CSS loads without render blocking
- [ ] JavaScript deferred or at end of body
- [ ] No unused CSS/JS loaded
- [ ] Images optimized (if any)

---

## ✅ Testing

### Unit Tests (Expense Model)
- [ ] `test_can_create_expense` passes
- [ ] `test_description_required` passes
- [ ] `test_amount_boundaries` passes
- [ ] `test_category_validation` passes
- [ ] `test_date_not_future` passes
- [ ] `test_soft_delete` passes
- [ ] Model coverage = 100%

### Feature Tests (ExpenseController)
- [ ] `test_index_displays_expenses` passes
- [ ] `test_create_form_displays` passes
- [ ] `test_store_creates_expense` passes
- [ ] `test_store_validation_fails` passes
- [ ] `test_edit_displays_expense` passes
- [ ] `test_update_modifies_expense` passes
- [ ] `test_destroy_soft_deletes` passes
- [ ] `test_daily_view_groups_by_date` passes
- [ ] `test_monthly_view_aggregates` passes
- [ ] `test_category_filter_works` passes
- [ ] Controller coverage ≥ 80%

### Test Execution
- [ ] All tests pass locally
- [ ] Tests run in < 30 seconds
- [ ] No flaky tests

---

## ✅ CI/CD Pipeline

### GitHub Actions Workflow
- [ ] Workflow file exists at `.github/workflows/laravel.yml`
- [ ] Triggers on push to `main`
- [ ] Triggers on pull requests to `main`
- [ ] PHP 8.2 environment configured
- [ ] Composer dependencies cached
- [ ] SQLite test database configured
- [ ] All tests run in CI
- [ ] Workflow completes in < 5 minutes

### Branch Protection
- [ ] Status checks required before merge
- [ ] Tests must pass before merge
- [ ] Failed tests block merge

### Repository
- [ ] All code committed to GitHub
- [ ] README exists with setup instructions
- [ ] Status badge displays in README

---

## ✅ Code Quality

### Laravel Best Practices
- [ ] PSR-12 coding style followed
- [ ] Type declarations used
- [ ] Form Request classes for validation
- [ ] Eloquent ORM used (no raw SQL)
- [ ] Route model binding used
- [ ] CSRF protection enabled

### File Structure
- [ ] `app/Models/Expense.php` exists
- [ ] `app/Http/Controllers/ExpenseController.php` exists
- [ ] `app/Http/Requests/StoreExpenseRequest.php` exists
- [ ] `app/Http/Requests/UpdateExpenseRequest.php` exists
- [ ] Migration file exists
- [ ] Factory file exists
- [ ] Seeder file exists
- [ ] All Blade views exist

### Frontend
- [ ] Vanilla CSS (no frameworks)
- [ ] Vanilla JavaScript (no frameworks)
- [ ] No jQuery
- [ ] No build tools required
- [ ] CSS organized with comments
- [ ] JavaScript uses ES6+

---

## ✅ Database

### Schema
- [ ] `expenses` table exists
- [ ] `id` column (bigint, PK, auto-increment)
- [ ] `description` column (string 255)
- [ ] `amount` column (decimal 10,2)
- [ ] `category` column (string)
- [ ] `date` column (date)
- [ ] `created_at` column (timestamp)
- [ ] `updated_at` column (timestamp)
- [ ] `deleted_at` column (timestamp, nullable)

### Data Integrity
- [ ] Sample data can be seeded
- [ ] 50+ sample expenses created
- [ ] All categories represented
- [ ] Dates span multiple months
- [ ] Soft delete works correctly

---

## ✅ Error Handling

### HTTP Errors
- [ ] 404 page displays for invalid expense ID
- [ ] 404 page links back to index
- [ ] 500 errors show generic message (no stack trace)
- [ ] Validation errors return 422 status

### Edge Cases
- [ ] Empty database handled gracefully
- [ ] Invalid category in URL handled
- [ ] Invalid date in URL handled
- [ ] Very large amounts display correctly
- [ ] Special characters in description display correctly

---

## Sign-Off

| Role | Name | Date | Signature |
|------|------|------|-----------|
| Developer | | | |
| Reviewer | | | |
| Product Owner | | | |

---

**Total Checklist Items:** 180+  
**Acceptance Threshold:** All items must pass (100%)
