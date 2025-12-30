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
- **PHP 8.4+** with SQLite extension enabled
- **Composer** (latest version)
- **SQLite** (for local development)
- **Node.js 18+** and **npm** (for E2E tests)

### Local Installation

#### Step 1: Clone and Navigate
```bash
git clone <repository-url>
cd copilot-demo-laravel2/laravel-app
```

#### Step 2: Install Dependencies
```bash
composer install
npm install  # For E2E tests
```

#### Step 3: Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create SQLite database
touch database/database.sqlite  # Unix/Mac
# Or on Windows:
# type nul > database/database.sqlite
```

#### Step 4: Configure Authentication
Edit `.env` and set your credentials:
```env
AUTH_USERNAME=your.email@example.com
PASSWORD_HASH=your_bcrypt_hash_here
```

To generate a password hash:
```bash
php artisan tinker
# Then run: echo Hash::make('your-password');
# Copy the output to PASSWORD_HASH in .env
```

#### Step 5: Database Setup
```bash
# Run migrations
php artisan migrate

# (Optional) Seed sample expenses for testing
php artisan db:seed
```

#### Step 6: Start Development Server
```bash
php artisan serve
```

Visit [http://127.0.0.1:8000](http://127.0.0.1:8000) to use the app.

**Login with your configured credentials:**
- Username: Value from `AUTH_USERNAME` in `.env`
- Password: The plain text password you hashed for `PASSWORD_HASH`

### Running Tests
```bash
# Run all tests (unit + feature + architecture)
php artisan test

# Run E2E tests (happy path)
composer test:e2e

# Run complete test suite
composer test:all
```

---

## 🚀 Deployment to Fly.io

### Prerequisites
- [Fly.io account](https://fly.io/signup)
- [Flyctl CLI](https://fly.io/docs/hands-on/install-flyctl/) installed and authenticated

### Step 1: Initial Setup
```bash
cd laravel-app

# Login to Fly.io
flyctl auth login

# Launch app (follow prompts)
flyctl launch
```

During `flyctl launch`:
- ✅ Choose app name or accept generated name
- ✅ Select region (e.g., `fra` for Frankfurt)
- ❌ **Do NOT** set up PostgreSQL (we use SQLite)
- ❌ **Do NOT** set up Redis
- ✅ Deploy now: **No** (we need to configure first)

### Step 2: Create Persistent Volume
SQLite requires a persistent volume to store the database:

```bash
# Create 1GB volume in same region as app
flyctl volumes create data --region fra --size 1 -a your-app-name
```

Replace `your-app-name` with your Fly.io app name.

### Step 3: Configure Environment Secrets
Set required secrets (never commit these!):

```bash
# Generate new APP_KEY for production
php artisan key:generate --show

# Set secrets on Fly.io
flyctl secrets set APP_KEY=base64:your-generated-key
flyctl secrets set APP_ENV=production
flyctl secrets set APP_DEBUG=false
flyctl secrets set AUTH_USERNAME=your.email@example.com
flyctl secrets set PASSWORD_HASH='$2y$12$your_bcrypt_hash_here'

# Optional: Set session secrets
flyctl secrets set SESSION_DRIVER=database
flyctl secrets set SESSION_LIFETIME=30
```

**Important:** Wrap `PASSWORD_HASH` in single quotes to prevent shell interpretation of `$`.

### Step 4: Verify fly.toml Configuration
Ensure `fly.toml` has proper volume mounting and migration setup:

```toml
[mounts]
  source = "data"
  destination = "/var/www/html/database"

[deploy]
  release_command = "php /var/www/html/artisan migrate --force"
```

### Step 5: Deploy Application
```bash
# Deploy to Fly.io
flyctl deploy

# Check deployment status
flyctl status

# View logs
flyctl logs
```

### Step 6: Access Your App
```bash
# Open app in browser
flyctl open

# Or visit directly
https://your-app-name.fly.dev
```

### Post-Deployment Management

#### Health Checks
After deployment, verify your app is running correctly:

```bash
# Quick health check
fly ssh console -C "php artisan about"

# Database connection check
fly ssh console -C "php artisan tinker --execute='echo DB::connection()->getDatabaseName();'"

