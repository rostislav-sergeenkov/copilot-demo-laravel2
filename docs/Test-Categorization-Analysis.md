# Test Categorization: Unit vs Feature vs E2E Tests

## Testing Philosophy

**Unit Tests**: Test individual units of code in isolation (single methods, classes, or functions). No database, no HTTP, no browser.
- **Focus**: Model methods, validation logic, helper functions, single class methods
- **Speed**: Very fast (milliseconds)
- **Examples**: Model scopes, accessors/mutators, utility functions

**Feature Tests**: Test application features through HTTP requests and database operations. Server-side only, no browser.
- **Focus**: Controllers, routes, business logic, database operations, server-side validation
- **Speed**: Fast (seconds)
- **Examples**: API endpoints, form submissions, query logic

**E2E Tests**: Test complete user workflows through a real browser. Full stack testing.
- **Focus**: UI, user interactions, visual elements, accessibility, browser behavior
- **Speed**: Slower (minutes)
- **Examples**: Form interactions, page navigation, responsive design

**Principle**: 
- Unit tests verify "functions work correctly"
- Feature tests verify "features work correctly"
- E2E tests verify "users can use it correctly"

---

## 📊 Test Pyramid Strategy

```
        /\
       /  \      E2E Tests (~80)
      /    \     - Complete workflows
     /      \    - UI/UX validation
    /--------\   - Browser testing
   /          \  
  /   Feature  \ Feature Tests (~70)
 /    Tests     \ - HTTP/Database
/                \ - Business logic
------------------
   Unit Tests     Unit Tests (~40)
                  - Model methods
                  - Helpers/Utils
```

**Recommended Distribution:**
- **Unit Tests**: 40 tests (fast, isolated)
- **Feature Tests**: 70 tests (medium speed, integration)
- **E2E Tests**: 80 tests (slower, comprehensive)
- **Total**: ~190 tests

---

## ✅ Feature Acceptance

### F1: Expense CRUD Interface

#### Create Expense

| Criterion | Unit | Feature | E2E | Rationale |
|-----------|------|---------|-----|-----------|
| "Add Expense" button visible on index page | | | ✅ | Visual element in browser |
| Create form loads at `/expenses/create` | | ✅ | ✅ | Feature: route response / E2E: rendering |
| Form contains: Description, Amount, Category, Date fields | | | ✅ | Visual form validation |
| Description field accepts up to 255 characters | ✅ | ✅ | ✅ | Unit: validation rule / Feature: request validation / E2E: UI feedback |
| Amount field accepts decimals (0.01 - 999999.99) | ✅ | ✅ | ✅ | Unit: validation rule / Feature: request validation / E2E: UI feedback |
| Category dropdown shows all 7 categories | | | ✅ | Visual UI element |
| Date picker restricts to today or earlier | ✅ | ✅ | ✅ | Unit: validation rule / Feature: request validation / E2E: UI constraint |
| Date picker restricts to within 5 years | ✅ | ✅ | ✅ | Unit: validation rule / Feature: request validation / E2E: UI constraint |
| Submit creates expense in database | | ✅ | ✅ | Feature: database operation / E2E: workflow completion |
| Redirects to index with success message | | ✅ | ✅ | Feature: HTTP redirect / E2E: message display |
| Cancel button returns to index without saving | | | ✅ | UI interaction |

**Unit Tests**: 4 items (validation rules)
**Feature Tests**: 6 items (route, database, redirects)
**E2E Tests**: 11 items (all items for complete workflow)

#### Read Expenses (Index)

| Criterion | Unit | Feature | E2E | Rationale |
|-----------|------|---------|-----|-----------|
| Index page loads at `/expenses` | | ✅ | | Route response |
| Table displays columns: Date, Description, Category, Amount, Actions | | | ✅ | Visual table structure |
| Expenses sorted by date (newest first) | ✅ | ✅ | ✅ | Unit: sort logic / Feature: query ordering / E2E: display verification |
| Pagination works (15 items per page) | | ✅ | ✅ | Feature: pagination logic / E2E: UI interaction |
| Amounts display as currency ($X.XX) | ✅ | | ✅ | Unit: formatting helper / E2E: visual formatting |
| Dates display in readable format | ✅ | | ✅ | Unit: formatting helper / E2E: visual formatting |
| Empty state shows when no expenses exist | | | ✅ | Visual state |

