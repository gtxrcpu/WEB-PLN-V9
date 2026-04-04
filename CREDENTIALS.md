# 🔐 User Credentials — PLN K3 Inventaris (Updated)

## Quick Login Reference

### 🔑 SUPERADMIN (Full Access)
- **Email:** `superadmin@pln.co.id`
- **Password:** `super123`
- **Access:** All features, all units

---

## Unit Listing (Dropdown Values)

| Code     | Display Name | Keterangan                              |
|----------|-------------|------------------------------------------|
| INDUK    | Induk        | Unit Induk (Pusat)                      |
| UP2WI    | UP2W I       | Unit Pelayanan dan Pengelolaan Wilayah I |
| UP2WII   | UP2W II      | Unit Pelayanan dan Pengelolaan Wilayah II|
| UP2WIII  | UP2W III     | Unit Pelayanan dan Pengelolaan Wilayah III|
| UP2WIV   | UP2W IV      | Unit Pelayanan dan Pengelolaan Wilayah IV |
| UP2WV    | UP2W V       | Unit Pelayanan dan Pengelolaan Wilayah V  |
| UP2WVI   | UP2W VI      | Unit Pelayanan dan Pengelolaan Wilayah VI |

---

## 👥 User Accounts per Unit

### INDUK (tidak termasuk konfigurasi email otomatis)
| Role    | Email                     | Password    |
|---------|---------------------------|-------------|
| Leader  | leader.induk@pln.co.id    | leader123   |
| Petugas | petugas.induk@pln.co.id   | petugas123  |

### UP2W I
| Role    | Email               | Password |
|---------|---------------------|----------|
| Leader  | leader.UPW1@pln.com | password |
| Petugas | UP2W1@pln.com       | password |

### UP2W II
| Role    | Email               | Password |
|---------|---------------------|----------|
| Leader  | leader.UPW2@pln.com | password |
| Petugas | UP2W2@pln.com       | password |

### UP2W III (tidak termasuk konfigurasi email otomatis)
| Role    | Email                     | Password    |
|---------|---------------------------|-------------|
| Leader  | leader.upw3@pln.co.id     | leader123   |
| Petugas | petugas.upw3@pln.co.id    | petugas123  |

### UP2W IV (tidak termasuk konfigurasi email otomatis)
| Role    | Email                     | Password    |
|---------|---------------------------|-------------|
| Leader  | leader.upw4@pln.co.id     | leader123   |
| Petugas | petugas.upw4@pln.co.id    | petugas123  |

### UP2W V
| Role    | Email               | Password |
|---------|---------------------|----------|
| Leader  | leader.UPW5@pln.com | password |
| Petugas | UP2W5@pln.com       | password |

### UP2W VI
| Role    | Email               | Password |
|---------|---------------------|----------|
| Leader  | leader.UPW6@pln.com | password |
| Petugas | UP2W6@pln.com       | password |

---

## 🚀 Apply Fixes

### 1. Fix Unit Names in Database (run manually)
```bash
# Option A: Via artisan migrate
docker exec <container> php artisan migrate --path=database/migrations/2026_04_01_210000_fix_unit_names_and_add_missing_units.php --force

# Option B: Via standalone PHP script (from project root in WSL)
php fix_units.php

# Option C: Via Laravel sail
./vendor/bin/sail artisan migrate --path=database/migrations/2026_04_01_210000_fix_unit_names_and_add_missing_units.php --force
```

### 2. Create Missing User Accounts
```bash
docker exec <container> php artisan db:seed --class=AdminSeeder --force
```

### 3. Full Rebuild (Fresh)
```bash
docker exec <container> php artisan migrate:fresh --seed
```

---

## ⚠️ Security Warning

**IMPORTANT:** These are default development credentials.

For production:
1. ✅ Change ALL passwords immediately
2. ✅ Delete unused accounts
3. ✅ Set `APP_ENV=production`
4. ✅ Set `APP_DEBUG=false`
5. ✅ Use strong passwords (min 12 characters)
