<?php

/**
 * Quick fix script to update unit names directly via PHP
 * Run from the project root: php fix_units.php
 * 
 * This script updates unit names to human-readable format in the database.
 */

// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== PLN Unit Name Fix Script ===" . PHP_EOL;
echo date('Y-m-d H:i:s') . PHP_EOL;
echo PHP_EOL;

// 1. Show current units
echo "--- Current Units in Database ---" . PHP_EOL;
$units = DB::table('units')->orderBy('id')->get();
foreach ($units as $unit) {
    echo "ID:{$unit->id} | Code:{$unit->code} | Name:{$unit->name}" . PHP_EOL;
}
echo PHP_EOL;

// 2. Remove stale old-format entries (UPW1, UP2W1 style)
$stalePatterns = ['UPW1', 'UPW2', 'UPW3', 'UPW4', 'UPW5', 'UPW6',
                  'UP2W1', 'UP2W2', 'UP2W3', 'UP2W4', 'UP2W5', 'UP2W6'];
$canonicalMap = [
    'UPW1'  => 'UP2WI',   'UP2W1' => 'UP2WI',
    'UPW2'  => 'UP2WII',  'UP2W2' => 'UP2WII',
    'UPW3'  => 'UP2WIII', 'UP2W3' => 'UP2WIII',
    'UPW4'  => 'UP2WIV',  'UP2W4' => 'UP2WIV',
    'UPW5'  => 'UP2WV',   'UP2W5' => 'UP2WV',
    'UPW6'  => 'UP2WVI',  'UP2W6' => 'UP2WVI',
];

foreach ($stalePatterns as $code) {
    $stale = DB::table('units')->where('code', $code)->first();
    if ($stale) {
        $canonicalCode = $canonicalMap[$code] ?? null;
        $canonical = $canonicalCode ? DB::table('units')->where('code', $canonicalCode)->first() : null;
        
        if ($canonical) {
            // Migrate references
            $usersUpdated = DB::table('users')->where('unit_id', $stale->id)->update(['unit_id' => $canonical->id]);
            echo "Migrated {$usersUpdated} users from unit '{$code}' to '{$canonicalCode}'" . PHP_EOL;
            
            $equipmentTables = ['apars', 'apats', 'apabs', 'fire_alarms', 'box_hydrants', 'rumah_pompas', 'p3ks', 'cctvs'];
            foreach ($equipmentTables as $tbl) {
                if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'unit_id')) {
                    DB::table($tbl)->where('unit_id', $stale->id)->update(['unit_id' => $canonical->id]);
                }
            }
            
            DB::table('units')->where('id', $stale->id)->delete();
            echo "Deleted stale unit ID:{$stale->id} Code:{$code}" . PHP_EOL;
        } else {
            echo "WARNING: No canonical unit for code '{$code}', skipping" . PHP_EOL;
        }
    }
}

// 3. Update names to human-readable format
$nameMap = [
    'INDUK'   => ['name' => 'Induk',    'description' => 'Unit Induk (Pusat)'],
    'UP2WI'   => ['name' => 'UP2WI',   'description' => 'Unit Pelayanan dan Pengelolaan Wilayah I'],
    'UP2WII'  => ['name' => 'UP2WII',  'description' => 'Unit Pelayanan dan Pengelolaan Wilayah II'],
    'UP2WIII' => ['name' => 'UP2WIII', 'description' => 'Unit Pelayanan dan Pengelolaan Wilayah III'],
    'UP2WIV'  => ['name' => 'UP2WIV',  'description' => 'Unit Pelayanan dan Pengelolaan Wilayah IV'],
    'UP2WV'   => ['name' => 'UP2WV',   'description' => 'Unit Pelayanan dan Pengelolaan Wilayah V'],
    'UP2WVI'  => ['name' => 'UP2WVI',  'description' => 'Unit Pelayanan dan Pengelolaan Wilayah VI'],
];

echo PHP_EOL . "--- Updating Unit Names ---" . PHP_EOL;
foreach ($nameMap as $code => $data) {
    $existing = DB::table('units')->where('code', $code)->first();
    if ($existing) {
        DB::table('units')->where('id', $existing->id)->update([
            'name'        => $data['name'],
            'description' => $data['description'],
            'updated_at'  => now(),
        ]);
        echo "Updated: {$code} -> '{$data['name']}'" . PHP_EOL;
    } else {
        DB::table('units')->insert([
            'code'        => $code,
            'name'        => $data['name'],
            'description' => $data['description'],
            'is_active'   => true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        echo "Created: {$code} '{$data['name']}'" . PHP_EOL;
    }
}

// 4. Check for duplicates
echo PHP_EOL . "--- Checking for Duplicates ---" . PHP_EOL;
$dupes = DB::table('units')
    ->select('code', DB::raw('COUNT(*) as cnt'))
    ->groupBy('code')
    ->having('cnt', '>', 1)
    ->get();

if ($dupes->count() > 0) {
    echo "WARNING: Found duplicates!" . PHP_EOL;
    foreach ($dupes as $d) {
        echo "  Code: {$d->code} - Count: {$d->cnt}" . PHP_EOL;
    }
} else {
    echo "OK: No duplicates found" . PHP_EOL;
}

// 5. Final state
echo PHP_EOL . "--- Final Units ---" . PHP_EOL;
$finalUnits = DB::table('units')->orderBy('id')->get();
foreach ($finalUnits as $unit) {
    echo "ID:{$unit->id} | Code:{$unit->code} | Name:{$unit->name} | Active:" . ($unit->is_active ? 'yes' : 'no') . PHP_EOL;
}

echo PHP_EOL . "✅ Done!" . PHP_EOL;