**Unit Tests**: 3 items (sort logic, formatting helpers)
**Feature Tests**: 3 items (route, query, pagination)
**E2E Tests**: 6 items (all visual elements)

#### Update Expense

| Criterion | Unit | Feature | E2E | Rationale |
|-----------|------|---------|-----|-----------|
| Edit button visible for each expense row | | | ✅ | Visual element |
| Edit form loads at `/expenses/{id}/edit` | | ✅ | ✅ | Feature: route response / E2E: rendering |
| Form pre-populates with existing expense data | | ✅ | ✅ | Feature: data retrieval / E2E: display verification |
| Submit updates expense in database | | ✅ | ✅ | Feature: database operation / E2E: workflow completion |
| Redirects to index with success message | | ✅ | ✅ | Feature: HTTP redirect / E2E: message display |
| Cancel button returns to index without saving | | | ✅ | UI interaction |

**Unit Tests**: 0 items
**Feature Tests**: 4 items (route, database, redirects)
**E2E Tests**: 6 items (complete workflow)

#### Delete Expense

| Criterion | Unit | Feature | E2E | Rationale |
|-----------|------|---------|-----|-----------|
| Delete button visible for each expense row | | | ✅ | Visual element |
| Confirmation dialog appears before deletion | | | ✅ | Browser dialog |
| Expense is soft-deleted (not permanently removed) | ✅ | ✅ | | Unit: model method / Feature: database verification |
| Deleted expense no longer appears in index | | ✅ | ✅ | Feature: query scope / E2E: display verification |
| Redirects to index with success message | | ✅ | ✅ | Feature: HTTP redirect / E2E: message display |

**Unit Tests**: 1 item (soft delete method)
**Feature Tests**: 3 items (database, query, redirect)
**E2E Tests**: 4 items (UI workflow)

**F1 Summary:**
- **Unit Tests**: 8 items (validation rules, formatting, model methods)
- **Feature Tests**: 16 items (routes, database operations, HTTP responses)
- **E2E Tests**: 27 items (complete CRUD workflows)

---

### F2: Daily Expenses View

| Criterion | Unit | Feature | E2E | Rationale |
|-----------|------|---------|-----|-----------|
| Page loads at `/expenses/daily` | | ✅ | | Route response |
| Date selector shows current date by default | | | ✅ | UI default state |
| Previous/Next day navigation works | | | ✅ | UI navigation |
| "Today" button returns to current date | | | ✅ | Button interaction |
| Expenses grouped by selected date | ✅ | ✅ | ✅ | Unit: grouping logic / Feature: query / E2E: display |
| Daily total is calculated correctly | ✅ | ✅ | ✅ | Unit: calculation method / Feature: aggregation / E2E: display |
| Category filter works on daily view | | ✅ | ✅ | Feature: filtering logic / E2E: UI interaction |
| Empty state shows when no expenses for date | | | ✅ | Visual state |
| Amounts display as currency format | ✅ | | ✅ | Unit: formatting helper / E2E: visual formatting |

**F2 Summary:**
- **Unit Tests**: 3 items (grouping logic, calculations, formatting)
- **Feature Tests**: 4 items (route, query, aggregation, filtering)
- **E2E Tests**: 8 items (UI navigation, display, interactions)

---

### F3: Monthly Expenses View

