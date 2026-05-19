#!/bin/bash

# ============================================
# PLN Inventaris - Production Deployment Script
# ============================================
# Run this script on production server after uploading code
# Make executable: chmod +x DEPLOYMENT_COMMANDS.sh
# Run: ./DEPLOYMENT_COMMANDS.sh
# ============================================

echo "🚀 Starting PLN Inventaris Deployment..."
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# ============================================
# 1. Check PHP version
# ============================================
echo "📋 Checking PHP version..."
php -v | head -n 1
echo ""

# ============================================
# 2. Install/Update Dependencies
# ============================================
echo "📦 Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Dependencies installed${NC}"
else
    echo -e "${RED}✗ Failed to install dependencies${NC}"
    exit 1
fi
echo ""

# ============================================
# 3. Environment Setup
# ============================================
echo "⚙️  Checking environment configuration..."
if [ ! -f .env ]; then
    echo -e "${YELLOW}⚠ .env file not found!${NC}"
    echo "Please create .env file from .env.production.example"
    echo "Then run this script again."
    exit 1
fi

# Check if APP_KEY is set
if grep -q "APP_KEY=base64:" .env; then
    echo -e "${GREEN}✓ APP_KEY is set${NC}"
else
    echo -e "${YELLOW}⚠ Generating APP_KEY...${NC}"
    php artisan key:generate --force
fi
echo ""

# ============================================
# 4. Clear All Caches
# ============================================
echo "🧹 Clearing all caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
echo -e "${GREEN}✓ Caches cleared${NC}"
echo ""

# ============================================
# 5. Run Migrations
# ============================================
echo "🗄️  Running database migrations..."
read -p "Run migrations? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Migrations completed${NC}"
    else
        echo -e "${RED}✗ Migration failed${NC}"
        exit 1
    fi
fi
echo ""

# ============================================
# 6. Storage Link
# ============================================
echo "🔗 Creating storage symlink..."
php artisan storage:link
echo -e "${GREEN}✓ Storage linked${NC}"
echo ""

# ============================================
# 7. Optimize for Production
# ============================================
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✓ Optimization complete${NC}"
echo ""

# ============================================
# 8. Set Permissions
# ============================================
echo "🔐 Setting file permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
echo -e "${GREEN}✓ Permissions set${NC}"
echo ""

# ============================================
# 9. Restart Services (requires sudo)
# ============================================
echo "🔄 Restarting services..."
read -p "Restart PHP-FPM and web server? (requires sudo) (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    # Detect PHP version
    PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
    
    # Restart PHP-FPM
    if systemctl is-active --quiet php${PHP_VERSION}-fpm; then
        sudo systemctl restart php${PHP_VERSION}-fpm
        echo -e "${GREEN}✓ PHP-FPM restarted${NC}"
    fi
    
    # Restart Nginx or Apache
    if systemctl is-active --quiet nginx; then
        sudo systemctl restart nginx
        echo -e "${GREEN}✓ Nginx restarted${NC}"
    elif systemctl is-active --quiet apache2; then
        sudo systemctl restart apache2
        echo -e "${GREEN}✓ Apache restarted${NC}"
    fi
fi
echo ""

# ============================================
# 10. Verification
# ============================================
echo "✅ Deployment completed!"
echo ""
echo "📊 Verification Checklist:"
echo "  1. Check APP_URL in .env: $(grep APP_URL .env)"
echo "  2. Check APP_ENV: $(grep APP_ENV .env)"
echo "  3. Check APP_DEBUG: $(grep APP_DEBUG .env)"
echo ""
echo "🌐 Test your application:"
echo "  - Open: https://poshan.my.id/pln"
echo "  - Check browser console (F12) for errors"
echo "  - Test floor plan placement feature"
echo "  - Verify all AJAX requests work"
echo ""
echo "📝 Logs location:"
echo "  - Laravel: storage/logs/laravel.log"
echo "  - Nginx: /var/log/nginx/error.log"
echo "  - PHP-FPM: /var/log/php${PHP_VERSION}-fpm.log"
echo ""
echo -e "${GREEN}🎉 Deployment script finished!${NC}"
