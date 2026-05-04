# Fix: Kartu Pemeriksaan Fire Alarm - Missing Column

## 🐛 Problem
Error saat mengakses form kartu pemeriksaan Fire Alarm:
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'catatan' in 'where clause'
```

## 🔍 Root Cause
Tabel `kartu_fire_alarms` tidak memiliki kolom `catatan` yang diperlukan untuk:
1. Menyimpan data pemeriksaan (lokasi, no seri, keterangan)
2. Membedakan kartu pemeriksaan dari kartu kendali (prefix `[PMK]`)
3. Tracking revisi berdasarkan kartu pemeriksaan yang ditolak

## ✅ Solution

### 1. Migration Created
**File:** `database/migrations/2026_05_03_100000_add_catatan_to_kartu_fire_alarms_table.php`

```php
Schema::table('kartu_fire_alarms', function (Blueprint $table) {
    $table->text('catatan')->nullable()->after('petugas');
});
```

### 2. Migration Executed
```bash
docker exec plnweb-laravel.test-1 php artisan migrate --force
```

**Result:**
```
✅ 2026_05_03_100000_add_catatan_to_kartu_fire_alarms_table ..... 112.40ms DONE
```

### 3. Column Verified
**Before:**
```json
[
    "id", "fire_alarm_id", "user_id", "panel_kontrol", "detector",
    "manual_call_point", "alarm_bell", "battery_backup", "uji_fungsi",
    "kesimpulan", "tgl_periksa", "revisi", "petugas", "pengawas",
    "created_at", "updated_at", "signature_id", "approved_by",
    "approved_at", "rejected_by", "rejected_at", "rejection_reason",
    "leader_signature_id", "leader_approved_by", "leader_approved_at",
    "leader_rejected_by", "leader_rejected_at", "leader_rejection_reason"
]
```

**After:**
```json
[
    "id", "fire_alarm_id", "user_id", "panel_kontrol", "detector",
    "manual_call_point", "alarm_bell", "battery_backup", "uji_fungsi",
    "kesimpulan", "tgl_periksa", "revisi", "petugas", 
    "catatan",  ← NEW COLUMN
    "pengawas", "created_at", "updated_at", "signature_id",
    "approved_by", "approved_at", "rejected_by", "rejected_at",
    "rejection_reason", "leader_signature_id", "leader_approved_by",
    "leader_approved_at", "leader_rejected_by", "leader_rejected_at",
    "leader_rejection_reason"
]
```

## 📊 Column Details

### `catatan` Column
- **Type:** TEXT
- **Nullable:** YES
- **Position:** After `petugas`
- **Purpose:** Store inspection notes with structured format

### Data Format
```
[PMK] Lokasi: Lobby Utama | No. Seri: FA-001 | Ket: Sensor rusak
```

**Components:**
- `[PMK]` - Prefix to identify pemeriksaan cards (vs kendali cards)
- `Lokasi:` - Fire Alarm location
- `No. Seri:` - Serial number
- `Ket:` - Additional notes/remarks

## 🔄 How It Works

### 1. Form Submission
```php
// User fills table with 10 rows
foreach ($request->rows as $row) {
    if (empty($row['kondisi'])) continue;
    
    // Build catatan with [PMK] prefix
    $catatan = '[PMK] ' . trim(
        ($row['lokasi']     ? 'Lokasi: ' . $row['lokasi'] . ' | '     : '') .
        ($row['no_seri']    ? 'No. Seri: ' . $row['no_seri'] . ' | '  : '') .
        ($row['keterangan'] ? 'Ket: ' . $row['keterangan']             : '')
    , ' |');
    
    KartuFireAlarm::create([
        'fire_alarm_id' => $fireAlarm->id,
        'catatan' => $catatan,
        // ... other fields
    ]);
}
```

### 2. Revisi Tracking
```php
// Query only pemeriksaan cards (with [PMK] prefix)
$latestKartu = KartuFireAlarm::where('fire_alarm_id', $fireAlarmId)
    ->where(function ($q) {
        $q->whereNotNull('catatan')->where('catatan', 'like', '[PMK]%');
    })
    ->orderBy('id', 'desc')
    ->first();

