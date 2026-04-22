# Fix Serial Number Format - Box Hydrant, Rumah Pompa, APAB

## Problem
Serial numbers showing without unit code, contoh:
- Actual: `H6.001` ❌
- Expected: `H6-UP2WI-001` ✅

## Root Cause
Konfigurasi format dan counter untuk equipment belum ada di database. Setiap unit harus punya konfigurasi sendiri untuk generate serial number.

## Solution

### Step 1: Run Equipment Settings Seeder

Jalankan seeder untuk membuat konfigurasi default untuk semua equipment types:

```bash
php artisan db:seed --class=EquipmentSettingsSeeder
```

Seeder ini akan:
1. Membaca semua unit dari database (INDUK, UP2WI, UP2WII, dll)
2. Membuat konfigurasi format dan counter untuk setiap equipment type per unit
3. Set counter awal ke 1 untuk setiap unit

### Step 2: Verify Settings Created

Cek di database bahwa settings sudah dibuat:

```sql
SELECT * FROM apar_settings 
WHERE key LIKE '%box_hydrant%' 
   OR key LIKE '%rumah_pompa%' 
   OR key LIKE '%apab%'
ORDER BY unit_id, key;
```

Expected output untuk setiap unit:
- `box_hydrant_kode_format` = `H6-{UNIT}-{NNN}`
- `box_hydrant_kode_counter` = `1`
- `rumah_pompa_kode_format` = `RUMAHPOMPA-{UNIT}-{NNN}`
- `rumah_pompa_kode_counter` = `1`
- `apab_kode_format` = `APAB-{UNIT}-{NNN}`
- `apab_kode_counter` = `1`

### Step 3: Test Serial Number Generation

1. Login sebagai user dari unit tertentu (misal UP2WI)
2. Buka halaman create:
   - Box Hydrant: http://localhost/box-hydrant/create
   - Rumah Pompa: http://localhost/rumah-pompa/create
   - APAB: http://localhost/apab/create
3. Cek serial number preview, harus menampilkan format dengan unit code:
   - Box Hydrant: `H6-UP2WI-001`
   - Rumah Pompa: `RUMAHPOMPA-UP2WI-001`
   - APAB: `APAB-UP2WI-001`

## What Changed

### Code Changes

Updated 3 models to use `AparSetting::getByUnit()` and `AparSetting::setByUnit()`:

1. **app/Models/BoxHydrant.php**
   - Changed from: `AparSetting::get('box_hydrant_kode_format')`
   - Changed to: `AparSetting::getByUnit('box_hydrant_kode_format', $unitId)`

2. **app/Models/RumahPompa.php**
   - Changed from: `AparSetting::get('rumah_pompa_kode_format')`
   - Changed to: `AparSetting::getByUnit('rumah_pompa_kode_format', $unitId)`

3. **app/Models/Apab.php**
   - Changed from: Hardcoded format
   - Changed to: `AparSetting::getByUnit('apab_kode_format', $unitId)`

### Database Changes

Created seeder: `EquipmentSettingsSeeder.php`
- Creates format and counter settings for all equipment types
- Per unit configuration (independent counters)
- Supports 7 equipment types: APAR, APAT, APAB, Fire Alarm, Box Hydrant, Rumah Pompa, P3K

## Format Placeholders

Available placeholders in format string:
- `{UNIT}` - Unit code (UP2WI, UP2WII, INDUK, etc)
- `{YYYY}` - 4-digit year (2026)
- `{YY}` - 2-digit year (26)
- `{MM}` - 2-digit month (04)
- `{NNN}` - 3-digit counter with leading zeros (001, 002, ...)
- `{NNNN}` - 4-digit counter with leading zeros (0001, 0002, ...)

## Customizing Format

Untuk mengubah format serial number per unit, edit di admin panel atau langsung di database:

```sql
-- Contoh: Ubah format Box Hydrant untuk unit UP2WI
UPDATE apar_settings 
SET value = 'BH-{UNIT}-{YYYY}-{NNN}' 
WHERE key = 'box_hydrant_kode_format' 
  AND unit_id = (SELECT id FROM units WHERE code = 'UP2WI');
```

Hasil: `BH-UP2WI-2026-001`

## Resetting Counter

Untuk reset counter ke 1:

```sql
-- Reset counter Box Hydrant untuk unit UP2WI
UPDATE apar_settings 
SET value = '1' 
WHERE key = 'box_hydrant_kode_counter' 
  AND unit_id = (SELECT id FROM units WHERE code = 'UP2WI');
```

## Troubleshooting

### Serial masih tanpa unit code

1. Cek apakah seeder sudah dijalankan:
   ```bash
   php artisan db:seed --class=EquipmentSettingsSeeder
   ```

2. Cek apakah user punya unit_id:
   ```sql
   SELECT id, name, email, unit_id FROM users WHERE email = 'your@email.com';
   ```

3. Cek apakah settings ada untuk unit tersebut:
   ```sql
   SELECT * FROM apar_settings 
   WHERE unit_id = <your_unit_id> 
     AND key LIKE '%box_hydrant%';
   ```

### Format tidak sesuai

Edit format di database atau tunggu admin panel untuk edit kode settings.

## Summary

✅ Box Hydrant, Rumah Pompa, dan APAB sekarang menggunakan sistem konfigurasi yang sama dengan APAR
✅ Setiap unit punya format dan counter independen
✅ Serial number akan menampilkan unit code dengan benar
✅ Counter per unit dimulai dari 001

Jalankan seeder dan test!