| Criterion | Unit | Feature | E2E | Rationale |
|-----------|------|---------|-----|-----------|
| Page loads at `/expenses/monthly` | | ✅ | | Route response |
| Month selector shows current month by default | | | ✅ | UI default state |
| Previous/Next month navigation works | | | ✅ | UI navigation |
| "This Month" button returns to current month | | | ✅ | Button interaction |
| Monthly total is calculated correctly | ✅ | ✅ | ✅ | Unit: calculation method / Feature: aggregation / E2E: display |
| Category breakdown shows all categories | | ✅ | ✅ | Feature: data grouping / E2E: display |
| Percentages calculated correctly (sum = 100%) | ✅ | ✅ | ✅ | Unit: percentage logic / Feature: calculation / E2E: display |
| Categories with $0 show 0% | ✅ | ✅ | ✅ | Unit: edge case logic / Feature: calculation / E2E: display |
| Empty state shows when no expenses for month | | | ✅ | Visual state |
| Amounts display as currency format | ✅ | | ✅ | Unit: formatting helper / E2E: visual formatting |

**F3 Summary:**
- **Unit Tests**: 4 items (calculations, percentage logic, formatting)
- **Feature Tests**: 5 items (route, aggregation, grouping, calculations)
- **E2E Tests**: 8 items (UI navigation, display, interactions)

---

### F4: Category Filtering

| Criterion | Unit | Feature | E2E | Rationale |
|-----------|------|---------|-----|-----------|
| Category filter dropdown on index page | | | ✅ | UI element |
| Category filter dropdown on daily view | | | ✅ | UI element |
| Category filter dropdown on monthly view | | | ✅ | UI element |
| Filter shows only matching expenses | | ✅ | ✅ | Feature: query logic / E2E: display verification |
| Totals update to reflect filtered results | ✅ | ✅ | ✅ | Unit: calculation logic / Feature: aggregation / E2E: display |
| "All Categories" option clears filter | | ✅ | ✅ | Feature: query reset / E2E: UI interaction |
| Filter persists through pagination | | ✅ | ✅ | Feature: state management / E2E: UI verification |
| Filter state preserved in URL | | ✅ | ✅ | Feature: URL params / E2E: browser verification |

**F4 Summary:**
- **Unit Tests**: 1 item (calculation logic)
- **Feature Tests**: 6 items (query logic, state management, URL params)
- **E2E Tests**: 8 items (UI dropdowns, interactions, verification)

---

## ✅ Data Validation

### Description Field

| Criterion | Unit | Feature | E2E | Rationale |
|-----------|------|---------|-----|-----------|
| Required - shows error when empty | ✅ | ✅ | ✅ | Unit: validation rule / Feature: request validation / E2E: error display |
| Max 255 characters - shows error when exceeded | ✅ | ✅ | ✅ | Unit: validation rule / Feature: request validation / E2E: error display |
| Accepts special characters and Unicode | ✅ | ✅ | ✅ | Unit: validation rule / Feature: database storage / E2E: display verification |

**Summary**: 3 Unit / 3 Feature / 3 E2E

### Amount Field

| Criterion | Unit | Feature | E2E | Rationale |
|-----------|------|---------|-----|-----------|
| Required - shows error when empty | ✅ | ✅ | ✅ | Unit: validation rule / Feature: request validation / E2E: error display |
| Minimum $0.01 - shows error below | ✅ | ✅ | ✅ | Unit: validation rule / Feature: request validation / E2E: error display |
| Maximum $999,999.99 - shows error above | ✅ | ✅ | ✅ | Unit: validation rule / Feature: request validation / E2E: error display |
| Accepts decimal values (2 places) | ✅ | ✅ | ✅ | Unit: validation rule / Feature: database precision / E2E: input verification |
| Rejects non-numeric input | ✅ | ✅ | ✅ | Unit: validation rule / Feature: request validation / E2E: error display |

**Summary**: 5 Unit / 5 Feature / 5 E2E

### Category Field

| Criterion | Unit | Feature | E2E | Rationale |
|-----------|------|---------|-----|-----------|
| Required - shows error when not selected | ✅ | ✅ | ✅ | Unit: validation rule / Feature: request validation / E2E: error display |
| Only accepts valid category values | ✅ | ✅ | ✅ | Unit: validation rule / Feature: enum validation / E2E: dropdown constraint |
| Shows error for invalid category | ✅ | ✅ | ✅ | Unit: validation rule / Feature: request validation / E2E: error display |

**Summary**: 3 Unit / 3 Feature / 3 E2E

### Date Field

