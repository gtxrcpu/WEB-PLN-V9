# Quick Fix - Serial Number Format

## Problem
Serial number menampilkan `H6.001` tanpa unit code.

## Solution

Jalankan script ini untuk setup konfigurasi:

```bash
php setup_equipment_settings.php
```

Script ini akan:
- Membuat konfigurasi format dan counter untuk semua equipment types
- Per unit (INDUK, UP2WI, UP2WII, dll)
- Set counter awal ke 1

## Verify

Setelah menjalankan script:

1. Login sebagai user UP2WI
2. Buka http://localhost/box-hydrant/create
3. Serial number harus menampilkan: `H6-UP2WI-001` ✅

## Alternative

Jika script tidak bisa dijalankan, gunakan seeder:

```bash
php artisan db:seed --class=EquipmentSettingsSeeder
```

## What Was Fixed

✅ Box Hydrant model - sekarang menggunakan `AparSetting::getByUnit()`
✅ Rumah Pompa model - sekarang menggunakan `AparSetting::getByUnit()`
✅ APAB model - sekarang menggunakan `AparSetting::getByUnit()`
✅ Created seeder untuk setup konfigurasi
✅ Created migration untuk setup konfigurasi

Semua equipment sekarang konsisten dengan APAR dalam menggunakan konfigurasi per unit.
