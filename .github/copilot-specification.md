# Expense Tracker - Technical Specification

## Project Overview

**Application Name:** Expense Tracker  
**Goal:** Record and view daily financial transactions  
**Framework:** Laravel (latest stable)  
**Database:** SQLite  
**Currency:** USD ($)  
**Timezone:** UTC  
**Locale:** en_US  
**Authentication:** None (single-user application)

---

## Features

### F1: Expense CRUD Interface
Graphical user interface for creating, reading, updating, and deleting expenses following Material Design guidelines.

### F2: Daily Expenses View
Display a table showing expenses grouped by day with daily totals.

### F3: Monthly Expenses View
Display a table showing expenses aggregated by month with category breakdowns.

### F4: Category Filtering
Filter expenses by category across all views.

---

## Data Model

### Expense Entity

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto-increment | Unique identifier |
| description | string(255) | required | Short description (e.g., "Lunch") |
| amount | decimal(10,2) | required, min: 0.01, max: 999999.99 | Expense amount |
| category | enum | required | One of 7 predefined categories |
| date | date | required, max: today | When expense occurred |
| created_at | timestamp | auto | Record creation time |
| updated_at | timestamp | auto | Record update time |
| deleted_at | timestamp | nullable | Soft delete timestamp |

### Categories

**Storage Format:** Categories are stored as display names (Title Case) in the database.

| Stored Value | Display Name |
|--------------|--------------|
| Groceries | Groceries |
| Transport | Transport |
| Housing and Utilities | Housing and Utilities |
| Restaurants and Cafes | Restaurants and Cafes |
| Health and Medicine | Health and Medicine |
| Clothing & Footwear | Clothing & Footwear |
| Entertainment | Entertainment |

---

## API Endpoints

### Expense Resource Routes

| Method | URI | Action | Description |
|--------|-----|--------|-------------|
| GET | /expenses | index | List all expenses (paginated) |
| GET | /expenses/create | create | Show create form |
| POST | /expenses | store | Create new expense |
| GET | /expenses/{id}/edit | edit | Show edit form |
| PUT/PATCH | /expenses/{id} | update | Update expense |
| DELETE | /expenses/{id} | destroy | Soft delete expense |

### View Routes

**⚠️ Route Order:** These routes MUST be defined BEFORE the resource routes to prevent `/expenses/{id}` from capturing them.

| Method | URI | Action | Description |
|--------|-----|--------|-------------|
| GET | /expenses/daily | daily | Daily expenses view |
| GET | /expenses/monthly | monthly | Monthly expenses view |

### Query Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| category | string | Filter by category | ?category=groceries |
| date | date | Filter daily view by date | ?date=2025-12-01 |
| month | string | Filter monthly view by month | ?month=2025-12 |
| page | int | Pagination page number | ?page=2 |

---

## User Interface

### Layout Structure

```
┌─────────────────────────────────────────────────────────┐
│ Header: App Title + Navigation                          │
├─────────────────────────────────────────────────────────┤
│ ┌─────────────────────────────────────────────────────┐ │
│ │ Flash Messages (Success/Error)                      │ │
│ └─────────────────────────────────────────────────────┘ │
│ ┌─────────────────────────────────────────────────────┐ │
│ │                                                     │ │
│ │                  Main Content                       │ │
│ │                                                     │ │
│ └─────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘
```

### Views

#### Index View (Expense List)
- Table with columns: Date, Description, Category, Amount, Actions
- Pagination (15 items per page)
- "Add Expense" button
- Category filter dropdown
- Empty state when no expenses

#### Create/Edit Form
- Description: Text input (max 255 characters)
- Amount: Number input (step 0.01)
- Category: Dropdown select
- Date: Date picker (max today)
- Submit and Cancel buttons
- Inline validation errors

#### Daily View
- Date selector with navigation (prev/next/today)
- Expenses grouped under date headers
- Daily totals displayed
- Category filter

#### Monthly View
- Month selector with navigation (prev/next/current)
- Monthly total
- Category breakdown with percentages
- Daily breakdown within month

### Material Design Requirements

- 8px grid system for spacing
- Elevation shadows for cards and dialogs
- Primary color scheme (consistent throughout)
- Typography scale following Material guidelines
- Touch targets minimum 44x44px on mobile
- Responsive breakpoints: 320px, 768px, 1024px, 1440px

---

## Validation Rules

### Expense Validation