| Criterion | Unit | Feature | E2E | Rationale |
|-----------|------|---------|-----|-----------|
| Required - shows error when empty | ✅ | ✅ | ✅ | Unit: validation rule / Feature: request validation / E2E: error display |
| Cannot be future date - shows error | ✅ | ✅ | ✅ | Unit: validation rule / Feature: request validation / E2E: error display |
| Cannot be older than 5 years - shows error | ✅ | ✅ | ✅ | Unit: validation rule / Feature: request validation / E2E: error display |
| Accepts valid date format | ✅ | ✅ | ✅ | Unit: validation rule / Feature: database casting / E2E: input verification |

**Summary**: 4 Unit / 4 Feature / 4 E2E

### Validation Errors

| Criterion | Unit | Feature | E2E | Rationale |
|-----------|------|---------|-----|-----------|
| Inline errors display next to fields | | | ✅ | Visual error placement |
| Flash message appears at top of form | | ✅ | ✅ | Feature: session flash / E2E: message display |
| Form repopulates with previous input | | ✅ | ✅ | Feature: old input / E2E: display verification |
| All validation rules work server-side | ✅ | ✅ | | Unit: rule definition / Feature: enforcement |

**Summary**: 1 Unit / 3 Feature / 3 E2E

**Validation Total:**
- **Unit Tests**: 19 items (all validation rules)
- **Feature Tests**: 21 items (request validation, enforcement)
- **E2E Tests**: 21 items (error display, UI feedback)

---

## ✅ User Interface

### Layout & Navigation

| Criterion | Unit | Feature | E2E | Rationale |
|-----------|------|---------|-----|-----------|
| Header displays app title "Expense Tracker" | | | ✅ | Visual element |
| Navigation links: All Expenses, Daily, Monthly | | | ✅ | Visual navigation |
| Current page highlighted in navigation | | | ✅ | Visual state |
| Footer (if applicable) displays correctly | | | ✅ | Visual element |
| Flash messages appear and auto-hide | | | ✅ | Visual behavior |

**Summary**: 0 Unit / 0 Feature / 5 E2E

### Material Design Compliance

| Criterion | Unit | Feature | E2E | Rationale |
|-----------|------|---------|-----|-----------|
| 8px grid system spacing used | | | ✅ | Visual/CSS measurement |
| Cards have elevation shadows | | | ✅ | Visual styling |
| Primary color consistent throughout | | | ✅ | Visual styling |
| Typography follows scale guidelines | | | ✅ | Visual styling |
| Buttons have proper hover/active states | | | ✅ | Visual interaction |

**Summary**: 0 Unit / 0 Feature / 5 E2E

### Responsive Design

| Criterion | Unit | Feature | E2E | Rationale |
|-----------|------|---------|-----|-----------|
| Desktop (1440px+) - full layout | | | ✅ | Visual responsive |
| Laptop (1024px) - adjusted layout | | | ✅ | Visual responsive |
| Tablet (768px) - responsive layout | | | ✅ | Visual responsive |
| Mobile (320px) - mobile-friendly layout | | | ✅ | Visual responsive |
| Touch targets minimum 44x44px on mobile | | | ✅ | Visual measurement |
| Tables scroll horizontally on small screens | | | ✅ | Visual behavior |

**Summary**: 0 Unit / 0 Feature / 6 E2E

### Empty States

| Criterion | Unit | Feature | E2E | Rationale |
|-----------|------|---------|-----|-----------|
| All empty state messages | | | ✅ | Visual messages |

**Summary**: 0 Unit / 0 Feature / 4 E2E

**UI Total:**
- **Unit Tests**: 0 items
- **Feature Tests**: 0 items
- **E2E Tests**: 20 items (all UI is visual)

---

## ✅ Accessibility

### All Accessibility Items

| Category | Unit | Feature | E2E | Rationale |
|----------|------|---------|-----|-----------|
| Keyboard Navigation | | | ✅ | Browser interaction |
| Screen Reader Support | | | ✅ | ARIA, semantic HTML |
| Visual Accessibility | | | ✅ | Contrast, sizing |

**Summary**: 0 Unit / 0 Feature / 12 E2E (all accessibility is browser-based)

