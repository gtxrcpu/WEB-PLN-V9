<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define all units with correct human-readable names
        // Codes are short identifiers; names use proper spacing (UP2W I, UP2W II, etc.)
        
        $units = [
            ['code' => 'INDUK',   'name' => 'Induk',    'description' => 'Unit Induk (Pusat)',                             'is_active' => true],
            ['code' => 'UP2WI',   'name' => 'UP2WI',    'description' => 'Unit Pelayanan dan Pengelolaan Wilayah I',       'is_active' => true],
            ['code' => 'UP2WII',  'name' => 'UP2WII',   'description' => 'Unit Pelayanan dan Pengelolaan Wilayah II',      'is_active' => true],
            ['code' => 'UP2WIII', 'name' => 'UP2WIII',  'description' => 'Unit Pelayanan dan Pengelolaan Wilayah III',     'is_active' => true],
            ['code' => 'UP2WIV',  'name' => 'UP2WIV',   'description' => 'Unit Pelayanan dan Pengelolaan Wilayah IV',      'is_active' => true],
            ['code' => 'UP2WV',   'name' => 'UP2WV',    'description' => 'Unit Pelayanan dan Pengelolaan Wilayah V',       'is_active' => true],
            ['code' => 'UP2WVI',  'name' => 'UP2WVI',   'description' => 'Unit Pelayanan dan Pengelolaan Wilayah VI',      'is_active' => true],
        ];

        $this->command->info('Cleaning up duplicate units...');
        
        // First, remove any duplicate units by code (keep the oldest one)
        $allUnits = \App\Models\Unit::all();
        $seenCodes = [];
        
        foreach ($allUnits as $unit) {
            if (in_array($unit->code, $seenCodes)) {
                $this->command->warn("Removing duplicate unit: ID {$unit->id}, Code: {$unit->code}");
                
                // Update references before deleting
                $this->updateReferences($unit->id, $seenCodes[$unit->code]);
                
                // Delete duplicate
                $unit->delete();
            } else {
                $seenCodes[$unit->code] = $unit->id;
            }
        }
        
        // Now create or update units with correct data
        foreach ($units as $unitData) {
            $unit = \App\Models\Unit::updateOrCreate(
                ['code' => $unitData['code']],
                $unitData
            );
            
            $this->command->info("✓ Unit: {$unit->code} - {$unit->name}");
        }

        $this->command->info('✅ Units seeded successfully with 7 unique units');
    }
    
    /**
     * Update references from old unit_id to new unit_id
     */
    private function updateReferences($oldUnitId, $newUnitId)
    {
        // Update users
        \DB::table('users')->where('unit_id', $oldUnitId)->update(['unit_id' => $newUnitId]);
        
        // Update equipment tables
        $tables = ['apars', 'apats', 'apabs', 'fire_alarms', 'box_hydrants', 'rumah_pompas', 'p3ks', 'cctvs'];
        
        foreach ($tables as $table) {
            if (\Schema::hasTable($table) && \Schema::hasColumn($table, 'unit_id')) {
                \DB::table($table)->where('unit_id', $oldUnitId)->update(['unit_id' => $newUnitId]);
            }
        }
    }
}
