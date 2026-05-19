# QR Code Fix - Production Deployment Guide

## 🔴 Problem
QR codes generated on localhost show "403 INVALID SIGNATURE" error when scanned on production server.

**Root Cause:**
- QR codes used `signedRoute()` which embeds `APP_URL` into the signature
- Localhost QR: `http://localhost/scan/apar/16?signature=xxx`
- Production scan: `https://poshan.my.id/pln/public/scan/apar/16?signature=xxx`
- Different URLs = Invalid signature

## ✅ Solution Applied

### 1. Removed Signed Route Middleware
**File:** `routes/web.php`

```php
// BEFORE (with signature validation)
Route::get('/scan/{module}/{id}', [EquipmentStatusController::class, 'show'])
    ->name('equipment.status')
    ->middleware('signed');  // ❌ This caused the problem

// AFTER (public access, no signature)
Route::get('/scan/{module}/{id}', [EquipmentStatusController::class, 'show'])
    ->name('equipment.status');  // ✅ Works everywhere
```

### 2. Updated All Equipment Models
Changed from `signedRoute()` to regular `route()` in:
- ✅ `app/Models/Apar.php`
- ✅ `app/Models/Apat.php`
- ✅ `app/Models/Apab.php`
- ✅ `app/Models/P3k.php`
- ✅ `app/Models/FireAlarm.php`
- ✅ `app/Models/BoxHydrant.php`
- ✅ `app/Models/RumahPompa.php`

**Before:**
```php
$url = \Illuminate\Support\Facades\URL::signedRoute('equipment.status', [
    'module' => 'apar', 
    'id' => $this->id
]);
```

**After:**
```php
$url = route('equipment.status', [
    'module' => 'apar', 
    'id' => $this->id
]);
```

## 🚀 Deployment Steps

### Step 1: Update Code on Production
```bash
# Pull latest code
git pull origin main

# Or upload files manually if not using git
```

### Step 2: Clear Caches
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### Step 3: Regenerate ALL QR Codes (IMPORTANT!)
Old QR codes still have signatures in them. You need to regenerate:

**Option A: Via Admin Panel (Recommended)**
1. Login as admin/superadmin
2. Go to: Admin → QR Regeneration
3. Click "Regenerate All QR Codes"
4. Wait for completion

**Option B: Via Artisan Command (if available)**
```bash
php artisan qr:regenerate --all
```

**Option C: Manual Regeneration**
```bash
# Run this PHP script
php artisan tinker

# Then run:
\App\Models\Apar::chunk(100, function($apars) {
    foreach($apars as $apar) {
        $apar->generateQrSvg(true);
    }
});

\App\Models\Apat::chunk(100, function($apats) {
    foreach($apats as $apat) {
        $apat->generateQrSvg(true);
    }
});

// Repeat for all equipment types...
```

### Step 4: Test QR Scanning
1. Print a new QR code from production
2. Scan with phone camera
3. Should redirect to: `https://poshan.my.id/pln/scan/apar/16` (no `/public/`, no signature)
4. Should show equipment details (no 403 error)

## 🎯 Expected Results

### Before Fix
- ❌ URL: `https://poshan.my.id/pln/public/scan/apar/16?signature=xxx`
- ❌ Error: 403 INVALID SIGNATURE
- ❌ QR codes from localhost don't work on production

### After Fix
- ✅ URL: `https://poshan.my.id/pln/scan/apar/16`
- ✅ No signature parameter
- ✅ Works from any environment (localhost, staging, production)
- ✅ No `/public/` in URL (if web server configured correctly)

## 🔒 Security Considerations

**Q: Is it safe to remove signature validation?**

**A: Yes, for this use case:**
- QR codes are meant for **public access** (anyone can scan)
- Equipment status is **read-only** information
- No sensitive data exposed (just serial number, status, location)
- No write operations possible from scan endpoint

**If you need security:**
- Add authentication middleware instead: `->middleware('auth')`
- Or add rate limiting: `->middleware('throttle:60,1')`
- Or add IP whitelist for internal network only

## 📝 Additional Notes

### Why `/public/` appears in URL?
This is a **web server configuration issue**, not related to QR codes.

**Fix for Apache (.htaccess):**
```apache
RewriteEngine On
RewriteCond %{REQUEST_URI} !^/public/
RewriteRule ^(.*)$ /public/$1 [L]
```

**Fix for Nginx:**
```nginx
root /path/to/plnweb/public;  # Point directly to public folder
```

### Regenerating QR Codes
After deployment, **all existing QR codes must be regenerated** because:
1. Old QR codes contain signed URLs with signatures
2. Signatures are no longer validated (route changed)
3. New QR codes use simple URLs without signatures

**Important:** Print new QR code stickers for all equipment!

## ✅ Verification Checklist

- [ ] Code updated on production server
- [ ] Route cache cleared
- [ ] All QR codes regenerated
- [ ] Test scan with phone camera works
- [ ] No 403 errors
- [ ] URL doesn't contain `/public/`
- [ ] URL doesn't contain `?signature=`
- [ ] Equipment details display correctly

## 🆘 Troubleshooting

### Still getting 403 error?
1. Check if route cache is cleared: `php artisan route:clear`
2. Check if QR code is regenerated (should not have `?signature=` in URL)
3. Check web server logs: `tail -f /var/log/nginx/error.log`

### QR code shows old URL with signature?
- QR code not regenerated yet
- Run regeneration script again
- Check `qr_svg_path` in database

### URL still contains `/public/`?
- This is web server configuration issue
- Update Apache `.htaccess` or Nginx config
- Point web root to `/public` folder directly

---

**Document Version:** 1.0  
**Date:** 2026-05-08  
**Fixed By:** Kiro AI Assistant
