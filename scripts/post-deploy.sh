#!/bin/bash
# ============================================
# PLN K3 Inventaris - Post-Deploy Script
# ============================================
# Run this INSIDE the container after deployment.
# Ensures storage link, permissions, and caches are correct.
#
# Usage (from host):
#   docker compose exec frankenphp bash scripts/post-deploy.sh
#
# Or directly inside container:
#   bash scripts/post-deploy.sh
# ============================================

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${GREEN}🚀 PLN K3 - Post-Deploy Script${NC}"
echo "============================================"

# 1. Storage link (idempotent - --force recreates if broken)
echo -e "\n${YELLOW}[1/6] Creating storage symlink...${NC}"
php artisan storage:link --force
echo -e "${GREEN}✓ Storage linked${NC}"

# 2. Directory permissions
echo -e "\n${YELLOW}[2/6] Setting directory permissions...${NC}"
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
# Ensure storage subdirectories exist
mkdir -p storage/app/public/qrcodes
mkdir -p storage/app/public/floor-plans
mkdir -p storage/app/public/avatars
mkdir -p storage/app/public/signatures
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
echo -e "${GREEN}✓ Permissions set${NC}"

# 3. Build frontend assets (skip if node_modules missing)
echo -e "\n${YELLOW}[3/6] Building frontend assets...${NC}"
if [ -d "node_modules" ]; then
    npm run build
    echo -e "${GREEN}✓ Assets built${NC}"
elif command -v npm &> /dev/null; then
    npm ci && npm run build
    echo -e "${GREEN}✓ Dependencies installed & assets built${NC}"
else
    echo -e "${RED}⚠ npm not available - skipping asset build${NC}"
    echo "  Make sure public/build/manifest.json exists for production!"
fi

# 4. Cache configuration (AFTER storage:link and asset build)
echo -e "\n${YELLOW}[4/6] Caching configuration...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✓ Config/route/view cached${NC}"

# 5. Run pending migrations
echo -e "\n${YELLOW}[5/6] Running pending migrations...${NC}"
php artisan migrate --force
echo -e "${GREEN}✓ Migrations complete${NC}"

# 6. Verify storage link works
echo -e "\n${YELLOW}[6/6] Verifying storage link...${NC}"
if [ -L "public/storage" ] && [ -d "public/storage" ]; then
    echo -e "${GREEN}✓ public/storage symlink is valid${NC}"
else
    echo -e "${RED}✗ public/storage symlink is BROKEN!${NC}"
    echo "  Try: rm -f public/storage && php artisan storage:link"
    exit 1
fi

echo ""
echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}✅ Post-deploy complete!${NC}"
echo -e "${GREEN}============================================${NC}"
