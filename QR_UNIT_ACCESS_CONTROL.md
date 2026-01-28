# QR Code Unit Access Control

## Deskripsi Fitur

Sistem QR Code sekarang dilengkapi dengan **Unit Access Control** yang membatasi akses QR code hanya untuk petugas dari unit yang sama.

## Use Case

### ✅ **Skenario Yang Diperbolehkan:**

1. **Petugas UP2WIII** scan QR APAR dari **UP2WIII** → ✅ **Berhasil**
2. **Petugas UP2WIV** scan QR APAR dari **UP2WIV** → ✅ **Berhasil**
3. **Petugas Induk** scan QR APAR dari **Induk** → ✅ **Berhasil**
4. **Admin/Superadmin/Inspector** scan QR dari unit manapun → ✅ **Berhasil** (full access)

### ❌ **Skenario Yang Diblokir:**

1. **Petugas UP2WIII** scan QR APAR dari **UP2WIV** → ❌ **403 Forbidden**
2. **Petugas UP2WIV** scan QR APAR dari **Induk** → ❌ **403 Forbidden**
3. **Petugas Induk** scan QR APAR dari **UP2WIII** → ❌ **403 Forbidden**

## Implementasi Teknis

### Files Modified:

**1. `app/Http/Controllers/AparController.php`**

- Method `riwayat()` - Added unit access check
- Method `viewKartu()` - Added unit access check

### Logic Flow:

```php
public function riwayat(Request $request, Apar $apar)
{
    // 1. Get user's unit_id
    $userUnitId = $this->getAuthUserUnitId();
    
    // 2. Check if user is admin/superadmin/inspector (bypass check)
    if (!auth()->user()->hasAnyRole(['superadmin', 'admin', 'inspector'])) {
        
        // 3. Check if APAR unit matches user unit
        if ($apar->unit_id != $userUnitId) {
            // 4. Abort with 403 error
            abort(403, 'Anda tidak memiliki akses ke APAR dari unit lain');
        }
    }
    
    // 5. Continue with normal flow...
}
```

## Role-Based Access

| Role | Access Level |
|------|--------------|
| **Superadmin** | ✅ All units (global access) |
| **Inspector** | ✅ All units (global access) |
| **Leader** | ⚠️ Own unit only |
| **Petugas** | ⚠️ Own unit only |

## Error Messages

### 403 Forbidden Error:

**Message:**
```
Anda tidak memiliki akses ke APAR dari unit lain. 
QR Code ini untuk unit: UP2WIII
```

**HTTP Status:** 403 Forbidden

## Testing

### Test Case 1: Same Unit Access ✅

```
1. Login sebagai petugas UP2WIII (user_id=4, unit_id=1)
2. Scan QR APAR dari UP2WIII (apar_id=1, unit_id=1)
3. Expected: Berhasil melihat riwayat
```

### Test Case 2: Different Unit Access ❌

```
1. Login sebagai petugas UP2WIII (user_id=4, unit_id=1)
2. Scan QR APAR dari UP2WIV (apar_id=2, unit_id=2)
3. Expected: 403 Forbidden dengan pesan error
```

### Test Case 3: Admin Full Access ✅

```
1. Login sebagai admin (user_id=1, role=admin)
2. Scan QR APAR dari unit manapun
3. Expected: Berhasil melihat semua
```

## Database Requirements

### Existing Column:
- `apars.unit_id` - Foreign key ke `units` table

### Relationship:
```php
// Apar Model
public function unit()
{
    return $this->belongsTo(Unit::class);
}
```

## Applied to All Equipment

Fitur ini bisa diterapkan ke semua equipment:
- APAR ✅ (Implemented)
- Fire Alarm (TODO)
- Box Hydrant (TODO)
- Rumah Pompa (TODO)
- P3K (TODO)
- APAB (TODO)
- APAT (TODO)

## Security Benefits

1. **Data Isolation** - Unit tidak bisa akses data unit lain
2. **Privacy** - Informasi sensitif tetap dalam unit
3. **Compliance** - Memenuhi principle of least privilege
4. **Audit Trail** - Jelas siapa akses apa dari unit mana

## Future Enhancements

1. **Custom Error Page** - 403 page dengan design yang lebih bagus
2. **Logging** - Log setiap attempt akses cross-unit
3. **Notification** - Notify admin jika ada suspicious access
4. **IP Whitelist** - Allow certain IPs untuk bypass check

---

**Implemented:** 2026-01-13  
**Author:** Development Team  
**Version:** 1.0
