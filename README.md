# Expense Tracker

A modern Laravel 11 application for tracking daily expenses, featuring category filtering, daily/monthly summaries, and a Material UI-inspired design. Built for simplicity, reliability, and developer productivity.

---

> **Tech Stack:** Laravel 11 · PHP 8.4 · SQLite · Blade · Playwright

---

## 🚀 Features

- **Expense CRUD:** Create, view, update, and soft-delete expenses
- **Category Filtering:** Organize and filter by 7 built-in categories
- **Daily & Monthly Views:** Summarize expenses by day or month, with category breakdowns
- **Material UI Design:** Clean, accessible, and responsive Blade templates
- **Validation & Data Integrity:** Centralized rules, server-side validation, and soft deletes
- **Comprehensive Testing:** PHPUnit (unit/feature) and Playwright (E2E/UI)

---

## 🏁 Quick Start

> [!TIP]
> All Laravel code lives in `laravel-app/`. Run all commands from that directory.

### Prerequisites
- PHP 8.4+
- Composer
- SQLite (for local development)

### Installation
```bash
cd laravel-app
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed  # (Optional) Seed sample expenses
php artisan serve
```
Visit [http://127.0.0.1:8000](http://127.0.0.1:8000) to use the app.

---

## 🗂️ Project Structure

```
laravel-app/
├── app/Models/Expense.php         # Domain model (categories, validation, soft deletes)
├── app/Http/Controllers/ExpenseController.php
├── app/Http/Requests/             # Form validation (Store/Update)
├── resources/views/expenses/      # Blade views & _form partial
├── database/migrations/           # SQLite schema
├── database/factories/ExpenseFactory.php
├── database/seeders/ExpenseSeeder.php
├── routes/web.php                 # Routes (custom before resource)
├── tests/                         # PHPUnit tests
│   ├── Feature/ExpenseControllerTest.php
│   └── Unit/Models/ExpenseTest.php
├── tests/e2e/                     # Playwright E2E tests
│   ├── crud.spec.ts, ...
│   └── helpers.ts
└── ...
```

---

## 📊 Testing & Quality

- **Unit/Feature:**
  ```bash
  php artisan test
  php artisan test --filter=Expense
  ```
- **E2E/UI:**
  ```bash
  # From project root
  npx playwright test
  ```
- **CI/CD:**
  - GitHub Actions: Runs tests and code style checks on PRs to `main`
  - PRs blocked if tests fail

---

## 🧩 Key Concepts

- **Categories:**
  - Defined as constants in the model: Groceries, Transport, Housing and Utilities, Restaurants and Cafes, Health and Medicine, Clothing & Footwear, Entertainment
- **Validation:**
  - Centralized in `Expense::validationRules()` and Form Requests
- **Soft Deletes:**
  - Expenses are never hard-deleted; can be restored
- **Material UI:**
  - Accessible, responsive, and visually consistent
- **Factory States:**
  - For test data: `->category('Groceries')`, `->today()`

---

## 📚 Documentation

- [Project Architecture Blueprint](docs/Project_Architecture_Blueprint.md)
- [Complete Test Suite Overview](docs/Complete-Test-Suite-Overview.md)
- [E2E Testing Quickstart](E2E-TESTING-QUICKSTART.md)
- [Feature Tests Summary](docs/Feature-Tests-Summary.md)
- [Unit Tests Summary](docs/Unit-Tests-Summary.md)

---

> [!NOTE]
> For full developer workflow, conventions, and CI details, see [.github/copilot-instructions.md](.github/copilot-instructions.md)