---

## ✅ Performance

### Page Load & Database

| Criterion | Unit | Feature | E2E | Rationale |
|-----------|------|---------|-----|-----------|
| Initial page load < 2 seconds | | | ✅ | Browser measurement |
| Time to Interactive < 3 seconds | | | ✅ | Browser measurement |
| No layout shift after load | | | ✅ | Browser measurement |
| Queries per page < 10 | | ✅ | | Database profiling |
| Index on `date` column exists | | ✅ | | Migration verification |
| Index on `category` column exists | | ✅ | | Migration verification |
| Composite index on `date, category` exists | | ✅ | | Migration verification |
| Pagination prevents loading all records | | ✅ | | Query verification |

**Summary**: 0 Unit / 5 Feature / 3 E2E

### Assets

| Criterion | Unit | Feature | E2E | Rationale |
|-----------|------|---------|-----|-----------|
| CSS loads without render blocking | | | ✅ | Browser behavior |
| JavaScript deferred or at end of body | | | ✅ | Browser behavior |
| No unused CSS/JS loaded | | | ✅ | Browser analysis |
| Images optimized (if any) | | | ✅ | Browser analysis |

**Summary**: 0 Unit / 0 Feature / 4 E2E

**Performance Total:**
- **Unit Tests**: 0 items
- **Feature Tests**: 5 items (database queries, indexes)
- **E2E Tests**: 7 items (page load metrics, asset loading)

---

## ✅ Testing Section

### Unit Tests (Expense Model)

**All items = Unit Tests** (testing model methods)
- 7 items total

### Feature Tests (ExpenseController)

**All items = Feature Tests** (testing controller actions)
- 10 items total

### Test Execution

**All items = Both Unit & Feature** (test suite execution)
- 3 items total

**Testing Total:**
- **Unit Tests**: 10 items (7 model tests + 3 execution)
- **Feature Tests**: 13 items (10 controller tests + 3 execution)
- **E2E Tests**: 0 items

---

## ✅ CI/CD Pipeline

| Category | Unit | Feature | E2E | Rationale |
|----------|------|---------|-----|-----------|
| All CI/CD workflow items | | ✅ | ✅ | Both test types run in CI |
| Branch protection items | | ✅ | ✅ | Both test types block merge |

**Summary**: 0 Unit / 8 Feature / 8 E2E (both test suites run in CI)

---

## ✅ Code Quality

### Laravel Best Practices

**All items verified through Feature Tests** (code structure validation)
- 6 items total

### File Structure

**All items verified through Feature Tests** (file existence)
- 8 items total

### Frontend

| Criterion | Unit | Feature | E2E | Rationale |
|-----------|------|---------|-----|-----------|
| No frameworks required | | ✅ | | File verification |
| CSS/JS organization | | | ✅ | Browser verification |

**Summary**: 0 Unit / 1 Feature / 1 E2E

**Code Quality Total:**
- **Unit Tests**: 0 items
- **Feature Tests**: 15 items (best practices, file structure)
- **E2E Tests**: 1 item (frontend verification)

---

## ✅ Database

### Schema & Data Integrity

**All items verified through Feature Tests** (database structure and operations)
- 14 items total

**Database Total:**
- **Unit Tests**: 0 items
- **Feature Tests**: 14 items
- **E2E Tests**: 0 items

---

## ✅ Error Handling

| Criterion | Unit | Feature | E2E | Rationale |
|-----------|------|---------|-----|-----------|
| 404 page displays for invalid expense ID | | ✅ | ✅ | Feature: response / E2E: display |
| 404 page links back to index | | | ✅ | Visual link |
| 500 errors show generic message | | ✅ | ✅ | Feature: response / E2E: display |
| Validation errors return 422 status | | ✅ | | HTTP response code |
| Edge cases handled gracefully | ✅ | ✅ | ✅ | Unit: logic / Feature: handling / E2E: display |

**Error Handling Total:**
- **Unit Tests**: 1 item (edge case logic)
- **Feature Tests**: 4 items (HTTP responses, error handling)
- **E2E Tests**: 3 items (error display, edge cases)

