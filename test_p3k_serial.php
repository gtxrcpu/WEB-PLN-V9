<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing P3K Serial Generation ===" . PHP_EOL . PHP_EOL;

// Test for petugas UP2WI (unit_id = 10)
$user = App\Models\User::where('email', 'UP2W1@pln.com')->first();
if ($user) {
    echo "User: {$user->name}" . PHP_EOL;
    echo "Unit ID: {$user->unit_id}" . PHP_EOL;
    
    $unit = App\Models\Unit::find($user->unit_id);
    if ($unit) {
        echo "Unit Code: {$unit->code}" . PHP_EOL;
        echo "Unit Name: {$unit->name}" . PHP_EOL;
        
        $unitCode = strtoupper(str_replace([' ', '-'], '', $unit->code ?? $unit->name));
        echo "Processed Unit Code: {$unitCode}" . PHP_EOL;
        
        $prefix = 'P3K-PMK-' . $unitCode . '-';
        echo "Expected Prefix for Pemakaian: {$prefix}" . PHP_EOL;
        
        // Check existing P3K
        $existing = App\Models\P3k::where('unit_id', $user->unit_id)
            ->where('jenis', 'pemakaian')
            ->orderBy('serial_no')
            ->get(['serial_no', 'jenis']);
        
        echo PHP_EOL . "Existing P3K (pemakaian) for this unit:" . PHP_EOL;
        foreach ($existing as $p3k) {
            echo "  - {$p3k->serial_no}" . PHP_EOL;
        }
        
        // Calculate next serial
        $last = App\Models\P3k::where('serial_no', 'like', $prefix . '%')
            ->where('jenis', 'pemakaian')
            ->where('unit_id', $user->unit_id)
            ->orderByRaw('CAST(SUBSTRING_INDEX(serial_no, "-", -1) AS UNSIGNED) DESC')
            ->value('serial_no');
        
        $nextNum = 1;
        if ($last && preg_match('/(\d+)$/', $last, $m)) {
            $nextNum = (int) $m[1] + 1;
        }
        $nextSerial = $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        
        echo PHP_EOL . "Last Serial: " . ($last ?? 'none') . PHP_EOL;
        echo "Next Serial: {$nextSerial}" . PHP_EOL;
    }
} else {
    echo "User UP2W1@pln.com not found" . PHP_EOL;
}
