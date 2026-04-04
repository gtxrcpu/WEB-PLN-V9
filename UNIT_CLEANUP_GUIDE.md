# Unit Cleanup Guide

## Masalah yang Ditemukan

Berdasarkan screenshot yang diberikan, ditemukan duplikasi data pada unit:
- Total 7 unit yang seharusnya unik
- Beberapa unit muncul lebih dari sekali (UP2WIII, UP2WIV muncul 2x)
- Unit yang seharusnya ada: INDUK, UP2WI, UP2WII, UP2WIII, UP2WIV, UP2WV, UP2WVI

## Solusi yang Diimplementasikan

### 1. Migration untuk Cleanup Data
File: `database/migrations/2026_04_01_123324_fix_duplicate_units_data.php`

**Fitur:**
- Identifikasi dan log semua unit sebelum cleanup
- Deteksi duplikasi berdasarkan `code`
- Update referensi di tabel users dan equipment sebelum menghapus duplikat
- Hapus unit duplikat (keep yang pertama/oldest)
- Tambah unique constraint pada kolom `code`
- Log lengkap semua perubahan

### 2. Migration untuk Fix User Roles
File: `database/migrations/2026_04_01_123442_fix_user_roles_data.php`

**Fitur:**
- Audit semua user dan role mereka
- Identifikasi user dengan role 'leader' tapi position 'petugas'
- Koreksi role: hapus 'leader', tambah 'petugas' dan 'user'
- Log semua perubahan

### 3. Update UnitSeeder
File: `database/seeders/UnitSeeder.php`

**Fitur:**
- Definisi 7 unit yang benar
- Cleanup duplikat sebelum seeding
- Update referensi sebelum hapus duplikat
- Menggunakan `updateOrCreate` untuk prevent duplikasi

### 4. Validation di Model
File: `app/Models/Unit.php`

**Fitur:**
- Event `saving` untuk validasi sebelum save
- Cek duplikasi code sebelum create/update
- Throw exception jika ada duplikasi
- Mencegah duplikasi di level aplikasi

### 5. Artisan Command
File: `app/Console/Commands/CleanupDuplicateUnits.php`

**Fitur:**
- Command: `php artisan units:cleanup-duplicates`
- Option `--dry-run` untuk preview tanpa perubahan
- Audit lengkap dengan tabel visual
- Cleanup duplikasi unit
- Fix user roles
- Update equipment references
- Logging lengkap

## Cara Penggunaan

### 1. Preview Perubahan (Dry Run)
```bash
php artisan units:cleanup-duplicates --dry-run
```

Output akan menampilkan:
- Daftar semua unit saat ini
- Unit yang duplikat
- User dengan role yang salah
- Preview perubahan yang akan dilakukan

### 2. Jalankan Cleanup
```bash
php artisan units:cleanup-duplicates
```

Ini akan:
- Merge unit duplikat
- Update semua referensi
- Fix user roles
- Log semua perubahan

### 3. Atau Gunakan Migration
```bash
php artisan migrate
```

Ini akan menjalankan kedua migration:
- `fix_duplicate_units_data`
- `fix_user_roles_data`

### 4. Atau Gunakan Seeder
```bash
php artisan db:seed --class=UnitSeeder
```

## Testing

### 1. Test Manual

#### Sebelum Cleanup:
```bash
# Cek unit duplikat
php artisan tinker
>>> \App\Models\Unit::select('code', \DB::raw('COUNT(*) as count'))->groupBy('code')->having('count', '>', 1)->get()

# Cek total unit
>>> \App\Models\Unit::count()

# Cek user roles
>>> \App\Models\User::with('roles')->get()->map(fn($u) => ['email' => $u->email, 'roles' => $u->roles->pluck('name')])
```

#### Jalankan Cleanup:
```bash
php artisan units:cleanup-duplicates --dry-run  # Preview dulu
php artisan units:cleanup-duplicates            # Jalankan
```