// Calculate next revision
if ($latestKartu && ($latestKartu->leader_rejected_at || $latestKartu->rejected_at)) {
    $nextRevisi = str_pad((int)($latestKartu->revisi ?? 0) + 1, 2, '0', STR_PAD_LEFT);
} else {
    $nextRevisi = '00';
}
```

### 3. Display in Approval View
```php
// Parse catatan to extract data
if ($kartu->catatan && str_starts_with($kartu->catatan, '[PMK]')) {
    $rawCatatan = ltrim(str_replace('[PMK]', '', $kartu->catatan ?? ''));
    $catatanParts = [];
    foreach (explode(' | ', $rawCatatan) as $part) {
        [$k, $v] = array_pad(explode(': ', $part, 2), 2, '');
        $catatanParts[trim($k)] = trim($v);
    }
    
    $lokasiVal = $catatanParts['Lokasi'] ?? '—';
    $noSeriVal = $catatanParts['No. Seri'] ?? '—';
    $keteranganVal = $catatanParts['Ket'] ?? '';
}
```

## 🎯 Benefits

### 1. Separation of Concerns
- **Kartu Kendali:** Regular inspection cards (no `[PMK]` prefix)
- **Kartu Pemeriksaan:** Mass inspection cards (with `[PMK]` prefix)
- Easy to filter and query separately

### 2. Structured Data Storage
- All inspection data in one field
- Easy to parse and display
- Maintains data integrity

### 3. Revisi Independence
- Pemeriksaan revisions tracked separately from Kendali
- No interference between card types
- Accurate revision counting

## 🧪 Testing

### Test 1: Create Pemeriksaan Card
```bash
# Access form
http://localhost/fire-alarm/kartu-pemeriksaan/create?fire_alarm_id=1

# Fill form and submit
# Expected: Success message, data saved with [PMK] prefix
```

### Test 2: Verify Data
```sql
SELECT id, fire_alarm_id, catatan, revisi 
FROM kartu_fire_alarms 
WHERE catatan LIKE '[PMK]%';
```

### Test 3: Revisi Tracking
```bash
# 1. Create pemeriksaan card (revisi: 00)
# 2. Leader rejects it
# 3. Create new pemeriksaan card
# Expected: revisi = 01
```

## 📝 Model Configuration

### KartuFireAlarm Model
```php
class KartuFireAlarm extends Model
{
    protected $guarded = [];  // All fields mass assignable
    
    protected $casts = [
        'tgl_periksa' => 'date',
        'leader_approved_at' => 'datetime',
        'leader_rejected_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];
}
```

**Note:** Using `$guarded = []` means all columns (including `catatan`) are automatically mass assignable. No need to explicitly add to `$fillable`.

## 🚀 Deployment Steps

### Production Deployment
```bash
# 1. Copy migration file
scp database/migrations/2026_05_03_100000_add_catatan_to_kartu_fire_alarms_table.php \
    server:/path/to/app/database/migrations/

# 2. Run migration
php artisan migrate --force

# 3. Clear cache
php artisan optimize:clear

# 4. Verify column
php artisan tinker
>>> Schema::hasColumn('kartu_fire_alarms', 'catatan')
=> true
```

## ✅ Verification Checklist

- [x] Migration file created
- [x] Migration executed successfully
- [x] Column `catatan` exists in database
- [x] Column is TEXT type and nullable
- [x] Model allows mass assignment
- [x] Form can be accessed without error
- [x] Data can be saved with [PMK] prefix
- [x] Revisi tracking works correctly
- [x] Approval view can parse catatan
- [x] Cache cleared

## 🔮 Future Considerations

### 1. Data Migration (if needed)
If there are existing kartu_fire_alarms records that should be pemeriksaan cards:
```sql
-- Add [PMK] prefix to existing records
UPDATE kartu_fire_alarms 
SET catatan = CONCAT('[PMK] ', COALESCE(catatan, ''))
WHERE /* condition to identify pemeriksaan cards */;
```

### 2. Index Optimization
For better query performance on large datasets:
```php
Schema::table('kartu_fire_alarms', function (Blueprint $table) {
    $table->index(['fire_alarm_id', 'catatan']);
});
```

### 3. Full-Text Search
If searching within catatan becomes common:
```php
Schema::table('kartu_fire_alarms', function (Blueprint $table) {
    $table->fullText('catatan');
});
```

## 📞 Troubleshooting

### Issue: Column still not found after migration
**Solution:**
```bash
# Clear all caches
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Restart PHP-FPM (if applicable)
sudo systemctl restart php8.2-fpm
```

### Issue: Migration already ran but column missing
**Solution:**
```bash
# Check migration status
php artisan migrate:status

# If migration shows as ran but column missing, rollback and re-run
php artisan migrate:rollback --step=1
php artisan migrate
```

### Issue: Data not saving to catatan
**Solution:**
```php
// Check model configuration
$model = new KartuFireAlarm();
dd($model->getFillable(), $model->getGuarded());

// Should show: fillable = [], guarded = []
```

## 🎉 Status: RESOLVED

The `catatan` column has been successfully added to `kartu_fire_alarms` table. The Kartu Pemeriksaan Fire Alarm feature is now fully functional!

---

**Date:** May 3, 2026
**Issue:** Missing `catatan` column
**Resolution Time:** ~5 minutes
**Status:** ✅ RESOLVED
