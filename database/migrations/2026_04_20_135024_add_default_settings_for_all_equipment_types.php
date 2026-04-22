<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\AparSetting;
use App\Models\Unit;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add default format and counter settings for all equipment types per unit
     */
    public function up(): void
    {
        // Equipment types with their default formats
        $equipmentSettings = [
            'apar' => 'APAR-{UNIT}-{NNN}',
            'apat' => 'APAT-{UNIT}-{NNN}',
            'apab' => 'APAB-{UNIT}-{NNN}',
            'fire_alarm' => 'FA-{UNIT}-{NNN}',
            'box_hydrant' => 'H6-{UNIT}-{NNN}',
            'rumah_pompa' => 'RUMAHPOMPA-{UNIT}-{NNN}',
            'p3k' => 'P3K-{UNIT}-{NNN}',
        ];

        // Get all units
        $units = Unit::all();

        foreach ($equipmentSettings as $type => $format) {
            // For each unit, create format and counter settings
            foreach ($units as $unit) {
                // Create format setting
                AparSetting::updateOrCreate(
                    [
                        'key' => "{$type}_kode_format",
                        'unit_id' => $unit->id,
                    ],
                    [
                        'value' => $format,
                        'type' => 'text',
                        'description' => "Format kode untuk {$type} di unit {$unit->name}",
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
                        'description' => "Counter untuk {$type} di unit {$unit->name}",
                    ]
                );
            }

            echo "✓ Created settings for {$type}\n";
        }

        echo "✅ All equipment settings created successfully\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete all equipment settings
        $equipmentTypes = ['apar', 'apat', 'apab', 'fire_alarm', 'box_hydrant', 'rumah_pompa', 'p3k'];
        
        foreach ($equipmentTypes as $type) {
            AparSetting::where('key', 'LIKE', "{$type}_kode_%")->delete();
        }
    }
};
