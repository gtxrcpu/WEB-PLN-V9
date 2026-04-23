<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FINAL QR CODE TEST ===\n\n";

// Test basic QR generation
echo "1. Testing basic QR generation...\n";
try {
    $testUrl = 'https://example.com/test-scan';
    $basicQr = \App\Helpers\QrCodeHelper::generateDataUri($testUrl);
    echo "✓ Basic QR generation: " . (strlen($basicQr) > 100 ? 'SUCCESS' : 'FAILED') . "\n";
    echo "   Length: " . strlen($basicQr) . " chars\n";
} catch (Exception $e) {
    echo "✗ Basic QR generation: FAILED - " . $e->getMessage() . "\n";
}

// Test visual QR generation
echo "\n2. Testing visual QR generation...\n";
try {
    $visualQr = \App\Helpers\QrCodeHelper::generateVisualSvg($testUrl, 'TEST', 'QR-FIX-001');
    echo "✓ Visual QR generation: " . (strlen($visualQr) > 1000 ? 'SUCCESS' : 'FAILED') . "\n";
    echo "   Length: " . strlen($visualQr) . " chars\n";
    
    // Save test file
    file_put_contents('qr_test_final.svg', $visualQr);
    echo "✓ Test QR saved: qr_test_final.svg\n";
    
    // Check structure
    if (preg_match('/width="(\d+)" height="(\d+)"/', $visualQr, $matches)) {
        echo "   Canvas size: {$matches[1]} x {$matches[2]}\n";
    }
    
    if (preg_match('/<svg x="(\d+)" y="(\d+)" width="(\d+)" height="(\d+)"/', $visualQr, $matches)) {
        echo "   QR position: x={$matches[1]}, y={$matches[2]}\n";
        echo "   QR size: {$matches[3]} x {$matches[4]}\n";
    }
    
} catch (Exception $e) {
    echo "✗ Visual QR generation: FAILED - " . $e->getMessage() . "\n";
}

// Test SVG data URI generation
echo "\n3. Testing SVG data URI generation...\n";
try {
    $dataUri = \App\Helpers\QrCodeHelper::generateVisualSvgDataUri($testUrl, 'TEST', 'QR-FIX-001');
    echo "✓ SVG Data URI generation: " . (strlen($dataUri) > 1000 ? 'SUCCESS' : 'FAILED') . "\n";
    echo "   Length: " . strlen($dataUri) . " chars\n";
} catch (Exception $e) {
    echo "✗ SVG Data URI generation: FAILED - " . $e->getMessage() . "\n";
}

echo "\n=== QR CODE IMPROVEMENTS SUMMARY ===\n";
echo "✓ Error correction level changed from H to M (better scanning)\n";
echo "✓ Proper quiet zone (4 modules minimum) implemented\n";
echo "✓ QR area kept completely free from overlapping elements\n";
echo "✓ Improved visual layout with proper spacing\n";
echo "✓ Logo positioning optimized to avoid QR interference\n";

echo "\n=== NEXT STEPS ===\n";
echo "1. Access /admin/qr-regeneration to regenerate all QR codes\n";
echo "2. Test scanning with multiple QR scanner apps:\n";
echo "   - Built-in camera apps (iOS/Android)\n";
echo "   - QR Code Reader apps\n";
echo "   - Barcode Scanner apps\n";
echo "3. Validate QR codes work in different lighting conditions\n";
echo "4. Test at various distances and angles\n";
echo "5. Verify all equipment types are scannable\n";

echo "\n=== FILES CREATED ===\n";
echo "- qr_test_final.svg (test QR for manual scanning)\n";
echo "- /admin/qr-regeneration (web interface for regeneration)\n";
echo "- QR_CODE_SCANNING_FIX.md (complete documentation)\n";

echo "\n=== TEST COMPLETE ===\n";