#### Setelah Cleanup:
```bash
# Verifikasi tidak ada duplikat
php artisan tinker
>>> \App\Models\Unit::select('code', \DB::raw('COUNT(*) as count'))->groupBy('code')->having('count', '>', 1)->get()
# Harusnya empty

# Verifikasi total unit = 7
>>> \App\Models\Unit::count()
# Harusnya 7

# Verifikasi semua unit
>>> \App\Models\Unit::orderBy('code')->get(['code', 'name'])
# Harusnya: INDUK, UP2WI, UP2WII, UP2WIII, UP2WIV, UP2WV, UP2WVI

# Verifikasi user roles sudah benar
>>> \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'leader'))->where('position', 'petugas')->count()
# Harusnya 0
```

### 2. Test Automated

Buat test file: `tests/Feature/UnitCleanupTest.php`

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UnitCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_duplicate_units_exist()
    {
        $duplicates = Unit::select('code', \DB::raw('COUNT(*) as count'))
            ->groupBy('code')
            ->having('count', '>', 1)
            ->get();
        
        $this->assertEquals(0, $duplicates->count(), 'No duplicate units should exist');
    }
    
    public function test_exactly_seven_units_exist()
    {
        $count = Unit::count();
        $this->assertEquals(7, $count, 'Should have exactly 7 units');
    }
    
    public function test_all_expected_units_exist()
    {
        $expectedCodes = ['INDUK', 'UP2WI', 'UP2WII', 'UP2WIII', 'UP2WIV', 'UP2WV', 'UP2WVI'];
        
        foreach ($expectedCodes as $code) {
            $this->assertTrue(
                Unit::where('code', $code)->exists(),
                "Unit {$code} should exist"
            );
        }
    }
    
    public function test_no_users_with_wrong_roles()
    {
        $wrongRoles = User::whereHas('roles', function($query) {
            $query->where('name', 'leader');
        })->where('position', 'petugas')->count();
        
        $this->assertEquals(0, $wrongRoles, 'No users should have leader role with petugas position');
    }
    
    public function test_cannot_create_duplicate_unit()
    {
        Unit::create([
            'code' => 'TEST',
            'name' => 'Test Unit',
            'is_active' => true
        ]);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('sudah ada');
        
        Unit::create([
            'code' => 'TEST',
            'name' => 'Test Unit 2',
            'is_active' => true
        ]);
    }
}
```

Jalankan test:
```bash
php artisan test --filter=UnitCleanupTest
```

## Log Files

Semua perubahan akan dicatat di:
- `storage/logs/laravel.log` - Log aplikasi
- `storage/logs/unit_cleanup_YYYY-MM-DD_HHMMSS.log` - Log khusus cleanup (dari migration)

## Rollback

Jika perlu rollback migration:
```bash
php artisan migrate:rollback --step=2
```

**PERHATIAN:** Rollback akan menghapus unique constraint, tapi tidak akan mengembalikan data yang sudah di-merge. Backup database sebelum menjalankan cleanup!

## Backup Database

Sebelum menjalankan cleanup, backup database:
```bash
# MySQL
mysqldump -u username -p database_name > backup_before_cleanup.sql

# Atau gunakan Laravel
php artisan db:backup  # Jika ada package backup
```

## Checklist

- [ ] Backup database
- [ ] Jalankan dry-run: `php artisan units:cleanup-duplicates --dry-run`
- [ ] Review output dry-run
- [ ] Jalankan cleanup: `php artisan units:cleanup-duplicates`
- [ ] Verifikasi tidak ada duplikat
- [ ] Verifikasi total unit = 7
- [ ] Verifikasi user roles sudah benar
- [ ] Test aplikasi secara manual
- [ ] Jalankan automated tests
- [ ] Review log files
- [ ] Dokumentasi perubahan

## Troubleshooting

### Error: "Unit dengan code 'XXX' sudah ada"
Ini berarti validation bekerja! Ada duplikasi yang dicoba dibuat. Cek kode yang mencoba membuat unit duplikat.

### Error: Database connection
Pastikan database sudah running dan konfigurasi `.env` benar.

### Unit masih duplikat setelah cleanup
Jalankan command lagi atau cek log untuk melihat error yang mungkin terjadi.

### User role tidak berubah
Cek apakah role 'petugas' dan 'user' ada di tabel roles. Jalankan `php artisan db:seed --class=RolePermissionSeeder` jika perlu.

## Support

Jika ada masalah, cek:
1. Log file di `storage/logs/`
2. Output command dengan `-v` flag: `php artisan units:cleanup-duplicates -v`
3. Database state dengan tinker
