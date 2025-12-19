# Acceptance Criteria to Test Mapping

This document maps each acceptance criterion from `.github/copilot-acceptance-checklist.md` to the corresponding Playwright E2E test.

## ✅ Feature Acceptance

### F1: Expense CRUD Interface → `crud.spec.ts`

#### Create Expense
| Acceptance Criterion | Test Name | Status |
|---------------------|-----------|--------|
| "Add Expense" button visible on index page | should show "Add Expense" button on index page | ✅ |
| Create form loads at `/expenses/create` | should load create form at /expenses/create | ✅ |
| Form contains: Description, Amount, Category, Date fields | should display all required form fields | ✅ |
| Description field accepts up to 255 characters | should accept description up to 255 characters | ✅ |
| Amount field accepts decimals (0.01 - 999999.99) | should accept decimal amounts | ✅ |
| Category dropdown shows all 7 categories | should show all 7 categories in dropdown | ✅ |
| Date picker restricts to today or earlier | See validation.spec.ts | ✅ |
| Date picker restricts to within 5 years | See validation.spec.ts | ✅ |
| Submit creates expense in database | should create expense and redirect to index | ✅ |
| Redirects to index with success message | should create expense and redirect to index | ✅ |
| Cancel button returns to index without saving | should return to index when cancel is clicked | ✅ |

#### Read Expenses (Index)
| Acceptance Criterion | Test Name | Status |
|---------------------|-----------|--------|
| Index page loads at `/expenses` | should load index page at /expenses | ✅ |
| Table displays columns: Date, Description, Category, Amount, Actions | should display table with required columns | ✅ |
| Expenses sorted by date (newest first) | should sort expenses by date (newest first) | ✅ |
| Pagination works (15 items per page) | should paginate expenses (15 per page) | ✅ |
| Amounts display as currency ($X.XX) | should display amounts as currency format | ✅ |
| Dates display in readable format | should display dates in readable format | ✅ |
| Empty state shows when no expenses exist | should show empty state when no expenses exist | ✅ |

#### Update Expense
| Acceptance Criterion | Test Name | Status |
|---------------------|-----------|--------|
| Edit button visible for each expense row | should show edit button for each expense | ✅ |
| Edit form loads at `/expenses/{id}/edit` | should load edit form at /expenses/{id}/edit | ✅ |
| Form pre-populates with existing expense data | should pre-populate form with existing data | ✅ |
| Submit updates expense in database | should update expense and redirect to index | ✅ |
| Redirects to index with success message | should update expense and redirect to index | ✅ |
| Cancel button returns to index without saving | should return to index when cancel is clicked | ✅ |

#### Delete Expense
| Acceptance Criterion | Test Name | Status |
|---------------------|-----------|--------|
| Delete button visible for each expense row | should show delete button for each expense | ✅ |
| Confirmation dialog appears before deletion | should show confirmation dialog before deletion | ✅ |
| Expense is soft-deleted (not permanently removed) | should soft-delete expense and remove from index | ✅ |
| Deleted expense no longer appears in index | should soft-delete expense and remove from index | ✅ |
| Redirects to index with success message | should soft-delete expense and remove from index | ✅ |

---

### F2: Daily Expenses View → `daily-view.spec.ts`

| Acceptance Criterion | Test Name | Status |
|---------------------|-----------|--------|
| Page loads at `/expenses/daily` | should load page at /expenses/daily | ✅ |
| Date selector shows current date by default | should show current date by default | ✅ |
| Previous/Next day navigation works | should navigate to previous day / next day | ✅ |
| "Today" button returns to current date | should return to current date when "Today" is clicked | ✅ |
| Expenses grouped by selected date | should show expenses for selected date | ✅ |
| Daily total is calculated correctly | should calculate daily total correctly | ✅ |
| Category filter works on daily view | should work with category filter on daily view | ✅ |
| Empty state shows when no expenses for date | should show empty state when no expenses for date | ✅ |
| Amounts display as currency format | should display amounts in currency format | ✅ |
| Category breakdown shown | should show category breakdown for the day | ✅ |

