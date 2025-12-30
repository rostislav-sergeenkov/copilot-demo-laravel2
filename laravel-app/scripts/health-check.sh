#!/bin/bash
# Health check script for deployed Laravel app on Fly.io
# Usage: fly ssh console -C "bash /var/www/html/scripts/health-check.sh"

set -e

echo "🔍 Laravel Health Check"
echo "================================"

# Application Info
echo -e "\n📊 Application Info:"
php artisan about --only=environment,cache,queue

# Database Connection
echo -e "\n💾 Database Connection:"
php artisan tinker --execute='
    try {
        $db = DB::connection()->getDatabaseName();
        echo "✅ Database: " . $db . "\n";
        $count = App\Models\Expense::count();
        echo "✅ Expenses count: " . $count . "\n";
    } catch (Exception $e) {
        echo "❌ Database error: " . $e->getMessage() . "\n";
        exit(1);
    }
'

# Routes Check
echo -e "\n🛣️  Routes (sample):"
php artisan route:list --columns=Method,URI,Name | grep -E '(login|expenses)' | head -10

# Storage Permissions
echo -e "\n🔐 Storage Permissions:"
ls -la storage/ | grep -E '^d' | awk '{print $1, $9}'

# Database File
echo -e "\n💾 Database File:"
ls -lh database/database.sqlite 2>/dev/null || echo "⚠️  Database file not found at default location"

echo -e "\n================================"
echo "✅ Health check complete!"
echo "$(date -u '+%Y-%m-%d %H:%M:%S UTC')"