# Test a query
fly ssh console -C "php artisan tinker --execute='echo App\Models\Expense::count();'"

# HTTP endpoint check
curl -I https://your-app-name.fly.dev/login
```

**Comprehensive Health Check Script:**

Create `scripts/health-check.sh` in `laravel-app/`:

```bash
#!/bin/bash
# Health check script for deployed Laravel app

echo "🔍 Laravel Health Check"
echo "================================"

echo -e "\n📊 Application Info:"
php artisan about --only=environment,cache,queue

echo -e "\n💾 Database Connection:"
php artisan tinker --execute='
    echo "Database: " . DB::connection()->getDatabaseName() . "\n";
    echo "Expenses: " . App\Models\Expense::count() . "\n";
'

echo -e "\n🛣️  Routes:"
php artisan route:list --columns=Method,URI,Name | grep -E '(login|expenses)' | head -10

echo -e "\n✅ Health check complete!"
```

Run it remotely:
```bash
fly ssh console -C "bash /var/www/html/scripts/health-check.sh"
```

**Automated CI/CD Health Checks:**

The GitHub Actions deployment workflow automatically runs health checks after each deployment:
- ✅ Application info and environment validation
- ✅ Database connection and query test
- ✅ Route validation
- ✅ HTTP endpoint verification

See [`.github/workflows/deploy.yml`](.github/workflows/deploy.yml) for implementation details.

#### Viewing Logs
```bash
# Stream live logs
flyctl logs

# View recent logs
flyctl logs --tail 100
```

#### SSH into Container
```bash
# Connect to running container
flyctl ssh console

# Check migrations
ls -la /var/www/html/database/migrations/

# Check database file
ls -la /var/www/html/database/database.sqlite
```

#### Run Artisan Commands
```bash
# SSH into container first
flyctl ssh console

# Then run commands
cd /var/www/html
php artisan migrate:status
php artisan cache:clear
php artisan config:cache
```

#### Scaling and Resources
```bash
# Scale to multiple instances
flyctl scale count 2

# Change VM size
flyctl scale vm shared-cpu-1x

# View current scale
flyctl scale show
```

#### Update Secrets
```bash
# List current secrets (values hidden)
flyctl secrets list

# Update a secret
flyctl secrets set PASSWORD_HASH='new_hash_here'

# Remove a secret
flyctl secrets unset SECRET_NAME
```

#### Redeploy After Changes
```bash
# After code changes, redeploy
git push  # Push changes to repo
flyctl deploy  # Deploy to Fly.io

# Or deploy from local directory without git
flyctl deploy --local-only
```

### Troubleshooting

#### Database Issues
```bash
# Check if volume is mounted
flyctl ssh console -C "df -h"

# Verify database file exists
flyctl ssh console -C "ls -la /var/www/html/database/"

# Run migrations manually
flyctl ssh console -C "php /var/www/html/artisan migrate --force"
```

#### Application Not Starting
```bash
# Check logs for errors
flyctl logs

# Verify secrets are set
flyctl secrets list

# Check app status
flyctl status

# Restart app
flyctl apps restart your-app-name
```

#### Permission Issues
```bash
# SSH into container
flyctl ssh console

# Fix permissions
cd /var/www/html
chown -R www-data:www-data storage bootstrap/cache database
chmod -R 775 storage bootstrap/cache database
```

### Security Best Practices

1. **Never commit secrets** - Use `flyctl secrets set` for all sensitive data
2. **Use strong passwords** - Generate bcrypt hashes with high cost factor
3. **Enable HTTPS** - Fly.io provides automatic HTTPS certificates
4. **Set APP_DEBUG=false** - Disable debug mode in production
5. **Regular updates** - Keep dependencies updated with `composer update`

### Cost Optimization

- **Free tier**: 3 shared-cpu-1x VMs with 256MB RAM (sufficient for small apps)
- **Volume**: 1GB volume included in free tier
- **Bandwidth**: 100GB outbound transfer/month included
- **Monitor usage**: Check dashboard at https://fly.io/dashboard

For more details, see [Fly.io Documentation](https://fly.io/docs/).

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
