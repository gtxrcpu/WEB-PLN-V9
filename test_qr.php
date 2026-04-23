<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== QR CODE ANALYSIS & TESTING ===\n\n";

// Test 1: Basic QR generation
echo "1. Testing basic QR generation...\n";
try {
    $testData = "https://example.com/test";
    $basicQr = \App\Helpers\QrCodeHelper::generateDataUri($testData);
    echo "✓ Basic QR generated successfully\n";
    echo "   Length: " . strlen($basicQr) . " chars\n";
} catch (Exception $e) {
    echo "✗ Basic QR failed: " . $e->getMessage() . "\n";
}

// Test 2: Visual QR generation
echo "\n2. Testing visual QR generation...\n";
try {
    $visualQr = \App\Helpers\QrCodeHelper::generateVisualSvg($testData, "APAR", "APAR-TEST-001");
    echo "✓ Visual QR generated successfully\n";
    echo "   Length: " . strlen($visualQr) . " chars\n";
    
    // Check for potential issues
    if (strpos($visualQr, 'QR Error') !== false) {
        echo "⚠ Warning: QR contains error message\n";
    }
    
    // Save for inspection
    file_put_contents('test_qr_visual.svg', $visualQr);
    echo "   Saved to: test_qr_visual.svg\n";
} catch (Exception $e) {
    echo "✗ Visual QR failed: " . $e->getMessage() . "\n";
}

// Test 3: Check existing APAR QR
echo "\n3. Testing existing APAR QR...\n";
try {
    $apar = \App\Models\Apar::first();
    if ($apar) {
        echo "Found APAR: " . $apar->serial_no . "\n";
        
        // Test QR URL generation
        $qrUrl = $apar->qr_url;
        echo "QR URL generated: " . strlen($qrUrl) . " chars\n";
        
        // Test regeneration
        $apar->generateQrSvg(true);
        echo "✓ QR regenerated successfully\n";
        
        if ($apar->qr_svg_path) {
            $fullPath = storage_path('app/public/' . $apar->qr_svg_path);
            if (file_exists($fullPath)) {
                $size = filesize($fullPath);
                echo "   File size: " . $size . " bytes\n";
                
                // Copy for analysis
                copy($fullPath, 'test_qr_existing.svg');
                echo "   Copied to: test_qr_existing.svg\n";
            }
        }
    } else {
        echo "No APAR found in database\n";
    }
} catch (Exception $e) {
    echo "✗ APAR QR test failed: " . $e->getMessage() . "\n";
}

// Test 4: Analyze QR structure
echo "\n4. Analyzing QR structure...\n";
try {
    $simpleQr = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
        ->size(300)
        ->margin(4)
        ->errorCorrection('M')
        ->generate($testData);
    
    file_put_contents('test_qr_simple.svg', $simpleQr);
    echo "✓ Simple QR saved to: test_qr_simple.svg\n";
    
    // Check dimensions
    if (preg_match('/viewBox="([^"]*)"/', $simpleQr, $matches)) {
        echo "   ViewBox: " . $matches[1] . "\n";
    }
    if (preg_match('/width="([^"]*)"/', $simpleQr, $matches)) {
        echo "   Width: " . $matches[1] . "\n";
    }
    if (preg_match('/height="([^"]*)"/', $simpleQr, $matches)) {
        echo "   Height: " . $matches[1] . "\n";
    }
} catch (Exception $e) {
    echo "✗ QR structure analysis failed: " . $e->getMessage() . "\n";
}

echo "\n=== ANALYSIS COMPLETE ===\n";
echo "Check generated SVG files for visual inspection\n";