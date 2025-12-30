# Health Check Scripts

Comprehensive health check utilities for Laravel Expense Tracker application.

## Available Scripts

### 1. Local Health Check (PowerShell)

**File**: `health-check-local.ps1`

**Purpose**: Check local development environment health

**Usage**:
```powershell
# Check local environment
cd laravel-app
.\scripts\health-check-local.ps1

# Check remote Fly.io deployment
.\scripts\health-check-local.ps1 -Remote -AppName your-app-name
```

**Checks**:
- ✅ Application info and environment
- ✅ Database connection and file
- ✅ Migration status
- ✅ Routes
- ✅ Storage directories
- ✅ .env configuration
- ✅ Dependencies (composer, npm)

### 2. Remote Health Check (Bash)

**File**: `health-check.sh`

**Purpose**: Health check script for deployed application

**Usage**:
```bash
# Run directly on Fly.io
fly ssh console -C "bash /var/www/html/scripts/health-check.sh"

# Or SSH in and run manually
fly ssh console
cd /var/www/html
bash scripts/health-check.sh
```

**Checks**:
- ✅ Application configuration
- ✅ Database connection and queries
- ✅ Routes validation
- ✅ Storage permissions
- ✅ Database file

### 3. Remote Health Check (PowerShell)

**File**: `health-check.ps1`

**Purpose**: PowerShell version for remote health check

**Usage**:
```powershell
# Run on Fly.io container (if PowerShell is available)
fly ssh console -C "pwsh /var/www/html/scripts/health-check.ps1"
```

## CI/CD Integration

Health checks are automatically run in the deployment pipeline.

**GitHub Actions**: `.github/workflows/deploy.yml`

**Automated Checks After Deployment**:
1. ⏳ Wait 30 seconds for app startup
2. 📊 Verify application info (`php artisan about`)
3. 💾 Test database connection and query
4. 🛣️  Validate routes
5. 🌐 HTTP endpoint check (login page returns 200)
6. ✅ Create deployment tag only if all pass

**Benefits**:
- Immediate deployment verification
- Catches configuration errors
- Prevents broken deployments from being tagged
- Audit trail in CI/CD logs

## Quick Commands

### Local Development

```powershell
# Complete local check
.\scripts\health-check-local.ps1

# Just database check
php artisan tinker --execute='echo App\Models\Expense::count();'

# Just environment
php artisan env

# Just routes
php artisan route:list
```

### Remote (Fly.io)

```bash
# Quick check
fly ssh console -C "php artisan about"

# Database check
fly ssh console -C "php artisan tinker --execute='echo App\Models\Expense::count();'"

# Full check
fly ssh console -C "bash /var/www/html/scripts/health-check.sh"

# HTTP check
curl -I https://your-app-name.fly.dev/login
```

## Common Issues

### Database Not Found
**Symptoms**: "database or disk is full" or "unable to open database file"

**Solution**:
```bash
# Check volume is mounted
fly ssh console -C "df -h | grep /data"

# Check database file
fly ssh console -C "ls -la /var/www/html/database/"

# Run migrations
fly ssh console -C "php artisan migrate --force"
```

### Permission Denied
**Symptoms**: "Permission denied" on storage directories

**Solution**:
```bash
fly ssh console
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### HTTP 500 Error
**Symptoms**: Login page returns 500 status

**Solution**:
```bash
# Check logs
fly logs

# Check environment
fly ssh console -C "php artisan about"

# Check .env secrets
fly secrets list

# Verify APP_KEY is set
fly secrets set APP_KEY=base64:your-key-here
```

## Integration with Documentation

See also:
- **README.md**: Complete deployment guide with health checks
- **docs/project-architecture-blueprint.md**: CI/CD pipeline details
- **docs/complete-test-suite-overview.md**: Testing and CI/CD integration
- **.github/workflows/deploy.yml**: Automated health check implementation

## Best Practices

1. **Run local health check before committing** major changes
2. **Check CI/CD logs** after each deployment
3. **Add new checks** as features are added
4. **Document findings** when issues are discovered
5. **Keep scripts updated** with application changes

## Future Enhancements

Potential additions:
- [ ] Performance metrics (response time, memory usage)
- [ ] Security checks (SSL, headers)
- [ ] Integration checks (external APIs if added)
- [ ] Backup verification
- [ ] User authentication test
- [ ] Cron job status (if scheduled tasks added)
