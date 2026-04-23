<?php
echo "=== QR CODE ANALYSIS REPORT ===\n\n";

// Analisis 1: Struktur QR Visual
echo "1. ANALYZING VISUAL QR STRUCTURE:\n";
$visualQr = file_get_contents('test_qr_visual.svg');
echo "   File size: " . strlen($visualQr) . " bytes\n";

// Check dimensions
if (preg_match('/width="(\d+)" height="(\d+)"/', $visualQr, $matches)) {
    echo "   Canvas size: {$matches[1]} x {$matches[2]}\n";
}
if (preg_match('/viewBox="([^"]*)"/', $visualQr, $matches)) {
    echo "   ViewBox: {$matches[1]}\n";
}

// Check QR positioning
if (preg_match('/<svg x="(\d+)" y="(\d+)" width="(\d+)" height="(\d+)"/', $visualQr, $matches)) {
    echo "   QR position: x={$matches[1]}, y={$matches[2]}\n";
    echo "   QR size: {$matches[3]} x {$matches[4]}\n";
}

// Check for overlapping elements
$logoCount = substr_count($visualQr, '<image');
$rectCount = substr_count($visualQr, '<rect');
$textCount = substr_count($visualQr, '<text');
echo "   Elements: {$logoCount} logos, {$rectCount} rectangles, {$textCount} texts\n";

// Analisis 2: Struktur QR Sederhana
echo "\n2. ANALYZING SIMPLE QR STRUCTURE:\n";
$simpleQr = file_get_contents('test_qr_simple.svg');
echo "   File size: " . strlen($simpleQr) . " bytes\n";

if (preg_match('/width="(\d+)" height="(\d+)"/', $simpleQr, $matches)) {
    echo "   Canvas size: {$matches[1]} x {$matches[2]}\n";
}
if (preg_match('/viewBox="([^"]*)"/', $simpleQr, $matches)) {
    echo "   ViewBox: {$matches[1]}\n";
}

// Analisis 3: Masalah Potensial
echo "\n3. POTENTIAL ISSUES IDENTIFIED:\n";

// Check margin/quiet zone
if (preg_match('/transform="translate\((\d+),(\d+)\)"/', $simpleQr, $matches)) {
    $margin = min($matches[1], $matches[2]);
    echo "   ✓ Simple QR margin: {$margin} units (good for scanning)\n";
} else {
    echo "   ⚠ Could not detect margin in simple QR\n";
}

// Check visual QR issues
$issues = [];
if (strpos($visualQr, 'y="60"') !== false && strpos($visualQr, 'height="260"') !== false) {
    $issues[] = "QR may be clipped (positioned at y=60 with height=260 in 360px canvas)";
}
if (strpos($visualQr, 'y="325"') !== false) {
    $issues[] = "Label bar may overlap QR area (positioned at y=325)";
}
if ($logoCount > 0) {
    $issues[] = "Logos may interfere with QR scanning";
}

if (empty($issues)) {
    echo "   ✓ No obvious structural issues found\n";
} else {
    foreach ($issues as $issue) {
        echo "   ⚠ {$issue}\n";
    }
}

// Analisis 4: Rekomendasi
echo "\n4. RECOMMENDATIONS:\n";
echo "   • Ensure QR has minimum 4-module quiet zone on all sides\n";
echo "   • Keep QR area completely free from overlapping elements\n";
echo "   • Use error correction level M (15%) instead of H (30%) for better scanning\n";
echo "   • Test with multiple QR scanner apps\n";
echo "   • Consider reducing visual elements that may interfere\n";

// Analisis 5: Generate Test QR
echo "\n5. GENERATING CLEAN TEST QR:\n";
try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    $testUrl = "https://example.com/test-scan";
    
    // Generate clean QR for comparison
    $cleanQr = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
        ->size(200)
        ->margin(4)
        ->errorCorrection('M')
        ->generate($testUrl);
    
    file_put_contents('test_qr_clean.svg', $cleanQr);
    echo "   ✓ Clean QR saved to: test_qr_clean.svg\n";
    
    // Generate improved visual QR
    $improvedQr = \App\Helpers\QrCodeHelper::generateVisualSvg($testUrl, "TEST", "TEST-001");
    file_put_contents('test_qr_improved.svg', $improvedQr);
    echo "   ✓ Improved visual QR saved to: test_qr_improved.svg\n";
    
} catch (Exception $e) {
    echo "   ✗ Error generating test QR: " . $e->getMessage() . "\n";
}

echo "\n=== ANALYSIS COMPLETE ===\n";
echo "Please test all generated QR codes with multiple scanner apps:\n";
echo "- test_qr_simple.svg (baseline)\n";
echo "- test_qr_visual.svg (current visual)\n";
echo "- test_qr_clean.svg (clean reference)\n";
echo "- test_qr_improved.svg (improved visual)\n";