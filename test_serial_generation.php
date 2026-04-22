<?php

/**
 * Test script to verify serial number generation for Box Hydrant, Rumah Pompa, and APAB
 * 
 * This script simulates what happens when a user from UP2WI unit creates equipment
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SERIAL NUMBER GENERATION TEST ===\n\n";

// Test 1: Check what units exist in database
echo "1. Units in database:\n";
$units = \App\Models\Unit::select('id', 'code', 'name')->get();
foreach ($units as $unit) {
    echo "   - ID: {$unit->id}, Code: {$unit->code}, Name: {$unit->name}\n";
}
echo "\n";

// Test 2: Find UP2WI unit
echo "2. Finding UP2WI unit:\n";
$up2wi = \App\Models\Unit::where('code', 'UP2WI')->first();
if ($up2wi) {
    echo "   ✓ Found UP2WI: ID={$up2wi->id}, Code={$up2wi->code}, Name={$up2wi->name}\n";
} else {
    echo "   ✗ UP2WI unit not found!\n";
    // Try finding by name
    $up2wi = \App\Models\Unit::where('name', 'LIKE', '%UP2WI%')->first();
    if ($up2wi) {
        echo "   ✓ Found by name: ID={$up2wi->id}, Code={$up2wi->code}, Name={$up2wi->name}\n";
    }
}
echo "\n";

// Test 3: Generate serial numbers for UP2WI
if ($up2wi) {
    echo "3. Generating serial numbers for UP2WI (unit_id={$up2wi->id}):\n";
    
    // Box Hydrant
    $boxHydrantSerial = \App\Models\BoxHydrant::generateNextSerial($up2wi->id, false);
    echo "   - Box Hydrant: {$boxHydrantSerial}\n";
    
    // Rumah Pompa
    $rumahPompaSerial = \App\Models\RumahPompa::generateNextSerial($up2wi->id, false);
    echo "   - Rumah Pompa: {$rumahPompaSerial}\n";
    
    // APAB
    $apabSerial = \App\Models\Apab::generateNextSerial($up2wi->id, false);
    echo "   - APAB: {$apabSerial}\n";
    
    echo "\n";
}

// Test 4: Generate serial numbers for INDUK (null unit_id)
echo "4. Generating serial numbers for INDUK (unit_id=null):\n";
$boxHydrantSerial = \App\Models\BoxHydrant::generateNextSerial(null, false);
echo "   - Box Hydrant: {$boxHydrantSerial}\n";

$rumahPompaSerial = \App\Models\RumahPompa::generateNextSerial(null, false);
echo "   - Rumah Pompa: {$rumahPompaSerial}\n";

$apabSerial = \App\Models\Apab::generateNextSerial(null, false);
echo "   - APAB: {$apabSerial}\n";

echo "\n=== TEST COMPLETE ===\n";
