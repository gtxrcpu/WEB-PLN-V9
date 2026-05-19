# 🔄 QR Code Regeneration - Quick Fix

## ❌ Problem
Scan QR code menampilkan error:
```
403 Invalid signature
Failed to load resource: the server responded with a status of 403 (Forbidden)
```

## ✅ Solution

### Step 1: Clear All Caches
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### Step 2: Regenerate All QR Codes

**Option A: Via Browser (Easiest)**
1. Buka browser
2. Akses: `http://localhost/regenerate-all-qr`
3. Tunggu sampai muncul response JSON:
   ```json
   {
     "success": true,
     "message": "All QR codes regenerated successfully",
     "stats": {
       "apar": 10,
       "apat": 5,
       ...
     },
     "total": 50
   }
   ```

**Option B: Via Command Line**
```bash
# Windows PowerShell
Invoke-WebRequest -Uri "http://localhost/regenerate-all-qr"

# Linux/Mac
curl http://localhost/regenerate-all-qr
```

### Step 3: Test QR Code
1. Buka halaman equipment (misal: APAR list)
2. Klik "Lihat QR" pada salah satu item
3. Scan QR code dengan kamera HP
4. Seharusnya redirect ke: `http://localhost/scan/apar/16`
5. Tampil halaman detail equipment (tidak ada error 403)

### Step 4: Remove Temporary Route (IMPORTANT!)
Setelah regenerasi selesai, **hapus route temporary** di `routes/web.php`:

Cari dan hapus blok ini:
```php
// Temporary route for QR regeneration (remove after use)
Route::get('/regenerate-all-qr', function() {
    // ... (seluruh function)
});
```

Atau comment out:
```php
// Route::get('/regenerate-all-qr', function() { ... });
```

Lalu clear route cache lagi:
```bash
php artisan route:clear
```

## 🎯 Expected Results

### Before Regeneration
- ❌ QR URL: `http://localhost/scan/apar/16?signature=xxx`
- ❌ Error: 403 Invalid signature

### After Regeneration
- ✅ QR URL: `http://localhost/scan/apar/16`
- ✅ No signature parameter
- ✅ Direct access to equipment details

## 📝 Notes

### Why Regeneration Needed?
- Old QR codes contain **signed URLs** with signatures
- Signatures are environment-specific (localhost vs production)
- New QR codes use **simple URLs** without signatures
- Works across all environments

### How Many QR Codes?
Script regenerates QR for:
- ✅ APAR (Alat Pemadam Api Ringan)
- ✅ APAT (Alat Pemadam Api Tradisional)
- ✅ APAB (Alat Pemadam Api Berat)
- ✅ P3K (Kotak P3K)
- ✅ Fire Alarm
- ✅ Box Hydrant
- ✅ Rumah Pompa

### Performance
- Uses `chunk(50)` for memory efficiency
- Processes 50 items at a time
- Safe for large datasets (1000+ items)

## 🆘 Troubleshooting

### Still Getting 403 Error?
1. **Check if regeneration completed**
   - Look for JSON response with `"success": true`
   - Check `total` count matches your equipment count

2. **Clear browser cache**
   - Press Ctrl+Shift+Delete
   - Clear cached images and files

3. **Verify route is correct**
   ```bash
   php artisan route:list | grep equipment.status
   ```
   Should show: `GET|HEAD scan/{module}/{id}`
   Should NOT show: `middleware: signed`

4. **Check QR code content**
   - Right-click QR image → Inspect
   - Check `src` attribute
   - Should be: `data:image/svg+xml;base64,...`
   - Decode base64 and check URL inside
   - Should NOT contain `?signature=`

### Regeneration Takes Too Long?
- Normal for 100+ items (may take 30-60 seconds)
- Don't close browser/terminal
- Wait for JSON response

### Database Connection Error?
- Make sure Laravel server is running: `php artisan serve`
- Or use Docker: `./vendor/bin/sail up`
- Check `.env` database credentials

## ✅ Verification Checklist

After regeneration:
- [ ] JSON response shows `"success": true`
- [ ] Total count matches equipment count
- [ ] Test scan QR code with phone camera
- [ ] No 403 error
- [ ] Equipment details display correctly
- [ ] URL format: `/scan/{module}/{id}` (no signature)
- [ ] Temporary route removed from `routes/web.php`
- [ ] Route cache cleared

---

**Quick Command Summary:**
```bash
# 1. Clear caches
php artisan route:clear && php artisan config:clear && php artisan cache:clear

# 2. Regenerate (open in browser)
# http://localhost/regenerate-all-qr

# 3. Remove temporary route from routes/web.php

# 4. Clear route cache again
php artisan route:clear
```

**Done!** 🎉