---

## 📊 Final Summary by Test Type

### Unit Tests (40 tests recommended)

| Category | Count | What to Test |
|----------|-------|--------------|
| **Model Validation** | 19 | All validation rules (description, amount, category, date) |
| **Model Methods** | 8 | Soft delete, scopes, accessors/mutators |
| **Calculations** | 8 | Daily totals, monthly totals, percentages, grouping logic |
| **Formatting Helpers** | 3 | Currency formatting, date formatting, sorting |
| **Edge Cases** | 2 | Edge case logic, boundary conditions |

**Total Unit Tests**: ~40 tests

**Key Unit Test Files:**
- `tests/Unit/Models/ExpenseTest.php` (model validation, methods)
- `tests/Unit/Helpers/CurrencyHelperTest.php` (if helper exists)
- `tests/Unit/Helpers/DateHelperTest.php` (if helper exists)

---

### Feature Tests (70 tests recommended)

| Category | Count | What to Test |
|----------|-------|--------------|
| **CRUD Operations** | 16 | Create, read, update, delete via HTTP |
| **Daily/Monthly Views** | 9 | Route responses, queries, aggregations |
| **Category Filtering** | 6 | Query logic, state management, URL params |
| **Validation Enforcement** | 21 | Server-side validation, request validation |
| **Database Operations** | 14 | Schema, indexes, soft deletes, seeding |
| **Code Quality** | 15 | File structure, best practices |
| **Performance** | 5 | Query optimization, pagination |
| **Error Handling** | 4 | HTTP errors, edge cases |

**Total Feature Tests**: ~70 tests (plus 10 already in Testing section)

**Key Feature Test Files:**
- `tests/Feature/ExpenseControllerTest.php` (CRUD, views, filtering)
- `tests/Feature/ValidationTest.php` (all validation rules)
- `tests/Feature/DatabaseTest.php` (schema, indexes, data integrity)

---

### E2E Tests (80 tests - already created!)

| Category | Count | What to Test |
|----------|-------|--------------|
| **CRUD Workflows** | 27 | Complete user workflows for create/read/update/delete |
| **Daily/Monthly Views** | 16 | UI navigation, display, interactions |
| **Category Filtering** | 8 | UI dropdowns, interactions, verification |
| **Validation Feedback** | 21 | Error display, UI feedback |
| **User Interface** | 20 | Layout, navigation, Material Design, responsive |
| **Accessibility** | 12 | Keyboard, screen reader, visual accessibility |
| **Performance** | 7 | Page load, asset loading, browser metrics |
| **Error Display** | 3 | Error pages, messages |

**Total E2E Tests**: ~80 tests ✅ **COMPLETE**

**E2E Test Files** (already created):
- `tests/e2e/crud.spec.ts` (25 tests)
- `tests/e2e/daily-view.spec.ts` (12 tests)
- `tests/e2e/monthly-view.spec.ts` (13 tests)
- `tests/e2e/filtering.spec.ts` (15 tests)
- `tests/e2e/validation.spec.ts` (25 tests)
- `tests/e2e/ui-accessibility.spec.ts` (30+ tests)

---

## 📈 Test Distribution Summary

| Test Type | Recommended | Status | Coverage |
|-----------|-------------|--------|----------|
| **Unit Tests** | ~40 tests | 🟡 Partial | Model tests exist, need helpers/calculations |
| **Feature Tests** | ~70 tests | 🟡 Partial | ExpenseControllerTest exists, need validation/database tests |
| **E2E Tests** | ~80 tests | ✅ Complete | Full suite created with Playwright |
| **Total** | ~190 tests | 🟡 In Progress | ~50% complete |

---

## ✅ Next Steps

### 1. Expand Unit Tests

Create/expand these test files:

