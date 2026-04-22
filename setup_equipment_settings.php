<?php

/**
 * Setup Equipment Settings
 * 
 * This script creates default format and counter settings for all equipment types per unit.
 * Run this after updating the code to ensure serial numbers work correctly.
 * 
 * Usage: php setup_equipment_settings.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== EQUIPMENT SETTINGS SETUP ===\n\n";

// Equipment types with their default formats
$equipmentSettings = [
    'apar' => [
        'format' => 'APAR-{UNIT}-{NNN}',
        'name' => 'APAR',
    ],
    'apat' => [
        'format' => 'APAT-{UNIT}-{NNN}',
        'name' => 'APAT',
    ],
    'apab' => [
        'format' => 'APAB-{UNIT}-{NNN}',
        'name' => 'APAB',
    ],
    'fire_alarm' => [
        'format' => 'FA-{UNIT}-{NNN}',
        'name' => 'Fire Alarm',
    ],
    'box_hydrant' => [
        'format' => 'H6-{UNIT}-{NNN}',
        'name' => 'Box Hydrant',
    ],
    'rumah_pompa' => [
        'format' => 'RUMAHPOMPA-{UNIT}-{NNN}',
        'name' => 'Rumah Pompa',
    ],
    'p3k' => [
        'format' => 'P3K-{UNIT}-{NNN}',
        'name' => 'P3K',
    ],
];

try {
    // Get all units
    $units = \App\Models\Unit::all();

    if ($units->isEmpty()) {
        echo "❌ No units found! Please run UnitSeeder first.\n";
        echo "   Run: php artisan db:seed --class=UnitSeeder\n";
        exit(1);
    }

    echo "Found {$units->count()} units:\n";
    foreach ($units as $unit) {
        echo "  - {$unit->code}: {$unit->name}\n";
    }
    echo "\n";

    $totalCreated = 0;
    $totalUpdated = 0;

    // Create settings for each equipment type and unit
    foreach ($equipmentSettings as $type => $config) {
        echo "Setting up {$config['name']}...\n";
        
        foreach ($units as $unit) {
            // Create format setting
            $formatSetting = \App\Models\AparSetting::updateOrCreate(
                [
                    'key' => "{$type}_kode_format",
                    'unit_id' => $unit->id,
                ],
                [
                    'value' => $config['format'],
                    'type' => 'text',
                    'description' => "Format kode untuk {$config['name']} di unit {$unit->name}",
                ]
            );

            if ($formatSetting->wasRecentlyCreated) {
                $totalCreated++;
            } else {
                $totalUpdated++;
            }

            // Create counter setting (start from 1)
            $counterSetting = \App\Models\AparSetting::updateOrCreate(
                [
                    'key' => "{$type}_kode_counter",
                    'unit_id' => $unit->id,
                ],
                [
                    'value' => '1',
                    'type' => 'number',
                    'description' => "Counter untuk {$config['name']} di unit {$unit->name}",
                ]
            );

            if ($counterSetting->wasRecentlyCreated) {
                $totalCreated++;
            } else {
                $totalUpdated++;
            }

            // Show example serial number
            $exampleSerial = str_replace(
                ['{UNIT}', '{NNN}'],
                [$unit->code, '001'],
                $config['format']
            );
            echo "  ✓ {$unit->code}: {$exampleSerial}\n";
        }
        
        echo "\n";
    }

    echo "✅ Setup completed successfully!\n\n";
    echo "📝 Summary:\n";
    echo "  - Units configured: {$units->count()}\n";
    echo "  - Equipment types: " . count($equipmentSettings) . "\n";
    echo "  - Settings created: {$totalCreated}\n";
    echo "  - Settings updated: {$totalUpdated}\n";
    echo "  - Total settings: " . ($totalCreated + $totalUpdated) . "\n";
    echo "\n";
    echo "💡 Each unit now has independent serial number counters!\n";
    echo "\n";
    echo "Next steps:\n";
    echo "1. Login as a user from any unit (e.g., UP2WI)\n";
    echo "2. Go to create page (e.g., /box-hydrant/create)\n";
    echo "3. Verify serial number shows unit code (e.g., H6-UP2WI-001)\n";
    echo "\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "=== SETUP COMPLETE ===\n";
