# Expense Tracker — Laravel Project Specification

## Goal
Record and view daily financial transactions.

## Features
1. **Graphical CRUD user interface for expenses**
    - Add, edit, delete, and view expenses.
2. **Display a table of daily expenses**
    - List all expenses for the current day.
3. **Display a table of monthly expenses**
    - List all expenses for the current month.
4. **Filter expenses by category**
    - Categories: Groceries, Transport, Housing and Utilities, Restaurants and Cafes, Health and Medicine, Clothing & Footwear, Entertainment.
5. **Export monthly expenses to CSV**
    - Download monthly expenses in Excel-compatible CSV format.

## Technical Requirements
1. **Framework**: Laravel
2. **Coding Standards**: Follow Laravel best practices
3. **Code Quality**: Use Larastan for static analysis
4. **Testing**: Implement unit and integration tests for both frontend and backend
5. **Source Control**: Store code on GitHub
6. **CI/CD**: Use GitHub Actions to run Larastan, unit, and integration tests on every pull request. Block merging if tests fail.
7. **Database**: SQLite
    - Table: `expenses`
    - Fields:
        - `description` (string): Short description (e.g., “Lunch”)
        - `amount` (decimal): Expense amount
        - `category` (string): Category
        - `date` (date): Date of expense

## Planning and Tracking
- Use GitHub Issues and GitHub Project for lifecycle tracking.

---
**Specification generated on:** 2025-11-25
**Elapsed time:** [pending seconds]
