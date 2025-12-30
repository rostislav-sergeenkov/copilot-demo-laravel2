# Health check script for deployed Laravel app on Fly.io
# Usage: 
#   Remote: fly ssh console -C "pwsh /var/www/html/scripts/health-check.ps1"
#   Local: .\scripts\health-check.ps1

Write-Host "🔍 Laravel Health Check" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Gray

# Application Info
Write-Host "`n📊 Application Info:" -ForegroundColor Yellow
php artisan about --only=environment,cache,queue

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Failed to get application info" -ForegroundColor Red
    exit 1
}

# Database Connection
Write-Host "`n💾 Database Connection:" -ForegroundColor Yellow
$dbCheck = @"
try {
    `$db = DB::connection()->getDatabaseName();
    echo "✅ Database: " . `$db . PHP_EOL;
    `$count = App\Models\Expense::count();
    echo "✅ Expenses count: " . `$count . PHP_EOL;
} catch (Exception `$e) {
    echo "❌ Database error: " . `$e->getMessage() . PHP_EOL;
    exit(1);
}
"@

php artisan tinker --execute=$dbCheck

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Database connection failed" -ForegroundColor Red
    exit 1
}

# Routes Check
Write-Host "`n🛣️  Routes (sample):" -ForegroundColor Yellow
php artisan route:list --columns=Method,URI,Name | Select-String -Pattern "(login|expenses)" | Select-Object -First 10

# Storage Permissions
Write-Host "`n🔐 Storage Permissions:" -ForegroundColor Yellow
if (Test-Path "storage") {
    Get-ChildItem storage -Directory | ForEach-Object {
        Write-Host "  $($_.Name)" -ForegroundColor Gray
    }
} else {
    Write-Host "⚠️  Storage directory not found" -ForegroundColor Yellow
}

# Database File
Write-Host "`n💾 Database File:" -ForegroundColor Yellow
if (Test-Path "database/database.sqlite") {
    $dbFile = Get-Item "database/database.sqlite"
    Write-Host "✅ Size: $([math]::Round($dbFile.Length / 1KB, 2)) KB" -ForegroundColor Green
} else {
    Write-Host "⚠️  Database file not found at default location" -ForegroundColor Yellow
}

Write-Host "`n================================" -ForegroundColor Gray
Write-Host "✅ Health check complete!" -ForegroundColor Green
Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss UTC')" -ForegroundColor Gray
