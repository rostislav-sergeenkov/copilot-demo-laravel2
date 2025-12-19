
<p align="center">
	<img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="320" alt="Laravel Logo">
</p>

# Expense Tracker

[![Laravel Tests](https://github.com/rostislav-sergeenkov/copilot-demo-laravel2/actions/workflows/laravel.yml/badge.svg)](https://github.com/rostislav-sergeenkov/copilot-demo-laravel2/actions/workflows/laravel.yml)

> **A modern, Material-inspired expense tracking app built with Laravel 11 and SQLite.**

---

## Overview

Expense Tracker is a simple, robust web application for recording, categorizing, and analyzing your daily and monthly expenses. Designed for clarity and productivity, it leverages Laravel's latest features and best practices.

:::info
**Tech Stack:** Laravel 11 · PHP 8.4+ · SQLite · Blade · Material UI principles
:::

---

## Features

- **Expense CRUD**: Add, edit, delete, and restore expenses
- **Category Filtering**: Organize by Groceries, Transport, Housing, and more
- **Daily & Monthly Views**: Summaries, breakdowns, and category percentages
- **Soft Deletes**: Restore deleted expenses anytime
- **Material UI Design**: Clean, accessible, and responsive interface
- **Comprehensive Tests**: Feature tests (PHPUnit) and E2E tests (Playwright)
- **Full Test Coverage**: 100% acceptance criteria coverage with automated E2E tests

---

## Quickstart

### Prerequisites
- PHP 8.2+
- Composer
- SQLite (for local/dev)

### Setup
```bash
cd laravel-app
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed   # Optional: seed sample expenses
php artisan serve
# App runs at http://127.0.0.1:8000
```

### Running Tests
```bash
# Backend tests (PHPUnit)
php artisan test                    # All tests
php artisan test --filter=Expense   # Only expense tests

# E2E tests (Playwright)
npm run test:e2e                    # All E2E tests
npm run test:e2e:ui                 # UI mode (recommended)
```

See [E2E-TESTING-QUICKSTART.md](E2E-TESTING-QUICKSTART.md) for detailed E2E testing guide.

---

## Architecture

:::info
See [`docs/Project_Architecture_Blueprint.md`](docs/Project_Architecture_Blueprint.md) for a full technical deep-dive.
:::

- **MVC Structure**: All code in `laravel-app/`
- **Single Model**: `Expense` (`app/Models/Expense.php`)
- **Validation**: Centralized in model and Form Requests
- **Views**: Blade templates in `resources/views/expenses/` (shared `_form.blade.php`)
- **Routes**: Custom views (`/expenses/daily`, `/expenses/monthly`) defined before resource routes
- **Testing**: 
  - Backend: `tests/Feature/ExpenseControllerTest.php` with database factories and states
  - E2E: `tests/e2e/*.spec.ts` - Playwright tests covering all acceptance criteria
- **CI/CD**: GitHub Actions runs both PHPUnit and Playwright tests on PRs

---

## Example Usage

```php
// Add a new expense (controller)
Expense::create([
		'description' => 'Lunch',
		'amount' => 12.50,
		'category' => 'Groceries',
		'date' => '2025-12-08',
]);

// Filter by category (controller)
$expenses = Expense::where('category', 'Transport')->paginate(15);
```

---

## Project Highlights

- **Centralized Categories**: `Expense::CATEGORIES` is the single source of truth
- **Soft Deletes**: Never lose data accidentally
- **Material UI**: Consistent, modern look and feel
- **Factory States**: For easy test data generation
- **Extensible**: Add new categories or fields with minimal changes

---

## FAQ

> **Q:** How do I add a new expense category?
>
> **A:** Edit the `CATEGORIES` constant in `app/Models/Expense.php` and update any relevant tests.

> **Q:** How do I run the app in production?
>
> **A:** Use a production-ready database (e.g., MySQL), set up environment variables, and run migrations as usual. See Laravel docs for deployment best practices.

---

> _Expense Tracker is a demo project for exploring Laravel best practices and modern PHP workflows._