**`tests/Unit/Models/ExpenseTest.php`**
```php
// Test all validation rules
test_description_required()
test_description_max_255()
test_description_accepts_unicode()
test_amount_required()
test_amount_min_0_01()
test_amount_max_999999_99()
test_amount_accepts_decimals()
test_amount_rejects_non_numeric()
test_category_required()
test_category_validates_enum()
test_date_required()
test_date_cannot_be_future()
test_date_cannot_be_older_than_5_years()

// Test model methods
test_soft_delete_works()
test_expenses_sorted_by_date_desc()
test_daily_total_calculation()
test_monthly_total_calculation()
test_percentage_calculation()
test_category_breakdown()
```

**`tests/Unit/Helpers/FormatHelperTest.php`** (if helpers exist)
```php
test_format_currency()
test_format_date()
test_format_percentage()
```

### 2. Expand Feature Tests

Create/expand these test files:

**`tests/Feature/ExpenseControllerTest.php`** (expand existing)
```php
// CRUD operations (expand existing tests)
// Add more edge cases and validation scenarios
```

**`tests/Feature/ValidationTest.php`** (new file)
```php
// Test all validation rules via HTTP
test_description_validation_via_http()
test_amount_validation_via_http()
test_category_validation_via_http()
test_date_validation_via_http()
test_validation_errors_return_422()
test_validation_repopulates_form()
```

**`tests/Feature/DatabaseTest.php`** (new file)
```php
test_expenses_table_has_correct_columns()
test_date_index_exists()
test_category_index_exists()
test_composite_index_exists()
test_soft_delete_column_exists()
test_seeder_creates_50_expenses()
test_pagination_limits_to_15_per_page()
```

**`tests/Feature/PerformanceTest.php`** (new file)
```php
test_index_page_queries_less_than_10()
test_daily_view_queries_less_than_10()
test_monthly_view_queries_less_than_10()
test_pagination_prevents_loading_all_records()
```

### 3. Run Complete Test Suite

```bash
cd laravel-app

# Run all Unit tests
php artisan test --testsuite=Unit

# Run all Feature tests
php artisan test --testsuite=Feature

# Run all E2E tests
npm run test:e2e

# Run everything
php artisan test && npm run test:e2e
```

### 4. Update CI/CD

Ensure `.github/workflows/laravel.yml` runs all test types:

```yaml
- name: Run Unit Tests
  run: php artisan test --testsuite=Unit

- name: Run Feature Tests
  run: php artisan test --testsuite=Feature
```

And `.github/workflows/e2e-tests.yml` runs E2E tests (already created).

---

## 🎯 Key Principles for Test Categorization

### When to Write Unit Tests
✅ Testing a single method or function
✅ No database or HTTP required
✅ Pure logic (calculations, validation rules, formatting)
✅ Very fast execution (milliseconds)

### When to Write Feature Tests
✅ Testing HTTP requests and responses
✅ Database operations (CRUD, queries, migrations)
✅ Server-side validation enforcement
✅ Controller actions and routes
✅ Integration between components

### When to Write E2E Tests
✅ Testing complete user workflows
✅ Visual elements and UI interactions
✅ Browser behavior (navigation, dialogs, forms)
✅ Accessibility and responsive design
✅ Cross-browser compatibility

### When to Write Multiple Test Types

Some criteria need **all three** test types:

**Example: "Amount field accepts decimals"**
- **Unit Test**: Verify validation rule allows decimals
- **Feature Test**: Verify HTTP request accepts decimal values
- **E2E Test**: Verify UI displays decimal input correctly

**Example: "Daily total is calculated correctly"**
- **Unit Test**: Verify calculation method produces correct sum
- **Feature Test**: Verify database aggregation returns correct total
- **E2E Test**: Verify total displays correctly in browser

---

## 📋 Acceptance Checklist Coverage

**Total Acceptance Criteria**: 180+ items

**Test Coverage:**
- ✅ **Unit Tests**: Cover 40 acceptance criteria (~22%)
- ✅ **Feature Tests**: Cover 90 acceptance criteria (~50%)
- ✅ **E2E Tests**: Cover 116 acceptance criteria (~64%)

**Note**: Many criteria require multiple test types, so percentages sum to >100%.

**Overlap Items**: ~35 criteria require all three test types for complete coverage.

---

**Status**: E2E tests complete ✅ | Unit tests partial 🟡 | Feature tests partial 🟡
