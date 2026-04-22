#!/bin/bash

echo "Clearing all Laravel caches..."

php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

echo "All caches cleared successfully!"
echo ""
echo "Silakan refresh browser Anda (Ctrl+Shift+R atau Cmd+Shift+R)"
