<?php

/**
 * Diagnostic script to identify why serial numbers show "INDUK" instead of unit code
 * 
 * Run this script while logged in as the UP2WI user to see what's happening
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== UNIT SERIAL NUMBER DIAGNOSTIC ===\n\n";

// Check if user is authenticated
if (!auth()->check()) {
    echo "❌ No user is authenticated. Please run this in a web context or set up auth.\n";
    echo "\nTo test, you can manually check:\n";
    echo "1. Login as UP2WI user\n";
    echo "2. Check the user's unit_id in the database\n";
    echo "3. Check the unit's code in the units table\n";
    exit(1);
}

$user = auth()->user();

echo "1. Current User Information:\n";
echo "   - ID: {$user->id}\n";
echo "   - Name: {$user->name}\n";
echo "   - Email: {$user->email}\n";
echo "   - Unit ID: " . ($user->unit_id ?? 'NULL') . "\n";
echo "\n";

if ($user->unit_id) {
    echo "2. User's Unit Information:\n";
    $unit = \App\Models\Unit::find($user->unit_id);
    if ($unit) {
        echo "   - Unit ID: {$unit->id}\n";
        echo "   - Unit Code: {$unit->code}\n";
        echo "   - Unit Name: {$unit->name}\n";
        echo "   - Is Active: " . ($unit->is_active ? 'Yes' : 'No') . "\n";
        echo "\n";
        
        echo "3. Expected Serial Numbers:\n";
        echo "   - Box Hydrant: H6-{$unit->code}-001\n";
        echo "   - Rumah Pompa: RUMAHPOMPA-{$unit->code}-001\n";
        echo "   - APAB: APAB-{$unit->code}-001\n";
        echo "\n";
        
        echo "4. Actual Generated Serial Numbers:\n";
        $boxHydrantSerial = \App\Models\BoxHydrant::generateNextSerial($user->unit_id, false);
        echo "   - Box Hydrant: {$boxHydrantSerial}\n";
        
        $rumahPompaSerial = \App\Models\RumahPompa::generateNextSerial($user->unit_id, false);
        echo "   - Rumah Pompa: {$rumahPompaSerial}\n";
        
        $apabSerial = \App\Models\Apab::generateNextSerial($user->unit_id, false);
        echo "   - APAB: {$apabSerial}\n";
        echo "\n";
        
        // Check if the unit code is "INDUK"
        if ($unit->code === 'INDUK') {
            echo "⚠️  PROBLEM FOUND:\n";
            echo "   Your user is assigned to a unit with code 'INDUK'.\n";
            echo "   This is why serial numbers show 'INDUK' instead of 'UP2WI'.\n";
            echo "\n";
            echo "   SOLUTION:\n";
            echo "   Update the unit code in the database:\n";
            echo "   UPDATE units SET code = 'UP2WI' WHERE id = {$unit->id};\n";
        } else {
            echo "✓ Unit code looks correct: {$unit->code}\n";
        }
    } else {
        echo "   ❌ Unit not found! User's unit_id ({$user->unit_id}) doesn't exist in units table.\n";
        echo "\n";
        echo "   SOLUTION:\n";
        echo "   Either:\n";
        echo "   1. Create the missing unit, or\n";
        echo "   2. Update user's unit_id to a valid unit\n";
    }
} else {
    echo "2. User has no unit assigned (unit_id is NULL)\n";
    echo "   This means the user is treated as 'INDUK' (headquarters).\n";
    echo "\n";
    
    // Check session
    $viewingUnitId = session('viewing_unit_id');
    if ($viewingUnitId) {
        echo "3. Session viewing_unit_id: {$viewingUnitId}\n";
        $unit = \App\Models\Unit::find($viewingUnitId);
        if ($unit) {
            echo "   - Unit Code: {$unit->code}\n";
            echo "   - Unit Name: {$unit->name}\n";
        }
    } else {
        echo "3. No viewing_unit_id in session\n";
    }
    echo "\n";
    
    echo "   SOLUTION:\n";
    echo "   If this user should be in UP2WI unit:\n";
    echo "   1. Find the UP2WI unit ID: SELECT id FROM units WHERE code = 'UP2WI';\n";
    echo "   2. Update user: UPDATE users SET unit_id = <unit_id> WHERE id = {$user->id};\n";
}

echo "\n5. All Units in Database:\n";
$units = \App\Models\Unit::select('id', 'code', 'name', 'is_active')->get();
foreach ($units as $unit) {
    $active = $unit->is_active ? '✓' : '✗';
    echo "   {$active} ID: {$unit->id}, Code: {$unit->code}, Name: {$unit->name}\n";
}

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
