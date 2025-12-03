# Expense Tracker - Task Checklist

## Overview
This document provides actionable task checklists for implementing the Expense Tracker application. Each section maps to a GitHub Issue with granular tasks that can be completed incrementally.

**Repository:** [rostislav-sergeenkov/copilot-demo-laravel2](https://github.com/rostislav-sergeenkov/copilot-demo-laravel2)  
**Tracking Epic:** [#33](https://github.com/rostislav-sergeenkov/copilot-demo-laravel2/issues/33)

---

## Phase 1: Foundation

### Task 1.1: Initialize Laravel Application
**Issue:** [#21](https://github.com/rostislav-sergeenkov/copilot-demo-laravel2/issues/21)  
**Priority:** High  
**Due:** December 3, 2025

#### Prerequisites
- [ ] PHP 8.2+ installed
- [ ] Composer installed
- [ ] Git configured

#### Tasks
- [ ] Create Laravel project
  ```powershell
  cd c:\Users\Rostislav_Sergeenkov\ROST\PROJECTS\copilot-demo-laravel2
  composer create-project laravel/laravel laravel-app
  ```
- [ ] Navigate to project directory
  ```powershell
  cd laravel-app
  ```
- [ ] Create SQLite database file
  ```powershell
  New-Item -Path "database/database.sqlite" -ItemType File -Force
  ```
- [ ] Configure `.env` file
  - [ ] Set `DB_CONNECTION=sqlite`
  - [ ] Set `DB_DATABASE=database/database.sqlite`
  - [ ] Set `APP_NAME="Expense Tracker"`
  - [ ] Set `APP_TIMEZONE=UTC`
- [ ] Verify installation
  ```powershell
  php artisan migrate
  php artisan serve
  ```
- [ ] Access http://localhost:8000 and verify Laravel welcome page

#### Verification
- [ ] `php artisan migrate` completes without errors
- [ ] Application loads in browser
- [ ] Database file exists at `database/database.sqlite`

---

### Task 1.2: Create Database Migration and Model
**Issue:** [#22](https://github.com/rostislav-sergeenkov/copilot-demo-laravel2/issues/22)  
**Priority:** High  
**Due:** December 3, 2025

#### Tasks
- [ ] Generate model with migration and factory
  ```powershell
  php artisan make:model Expense -mf
  ```
- [ ] Edit migration file `database/migrations/xxxx_create_expenses_table.php`
  - [ ] Add `description` column (string, 255)
  - [ ] Add `amount` column (decimal 10,2)
  - [ ] Add `category` column (string)
  - [ ] Add `date` column (date)
  - [ ] Add `softDeletes()`
  - [ ] Add index on `date` column
  - [ ] Add index on `category` column
  - [ ] Add composite index on `date, category`
- [ ] Edit model file `app/Models/Expense.php`
  - [ ] Add `use SoftDeletes` trait
  - [ ] Define `$fillable` array
  - [ ] Define `$casts` for date and amount
  - [ ] Add `CATEGORIES` constant with all 7 categories
- [ ] Edit factory file `database/factories/ExpenseFactory.php`
  - [ ] Define `definition()` method with faker data
  - [ ] Use random category from `Expense::CATEGORIES`
  - [ ] Generate realistic amounts ($1-500)
  - [ ] Generate dates within last 3 months
- [ ] Run migration
  ```powershell
  php artisan migrate
  ```

#### Verification
- [ ] Migration runs successfully
- [ ] Table `expenses` exists with correct columns
- [ ] Indexes exist on date and category
- [ ] Factory generates valid expense records

---

### Task 1.3: Create Database Seeder
**Issue:** [#32](https://github.com/rostislav-sergeenkov/copilot-demo-laravel2/issues/32)  
**Priority:** Medium  
**Due:** December 4, 2025

#### Tasks
- [ ] Generate seeder
  ```powershell
  php artisan make:seeder ExpenseSeeder
  ```
- [ ] Edit `database/seeders/ExpenseSeeder.php`
  - [ ] Clear existing expenses (optional)
  - [ ] Create 50+ sample expenses using factory
  - [ ] Ensure all categories represented
  - [ ] Ensure dates span 3+ months
- [ ] Register seeder in `database/seeders/DatabaseSeeder.php`
  - [ ] Add `$this->call(ExpenseSeeder::class)`
- [ ] Run seeder
  ```powershell
  php artisan db:seed --class=ExpenseSeeder
  ```

#### Sample Data Distribution
- [ ] Groceries: ~10 expenses
- [ ] Transport: ~8 expenses
- [ ] Housing and Utilities: ~5 expenses
- [ ] Restaurants and Cafes: ~10 expenses
- [ ] Health and Medicine: ~5 expenses
- [ ] Clothing & Footwear: ~5 expenses
- [ ] Entertainment: ~7 expenses

#### Verification
- [ ] Seeder runs without errors
- [ ] 50+ records in expenses table
- [ ] All categories have at least 1 expense
- [ ] Dates span multiple months

---

## Phase 2: Core Functionality

### Task 2.1: Implement ExpenseController
**Issue:** [#23](https://github.com/rostislav-sergeenkov/copilot-demo-laravel2/issues/23)  
**Priority:** High  
**Due:** December 4, 2025

#### Tasks
- [ ] Generate controller
  ```powershell
  php artisan make:controller ExpenseController --resource
  ```
- [ ] Generate form request classes
  ```powershell
  php artisan make:request StoreExpenseRequest
  php artisan make:request UpdateExpenseRequest
  ```
- [ ] Edit `app/Http/Requests/StoreExpenseRequest.php`
  - [ ] Set `authorize()` to return `true`
  - [ ] Define validation rules
  - [ ] Add custom error messages (optional)
- [ ] Edit `app/Http/Requests/UpdateExpenseRequest.php`
  - [ ] Copy rules from StoreExpenseRequest
- [ ] Edit `app/Http/Controllers/ExpenseController.php`
  - [ ] Implement `index()` - paginate 15, order by date desc
  - [ ] Implement `create()` - return view with categories
  - [ ] Implement `store()` - validate and create
  - [ ] Implement `edit()` - find expense, return view
  - [ ] Implement `update()` - validate and update
  - [ ] Implement `destroy()` - soft delete
- [ ] Edit `routes/web.php`
  - [ ] Add redirect from `/` to expenses index
  - [ ] Add resource route for expenses

#### Controller Methods Checklist
- [ ] `index()`: Lists expenses with pagination
- [ ] `create()`: Shows create form
- [ ] `store()`: Validates and saves new expense
- [ ] `edit($expense)`: Shows edit form
- [ ] `update($expense)`: Validates and updates
- [ ] `destroy($expense)`: Soft deletes expense

#### Verification
- [ ] All routes registered (`php artisan route:list`)
- [ ] CRUD operations work via Postman/browser
- [ ] Validation errors returned correctly
- [ ] Success redirects work

---

### Task 2.2: Create UI Layout
**Issue:** [#24](https://github.com/rostislav-sergeenkov/copilot-demo-laravel2/issues/24)  
**Priority:** High  
**Due:** December 4, 2025

#### Tasks
- [ ] Create layouts directory
  ```powershell
  New-Item -Path "resources/views/layouts" -ItemType Directory -Force
  ```
- [ ] Create `resources/views/layouts/app.blade.php`
  - [ ] Add HTML5 doctype and structure
  - [ ] Add meta viewport for responsiveness
  - [ ] Add CSRF meta tag
  - [ ] Add inline CSS styles (Material Design)
  - [ ] Create header with navigation
  - [ ] Add flash message display area
  - [ ] Add `@yield('content')` section
  - [ ] Add inline JavaScript for interactions
- [ ] Define CSS variables
  - [ ] Primary color (#1976D2)
  - [ ] Error color (#D32F2F)
  - [ ] Success color (#388E3C)
  - [ ] Spacing unit (8px)
- [ ] Create CSS components
  - [ ] `.container` - max-width wrapper
  - [ ] `.card` - elevated content boxes
  - [ ] `.btn` - button base styles
  - [ ] `.btn-primary`, `.btn-danger` - variants
  - [ ] `.form-group`, `.form-input`, `.form-label`
  - [ ] `.table` - data tables
  - [ ] `.alert`, `.alert-success`, `.alert-error`
  - [ ] `.nav`, `.nav-links` - navigation
- [ ] Create JavaScript functions
  - [ ] Auto-hide flash messages
  - [ ] Delete confirmation dialog
  - [ ] Form validation feedback

#### Verification
- [ ] Layout renders correctly
- [ ] Navigation links work
- [ ] Responsive on mobile (320px+)
- [ ] Flash messages display and auto-hide

---

### Task 2.3: Implement CRUD Views
**Issue:** [#25](https://github.com/rostislav-sergeenkov/copilot-demo-laravel2/issues/25)  
**Priority:** High  
**Due:** December 5, 2025

#### Tasks
- [ ] Create expenses views directory
  ```powershell
  New-Item -Path "resources/views/expenses" -ItemType Directory -Force
  ```
- [ ] Create `resources/views/expenses/_form.blade.php` (partial)
  - [ ] Description text input
  - [ ] Amount number input (step="0.01")
  - [ ] Category select dropdown
  - [ ] Date input (max=today)
  - [ ] Error display for each field
  - [ ] Use `old()` for form repopulation
- [ ] Create `resources/views/expenses/index.blade.php`
  - [ ] Page title
  - [ ] "Add Expense" button
  - [ ] Category filter dropdown
  - [ ] Expenses table (Date, Description, Category, Amount, Actions)
  - [ ] Edit and Delete action buttons
  - [ ] Pagination links
  - [ ] Empty state when no expenses
- [ ] Create `resources/views/expenses/create.blade.php`
  - [ ] Page title "Add Expense"
  - [ ] Include `_form` partial
  - [ ] Submit button "Create"
  - [ ] Cancel link to index
- [ ] Create `resources/views/expenses/edit.blade.php`
  - [ ] Page title "Edit Expense"
  - [ ] Include `_form` partial with expense data
  - [ ] Submit button "Update"
  - [ ] Cancel link to index
  - [ ] Method spoofing for PUT

#### Form Fields Checklist
- [ ] Description: `<input type="text" name="description" required maxlength="255">`
- [ ] Amount: `<input type="number" name="amount" step="0.01" min="0.01" required>`
- [ ] Category: `<select name="category" required>` with all options
- [ ] Date: `<input type="date" name="date" max="{{ date('Y-m-d') }}" required>`

#### Verification
- [ ] Index displays expenses in table
- [ ] Create form submits successfully
- [ ] Edit form loads with existing data
- [ ] Validation errors display inline
- [ ] Delete confirms before action
- [ ] Pagination works correctly

---

## Phase 3: Feature Implementation

### Task 3.1: Implement Daily View
**Issue:** [#26](https://github.com/rostislav-sergeenkov/copilot-demo-laravel2/issues/26)  
**Priority:** Medium  
**Due:** December 5, 2025

#### Tasks
- [ ] Add route for daily view in `routes/web.php`
  ```php
  Route::get('expenses/daily', [ExpenseController::class, 'daily'])->name('expenses.daily');
  ```
- [ ] Add `daily()` method to ExpenseController
  - [ ] Get date from query parameter (default today)
  - [ ] Fetch expenses for selected date
  - [ ] Calculate daily total
  - [ ] Calculate previous/next dates
  - [ ] Return view with data
- [ ] Create `resources/views/expenses/daily.blade.php`
  - [ ] Date navigation (prev/next arrows)
  - [ ] Date picker input
  - [ ] "Today" quick link
  - [ ] Daily total display
  - [ ] List of expenses for date
  - [ ] Empty state for no expenses

#### Verification
- [ ] Daily view loads at `/expenses/daily`
- [ ] Date navigation works
- [ ] Correct expenses shown for date
- [ ] Daily total is accurate
- [ ] Empty state displays when no expenses

---

### Task 3.2: Implement Monthly View
**Issue:** [#27](https://github.com/rostislav-sergeenkov/copilot-demo-laravel2/issues/27)  
**Priority:** Medium  
**Due:** December 5, 2025

#### Tasks
- [ ] Add route for monthly view in `routes/web.php`
  ```php
  Route::get('expenses/monthly', [ExpenseController::class, 'monthly'])->name('expenses.monthly');
  ```
- [ ] Add `monthly()` method to ExpenseController
  - [ ] Get month from query parameter (default current)
  - [ ] Fetch expenses for selected month
  - [ ] Calculate monthly total
  - [ ] Calculate category breakdown
  - [ ] Calculate previous/next months
  - [ ] Return view with data
- [ ] Create `resources/views/expenses/monthly.blade.php`
  - [ ] Month navigation (prev/next)
  - [ ] Month/year picker
  - [ ] "This Month" quick link
  - [ ] Monthly total display
  - [ ] Category breakdown with amounts and percentages
  - [ ] Daily breakdown (optional)

#### Verification
- [ ] Monthly view loads at `/expenses/monthly`
- [ ] Month navigation works
- [ ] Monthly total is accurate
- [ ] Category breakdown shows correct percentages
- [ ] Empty state for months with no expenses

---

### Task 3.3: Implement Category Filtering
**Issue:** [#28](https://github.com/rostislav-sergeenkov/copilot-demo-laravel2/issues/28)  
**Priority:** Medium  
**Due:** December 6, 2025

#### Tasks
- [ ] Update `index()` method in ExpenseController
  - [ ] Accept `category` query parameter
  - [ ] Filter expenses when category provided
  - [ ] Pass selected category to view
- [ ] Update `daily()` method
  - [ ] Accept `category` query parameter
  - [ ] Filter expenses when category provided
  - [ ] Update daily total for filtered results
- [ ] Update `monthly()` method
  - [ ] Accept `category` query parameter
  - [ ] Filter expenses when category provided
  - [ ] Update totals for filtered results
- [ ] Update index view
  - [ ] Add category dropdown filter
  - [ ] Preserve filter in pagination links
  - [ ] Show active filter indicator
  - [ ] Add "Clear Filter" option
- [ ] Update daily view
  - [ ] Add category filter dropdown
  - [ ] Preserve filter in date navigation
- [ ] Update monthly view
  - [ ] Add category filter dropdown
  - [ ] Preserve filter in month navigation

#### Verification
- [ ] Filter works on index page
- [ ] Filter works on daily view
- [ ] Filter works on monthly view
- [ ] Totals update when filtered
- [ ] Filter persists through pagination
- [ ] Clear filter returns all expenses

---

## Phase 4: Quality Assurance

### Task 4.1: Implement Unit Tests
**Issue:** [#29](https://github.com/rostislav-sergeenkov/copilot-demo-laravel2/issues/29)  
**Priority:** High  
**Due:** December 6, 2025

#### Tasks
- [ ] Configure PHPUnit for SQLite in-memory
  - [ ] Edit `phpunit.xml`
  - [ ] Set `DB_CONNECTION` to `sqlite`
  - [ ] Set `DB_DATABASE` to `:memory:`
- [ ] Create test directory
  ```powershell
  New-Item -Path "tests/Unit/Models" -ItemType Directory -Force
  ```
- [ ] Generate unit test
  ```powershell
  php artisan make:test Models/ExpenseTest --unit
  ```
- [ ] Implement test cases in `tests/Unit/Models/ExpenseTest.php`
  - [ ] `test_can_create_expense_with_valid_data()`
  - [ ] `test_expense_has_fillable_attributes()`
  - [ ] `test_expense_casts_date_correctly()`
  - [ ] `test_expense_casts_amount_to_decimal()`
  - [ ] `test_expense_can_be_soft_deleted()`
  - [ ] `test_soft_deleted_expense_excluded_from_query()`
  - [ ] `test_soft_deleted_expense_can_be_restored()`
  - [ ] `test_categories_constant_exists()`
  - [ ] `test_factory_creates_valid_expense()`
- [ ] Run unit tests
  ```powershell
  php artisan test --filter=ExpenseTest
  ```

#### Verification
- [ ] All unit tests pass
- [ ] Tests run in under 5 seconds
- [ ] No database state leaks between tests

---

### Task 4.2: Implement Feature Tests
**Issue:** [#30](https://github.com/rostislav-sergeenkov/copilot-demo-laravel2/issues/30)  
**Priority:** High  
**Due:** December 7, 2025

#### Tasks
- [ ] Generate feature test
  ```powershell
  php artisan make:test ExpenseControllerTest
  ```
- [ ] Implement index tests
  - [ ] `test_index_page_loads_successfully()`
  - [ ] `test_index_displays_expenses()`
  - [ ] `test_index_paginates_results()`
  - [ ] `test_index_shows_empty_state()`
- [ ] Implement create tests
  - [ ] `test_create_form_displays()`
  - [ ] `test_store_creates_expense()`
  - [ ] `test_store_validates_required_fields()`
  - [ ] `test_store_validates_amount_range()`
  - [ ] `test_store_validates_category()`
  - [ ] `test_store_validates_date_not_future()`
- [ ] Implement edit/update tests
  - [ ] `test_edit_displays_expense_data()`
  - [ ] `test_edit_returns_404_for_missing()`
  - [ ] `test_update_modifies_expense()`
  - [ ] `test_update_validates_data()`
- [ ] Implement delete tests
  - [ ] `test_destroy_soft_deletes_expense()`
  - [ ] `test_deleted_expense_not_in_index()`
- [ ] Implement filter tests
  - [ ] `test_index_filters_by_category()`
  - [ ] `test_daily_filters_by_category()`
  - [ ] `test_monthly_filters_by_category()`
- [ ] Implement view tests
  - [ ] `test_daily_view_displays_expenses()`
  - [ ] `test_daily_view_calculates_total()`
  - [ ] `test_monthly_view_displays_expenses()`
  - [ ] `test_monthly_view_shows_category_breakdown()`
- [ ] Run all tests
  ```powershell
  php artisan test
  ```

#### Verification
- [ ] All feature tests pass
- [ ] Tests cover success and error paths
- [ ] Tests complete in under 30 seconds

---

### Task 4.3: Set Up GitHub Actions CI/CD
**Issue:** [#31](https://github.com/rostislav-sergeenkov/copilot-demo-laravel2/issues/31)  
**Priority:** High  
**Due:** December 7, 2025

#### Tasks
- [ ] Create workflows directory
  ```powershell
  New-Item -Path ".github/workflows" -ItemType Directory -Force
  ```
- [ ] Create `.github/workflows/laravel.yml`
  - [ ] Define workflow name
  - [ ] Set trigger on push to main
  - [ ] Set trigger on pull_request to main
  - [ ] Configure job: tests
  - [ ] Add checkout step
  - [ ] Add PHP setup step (8.2, extensions)
  - [ ] Add Composer cache step
  - [ ] Add install dependencies step
  - [ ] Add environment setup step
  - [ ] Add run tests step
- [ ] Commit and push workflow
- [ ] Verify workflow runs on GitHub
- [ ] Configure branch protection rules
  - [ ] Require status checks to pass
  - [ ] Require "tests" job to pass
- [ ] Add status badge to README

#### Workflow Steps Checklist
- [ ] `actions/checkout@v4`
- [ ] `shivammathur/setup-php@v2` with PHP 8.2
- [ ] `actions/cache@v3` for Composer
- [ ] `composer install`
- [ ] `cp .env.example .env`
- [ ] `php artisan key:generate`
- [ ] `touch database/database.sqlite`
- [ ] `php artisan test`

#### Verification
- [ ] Workflow triggers on push
- [ ] Workflow triggers on PR
- [ ] Tests pass in CI
- [ ] Failed tests block merge
- [ ] Badge displays in README

---

## Quick Reference

### Commands Cheatsheet

```powershell
# Navigate to project
cd c:\Users\Rostislav_Sergeenkov\ROST\PROJECTS\copilot-demo-laravel2\laravel-app

# Database
php artisan migrate
php artisan migrate:fresh
php artisan db:seed
php artisan db:seed --class=ExpenseSeeder

# Development
php artisan serve
php artisan route:list
php artisan tinker

# Testing
php artisan test
php artisan test --filter=ExpenseTest
php artisan test --filter=ExpenseControllerTest
php artisan test --coverage

# Code Generation
php artisan make:model Expense -mf
php artisan make:controller ExpenseController --resource
php artisan make:request StoreExpenseRequest
php artisan make:seeder ExpenseSeeder
php artisan make:test ExpenseTest --unit
php artisan make:test ExpenseControllerTest
```

### File Locations

| File | Path |
|------|------|
| Environment | `laravel-app/.env` |
| Routes | `laravel-app/routes/web.php` |
| Expense Model | `laravel-app/app/Models/Expense.php` |
| Controller | `laravel-app/app/Http/Controllers/ExpenseController.php` |
| Migration | `laravel-app/database/migrations/*_create_expenses_table.php` |
| Factory | `laravel-app/database/factories/ExpenseFactory.php` |
| Seeder | `laravel-app/database/seeders/ExpenseSeeder.php` |
| Layout | `laravel-app/resources/views/layouts/app.blade.php` |
| Index View | `laravel-app/resources/views/expenses/index.blade.php` |
| Create View | `laravel-app/resources/views/expenses/create.blade.php` |
| Edit View | `laravel-app/resources/views/expenses/edit.blade.php` |
| Daily View | `laravel-app/resources/views/expenses/daily.blade.php` |
| Monthly View | `laravel-app/resources/views/expenses/monthly.blade.php` |
| Unit Tests | `laravel-app/tests/Unit/Models/ExpenseTest.php` |
| Feature Tests | `laravel-app/tests/Feature/ExpenseControllerTest.php` |
| CI Workflow | `.github/workflows/laravel.yml` |

---

## Progress Tracking

| Task | Issue | Status | Completed |
|------|-------|--------|-----------|
| Initialize Laravel | #21 | ⬜ Not Started | |
| Create Migration/Model | #22 | ⬜ Not Started | |
| Create Seeder | #32 | ⬜ Not Started | |
| Implement Controller | #23 | ⬜ Not Started | |
| Create UI Layout | #24 | ⬜ Not Started | |
| Implement CRUD Views | #25 | ⬜ Not Started | |
| Daily View | #26 | ⬜ Not Started | |
| Monthly View | #27 | ⬜ Not Started | |
| Category Filtering | #28 | ⬜ Not Started | |
| Unit Tests | #29 | ⬜ Not Started | |
| Feature Tests | #30 | ⬜ Not Started | |
| GitHub Actions | #31 | ⬜ Not Started | |

**Legend:** ⬜ Not Started | 🟡 In Progress | ✅ Complete