```php
[
    'description' => 'required|string|max:255',
    'amount' => 'required|numeric|min:0.01|max:999999.99',
    'category' => 'required|in:Groceries,Transport,Housing and Utilities,Restaurants and Cafes,Health and Medicine,Clothing & Footwear,Entertainment',
    'date' => 'required|date|before_or_equal:today|after_or_equal:' . now()->subYears(5)->format('Y-m-d')
]
```

**Date Constraints:**
- Maximum: Today (no future dates)
- Minimum: 5 years in the past

### Error Messages

| Field | Rule | Message |
|-------|------|---------|
| description | required | The description field is required. |
| description | max | The description may not be greater than 255 characters. |
| amount | required | The amount field is required. |
| amount | min | The amount must be at least 0.01. |
| amount | max | The amount may not be greater than 999,999.99. |
| category | required | Please select a category. |
| category | in | The selected category is invalid. |
| date | required | The date field is required. |
| date | before_or_equal | The date cannot be in the future. |

---

## Technical Architecture

### Directory Structure

```
laravel-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── ExpenseController.php
│   │   └── Requests/
│   │       ├── StoreExpenseRequest.php
│   │       └── UpdateExpenseRequest.php
│   └── Models/
│       └── Expense.php
├── database/
│   ├── factories/
│   │   └── ExpenseFactory.php
│   ├── migrations/
│   │   └── xxxx_xx_xx_create_expenses_table.php
│   └── seeders/
│       └── ExpenseSeeder.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       └── expenses/
│           ├── index.blade.php
│           ├── create.blade.php
│           ├── edit.blade.php
│           ├── daily.blade.php
│           ├── monthly.blade.php
│           └── _form.blade.php
├── routes/
│   └── web.php
└── tests/
    ├── Unit/
    │   └── Models/
    │       └── ExpenseTest.php
    └── Feature/
        └── ExpenseControllerTest.php
```

### Database Indexes

| Index | Columns | Purpose |
|-------|---------|---------|
| expenses_date_index | date | Daily view queries |
| expenses_category_index | category | Category filtering |
| expenses_date_category_index | date, category | Combined filters |

---

## Testing Requirements

### Unit Tests (Expense Model)

| Test Case | Description |
|-----------|-------------|
| test_can_create_expense | Verify expense creation with valid data |
| test_description_required | Validate description is required |
| test_amount_boundaries | Validate amount min/max constraints |
| test_category_validation | Validate only allowed categories |
| test_date_not_future | Validate date cannot be future |
| test_soft_delete | Verify soft delete functionality |

### Feature Tests (ExpenseController)

| Test Case | Description |
|-----------|-------------|
| test_index_displays_expenses | Verify list view loads |
| test_create_form_displays | Verify create form renders |
| test_store_creates_expense | Verify expense creation |
| test_store_validation_fails | Verify validation errors |
| test_edit_displays_expense | Verify edit form loads |
| test_update_modifies_expense | Verify expense update |
| test_destroy_soft_deletes | Verify soft delete |
| test_daily_view_groups_by_date | Verify daily grouping |
| test_monthly_view_aggregates | Verify monthly totals |
| test_category_filter_works | Verify filtering |

### Coverage Requirements

- Unit tests: 100% for Expense model
- Feature tests: >= 80% for controller
- Critical paths: 100% coverage

---

## CI/CD Pipeline

### GitHub Actions Workflow

**Triggers:**
- Push to `main` branch
- Pull requests to `main` branch

**Jobs:**

1. **Setup**
   - Checkout code
   - Setup PHP 8.2
   - Install Composer dependencies (cached)

2. **Test**
   - Configure SQLite test database
   - Run PHPUnit tests
   - Generate coverage report

3. **Lint** (optional)
   - Run Laravel Pint for code style

**Branch Protection:**
- Require status checks to pass
- Block merge on test failure

---

## Performance Requirements

| Metric | Target |
|--------|--------|
| Page load time | < 2 seconds |
| API response time | < 200ms |
| Time to Interactive | < 3 seconds |
| Database queries per page | < 10 |

### Optimization Strategies

- Eager loading for relationships
- Database indexes on filtered columns
- Pagination for large datasets
- Asset minification in production
- Query result caching where appropriate

---

## Accessibility Requirements

| Requirement | Standard |
|-------------|----------|
| Compliance level | WCAG 2.1 AA |
| Keyboard navigation | Full support |
| Screen reader | ARIA labels |
| Color contrast | 4.5:1 minimum |
| Focus indicators | Visible |

