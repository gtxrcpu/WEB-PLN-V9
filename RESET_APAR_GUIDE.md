# Reset APAR Data - Per Unit Independent Counter

## Tujuan
Reset semua data APAR dan counter agar setiap unit memiliki serial number independen mulai dari 001.

## Struktur Serial Number Per Unit

### ✅ Yang Diinginkan:
- **INDUK**: APAR A1.001, A1.002, A1.003, ...
- **UP2WIII**: APAR A1.001, A1.002, A1.003, ...
- **UP2WIV**: APAR A1.001, A1.002, A1.003, ...

Setiap unit punya counter sendiri yang **tidak loncat** dan **independen**.

## Cara Reset Data APAR

### Option 1: Gunakan Seeder (RECOMMENDED)

```bash
# Jalankan seeder untuk reset
php artisan db:seed --class=ResetAparSeeder
```

**Seeder ini akan:**
- ✅ Hapus semua data APAR
- ✅ Hapus semua Kartu APAR
- ✅ Reset counter untuk Induk → 001
- ✅ Reset counter untuk setiap unit → 001

### Option 2: Manual via Tinker

```bash
php artisan tinker
```

Kemudian jalankan:
```php
// 1. Hapus semua APAR
App\Models\Apar::truncate();

// 2. Hapus semua Kartu APAR
DB::table('kartu_apars')->truncate();

// 3. Reset counter Induk
App\Models\AparSetting::set('apar_kode_counter_induk', 1);

// 4. Reset counter untuk setiap unit
$units = App\Models\Unit::all();
foreach ($units as $unit) {
    App\Models\AparSetting::set("apar_kode_counter_{$unit->id}", 1);
}

echo "✅ Reset Complete!\n";
```

### Option 3: Via Database GUI (phpMyAdmin/TablePlus)

```sql
-- 1. Truncate tables
TRUNCATE TABLE apars;
TRUNCATE TABLE kartu_apars;

-- 2. Reset settings (check unit IDs first)
-- Assuming unit_id: 1=INDUK, 2=UP2WIII, 3=UP2WIV

-- Reset Induk
UPDATE apar_settings SET value = '1' WHERE setting_key = 'apar_kode_counter_induk';

-- Reset UP2WIII (adjust unit_id as needed)
UPDATE apar_settings SET value = '1' WHERE setting_key = 'apar_kode_counter_1';

-- Reset UP2WIV (adjust unit_id as needed)
UPDATE apar_settings SET value = '1' WHERE setting_key = 'apar_kode_counter_2';

-- Or delete all counter settings to start fresh
DELETE FROM apar_settings WHERE setting_key LIKE 'apar_kode_counter_%';
```

## Verifikasi Setelah Reset

### Cek Counter Settings:
```bash
php artisan tinker
```

```php
// Lihat semua counter
$settings = App\Models\AparSetting::where('setting_key', 'LIKE', 'apar_kode_counter_%')->get();
foreach ($settings as $setting) {
    echo "{$setting->setting_key}: {$setting->value}\n";
}
```

### Expected Output:
```
apar_kode_counter_induk: 1
apar_kode_counter_1: 1
apar_kode_counter_2: 1
apar_kode_counter_3: 1
```

## Testing Per-Unit Serial Generation

### Test 1: Tambah APAR untuk INDUK
1. Login sebagai user dengan `unit_id = null` atau Induk
2. Tambah APAR
3. Serial harus: **A1.001**

### Test 2: Tambah APAR untuk UP2WIII
1. Login sebagai user UP2WIII (`unit_id = 1`)
2. Tambah APAR
3. Serial harus: **A1.001** (independent from Induk)

### Test 3: Tambah APAR untuk UP2WIV
1. Login sebagai user UP2WIV (`unit_id = 2`)
2. Tambah APAR
3. Serial harus: **A1.001** (independent from others)

## Logic yang Sudah Diperbaiki

File `app/Models/Apar.php` - method `generateNextSerial()` sudah:

✅ **Per-unit counter**
```php
$counterKey = $unitId ? "apar_kode_counter_{$unitId}" : "apar_kode_counter_induk";
```

✅ **Auto-sync dengan database**
```php
$query = self::query();
if ($unitId) {
    $query->where('unit_id', $unitId);
} else {
    $query->whereNull('unit_id');
}
```

✅ **Duplicate prevention**
```php
$exists = self::where('serial_no', $serial)->orWhere('barcode', $serial)->exists();
```

## Troubleshooting

### Jika masih ada duplicate:
```bash
# Re-run reset seeder
php artisan db:seed --class=ResetAparSeeder
```

### Jika counter loncat:
- Pastikan tidak ada data APAR yang dihapus manual
- Counter akan auto-sync dengan data tertinggi di database
- Gunakan seeder untuk clean reset

## Notes

⚠️ **PERINGATAN**: Reset akan **menghapus SEMUA data APAR dan Kartu APAR**!

✅ **Backup dulu** jika ada data penting:
```bash
# Export data
php artisan db:dump
# atau
mysqldump -u user -p database_name > backup.sql
```

---

**Setelah reset, setiap unit akan punya serial independen tanpa loncat!** 🎉