---

### F3: Monthly Expenses View → `monthly-view.spec.ts`

| Acceptance Criterion | Test Name | Status |
|---------------------|-----------|--------|
| Page loads at `/expenses/monthly` | should load page at /expenses/monthly | ✅ |
| Month selector shows current month by default | should show current month by default | ✅ |
| Previous/Next month navigation works | should navigate to previous month / next month | ✅ |
| "This Month" button returns to current month | should return to current month when "This Month" is clicked | ✅ |
| Monthly total is calculated correctly | should calculate monthly total correctly | ✅ |
| Category breakdown shows all categories | should show category breakdown | ✅ |
| Percentages calculated correctly (sum = 100%) | should calculate percentages correctly (sum = 100%) | ✅ |
| Categories with $0 show 0% | should show 0% for categories with $0 | ✅ |
| Empty state shows when no expenses for month | should show empty state when no expenses for month | ✅ |
| Amounts display as currency format | should display amounts in currency format | ✅ |

---

### F4: Category Filtering → `filtering.spec.ts`

| Acceptance Criterion | Test Name | Status |
|---------------------|-----------|--------|
| Category filter dropdown on index page | should show category filter dropdown on index page | ✅ |
| Category filter dropdown on daily view | should show category filter dropdown on daily view | ✅ |
| Category filter dropdown on monthly view | should show category filter dropdown on monthly view | ✅ |
| Filter shows only matching expenses | should filter to show only matching expenses | ✅ |
| Totals update to reflect filtered results | should update daily/monthly total when filtered | ✅ |
| "All Categories" option clears filter | should clear filter when "All Categories" is selected | ✅ |
| Filter persists through pagination | should persist filter through pagination | ✅ |
| Filter state preserved in URL | should preserve filter state in URL | ✅ |

---

## ✅ Data Validation → `validation.spec.ts`

### Description Field
| Acceptance Criterion | Test Name | Status |
|---------------------|-----------|--------|
| Required - shows error when empty | should show error when description is empty | ✅ |
| Max 255 characters - shows error when exceeded | should show error when description exceeds 255 characters | ✅ |
| Accepts special characters and Unicode | should accept special characters and Unicode | ✅ |

### Amount Field
| Acceptance Criterion | Test Name | Status |
|---------------------|-----------|--------|
| Required - shows error when empty | should show error when amount is empty | ✅ |
| Minimum $0.01 - shows error below | should show error when amount is below $0.01 | ✅ |
| Maximum $999,999.99 - shows error above | should show error when amount exceeds $999,999.99 | ✅ |
| Accepts decimal values (2 places) | should accept decimal values with 2 places | ✅ |
| Rejects non-numeric input | should reject non-numeric input | ✅ |

### Category Field
| Acceptance Criterion | Test Name | Status |
|---------------------|-----------|--------|
| Required - shows error when not selected | should show error when category is not selected | ✅ |
| Only accepts valid category values | should only accept valid category values | ✅ |
| Shows error for invalid category | should only accept valid category values | ✅ |

### Date Field
| Acceptance Criterion | Test Name | Status |
|---------------------|-----------|--------|
| Required - shows error when empty | should show error when date is empty | ✅ |
| Cannot be future date - shows error | should show error for future dates | ✅ |
| Cannot be older than 5 years - shows error | should show error for dates older than 5 years | ✅ |
| Accepts valid date format | should accept today's date / dates within 5 years | ✅ |

### Validation Errors
| Acceptance Criterion | Test Name | Status |
|---------------------|-----------|--------|
| Inline errors display next to fields | should display inline errors next to fields | ✅ |
| Flash message appears at top of form | should show flash message at top of form | ✅ |
| Form repopulates with previous input | should repopulate form with previous input after validation error | ✅ |
| All validation rules work server-side | should validate server-side (not just client-side) | ✅ |

---

## ✅ User Interface → `ui-accessibility.spec.ts`

