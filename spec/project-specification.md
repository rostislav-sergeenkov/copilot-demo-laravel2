# Expense Tracker - Project Specification

## 1. Project Overview

### 1.1 Goal
Build a Laravel-based web application to record and view daily financial transactions.

### 1.2 Project Management
- Issue tracking: GitHub Issues
- Version control: GitHub.com
- CI/CD: GitHub Actions

## 2. Functional Requirements

### 2.1 Core Features

#### F1: CRUD Operations for Expenses
- **Create**: Add new expense entries with validation
- **Read**: View expense details
- **Update**: Edit existing expense entries
- **Delete**: Remove expense entries (soft delete recommended)
- **UI**: Graphical interface with forms and tables

**Acceptance Criteria:**
- User can create an expense with all required fields
- User can edit any existing expense
- User can delete an expense with confirmation
- All operations provide user feedback (success/error messages)

#### F2: Daily Expenses View
- Display expenses grouped by date (today by default)
- Show total amount for the day
- Sort by date (newest first)

**Acceptance Criteria:**
- Table displays all expenses for selected day
- Daily total is calculated and displayed
- User can navigate between dates

#### F3: Monthly Expenses View
- Display expenses for current month by default
- Show total amount for the month
- Group by date within the month
- Allow month navigation (previous/next)

**Acceptance Criteria:**
- Table displays all expenses for selected month
- Monthly total is calculated and displayed
- User can switch between months
- Data is properly aggregated by date

#### F4: Category Filter
- Filter expenses by one or more categories
- Maintain filter state during navigation
- Clear filter option

**Acceptance Criteria:**
- Dropdown/checkbox for category selection
- Table updates dynamically when filter applied
- Filtered totals recalculated correctly
- "Clear filter" button restores all expenses

### 2.2 Expense Categories
The following categories are supported:
- Groceries
- Transport
- Housing and Utilities
- Restaurants and Cafes
- Health and Medicine
- Clothing & Footwear
- Entertainment

## 3. Technical Requirements

### 3.1 Framework and Standards
- **Framework**: Laravel (latest stable version recommended)
- **Coding Standards**: PSR-12, Laravel best practices
- **Architecture**: MVC pattern
- **Code Quality**: Follow SOLID principles

### 3.2 Database Schema

