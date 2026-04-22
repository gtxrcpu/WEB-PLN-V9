# Unit Serial Number Issue - Diagnosis and Fix

## Problem
When logged in as UP2WI user, serial numbers for Box Hydrant, Rumah Pompa, and APAB show "INDUK" instead of the unit code (e.g., "UP2WI").

Example:
- Expected: `H6-UP2WI-001`
- Actual: `H6-INDUK-001`

## Root Cause
The serial number generation is working correctly. The issue is with the data in the database:

1. **User's unit_id**: The user account may not have the correct `unit_id` set
2. **Unit's code**: The unit record may have `code = 'INDUK'` instead of `code = 'UP2WI'`

## How Serial Number Generation Works

When creating equipment (Box Hydrant, Rumah Pompa, APAB), the system:

1. Gets the authenticated user's `unit_id` via `getAuthUserUnitId()`
2. Looks up the unit record using that `unit_id`
3. Uses the unit's `code` field in the serial number format
4. If `unit_id` is NULL, uses "INDUK" as the code

## Verification Steps

### Step 1: Check Current User's Unit Assignment

```sql
-- Find the user (replace with actual email)
SELECT id, name, email, unit_id 
FROM users 
WHERE email = 'user.up2w1@pln.com';
```

### Step 2: Check Unit Data

```sql
-- Check all units
SELECT id, code, name, is_active 
FROM units 
ORDER BY code;
```

Expected units:
- `INDUK` - Induk (headquarters)
- `UP2WI` - UP2WI
- `UP2WII` - UP2WII
- `UP2WIII` - UP2WIII
- `UP2WIV` - UP2WIV
- `UP2WV` - UP2WV
- `UP2WVI` - UP2WVI

### Step 3: Check User-Unit Relationship

```sql
-- Check which unit the user is assigned to
SELECT 
    u.id as user_id,
    u.name as user_name,
    u.email,
    u.unit_id,
    un.code as unit_code,
    un.name as unit_name
FROM users u
LEFT JOIN units un ON u.unit_id = un.id
WHERE u.email = 'user.up2w1@pln.com';
```

## Solutions

### Solution 1: User Has Wrong unit_id

If the user's `unit_id` points to the wrong unit:

```sql
-- Find the correct UP2WI unit ID
SELECT id FROM units WHERE code = 'UP2WI';

-- Update user's unit_id (replace <unit_id> and <user_id>)
UPDATE users 
SET unit_id = <unit_id> 
WHERE id = <user_id>;
```

### Solution 2: User Has NULL unit_id

If the user's `unit_id` is NULL:

```sql
-- Find the UP2WI unit ID
SELECT id FROM units WHERE code = 'UP2WI';

-- Assign user to UP2WI unit
UPDATE users 
SET unit_id = <unit_id> 
WHERE email = 'user.up2w1@pln.com';
```

### Solution 3: Unit Has Wrong Code

If the unit exists but has the wrong code (e.g., code is 'INDUK' but should be 'UP2WI'):

```sql
-- Update unit code
UPDATE units 
SET code = 'UP2WI', name = 'UP2WI' 
WHERE id = <unit_id>;
```

### Solution 4: Run Unit Seeder

If units are missing or incorrect, run the seeder:

```bash
php artisan db:seed --class=UnitSeeder
```

This will create/update all units with correct codes:
- INDUK, UP2WI, UP2WII, UP2WIII, UP2WIV, UP2WV, UP2WVI

## Testing After Fix

1. Login as the UP2WI user
2. Navigate to create Box Hydrant, Rumah Pompa, or APAB
3. Check the serial number preview
4. Expected format:
   - Box Hydrant: `H6-UP2WI-001`
   - Rumah Pompa: `RUMAHPOMPA-UP2WI-001`
   - APAB: `APAB-UP2WI-001`

## Diagnostic Script

Run the diagnostic script to identify the exact issue:

```bash
php diagnose_unit_issue.php
```

This will show:
- Current user information
- User's unit assignment
- Unit code
- Expected vs actual serial numbers
- Specific SQL commands to fix the issue

## Code Implementation (Already Fixed)

The following controllers have been updated to pass `unit_id` to `generateNextSerial()`:

✅ `ApatController.php` - Fixed in TASK 1
✅ `FireAlarmController.php` - Fixed in TASK 1
✅ `BoxHydrantController.php` - Fixed in TASK 5
✅ `RumahPompaController.php` - Fixed in TASK 5
✅ `ApabController.php` - Fixed in TASK 5

All models correctly generate unit-specific serial numbers when provided with a valid `unit_id`.

## Summary

The code is working correctly. The issue is with the database data:
1. Ensure users are assigned to the correct unit (`unit_id` field)
2. Ensure units have the correct `code` field (UP2WI, not INDUK)
3. Run the diagnostic script to identify the specific issue
4. Apply the appropriate SQL fix from the solutions above
