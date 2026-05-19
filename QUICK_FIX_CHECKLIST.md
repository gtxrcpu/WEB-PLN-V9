# 🚨 Quick Fix Checklist - Mixed Content Error

## Problem
Application works on localhost but fails on production with:
- ❌ Mixed Content Error
- ❌ Failed to fetch
- ❌ net::ERR_BLOCKED_BY_CLIENT

## Root Cause
HTTPS page trying to load HTTP resources (blocked by browser security)

---

## ✅ Step-by-Step Fix (5 Minutes)

### Step 1: Update .env on Production Server ⚠️ CRITICAL

SSH into your production server and edit `.env`:

```bash
ssh user@poshan.my.id
cd /path/to/plnweb
nano .env
```

**Change these lines:**
```env
# OLD (WRONG)
APP_URL=http://localhost
APP_ENV=local
APP_DEBUG=true

# NEW (CORRECT)
APP_URL=https://poshan.my.id/pln
APP_ENV=production
APP_DEBUG=false
```

Save and exit (Ctrl+X, Y, Enter)

### Step 2: Clear Laravel Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 3: Rebuild Caches

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 4: Restart Services

```bash
# For PHP-FPM (adjust version if needed)
sudo systemctl restart php8.2-fpm

# For Nginx
sudo systemctl restart nginx

# OR for Apache
sudo systemctl restart apache2
```

### Step 5: Test

1. Open: https://poshan.my.id/pln
2. Press F12 (open DevTools)
3. Go to Console tab
4. Look for errors:
   - ✅ No red errors = Fixed!
   - ❌ Still errors = Continue to advanced fixes

---

## 🔍 Verification

### Test Floor Plan Feature

1. Login to application
2. Go to Floor Plans → Select a plan → Placement mode
3. Drag an equipment marker
4. Check console for: `✓ Saved: EQUIPMENT-NAME at X% Y%`
5. Refresh page → marker should stay in new position

### Check URLs in Page Source

Right-click page → View Page Source

Search for `http://` (Ctrl+F)

**Should find:**
- ✅ `https://poshan.my.id/pln/...` (GOOD)

**Should NOT find:**
- ❌ `http://poshan.my.id/pln/...` (BAD)

---

## 🆘 If Still Not Working

### Check 1: Verify .env is loaded

Create test route:
```bash
php artisan tinker
>>> config('app.url')
```

Should output: `"https://poshan.my.id/pln"`

If not, run: `php artisan config:clear` again

### Check 2: Web Server Configuration

**For Nginx**, check if SSL is configured:
```bash
sudo nano /etc/nginx/sites-available/default
```

Should have:
```nginx
listen 443 ssl;
ssl_certificate /path/to/cert.pem;
ssl_certificate_key /path/to/key.pem;
```

**For Apache**, check if SSL module is enabled:
```bash
sudo a2enmod ssl
sudo systemctl restart apache2
```

### Check 3: File Permissions

```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Check 4: Check Logs

```bash
# Laravel log
tail -f storage/logs/laravel.log

# Nginx error log
tail -f /var/log/nginx/error.log

# PHP-FPM log
tail -f /var/log/php8.2-fpm.log
```

---

## 📞 Emergency Rollback

If something breaks:

```bash
# Restore old .env
cp .env.backup .env

# Clear caches
php artisan config:clear
php artisan cache:clear

# Restart services
sudo systemctl restart php8.2-fpm nginx
```

---

## ✅ Success Indicators

- ✅ Browser console shows no errors
- ✅ All URLs use `https://`
- ✅ Floor plan placement works
- ✅ Green padlock in browser address bar
- ✅ No "Not Secure" warning

---

## 📋 Common Mistakes to Avoid

1. ❌ Forgetting to clear config cache after changing .env
2. ❌ Using `http://` instead of `https://` in APP_URL
3. ❌ Leaving `/public` in APP_URL
4. ❌ Not restarting PHP-FPM after changes
5. ❌ Having APP_DEBUG=true in production

---

## 🎯 One-Liner Fix (if you're confident)

```bash
sed -i 's|APP_URL=.*|APP_URL=https://poshan.my.id/pln|' .env && \
sed -i 's|APP_ENV=.*|APP_ENV=production|' .env && \
sed -i 's|APP_DEBUG=.*|APP_DEBUG=false|' .env && \
php artisan config:clear && \
php artisan config:cache && \
sudo systemctl restart php8.2-fpm nginx && \
echo "✅ Done! Test your application now."
```

---

**Time to fix:** ~5 minutes  
**Difficulty:** Easy  
**Risk:** Low (only changes configuration)

**Need help?** Check full documentation: `PRODUCTION_DEPLOYMENT_TROUBLESHOOTING.md`
