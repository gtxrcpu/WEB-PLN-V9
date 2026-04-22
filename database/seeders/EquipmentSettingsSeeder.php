<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AparSetting;
use App\Models\Unit;

class EquipmentSettingsSeeder extends Seeder
{
    /**
     * Seed default format and counter settings for all equipment types per unit
     * 
     * This ensures each unit has its own independent settings for serial number generation
     */
    public function run(): void
    {
        $this->command->info('🔧 Creating equipment settings for all units...');
        $this->command->newLine();

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

        // Get all units
        $units = Unit::all();

        if ($units->isEmpty()) {
            $this->command->error('❌ No units found! Please run UnitSeeder first.');
            return;
        }

        $this->command->info("Found {$units->count()} units:");
        foreach ($units as $unit) {
            $this->command->info("  - {$unit->code}: {$unit->name}");
        }
        $this->command->newLine();

        // Create settings for each equipment type and unit
        foreach ($equipmentSettings as $type => $config) {
            $this->command->info("Setting up {$config['name']}...");
            
            foreach ($units as $unit) {
                // Create format setting
                AparSetting::updateOrCreate(
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

                // Create counter setting (start from 1)
                AparSetting::updateOrCreate(
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

                // Show example serial number
                $exampleSerial = str_replace(
                    ['{UNIT}', '{NNN}'],
                    [$unit->code, '001'],
                    $config['format']
                );
                $this->command->info("  ✓ {$unit->code}: {$exampleSerial}");
            }
            
            $this->command->newLine();
        }

        $this->command->info('✅ All equipment settings created successfully!');
        $this->command->newLine();
        $this->command->info('📝 Summary:');
        $this->command->info("  - {$units->count()} units configured");
        $this->command->info("  - " . count($equipmentSettings) . " equipment types configured");
        $this->command->info("  - Total settings created: " . ($units->count() * count($equipmentSettings) * 2));
        $this->command->newLine();
        $this->command->info('💡 Each unit now has independent serial number counters!');
    }
}
