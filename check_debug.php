<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check template
$t = \App\Models\KartuTemplate::where('module', 'apar')->first();
if ($t) {
    echo "=== TEMPLATE FOUND (id={$t->id}) ===\n";
    foreach ($t->inspection_fields as $i => $f) {
        echo "  field[$i] label={$f['label']} | key=" . ($f['key'] ?? 'NULL') . " | type={$f['type']}\n";
    }
} else {
    echo "=== NO APAR TEMPLATE IN DB ===\n";
}

// Check latest kartu
echo "\n=== LATEST 3 KARTU_APARS ===\n";
$kartus = \App\Models\KartuApar::latest()->take(3)->get();
foreach ($kartus as $k) {
    echo "id={$k->id} | pressure_gauge={$k->pressure_gauge} | pin_segel={$k->pin_segel} | selang={$k->selang} | tabung={$k->tabung} | label={$k->label} | kondisi_fisik={$k->kondisi_fisik}\n";
}
