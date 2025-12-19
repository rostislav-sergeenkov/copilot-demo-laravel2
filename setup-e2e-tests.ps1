#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Setup script for Playwright E2E tests
.DESCRIPTION
    This script installs all dependencies needed to run Playwright E2E tests
    for the Laravel Expense Tracker application.
#>

Write-Host "🎭 Setting up Playwright E2E Tests..." -ForegroundColor Cyan

# Navigate to laravel-app directory
Set-Location -Path "$PSScriptRoot\..\laravel-app" -ErrorAction Stop

# Check if Node.js is installed
Write-Host "`n📦 Checking Node.js installation..." -ForegroundColor Yellow
if (-not (Get-Command node -ErrorAction SilentlyContinue)) {
    Write-Host "❌ Node.js is not installed. Please install Node.js 18+ from https://nodejs.org/" -ForegroundColor Red
    exit 1
}

$nodeVersion = node --version
Write-Host "✅ Node.js $nodeVersion found" -ForegroundColor Green

# Check if npm is installed
if (-not (Get-Command npm -ErrorAction SilentlyContinue)) {
    Write-Host "❌ npm is not installed" -ForegroundColor Red
    exit 1
}

# Install npm dependencies
Write-Host "`n📦 Installing npm dependencies..." -ForegroundColor Yellow
npm install

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Failed to install npm dependencies" -ForegroundColor Red
    exit 1
}

Write-Host "✅ npm dependencies installed" -ForegroundColor Green

# Install Playwright browsers
Write-Host "`n🌐 Installing Playwright browsers..." -ForegroundColor Yellow
Write-Host "This may take a few minutes..." -ForegroundColor Gray
npx playwright install

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Failed to install Playwright browsers" -ForegroundColor Red
    exit 1
}

Write-Host "✅ Playwright browsers installed" -ForegroundColor Green

# Check if Laravel is set up
Write-Host "`n🔍 Checking Laravel setup..." -ForegroundColor Yellow

if (-not (Test-Path ".env")) {
    Write-Host "⚠️  .env file not found. Copying from .env.example..." -ForegroundColor Yellow
    Copy-Item ".env.example" ".env"
}

if (-not (Test-Path "vendor")) {
    Write-Host "⚠️  Vendor directory not found. Run 'composer install' first" -ForegroundColor Yellow
} else {
    Write-Host "✅ Composer dependencies found" -ForegroundColor Green
}

if (-not (Test-Path "database/database.sqlite")) {
    Write-Host "⚠️  SQLite database not found. Creating..." -ForegroundColor Yellow
    New-Item -Path "database/database.sqlite" -ItemType File -Force | Out-Null
    
    Write-Host "🔄 Running migrations..." -ForegroundColor Yellow
    php artisan migrate --force
}

Write-Host "`n✨ Setup complete!" -ForegroundColor Green
Write-Host "`nYou can now run tests with:" -ForegroundColor Cyan
Write-Host "  npm run test:e2e          - Run all tests" -ForegroundColor White
Write-Host "  npm run test:e2e:ui       - Run with UI mode (recommended)" -ForegroundColor White
Write-Host "  npm run test:e2e:headed   - Run in headed mode" -ForegroundColor White
Write-Host "  npm run test:e2e:debug    - Run in debug mode" -ForegroundColor White

Write-Host "`n📖 Documentation:" -ForegroundColor Cyan
Write-Host "  tests/e2e/README.md       - E2E testing guide" -ForegroundColor White
Write-Host "  TESTING.md                - Test coverage mapping" -ForegroundColor White

Write-Host "`n🚀 To start testing, run: npm run test:e2e:ui" -ForegroundColor Green
