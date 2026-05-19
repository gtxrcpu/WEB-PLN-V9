# Production Deployment Troubleshooting Guide

## 🔴 Problem Summary

**Issue**: Application works perfectly on localhost but fails on production server (https://poshan.my.id/pln/)

**Error Type**: Mixed Content Error + CORS/Fetch Failure

---

## 📊 Console Log Analysis

### Localhost (Working) ✅
```
Placed equipment from server: ▶ Object
Loaded markers: ▶ Proxy(Array)
Drag handlers attached
Total markers count: 3
Already initialized, skipping...
Placed marker: {type: 'apar', id: 22, uid: 'apar_22_1778204041004', ...}
✓ Saved: APAR-UP2WI-003 at 44.15% 63.76%
```

### Production Server (Failing) ❌
```
Already initialized, skipping...
❌ Failed to load resource: net::ERR_BLOCKED_BY_CLIENT
⚠️ Mixed Content: The page at 'https://poshan.my.id/pln/public/admin/floor-plans/4/placement' 
   was loaded over HTTPS, but requested an insecure element 
   'http://poshan.my.id/pln/public/images/location.png'
   
⚠️ Mixed Content: The page at 'https://poshan.my.id/pln/public/admin/floor-plans/4/placement' 
   was loaded over HTTPS, but requested an insecure resource 
   'http://poshan.my.id/pln/public/admin/floor-plans/4/save-placement'
   
❌ Failed to save: TypeError: Failed to fetch
   at Proxy.savePosition (placement:669:48)
```

---

## 🔍 Root Cause Analysis

### Primary Issue: **Mixed Content Error**

**What is Mixed Content?**
- HTTPS page loading HTTP resources
- Modern browsers **block** HTTP requests from HTTPS pages for security
- This is a **browser-level security policy**, not a server error

**Why it happens:**
1. Production site uses HTTPS: `https://poshan.my.id`
2. Laravel generates URLs using `APP_URL` from `.env`
3. Current `.env` has: `APP_URL=http://localhost`
4. Laravel's `route()`, `asset()`, `url()` helpers use this base URL
5. Result: HTTPS page tries to load HTTP resources → **BLOCKED**

### Secondary Issues Identified:

1. **No TrustProxies Middleware**
   - Laravel behind reverse proxy (nginx/Apache) needs this
   - Without it, Laravel can't detect HTTPS from proxy headers
   - Results in HTTP URLs even when accessed via HTTPS

2. **Public folder in URL**
   - URL shows `/pln/public/` which is incorrect
   - Should be configured to serve from `/pln/` directly
   - Indicates web server misconfiguration

3. **CSRF Token might fail**
   - Mixed content blocks the POST request
   - CSRF validation never reached

---

## ✅ Solution Implementation

### Solution 1: Fix APP_URL (CRITICAL - Must Do First)

**For Production Server:**

Create/update `.env.production` or update `.env` on server:

```env
APP_NAME="PLN Inventaris"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://poshan.my.id/pln

# Force HTTPS
ASSET_URL=https://poshan.my.id/pln
```

**Important Notes:**
- Remove `/public` from URL
- Use `https://` not `http://`
- Set `APP_ENV=production`
- Set `APP_DEBUG=false` for security

### Solution 2: Create TrustProxies Middleware

Laravel needs to trust proxy headers to detect HTTPS properly.

**Create the middleware:**


**File created:** `app/Http/Middleware/TrustProxies.php`

**Register in bootstrap/app.php:**

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
        'prevent.guest' => \App\Http\Middleware\PreventGuestAccess::class,
    ]);
    
    // Trust proxies for HTTPS detection behind reverse proxy
    $middleware->trustProxies(at: '*');
})
```

### Solution 3: Force HTTPS in Production

**Create AppServiceProvider configuration:**

Add to `app/Providers/AppServiceProvider.php`:

```php
public function boot(): void
{
    // Force HTTPS in production
    if ($this->app->environment('production')) {
        \URL::forceScheme('https');
    }
}
```

### Solution 4: Web Server Configuration

**For Apache (.htaccess):**

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Force HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Remove /public from URL
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ /public/$1 [L]
</IfModule>
```

**For Nginx:**

