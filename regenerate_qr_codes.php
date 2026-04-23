<?php
/**
 * Script to regenerate all QR codes in the system with improved layout
 * This addresses scanning issues by using proper quiet zones and error correction
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== QR CODE REGENERATION SCRIPT ===\n\n";

$stats = [
    'apar' => ['total' => 0, 'success' => 0, 'failed' => 0],
    'apat' => ['total' => 0, 'success' => 0, 'failed' => 0],
    'apab' => ['total' => 0, 'success' => 0, 'failed' => 0],
    'p3k' => ['total' => 0, 'success' => 0, 'failed' => 0],
    'box_hydrant' => ['total' => 0, 'success' => 0, 'failed' => 0],
    'fire_alarm' => ['total' => 0, 'success' => 0, 'failed' => 0],
    'rumah_pompa' => ['total' => 0, 'success' => 0, 'failed' => 0],
];

function regenerateModelQr($modelClass, $label, &$stats, $key) {
    echo "Processing {$label}...\n";
    
    try {
        $items = $modelClass::all();
        $stats[$key]['total'] = $items->count();
        
        foreach ($items as $item) {
            try {
                // Force regeneration with new improved layout
                $item->generateQrSvg(true);
                $stats[$key]['success']++;
                echo "  ✓ {$item->serial_no}\n";
            } catch (Exception $e) {
                $stats[$key]['failed']++;
                echo "  ✗ {$item->serial_no}: " . $e->getMessage() . "\n";
            }
        }
        
        echo "  Completed: {$stats[$key]['success']}/{$stats[$key]['total']} successful\n\n";
        
    } catch (Exception $e) {
        echo "  ✗ Failed to process {$label}: " . $e->getMessage() . "\n\n";
    }
}

// Regenerate QR codes for all equipment types
regenerateModelQr(\App\Models\Apar::class, 'APAR Equipment', $stats, 'apar');
regenerateModelQr(\App\Models\Apat::class, 'APAT Equipment', $stats, 'apat');
regenerateModelQr(\App\Models\Apab::class, 'APAB Equipment', $stats, 'apab');
regenerateModelQr(\App\Models\P3k::class, 'P3K Equipment', $stats, 'p3k');
regenerateModelQr(\App\Models\BoxHydrant::class, 'Box Hydrant Equipment', $stats, 'box_hydrant');
regenerateModelQr(\App\Models\FireAlarm::class, 'Fire Alarm Equipment', $stats, 'fire_alarm');
regenerateModelQr(\App\Models\RumahPompa::class, 'Rumah Pompa Equipment', $stats, 'rumah_pompa');

// Summary report
echo "=== REGENERATION SUMMARY ===\n";
$totalItems = 0;
$totalSuccess = 0;
$totalFailed = 0;

foreach ($stats as $type => $data) {
    $totalItems += $data['total'];
    $totalSuccess += $data['success'];
    $totalFailed += $data['failed'];
    
    if ($data['total'] > 0) {
        $percentage = round(($data['success'] / $data['total']) * 100, 1);
        echo sprintf("%-15s: %3d/%3d (%s%%)\n", 
            strtoupper($type), 
            $data['success'], 
            $data['total'], 
            $percentage
        );
    }
}

echo str_repeat('-', 30) . "\n";
echo sprintf("%-15s: %3d/%3d (%s%%)\n", 
    'TOTAL', 
    $totalSuccess, 
    $totalItems, 
    $totalItems > 0 ? round(($totalSuccess / $totalItems) * 100, 1) : 0
);

if ($totalFailed > 0) {
    echo "\n⚠ {$totalFailed} items failed to regenerate. Check error messages above.\n";
}

echo "\n=== QR CODE IMPROVEMENTS ===\n";
echo "✓ Error correction level changed from H to M (better scanning)\n";
echo "✓ Proper quiet zone (4 modules minimum) implemented\n";
echo "✓ QR area kept completely free from overlapping elements\n";
echo "✓ Improved visual layout with proper spacing\n";
echo "✓ Logo positioning optimized to avoid QR interference\n";

echo "\n=== TESTING RECOMMENDATIONS ===\n";
echo "1. Test QR codes with multiple scanner apps:\n";
echo "   - Built-in camera apps (iOS/Android)\n";
echo "   - QR Code Reader apps\n";
echo "   - Barcode Scanner apps\n";
echo "2. Test in different lighting conditions\n";
echo "3. Test at various distances and angles\n";
echo "4. Verify all equipment types are scannable\n";

echo "\n=== REGENERATION COMPLETE ===\n";