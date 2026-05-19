<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔄 Regenerating QR codes...\n\n";

// Regenerate APAR
$apars = \App\Models\Apar::all();
echo "📦 APAR: " . $apars->count() . " items\n";
foreach ($apars as $apar) {
    try {
        $apar->generateQrSvg(true);
        echo "  ✓ {$apar->serial_no}\n";
    } catch (\Exception $e) {
        echo "  ✗ {$apar->serial_no}: {$e->getMessage()}\n";
    }
}

// Regenerate APAT
$apats = \App\Models\Apat::all();
echo "\n📦 APAT: " . $apats->count() . " items\n";
foreach ($apats as $apat) {
    try {
        $apat->generateQrSvg(true);
        echo "  ✓ {$apat->serial_no}\n";
    } catch (\Exception $e) {
        echo "  ✗ {$apat->serial_no}: {$e->getMessage()}\n";
    }
}

// Regenerate APAB
$apabs = \App\Models\Apab::all();
echo "\n📦 APAB: " . $apabs->count() . " items\n";
foreach ($apabs as $apab) {
    try {
        $apab->generateQrSvg(true);
        echo "  ✓ {$apab->serial_no}\n";
    } catch (\Exception $e) {
        echo "  ✗ {$apab->serial_no}: {$e->getMessage()}\n";
    }
}

// Regenerate P3K
$p3ks = \App\Models\P3k::all();
echo "\n📦 P3K: " . $p3ks->count() . " items\n";
foreach ($p3ks as $p3k) {
    try {
        $p3k->generateQrSvg(true);
        echo "  ✓ {$p3k->serial_no}\n";
    } catch (\Exception $e) {
        echo "  ✗ {$p3k->serial_no}: {$e->getMessage()}\n";
    }
}

// Regenerate Fire Alarm
$fireAlarms = \App\Models\FireAlarm::all();
echo "\n📦 Fire Alarm: " . $fireAlarms->count() . " items\n";
foreach ($fireAlarms as $fireAlarm) {
    try {
        $fireAlarm->generateQrSvg(true);
        echo "  ✓ {$fireAlarm->serial_no}\n";
    } catch (\Exception $e) {
        echo "  ✗ {$fireAlarm->serial_no}: {$e->getMessage()}\n";
    }
}

// Regenerate Box Hydrant
$boxHydrants = \App\Models\BoxHydrant::all();
echo "\n📦 Box Hydrant: " . $boxHydrants->count() . " items\n";
foreach ($boxHydrants as $boxHydrant) {
    try {
        $boxHydrant->generateQrSvg(true);
        echo "  ✓ {$boxHydrant->serial_no}\n";
    } catch (\Exception $e) {
        echo "  ✗ {$boxHydrant->serial_no}: {$e->getMessage()}\n";
    }
}

// Regenerate Rumah Pompa
$rumahPompas = \App\Models\RumahPompa::all();
echo "\n📦 Rumah Pompa: " . $rumahPompas->count() . " items\n";
foreach ($rumahPompas as $rumahPompa) {
    try {
        $rumahPompa->generateQrSvg(true);
        echo "  ✓ {$rumahPompa->serial_no}\n";
    } catch (\Exception $e) {
        echo "  ✗ {$rumahPompa->serial_no}: {$e->getMessage()}\n";
    }
}

echo "\n✅ QR code regeneration completed!\n";
echo "📝 All QR codes now use non-signed URLs\n";
echo "🔗 Format: http://localhost/scan/{module}/{id}\n";