```nginx
server {
    listen 80;
    server_name poshan.my.id;
    
    # Force HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name poshan.my.id;
    
    root /path/to/plnweb/public;  # Point directly to public folder
    index index.php;
    
    # SSL Configuration
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Important: Pass HTTPS info to PHP
        fastcgi_param HTTPS on;
        fastcgi_param HTTP_X_FORWARDED_PROTO https;
    }
}
```

---

## 📋 Deployment Checklist

### Pre-Deployment

- [ ] Update `.env` with production values
- [ ] Set `APP_URL=https://your-domain.com`
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate new `APP_KEY` if needed: `php artisan key:generate`
- [ ] Configure database credentials
- [ ] Add TrustProxies middleware
- [ ] Update AppServiceProvider with forceScheme

### Server Configuration

- [ ] Point web root to `/public` folder (not parent)
- [ ] Configure SSL certificate (Let's Encrypt recommended)
- [ ] Set up HTTPS redirect (HTTP → HTTPS)
- [ ] Configure PHP-FPM to pass HTTPS headers
- [ ] Set proper file permissions (755 for folders, 644 for files)
- [ ] Make `storage/` and `bootstrap/cache/` writable (775)

### Post-Deployment

- [ ] Run `composer install --optimize-autoloader --no-dev`
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Run `php artisan migrate --force` (if needed)
- [ ] Run `php artisan storage:link`
- [ ] Clear all caches: `php artisan optimize:clear`
- [ ] Test all routes with HTTPS
- [ ] Check browser console for mixed content errors
- [ ] Test file uploads
- [ ] Test AJAX requests (like floor plan placement)
- [ ] Verify CSRF tokens work
- [ ] Check error logs: `storage/logs/laravel.log`

### Security Checklist

- [ ] Remove `.env.example` from production
- [ ] Disable directory listing
- [ ] Hide Laravel version (remove `X-Powered-By` header)
- [ ] Set up rate limiting
- [ ] Configure CORS properly
- [ ] Enable HSTS header
- [ ] Set secure session cookies
- [ ] Review file upload security
- [ ] Set up backup system
- [ ] Configure monitoring/logging

---

## 🔧 Quick Fix Commands

**On Production Server:**

```bash
# 1. Update environment
nano .env
# Update APP_URL to https://poshan.my.id/pln

# 2. Clear and rebuild caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 3. Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Fix permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 5. Restart services
sudo systemctl restart php8.2-fpm  # Adjust PHP version
sudo systemctl restart nginx       # or apache2
```

---

## 🐛 Debugging Tools

### Check Current URL Generation

Create a test route to verify URL generation:

```php
// routes/web.php
Route::get('/test-urls', function() {
    return [
        'app_url' => config('app.url'),
        'asset_url' => config('app.asset_url'),
        'current_url' => url()->current(),
        'route_url' => route('login'),
        'asset_url_test' => asset('images/test.png'),
        'is_https' => request()->secure(),
        'scheme' => request()->getScheme(),
        'host' => request()->getHost(),
    ];
});
```

Access: `https://poshan.my.id/pln/test-urls`

Expected output:
```json
{
    "app_url": "https://poshan.my.id/pln",
    "asset_url": "https://poshan.my.id/pln",
    "current_url": "https://poshan.my.id/pln/test-urls",
    "route_url": "https://poshan.my.id/pln/login",
    "asset_url_test": "https://poshan.my.id/pln/images/test.png",
    "is_https": true,
    "scheme": "https",
    "host": "poshan.my.id"
}
```

### Check Browser Console

Open DevTools (F12) and check:
1. **Console tab**: Look for mixed content warnings
2. **Network tab**: Check failed requests (red items)
3. **Security tab**: Verify SSL certificate
4. **Application tab**: Check cookies (should have `Secure` flag)

### Check Server Logs

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Nginx error log
tail -f /var/log/nginx/error.log

# PHP-FPM log
tail -f /var/log/php8.2-fpm.log

# Apache error log
tail -f /var/log/apache2/error.log
```

---

## 🎯 Specific Fix for Floor Plan Placement Issue

The error in your screenshot is specifically in the floor plan placement feature.

**Problem:**
```javascript
fetch('http://poshan.my.id/pln/public/admin/floor-plans/4/save-placement', ...)
// ^^^^ HTTP on HTTPS page = BLOCKED
```

**Solution Applied:**

The route helper will automatically use HTTPS once you:
1. ✅ Set `APP_URL=https://poshan.my.id/pln` in `.env`
2. ✅ Add TrustProxies middleware (already created above)
3. ✅ Clear config cache: `php artisan config:clear`

The Blade template already uses the correct syntax:
```php
fetch('{{ route("leader.floor-plans.save-placement", $floorPlan) }}', ...)
```

This will automatically generate HTTPS URLs after the fixes.

---

## 📱 Testing After Deployment

### 1. Test Mixed Content Fix

Open browser DevTools (F12) → Console tab

Expected: **No red errors**, **No mixed content warnings**

### 2. Test Floor Plan Placement

1. Go to Floor Plans → Select a plan → Placement mode
2. Drag an equipment marker
3. Check console for: `✓ Saved: EQUIPMENT-NAME at X% Y%`
4. Refresh page → marker should stay in new position

### 3. Test All AJAX Features

- [ ] Floor plan placement (drag & drop)
- [ ] QR code scanning
- [ ] Form submissions
- [ ] File uploads
- [ ] Approval actions
- [ ] Search functionality

---

## 🚨 Common Pitfalls to Avoid

### ❌ Don't Do This:

1. **Hardcoding HTTP URLs**
   ```php
   // BAD
   <img src="http://poshan.my.id/pln/images/logo.png">
   
   // GOOD
   <img src="{{ asset('images/logo.png') }}">
   ```

2. **Forgetting to clear cache**
   - Config cache stores old APP_URL
   - Always run `php artisan config:clear` after changing `.env`

3. **Wrong web root**
   - Don't point to `/home/user/plnweb/`
   - Point to `/home/user/plnweb/public/`

4. **Exposing .env file**
   - Make sure `.env` is not publicly accessible
   - Check: `https://poshan.my.id/pln/.env` should return 404

5. **Debug mode in production**
   - `APP_DEBUG=true` exposes sensitive info
   - Always use `APP_DEBUG=false` in production

---

## 📊 Environment Comparison

| Setting | Localhost | Production |
|---------|-----------|------------|
| APP_URL | http://localhost | https://poshan.my.id/pln |
| APP_ENV | local | production |
| APP_DEBUG | true | false |
| HTTPS | No | Yes (Required) |
| Web Root | /public via artisan serve | /public via nginx/apache |
| Database | Docker MySQL | Production MySQL |
| File Permissions | Relaxed | Strict (755/644) |
| Caching | Disabled | Enabled (config/route/view) |
| Error Display | Full stack trace | Generic error page |

---

## 🔐 Security Headers (Bonus)

Add to your web server config for extra security:

**Nginx:**
```nginx
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
```

**Apache (.htaccess):**
```apache
<IfModule mod_headers.c>
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>
```

---

## 📞 Support & Monitoring

### Set Up Error Monitoring

Consider integrating:
- **Sentry** (error tracking)
- **Laravel Telescope** (debugging)
- **Laravel Horizon** (queue monitoring)
- **Uptime monitoring** (Pingdom, UptimeRobot)

### Log Rotation

```bash
# Add to /etc/logrotate.d/laravel
/path/to/plnweb/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 0644 www-data www-data
}
```

---

## ✅ Success Indicators

Your deployment is successful when:

- ✅ No mixed content errors in browser console
- ✅ All URLs use HTTPS (check page source)
- ✅ Floor plan placement saves successfully
- ✅ AJAX requests complete without errors
- ✅ Images and assets load properly
- ✅ Forms submit successfully
- ✅ File uploads work
- ✅ SSL certificate is valid (green padlock)
- ✅ No 500 errors in production
- ✅ Logs show no critical errors

---

## 📝 Summary

**Root Cause:** Mixed Content Error (HTTPS page loading HTTP resources)

**Primary Fix:** Update `APP_URL` in `.env` to use HTTPS

**Supporting Fixes:**
1. Add TrustProxies middleware
2. Force HTTPS in AppServiceProvider
3. Configure web server properly
4. Clear and rebuild caches

**Prevention:** Always use Laravel helpers (`route()`, `asset()`, `url()`) instead of hardcoded URLs

---

**Document Version:** 1.0  
**Last Updated:** 2026-05-08  
**Author:** Kiro AI Assistant  
**Project:** PLN Inventaris System