### Layout & Navigation
| Acceptance Criterion | Test Name | Status |
|---------------------|-----------|--------|
| Header displays app title "Expense Tracker" | should display app title "Expense Tracker" in header | ✅ |
| Navigation links: All Expenses, Daily, Monthly | should show navigation links | ✅ |
| Current page highlighted in navigation | should highlight current page in navigation | ✅ |
| Flash messages appear and auto-hide | should show flash messages | ✅ |

### Material Design Compliance
| Acceptance Criterion | Test Name | Status |
|---------------------|-----------|--------|
| 8px grid system spacing used | should use 8px grid system spacing | ✅ |
| Cards have elevation shadows | should have elevation shadows on cards | ✅ |
| Primary color consistent throughout | should have consistent primary color | ✅ |
| Buttons have proper hover/active states | should have proper button hover/active states | ✅ |

### Responsive Design
| Acceptance Criterion | Test Name | Status |
|---------------------|-----------|--------|
| Desktop (1440px+) - full layout | should work on desktop (1440px) | ✅ |
| Laptop (1024px) - adjusted layout | should work on laptop (1024px) | ✅ |
| Tablet (768px) - responsive layout | should work on tablet (768px) | ✅ |
| Mobile (320px) - mobile-friendly layout | should work on mobile (320px) | ✅ |
| Touch targets minimum 44x44px on mobile | should have minimum 44x44px touch targets on mobile | ✅ |
| Tables scroll horizontally on small screens | should allow horizontal scroll for tables on small screens | ✅ |

### Empty States
| Acceptance Criterion | Test Name | Status |
|---------------------|-----------|--------|
| Index: "No expenses recorded yet..." | should show empty state on index when no expenses | ✅ |
| Filtered index: "No expenses found for..." | should show appropriate empty state for filtered results | ✅ |
| Daily: "No expenses recorded for this date." | See daily-view.spec.ts | ✅ |
| Monthly: "No expenses recorded for this month." | See monthly-view.spec.ts | ✅ |

---

## ✅ Accessibility → `ui-accessibility.spec.ts`

### Keyboard Navigation
| Acceptance Criterion | Test Name | Status |
|---------------------|-----------|--------|
| All interactive elements focusable via Tab | should allow Tab navigation through interactive elements | ✅ |
| Focus order is logical | should allow Tab navigation through interactive elements | ✅ |
| Focus indicators visible | should show visible focus indicators | ✅ |
| Enter/Space activates buttons | should activate buttons with Enter/Space | ✅ |

### Screen Reader Support
| Acceptance Criterion | Test Name | Status |
|---------------------|-----------|--------|
| All form inputs have labels | should have labels for all form inputs | ✅ |
| ARIA labels on icon buttons | should have ARIA labels on icon buttons | ✅ |
| Table headers properly associated | should have properly associated table headers | ✅ |
| Page titles descriptive | should have descriptive page titles | ✅ |

### Visual Accessibility
| Acceptance Criterion | Test Name | Status |
|---------------------|-----------|--------|
| Color contrast ratio ≥ 4.5:1 | should have sufficient color contrast (4.5:1) | ✅ |
| Information not conveyed by color alone | should not convey information by color alone | ✅ |
| Text resizable to 200% without loss | should allow text resize to 200% without content loss | ✅ |

---

## ✅ Performance → `ui-accessibility.spec.ts`

### Page Load
| Acceptance Criterion | Test Name | Status |
|---------------------|-----------|--------|
| Initial page load < 2 seconds | should load initial page quickly | ✅ |
| No layout shift after load | should not have excessive layout shift | ✅ |

---

## 📊 Test Statistics

- **Total Acceptance Criteria**: ~130
- **Test Files**: 6
- **Test Cases**: ~80+
- **Coverage**: 100% of specified acceptance criteria

## 🚀 Running Tests

```bash
# Run all tests
npm run test:e2e

# Run specific test file
npx playwright test crud.spec.ts

# Run in UI mode (recommended)
npm run test:e2e:ui
```

See [tests/e2e/README.md](../tests/e2e/README.md) for detailed instructions.