---

## Behavioral Specifications

### Currency & Formatting

| Format | Specification | Example |
|--------|---------------|--------|
| Currency symbol | USD ($) prefix | $125.50 |
| Decimal separator | Period (.) | 1234.56 |
| Thousands separator | Comma (,) | $1,234.56 |
| Decimal places | Always 2 | $5.00 |
| Date display | MMMM D, YYYY | December 2, 2025 |
| Date input | YYYY-MM-DD | 2025-12-02 |

### User Interactions

| Action | Behavior |
|--------|----------|
| Delete expense | JavaScript `confirm()` dialog before deletion |
| Form validation | Client-side HTML5 validation + server-side Laravel validation |
| Edit historical | Users CAN edit expenses from any date within the 5-year limit |
| Duplicate expenses | Allowed - identical expenses can be created |
| Restore deleted | NOT available - soft deletes are permanent from user perspective |

### Empty States

| View | Empty State Message |
|------|--------------------|
| Index (no expenses) | "No expenses recorded yet. Click 'Add Expense' to get started." |
| Index (filtered, no results) | "No expenses found for the selected category." |
| Daily (no expenses) | "No expenses recorded for this date." |
| Monthly (no expenses) | "No expenses recorded for this month." |
| Category with 0 expenses | Show row with $0.00 and 0% |

### Pagination

| View | Items per Page |
|------|---------------|
| Index | 15 |
| Daily | No pagination (show all for selected date) |
| Monthly | No pagination (show summary only) |

### Sorting

| View | Default Sort | User Configurable |
|------|--------------|------------------|
| Index | Date DESC (newest first) | No |
| Daily | Time of creation ASC | No |
| Monthly | Category alphabetically | No |

### Error Pages

| Error | Page |
|-------|------|
| 404 Not Found | Custom page with link to expenses index |
| 500 Server Error | Generic error page (no stack traces) |
| Validation Error | Inline field errors + flash message |

---

## Browser Support

| Browser | Minimum Version |
|---------|----------------|
| Chrome | 90+ |
| Firefox | 88+ |
| Safari | 14+ |
| Edge | 90+ |
| Mobile Safari | iOS 14+ |
| Chrome Android | 90+ |

---

## Implementation Tracking

All implementation tasks are tracked via GitHub Issues:

| Issue | Title | Phase |
|-------|-------|-------|
| #21 | Project Setup: Initialize Laravel Application | Phase 1 |
| #22 | Database: Create Expenses Table Migration and Model | Phase 1 |
| #32 | Database: Create Expense Seeder | Phase 1 |
| #23 | Backend: Implement ExpenseController | Phase 2 |
| #24 | Frontend: Create Material Design UI Layout | Phase 2 |
| #25 | Frontend: Implement Expense CRUD Views | Phase 2 |
| #26 | Feature: Implement Daily Expenses View | Phase 3 |
| #27 | Feature: Implement Monthly Expenses View | Phase 3 |
| #28 | Feature: Implement Category Filtering | Phase 3 |
| #29 | Testing: Unit Tests for Expense Model | Phase 4 |
| #30 | Testing: Feature Tests for ExpenseController | Phase 4 |
| #31 | CI/CD: Set Up GitHub Actions Workflow | Phase 4 |
| #33 | [Epic] Expense Tracker Implementation | Tracking |

---

## Frontend Technology

**Approach:** Vanilla HTML, CSS, and JavaScript (no frameworks)

| Component | Technology | Rationale |
|-----------|------------|-----------|
| Templating | Laravel Blade | Built-in, no extra dependencies |
| Styling | Vanilla CSS | Lightweight, no build step |
| JavaScript | Vanilla JS (ES6+) | No jQuery, no frameworks |
| Icons | Inline SVG | No icon library dependencies |
| Build tools | None required | Direct file serving |

**NOT Used:**
- ❌ Tailwind CSS
- ❌ Bootstrap
- ❌ jQuery
- ❌ Vue.js / React
- ❌ Vite asset bundling (for CSS/JS)

---

## References

- [Project Requirements](/requirement.txt)
- [Project Constitution](/.github/copilot-constitution.md)
- [Implementation Plan](/.github/copilot-plan.md)
- [Task Checklist](/.github/copilot-tasks.md)
- [Laravel Documentation](https://laravel.com/docs)
- [Material Design Guidelines](https://material.io/design)
