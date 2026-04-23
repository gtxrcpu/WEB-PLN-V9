<?php
/**
 * Script to validate QR codes after regeneration
 * Tests QR code structure and generates sample files for manual testing
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== QR CODE VALIDATION SCRIPT ===\n\n";

function validateQrStructure($svgContent, $itemName) {
    $issues = [];
    
    // Check canvas dimensions
    if (preg_match('/width="(\d+)" height="(\d+)"/', $svgContent, $matches)) {
        $width = (int)$matches[1];
        $height = (int)$matches[2];
        
        if ($width < 300 || $height < 300) {
            $issues[] = "Canvas too small: {$width}x{$height}";
        }
    } else {
        $issues[] = "No canvas dimensions found";
    }
    
    // Check QR positioning
    if (preg_match('/<svg x="(\d+)" y="(\d+)" width="(\d+)" height="(\d+)"/', $svgContent, $matches)) {
        $qrX = (int)$matches[1];
        $qrY = (int)$matches[2];
        $qrW = (int)$matches[3];
        $qrH = (int)$matches[4];
        
        // Check if QR has adequate margins
        if ($qrX < 10) {
            $issues[] = "QR too close to left edge: x={$qrX}";
        }
        if ($qrY < 50) {
            $issues[] = "QR too close to top edge: y={$qrY}";
        }
        
        // Check QR size
        if ($qrW < 200 || $qrH < 200) {
            $issues[] = "QR too small: {$qrW}x{$qrH}";
        }
    } else {
        $issues[] = "QR positioning not found";
    }
    
    // Check for error correction level
    if (strpos($svgContent, 'errorCorrection') !== false) {
        if (strpos($svgContent, 'errorCorrection("H")') !== false) {
            $issues[] = "Using high error correction (may affect scanning)";
        }
    }
    
    return $issues;
}

function testEquipmentQr($modelClass, $label, $sampleCount = 3) {
    echo "Testing {$label} QR codes...\n";
    
    try {
        $items = $modelClass::limit($sampleCount)->get();
        
        if ($items->isEmpty()) {
            echo "  No {$label} items found\n\n";
            return;
        }
        
        $validCount = 0;
        $totalIssues = 0;
        
        foreach ($items as $item) {
            echo "  Checking {$item->serial_no}...\n";
            
            if (!$item->qr_svg_path) {
                echo "    ✗ No QR SVG path\n";
                continue;
            }
            
            $fullPath = storage_path('app/public/' . $item->qr_svg_path);
            if (!file_exists($fullPath)) {
                echo "    ✗ QR file not found: {$fullPath}\n";
                continue;
            }
            
            $svgContent = file_get_contents($fullPath);
            $issues = validateQrStructure($svgContent, $item->serial_no);
            
            if (empty($issues)) {
                echo "    ✓ QR structure valid\n";
                $validCount++;
                
                // Save sample for manual testing
                $samplePath = "sample_qr_{$label}_{$item->serial_no}.svg";
                copy($fullPath, $samplePath);
                echo "    Sample saved: {$samplePath}\n";
            } else {
                echo "    ⚠ Issues found:\n";
                foreach ($issues as $issue) {
                    echo "      - {$issue}\n";
                }
                $totalIssues += count($issues);
            }
        }
        
        echo "  Summary: {$validCount}/{$items->count()} valid QR codes\n";
        if ($totalIssues > 0) {
            echo "  Total issues: {$totalIssues}\n";
        }
        echo "\n";
        
    } catch (Exception $e) {
        echo "  ✗ Error testing {$label}: " . $e->getMessage() . "\n\n";
    }
}

// Test QR codes for each equipment type
testEquipmentQr(\App\Models\Apar::class, 'APAR');
testEquipmentQr(\App\Models\Apat::class, 'APAT');
testEquipmentQr(\App\Models\Apab::class, 'APAB');
testEquipmentQr(\App\Models\P3k::class, 'P3K');
testEquipmentQr(\App\Models\BoxHydrant::class, 'BoxHydrant');
testEquipmentQr(\App\Models\FireAlarm::class, 'FireAlarm');
testEquipmentQr(\App\Models\RumahPompa::class, 'RumahPompa');

// Generate comprehensive test QR
echo "Generating comprehensive test QR...\n";
try {
    $testUrl = url('/') . "/scan/test-qr-" . time();
    
    // Test different configurations
    $configs = [
        ['name' => 'minimal', 'size' => 200, 'margin' => 4, 'ec' => 'M'],
        ['name' => 'standard', 'size' => 300, 'margin' => 4, 'ec' => 'M'],
        ['name' => 'large', 'size' => 400, 'margin' => 6, 'ec' => 'M'],
    ];
    
    foreach ($configs as $config) {
        $qr = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
            ->size($config['size'])
            ->margin($config['margin'])
            ->errorCorrection($config['ec'])
            ->generate($testUrl);
        
        $filename = "test_qr_{$config['name']}.svg";
        file_put_contents($filename, $qr);
        echo "  ✓ Generated {$filename}\n";
    }
    
    // Generate visual QR with current helper
    $visualQr = \App\Helpers\QrCodeHelper::generateVisualSvg($testUrl, "TEST", "QR-VALIDATION");
    file_put_contents('test_qr_visual_current.svg', $visualQr);
    echo "  ✓ Generated test_qr_visual_current.svg\n";
    
} catch (Exception $e) {
    echo "  ✗ Error generating test QR: " . $e->getMessage() . "\n";
}

echo "\n=== VALIDATION COMPLETE ===\n";
echo "Manual testing steps:\n";
echo "1. Open generated sample QR files in image viewer\n";
echo "2. Test scanning with phone camera/QR apps\n";
echo "3. Verify URLs redirect correctly\n";
echo "4. Check QR codes are readable in different lighting\n";
echo "5. Test from various distances and angles\n";

echo "\nGenerated files for testing:\n";
$files = glob('sample_qr_*.svg') + glob('test_qr_*.svg');
foreach ($files as $file) {
    echo "- {$file}\n";
}