<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "======================================\n";
echo "  DIAGNOSTIC: Unit & APAR Data Check  \n";
echo "======================================\n\n";

// 1. Check Units
echo "1. Units:\n";
echo str_repeat("-", 50) . "\n";
$units = DB::table('units')->select('id', 'name', 'code')->get();
foreach ($units as $unit) {
    echo sprintf("  ID: %s | Name: %-15s | Code: %s\n", $unit->id, $unit->name, $unit->code);
}

// 2. Check APARs
echo "\n2. APARs (first 5):\n";
echo str_repeat("-", 50) . "\n";
$apars = DB::table('apars')->select('id', 'serial_no', 'barcode', 'unit_id')->limit(5)->get();
foreach ($apars as $apar) {
    $unitName = $apar->unit_id ?
        DB::table('units')->where('id', $apar->unit_id)->value('name') :
        'Induk (NULL)';
    echo sprintf(
        "  ID: %s | Serial: %-15s | Unit: %s (ID: %s)\n",
        $apar->id,
        $apar->serial_no,
        $unitName,
        $apar->unit_id ?? 'NULL'
    );
}

// 3. Check Users
echo "\n3. Users (petugas/leader):\n";
echo str_repeat("-", 50) . "\n";
$users = DB::table('users')
    ->select('id', 'name', 'email', 'unit_id', 'role')
    ->whereIn('role', ['petugas', 'leader'])
    ->get();
foreach ($users as $user) {
    $unitName = $user->unit_id ?
        DB::table('units')->where('id', $user->unit_id)->value('name') :
        'Induk (NULL)';
    echo sprintf(
        "  ID: %s | Name: %-20s | Role: %-10s | Unit: %s (ID: %s)\n",
        $user->id,
        $user->name,
        $user->role ?? 'NULL',
        $unitName,
        $user->unit_id ?? 'NULL'
    );
}

// 4. APAR Count per Unit
echo "\n4. APAR Count per Unit:\n";
echo str_repeat("-", 50) . "\n";
$counts = DB::table('apars')
    ->select('unit_id', DB::raw('COUNT(*) as total'))
    ->groupBy('unit_id')
    ->get();
foreach ($counts as $count) {
    $unitName = $count->unit_id ?
        DB::table('units')->where('id', $count->unit_id)->value('name') :
        'Induk (NULL)';
    echo sprintf(
        "  Unit: %-20s (ID: %s) = %d APAR(s)\n",
        $unitName,
        $count->unit_id ?? 'NULL',
        $count->total
    );
}

echo "\n======================================\n";
echo "  Diagnostic Complete!\n";
echo "======================================\n";