#### Expenses Table
```sql
CREATE TABLE expenses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    category VARCHAR(50) NOT NULL,
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

**Field Specifications:**
- `id`: Auto-incrementing primary key
- `description`: Short text (max 255 chars), e.g., "Lunch", "Bus ticket"
- `amount`: Decimal with 2 decimal places, must be positive
- `category`: Enum from predefined list (see section 2.2)
- `date`: Date when expense occurred (not created_at)
- `created_at`, `updated_at`: Laravel timestamps
- `deleted_at`: For soft deletes (optional but recommended)

**Indexes:**
```sql
CREATE INDEX idx_date ON expenses(date);
CREATE INDEX idx_category ON expenses(category);
CREATE INDEX idx_date_category ON expenses(date, category);
```

### 3.3 Laravel Components Required

#### Models
- `App\Models\Expense`
  - Mass assignment protection
  - Date casting
  - Category validation
  - Soft deletes (optional)

#### Controllers
- `App\Http\Controllers\ExpenseController`
  - Resource controller with standard CRUD methods
  - Custom methods for daily/monthly views

#### Requests
- `App\Http\Requests\StoreExpenseRequest`
- `App\Http\Requests\UpdateExpenseRequest`
  - Validation rules for all fields

#### Routes
```php
Route::resource('expenses', ExpenseController::class);
Route::get('expenses/daily/{date?}', [ExpenseController::class, 'daily']);
Route::get('expenses/monthly/{month?}', [ExpenseController::class, 'monthly']);
```

#### Views (Blade Templates)
- `resources/views/expenses/index.blade.php` - Main listing
- `resources/views/expenses/create.blade.php` - Create form
- `resources/views/expenses/edit.blade.php` - Edit form
- `resources/views/expenses/daily.blade.php` - Daily view
- `resources/views/expenses/monthly.blade.php` - Monthly view

### 3.4 Validation Rules

```php
'description' => 'required|string|max:255',
'amount' => 'required|numeric|min:0.01|max:999999.99',
'category' => 'required|in:Groceries,Transport,Housing and Utilities,Restaurants and Cafes,Health and Medicine,Clothing & Footwear,Entertainment',
'date' => 'required|date|before_or_equal:today'
```

## 4. Testing Requirements

### 4.1 Backend Tests

#### Unit Tests
- Model validation tests
- Business logic tests
- Helper function tests

**Minimum Coverage:**
- `tests/Unit/Models/ExpenseTest.php`
- Test expense creation, validation, and calculations

#### Feature/Integration Tests
- HTTP request/response tests
- Database interaction tests
- CRUD operation tests

**Minimum Coverage:**
- `tests/Feature/ExpenseControllerTest.php`
  - Test all CRUD operations
  - Test daily/monthly views
  - Test category filtering
  - Test validation errors
  - Test edge cases (empty results, invalid dates)

### 4.2 Frontend Tests
- JavaScript validation tests (if applicable)
- Form submission tests
- UI interaction tests

### 4.3 Test Database
- Use in-memory SQLite for testing (`:memory:`)
- Configure in `phpunit.xml`

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

## 5. CI/CD Pipeline

### 5.1 GitHub Actions Workflow

**File**: `.github/workflows/laravel.yml`

**Pipeline Steps:**
1. Checkout code
2. Setup PHP environment (version as per composer.json)
3. Install Composer dependencies
4. Copy `.env.example` to `.env`
5. Generate application key
6. Run migrations
7. Run PHPUnit tests
8. Run code quality checks (optional: PHPStan, PHP CS Fixer)

**Trigger Events:**
- Pull requests to `main` branch
- Push to `main` branch

**Merge Protection:**
- All tests must pass
- No merge if pipeline fails

### 5.2 Quality Gates
- All tests pass (exit code 0)
- Code coverage minimum: 70% (recommended)
- No critical PHP errors

## 6. User Interface Guidelines

### 6.1 Layout
- Responsive design (mobile-friendly)
- Bootstrap or Tailwind CSS recommended
- Clear navigation between views

### 6.2 Forms
- Client-side validation (HTML5 + JavaScript)
- Server-side validation (Laravel)
- Error messages displayed inline
- Success messages after operations

### 6.3 Tables
- Sortable columns
- Pagination (20-50 items per page)
- Total amount displayed prominently
- Export functionality (optional, future enhancement)

## 7. Non-Functional Requirements

### 7.1 Performance
- Page load time < 2 seconds
- Database queries optimized (eager loading where needed)

### 7.2 Security
- CSRF protection (Laravel default)
- Input sanitization
- SQL injection prevention (Eloquent ORM)

### 7.3 Usability
- Intuitive navigation
- Consistent UI/UX
- Helpful error messages

## 8. Development Workflow

### 8.1 Git Workflow
1. Create feature branch from `main`
2. Implement feature with tests
3. Run tests locally
4. Create pull request
5. Wait for CI/CD pipeline
6. Code review
7. Merge after approval and passing tests

### 8.2 Branch Naming
- Feature: `feature/expense-crud`
- Bugfix: `bugfix/validation-error`
- Hotfix: `hotfix/critical-bug`

## 9. Acceptance Criteria Summary

The project is considered complete when:
- [ ] All CRUD operations work correctly
- [ ] Daily and monthly views display accurate data
- [ ] Category filtering functions properly
- [ ] All unit and integration tests pass
- [ ] GitHub Actions pipeline is configured and working
- [ ] Code follows Laravel best practices
- [ ] Database schema is implemented in SQLite
- [ ] Application is deployable and runs without errors

## 10. Future Enhancements (Out of Scope)

- User authentication and multi-user support
- Budget tracking and alerts
- Data visualization (charts and graphs)
- Export to CSV/PDF
- Recurring expenses
- Income tracking
- Mobile application
