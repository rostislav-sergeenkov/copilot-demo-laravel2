# copilot-demo-laravel2

[![Laravel Tests](https://github.com/rostislav-sergeenkov/copilot-demo-laravel2/actions/workflows/laravel.yml/badge.svg)](https://github.com/rostislav-sergeenkov/copilot-demo-laravel2/actions/workflows/laravel.yml)

Demo Laravel project for Copilot

## Expense Tracker

A simple expense tracking application built with Laravel.

### Features

- **CRUD Operations**: Create, read, update, and delete expenses
- **Categories**: Organize expenses by category (Groceries, Transport, Housing, etc.)
- **Views**: View expenses by list, daily summary, or monthly summary
- **Category Filtering**: Filter expenses by category across all views
- **Soft Deletes**: Safely delete expenses with ability to restore

### Getting Started

#### Prerequisites

- PHP 8.2+
- Composer
- SQLite (for development/testing)

#### Installation

```bash
cd laravel-app
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
```

#### Seeding Sample Data

To populate the database with sample expenses:

```bash
php artisan db:seed
```

Or seed only expenses:

```bash
php artisan db:seed --class=ExpenseSeeder
```

#### Running Tests

```bash
php artisan test
```

#### Starting the Development Server

```bash
php artisan serve
```

Visit http://127.0.0.1:8000 to view the application.
