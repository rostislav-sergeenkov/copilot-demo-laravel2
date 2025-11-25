# Expense Tracker — Project Specification

## Goal
Develop a web application to record and view daily financial transactions.

## Project Management
- Implementation lifecycle tracked via GitHub Issues and GitHub Projects.

## Features

1. **Expense CRUD UI**
   - Graphical interface for creating, reading, updating, and deleting expenses.

2. **Daily Expenses Table**
   - Display a table of expenses for the current day.

3. **Monthly Expenses Table**
   - Display a table of expenses for the current month.

4. **Category Filtering**
   - Filter expenses by category.

5. **CSV Export**
   - Export monthly expenses to an Excel-compatible CSV file.

## Technical Requirements

1. **Framework**
   - Built using Laravel, following Laravel coding standards and best practices.

2. **Code Quality**
   - Enforced via Larastan.

3. **Testing**
   - Unit and integration tests implemented for both frontend and backend.

4. **Source Control**
   - Source code hosted on GitHub.

5. **CI/CD**
   - GitHub Actions pipeline:
     - Runs Larastan code quality checks.
     - Executes unit and integration tests on every pull request.
     - Blocks merging if tests fail.

6. **Data Storage**
   - Expenses stored in an SQLite table with the following fields:
     - `description` (string): Short description (e.g., “Lunch”)
     - `amount` (decimal): Expense amount
     - `category` (string): Category (Groceries, Transport, Housing and Utilities, Restaurants and Cafes, Health and Medicine, Clothing & Footwear, Entertainment)
     - `date` (date): Date of the expense

## Categories

- Groceries
- Transport
- Housing and Utilities
- Restaurants and Cafes
- Health and Medicine
- Clothing & Footwear
- Entertainment

## Quality Assurance

- All code changes must pass Larastan checks and automated tests before merging.
