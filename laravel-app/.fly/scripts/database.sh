#!/usr/bin/env bash
# Database setup script for Fly.io deployment
# This script runs during container startup

# Check if we're using SQLite and the database doesn't exist
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
    DB_PATH="/var/www/html/storage/database/database.sqlite"
    DB_DIR="/var/www/html/storage/database"
    
    # Create database directory if it doesn't exist
    mkdir -p "$DB_DIR"
    
    # Ensure www-data owns the directory and can write to it
    chown -R www-data:www-data "$DB_DIR"
    chmod -R 775 "$DB_DIR"
    
    # Create database file if it doesn't exist
    if [ ! -f "$DB_PATH" ]; then
        echo "Creating SQLite database at $DB_PATH"
        touch "$DB_PATH"
        chown www-data:www-data "$DB_PATH"
        chmod 664 "$DB_PATH"
    else
        echo "SQLite database already exists at $DB_PATH"
        # Ensure existing database has correct permissions
        chown www-data:www-data "$DB_PATH"
        chmod 664 "$DB_PATH"
    fi
fi

# Run migrations
echo "Running database migrations..."
/usr/bin/php /var/www/html/artisan migrate --force --no-ansi -q

# Optional: Seed database if needed (uncomment if you want to seed on every deploy)
# /usr/bin/php /var/www/html/artisan db:seed --force --no-ansi -q
